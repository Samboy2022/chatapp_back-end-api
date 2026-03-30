<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Call;
use App\Models\User;
use App\Models\Chat;
use Carbon\Carbon;

class CallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users for testing
        $users = User::limit(10)->get();
        
        if ($users->count() < 2) {
            $this->command->info('Not enough users found. Creating test users...');
            
            // Create test users if they don't exist
            $user1 = User::firstOrCreate([
                'email' => 'testuser1@test.com'
            ], [
                'name' => 'Test User 1',
                'phone_number' => '+1234567890',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);
            
            $user2 = User::firstOrCreate([
                'email' => 'testuser2@test.com'
            ], [
                'name' => 'Test User 2',
                'phone_number' => '+1234567891',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);
            
            $user3 = User::firstOrCreate([
                'email' => 'testuser3@test.com'
            ], [
                'name' => 'Test User 3',
                'phone_number' => '+1234567892',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);
            
            $users = collect([$user1, $user2, $user3]);
        }
        
        // Get or create some chats
        $chats = Chat::limit(5)->get();
        
        if ($chats->count() < 2) {
            $this->command->info('Creating test chats...');
            
            // Create a private chat
            $privateChat = Chat::create([
                'type' => 'private',
                'name' => null,
                'created_by' => $users->first()->id,
            ]);
            
            // Add participants to private chat
            $privateChat->participants()->attach([
                $users->get(0)->id => ['joined_at' => now()],
                $users->get(1)->id => ['joined_at' => now()],
            ]);
            
            // Create a group chat
            $groupChat = Chat::create([
                'type' => 'group',
                'name' => 'Test Group Chat',
                'created_by' => $users->first()->id,
            ]);
            
            // Add participants to group chat
            $groupChat->participants()->attach([
                $users->get(0)->id => ['joined_at' => now(), 'role' => 'admin'],
                $users->get(1)->id => ['joined_at' => now(), 'role' => 'member'],
                $users->get(2)->id => ['joined_at' => now(), 'role' => 'member'],
            ]);
            
            $chats = collect([$privateChat, $groupChat]);
        }
        
        $this->command->info('Creating test call records...');
        
        // Create various call scenarios
        $callScenarios = [
            // Recent successful video call
            [
                'caller_id' => $users->get(0)->id,
                'receiver_id' => $users->get(1)->id,
                'chat_id' => $chats->first()->id,
                'call_type' => 'video',
                'status' => 'ended',
                'started_at' => now()->subMinutes(30),
                'answered_at' => now()->subMinutes(30)->addSeconds(5),
                'ended_at' => now()->subMinutes(25),
                'duration' => 300, // 5 minutes
            ],
            
            // Recent missed voice call
            [
                'caller_id' => $users->get(1)->id,
                'receiver_id' => $users->get(0)->id,
                'chat_id' => $chats->first()->id,
                'call_type' => 'audio',
                'status' => 'missed',
                'started_at' => now()->subMinutes(15),
                'answered_at' => null,
                'ended_at' => now()->subMinutes(15)->addSeconds(30),
                'duration' => null,
            ],
            
            // Declined video call
            [
                'caller_id' => $users->get(0)->id,
                'receiver_id' => $users->get(2)->id,
                'chat_id' => $chats->last()->id,
                'call_type' => 'video',
                'status' => 'declined',
                'started_at' => now()->subHours(1),
                'answered_at' => null,
                'ended_at' => now()->subHours(1)->addSeconds(10),
                'duration' => null,
            ],
            
            // Long successful voice call
            [
                'caller_id' => $users->get(2)->id,
                'receiver_id' => $users->get(1)->id,
                'chat_id' => $chats->first()->id,
                'call_type' => 'audio',
                'status' => 'ended',
                'started_at' => now()->subHours(2),
                'answered_at' => now()->subHours(2)->addSeconds(3),
                'ended_at' => now()->subHours(2)->addMinutes(45),
                'duration' => 2700, // 45 minutes
            ],
            
            // Currently active call (for testing real-time features)
            [
                'caller_id' => $users->get(1)->id,
                'receiver_id' => $users->get(0)->id,
                'chat_id' => $chats->first()->id,
                'call_type' => 'video',
                'status' => 'answered',
                'started_at' => now()->subMinutes(2),
                'answered_at' => now()->subMinutes(2)->addSeconds(8),
                'ended_at' => null,
                'duration' => null,
            ],
            
            // Ringing call (for testing real-time features)
            [
                'caller_id' => $users->get(0)->id,
                'receiver_id' => $users->get(2)->id,
                'chat_id' => $chats->last()->id,
                'call_type' => 'audio',
                'status' => 'ringing',
                'started_at' => now()->subSeconds(30),
                'answered_at' => null,
                'ended_at' => null,
                'duration' => null,
            ],
        ];
        
        // Add some historical calls from previous days
        for ($i = 1; $i <= 7; $i++) {
            $callScenarios[] = [
                'caller_id' => $users->random()->id,
                'receiver_id' => $users->random()->id,
                'chat_id' => $chats->random()->id,
                'call_type' => collect(['audio', 'video'])->random(),
                'status' => collect(['ended', 'missed', 'declined'])->random(),
                'started_at' => now()->subDays($i)->subMinutes(rand(10, 120)),
                'answered_at' => rand(0, 1) ? now()->subDays($i)->subMinutes(rand(10, 120))->addSeconds(rand(2, 10)) : null,
                'ended_at' => now()->subDays($i)->subMinutes(rand(5, 100)),
                'duration' => rand(0, 1) ? rand(30, 1800) : null, // 30 seconds to 30 minutes
            ];
        }
        
        // Create the calls
        foreach ($callScenarios as $scenario) {
            // Make sure caller and receiver are different
            if ($scenario['caller_id'] === $scenario['receiver_id']) {
                $scenario['receiver_id'] = $users->where('id', '!=', $scenario['caller_id'])->first()->id;
            }
            
            Call::create($scenario);
        }
        
        $this->command->info('Created ' . count($callScenarios) . ' test call records');
        
        // Create call participants for some calls
        $calls = Call::with('chat.participants')->get();
        
        foreach ($calls as $call) {
            if ($call->chat && $call->chat->participants->count() > 2) {
                // For group chats, add other participants
                $otherParticipants = $call->chat->participants->where('id', '!=', $call->caller_id);
                
                foreach ($otherParticipants as $participant) {
                    if ($participant->id !== $call->receiver_id) {
                        $call->participants()->create([
                            'user_id' => $participant->id,
                            'joined_at' => $call->started_at,
                            'left_at' => $call->ended_at,
                            'status' => in_array($call->status, ['answered', 'ended']) ? 'answered' : 'missed',
                        ]);
                    }
                }
            }
        }
        
        $this->command->info('Call seeding completed successfully!');
    }
}
