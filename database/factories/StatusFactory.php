<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Status>
 */
class StatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'content_type' => $this->faker->randomElement(['text', 'image', 'video']),
            'content' => $this->faker->sentence,
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 day'),
            'media_url' => null,
            'thumbnail_url' => null,
            'background_color' => null,
            'font_style' => null,
            'privacy_settings' => null,
        ];
    }
}
