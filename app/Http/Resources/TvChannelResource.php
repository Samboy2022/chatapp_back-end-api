<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TvChannelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'stream_url' => $this->stream_url,
            'thumbnail_url' => $this->thumbnail_url,
            'is_live' => $this->is_live,
            'view_count' => $this->view_count,
            'sort_order' => $this->sort_order,
        ];
    }
}
