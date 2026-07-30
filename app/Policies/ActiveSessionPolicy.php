<?php

namespace App\Policies;

use App\Models\ActiveSession;
use App\Models\User;

class ActiveSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, ActiveSession $activeSession): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, ActiveSession $activeSession): bool
    {
        return $user->role === 'admin';
    }
}
