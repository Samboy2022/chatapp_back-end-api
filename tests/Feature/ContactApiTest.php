<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /**
     * Test fetching contacts.
     *
     * @return void
     */
    public function test_can_get_contacts()
    {
        $response = $this->getJson('/api/contacts');

        $response->assertStatus(200);
    }

    /**
     * Test syncing contacts.
     *
     * @return void
     */
    public function test_can_sync_contacts()
    {
        $contactUser = User::factory()->create();

        $response = $this->postJson('/api/contacts/sync', [
            'contacts' => [
                [
                    'phone' => $contactUser->phone_number,
                    'name' => $contactUser->name,
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contacts synced successfully',
            ]);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
        ]);
    }

    /**
     * Test getting blocked contacts.
     *
     * @return void
     */
    public function test_can_get_blocked_contacts()
    {
        $blockedUser = User::factory()->create();

        // Create blocked contact relationship
        \App\Models\Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $blockedUser->id,
            'is_blocked' => true
        ]);

        $response = $this->getJson('/api/contacts/blocked');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Blocked contacts retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'contact_user_id',
                            'contact_name',
                            'is_blocked',
                            'is_favorite',
                            'added_at',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]);
    }

    /**
     * Test getting favorite contacts.
     *
     * @return void
     */
    public function test_can_get_favorite_contacts()
    {
        $favoriteUser = User::factory()->create();

        // Create favorite contact relationship
        \App\Models\Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $favoriteUser->id,
            'is_favorite' => true
        ]);

        $response = $this->getJson('/api/contacts/favorites');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Favorite contacts retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'contact_user_id',
                            'contact_name',
                            'is_blocked',
                            'is_favorite',
                            'added_at',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]);
    }

    /**
     * Test blocking a contact.
     *
     * @return void
     */
    public function test_can_block_contact()
    {
        $contactUser = User::factory()->create();

        // First create contact relationship
        \App\Models\Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
            'is_blocked' => false
        ]);

        $response = $this->postJson('/api/contacts/block/' . $contactUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User blocked successfully',
            ]);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
            'is_blocked' => true,
        ]);
    }

    /**
     * Test unblocking a contact.
     *
     * @return void
     */
    public function test_can_unblock_contact()
    {
        $contactUser = User::factory()->create();

        // Create blocked contact relationship
        \App\Models\Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
            'is_blocked' => true
        ]);

        $response = $this->postJson('/api/contacts/unblock/' . $contactUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User unblocked successfully',
            ]);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
            'is_blocked' => false,
        ]);
    }

    /**
     * Test toggling favorite status.
     *
     * @return void
     */
    public function test_can_toggle_favorite_contact()
    {
        $contactUser = User::factory()->create();

        // Create contact relationship
        \App\Models\Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
            'is_favorite' => false
        ]);

        // Add to favorites
        $response = $this->postJson('/api/contacts/favorite/' . $contactUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contact added to favorites',
            ]);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
            'is_favorite' => true,
        ]);

        // Remove from favorites
        $response = $this->postJson('/api/contacts/favorite/' . $contactUser->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Contact removed from favorites',
            ]);

        $this->assertDatabaseHas('contacts', [
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser->id,
            'is_favorite' => false,
        ]);
    }

    /**
     * Test searching contacts.
     *
     * @return void
     */
    public function test_can_search_contacts()
    {
        $contactUser1 = User::factory()->create(['name' => 'John Doe']);
        $contactUser2 = User::factory()->create(['name' => 'Jane Smith']);

        // Create contact relationships
        \App\Models\Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser1->id
        ]);

        \App\Models\Contact::factory()->create([
            'user_id' => $this->user->id,
            'contact_user_id' => $contactUser2->id
        ]);

        $response = $this->getJson('/api/contacts/search?query=John Doe Smith Johnson Williams Brown Davis Miller Wilson Taylor Anderson');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Users found successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'phone_number',
                            'country_code',
                            'avatar_url',
                            'last_seen_at',
                            'is_online'
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ]);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('John Doe', $data[0]['name']);
    }

    /**
     * Test getting all users as contacts (for contact list).
     *
     * @return void
     */
    public function test_can_get_all_users_as_contacts()
    {
        // Create some users
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/contacts/list');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Users retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone_number',
                            'avatar',
                            'is_online',
                            'last_seen',
                            'can_chat',
                            'member_since'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                        'from',
                        'to'
                    ]
                ]
            ]);
    }

    /**
     * Test getting online users.
     *
     * @return void
     */
    public function test_can_get_online_users()
    {
        // Create users and set some as online
        $onlineUser = User::factory()->create(['is_online' => true]);
        $offlineUser = User::factory()->create(['is_online' => false]);

        $response = $this->getJson('/api/contacts/online');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Online users retrieved successfully'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'users' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'phone_number',
                            'avatar',
                            'is_online',
                            'last_seen',
                            'can_chat'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page'
                    ]
                ]
            ]);

        $data = $response->json('data.users');
        $onlineUsers = collect($data)->where('is_online', true);

        $this->assertGreaterThan(0, $onlineUsers->count());
    }

    /**
     * Test contact validation errors.
     *
     * @return void
     */
    public function test_contact_validation_errors()
    {
        // Test blocking non-existent contact
        $response = $this->postJson('/api/contacts/block/9999');

        $response->assertStatus(404);

        // Test favoriting non-existent contact
        $response = $this->postJson('/api/contacts/favorite/9999');

        $response->assertStatus(404);
    }
}