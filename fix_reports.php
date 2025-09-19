<?php

// Script to fix performance issues in reports/index.blade.php
$file = 'resources/views/admin/reports/index.blade.php';
$content = file_get_contents($file);

// Replace problematic database queries with controller variables
$replacements = [
    // Main dashboard cards
    '{{ App\Models\User::count() }}' => '{{ $stats[\'total_users\'] }}',
    '{{ App\Models\Message::count() }}' => '{{ $stats[\'total_messages\'] }}',
    '{{ App\Models\Chat::count() }}' => '{{ $stats[\'total_chats\'] }}',
    '{{ App\Models\Call::count() }}' => '{{ $stats[\'total_calls\'] }}',
    
    // Performance-critical queries
    '{{ App\Models\User::where(\'last_seen_at\', \'>=\', now()->subMinutes(5))->count() }}' => '{{ $stats[\'online_users\'] }}',
    '{{ App\Models\Message::whereDate(\'created_at\', today())->count() }}' => '{{ $stats[\'today_messages\'] }}',
    '{{ App\Models\Status::where(\'expires_at\', \'>\', now())->count() }}' => '{{ $stats[\'active_statuses\'] }}',
    '{{ App\Models\Chat::where(\'type\', \'group\')->count() }}' => '{{ $stats[\'group_chats\'] }}',
    '{{ App\Models\Call::where(\'status\', \'ended\')->count() }}' => '{{ $stats[\'successful_calls\'] }}',
];

// Apply replacements
foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Write the fixed content back
file_put_contents($file, $content);

echo "Fixed " . count($replacements) . " database queries in reports view\n"; 