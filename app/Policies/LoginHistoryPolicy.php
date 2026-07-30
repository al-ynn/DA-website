<?php

namespace App\Policies;

use App\Models\LoginHistory;
use App\Models\User;

class LoginHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, LoginHistory $loginHistory): bool
    {
        return $user->role === 'admin';
    }
}
