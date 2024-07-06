<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function edit(User $user, User $registeredUser)
    {
        return $user->is($registeredUser);
    }
}
