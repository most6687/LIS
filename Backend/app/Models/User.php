<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="User_ID", type="integer", example=1),
 *     @OA\Property(property="Username", type="string", example="john_doe"),
 *     @OA\Property(property="Role", type="string", example="Admin"),
 *     @OA\Property(property="Full_Name", type="string", example="John Doe"),
 *     @OA\Property(property="Email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="Phone", type="string", example="+1234567890"),
 *     @OA\Property(property="Department", type="string", example="IT"),
 *     @OA\Property(property="Hire_Date", type="string", format="date", example="2020-01-01"),
 *     @OA\Property(property="Is_Active", type="boolean", example=true),
 *     @OA\Property(property="Last_Login", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $primaryKey = 'User_ID';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'username',
        'password',
        'Role',
        'Full_Name',
        'Email',
        'Phone',
        'Department',
        'Hire_Date',
        'Is_Active',
        'Last_Login',
        'Permissions',
        'Role_ID',
    ];

    protected $hidden = [
        'password',
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
        $this->attributes['password'] = bcrypt($value);
    }

    // العلاقات
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
        return $this->belongsTo(Role::class, 'Role_ID', 'Role_ID');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'User_ID', 'User_ID');
    }

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

    public function hasPermission($permissionName)
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->permissions()->where('Permission_Name', $permissionName)->exists();
    }
}