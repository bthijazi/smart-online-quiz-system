<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // دالة التحقق من البريد الإلكتروني وكلمة المرور وإصدار رمز الدخول (Token)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('role')
            ->where('email', strtolower(trim($request->email)))
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $token = $user->createToken('soqs-auth-token')->plainTextToken;

        DB::table('activity_logs')->insert([
            'user_id' => $user->user_id,
            'action' => 'User logged in',
            'timestamp' => now(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->user_id,
                'name' => $user->username,
                'email' => $user->email,
                'role' => strtolower($user->role->role_name ?? 'student'),
            ],
        ]);
    }

    // دالة إنشاء حساب طالب جديد في النظام
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:users,username',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $studentRole = Role::where('role_name', 'student')->firstOrFail();

        $user = User::create([
            'role_id' => $studentRole->role_id,
            'username' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password_hash' => Hash::make($request->password),
            'created_at' => now(),
        ]);

        $token = $user->createToken('soqs-auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->user_id,
                'name' => $user->username,
                'email' => $user->email,
                'role' => 'student',
            ],
        ], 201);
    }

    // دالة تحديث اسم المستخدم في صفحة الملف الشخصي
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string|max:100|unique:users,username,'.$user->user_id.',user_id',
        ]);

        $user->update(['username' => trim($request->name)]);

        return response()->json([
            'id' => $user->user_id,
            'name' => $user->username,
            'email' => $user->email,
            'role' => strtolower($user->role->role_name ?? 'student'),
        ]);
    }

    // دالة إلغاء رمز المصادقة عند تسجيل الخروج
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
