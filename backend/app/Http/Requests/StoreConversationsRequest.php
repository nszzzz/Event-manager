<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConversationsRequest extends FormRequest
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
            'subject' => ['nullable', 'string', 'max:255'],

            'channel' => ['required', 'string', 'in:web_chat,voice'],
        ];
    }

    public function messages(): array
    {
        return [
            'channel.required' => 'A communication channel is required.',
            'channel.in' => 'The selected channel must be either web chat or voice.',
            'subject.max' => 'The subject may not be greater than 255 characters.',
        ];
    }
}
