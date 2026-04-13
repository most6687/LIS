<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'Role_ID';
    protected $fillable = ['Role_Name'];

    public $timestamps = true;

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'Role_ID',
            'Permission_ID'
        );
    }

    public function users()
    {
        return $this->hasMany(User::class, 'Role_ID', 'Role_ID');
    }
}