<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadioProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'host' => $this->host,

            'audio_url' => $this->audio_url,
            'thumbnail_url' => $this->thumbnail_url,

            'duration_seconds' => $this->duration_seconds,
            'file_size' => $this->file_size,

            'is_live' => $this->isLive(),
            // The client uses this to decide whether to offer a download
            // button, so compute it here rather than shipping the raw column.
            'is_downloadable' => $this->isDownloadable(),

            'play_count' => $this->play_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'sort_order' => $this->sort_order,
        ];
    }
}
