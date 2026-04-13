<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'Permission_ID';
    protected $fillable = ['Permission_Name'];

    public $timestamps = true;

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'Permission_ID',
            'Role_ID'
        );
    }
}