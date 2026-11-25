<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ExamController extends Controller
{
    // دالة مساعدة تتحقق هل يحق للطالب رؤية درجته وإجاباته الآن أم لا
    private function isResultVisible(Quiz $quiz): bool
    {
        if ($quiz->result_release_mode === 'immediate') {
            return true;
        }
        if ($quiz->result_release_mode === 'after_end') {
            return $quiz->available_until ? now()->greaterThanOrEqualTo(Carbon::parse($quiz->available_until)) : true;
        }

        return (bool) $quiz->results_released;
    }

    // دالة تجلب جميع الاختبارات المنشورة للطالب مع حالة التوقيت والعد التنازلي
    public function availableQuizzes(Request $request)
    {
        $now = now();
        $userId = $request->user()->user_id;
        $completedQuizIds = QuizAttempt::where('user_id', $userId)
            ->whereNotNull('ended_at')
            ->pluck('quiz_id')
            ->toArray();

        $courses = DB::table('courses')->get()->keyBy('course_id');
        $usersMap = DB::table('users')->pluck('username', 'user_id');

        $quizzes = Quiz::where('is_published', true)->orderByDesc('quiz_id')->get()->map(function ($quiz) use ($now, $courses, $usersMap, $completedQuizIds) {
            $pivot = DB::table('quiz_questions')->where('quiz_id', $quiz->quiz_id)->get();
            $course = $courses[$quiz->course_id] ?? null;

            $qCount = $pivot->count();
            $tMarks = $pivot->sum('marks');

            if ($quiz->exam_mode === 'sectioned' && $quiz->section_rules) {
                $rawRules = json_decode($quiz->section_rules, true) ?: [];
                $rules = $rawRules['sections'] ?? $rawRules;
                $qCount = collect($rules)->sum('count');
                $tMarks = collect($rules)->sum(fn ($r) => ($r['count'] ?? 0) * ($r['marks_each'] ?? 1));
            } elseif ($quiz->exam_mode === 'random_pool' && $quiz->section_rules) {
                $rule = json_decode($quiz->section_rules, true) ?: [];
                $qCount = $rule['random_count'] ?? $qCount;
                $tMarks = $qCount * ($rule['marks_each'] ?? 1);
            }

            $quiz->questions_count = $qCount;
            $quiz->total_marks = $tMarks;
            $quiz->course_name = $course->course_name ?? 'Course';
            $quiz->instructor_name = ($course && isset($usersMap[$course->created_by])) ? $usersMap[$course->created_by] : 'Instructor';

            $from = $quiz->available_from ? Carbon::parse($quiz->available_from) : null;
            $until = $quiz->available_until ? Carbon::parse($quiz->available_until) : null;

            if (in_array($quiz->quiz_id, $completedQuizIds)) {
                $quiz->availability_status = 'completed';
                $quiz->starts_in_seconds = 0;
            } elseif ($from && $now->lt($from)) {
                $quiz->availability_status = 'upcoming';
                $quiz->starts_in_seconds = $now->diffInSeconds($from);
            } elseif ($until && $now->gt($until)) {
                $quiz->availability_status = 'ended';
                $quiz->starts_in_seconds = 0;
            } else {
                $quiz->availability_status = 'open';
                $quiz->starts_in_seconds = 0;
            }

            return $quiz;
        })->filter(fn ($q) => $q->questions_count > 0)->values();

        return response()->json($quizzes);
    }

    // دالة بدء جلسة الاختبار وتوليد الأسئلة حسب النمط وترتيب الأنواع
    public function startExam(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if (! $quiz->is_published) {
            return response()->json(['message' => 'This quiz is not published yet.'], 403);
        }

        $alreadySubmitted = QuizAttempt::where('user_id', $request->user()->user_id)
            ->where('quiz_id', $quiz->quiz_id)
            ->whereNotNull('ended_at')
            ->exists();

        if ($alreadySubmitted) {
            return response()->json(['message' => 'You have already completed this quiz. Only one attempt is allowed.'], 403);
        }

        $now = now();
        if ($quiz->available_from && $now->lt(Carbon::parse($quiz->available_from))) {
            return response()->json(['message' => 'This exam has not started yet.'], 403);
        }
        if ($quiz->available_until && $now->gt(Carbon::parse($quiz->available_until))) {
            return response()->json(['message' => 'The deadline for this exam has already passed.'], 403);
        }

        $questions = collect();

        if ($quiz->exam_mode === 'sectioned' && $quiz->section_rules) {
            $rawSections = json_decode($quiz->section_rules, true) ?: [];
            $sections = $rawSections['sections'] ?? $rawSections;
            foreach ($sections as $sec) {
                if (! is_array($sec) || empty($sec['lecture'])) {
                    continue;
                }
                $lecQuestions = Question::where('course_id', $quiz->course_id)
                    ->where('lecture_name', $sec['lecture'])
                    ->with(['options:option_id,question_id,option_text'])
                    ->inRandomOrder()
                    ->limit((int) ($sec['count'] ?? 1))
                    ->get()
                    ->map(function ($q) use ($sec) {
                        $q->marks = (float) ($sec['marks_each'] ?? 1);
                        $q->section_title = $sec['lecture'];

                        return $q;
                    });
                $questions = $questions->merge($lecQuestions);
            }
        } elseif ($quiz->exam_mode === 'random_pool' && $quiz->section_rules) {
            $rule = json_decode($quiz->section_rules, true) ?: [];
            $pivotRows = DB::table('quiz_questions')->where('quiz_id', $quiz->quiz_id)->get()->keyBy('question_id');
            $questions = Question::whereIn('question_id', $pivotRows->keys())
                ->with(['options:option_id,question_id,option_text'])
                ->inRandomOrder()
                ->limit((int) ($rule['random_count'] ?? $pivotRows->count()))
                ->get()
                ->map(function ($q) use ($pivotRows, $rule) {
                    $q->marks = (float) ($rule['marks_each'] ?? ($pivotRows[$q->question_id]->marks ?? 1));

                    return $q;
                });
        } else {
            $pivotRows = DB::table('quiz_questions')->where('quiz_id', $quiz->quiz_id)->orderBy('sort_order')->get()->keyBy('question_id');
            $questions = Question::whereIn('question_id', $pivotRows->keys())
                ->with(['options:option_id,question_id,option_text'])
                ->get()
                ->sortBy(fn ($q) => $pivotRows[$q->question_id]->sort_order ?? 999)
                ->values()
                ->map(function ($q) use ($pivotRows) {
                    $q->marks = (float) ($pivotRows[$q->question_id]->marks ?? $q->marks ?? 1);

                    return $q;
                });
        }

        // ترتيب أنواع الأسئلة حسب اختيار المحاضر مع خلط الأسئلة عشوائياً داخل كل نوع
        $rulesData = $quiz->section_rules ? (json_decode($quiz->section_rules, true) ?: []) : [];
        $typeOrderStr = $rulesData['type_order'] ?? 'mixed';

        if ($typeOrderStr !== 'mixed') {
            $orderList = explode(',', $typeOrderStr);
            $grouped = $questions->shuffle()->groupBy('question_type');
            $sortedQuestions = collect();

            foreach ($orderList as $qType) {
                if ($grouped->has($qType)) {
                    $sortedQuestions = $sortedQuestions->merge($grouped->get($qType));
                }
            }
            $questions = $sortedQuestions->values();
        }

        $quiz->setRelation('questions', $questions);
        $quiz->total_marks = $questions->sum('marks');

        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->user_id,
            'quiz_id' => $quiz->quiz_id,
            'started_at' => now(),
            'is_flagged' => false,
        ]);

        DB::table('behavior_logs')->insert([
            'attempt_id' => $attempt->attempt_id,
            'tab_switches' => 0,
            'mouse_leaves' => 0,
            'gaze_deviation_count' => 0,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'attempt_id' => $attempt->attempt_id,
            'quiz' => $quiz,
        ]);
    }

    // دالة تسجيل مؤشرات سلوك الطالب أثناء الامتحان
        public function logBehavior(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);

        $tabs = (int) $request->input('tab_switches', 0);
        $mouse = (int) $request->input('mouse_leaves', 0);
        $copyAttempts = (int) $request->input('copy_paste_attempts', 0);

        DB::table('behavior_logs')->updateOrInsert(
            ['attempt_id' => $attempt->attempt_id],
            [
                'tab_switches' => $tabs,
                'mouse_leaves' => $mouse,
                'gaze_deviation_count' => (int) $request->input('gaze_deviation_count', 0),
                'recorded_at' => now(),
            ]
        );

        if ($copyAttempts >= 1) {
            $attempt->update(['is_flagged' => true]);
            DB::table('suspicious_alerts')->updateOrInsert(
                ['attempt_id' => $attempt->attempt_id, 'alert_type' => 'Unauthorized Copy/Paste Attempt'],
                ['confidence_score' => 0.95, 'created_at' => now()]
            );
        } elseif ($tabs >= 2) {
            $attempt->update(['is_flagged' => true]);
            DB::table('suspicious_alerts')->updateOrInsert(
                ['attempt_id' => $attempt->attempt_id, 'alert_type' => 'Excessive Tab/Focus Switching'],
                ['confidence_score' => 0.90, 'created_at' => now()]
            );
        } elseif ($mouse >= 5) {
            $attempt->update(['is_flagged' => true]);
            DB::table('suspicious_alerts')->updateOrInsert(
                ['attempt_id' => $attempt->attempt_id, 'alert_type' => 'Repeated Cursor Exit from Exam Area'],
                ['confidence_score' => 0.85, 'created_at' => now()]
            );
        }

        return response()->json(['status' => 'logged']);
    }


    // دالة نبض الاتصال لكشف انقطاع الإنترنت المتعمد
    public function heartbeat(Request $request, $attemptId)
    {
        $attempt = QuizAttempt::findOrFail($attemptId);

        if ($request->boolean('reconnected') && $request->boolean('is_intentional')) {
            $attempt->update(['is_flagged' => true]);
            DB::table('suspicious_alerts')->insert([
                'attempt_id' => $attempt->attempt_id,
                'alert_type' => 'Intentional Network Disconnect',
                'confidence_score' => 0.92,
                'created_at' => now(),
            ]);
        }

        return response()->json(['status' => 'alive']);
    }

