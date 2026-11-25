<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    // دالة تجلب جميع المساقات الدراسية المتاحة في النظام
    public function getCourses()
    {
        return response()->json(DB::table('courses')->orderBy('course_name')->get());
    }

    // دالة تسمح للمحاضر بإضافة مساق دراسي جديد
    public function storeCourse(Request $request)
    {
        $data = $request->validate(['course_name' => 'required|string|max:150|unique:courses,course_name']);
        $id = DB::table('courses')->insertGetId([
            'course_name' => trim($data['course_name']),
            'created_by' => $request->user()->user_id,
        ]);

        return response()->json(['course_id' => $id, 'course_name' => trim($data['course_name'])], 201);
    }

    // دالة تجلب اختبارات المحاضر مع عدد الأسئلة ومجموع الدرجات والأسئلة المرتبطة
    public function getQuizzes()
    {
        $quizzes = Quiz::with('course')->orderByDesc('quiz_id')->get()->map(function ($quiz) {
            $attached = DB::table('quiz_questions')->where('quiz_id', $quiz->quiz_id)->orderBy('sort_order')->get();
            $quiz->questions_count = $attached->count();
            $quiz->total_marks = $attached->sum('marks');
            $quiz->attached_questions = $attached;
            $quiz->status = $quiz->is_published ? 'Published' : 'Draft';

            return $quiz;
        });

        return response()->json($quizzes);
    }

       // دالة إنشاء اختبار جديد (المرحلة الأولى من معالج المحاضر)
    public function storeQuiz(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'course_id' => 'required|integer',
            'duration' => 'required|integer|min:1|max:300',
            'passingScore' => 'required|numeric|min:0|max:100',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date',
            'result_release_mode' => 'nullable|in:immediate,after_end,manual',
            'questions_per_page' => 'nullable|integer|min:1|max:50',
            'exam_mode' => 'nullable|in:fixed,random_pool,sectioned',
            'allow_backtracking' => 'nullable|boolean',
            'show_answers' => 'nullable|boolean',
            'section_rules' => 'nullable|string',
        ]);

        $mode = $data['result_release_mode'] ?? 'immediate';

        $quiz = Quiz::create([
            'course_id' => $data['course_id'],
            'title' => $data['title'],
            'duration_minutes' => $data['duration'],
            'pass_percentage' => $data['passingScore'],
            'available_from' => $data['available_from'] ?? null,
            'available_until' => $data['available_until'] ?? null,
            'result_release_mode' => $mode,
            'results_released' => $mode === 'immediate',
            'questions_per_page' => $data['questions_per_page'] ?? 1,
            'exam_mode' => $data['exam_mode'] ?? 'fixed',
            'allow_backtracking' => $data['allow_backtracking'] ?? true,
            'show_answers' => $data['show_answers'] ?? true,
            'section_rules' => $data['section_rules'] ?? null,
            'is_published' => false,
        ]);

        return response()->json($quiz, 201);
    }

    // دالة تعديل إعدادات الاختبار (تعمل قبل النشر وبعد النشر)
    public function updateQuiz(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'course_id' => 'required|integer',
            'duration' => 'required|integer|min:1|max:300',
            'passingScore' => 'required|numeric|min:0|max:100',
            'available_from' => 'nullable|date',
            'available_until' => 'nullable|date',
            'result_release_mode' => 'nullable|in:immediate,after_end,manual',
            'questions_per_page' => 'nullable|integer|min:1|max:50',
            'exam_mode' => 'nullable|in:fixed,random_pool,sectioned',
            'allow_backtracking' => 'nullable|boolean',
            'show_answers' => 'nullable|boolean',
            'section_rules' => 'nullable|string',
        ]);

        $mode = $data['result_release_mode'] ?? $quiz->result_release_mode;

        $quiz->update([
            'title' => $data['title'],
            'course_id' => $data['course_id'],
            'duration_minutes' => $data['duration'],
            'pass_percentage' => $data['passingScore'],
            'available_from' => $data['available_from'] ?? null,
            'available_until' => $data['available_until'] ?? null,
            'result_release_mode' => $mode,
            'results_released' => $mode === 'immediate' ? true : ($mode === 'manual' ? $quiz->results_released : false),
            'questions_per_page' => $data['questions_per_page'] ?? 1,
            'exam_mode' => $data['exam_mode'] ?? $quiz->exam_mode,
            'allow_backtracking' => $data['allow_backtracking'] ?? $quiz->allow_backtracking,
            'show_answers' => $data['show_answers'] ?? $quiz->show_answers,
            'section_rules' => $data['section_rules'] ?? $quiz->section_rules,
        ]);

        return response()->json($quiz);
    }

    // دالة تسمح للمحاضر بإظهار أو إخفاء الإجابات الصحيحة عن الطلاب في أي وقت
    public function toggleQuizAnswers($id)
    {
        $quiz = Quiz::findOrFail($id);
        $newState = ! (bool) $quiz->show_answers;
        $quiz->update(['show_answers' => $newState]);

        return response()->json([
            'message' => $newState ? 'Answers are now visible to students.' : 'Answers are now hidden from students.',
            'show_answers' => $newState,
        ]);
    }

    // دالة حفظ الأسئلة المختارة وترتيبها ودرجاتها ونشر الاختبار أو حفظه كمسودة
    public function syncQuizQuestions(Request $request, $id)
    {
        $quiz = Quiz::findOrFail($id);

        $data = $request->validate([
            'items' => 'nullable|array',
            'items.*.question_id' => 'required|integer',
            'items.*.marks' => 'required|numeric|min:0.5',
            'is_published' => 'nullable|boolean',
            'section_rules' => 'nullable|string',
        ]);

        DB::table('quiz_questions')->where('quiz_id', $quiz->quiz_id)->delete();

        foreach (($data['items'] ?? []) as $index => $item) {
            DB::table('quiz_questions')->insert([
                'quiz_id' => $quiz->quiz_id,
                'question_id' => $item['question_id'],
                'marks' => $item['marks'],
                'sort_order' => $index + 1,
            ]);
        }

        $updates = [];
        if (isset($data['is_published'])) {
            $updates['is_published'] = (bool) $data['is_published'];
        }
        if (array_key_exists('section_rules', $data)) {
            $updates['section_rules'] = $data['section_rules'];
        }
        if (! empty($updates)) {
            $quiz->update($updates);
        }

        return response()->json(['message' => 'Quiz saved successfully.']);
    }

        // دالة تسمح للمحاضر بإظهار أو إخفاء نتائج الاختبار عن الطلاب في أي وقت
    public function releaseQuizResults($id)
    {
        $quiz = Quiz::findOrFail($id);
        $newState = ! (bool) $quiz->results_released;

        $quiz->update([
            'result_release_mode' => 'manual',
            'results_released' => $newState,
        ]);

        return response()->json([
            'message' => $newState ? 'Results are now visible to students.' : 'Results are now hidden from students.',
            'results_released' => $newState,
        ]);
    }


    // دالة حذف اختبار بالكامل
    public function deleteQuiz($id)
    {
        Quiz::findOrFail($id)->delete();

        return response()->json(['message' => 'Quiz deleted']);
    }

    // دالة تجلب بنك الأسئلة مع اسم المساق والمحاضرة
    public function getQuestions(Request $request)
    {
        $query = Question::with('options')->orderByDesc('question_id');
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $coursesMap = DB::table('courses')->pluck('course_name', 'course_id');

        $questions = $query->get()->map(function ($q) use ($coursesMap) {
            $q->course_name = $coursesMap[$q->course_id] ?? 'Course';

            return $q;
        });

        return response()->json($questions);
    }

    // دالة إضافة سؤال جديد لبنك الأسئلة (اختيار من متعدد، صح/خطأ، أو مقالي)
    public function storeQuestion(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required|integer',
            'lecture_name' => 'nullable|string|max:120',
            'question_type' => 'required|in:mcq,true_false,essay',
            'text' => 'required|string',
            'marks' => 'nullable|numeric|min:0.5',
            'options' => 'nullable|array',
            'correctAnswer' => 'nullable|string',
        ]);

        $question = Question::create([
            'course_id' => $data['course_id'],
            'lecture_name' => trim((string) ($data['lecture_name'] ?? 'Lecture 1')) ?: 'Lecture 1',
            'question_type' => $data['question_type'],
            'question_text' => $data['text'],
            'marks' => $data['marks'] ?? 1.00,
        ]);

        if ($data['question_type'] === 'mcq') {
            $letters = ['A', 'B', 'C', 'D'];
            foreach (($data['options'] ?? []) as $index => $optText) {
                if (trim((string) $optText) === '') {
                    continue;
                }
                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => $optText,
                    'is_correct' => (($letters[$index] ?? '') === strtoupper((string) $data['correctAnswer'])),
                ]);
            }
        } elseif ($data['question_type'] === 'true_false') {
            $ans = strtolower((string) $data['correctAnswer']);
            QuestionOption::create(['question_id' => $question->question_id, 'option_text' => 'True', 'is_correct' => ($ans === 'true' || $ans === 'a')]);
            QuestionOption::create(['question_id' => $question->question_id, 'option_text' => 'False', 'is_correct' => ($ans === 'false' || $ans === 'b')]);
        }

        return response()->json($question->load('options'), 201);
    }

        // دالة تعديل بيانات سؤال موجود في بنك الأسئلة (المساق، المحاضرة، النص، أو الخيارات)
    public function updateQuestion(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $data = $request->validate([
            'course_id' => 'required|integer',
            'lecture_name' => 'nullable|string|max:120',
            'question_type' => 'required|in:mcq,true_false,essay',
            'text' => 'required|string',
            'marks' => 'nullable|numeric|min:0.5',
            'options' => 'nullable|array',
            'correctAnswer' => 'nullable|string',
        ]);

        $question->update([
            'course_id' => $data['course_id'],
            'lecture_name' => trim((string) ($data['lecture_name'] ?? 'Lecture 1')) ?: 'Lecture 1',
            'question_type' => $data['question_type'],
            'question_text' => $data['text'],
            'marks' => $data['marks'] ?? 1.00,
        ]);

        QuestionOption::where('question_id', $question->question_id)->delete();

        if ($data['question_type'] === 'mcq') {
            $letters = ['A', 'B', 'C', 'D'];
            foreach (($data['options'] ?? []) as $index => $optText) {
                if (trim((string) $optText) === '') {
                    continue;
                }
                QuestionOption::create([
                    'question_id' => $question->question_id,
                    'option_text' => $optText,
                    'is_correct' => (($letters[$index] ?? '') === strtoupper((string) $data['correctAnswer'])),
                ]);
            }
        } elseif ($data['question_type'] === 'true_false') {
            $ans = strtolower((string) $data['correctAnswer']);
            QuestionOption::create(['question_id' => $question->question_id, 'option_text' => 'True', 'is_correct' => ($ans === 'true' || $ans === 'a')]);
            QuestionOption::create(['question_id' => $question->question_id, 'option_text' => 'False', 'is_correct' => ($ans === 'false' || $ans === 'b')]);
        }

        return response()->json($question->load('options'));
    }

    // دالة حذف سؤال من بنك الأسئلة
    public function deleteQuestion($id)
    {
        Question::findOrFail($id)->delete();

        return response()->json(['message' => 'Question deleted']);
    }

    // دالة تجلب جميع نتائج الطلاب للمحاضر
    public function getResults()
    {
        $attempts = QuizAttempt::whereNotNull('ended_at')
            ->with(['user', 'quiz'])
            ->orderByDesc('attempt_id')
            ->get()
            ->map(function ($attempt) {
                $attempt->behavior = DB::table('behavior_logs')->where('attempt_id', $attempt->attempt_id)->first();
                $alerts = DB::table('suspicious_alerts')
                    ->where('attempt_id', $attempt->attempt_id)
                    ->pluck('alert_type')
                    ->unique()
                    ->implode(' + ');
                $attempt->latest_alert = $alerts ? (object) ['alert_type' => $alerts] : null;

                return $attempt;
            });

        return response()->json($attempts);
    }

