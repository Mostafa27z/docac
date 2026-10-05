<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStudentProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
        ]);

        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    /** @test */
    public function admin_can_view_student_profile()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.students.show', $this->student->id));

        $response->assertStatus(200);
        $response->assertSee($this->student->name);
        $response->assertSee($this->student->email);
    }

    /** @test */
    public function admin_can_subscribe_student_to_course_from_profile()
    {
        $course = Course::create([
            'instructor_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Test Course 1',
            'slug' => 'test-course-1',
            'price' => 100,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.students.profileSubscribe', $this->student->id), [
            'course_id' => $course->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('course_enrollments', [
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_unsubscribe_student_from_course()
    {
        $course = Course::create([
            'instructor_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'title' => 'Test Course 2',
            'slug' => 'test-course-2',
            'price' => 150,
            'status' => 'published',
        ]);

        CourseEnrollment::create([
            'student_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'total_price' => 150,
            'paid_amount' => 150,
            'payment_status' => 'fully_paid',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.students.unsubscribe', [$this->student->id, $course->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('course_enrollments', [
            'student_id' => $this->student->id,
            'course_id' => $course->id,
        ]);
    }

    /** @test */
    public function admin_can_bulk_subscribe_multiple_students_to_multiple_courses_by_ids()
    {
        $student2 = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
        ]);

        $course1 = Course::create([
            'instructor_id' => $this->admin->id,
            'title' => 'Bulk Course 1',
            'slug' => 'bulk-course-1',
            'price' => 100,
            'status' => 'published',
        ]);

        $course2 = Course::create([
            'instructor_id' => $this->admin->id,
            'title' => 'Bulk Course 2',
            'slug' => 'bulk-course-2',
            'price' => 200,
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.students.bulkSubscribe'), [
            'student_ids' => [$this->student->id, $student2->id],
            'course_ids' => [$course1->id, $course2->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check that both students are enrolled in both courses (2 students * 2 courses = 4 enrollments)
        $this->assertDatabaseHas('course_enrollments', ['student_id' => $this->student->id, 'course_id' => $course1->id]);
        $this->assertDatabaseHas('course_enrollments', ['student_id' => $this->student->id, 'course_id' => $course2->id]);
        $this->assertDatabaseHas('course_enrollments', ['student_id' => $student2->id, 'course_id' => $course1->id]);
        $this->assertDatabaseHas('course_enrollments', ['student_id' => $student2->id, 'course_id' => $course2->id]);
    }

    /** @test */
    public function admin_can_bulk_subscribe_students_by_pasted_emails_list()
    {
        $student2 = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'email' => 'student_pasted_2@test.com',
        ]);

        $course = Course::create([
            'instructor_id' => $this->admin->id,
            'title' => 'Pasted Email Course',
            'slug' => 'pasted-email-course',
            'price' => 150,
            'status' => 'published',
        ]);

        // Paste formatted emails (comma, new line, spaces, unknown email)
        $emailsText = "{$this->student->email}, student_pasted_2@test.com\nnon_existent@example.com";

        $response = $this->actingAs($this->admin)->post(route('admin.students.bulkSubscribe'), [
            'emails' => $emailsText,
            'course_ids' => [$course->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_enrollments', ['student_id' => $this->student->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('course_enrollments', ['student_id' => $student2->id, 'course_id' => $course->id]);
    }
}
