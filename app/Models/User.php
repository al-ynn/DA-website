<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'birthdate',
        'contact_number',
        'email',
        'password',
        'role',
        'additional_tasks',
        'is_disabled',
        'is_draft',
        'has_temporary_password',
        'password_expires_at',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'additional_tasks' => 'array',
        'is_disabled' => 'boolean',
        'is_draft' => 'boolean',
        'has_temporary_password' => 'boolean',
        'password_changed_at' => 'datetime',
        'password_expires_at' => 'datetime',
        'password_reminder_dismissed_at' => 'datetime',
    ];

    /**
     * Tasks assigned to this user.
     */
    public function taskPresets(): BelongsToMany
    {
        return $this->belongsToMany(
            TaskPreset::class,
            'user_task_presets',
            'user_id',
            'task_preset_id'
        )->withTimestamps();
    }

    /**
     * Legacy preset assignments retained for backward compatibility.
     */
    public function legacyTaskPresets(): BelongsToMany
    {
        return $this->belongsToMany(
            TaskPreset::class,
            'task_user',
            'user_id',
            'task_preset_id'
        )->withTimestamps();
    }

    /**
     * Individual laboratory tasks assigned to this user.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            LaboratoryTask::class,
            'user_task_assignments',
            'user_id',
            'laboratory_task_id'
        )->withTimestamps();
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function createdReports(): HasMany
    {
        return $this->reports();
    }
}
