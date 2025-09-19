<?php

namespace Database\Factories;

use App\Models\Call;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Call>
 */
class CallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $caller = User::factory()->create();
        $receiver = User::factory()->create();

        return [
            'chat_id' => $chat->id,
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'type' => $this->faker->randomElement(['audio', 'video']),
            'call_type' => $this->faker->randomElement(['audio', 'video']),
            'status' => $this->faker->randomElement(['ringing', 'answered', 'ended', 'declined', 'missed']),
            'started_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'answered_at' => $this->faker->optional(0.6)->dateTimeBetween('-1 hour', 'now'),
            'ended_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'duration' => $this->faker->numberBetween(0, 1800),
            'participants' => [],
        ];
    }

    /**
     * Indicate that the call is ringing.
     */
    public function ringing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ringing',
            'answered_at' => null,
            'ended_at' => null,
            'duration' => 0,
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the call was answered.
     */
    public function answered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'answered',
            'answered_at' => now()->subMinutes($this->faker->numberBetween(0, 10)),
            'started_at' => now()->subMinutes($this->faker->numberBetween(11, 20)),
            'ended_at' => null,
            'duration' => 0,
        ]);
    }

    /**
     * Indicate that the call ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $this->faker->numberBetween(60, 1200), // 1-20 minutes
        ]);
    }

    /**
     * Indicate that the call was declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'declined',
            'answered_at' => null,
            'ended_at' => now(),
            'duration' => 0,
        ]);
    }

    /**
     * Indicate that the call was missed.
     */
    public function missed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'missed',
            'answered_at' => null,
            'ended_at' => now(),
            'duration' => 0,
        ]);
    }

    /**
     * Indicate that the call is audio.
     */
    public function audio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'audio',
            'call_type' => 'audio',
        ]);
    }

    /**
     * Indicate that the call is video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'call_type' => 'video',
        ]);
    }
}
