<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMultiDeviceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Sample Course',
            'slug' => 'sample-course',
            'type' => 'recorded',
            'status' => 'published',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_create_student_with_multiple_devices_allowed()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'name' => 'Multi Device Student',
            'email' => 'multi@test.com',
            'phone' => '01011112222',
            'password' => 'password123',
            'allow_multiple_devices' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Multi Device Student',
            'email' => 'multi@test.com',
            'role' => 'student',
            'allow_multiple_devices' => true,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_create_regular_student_without_multiple_devices()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.students.store'), [
            'name' => 'Single Device Student',
            'email' => 'single@test.com',
            'phone' => '01033334444',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Single Device Student',
            'email' => 'single@test.com',
            'role' => 'student',
            'allow_multiple_devices' => false,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_toggle_multiple_devices_for_existing_student()
    {
        $student = User::create([
            'name' => 'Toggle Student',
            'email' => 'toggle@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'status' => 'active',
            'allow_multiple_devices' => false,
        ]);

        // Enable multi-device
        $response = $this->actingAs($this->admin)->post(route('admin.students.toggleMultiDevice', $student->id));
        $response->assertRedirect();
        $this->assertTrue($student->fresh()->allow_multiple_devices);

        // Disable multi-device
        $response2 = $this->actingAs($this->admin)->post(route('admin.students.toggleMultiDevice', $student->id));
        $response2->assertRedirect();
        $this->assertFalse($student->fresh()->allow_multiple_devices);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multi_device_student_can_login_from_multiple_devices_simultaneously()
    {
        $student = User::create([
            'name' => 'Multi Student Login',
            'email' => 'multilogin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'status' => 'active',
            'allow_multiple_devices' => true,
            'active_device_id' => 'device_first',
        ]);

        // Login from Device 1
        $res1 = $this->postJson('/api/v1/student/login', [
            'email' => 'multilogin@test.com',
            'password' => 'password123',
            'device_id' => 'device_first',
        ]);

        $res1->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.allow_multiple_devices', true);

        // Login from Device 2 with a different device_id - must succeed
        $res2 = $this->postJson('/api/v1/student/login', [
            'email' => 'multilogin@test.com',
            'password' => 'password123',
            'device_id' => 'device_second',
        ]);

        $res2->assertStatus(200)
             ->assertJsonPath('success', true)
             ->assertJsonPath('data.allow_multiple_devices', true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multi_device_student_can_access_single_device_protected_routes_from_any_device()
    {
        $student = User::create([
            'name' => 'Multi Student Access',
            'email' => 'multiaccess@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'status' => 'active',
            'allow_multiple_devices' => true,
            'active_device_id' => 'device_initial',
        ]);

        $token = $student->createToken('student_auth_token')->plainTextToken;

        // Request courses API with Device A
        $resA = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_initial',
        ])->getJson('/api/v1/student/courses');

        $resA->assertStatus(200);

        // Request courses API with Device B (different device_id)
        $resB = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'X-Device-ID' => 'device_completely_different',
        ])->getJson('/api/v1/student/courses');

        $resB->assertStatus(200);

        // Ensure token is still valid (not revoked)
        $this->assertEquals(1, $student->fresh()->tokens()->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function single_device_student_is_still_restricted_on_second_device()
    {
        $student = User::create([
            'name' => 'Restricted Student',
            'email' => 'restricted@test.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'status' => 'active',
            'allow_multiple_devices' => false,
            'active_device_id' => 'device_locked',
        ]);

        // Attempt login on second device fails
        $res = $this->postJson('/api/v1/student/login', [
            'email' => 'restricted@test.com',
            'password' => 'password123',
            'device_id' => 'device_intruder',
        ]);

        $res->assertStatus(403)
            ->assertJsonPath('message', 'هذا الحساب مسجل على جهاز آخر، يرجى التواصل مع الإدارة');
    }
}
