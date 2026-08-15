<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Instructor
        $instructor = User::create([
            'name' => 'Dr. Ahmed Ali',
            'email' => 'instructor@lms.com',
            'phone' => '+201000000000',
            'password' => Hash::make('password123'),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        // 2. Create Student
        $student = User::create([
            'name' => 'Mohamed Student',
            'email' => 'student@lms.com',
            'phone' => '+201111111111',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'status' => 'active',
        ]);

        // 3. Create Course
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Cardiology Basics',
            'slug' => Str::slug('Cardiology Basics'),
            'description' => 'Introduction course to electrocardiogram (ECG) interpretation and cardiac biology basics.',
            'thumbnail' => 'courses/cardiology-basics.jpg',
            'type' => 'recorded',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // 4. Create Section
        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'Section 1: ECG Introduction',
            'sort_order' => 1,
        ]);

        // 5. Create Lessons
        $lesson1 = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Lesson 1: Introduction to Cardiac Cycles',
            'description' => 'Basics of cardiac contraction and electrical impulse pathways.',
            'type' => 'video',
            'video_url' => 'videos/cardiac-cycles.mp4',
            'video_duration_seconds' => 600, // 10 minutes
            'sort_order' => 1,
            'is_preview' => true,
        ]);

        $lesson2 = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Lesson 2: Reading P-Waves',
            'description' => 'Detailed understanding of atrial depolarization.',
            'type' => 'video',
            'video_url' => 'videos/p-waves.mp4',
            'video_duration_seconds' => 900, // 15 minutes
            'sort_order' => 2,
            'is_preview' => false,
        ]);

        // 6. Create Quiz for Lesson 2
        $quiz = Quiz::create([
            'lesson_id' => $lesson2->id,
            'title' => 'ECG Reading Basics MCQ Quiz',
            'pass_percentage' => 50.00,
            'time_limit_minutes' => 10,
            'attempts_allowed' => 3,
        ]);

        // 7. Questions
        $question1 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'What does the P-wave represent on an ECG?',
            'type' => 'mcq',
            'points' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $question1->id,
            'option_text' => 'Atrial depolarization',
            'is_correct' => true,
        ]);
        QuestionOption::create([
            'question_id' => $question1->id,
            'option_text' => 'Ventricular depolarization',
            'is_correct' => false,
        ]);
        QuestionOption::create([
            'question_id' => $question1->id,
            'option_text' => 'Atrial repolarization',
            'is_correct' => false,
        ]);

        // 8. Auto enroll student in this cardiology course for testing
        \App\Models\CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => 'active',
            'progress_percentage' => 0.00,
            'enrolled_at' => now(),
        ]);
    }
}
