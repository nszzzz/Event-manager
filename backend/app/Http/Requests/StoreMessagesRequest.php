<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessagesRequest extends FormRequest
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
            'content' => [
                'required',
                'string',
                'min:1',
                'max:5000'
            ],

            'message_type' => [
                'sometimes',
                'string',
                'in:text,system,voice_transcript'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Message content is required.',
            'content.min' => 'The message must be at least 1 character.',
            'content.max' => 'The message may not exceed 5000 characters.',
            'message_type.in' => 'Invalid message type.',
        ];
    }
}
