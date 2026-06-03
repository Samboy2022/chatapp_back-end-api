<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:text,image,video,audio,document,location,contact',
            'content' => 'required_if:type,text|nullable|string',
            'media_url' => 'required_if:type,image,video,audio,document|nullable|string',
            'media_type' => 'nullable|string',
            'media_size' => 'nullable|integer',
            'media_duration' => 'nullable|integer',
            'latitude' => 'required_if:type,location|nullable|numeric',
            'longitude' => 'required_if:type,location|nullable|numeric',
            'location_name' => 'nullable|string',
            'contact_name' => 'required_if:type,contact|nullable|string',
            'contact_phone' => 'required_if:type,contact|nullable|string',
            'reply_to_message_id' => 'nullable|exists:messages,id',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Handle both 'type' and 'message_type' field names for compatibility
        if ($this->has('message_type') && !$this->has('type')) {
            $this->merge(['type' => $this->input('message_type')]);
        }
    }
}
