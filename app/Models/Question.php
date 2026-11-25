<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';

    protected $primaryKey = 'question_id';

    public $timestamps = false;

    protected $guarded = [];

    // دالة تجلب خيارات الإجابة (A, B, C, D أو صح/خطأ) لهذا السؤال
    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id', 'question_id');
    }
}
