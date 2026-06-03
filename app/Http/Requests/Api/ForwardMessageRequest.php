<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ForwardMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message_id' => 'required|exists:messages,id',
            'target_chat_id' => 'required|exists:chats,id',
            'additional_text' => 'nullable|string|max:1000',
        ];
    }
}
