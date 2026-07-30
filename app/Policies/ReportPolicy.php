<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Report $report): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Report $report): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->role === 'admin';
    }
}
