<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $primaryKey = 'quiz_id';

    public $timestamps = false;

    protected $guarded = [];

    // دالة تجلب المساق الذي ينتمي إليه هذا الاختبار
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
