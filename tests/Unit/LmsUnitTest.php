<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\CourseEnrollment;
use App\Services\BunnyCdnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LmsUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_calculate_correct_bunny_cdn_signatures()
    {
        // Bind custom config values
        config(['services.bunny.cdn_url' => 'https://cdn.test.com']);
        config(['services.bunny.security_key' => 'secretkey123']);

        $service = new BunnyCdnService();
        
        $path = 'courses/videos/lecture1.mp4';
        $signedUrl = $service->generateSignedUrl($path, 3600);

        $this->assertStringContainsString('https://cdn.test.com/courses/videos/lecture1.mp4', $signedUrl);
        $this->assertStringContainsString('token=', $signedUrl);
        $this->assertStringContainsString('expires=', $signedUrl);
    }

    /** @test */
    public function a_course_has_relations()
    {
        $instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor'
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Biology 101',
            'slug' => 'biology-101',
            'description' => 'Test course',
            'type' => 'recorded',
            'status' => 'published',
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Section 1',
            'sort_order' => 1
        ]);

        $this->assertEquals($instructor->id, $course->instructor->id);
        $this->assertCount(1, $course->sections);
        $this->assertEquals($section->id, $course->sections->first()->id);
    }

    /** @test */
    public function a_lesson_has_attributes_and_belongs_to_section()
    {
        $instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor@test.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor'
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Biology 101',
            'slug' => 'biology-101',
            'description' => 'Test course',
            'type' => 'recorded',
            'status' => 'published',
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Section 1',
            'sort_order' => 1
        ]);

        $lesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Lesson Title',
            'type' => 'video',
            'video_url' => 'lectures/dna.mp4',
            'video_duration_seconds' => 300,
            'sort_order' => 1,
            'is_preview' => true
        ]);

        $this->assertEquals($section->id, $lesson->section->id);
        $this->assertTrue($lesson->is_preview);
    }
}
