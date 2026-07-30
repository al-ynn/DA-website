<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TaskPreset extends Model
{
    protected $fillable = [
        'task_category_id',
        'name',
        'description',
        'is_active',
    ];

    /**
     * Category this preset belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    /**
     * Tasks included in this preset.
     */
    public function laboratoryTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            LaboratoryTask::class,
            'task_preset_task',
            'task_preset_id',
            'laboratory_task_id'
        )->withTimestamps();
    }

    /**
     * Users assigned to this preset.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_task_presets',
            'task_preset_id',
            'user_id'
        )->withTimestamps();
    }
}
