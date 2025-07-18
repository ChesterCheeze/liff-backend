<?php

namespace App\Http\Requests\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LineAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'line_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'picture_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'line_id.required' => 'The LINE ID is required.',
            'name.required' => 'The name field is required.',
            'picture_url.url' => 'The picture URL must be a valid URL.',
        ];
    }
}
