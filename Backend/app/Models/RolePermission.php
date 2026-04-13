<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permissions';
    protected $fillable = ['Role_ID', 'Permission_ID'];

    public $timestamps = true;

    public function role()
    {
        return $this->belongsTo(Role::class, 'Role_ID', 'Role_ID');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'Permission_ID', 'Permission_ID');
    }
}