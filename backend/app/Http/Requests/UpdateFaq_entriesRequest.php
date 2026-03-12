<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaq_entriesRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:255'],

            'category' => ['nullable', 'string', 'max:100'],

            'answer' => ['required', 'string', 'max:5000'],

            'keywords' => ['nullable', 'array'],

            'keywords.*' => ['string', 'max:100'],

            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The FAQ title is required.',
            'title.max' => 'The FAQ title may not exceed 255 characters.',

            'answer.required' => 'The FAQ answer is required.',
            'answer.max' => 'The FAQ answer may not exceed 5000 characters.',

            'keywords.array' => 'Keywords must be an array.',
        ];
    }
}
