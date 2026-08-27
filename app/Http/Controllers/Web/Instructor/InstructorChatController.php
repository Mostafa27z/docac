<?php

namespace App\Http\Controllers\Web\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class InstructorChatController extends Controller
{
    public function index(Request $request)
    {
        $instructorId = auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        // 1. Fetch conversations
        $conversationsQuery = Conversation::query();
        if (!$isAdmin) {
            $conversationsQuery->where('instructor_id', $instructorId);
        }
        $conversations = $conversationsQuery->with(['student', 'course'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        // 2. Fetch courses to allow starting chats/broadcasting per course
        $courses = $isAdmin ? Course::all() : Course::where('instructor_id', $instructorId)->get();

        // 3. Fetch all students enrolled in any of the courses
        $enrolledStudents = User::where('role', 'student')
            ->whereHas('enrollments.course', function ($query) use ($instructorId, $isAdmin) {
                if (!$isAdmin) {
                    $query->where('instructor_id', $instructorId);
                }
            })
            ->get();

        $selectedConversation = null;
        $messages = collect();

        if ($request->has('conversation_id')) {
            $selectedConversationQuery = Conversation::where('id', $request->conversation_id);
            if (!$isAdmin) {
                $selectedConversationQuery->where('instructor_id', $instructorId);
            }
            $selectedConversation = $selectedConversationQuery->with(['student', 'course'])->first();

            if ($selectedConversation) {
                $messages = $selectedConversation->messages()->with('sender')->get();
            }
        }

        return view('instructor.chats.index', compact(
            'conversations',
            'courses',
            'enrolledStudents',
            'selectedConversation',
            'messages'
        ));
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        if (auth()->user()->role !== 'admin' && $conversation->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'message_text' => 'required|string',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'message_text' => $validated['message_text'],
            'type' => 'text',
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        return redirect()->route('instructor.chats.index', ['conversation_id' => $conversation->id])
            ->with('success', 'تم إرسال الرسالة بنجاح.');
    }

    public function startChat(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'student_id' => 'required|exists:users,id',
        ]);

        // Verify course
        $courseQuery = Course::where('id', $validated['course_id']);
        if (auth()->user()->role !== 'admin') {
            $courseQuery->where('instructor_id', auth()->id());
        }
        $course = $courseQuery->firstOrFail();

        // Find or create conversation
        $conversation = Conversation::firstOrCreate([
            'course_id' => $course->id,
            'student_id' => $validated['student_id'],
            'instructor_id' => $course->instructor_id,
        ]);

        return redirect()->route('instructor.chats.index', ['conversation_id' => $conversation->id]);
    }

    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required', // Can be 'all' or a course ID
            'message_text' => 'required|string',
        ]);

        $instructorId = auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        // Get student IDs enrolled
        $query = CourseEnrollment::whereHas('course', function ($q) use ($instructorId, $validated, $isAdmin) {
            if (!$isAdmin) {
                $q->where('instructor_id', $instructorId);
            }
            if ($validated['course_id'] !== 'all') {
                $q->where('course_id', $validated['course_id']);
            }
        });

        $studentIds = $query->pluck('student_id')->unique();

        if ($studentIds->isEmpty()) {
            return redirect()->back()->with('error', 'لا يوجد طلاب مشتركين لإرسال الرسالة الجماعية لهم.');
        }

        foreach ($studentIds as $studentId) {
            $enrollment = CourseEnrollment::where('student_id', $studentId)
                ->whereHas('course', function ($q) use ($instructorId, $validated, $isAdmin) {
                    if (!$isAdmin) {
                        $q->where('instructor_id', $instructorId);
                    }
                    if ($validated['course_id'] !== 'all') {
                        $q->where('course_id', $validated['course_id']);
                    }
                })
                ->first();

            if (!$enrollment) {
                continue;
            }

            $conversation = Conversation::firstOrCreate([
                'course_id' => $enrollment->course_id,
                'student_id' => $studentId,
                'instructor_id' => $enrollment->course->instructor_id,
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'message_text' => $validated['message_text'],
                'type' => 'text',
            ]);

            $conversation->update([
                'last_message_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'تم إرسال الرسالة الجماعية بنجاح إلى جميع الطلاب المشتركين.');
    }
}
