<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],

            'sex' => [
                'required',
                Rule::in(['MALE', 'FEMALE']),
            ],

            'birthdate' => [
                'required',
                'date',
                'before:today',
            ],

            'contact_number' => [
                'required',
                'regex:/^\+639\d{9}$/',
                Rule::unique('users', 'contact_number')->ignore($this->route('user')),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],

            'role' => [
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