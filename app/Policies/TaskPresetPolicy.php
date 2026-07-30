<?php

namespace App\Policies;

use App\Models\TaskPreset;
use App\Models\User;

class TaskPresetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, TaskPreset $taskPreset): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, TaskPreset $taskPreset): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, TaskPreset $taskPreset): bool
    {
        return $user->role === 'admin';
    }

    public function restore(User $user, TaskPreset $taskPreset): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, TaskPreset $taskPreset): bool
    {
        return $user->role === 'admin';
    }
}
