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
}
