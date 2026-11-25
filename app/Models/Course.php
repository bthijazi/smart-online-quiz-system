<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 'courses';

    protected $primaryKey = 'course_id';

    public $timestamps = false;

    protected $guarded = [];

    // دالة تجلب المحاضر المسؤول عن المساق
    public function instructor()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
