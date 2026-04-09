<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\ActivityLog;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'User_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'Username',
        'Password',
        'Role',
        'Full_Name',
        'Email',
        'Phone',
        'Department',
        'Hire_Date',
        'Is_Active',
        'Last_Login',
        'Permissions',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    protected $casts = [
        'Hire_Date' => 'date',
        'Last_Login' => 'datetime',
        'Is_Active' => 'boolean',
        'Permissions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['Password'] = bcrypt($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    public function role()
    {
        return $this->belongsTo(Role::class, 'Role_ID', 'Role_ID');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'User_ID');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    */

    public function isAdmin()
    {
        return $this->Role === 'Admin';
    }

    public function isActive()
    {
        return $this->Is_Active;
    }

    public function getNameAttribute()
    {
        return $this->Full_Name;
    }
}