// دالة عرض ورقة إجابات الطالب بالتفصيل للمحاضر والأدمن
public function reviewAttempt($attemptId)
{
    $attempt = QuizAttempt::with(['user', 'quiz', 'answers'])->findOrFail($attemptId);
    $questionIds = $attempt->answers->pluck('question_id')->unique()->values();
    $pivotRows = DB::table('quiz_questions')->where('quiz_id', $attempt->quiz_id)->get()->keyBy('question_id');
    $questions = Question::whereIn('question_id', $questionIds)->with('options')->get()->map(function ($q) use ($pivotRows) {
        $q->marks = $pivotRows[$q->question_id]->marks ?? $q->marks ?? 1;

        return $q;
    });
    $attempt->setRelation('questions', $questions);

    return response()->json($attempt);
}

// دالة حفظ درجات الأسئلة المقالية بعد تصحيح المحاضر يدوياً وإعادة حساب النسبة
public function gradeEssayAnswers(Request $request, $attemptId)
{
    $attempt = QuizAttempt::findOrFail($attemptId);
    $grades = $request->input('grades', []);
    foreach ($grades as $questionId => $marks) {
        $ans = StudentAnswer::where('attempt_id', $attempt->attempt_id)->where('question_id', $questionId)->first();
        if ($ans) {
            $ans->update(['awarded_marks' => (float) $marks, 'is_correct' => ((float) $marks) > 0]);
        }
    }
    $qIds = StudentAnswer::where('attempt_id', $attempt->attempt_id)->pluck('question_id');
    $pivot = DB::table('quiz_questions')->where('quiz_id', $attempt->quiz_id)->get()->keyBy('question_id');
    $totalPossible = Question::whereIn('question_id', $qIds)->get()->sum(fn ($q) => $pivot[$q->question_id]->marks ?? $q->marks ?? 1);
    $totalEarned = StudentAnswer::where('attempt_id', $attempt->attempt_id)->sum('awarded_marks');
    $percentage = $totalPossible > 0 ? round(($totalEarned / $totalPossible) * 100, 2) : 0;
    $attempt->update(['score' => $percentage]);

    return response()->json(['message' => 'Grades updated', 'new_score' => $percentage]);
}
}