// دالة تسليم الاختبار وتصحيح الإجابات واستدعاء نموذج الذكاء الاصطناعي
public function submitExam(Request $request, $attemptId)
{
    $attempt = QuizAttempt::with('quiz')->findOrFail($attemptId);
    $answersPayload = $request->input('answers', []);
    $marksMap = $request->input('question_marks', []);
    $questionIds = array_map('intval', array_keys($marksMap));
    if (empty($questionIds)) {
        $questionIds = DB::table('quiz_questions')->where('quiz_id', $attempt->quiz_id)->pluck('question_id')->toArray();
    }
    $questions = Question::whereIn('question_id', $questionIds)->with('options')->get();
    $totalPossibleMarks = 0;
    $earnedMarks = 0;
    $correctQuestionsCount = 0;
    foreach ($questions as $question) {
        $qMarks = (float) ($marksMap[$question->question_id] ?? $question->marks ?? 1);
        $totalPossibleMarks += $qMarks;
        $studentVal = $answersPayload[$question->question_id] ?? null;
        if ($question->question_type === 'essay') {
            $textAns = is_string($studentVal) ? trim($studentVal) : '';
            StudentAnswer::create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $question->question_id,
                'selected_option_id' => null,
                'essay_answer' => $textAns !== '' ? $textAns : null,
                'is_correct' => false,
                'awarded_marks' => 0,
            ]);
        } else {
            $selectedOptionId = is_numeric($studentVal) ? (int) $studentVal : null;
            $correctOption = $question->options->firstWhere('is_correct', true);
            $isCorrect = $selectedOptionId && $correctOption && ($correctOption->option_id === $selectedOptionId);
            if ($isCorrect) {
                $earnedMarks += $qMarks;
                $correctQuestionsCount++;
            }
            StudentAnswer::create([
                'attempt_id' => $attempt->attempt_id,
                'question_id' => $question->question_id,
                'selected_option_id' => $selectedOptionId,
                'essay_answer' => null,
                'is_correct' => $isCorrect,
                'awarded_marks' => $isCorrect ? $qMarks : 0,
            ]);
        }
    }
    $scorePercentage = $totalPossibleMarks > 0 ? round(($earnedMarks / $totalPossibleMarks) * 100, 2) : 0;
    $durationSeconds = max(1, now()->diffInSeconds($attempt->started_at));
    $metric = DB::table('behavior_logs')->where('attempt_id', $attempt->attempt_id)->first();
    $aiResult = $this->runAiDecisionTree($metric, $durationSeconds);
    $isFlagged = $attempt->is_flagged || ($aiResult['is_cheating'] ?? false);
    $attempt->update([
        'ended_at' => now(),
        'score' => $scorePercentage,
        'is_flagged' => $isFlagged,
    ]);
    if ($aiResult['is_cheating'] ?? false) {
        DB::table('suspicious_alerts')->insert([
            'attempt_id' => $attempt->attempt_id,
            'alert_type' => $aiResult['reason'] ?? 'AI Decision Tree Anomaly',
            'confidence_score' => $aiResult['confidence'] ?? 0.85,
            'created_at' => now(),
        ]);
    }
    $visible = $this->isResultVisible($attempt->quiz);

    return response()->json([
        'answersVisible' => (bool) $attempt->quiz->show_answers,
        'attempt_id' => $attempt->attempt_id,
        'quizTitle' => $attempt->quiz->title,
        'resultVisible' => $visible,
        'releaseMode' => $attempt->quiz->result_release_mode,
        'totalQuestions' => $questions->count(),
        'correctAnswers' => $correctQuestionsCount,
        'earnedMarks' => $visible ? $earnedMarks : null,
        'totalMarks' => $totalPossibleMarks,
        'percentage' => $visible ? $scorePercentage : null,
        'isFlagged' => $isFlagged,
    ]);
}

