<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkAssignTaskPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'user_ids.*' => [
                'distinct',
                'exists:users,id',
            ],

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
