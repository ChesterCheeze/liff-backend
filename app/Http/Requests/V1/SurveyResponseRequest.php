<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class SurveyResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'survey_id' => ['required', 'integer', 'exists:surveys,id'],
            'line_id' => ['required', 'string', 'max:255'],
            'answers' => ['required', 'json', 'max:10000'], // Limit JSON size
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->answers) {
                $decoded = json_decode($this->answers, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $validator->errors()->add('answers', 'Invalid JSON format.');
                } elseif (! is_array($decoded)) {
                    $validator->errors()->add('answers', 'Answers must be a JSON object.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'survey_id.required' => 'The survey ID is required.',
            'survey_id.exists' => 'The selected survey does not exist.',
            'line_id.required' => 'The LINE ID is required.',
            'answers.required' => 'Survey answers are required.',
            'answers.json' => 'Answers must be in valid JSON format.',
        ];
    }
}
