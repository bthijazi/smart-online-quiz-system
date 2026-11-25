<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    // هذه الدالة تقوم بإدخال البيانات الأساسية للمشروع عند تشغيل db:seed
    public function run(): void
    {
        // 1. إدخال الأدوار الأساسية
        $adminRoleId = DB::table('roles')->insertGetId(['role_name' => 'admin']);
        $instRoleId = DB::table('roles')->insertGetId(['role_name' => 'instructor']);
        $studRoleId = DB::table('roles')->insertGetId(['role_name' => 'student']);

        // 2. إنشاء حساب الأدمن والمحاضر والطالب
        DB::table('users')->insert([
            'role_id' => $adminRoleId,
            'username' => 'Administrator',
            'email' => 'admin@smartquiz.com',
            'password_hash' => Hash::make('Admin123'),
        ]);

        $instructorId = DB::table('users')->insertGetId([
            'role_id' => $instRoleId,
            'username' => 'Instructor',
            'email' => 'instructor@smartquiz.com',
            'password_hash' => Hash::make('Instructor123'),
        ]);

        DB::table('users')->insert([
            'role_id' => $studRoleId,
            'username' => 'Student',
            'email' => 'student@smartquiz.com',
            'password_hash' => Hash::make('Student123'),
        ]);

        // 3. إدخال المساقات الدراسية الأربعة
        $courses = [
            'Introduction to Programming',
            'Database Management Systems',
            'Artificial Intelligence',
            'Web Engineering',
        ];

        foreach ($courses as $courseName) {
            DB::table('courses')->insert([
                'course_name' => $courseName,
                'created_by' => $instructorId,
            ]);
        }
    }
}
