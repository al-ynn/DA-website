<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefaultTaskPreset extends Model
{
    protected $fillable = [
        'task_category_id',
        'name',
        'description',
        'laboratory_task_ids',
    ];

    protected $casts = [
        'laboratory_task_ids' => 'array',
    ];
}