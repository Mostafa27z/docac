<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsWebTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $instructor;
    protected User $student;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

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
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Biology Course Test',
            'slug' => 'biology-course-test',
            'type' => 'recorded',
            'status' => 'published',
        ]);
    }

    /** @test */
    public function guests_cannot_access_any_dashboard()
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/instructor/dashboard')->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_access_admin_and_instructor_dashboards()
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $response2 = $this->actingAs($this->admin)->get('/instructor/dashboard');
        $response2->assertStatus(200);
    }

    /** @test */
    public function instructor_can_access_instructor_dashboard_but_not_admin()
    {
        $response = $this->actingAs($this->instructor)->get('/instructor/dashboard');
        $response->assertStatus(200);

        $response2 = $this->actingAs($this->instructor)->get('/admin/dashboard');
        $response2->assertStatus(403);
    }

    /** @test */
    public function instructor_can_create_draft_courses()
    {
        $response = $this->actingAs($this->instructor)->post('/instructor/courses', [
            'title' => 'Medical Physiology',
            'description' => 'A course about body organs dynamics',
            'type' => 'recorded',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'title' => 'Medical Physiology',
            'status' => 'draft',
        ]);
    }

    /** @test */
    public function admin_can_generate_activation_codes()
    {
        $response = $this->actingAs($this->admin)->post('/admin/activation-codes', [
            'course_id' => $this->course->id,
            'quantity' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('course_activation_codes', 5);
    }

    /** @test */
    public function instructor_can_manage_own_course_and_generate_codes()
    {
        $response = $this->actingAs($this->instructor)->get("/instructor/courses/{$this->course->id}/manage");
        $response->assertStatus(200);

        $codeResponse = $this->actingAs($this->instructor)->post("/instructor/courses/{$this->course->id}/activation-codes", [
            'quantity' => 3
        ]);
        $codeResponse->assertRedirect();
        $this->assertDatabaseCount('course_activation_codes', 3);
    }

    /** @test */
    public function instructor_cannot_manage_other_instructors_course()
    {
        $otherInstructor = User::create([
            'name' => 'Other Doctor',
            'email' => 'other@test.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor'
        ]);

        $response = $this->actingAs($otherInstructor)->get("/instructor/courses/{$this->course->id}/manage");
        $response->assertStatus(403);
    }

    /** @test */
    public function instructor_can_create_quiz_without_optional_time_limit()
    {
        $section = \App\Models\CourseSection::create([
            'course_id' => $this->course->id,
            'title' => 'Test Section',
            'sort_order' => 1,
        ]);

        $lesson = \App\Models\Lesson::create([
            'section_id' => $section->id,
            'title' => 'Test Quiz Lesson',
            'type' => 'quiz',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->instructor)->post("/instructor/lessons/{$lesson->id}/quizzes", [
            'title' => 'Biology Midterm Quiz',
            'pass_percentage' => 60,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'lesson_id' => $lesson->id,
            'title' => 'Biology Midterm Quiz',
            'pass_percentage' => 60,
            'time_limit_minutes' => null,
            'attempts_allowed' => null,
        ]);
    }

    /** @test */
    public function instructor_can_create_quiz_and_link_to_existing_lesson()
    {
        $section = \App\Models\CourseSection::create([
            'course_id' => $this->course->id,
            'title' => 'Test Section',
            'sort_order' => 1,
        ]);

        $lesson = \App\Models\Lesson::create([
            'section_id' => $section->id,
            'title' => 'Test Video Lesson',
            'type' => 'video',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->instructor)->post("/instructor/courses/{$this->course->id}/quizzes", [
            'lesson_id' => $lesson->id,
            'title' => 'Linked Exam',
            'pass_percentage' => 70,
            'attempts_allowed' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'lesson_id' => $lesson->id,
            'title' => 'Linked Exam',
            'pass_percentage' => 70,
            'attempts_allowed' => 2,
        ]);
    }

    /** @test */
    public function instructor_cannot_access_profile_routes()
    {
        $this->actingAs($this->instructor)->get('/profile')->assertStatus(403);
        $this->actingAs($this->instructor)->put('/profile', ['name' => 'New Name', 'email' => 'inst@test.com'])->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_profile_routes()
    {
        $this->actingAs($this->admin)->get('/profile')->assertStatus(200);
    }

    /** @test */
    public function admin_can_manage_other_admins_but_cannot_delete_self()
    {
        // 1. Can view list
        $response = $this->actingAs($this->admin)->get('/admin/admins');
        $response->assertStatus(200);

        // 2. Can create new admin
        $createResponse = $this->actingAs($this->admin)->post('/admin/admins', [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
        ]);
        $createResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@test.com',
            'role' => 'admin',
        ]);

        $newAdmin = User::where('email', 'newadmin@test.com')->first();

        // 3. Can update other admin
        $updateResponse = $this->actingAs($this->admin)->put("/admin/admins/{$newAdmin->id}", [
            'name' => 'Updated Admin Name',
            'email' => 'newadmin@test.com',
            'status' => 'suspended',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $newAdmin->id,
            'name' => 'Updated Admin Name',
            'status' => 'suspended',
        ]);

        // 4. Cannot delete self
        $deleteSelfResponse = $this->actingAs($this->admin)->delete("/admin/admins/{$this->admin->id}");
        $deleteSelfResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);

        // 5. Can delete other admin
        $deleteOtherResponse = $this->actingAs($this->admin)->delete("/admin/admins/{$newAdmin->id}");
        $deleteOtherResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $newAdmin->id]);
    }

    /** @test */
    public function home_page_and_logout_have_no_cache_headers()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $logoutResponse = $this->actingAs($this->admin)->post('/logout');
        $logoutResponse->assertRedirect('/');
        $logoutResponse->assertHeader('Cache-Control');
        $this->assertStringContainsString('no-store', $logoutResponse->headers->get('Cache-Control'));
    }
}
