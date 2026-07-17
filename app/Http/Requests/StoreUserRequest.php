<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isDraft = $this->boolean('is_draft');

        return [
            'first_name' => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'max:255',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'last_name' => [
                $isDraft ? 'nullable' : 'required',
                'string',
                'max:255',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],

            'sex' => [
                'exclude_if:is_draft,true',
                'required',
                Rule::in(['MALE', 'FEMALE']),
            ],

            'birthdate' => [
                'exclude_if:is_draft,true',
                'required',
                'date',
                'before:today',
            ],

            'contact_number' => [
                'exclude_if:is_draft,true',
                'required',
                'regex:/^\+639\d{9}$/',
                Rule::unique('users', 'contact_number'),
            ],

            'email' => [
                'exclude_if:is_draft,true',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'role' => [
                'exclude_if:is_draft,true',
                'required',
                Rule::in([
                    'admin',
                    'chemist',
                    'agriculturist',
                ]),
            ],

            'additional_tasks' => [
                'nullable',
                'array',
            ],

            'additional_tasks.*' => [
                Rule::in([
                    'chemist',
                    'agriculturist',
                ]),
            ],

            'is_draft' => [
                'nullable',
                'boolean',
            ],

            'is_disabled' => [
                'required',
                'boolean',
            ],
        ];
    }
    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'contact_number.regex' => 'The contact number must be in the format +639XXXXXXXXX.',
            'contact_number.unique' => 'This contact number is already in use.',
            'email.unique' => 'This email address is already in use.',
            'birthdate.before' => 'Birthdate must be a valid date before today.',
        ];
    }
}