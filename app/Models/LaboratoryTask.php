<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LaboratoryTask extends Model
{
    protected $fillable = [
        'task_category_id',
        'name',
    ];

    /**
     * Category this laboratory task belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }
    /**
     * Presets that include this task.
     */
    public function taskPresets(): BelongsToMany
    {
        return $this->belongsToMany(
            TaskPreset::class,
            'task_preset_task',
            'laboratory_task_id',
            'task_preset_id'
        )->withTimestamps();
    }

    /**
     * Users assigned to this individual task.
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_task_assignments',
            'laboratory_task_id',
            'user_id'
        )->withTimestamps();
    }
}
