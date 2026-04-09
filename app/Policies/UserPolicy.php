<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user)
    {
        return $user->Role === 'Admin';
    }

    public function view(User $user, User $model)
    {
        return $user->Role === 'Admin';
    }

    public function create(User $user)
    {
        return $user->Role === 'Admin';
    }

    public function update(User $user, User $model)
    {
        return $user->Role === 'Admin';
    }

    public function delete(User $user, User $model)
    {
        return $user->Role === 'Admin';
    }
}