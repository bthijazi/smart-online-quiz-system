<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $table = 'question_options';

    protected $primaryKey = 'option_id';

    public $timestamps = false;

    protected $guarded = [];
}
