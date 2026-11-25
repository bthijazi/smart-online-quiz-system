<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\InstructorController;
use Illuminate\Support\Facades\Route;

// مسارات عامة (تسجيل الدخول وإنشاء حساب طالب)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// مسارات محمية تتطلب رمز مصادقة (Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // إدارة المساقات
    Route::get('/courses', [InstructorController::class, 'getCourses']);
    Route::post('/courses', [InstructorController::class, 'storeCourse']);

    // مسارات المحاضر
    Route::prefix('instructor')->group(function () {
        Route::get('/quizzes', [InstructorController::class, 'getQuizzes']);
        Route::post('/quizzes', [InstructorController::class, 'storeQuiz']);
        Route::put('/quizzes/{id}', [InstructorController::class, 'updateQuiz']);
        Route::delete('/quizzes/{id}', [InstructorController::class, 'deleteQuiz']);
        Route::post('/quizzes/{id}/questions', [InstructorController::class, 'syncQuizQuestions']);
        Route::post('/quizzes/{id}/release-results', [InstructorController::class, 'releaseQuizResults']);

        Route::get('/questions', [InstructorController::class, 'getQuestions']);
        Route::post('/questions', [InstructorController::class, 'storeQuestion']);
        Route::delete('/questions/{id}', [InstructorController::class, 'deleteQuestion']);

        Route::get('/results', [InstructorController::class, 'getResults']);
        Route::get('/attempts/{attemptId}/review', [InstructorController::class, 'reviewAttempt']);
        Route::post('/attempts/{attemptId}/grade', [InstructorController::class, 'gradeEssayAnswers']);
        Route::post('/quizzes/{id}/toggle-answers', [InstructorController::class, 'toggleQuizAnswers']);

        Route::get('/questions', [InstructorController::class, 'getQuestions']);
        Route::post('/questions', [InstructorController::class, 'storeQuestion']);
        Route::put('/questions/{id}', [InstructorController::class, 'updateQuestion']);
        Route::delete('/questions/{id}', [InstructorController::class, 'deleteQuestion']);
    });

    // مسارات الطالب
    Route::prefix('student')->group(function () {
        Route::get('/quizzes', [ExamController::class, 'availableQuizzes']);
        Route::post('/quizzes/{id}/start', [ExamController::class, 'startExam']);
        Route::post('/attempts/{attemptId}/behavior', [ExamController::class, 'logBehavior']);
        Route::post('/attempts/{attemptId}/heartbeat', [ExamController::class, 'heartbeat']);
        Route::post('/attempts/{attemptId}/submit', [ExamController::class, 'submitExam']);
        Route::get('/results', [ExamController::class, 'myResults']);
        Route::get('/attempts/{attemptId}/review', [ExamController::class, 'reviewAttempt']);
    });

    // مسارات الأدمن
    Route::prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'dashboardStats']);
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/quizzes', [AdminController::class, 'getAllQuizzes']);
        Route::delete('/quizzes/{id}', [AdminController::class, 'deleteQuiz']);
        Route::get('/results', [AdminController::class, 'getAllResults']);
        Route::get('/attempts/{attemptId}/review', [InstructorController::class, 'reviewAttempt']);
        Route::post('/backups', [AdminController::class, 'createBackup']);
    });
});
