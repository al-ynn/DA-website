<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskPresetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_category_id' => [
                'required',
                'exists:task_categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'laboratory_tasks' => [
                'required',
                'array',
                'min:1',
            ],

            'laboratory_tasks.*' => [
                'exists:laboratory_tasks,id',
            ],
        ];
    }
}