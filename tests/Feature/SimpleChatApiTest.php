<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use Laravel\Sanctum\Sanctum;

class SimpleChatApiTest extends TestCase
{
    protected $user;
    protected $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Test basic chat creation without RefreshDatabase
     */
    public function test_basic_chat_creation()
    {
        $response = $this->postJson('/api/chats', [
            'participants' => [$this->otherUser->id],
            'type' => 'private',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Chat created successfully',
            ]);

        // Verify the chat was created in database
        $this->assertDatabaseHas('chats', [
            'type' => 'private',
        ]);

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->otherUser->id,
        ]);
    }

    /**
     * Test getting chats
     */
    public function test_getting_chats()
    {
        $response = $this->getJson('/api/chats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
