<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Events\MessageSent;
use Carbon\Carbon;

class ChatManagementTest extends TestCase
{
    private User $user;
    private User $otherUser;
    private User $thirdUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = $this->authenticateUser();
        $this->otherUser = User::factory()->create();
        $this->thirdUser = User::factory()->create();
    }

    /** @test */
    public function user_can_create_private_chat()
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

        $this->assertDatabaseHas('chats', [
            'type' => 'private',
            'created_by' => $this->user->id,
        ]);

        $chat = Chat::latest()->first();
        $this->assertTrue($chat->participants->contains($this->user));
        $this->assertTrue($chat->participants->contains($this->otherUser));
    }

    /** @test */
    public function user_can_create_group_chat()
    {
        $response = $this->postJson('/api/chats', [
            'participants' => [$this->otherUser->id, $this->thirdUser->id],
            'name' => 'Test Group',
            'description' => 'A test group chat',
            'type' => 'group',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Chat created successfully',
            ]);

        $this->assertDatabaseHas('chats', [
            'type' => 'group',
            'name' => 'Test Group',
            'created_by' => $this->user->id,
        ]);

        $chat = Chat::latest()->first();
        $this->assertTrue($chat->participants->contains($this->user));
        $this->assertTrue($chat->participants->contains($this->otherUser));
        $this->assertTrue($chat->participants->contains($this->thirdUser));
    }

    /** @test */
    public function user_can_get_chat_list()
    {
        // Create a chat first
        $chat = Chat::factory()->create(['created_by' => $this->user->id]);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->getJson('/api/chats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function user_can_send_message_to_chat()
    {
        // Create a chat first
        $chat = Chat::factory()->create(['created_by' => $this->user->id]);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->postJson("/api/chats/{$chat->id}/messages", [
            'type' => 'text',
            'content' => 'Hello, this is a test message!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'sender_id' => $this->user->id,
            'content' => 'Hello, this is a test message!',
        ]);
    }

    /** @test */
    public function user_cannot_send_message_to_unauthorized_chat()
    {
        // Create a chat without the user as participant
        $chat = Chat::factory()->create(['created_by' => $this->otherUser->id]);
        $chat->participants()->attach([$this->otherUser->id, $this->thirdUser->id]);

        $response = $this->postJson("/api/chats/{$chat->id}/messages", [
            'type' => 'text',
            'content' => 'This should fail!',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You are not a participant in this chat',
            ]);
    }

    /** @test */
    public function user_can_get_messages_from_chat()
    {
        // Create a chat and message
        $chat = Chat::factory()->create(['created_by' => $this->user->id]);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);
        
        Message::factory()->create([
            'chat_id' => $chat->id,
            'sender_id' => $this->otherUser->id,
            'content' => 'Test message',
        ]);

        $response = $this->getJson("/api/chats/{$chat->id}/messages");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function user_can_leave_group_chat()
    {
        // Create a group chat
        $chat = Chat::factory()->create([
            'type' => 'group',
            'created_by' => $this->otherUser->id,
        ]);
        $chat->participants()->attach([
            $this->user->id,
            $this->otherUser->id,
            $this->thirdUser->id,
        ]);

        $response = $this->postJson("/api/chats/{$chat->id}/leave");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Left chat successfully',
            ]);

        $this->assertFalse($chat->fresh()->participants->contains($this->user));
    }

    /** @test */
    public function user_cannot_leave_private_chat()
    {
        // Create a private chat
        $chat = Chat::factory()->create([
            'type' => 'private',
            'created_by' => $this->user->id,
        ]);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->postJson("/api/chats/{$chat->id}/leave");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot leave private chat',
            ]);
    }
}