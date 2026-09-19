<?php

namespace Tests\Feature\Instructor;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $instructor;
    protected Course $course;
    protected Quiz $quiz;
    protected Question $question;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@quiztest.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->instructor = User::create([
            'name' => 'Instructor User',
            'email' => 'instructor@quiztest.com',
            'password' => bcrypt('password123'),
            'role' => 'instructor',
            'status' => 'active',
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'ECG Fundamentals',
            'slug' => 'ecg-fundamentals',
            'type' => 'recorded',
            'status' => 'published',
            'price' => 100,
        ]);

        $section = CourseSection::create([
            'course_id' => $this->course->id,
            'title' => 'Section 1',
            'sort_order' => 1,
        ]);

        $lesson = Lesson::create([
            'section_id' => $section->id,
            'title' => 'Quiz Lesson',
            'type' => 'quiz',
            'sort_order' => 1,
        ]);

        $this->quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'ECG Assessment',
            'pass_percentage' => 60,
        ]);

        $this->question = Question::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'What does P wave represent?',
            'points' => 2,
        ]);

        QuestionOption::create([
            'question_id' => $this->question->id,
            'option_text' => 'Atrial depolarization',
            'is_correct' => true,
        ]);

        QuestionOption::create([
            'question_id' => $this->question->id,
            'option_text' => 'Ventricular repolarization',
            'is_correct' => false,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function instructor_and_admin_can_update_quiz_question()
    {
        $response = $this->actingAs($this->instructor)->put(route('instructor.questions.update', $this->question->id), [
            'question_text' => 'Updated P wave question text?',
            'points' => 5,
            'options' => [
                ['text' => 'Option A (Correct)'],
                ['text' => 'Option B'],
                ['text' => 'Option C'],
            ],
            'correct_option_index' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('questions', [
            'id' => $this->question->id,
            'question_text' => 'Updated P wave question text?',
            'points' => 5,
        ]);
        $this->assertDatabaseHas('question_options', [
            'question_id' => $this->question->id,
            'option_text' => 'Option A (Correct)',
            'is_correct' => 1,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function instructor_and_admin_can_delete_quiz_question()
    {
        $response = $this->actingAs($this->admin)->delete(route('instructor.questions.destroy', $this->question->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('questions', [
            'id' => $this->question->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function instructor_and_admin_can_update_quiz_settings()
    {
        $response = $this->actingAs($this->instructor)->put(route('instructor.quizzes.update', $this->quiz->id), [
            'title' => 'Updated Quiz Title',
            'pass_percentage' => 75,
            'time_limit_minutes' => 45,
            'attempts_allowed' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quizzes', [
            'id' => $this->quiz->id,
            'title' => 'Updated Quiz Title',
            'pass_percentage' => 75,
            'time_limit_minutes' => 45,
            'attempts_allowed' => 5,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function instructor_and_admin_can_delete_quiz()
    {
        $response = $this->actingAs($this->admin)->delete(route('instructor.quizzes.destroy', $this->quiz->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('quizzes', [
            'id' => $this->quiz->id,
        ]);
    }
}
