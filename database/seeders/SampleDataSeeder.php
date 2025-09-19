<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Status;
use App\Models\Call;
use App\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create an admin user or get existing one
        $admin = User::firstOrCreate(
            ['email' => 'admin@chatwave.com'],
            [
                'name' => 'ChatWave Admin',
                'phone_number' => '+1234567890',
                'country_code' => '+1',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_online' => true,
                'last_seen_at' => now(),
            ]
        );

        // Create sample users (only if we don't have enough users)
        if (User::count() < 10) {
            User::factory(10)->create();
        }
        
        $users = User::all();

        // Create sample chats (only if we don't have enough chats)
        if (Chat::count() < 5) {
            for ($i = 0; $i < 5; $i++) {
                $chat = Chat::create([
                    'name' => $i % 3 == 0 ? 'Group Chat ' . ($i + 1) : null,
                    'type' => $i % 3 == 0 ? 'group' : 'private',
                    'description' => $i % 3 == 0 ? 'Sample group chat description' : null,
                ]);

                // Add participants to chat_participants table
                $participants = $users->random($i % 3 == 0 ? rand(3, 6) : 2);
                foreach ($participants as $user) {
                    DB::table('chat_participants')->insertOrIgnore([
                        'chat_id' => $chat->id,
                        'user_id' => $user->id,
                        'role' => $participants->first()->id === $user->id ? 'admin' : 'member',
                        'joined_at' => now()->subDays(rand(1, 30)),
                    ]);
                }

                // Create sample messages for each chat
                for ($j = 0; $j < rand(3, 10); $j++) {
                    Message::create([
                        'chat_id' => $chat->id,
                        'sender_id' => $participants->random()->id,
                        'message_type' => collect(['text', 'image', 'video', 'audio', 'document'])->random(),
                        'content' => 'Sample message content ' . ($j + 1),
                        'created_at' => now()->subHours(rand(1, 48)),
                    ]);
                }
            }
        }

        // Create sample status updates (only if we don't have enough)
        if (Status::count() < 10) {
            for ($i = 0; $i < 10; $i++) {
                Status::create([
                    'user_id' => $users->random()->id,
                    'content_type' => collect(['text', 'image', 'video'])->random(),
                    'content' => 'Sample status content ' . ($i + 1),
                    'expires_at' => now()->addHours(rand(1, 24)),
                    'created_at' => now()->subHours(rand(1, 12)),
                ]);
            }
        }

        // Create sample calls (only if we don't have enough)
        if (Call::count() < 15) {
            $chats = Chat::all();
            for ($i = 0; $i < 15; $i++) {
                $chat = $chats->random();
                $caller = $users->random();

                // Get all chat participants from the database
                $participants = DB::table('chat_participants')
                    ->where('chat_id', $chat->id)
                    ->pluck('user_id')
                    ->toArray();

                // Get receiver (any participant except the caller)
                $other_participants = array_filter($participants, function($participant_id) use ($caller) {
                    return $participant_id !== $caller->id;
                });

                // If no other participants, pick a random user
                if (empty($other_participants)) {
                    $receiver_id = $users->where('id', '!=', $caller->id)->random()->id;
                } else {
                    $receiver_id = collect($other_participants)->random();
                }

                Call::create([
                    'chat_id' => $chat->id,
                    'caller_id' => $caller->id,
                    'receiver_id' => $receiver_id,
                    'call_type' => collect(['audio', 'video'])->random(),
                    'status' => collect(['ended', 'missed', 'declined'])->random(),
                    'duration' => rand(10, 1800), // 10 seconds to 30 minutes
                    'started_at' => now()->subDays(rand(1, 7)),
                    'ended_at' => now()->subDays(rand(1, 7))->addMinutes(rand(1, 30)),
                ]);
            }
        }

        echo "Sample data created successfully!\n";
        echo "Admin user: admin@chatwave.com (password: password)\n";
        echo "Total users: " . User::count() . "\n";
        echo "Total chats: " . Chat::count() . "\n";
        echo "Total messages: " . Message::count() . "\n";
        echo "Total status updates: " . Status::count() . "\n";
        echo "Total calls: " . Call::count() . "\n";
    }
} 