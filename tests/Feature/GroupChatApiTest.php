<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageReaction;
use Laravel\Sanctum\Sanctum;

class GroupChatApiTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $otherUser;
    protected $thirdUser;
    protected $groupChat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->thirdUser = User::factory()->create();

        // Create group chat
        $this->groupChat = Chat::factory()->create([
            'type' => 'group',
            'name' => 'Test Group',
            'created_by' => $this->user->id
        ]);

        // Add participants
        $this->groupChat->participants()->attach([
            $this->user->id => ['role' => 'admin'],
            $this->otherUser->id => ['role' => 'member'],
            $this->thirdUser->id => ['role' => 'member']
        ]);

        Sanctum::actingAs($this->user);
    }

    /**
     * Test sending message to group chat.
     *
     * @return void
     */
    public function test_can_send_message_to_group_chat()
    {
        $messageData = [
            'type' => 'text',
            'content' => 'Hello group!',
        ];

        $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages', $messageData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->user->id,
            'message_type' => 'text',
            'content' => 'Hello group!',
        ]);
    }

    /**
     * Test sending image message to group chat.
     *
     * @return void
     */
    public function test_can_send_image_message_to_group_chat()
    {
        $messageData = [
            'type' => 'image',
            'content' => 'Group image!',
            'media_url' => 'https://example.com/group-image.jpg',
            'media_type' => 'image/jpeg',
            'media_size' => 2048000,
        ];

        $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages', $messageData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->user->id,
            'message_type' => 'image',
            'media_url' => 'https://example.com/group-image.jpg',
        ]);
    }

    /**
     * Test sending video message to group chat.
     *
     * @return void
     */
    public function test_can_send_video_message_to_group_chat()
    {
        $messageData = [
            'type' => 'video',
            'content' => 'Group video!',
            'media_url' => 'https://example.com/group-video.mp4',
            'media_type' => 'video/mp4',
            'media_size' => 5120000,
        ];

        $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages', $messageData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->user->id,
            'message_type' => 'video',
            'media_url' => 'https://example.com/group-video.mp4',
        ]);
    }

    /**
     * Test sending file message to group chat.
     *
     * @return void
     */
    public function test_can_send_file_message_to_group_chat()
    {
        $messageData = [
            'type' => 'file',
            'content' => 'Group document',
            'media_url' => 'https://example.com/group-document.pdf',
            'media_type' => 'application/pdf',
            'media_size' => 1024000,
            'file_name' => 'document.pdf',
        ];

        $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages', $messageData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->user->id,
            'message_type' => 'file',
            'media_url' => 'https://example.com/group-document.pdf',
        ]);
    }

    /**
     * Test group member can send message.
     *
     * @return void
     */
    public function test_group_member_can_send_message()
    {
        $this->actingAs($this->otherUser);

        $messageData = [
            'type' => 'text',
            'content' => 'Message from member',
        ];

        $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages', $messageData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Message sent successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->otherUser->id,
            'content' => 'Message from member',
        ]);
    }

    /**
     * Test non-member cannot send message to group.
     *
     * @return void
     */
    public function test_non_member_cannot_send_message_to_group()
     {
         $nonMember = User::factory()->create();
         $this->actingAs($nonMember);

         $messageData = [
             'type' => 'text',
             'content' => 'Message from non-member',
         ];

         $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages', $messageData);

         $response->assertStatus(403)
             ->assertJson([
                 'success' => false,
                 'message' => 'You are not a participant in this chat',
             ]);
     }

    /**
     * Test adding user to group chat.
     *
     * @return void
     */
    public function test_admin_can_add_user_to_group()
    {
        $newUser = User::factory()->create();

        $response = $this->postJson('/api/groups/' . $this->groupChat->id . '/users', [
            'user_id' => $newUser->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User added to group successfully',
            ]);

        $this->assertDatabaseHas('chat_participants', [
            'chat_id' => $this->groupChat->id,
            'user_id' => $newUser->id,
        ]);
    }

    /**
     * Test adding multiple users to group chat.
     *
     * @return void
     */
    public function test_admin_can_add_multiple_users_to_group()
    {
        $newUser1 = User::factory()->create();
        $newUser2 = User::factory()->create();

        $response = $this->postJson('/api/groups/' . $this->groupChat->id . '/members', [
            'user_ids' => [$newUser1->id, $newUser2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Users added to group successfully',
            ]);

        $this->assertDatabaseHas('chat_participants', [
            'chat_id' => $this->groupChat->id,
            'user_id' => $newUser1->id,
        ]);

        $this->assertDatabaseHas('chat_participants', [
            'chat_id' => $this->groupChat->id,
            'user_id' => $newUser2->id,
        ]);
    }

    /**
     * Test removing user from group chat.
     *
     * @return void
     */
    public function test_admin_can_remove_user_from_group()
    {
        $response = $this->deleteJson('/api/groups/' . $this->groupChat->id . '/users/' . $this->otherUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User removed from group successfully',
            ]);

        $this->assertDatabaseMissing('chat_participants', [
            'chat_id' => $this->groupChat->id,
            'user_id' => $this->otherUser->id,
        ]);
    }

    /**
     * Test group member leaving group.
     *
     * @return void
     */
    public function test_member_can_leave_group()
    {
        $this->actingAs($this->otherUser);

        $response = $this->postJson('/api/groups/' . $this->groupChat->id . '/leave');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Left group successfully',
            ]);

        $this->assertDatabaseMissing('chat_participants', [
            'chat_id' => $this->groupChat->id,
            'user_id' => $this->otherUser->id,
        ]);
    }

    /**
     * Test getting group information.
     *
     * @return void
     */
    public function test_can_get_group_information()
    {
        $response = $this->getJson('/api/groups/' . $this->groupChat->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'type',
                    'participants' => [
                        '*' => [
                            'id',
                            'name',
                            'role'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test updating group information.
     *
     * @return void
     */
    public function test_admin_can_update_group_information()
    {
        $updateData = [
            'name' => 'Updated Group Name',
            'description' => 'Updated group description',
        ];

        $response = $this->putJson('/api/groups/' . $this->groupChat->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Group updated successfully',
            ]);

        $this->assertDatabaseHas('chats', [
            'id' => $this->groupChat->id,
            'name' => 'Updated Group Name',
            'description' => 'Updated group description',
        ]);
    }

    /**
     * Test non-admin cannot update group information.
     *
     * @return void
     */
    public function test_non_admin_cannot_update_group_information()
    {
        $this->actingAs($this->otherUser);

        $updateData = [
            'name' => 'Unauthorized Update',
        ];

        $response = $this->putJson('/api/groups/' . $this->groupChat->id, $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Only admins can update group information',
            ]);
    }

    /**
     * Test group message reactions.
     *
     * @return void
     */
    public function test_group_message_reactions()
    {
        // Send a message to group
        $message = Message::factory()->create([
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->otherUser->id,
            'message_type' => 'text',
            'content' => 'Message to react to'
        ]);

        // Add reaction
        $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages/' . $message->id . '/react', [
            'emoji' => '👍'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reaction added successfully',
            ]);

        $this->assertDatabaseHas('message_reactions', [
            'message_id' => $message->id,
            'user_id' => $this->user->id,
            'emoji' => '👍'
        ]);

        // Switch to another user and add different reaction
        $this->actingAs($this->thirdUser);

        $response = $this->postJson('/api/chats/' . $this->groupChat->id . '/messages/' . $message->id . '/react', [
            'emoji' => '❤️'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('message_reactions', [
            'message_id' => $message->id,
            'user_id' => $this->thirdUser->id,
            'emoji' => '❤️'
        ]);
    }

    /**
     * Test group message editing by sender.
     *
     * @return void
     */
    public function test_group_member_can_edit_own_message()
    {
        $message = Message::factory()->create([
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->otherUser->id,
            'message_type' => 'text',
            'content' => 'Original group message'
        ]);

        $this->actingAs($this->otherUser);

        $response = $this->putJson('/api/chats/' . $this->groupChat->id . '/messages/' . $message->id, [
            'content' => 'Edited group message'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Message updated successfully',
            ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => 'Edited group message',
        ]);

        $this->assertNotNull($message->fresh()->edited_at);
    }

    /**
     * Test admin can delete any message in group.
     *
     * @return void
     */
    public function test_admin_can_delete_any_message_in_group()
    {
        $message = Message::factory()->create([
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->otherUser->id,
            'message_type' => 'text',
            'content' => 'Message to delete by admin'
        ]);

        $response = $this->deleteJson('/api/chats/' . $this->groupChat->id . '/messages/' . $message->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Message deleted successfully',
            ]);

        $this->assertSoftDeleted('messages', ['id' => $message->id]);
    }

    /**
     * Test getting group messages with pagination.
     *
     * @return void
     */
    public function test_can_get_group_messages_with_pagination()
    {
        // Create multiple messages
        Message::factory()->count(10)->create([
            'chat_id' => $this->groupChat->id,
            'sender_id' => $this->otherUser->id,
            'message_type' => 'text'
        ]);

        $response = $this->getJson('/api/chats/' . $this->groupChat->id . '/messages?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(5, $data['data']);
        $this->assertEquals(10, $data['total']);
    }

    /**
     * Test group chat validation errors.
     *
     * @return void
     */
    public function test_group_chat_validation_errors()
    {
        // Test adding non-existent user to group
        $response = $this->postJson('/api/groups/' . $this->groupChat->id . '/users', [
            'user_id' => 9999,
        ]);

        $response->assertStatus(422);

        // Test removing non-existent user from group
        $response = $this->deleteJson('/api/groups/' . $this->groupChat->id . '/users/9999');

        $response->assertStatus(404);
    }
}