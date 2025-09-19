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
} 