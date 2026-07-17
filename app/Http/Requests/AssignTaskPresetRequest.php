<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTaskPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_presets' => [
                'array',
            ],

            'task_presets.*' => [
                'distinct',
                'exists:task_presets,id',
            ],

            'task_ids' => [
                'array',
            ],

            'task_ids.*' => [
                'distinct',
                'exists:laboratory_tasks,id',
            ],
        ];
    }
}
