<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendP2PMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
            'type' => 'required|in:text,image,video,audio,document,location',
            'reply_to_id' => 'nullable|exists:messages,id',
            'media_url' => 'nullable|string',
            'media_type' => 'nullable|string',
            'media_size' => 'nullable|integer',
            'duration' => 'nullable|integer',
        ];
    }
}
