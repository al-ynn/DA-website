<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Laboratory tasks under this category.
     */
    public function laboratoryTasks(): HasMany
    {
        return $this->hasMany(LaboratoryTask::class);
    }

    /**
     * Presets under this category.
     */
    public function taskPresets(): HasMany
    {
        return $this->hasMany(TaskPreset::class);
    }
}