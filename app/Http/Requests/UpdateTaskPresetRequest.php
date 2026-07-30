<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'integer',
                'distinct',
                Rule::exists('laboratory_tasks', 'id')->where(
                    fn ($query) => $query->where(
                        'task_category_id',
                        $this->integer('task_category_id')
                    )
                ),
            ],
        ];
    }
}