// دالة الاتصال بخدمة بايثون (Decision Tree) لتحليل سلوك الطالب
private function runAiDecisionTree($metric, $durationSeconds)
{
    try {
        $response = Http::timeout(3)->post('127.0.0', [
            'tab_switches' => $metric->tab_switches ?? 0,
            'mouse_leaves' => $metric->mouse_leaves ?? 0,
            'gaze_deviations' => $metric->gaze_deviation_count ?? 0,
            'duration_seconds' => $durationSeconds,
        ]);
        if ($response->successful()) {
            return $response->json();
        }
    } catch (\Exception $e) {
        if (($metric->tab_switches ?? 0) >= 3 || ($metric->mouse_leaves ?? 0) >= 5) {
            return ['is_cheating' => true, 'confidence' => 0.88, 'reason' => 'Excessive Tab/Focus Switching'];
        }
    }

    return ['is_cheating' => false, 'confidence' => 0.12];
}

// دالة تجلب نتائج الطالب السابقة
public function myResults(Request $request)
{
    $attempts = QuizAttempt::where('user_id', $request->user()->user_id)
    ->whereNotNull('ended_at')
    ->with('quiz')
    ->orderByDesc('attempt_id')
    ->get()
    ->map(function ($attempt) {
        $visible = $attempt->quiz ? $this->isResultVisible($attempt->quiz) : true;
        $attempt->answers_visible = $visible && (bool) ($attempt->quiz->show_answers ?? true);
        $attempt->result_visible = $visible;
        if (! $visible) {
            $attempt->score = null;
        }

        return $attempt;
    });

    return response()->json($attempts);
}

// دالة عرض ورقة إجابات الطالب لنفسه (إذا سمح المحاضر بإظهار النتيجة)
public function reviewAttempt(Request $request, $attemptId)
{
    $attempt = QuizAttempt::where('user_id', $request->user()->user_id)
    ->with(['quiz', 'answers'])
    ->findOrFail($attemptId);
    if ($attempt->quiz && ! $this->isResultVisible($attempt->quiz)) {
        return response()->json(['message' => 'Results are hidden by the instructor until the scheduled release time.'], 403);
    }
    if ($attempt->quiz && ! $attempt->quiz->show_answers) {
        return response()->json(['message' => 'Answer review is disabled by the instructor for this quiz.'], 403);
    }

    $questionIds = $attempt->answers->pluck('question_id')->unique()->values();
    $pivotRows = DB::table('quiz_questions')->where('quiz_id', $attempt->quiz_id)->get()->keyBy('question_id');
    $questions = Question::whereIn('question_id', $questionIds)->with('options')->get()->map(function ($q) use ($pivotRows) {
        $q->marks = $pivotRows[$q->question_id]->marks ?? $q->marks ?? 1;

        return $q;
    });
    $attempt->setRelation('questions', $questions);

    return response()->json($attempt);
}
}
