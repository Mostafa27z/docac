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

    /** @test */
    public function it_can_detect_and_handle_youtube_video_urls()
    {
        $service = new BunnyCdnService();

        $urls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
        ];

        foreach ($urls as $url => $expectedId) {
            $this->assertEquals($expectedId, $service->extractYouTubeId($url) ?? $url);
            $playbackUrl = $service->buildVideoPlaybackUrl($url);
            $this->assertStringContainsString("https://www.youtube.com/embed/{$expectedId}", $playbackUrl);
        }

        $this->assertFalse($service->isYouTubeUrl('courses/videos/lecture1.mp4'));
    }

    /** @test */
    public function lesson_model_identifies_video_providers()
    {
        $instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor_yt@test.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor'
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'YouTube Test Course',
            'slug' => 'youtube-test-course',
            'type' => 'recorded',
            'status' => 'published',
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Section 1',
            'sort_order' => 1
        ]);

        $ytLesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'YouTube Lesson',
            'type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'sort_order' => 1,
        ]);

        $bunnyGuidLesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Bunny Stream Lesson',
            'type' => 'video',
            'video_url' => '123e4567-e89b-12d3-a456-426614174000',
            'sort_order' => 2,
        ]);

        $bunnyStorageLesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Bunny Storage Lesson',
            'type' => 'video',
            'video_url' => 'courses/videos/lecture1.mp4',
            'sort_order' => 3,
        ]);

        $this->assertEquals('youtube', $ytLesson->video_provider);
        $this->assertTrue($ytLesson->is_youtube);
        $this->assertEquals('https://www.youtube.com/embed/dQw4w9WgXcQ', $ytLesson->youtube_embed_url);

        $this->assertEquals('bunny_stream', $bunnyGuidLesson->video_provider);
        $this->assertFalse($bunnyGuidLesson->is_youtube);

        $this->assertEquals('bunny_storage', $bunnyStorageLesson->video_provider);
        $this->assertFalse($bunnyStorageLesson->is_youtube);
    }
}
