<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'username',
        'password',
        'role',
        'full_name',
        'email',
        'phone',
        'department',
        'hire_date',
        'is_active',
        'last_login',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'last_login' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // bcrypt password عند التخزين تلقائي
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // العلاقات
    public function tests()
    {
        return $this->hasMany(Test::class, 'user_id', 'user_id');
    }

    public function samples()
    {
        return $this->hasMany(Sample::class, 'user_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id', 'user_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'user_id', 'user_id');
    }

    // دوال مساعدة
    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function getNameAttribute()
    {
        return $this->full_name;
    }
}
