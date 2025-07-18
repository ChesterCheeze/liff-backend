<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class SurveyQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/'],
            'type' => ['required', 'string', 'in:text,textarea,radio,checkbox,select,email,number,date,url,tel'],
            'required' => ['sometimes', 'boolean'],
        ];

        // Only require survey_id if it's not being injected from route (when no Survey model binding)
        if (! $this->route('survey')) {
            $rules['survey_id'] = ['required', 'integer', 'exists:surveys,id'];
        }

        // For updates, make most fields optional except survey_id
        if ($this->isMethod('PATCH') || $this->isMethod('PUT')) {
            unset($rules['survey_id']); // Survey ID shouldn't be changeable in updates
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
            'survey_id.required' => 'The survey ID is required.',
            'survey_id.exists' => 'The selected survey does not exist.',
            'label.required' => 'The question label is required.',
            'name.required' => 'The question name is required.',
            'name.regex' => 'The question name must be a valid identifier (letters, numbers, underscores only).',
            'type.required' => 'The question type is required.',
            'type.in' => 'The question type must be one of: text, textarea, radio, checkbox, select, email, number, date, url, tel.',
        ];
    }
}
