<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // هذه الدالة تقوم بإنشاء جميع جداول النظام بالترتيب الصحيح للمفاتيح الأجنبية
    public function up(): void
    {
        // 1. جدول الأدوار (Admin, Instructor, Student)
        Schema::create('roles', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_name', 50)->unique();
        });

        // 2. جدول المستخدمين
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->unsignedBigInteger('role_id');
            $table->string('username', 100)->unique();
            $table->string('email', 150)->unique();
            $table->string('password_hash');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('role_id')->references('role_id')->on('roles')->onDelete('cascade');
        });

        // 3. جدول المساقات الدراسية
        Schema::create('courses', function (Blueprint $table) {
            $table->id('course_id');
            $table->string('course_name', 150);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('cascade');
        });

        // 4. جدول الاختبارات (يدعم الأنماط الثلاثة، الجدولة، وسياسة إظهار النتيجة)
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id('quiz_id');
            $table->unsignedBigInteger('course_id');
            $table->string('title', 150);
            $table->integer('duration_minutes')->default(30);
            $table->decimal('pass_percentage', 5, 2)->default(50.00);
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('result_release_mode', 30)->default('immediate'); // immediate, after_end, manual
            $table->boolean('results_released')->default(true);
            $table->integer('questions_per_page')->default(1);
            $table->string('exam_mode', 30)->default('fixed'); // fixed, random_pool, sectioned
            $table->boolean('allow_backtracking')->default(true);
            $table->text('section_rules')->nullable();

            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
        });

        // 5. جدول بنك الأسئلة (مربوط بالمساق، المحاضرة، نوع السؤال، ودرجته)
        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->unsignedBigInteger('course_id');
            $table->string('lecture_name', 120)->default('Lecture 1');
            $table->string('question_type', 30)->default('mcq'); // mcq, true_false, essay
            $table->text('question_text');
            $table->decimal('marks', 5, 2)->default(1.00);

            $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
        });

        // 6. جدول خيارات الإجابة (للأسئلة الموضوعية: اختيار من متعدد وصح/خطأ)
        Schema::create('question_options', function (Blueprint $table) {
            $table->id('option_id');
            $table->unsignedBigInteger('question_id');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);

            $table->foreign('question_id')->references('question_id')->on('questions')->onDelete('cascade');
        });

        // 7. جدول ربط الأسئلة بالاختبار مع حفظ الترتيب والدرجة المخصصة
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->unsignedBigInteger('question_id');
            $table->decimal('marks', 5, 2)->default(1.00);
            $table->integer('sort_order')->default(1);

            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes')->onDelete('cascade');
            $table->foreign('question_id')->references('question_id')->on('questions')->onDelete('cascade');
        });

        // 8. جدول محاولات الطلاب
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id('attempt_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('quiz_id');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('is_flagged')->default(false);

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes')->onDelete('cascade');
        });

        // 9. جدول إجابات الطلاب (يدعم الإجابة الموضوعية والمقالية والدرجة المكتسبة)
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id('answer_id');
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('selected_option_id')->nullable();
            $table->text('essay_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->decimal('awarded_marks', 5, 2)->default(0);

            $table->foreign('attempt_id')->references('attempt_id')->on('quiz_attempts')->onDelete('cascade');
            $table->foreign('question_id')->references('question_id')->on('questions')->onDelete('cascade');
        });

        // 10. جداول المراقبة الذكية والنشاط والنسخ الاحتياطي
        Schema::create('behavior_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('attempt_id');
            $table->integer('tab_switches')->default(0);
            $table->integer('mouse_leaves')->default(0);
            $table->integer('gaze_deviation_count')->default(0);
            $table->timestamp('recorded_at')->useCurrent();
        });

        Schema::create('suspicious_alerts', function (Blueprint $table) {
            $table->id('alert_id');
            $table->unsignedBigInteger('attempt_id');
            $table->string('alert_type', 200);
            $table->decimal('confidence_score', 4, 2)->default(0.85);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('connection_logs', function (Blueprint $table) {
            $table->id('conn_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attempt_id')->nullable();
            $table->string('event_type', 100);
            $table->timestamp('event_time')->useCurrent();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('activity_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action', 255);
            $table->timestamp('timestamp')->useCurrent();
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id('backup_id');
            $table->unsignedBigInteger('user_id');
            $table->string('backup_name', 150);
            $table->timestamp('backup_date')->useCurrent();
            $table->string('status', 50)->default('Completed');
        });
    }

    // هذه الدالة تحذف الجداول عند التراجع عن الهجرة
    public function down(): void
    {
        Schema::dropIfExists('backups');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('connection_logs');
        Schema::dropIfExists('suspicious_alerts');
        Schema::dropIfExists('behavior_logs');
        Schema::dropIfExists('student_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
