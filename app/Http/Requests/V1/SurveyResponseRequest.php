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
            'line_id' => ['sometimes', 'string', 'max:255'],
            'answers' => ['required', 'array'], // Changed from json to array
            'answers.*' => ['string'], // Validate each answer as string
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->answers && ! is_array($this->answers)) {
                $validator->errors()->add('answers', 'Answers must be an array.');
            }
        });
    }

    /**
     * Get the validated data with sanitized answers
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        // Sanitize answers to prevent XSS
        if (isset($validated['answers']) && is_array($validated['answers'])) {
            $validated['answers'] = $this->sanitizeAnswers($validated['answers']);
        }

        return $validated;
    }

    /**
     * Sanitize answers to prevent XSS attacks
     */
    private function sanitizeAnswers(array $answers): array
    {
        $sanitized = [];

        foreach ($answers as $key => $value) {
            if (is_string($value)) {
                // Remove script tags and PHP tags
                $value = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $value);
                $value = preg_replace('/<\?php.*?\?>/s', '', $value);
                $value = preg_replace('/<\?.*?\?>/s', '', $value);

                // Strip dangerous HTML tags but keep basic formatting
                $value = strip_tags($value, '<p><br><strong><em><u>');

                // Escape any remaining HTML entities
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    public function messages(): array
    {
        return [
            'survey_id.required' => 'The survey ID is required.',
            'survey_id.exists' => 'The selected survey does not exist.',
            'line_id.required' => 'The LINE ID is required.',
            'answers.required' => 'Survey answers are required.',
            'answers.array' => 'Answers must be an array.',
            'answers.*.string' => 'Each answer must be a string.',
        ];
    }
}
