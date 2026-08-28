<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $instructor;
    protected Course $course;
    protected Lesson $lesson;
    protected Quiz $quiz;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup instructor and student
        $this->instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor',
        ]);

        $this->student = User::create([
            'name' => 'Student User',
            'email' => 'student@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'active_device_id' => 'device_a_123',
        ]);

        // 2. Setup Course Structure
        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Test Medical Course',
            'slug' => 'test-medical-course',
            'description' => 'ECG cardiology parameters.',
            'type' => 'recorded',
            'status' => 'published',
        ]);

        $section = CourseSection::create([
            'course_id' => $this->course->id,
            'title' => 'Section One',
            'sort_order' => 1,
        ]);

        $this->lesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'ECG Leads Waveform',
            'type' => 'video',
            'video_url' => 'videos/waveforms.mp4',
            'video_duration_seconds' => 100, // 100 seconds
            'sort_order' => 1,
        ]);

        // 3. Create Quiz
        $this->quiz = Quiz::create([
            'lesson_id' => $this->lesson->id,
            'title' => 'Waveform Quiz',
            'pass_percentage' => 50.00,
        ]);

        $question = Question::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'P-Wave matches Atrial Activity?',
            'type' => 'mcq',
            'points' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Yes',
            'is_correct' => true,
        ]);
        QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'No',
            'is_correct' => false,
        ]);
    }

    /** @test */
    public function guests_cannot_access_protected_apis()
    {
        $response = $this->getJson('/api/v1/student/courses');
        $response->assertStatus(401);
    }

    /** @test */
    public function students_can_login_and_retrieve_profile()
    {
        $response = $this->postJson('/api/v1/student/login', [
            'email' => 'student@test.com',
            'password' => 'password123',
            'device_id' => 'device_a_123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'token', 'data']);

        $token = $response->json('token');

        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/student/profile');

        $profileResponse->assertStatus(200)
                        ->assertJsonPath('data.email', 'student@test.com');
    }

    /** @test */
    public function student_enrollment_gate_blocks_unenrolled_students()
    {
        $token = $this->student->createToken('test_token')->plainTextToken;

        // Try getting lesson before enrollment
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_a_123'
        ])->getJson("/api/v1/student/lectures/{$this->lesson->id}");

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'You are not enrolled in this course.');
    }

    /** @test */
    public function student_can_enroll_and_view_lessons_and_update_progress()
    {
        $token = $this->student->createToken('test_token')->plainTextToken;

        // Generate an activation code first
        $instructorToken = $this->instructor->createToken('instructor_token')->plainTextToken;
        $genResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $instructorToken
        ])->postJson("/api/v1/courses/{$this->course->id}/activation-code");
        $code = $genResponse->json('data.code');

        // Enroll using code
        $enrollResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_a_123'
        ])->postJson("/api/v1/student/courses/{$this->course->id}/enroll", [
            'code' => $code
        ]);

        $enrollResponse->assertStatus(201);

        // Fetch Lesson detail with signed token (Simulated)
        $lessonResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_a_123'
        ])->getJson("/api/v1/student/lectures/{$this->lesson->id}");

        $lessonResponse->assertStatus(200);

        // Update progress (Watch 50 seconds out of 100)
        $progressResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_a_123'
        ])->putJson("/api/v1/student/lectures/{$this->lesson->id}/progress", [
            'watched_seconds' => 50,
        ]);

        $progressResponse->assertStatus(200)
                         ->assertJsonPath('data.percentage', '50.00');
    }

    /** @test */
    public function instructor_can_generate_code_and_student_can_activate_it()
    {
        $instructorToken = $this->instructor->createToken('instructor_token')->plainTextToken;
        $student2 = User::create([
            'name' => 'Student 2',
            'email' => 'student2@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'active_device_id' => 'device_b_456'
        ]);
        $studentToken = $student2->createToken('student2_token')->plainTextToken;

        // 1. Generate code using Instructor token
        $genResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $instructorToken
        ])->postJson("/api/v1/courses/{$this->course->id}/activation-code");

        $genResponse->assertStatus(201)
                    ->assertJsonStructure(['success', 'data' => ['code', 'course_id']]);

        $code = $genResponse->json('data.code');

        // 2. Student activates course using that code
        $actResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $studentToken,
            'X-Device-ID' => 'device_b_456'
        ])->postJson("/api/v1/courses/activate-with-code", [
            'code' => $code,
        ]);

        $actResponse->assertStatus(200)
                    ->assertJsonPath('success', true);

        // 3. Double check if activation code cannot be reused
        $reuseResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $studentToken,
            'X-Device-ID' => 'device_b_456'
        ])->postJson("/api/v1/courses/activate-with-code", [
            'code' => $code,
        ]);

        $reuseResponse->assertStatus(400)
                      ->assertJsonPath('success', false);
    }

    /** @test */
    public function single_device_login_restriction_enforced()
    {
        // 1. Student registers device A
        $this->student->update(['active_device_id' => 'device_a']);

        // 2. Attempt login on Device B should fail
        $loginResponse = $this->postJson('/api/v1/student/login', [
            'email' => $this->student->email,
            'password' => 'password123',
            'device_id' => 'device_b'
        ]);

        $loginResponse->assertStatus(403)
                      ->assertJsonPath('message', 'هذا الحساب مسجل على جهاز آخر، يرجى التواصل مع الإدارة');

        // 3. Login on Device A succeeds
        $loginResponse2 = $this->postJson('/api/v1/student/login', [
            'email' => $this->student->email,
            'password' => 'password123',
            'device_id' => 'device_a'
        ]);

        $loginResponse2->assertStatus(200);
    }

    /** @test */
    public function student_can_retrieve_active_ads()
    {
        // 1. Create an active ad and an inactive ad
        \App\Models\Ad::create([
            'title' => 'Active Promo Ad',
            'description' => 'Active promo description',
            'image' => 'storage/ads/promo.png',
            'link' => 'https://t.me/promo',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\Ad::create([
            'title' => 'Inactive Promo Ad',
            'description' => 'Inactive promo description',
            'image' => 'storage/ads/promo2.png',
            'link' => 'https://t.me/promo2',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        // 2. Call Ads endpoint
        $response = $this->getJson('/api/v1/student/ads');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.title', 'Active Promo Ad')
                 ->assertJsonPath('data.0.link', 'https://t.me/promo');
    }

    /** @test */
    public function student_can_retrieve_course_quizzes_when_enrolled()
    {
        $token = $this->student->createToken('test_token')->plainTextToken;

        // Try getting course quizzes before enrollment
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_a_123'
        ])->getJson("/api/v1/student/courses/{$this->course->id}/quizzes");

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'You are not enrolled in this course.');

        // Enroll the student (manually/simulate enrollment via DB)
        \App\Models\CourseEnrollment::create([
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
            'status' => 'active',
            'progress_percentage' => 0.00,
            'enrolled_at' => now(),
            'total_price' => $this->course->price ?? 0.00,
        ]);

        // Get course quizzes after enrollment
        $responseEnrolled = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_a_123'
        ])->getJson("/api/v1/student/courses/{$this->course->id}/quizzes");

        $responseEnrolled->assertStatus(200)
                         ->assertJsonPath('success', true)
                         ->assertJsonCount(1, 'data')
                         ->assertJsonPath('data.0.id', $this->quiz->id);
    }
}
