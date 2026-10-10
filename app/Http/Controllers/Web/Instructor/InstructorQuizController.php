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

    public function updateQuiz(Request $request, Quiz $quiz)
    {
        if (auth()->user()->role !== 'admin' && $quiz->lesson->section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'pass_percentage' => 'required|numeric|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer',
            'attempts_allowed' => 'nullable|integer',
        ]);

        $quiz->update([
            'title' => $validated['title'],
            'pass_percentage' => $validated['pass_percentage'],
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'attempts_allowed' => $validated['attempts_allowed'] ?? null,
        ]);

        return redirect()->back()->with('success', 'تم تحديث بيانات الامتحان بنجاح.');
    }

    public function destroyQuiz(Quiz $quiz)
    {
        if (auth()->user()->role !== 'admin' && $quiz->lesson->section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $quiz->delete();

        return redirect()->back()->with('success', 'تم حذف الامتحان بنجاح.');
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
            'options.*.text' => 'nullable|string',
            'correct_option_index' => 'required|integer',
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => $validated['question_text'],
            'points' => $validated['points'],
        ]);

        $correctOriginalIndex = (int) $validated['correct_option_index'];
        foreach ($validated['options'] as $index => $opt) {
            $text = trim($opt['text'] ?? '');
            if ($text === '') continue;

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $text,
                'is_correct' => ($index === $correctOriginalIndex),
            ]);
        }

        return redirect()->back()->with('success', 'تم حفظ السؤال والخيارات بنجاح.');
    }

    public function updateQuestion(Request $request, Question $question)
    {
        if (auth()->user()->role !== 'admin' && $question->quiz->lesson->section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'question_text' => 'required|string',
            'points' => 'required|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.text' => 'nullable|string',
            'correct_option_index' => 'required|integer',
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'points' => $validated['points'],
        ]);

        $question->options()->delete();

        $correctOriginalIndex = (int) $validated['correct_option_index'];
        foreach ($validated['options'] as $index => $opt) {
            $text = trim($opt['text'] ?? '');
            if ($text === '') continue;

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $text,
                'is_correct' => ($index === $correctOriginalIndex),
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث السؤال والخيارات بنجاح.');
    }

    public function destroyQuestion(Question $question)
    {
        if (auth()->user()->role !== 'admin' && $question->quiz->lesson->section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $question->delete();

        return redirect()->back()->with('success', 'تم حذف السؤال بنجاح.');
    }

    public function storeBulkQuestions(Request $request, Quiz $quiz)
    {
        if (auth()->user()->role !== 'admin' && $quiz->lesson->section->course->instructor_id !== auth()->id()) {
            abort(403);
        }

        $questionsData = [];

        if ($request->filled('questions_json')) {
            $decoded = json_decode($request->input('questions_json'), true);
            if (is_array($decoded)) {
                $questionsData = $decoded;
            }
        } elseif ($request->has('questions') && is_array($request->input('questions'))) {
            $questionsData = $request->input('questions');
        } elseif ($request->filled('raw_text')) {
            $questionsData = $this->parseRawQuestionsText($request->input('raw_text'));
        }

        if (empty($questionsData)) {
            return redirect()->back()->withErrors(['bulk' => 'لم يتم العثور على أي أسئلة صالحة للإضافة. يرجى التحقق من الصيغة المكتوبة.'])->withInput();
        }

        $createdCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($quiz, $questionsData, &$createdCount) {
            foreach ($questionsData as $item) {
                $questionText = trim($item['question_text'] ?? $item['text'] ?? '');
                if (empty($questionText)) continue;

                $points = isset($item['points']) && (int)$item['points'] > 0 ? (int)$item['points'] : 1;
                $options = $item['options'] ?? [];

                if (empty($options) || !is_array($options)) continue;

                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question_text' => $questionText,
                    'points' => $points,
                ]);

                $correctIndex = isset($item['correct_option_index']) ? (int)$item['correct_option_index'] : -1;

                foreach ($options as $idx => $opt) {
                    $optText = is_array($opt) ? trim($opt['text'] ?? '') : trim($opt);
                    if ($optText === '') continue;

                    $isCorrect = false;
                    if (is_array($opt) && isset($opt['is_correct'])) {
                        $isCorrect = (bool)$opt['is_correct'];
                    } elseif ($idx === $correctIndex) {
                        $isCorrect = true;
                    }

                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optText,
                        'is_correct' => $isCorrect,
                    ]);
                }

                // If no option was marked correct, fallback to first option
                $hasCorrect = $question->options()->where('is_correct', true)->exists();
                if (!$hasCorrect && $question->options()->count() > 0) {
                    $question->options()->first()->update(['is_correct' => true]);
                }

                $createdCount++;
            }
        });

        return redirect()->back()->with('success', "تمت إضافة {$createdCount} سؤال بنجاح إلى الامتحان.");
    }

    private function parseRawQuestionsText(string $rawText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($rawText));
        $questions = [];
        $currentQuestion = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            // 1. Check if line indicates Answer: ...
            if (preg_match('/^(?:answer|ans|الجواب|الإجابة|الاجابة|الصحيح)\s*[:=\-]\s*([a-z0-9\x{0621}-\x{064A}]+)/iu', $trimmed, $ansMatch)) {
                if ($currentQuestion && !empty($currentQuestion['options'])) {
                    $ansKey = mb_strtolower(trim($ansMatch[1]));
                    $ansIndex = $this->resolveAnswerIndex($ansKey, $currentQuestion['options']);
                    if ($ansIndex !== null) {
                        $currentQuestion['correct_option_index'] = $ansIndex;
                        foreach ($currentQuestion['options'] as $k => &$opt) {
                            $opt['is_correct'] = ($k === $ansIndex);
                        }
                    }
                }
                continue;
            }

            // 2. Check if line is a Question start (numbered like 1., 1), Q1:, س1:, etc.):
            if (preg_match('/^(?:Q\s*\d+|\d+|س\s*\d+|سؤال\s*\d+)[\.\)\-\:\s]\s*(.+)$/iu', $trimmed, $qMatch)) {
                if ($currentQuestion && !empty($currentQuestion['options'])) {
                    $questions[] = $currentQuestion;
                }
                $currentQuestion = [
                    'question_text' => trim($qMatch[1]),
                    'points' => 1,
                    'options' => [],
                    'correct_option_index' => null,
                ];
                continue;
            }

            // 3. Check if line is an Option (e.g. A) ..., *B) ..., أ) ..., *ب) ...):
            if (preg_match('/^(\*|\[x\]|\(x\))?\s*([A-Za-z\x{0621}-\x{064A}])[\.\)\-\:\/\]]\s*(.+)$/u', $trimmed, $optMatch)) {
                if ($currentQuestion) {
                    $isMarkedCorrect = !empty($optMatch[1]);
                    $optText = trim($optMatch[3]);
                    $currentQuestion['options'][] = [
                        'key' => mb_strtolower($optMatch[2]),
                        'text' => $optText,
                        'is_correct' => $isMarkedCorrect,
                    ];
                    if ($isMarkedCorrect) {
                        $currentQuestion['correct_option_index'] = count($currentQuestion['options']) - 1;
                    }
                    continue;
                }
            }

            // 4. If line is not an option and we don't have a question yet
            if (!$currentQuestion) {
                $currentQuestion = [
                    'question_text' => $trimmed,
                    'points' => 1,
                    'options' => [],
                    'correct_option_index' => null,
                ];
            } else {
                // If question has no options yet, append to question text
                if (empty($currentQuestion['options'])) {
                    $currentQuestion['question_text'] .= ' ' . $trimmed;
                } else {
                    // If question already had options and this line doesn't look like an option, start a new question
                    $questions[] = $currentQuestion;
                    $currentQuestion = [
                        'question_text' => $trimmed,
                        'points' => 1,
                        'options' => [],
                        'correct_option_index' => null,
                    ];
                }
            }
        }

        if ($currentQuestion && !empty($currentQuestion['options'])) {
            $questions[] = $currentQuestion;
        }

        return $questions;
    }

    private function resolveAnswerIndex(string $key, array $options): ?int
    {
        $arabicLetters = ['أ' => 0, 'ا' => 0, 'ب' => 1, 'ج' => 2, 'د' => 3, 'ه' => 4, 'و' => 5];
        $latinLetters = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5];

        if (isset($arabicLetters[$key])) return $arabicLetters[$key];
        if (isset($latinLetters[$key])) return $latinLetters[$key];
        if (is_numeric($key)) {
            $num = (int)$key;
            if ($num >= 1 && $num <= count($options)) return $num - 1;
            if ($num >= 0 && $num < count($options)) return $num;
        }

        foreach ($options as $idx => $opt) {
            if (isset($opt['key']) && $opt['key'] === $key) {
                return $idx;
            }
        }

        return null;
    }
}
