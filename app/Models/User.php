<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

    public $timestamps = false;

    protected $guarded = [];

    protected $hidden = ['password_hash'];

    // دالة تحدد عمود كلمة المرور المستخدم في المصادقة
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // دالة تجلب الدور الخاص بالمستخدم (admin, instructor, student)
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
