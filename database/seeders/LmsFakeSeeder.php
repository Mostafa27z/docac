<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\CourseEnrollment;
use App\Models\LiveSession;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LmsFakeSeeder extends Seeder
{
    /**
     * Run the database seeds with fake sample data.
     */
    public function run(): void
    {
        // 0. Create System Admin
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@lms.com',
            'phone' => '+201000000000',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // 1. Create Instructors
        $instructors = [];
        $names = ['Dr. Sarah Connor', 'Prof. John Doe', 'Dr. Emma Watson'];
        foreach ($names as $index => $name) {
            $instructors[] = User::create([
                'name' => $name,
                'email' => "instructor" . ($index + 1) . "@lms.com",
                'phone' => '+20100000000' . $index,
                'password' => Hash::make('password123'),
                'role' => 'instructor',
                'status' => 'active',
            ]);
        }

        // 2. Create Students
        $students = [];
        for ($i = 1; $i <= 10; $i++) {
            $students[] = User::create([
                'name' => "Student Number " . $i,
                'email' => "student{$i}@lms.com",
                'phone' => '+20111111111' . $i,
                'password' => Hash::make('password123'),
                'role' => 'student',
                'status' => 'active',
            ]);
        }

        // 3. Create Courses
        $courseData = [
            [
                'title' => 'Electrocardiogram (ECG) Interpretation',
                'desc' => 'Learn how to read and interpret ECGs like a professional physician. Essential for cardiology students.',
                'type' => 'recorded'
            ],
            [
                'title' => 'Advanced Pediatrics Life Support',
                'desc' => 'Emergency pediatric care, resuscitation protocols, and acute illness management in children.',
                'type' => 'live'
            ],
            [
                'title' => 'Clinical Pathology Basics',
                'desc' => 'General overview of diagnostic laboratory methodologies and blood smear evaluations.',
                'type' => 'mixed'
            ]
        ];

        foreach ($courseData as $index => $data) {
            $instructor = $instructors[$index % count($instructors)];
            $course = Course::create([
                'instructor_id' => $instructor->id,
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['desc'],
                'thumbnail' => "courses/thumbnails/course_" . ($index + 1) . ".jpg",
                'type' => $data['type'],
                'status' => 'published',
                'published_at' => now(),
            ]);

            // Add Sections to Course
            for ($s = 1; $s <= 2; $s++) {
                $section = CourseSection::create([
                    'course_id' => $course->id,
                    'title' => "Section {$s}: Foundational Theory",
                    'sort_order' => $s,
                ]);

                // Add Lessons to Section
                for ($l = 1; $l <= 3; $l++) {
                    $lessonType = ($s === 1 && $l === 3) ? 'quiz' : 'video';
                    $lesson = Lesson::create([
                        'section_id' => $section->id,
                        'title' => "Lesson {$l}: Subject overview part {$l}",
                        'description' => "Detailed instruction regarding sub-topic module {$l}.",
                        'type' => $lessonType,
                        'video_url' => $lessonType === 'video' ? "courses/videos/lesson_" . $course->id . "_" . $s . "_" . $l . ".mp4" : null,
                        'video_duration_seconds' => $lessonType === 'video' ? rand(300, 1800) : 0,
                        'sort_order' => $l,
                        'is_preview' => ($s === 1 && $l === 1),
                    ]);

                    // If it is a quiz lesson, attach quiz structure
                    if ($lessonType === 'quiz') {
                        $quiz = Quiz::create([
                            'lesson_id' => $lesson->id,
                            'title' => "Module MCQ Assessment",
                            'pass_percentage' => 60.00,
                            'time_limit_minutes' => 15,
                            'attempts_allowed' => 3
                        ]);

                        // Questions
                        for ($q = 1; $q <= 3; $q++) {
                            $question = Question::create([
                                'quiz_id' => $quiz->id,
                                'question_text' => "What is the primary indicator of abnormal cardiac cycles in condition scenario #{$q}?",
                                'type' => 'mcq',
                                'points' => 2,
                            ]);

                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_text' => 'Elevated ST Segments',
                                'is_correct' => true,
                            ]);
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_text' => 'Prolonged PR Interval',
                                'is_correct' => false,
                            ]);
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_text' => 'Sinus Tachycardia',
                                'is_correct' => false,
                            ]);
                        }
                    }
                }
            }

            // Create Live sessions if course type supports it
            if (in_array($data['type'], ['live', 'mixed'])) {
                LiveSession::create([
                    'course_id' => $course->id,
                    'title' => 'Live Q&A Session - Clinical Cases Review',
                    'description' => 'Interactive Zoom review covering real-world medical diagnoses.',
                    'start_at' => now()->addDays(2),
                    'end_at' => now()->addDays(2)->addHours(2),
                    'meeting_provider' => 'zoom',
                    'meeting_url' => 'https://zoom.us/j/9876543210',
                    'meeting_id' => '987 654 3210',
                    'status' => 'scheduled',
                ]);
            }

            // Enroll some students
            foreach ($students as $student) {
                if (rand(0, 1)) {
                    CourseEnrollment::create([
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                        'status' => 'active',
                        'progress_percentage' => rand(0, 100),
                        'enrolled_at' => now()->subDays(rand(1, 10)),
                    ]);

                    // Generate a conversation/chat thread for this enrollment
                    $conversation = Conversation::create([
                        'course_id' => $course->id,
                        'student_id' => $student->id,
                        'instructor_id' => $course->instructor_id,
                        'last_message_at' => now(),
                    ]);

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $course->instructor_id,
                        'message_text' => "Hello Student! Welcome to the course. Let me know if you have any questions.",
                        'type' => 'text',
                    ]);

                    Message::create([
                        'conversation_id' => $conversation->id,
                        'sender_id' => $student->id,
                        'message_text' => "Thank you Doctor. I am excited to start the lectures.",
                        'type' => 'text',
                    ]);
                }
            }
        }
    }
}
