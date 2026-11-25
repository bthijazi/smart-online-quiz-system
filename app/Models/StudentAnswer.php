<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $table = 'student_answers';

    protected $primaryKey = 'answer_id';

    public $timestamps = false;

    protected $guarded = [];
}
