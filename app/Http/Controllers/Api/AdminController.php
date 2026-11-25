<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // دالة تحسب الإحصائيات العامة للنظام في لوحة الأدمن
    public function dashboardStats()
    {
        $attempts = QuizAttempt::whereNotNull('ended_at')->get();

        return response()->json([
            'totalUsers' => User::count(),
            'totalStudents' => User::whereHas('role', fn ($q) => $q->where('role_name', 'student'))->count(),
            'totalInstructors' => User::whereHas('role', fn ($q) => $q->where('role_name', 'instructor'))->count(),
            'totalQuizzes' => Quiz::count(),
            'totalSubmissions' => $attempts->count(),
            'averageScore' => $attempts->count() ? round($attempts->avg('score'), 1) : 0,
        ]);
    }

    // دالة تجلب قائمة جميع المستخدمين مع أدوارهم
    public function getUsers()
    {
        $users = User::with('role')->orderByDesc('user_id')->get()->map(fn ($u) => [
            'id' => $u->user_id,
            'name' => $u->username,
            'email' => $u->email,
            'role' => strtolower($u->role->role_name ?? 'student'),
            'created_at' => $u->created_at,
        ]);

        return response()->json($users);
    }

    // دالة تتيح للأدمن إضافة حساب طالب أو محاضر جديد
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:users,username',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,instructor,admin',
        ]);

        $role = Role::where('role_name', $request->role)->firstOrFail();

        $user = User::create([
            'role_id' => $role->role_id,
            'username' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password_hash' => Hash::make($request->password),
            'created_at' => now(),
        ]);

        return response()->json([
            'id' => $user->user_id,
            'name' => $user->username,
            'email' => $user->email,
            'role' => $request->role,
        ], 201);
    }

    // دالة تعديل بيانات مستخدم موجود
    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'name' => 'required|string|max:100|unique:users,username,'.$userId.',user_id',
            'email' => 'required|email|max:150|unique:users,email,'.$userId.',user_id',
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:student,instructor,admin',
        ]);

        $role = Role::where('role_name', $request->role)->firstOrFail();
        $user->username = trim($request->name);
        $user->email = strtolower(trim($request->email));
        $user->role_id = $role->role_id;

        if ($request->filled('password')) {
            $user->password_hash = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['message' => 'User updated successfully']);
    }

    // دالة حذف مستخدم مع جميع سجلاته المرتبطة
    public function deleteUser(Request $request, $userId)
    {
        if ((int) $request->user()->user_id === (int) $userId) {
            return response()->json(['message' => 'Cannot delete your own admin account.'], 403);
        }

        User::findOrFail($userId)->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // دالة تجلب جميع الاختبارات في النظام مع عدد أسئلتها وحالة نشرها
    public function getAllQuizzes()
    {
        $quizzes = Quiz::with('course')->orderByDesc('quiz_id')->get()->map(function ($quiz) {
            $quiz->questions_count = DB::table('quiz_questions')->where('quiz_id', $quiz->quiz_id)->count();
            $quiz->status = $quiz->is_published ? 'Published' : 'Draft';

            return $quiz;
        });

        return response()->json($quizzes);
    }

    // دالة حذف أي اختبار من لوحة الأدمن
    public function deleteQuiz($quizId)
    {
        Quiz::findOrFail($quizId)->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    // دالة تجلب جميع محاولات الطلاب مع تنبيهات المراقبة الذكية
    public function getAllResults()
    {
        $attempts = QuizAttempt::with(['quiz', 'user'])
            ->whereNotNull('ended_at')
            ->orderByDesc('ended_at')
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

    // دالة تسجيل نسخة احتياطية لقاعدة البيانات في السجلات
       // دالة إنشاء نسخة احتياطية حقيقية لقاعدة البيانات وتسجيلها في السجلات
    public function createBackup(Request $request)
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $backupName = 'soqs_proj_backup_'.date('Y_m_d_His').'.sql';
        $fullPath = $dir.DIRECTORY_SEPARATOR.$backupName;

        // توليد محتوى SQL للجداول الأساسية
        $tables = ['roles', 'users', 'courses', 'quizzes', 'questions', 'question_options', 'quiz_questions', 'quiz_attempts', 'student_answers'];
        $sqlDump = "-- Smart Online Quiz System Backup\n-- Date: ".now()."\n\n";

        foreach ($tables as $table) {
            $rows = DB::table($table)->get();
            $sqlDump .= "-- Table: {$table} (".$rows->count()." rows)\n";
            foreach ($rows as $row) {
                $cols = implode(', ', array_keys((array) $row));
                $vals = implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : "'".addslashes((string) $v)."'", (array) $row));
                $sqlDump .= "INSERT INTO {$table} ({$cols}) VALUES ({$vals});\n";
            }
            $sqlDump .= "\n";
        }

        file_put_contents($fullPath, $sqlDump);

        DB::table('backups')->insert([
            'user_id' => $request->user()->user_id,
            'backup_name' => $backupName,
            'backup_date' => now(),
            'status' => 'Completed',
        ]);

        return response()->json([
            'message' => 'Real SQL Backup created successfully!',
            'backup_name' => $backupName,
            'sql_content' => $sqlDump,
        ]);
    }
}
