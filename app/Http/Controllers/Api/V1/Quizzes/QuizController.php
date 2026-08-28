<?php

namespace App\Http\Controllers\Api\V1\Quizzes;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function getCourseQuizzes(Request $request, Course $course)
    {
        // Get all quizzes for lessons in sections belonging to this course
        $quizzes = Quiz::whereHas('lesson.section', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->with(['lesson' => function ($query) {
            $query->select('id', 'section_id', 'title', 'sort_order');
        }])->get();

        return response()->json([
            'success' => true,
            'message' => 'Course quizzes retrieved successfully.',
            'data' => $quizzes
        ]);
    }

    public function getLessonQuiz(Request $request, Lesson $lesson)
    {
        // 7. Get MCQ questions (Spec: 7. الامتحانات)
        $quiz = Quiz::where('lesson_id', $lesson->id)->first();
        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'No quiz found for this lecture.'
            ], 404);
        }

        // Return questions and options without disclosing correctness
        $quiz->load(['questions.options' => function($query) {
            $query->select('id', 'question_id', 'option_text');
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz retrieved successfully.',
            'data' => $quiz
        ]);
    }

    public function submitLessonQuiz(Request $request, Lesson $lesson)
    {
        $quiz = Quiz::where('lesson_id', $lesson->id)->first();
        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'No quiz found for this lecture.'
            ], 404);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_id' => 'required|exists:question_options,id',
        ]);

        $user = $request->user();

        // Create an attempt
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'score' => 0.00,
            'passed' => false,
            'started_at' => now(),
        ]);

        $totalQuestions = $quiz->questions()->count();
        $correctAnswersCount = 0;
        $correctAnswersDetails = [];

        // Fetch correct option maps to return details
        $allQuestions = $quiz->questions()->with('options')->get();

        foreach ($validated['answers'] as $ans) {
            $question = $allQuestions->firstWhere('id', $ans['question_id']);
            if (!$question) continue;

            $option = $question->options->firstWhere('id', $ans['option_id']);
            $isCorrect = $option ? (bool)$option->is_correct : false;

            if ($isCorrect) {
                $correctAnswersCount++;
            }

            QuizAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'question_id' => $ans['question_id'],
                'selected_option_id' => $ans['option_id'],
                'is_correct' => $isCorrect,
            ]);
        }

        $scorePercentage = $totalQuestions > 0 ? ($correctAnswersCount / $totalQuestions) * 100 : 0.00;
        $passed = $scorePercentage >= $quiz->pass_percentage;

        $attempt->update([
            'score' => $scorePercentage,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        // If passed, mark lesson progress as completed
        if ($passed && $quiz->lesson_id) {
            \App\Models\LessonProgress::updateOrCreate(
                [
                    'student_id' => $user->id,
                    'lesson_id' => $quiz->lesson_id,
                ],
                [
                    'watched_seconds' => 0,
                    'duration_seconds' => 0,
                    'percentage' => 100.00,
                    'last_position_seconds' => 0,
                    'completed_at' => now(),
                ]
            );

            $this->updateCourseEnrollmentProgress($user->id, $lesson->section->course_id);
        }

        // Prepare the correct answers list to return to the student as requested by Spec:
        // المخرجات: النتيجة النهائية score والإجابات الصحيحة
        foreach ($allQuestions as $q) {
            $correctOption = $q->options->firstWhere('is_correct', true);
            $correctAnswersDetails[] = [
                'question_id' => $q->id,
                'correct_option_id' => $correctOption ? $correctOption->id : null,
                'explanation' => 'Correct option verified.'
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Quiz submitted successfully.',
            'score' => $scorePercentage,
            'passed' => $passed,
            'correct_answers' => $correctAnswersDetails
        ]);
    }

    public function show(Request $request, Quiz $quiz)
    {
        // Return quiz details and questions without disclosing correctness
        $quiz->load(['questions.options' => function($query) {
            $query->select('id', 'question_id', 'option_text'); // is_correct is excluded
        }]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz retrieved successfully.',
            'data' => $quiz
        ]);
    }

    public function startAttempt(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        // Optional: Check attempts allowed limits
        if ($quiz->attempts_allowed) {
            $previousAttemptsCount = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('student_id', $user->id)
                ->count();

            if ($previousAttemptsCount >= $quiz->attempts_allowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have exceeded the maximum allowed attempts for this quiz.'
                ], 403);
            }
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $user->id,
            'score' => 0.00,
            'passed' => false,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz attempt started.',
            'data' => $attempt
        ], 201);
    }

    public function submitAttempt(Request $request, QuizAttempt $attempt)
    {
        if ($attempt->submitted_at) {
            return response()->json([
                'success' => false,
                'message' => 'This attempt has already been submitted.'
            ], 400);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.selected_option_id' => 'required|exists:question_options,id',
        ]);

        $quiz = $attempt->quiz;
        $totalQuestions = $quiz->questions()->count();
        $correctAnswersCount = 0;

        foreach ($validated['answers'] as $ans) {
            $question = Question::find($ans['question_id']);
            $option = $question->options()->where('id', $ans['selected_option_id'])->first();
            $isCorrect = $option ? $option->is_correct : false;

            if ($isCorrect) {
                $correctAnswersCount++;
            }

            QuizAnswer::create([
                'quiz_attempt_id' => $attempt->id,
                'question_id' => $ans['question_id'],
                'selected_option_id' => $ans['selected_option_id'],
                'is_correct' => $isCorrect,
            ]);
        }

        $scorePercentage = $totalQuestions > 0 ? ($correctAnswersCount / $totalQuestions) * 100 : 0.00;
        $passed = $scorePercentage >= $quiz->pass_percentage;

        $attempt->update([
            'score' => $scorePercentage,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);

        // If quiz is part of a lesson, update lesson progress to complete if passed
        if ($passed && $quiz->lesson_id) {
            \App\Models\LessonProgress::updateOrCreate(
                [
                    'student_id' => $attempt->student_id,
                    'lesson_id' => $quiz->lesson_id,
                ],
                [
                    'watched_seconds' => 0,
                    'duration_seconds' => 0,
                    'percentage' => 100.00,
                    'last_position_seconds' => 0,
                    'completed_at' => now(),
                ]
            );

            // Re-calculate course enrollment progress
            $lesson = \App\Models\Lesson::find($quiz->lesson_id);
            if ($lesson) {
                $this->updateCourseEnrollmentProgress($attempt->student_id, $lesson->section->course_id);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Quiz attempt submitted successfully.',
            'data' => [
                'attempt_id' => $attempt->id,
                'score' => $scorePercentage,
                'passed' => $passed,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswersCount,
            ]
        ]);
    }

    protected function updateCourseEnrollmentProgress(int $studentId, int $courseId)
    {
        $totalLessons = \App\Models\Lesson::whereHas('section', function($q) use ($courseId) {
            $q->where('course_id', $courseId);
        })->count();

        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = \App\Models\LessonProgress::where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->whereHas('lesson.section', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->count();

        $overallPercentage = ($completedLessons / $totalLessons) * 100;
        $status = $overallPercentage >= 100 ? 'completed' : 'active';
        $completedDate = $overallPercentage >= 100 ? now() : null;

        \App\Models\CourseEnrollment::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->update([
                'progress_percentage' => min(100.00, $overallPercentage),
                'status' => $status,
                'completed_at' => $completedDate,
            ]);
    }
}
