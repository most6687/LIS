<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $primaryKey = 'Permission_ID';

    protected $fillable = [
        'name'
    ];

    public $timestamps = false;
}