<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $primaryKey = 'User_ID';

    public $incrementing = true;

    protected $keyType = 'int';

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
        'role_id',
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

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'User_ID', 'User_ID');
    }

    public function samples()
    {
        return $this->hasMany(Sample::class, 'User_ID', 'User_ID');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'User_ID', 'User_ID');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'User_ID', 'User_ID');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'User_ID', 'User_ID');
    }

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

    public function hasPermission($permissionName)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role
            ->permissions()
            ->where('permission_name', $permissionName)
            ->exists();
    }
}
