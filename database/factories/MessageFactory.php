<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Chat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_id' => Chat::factory(),
            'sender_id' => User::factory(),
            'message_type' => 'text',
            'content' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['sent', 'delivered', 'read']),
            'sent_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ];
    }

    /**
     * Create a text message.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'text',
            'content' => $this->faker->sentence(),
        ]);
    }

    /**
     * Create an image message.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'image',
            'content' => $this->faker->optional(0.7)->sentence(),
            'media_url' => $this->faker->imageUrl(),
            'media_type' => 'image/jpeg',
            'media_size' => $this->faker->numberBetween(1000, 5000000),
        ]);
    }

    /**
     * Create a video message.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'video',
            'content' => $this->faker->optional(0.5)->sentence(),
            'media_url' => 'https://example.com/video.mp4',
            'media_type' => 'video/mp4',
            'media_size' => $this->faker->numberBetween(100000, 50000000),
            'media_duration' => $this->faker->numberBetween(10, 300),
        ]);
    }

    /**
     * Create an audio message.
     */
    public function audio(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'audio',
            'media_url' => 'https://example.com/audio.mp3',
            'media_type' => 'audio/mp3',
            'media_size' => $this->faker->numberBetween(50000, 5000000),
            'media_duration' => $this->faker->numberBetween(30, 600),
        ]);
    }

    /**
     * Create a document message.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'document',
            'content' => $this->faker->optional(0.3)->sentence(),
            'media_url' => 'https://example.com/document.pdf',
            'media_type' => 'application/pdf',
            'media_size' => $this->faker->numberBetween(1000, 10000000),
            'file_name' => $this->faker->word() . '.pdf',
        ]);
    }

    /**
     * Create a location message.
     */
    public function location(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'location',
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'location_name' => $this->faker->address(),
        ]);
    }

    /**
     * Create a contact message.
     */
    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'contact',
            'contact_name' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
        ]);
    }

    /**
     * Create a sent message.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    /**
     * Create a delivered message.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Create a read message.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'read',
            'delivered_at' => $this->faker->dateTimeBetween('-2 days', '-1 day'),
            'read_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ]);
    }

    /**
     * Create a message with a reply.
     */
    public function withReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'reply_to_message_id' => Message::factory(),
        ]);
    }
}
