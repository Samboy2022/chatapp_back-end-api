<?php

namespace Database\Factories;

use App\Models\MediaFile;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;

class MediaFileFactory extends Factory
{
    protected $model = MediaFile::class;

    public function definition(): array
    {
        $types = ['image', 'video', 'audio', 'document', 'voice'];
        $type = $this->faker->randomElement($types);
        
        $formats = [
            'image' => ['jpg', 'png', 'gif', 'webp'],
            'video' => ['mp4', 'mov', 'avi'],
            'audio' => ['mp3', 'wav', 'ogg'],
            'document' => ['pdf', 'doc', 'docx', 'txt'],
            'voice' => ['ogg', 'mp3', 'wav'],
        ];
        
        $format = $this->faker->randomElement($formats[$type]);
        $size = $this->faker->numberBetween(10240, 10485760); // 10KB to 10MB
        
        return [
            'user_id' => User::factory(),
            'public_id' => $this->faker->unique()->regexify('[a-z0-9]{10}/[a-z0-9]{15}'),
            'url' => "https://res.cloudinary.com/test/{$type}/upload/test.{$format}",
            'thumbnail_url' => $type === 'image' ? "https://res.cloudinary.com/test/image/upload/c_fill,h_200,w_200/test.{$format}" : null,
            'type' => $type,
            'format' => $format,
            'resource_type' => $type === 'document' ? 'raw' : ($type === 'audio' || $type === 'voice' ? 'video' : $type),
            'size' => $size,
            'size_formatted' => $this->formatBytes($size),
            'width' => in_array($type, ['image', 'video']) ? $this->faker->numberBetween(640, 1920) : null,
            'height' => in_array($type, ['image', 'video']) ? $this->faker->numberBetween(480, 1080) : null,
            'duration' => in_array($type, ['video', 'audio', 'voice']) ? $this->faker->numberBetween(10, 300) : null,
            'folder' => "media/{$type}s",
            'chat_id' => null,
            'message_id' => null,
            'usage_type' => 'message',
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the media file is an avatar
     */
    public function avatar(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'format' => 'jpg',
            'resource_type' => 'image',
            'folder' => 'avatars',
            'usage_type' => 'avatar',
            'width' => 500,
            'height' => 500,
            'metadata' => [
                'small_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_50,w_50/avatar.jpg'
            ],
        ]);
    }

    /**
     * Indicate that the media file belongs to a chat
     */
    public function forChat(Chat $chat = null): static
    {
        return $this->state(fn (array $attributes) => [
            'chat_id' => $chat?->id ?? Chat::factory(),
        ]);
    }

    /**
     * Indicate that the media file is an image
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'format' => 'jpg',
            'resource_type' => 'image',
            'width' => 1920,
            'height' => 1080,
            'thumbnail_url' => 'https://res.cloudinary.com/test/image/upload/c_fill,h_200,w_200/test.jpg',
        ]);
    }

    /**
     * Indicate that the media file is a video
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'format' => 'mp4',
            'resource_type' => 'video',
            'width' => 1920,
            'height' => 1080,
            'duration' => 120,
        ]);
    }

    /**
     * Indicate that the media file is an audio
     */
    public function audio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'audio',
            'format' => 'mp3',
            'resource_type' => 'video',
            'width' => null,
            'height' => null,
            'duration' => 180,
        ]);
    }

    /**
     * Indicate that the media file is a document
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
            'format' => 'pdf',
            'resource_type' => 'raw',
            'width' => null,
            'height' => null,
            'thumbnail_url' => null,
        ]);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
