<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $table = 'quiz_attempts';

    protected $primaryKey = 'attempt_id';

    public $timestamps = false;

    protected $guarded = [];

    // دالة تجلب بيانات الطالب صاحب المحاولة
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // دالة تجلب بيانات الاختبار
    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }

    // دالة تجلب جميع إجابات الطالب في هذه المحاولة
    public function answers()
    {
        return $this->hasMany(StudentAnswer::class, 'attempt_id', 'attempt_id');
    }
}
