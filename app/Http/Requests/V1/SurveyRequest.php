<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class SurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'section' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive,draft'],
        ];

        // For updates, make all fields optional
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            foreach ($rules as $field => $fieldRules) {
                if (in_array('required', $fieldRules)) {
                    $rules[$field] = array_merge(['sometimes'], $fieldRules);
                }
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'section.required' => 'The section field is required.',
            'name.required' => 'The survey name is required.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be one of: active, inactive, draft.',
        ];
    }
}
