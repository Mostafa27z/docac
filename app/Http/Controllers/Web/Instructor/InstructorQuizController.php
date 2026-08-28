<?php

namespace App\Http\Controllers\Web\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Lesson;
use App\Models\Course;
use Illuminate\Http\Request;

class InstructorQuizController extends Controller
{
    public function index()
    {
        $instructorId = auth()->id();
        $isAdmin = auth()->user()->role === 'admin';

        $quizzes = Quiz::whereHas('lesson.section.course', function ($q) use ($instructorId, $isAdmin) {
            if (!$isAdmin) {
                $q->where('instructor_id', $instructorId);
            }
        })->with(['lesson.section.course'])->latest()->get();

        return view('instructor.quizzes.index', compact('quizzes'));
    }

    public function storeQuiz(Request $request, Lesson $lesson)
    {
        if (auth()->user()->role !== 'admin' && $lesson->section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'pass_percentage' => 'required|numeric|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer',
            'attempts_allowed' => 'nullable|integer',
        ]);

        Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'pass_percentage' => $validated['pass_percentage'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'attempts_allowed' => $validated['attempts_allowed'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Quiz assessment created.');
    }

    public function storeQuizForCourse(Request $request, Course $course)
    {
        if (auth()->user()->role !== 'admin' && $course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'pass_percentage' => 'required|numeric|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer',
            'attempts_allowed' => 'nullable|integer',
        ]);

        // Check if the lesson belongs to this course
        $lesson = Lesson::findOrFail($validated['lesson_id']);
        if ($lesson->section->course_id !== $course->id) {
            abort(403, 'الدرس المختار لا ينتمي لهذا الكورس.');
        }

        // Check if quiz already exists for this lesson
        $existingQuiz = Quiz::where('lesson_id', $lesson->id)->first();
        if ($existingQuiz) {
            return redirect()->back()->with('error', 'هذا الدرس يحتوي بالفعل على امتحان.');
        }

        Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'pass_percentage' => $validated['pass_percentage'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'attempts_allowed' => $validated['attempts_allowed'] ?? null,
        ]);

        return redirect()->back()->with('success', 'تم إنشاء الامتحان بنجاح وربطه بالدرس.');
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        if (auth()->user()->role !== 'admin' && $quiz->lesson->section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'question_text' => 'required|string',
            'points' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'correct_option_index' => 'required|integer',
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => $validated['question_text'],
            'points' => $validated['points'],
        ]);

        foreach ($validated['options'] as $index => $opt) {
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $opt['text'],
                'is_correct' => ($index === (int)$validated['correct_option_index']),
            ]);
        }

        return redirect()->back()->with('success', 'MCQ question and options saved.');
    }
}
