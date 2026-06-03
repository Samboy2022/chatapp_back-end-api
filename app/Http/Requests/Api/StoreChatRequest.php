<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'participants' => 'required|array',
            'participants.*' => 'exists:users,id',
            'type' => 'sometimes|in:private,group',
            'name' => 'required_if:type,group|string|max:255',
            'description' => 'sometimes|string|max:500',
        ];
    }
}
