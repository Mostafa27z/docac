<?php

namespace App\Http\Controllers\Api\V1\Courses;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CoursePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\EphemeralKey;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    /**
     * Set Stripe API key from configuration.
     */
    protected function setStripeKey(): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            abort(500, 'Stripe secret key is not configured.');
        }
        Stripe::setApiKey($secret);
    }

    /**
     * Get or create a Stripe Customer for the authenticated user.
     */
    protected function getOrCreateStripeCustomer(User $user): string
    {
        $this->setStripeKey();

        if ($user->stripe_customer_id) {
            try {
                $customer = Customer::retrieve($user->stripe_customer_id);
                if (!$customer->isDeleted()) {
                    return $customer->id;
                }
            } catch (\Exception $e) {
                Log::warning('Stripe customer retrieval failed, creating fresh customer', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => (string) $user->id,
                'phone' => (string) ($user->phone ?? ''),
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Step 1: Initialize Payment Sheet for mobile (Flutter / iOS Apple Pay / Android Google Pay / Card).
     *
     * POST /api/v1/student/courses/{course}/payment-sheet
     */
    public function initPaymentSheet(Request $request, Course $course)
    {
        $user = $request->user();

        // 1. Validate course status
        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot purchase an unpublished or draft course.'
            ], 400);
        }

        if (isset($course->allowed) && !$course->allowed) {
            return response()->json([
                'success' => false,
                'message' => 'This course is currently not open for enrollment.'
            ], 400);
        }

        // 2. Validate course price
        $price = (float) $course->price;
        if ($price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This course is free. Please enroll directly without payment.'
            ], 400);
        }

        // 3. Check existing enrollment
        $existing = CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ], 400);
        }

        try {
            $this->setStripeKey();

            // 4. Customer & Ephemeral Key
            $customerId = $this->getOrCreateStripeCustomer($user);

            $ephemeralKey = EphemeralKey::create(
                ['customer' => $customerId],
                ['stripe_version' => '2024-12-18.acacia']
            );

            // 5. Create Payment Intent
            $currency = strtolower(config('services.stripe.currency', 'usd'));
            $amountInCents = (int) round($price * 100);

            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => $currency,
                'customer' => $customerId,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'description' => "DocAC Course Purchase: {$course->title} (Student: {$user->email})",
                'metadata' => [
                    'course_id' => (string) $course->id,
                    'student_id' => (string) $user->id,
                    'course_title' => $course->title,
                    'student_email' => $user->email,
                    'student_name' => $user->name,
                    'type' => 'course_purchase',
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment sheet parameters generated successfully.',
                'data' => [
                    'paymentIntentClientSecret' => $paymentIntent->client_secret,
                    'paymentIntentId' => $paymentIntent->id,
                    'ephemeralKeySecret' => $ephemeralKey->secret,
                    'customerId' => $customerId,
                    'publishableKey' => config('services.stripe.key'),
                    'merchantDisplayName' => config('app.name', 'DocAC Academy'),
                    'amount' => $price,
                    'currency' => $currency,
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                        'price' => $price,
                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Stripe initPaymentSheet failed', [
                'course_id' => $course->id,
                'student_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 2: Confirm successful mobile payment and enroll student in course.
     *
     * POST /api/v1/student/courses/{course}/confirm-payment
     */
    public function confirmPayment(Request $request, Course $course)
    {
        $validated = $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $user = $request->user();

        try {
            $this->setStripeKey();

            $paymentIntent = PaymentIntent::retrieve($validated['payment_intent_id']);

            // 1. Verify Payment Status
            if ($paymentIntent->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment has not been completed yet (status: ' . $paymentIntent->status . ').'
                ], 400);
            }

            // 2. Verify Metadata Security Checks
            $metaCourseId = $paymentIntent->metadata->course_id ?? null;
            $metaStudentId = $paymentIntent->metadata->student_id ?? null;

            if ((string) $metaCourseId !== (string) $course->id || (string) $metaStudentId !== (string) $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment confirmation failed: payment details do not match this course or user.'
                ], 403);
            }

            // 3. Enroll student idempotently
            $enrollment = DB::transaction(function () use ($course, $user, $paymentIntent) {
                $enrollment = CourseEnrollment::where('course_id', $course->id)
                    ->where('student_id', $user->id)
                    ->first();

                if ($enrollment) {
                    if ($enrollment->status !== 'active') {
                        $enrollment->update([
                            'status' => 'active',
                            'payment_status' => 'fully_paid',
                            'paid_amount' => $course->price,
                        ]);
                    }
                } else {
                    $enrollment = CourseEnrollment::create([
                        'course_id' => $course->id,
                        'student_id' => $user->id,
                        'status' => 'active',
                        'progress_percentage' => 0.00,
                        'enrolled_at' => now(),
                        'total_price' => $course->price,
                        'paid_amount' => $course->price,
                        'payment_status' => 'fully_paid',
                    ]);
                }

                // Check if payment record already logged for this transaction
                $alreadyLogged = CoursePayment::where('transaction_id', $paymentIntent->id)->exists();
                if (!$alreadyLogged) {
                    $paymentMethod = $paymentIntent->payment_method_types[0] ?? 'stripe';
                    CoursePayment::create([
                        'course_enrollment_id' => $enrollment->id,
                        'amount' => $course->price,
                        'payment_method' => $paymentMethod,
                        'transaction_id' => $paymentIntent->id,
                        'notes' => "Stripe in-app payment confirmed (PaymentIntent: {$paymentIntent->id})",
                    ]);
                }

                return $enrollment;
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed and enrollment activated successfully!',
                'data' => [
                    'enrollment_id' => $enrollment->id,
                    'course_id' => $course->id,
                    'status' => $enrollment->status,
                    'payment_status' => $enrollment->payment_status,
                    'enrolled_at' => $enrollment->enrolled_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Stripe confirmPayment failed', [
                'course_id' => $course->id,
                'student_id' => $user->id,
                'payment_intent_id' => $validated['payment_intent_id'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook endpoint to catch background Stripe events (e.g. payment_intent.succeeded).
     *
     * POST /api/stripe/webhook
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        $event = null;

        try {
            if (!empty($webhookSecret) && !empty($sigHeader)) {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                $data = json_decode($payload, true);
                $event = \Stripe\Event::constructFrom($data);
            }
        } catch (\Exception $e) {
            Log::error('Stripe Webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $courseId = $paymentIntent->metadata->course_id ?? null;
            $studentId = $paymentIntent->metadata->student_id ?? null;

            if ($courseId && $studentId) {
                $course = Course::find($courseId);
                $student = User::find($studentId);

                if ($course && $student) {
                    DB::transaction(function () use ($course, $student, $paymentIntent) {
                        $enrollment = CourseEnrollment::where('course_id', $course->id)
                            ->where('student_id', $student->id)
                            ->first();

                        if ($enrollment) {
                            if ($enrollment->status !== 'active') {
                                $enrollment->update([
                                    'status' => 'active',
                                    'payment_status' => 'fully_paid',
                                    'paid_amount' => $course->price,
                                ]);
                            }
                        } else {
                            $enrollment = CourseEnrollment::create([
                                'course_id' => $course->id,
                                'student_id' => $student->id,
                                'status' => 'active',
                                'progress_percentage' => 0.00,
                                'enrolled_at' => now(),
                                'total_price' => $course->price,
                                'paid_amount' => $course->price,
                                'payment_status' => 'fully_paid',
                            ]);
                        }

                        $alreadyLogged = CoursePayment::where('transaction_id', $paymentIntent->id)->exists();
                        if (!$alreadyLogged) {
                            CoursePayment::create([
                                'course_enrollment_id' => $enrollment->id,
                                'amount' => $course->price,
                                'payment_method' => $paymentIntent->payment_method_types[0] ?? 'stripe',
                                'transaction_id' => $paymentIntent->id,
                                'notes' => "Webhook confirmed (PaymentIntent: {$paymentIntent->id})",
                            ]);
                        }
                    });
                }
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
