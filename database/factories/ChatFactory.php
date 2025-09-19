<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->optional(0.8)->words(2, true), // 80% chance of having a name
            'type' => $this->faker->randomElement(['private', 'group']),
            'description' => $this->faker->optional(0.6)->sentence(),
            'avatar_url' => $this->faker->optional(0.3)->imageUrl(200, 200),
            'created_by' => User::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Create a private chat.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'private',
            'name' => null, // Private chats don't have names
        ]);
    }

    /**
     * Create a group chat.
     */
    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'group',
            'name' => $this->faker->words(2, true),
        ]);
    }

    /**
     * Create an inactive chat.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a chat with a specific creator.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}
