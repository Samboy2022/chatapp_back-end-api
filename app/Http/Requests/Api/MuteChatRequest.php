<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MuteChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // false unmutes. The app used to send this and be ignored — its
            // "Unmute" muted the chat for another 24 hours.
            'mute' => 'sometimes|boolean',
            'duration_minutes' => 'sometimes|nullable|integer|min:1|max:525600',
            'duration_hours' => 'sometimes|integer|min:1|max:8760',
            // Muted until the user unmutes.
            'always' => 'sometimes|boolean',
        ];
    }
}
