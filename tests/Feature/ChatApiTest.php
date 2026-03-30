<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use Laravel\Sanctum\Sanctum;

class ChatApiTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $otherUser;
    protected $thirdUser;
    protected $chat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->thirdUser = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Test fetching user's chats when user has no chats.
     */
    public function test_can_get_empty_chats_list()
    {
        $response = $this->getJson('/api/chats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'chats' => []
                ]
            ]);
    }

    /**
     * Test fetching user's chats with existing chats.
     */
    public function test_can_get_chats_with_data()
    {
        // Create a private chat
        $privateChat = Chat::factory()->create(['type' => 'private']);
        $privateChat->participants()->attach([$this->user->id, $this->otherUser->id]);

        // Create a group chat
        $groupChat = Chat::factory()->create([
            'type' => 'group',
            'name' => 'Test Group',
            'created_by' => $this->user->id
        ]);
        $groupChat->participants()->attach([$this->user->id, $this->otherUser->id, $this->thirdUser->id]);

        // Add a message to one chat
        Message::factory()->create([
            'chat_id' => $privateChat->id,
            'sender_id' => $this->otherUser->id,
            'content' => 'Hello!',
            'message_type' => 'text'
        ]);

        $response = $this->getJson('/api/chats');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'chats' => [
                        '*' => [
                            'id',
                            'type',
                            'name',
                            'unread_count',
                            'latest_message',
                            'participants',
                            'created_at'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test creating a private chat.
     */
    public function test_can_create_private_chat()
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

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->otherUser->id,
        ]);
    }

    /**
     * Test creating a group chat.
     */
    public function test_can_create_group_chat()
    {
        $response = $this->postJson('/api/chats', [
            'participants' => [$this->otherUser->id, $this->thirdUser->id],
            'name' => 'Test Group Chat',
            'description' => 'A test group chat',
            'type' => 'group',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Chat created successfully',
            ]);

        $this->assertDatabaseHas('chats', [
            'name' => 'Test Group Chat',
            'description' => 'A test group chat',
            'type' => 'group',
            'created_by' => $this->user->id,
        ]);
    }

    /**
     * Test creating chat with validation errors.
     */
    public function test_create_chat_validation_errors()
    {
        // Missing participants
        $response = $this->postJson('/api/chats', [
            'type' => 'group',
            'name' => 'Test Chat'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation errors',
            ]);

        // Group chat without name
        $response = $this->postJson('/api/chats', [
            'participants' => [$this->otherUser->id],
            'type' => 'group'
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test creating chat with non-existent user.
     */
    public function test_create_chat_with_invalid_participant()
    {
        $response = $this->postJson('/api/chats', [
            'participants' => [9999], // Non-existent user ID
            'type' => 'private',
        ]);

        $response->assertStatus(422) // Should fail due to validation
            ->assertJson([
                'success' => false,
                'message' => 'Validation errors',
            ]);
    }

    /**
     * Test getting a specific chat.
     */
    public function test_can_get_specific_chat()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->getJson('/api/chats/' . $chat->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'chat' => [
                        'id' => $chat->id,
                        'type' => 'private',
                    ]
                ]
            ]);
    }

    /**
     * Test getting chat that user is not participant of.
     */
    public function test_cannot_get_chat_user_not_participant()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->otherUser->id, $this->thirdUser->id]); // User not included

        $response = $this->getJson('/api/chats/' . $chat->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You are not a participant of this chat'
            ]);
    }

    /**
     * Test updating a group chat.
     */
    public function test_can_update_group_chat()
    {
        $chat = Chat::factory()->create([
            'type' => 'group',
            'name' => 'Original Name',
            'created_by' => $this->user->id
        ]);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id], ['role' => 'admin']);

        $response = $this->putJson('/api/chats/' . $chat->id, [
            'name' => 'Updated Group Name',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chat updated successfully',
            ]);

        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
            'name' => 'Updated Group Name',
            'description' => 'Updated description',
        ]);
    }

    /**
     * Test updating chat without admin privileges.
     */
    public function test_cannot_update_group_chat_without_admin()
    {
        $chat = Chat::factory()->create([
            'type' => 'group',
            'name' => 'Original Name',
            'created_by' => $this->otherUser->id
        ]);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id], ['role' => 'member']);

        $response = $this->putJson('/api/chats/' . $chat->id, [
            'name' => 'Updated Group Name',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only admins can update group chat details'
            ]);
    }

    /**
     * Test archiving a chat.
     */
    public function test_can_archive_chat()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->postJson('/api/chats/' . $chat->id . '/archive');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chat archived successfully'
            ]);

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->user->id,
            'chat_id' => $chat->id,
            'is_archived' => true,
        ]);
    }

    /**
     * Test unarchiving a chat.
     */
    public function test_can_unarchive_chat()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id], ['is_archived' => true]);

        $response = $this->postJson('/api/chats/' . $chat->id . '/archive');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chat unarchived successfully'
            ]);

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->user->id,
            'chat_id' => $chat->id,
            'is_archived' => false,
        ]);
    }

    /**
     * Test pinning a chat.
     */
    public function test_can_pin_chat()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->postJson('/api/chats/' . $chat->id . '/pin');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chat pinned successfully'
            ]);

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->user->id,
            'chat_id' => $chat->id,
            'is_pinned' => true,
        ]);
    }

    /**
     * Test unpinning a chat.
     */
    public function test_can_unpin_chat()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id], ['is_pinned' => true]);

        $response = $this->postJson('/api/chats/' . $chat->id . '/pin');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chat unpinned successfully'
            ]);

        $this->assertDatabaseHas('chat_participants', [
            'user_id' => $this->user->id,
            'chat_id' => $chat->id,
            'is_pinned' => false,
        ]);
    }

    /**
     * Test muting a chat.
     */
    public function test_can_mute_chat()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->postJson('/api/chats/' . $chat->id . '/mute', [
            'duration_hours' => 24
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Chat muted for 24 hours'
            ]);

        $participant = $chat->participants()->where('user_id', $this->user->id)->first();
        $this->assertNotNull($participant->pivot->muted_until);
    }

    /**
     * Test leaving a group chat.
     */
    public function test_can_leave_group_chat()
    {
        $chat = Chat::factory()->create([
            'type' => 'group',
            'created_by' => $this->otherUser->id
        ]);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id, $this->thirdUser->id]);

        $response = $this->postJson('/api/chats/' . $chat->id . '/leave');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Left chat successfully'
            ]);

        $this->assertDatabaseMissing('chat_participants', [
            'user_id' => $this->user->id,
            'chat_id' => $chat->id,
        ]);
    }

    /**
     * Test cannot leave private chat.
     */
    public function test_cannot_leave_private_chat()
    {
        $chat = Chat::factory()->create(['type' => 'private']);
        $chat->participants()->attach([$this->user->id, $this->otherUser->id]);

        $response = $this->postJson('/api/chats/' . $chat->id . '/leave');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot leave private chat'
            ]);
    }


}
