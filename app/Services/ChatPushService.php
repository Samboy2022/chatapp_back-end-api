<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use App\Support\NotificationSettings;
use Illuminate\Support\Facades\Log;

/**
 * Push notifications for chat messages.
 *
 * Messages were only ever broadcast over the websocket, which reaches people
 * who already have the app open — precisely the ones who least need telling.
 * Anyone with the app closed or backgrounded got nothing at all. This sends
 * the actual push.
 */
class ChatPushService
{
    public function __construct(private OneSignalService $push)
    {
    }

    /**
     * Notify a chat's participants about a new message.
     *
     * Never throws: a failed notification must not roll back or fail the send
     * that triggered it. The message is already saved by this point.
     */
    public function notifyNewMessage(Message $message): void
    {
        try {
            if (!$this->push->isConfigured()) {
                return;
            }

            $chat = $message->chat;

            if (!$chat) {
                return;
            }

            $isGroup = $chat->isGroup();
            $recipients = $this->recipientsFor($message, $isGroup);

            if ($recipients === []) {
                return;
            }

            $sender = $message->sender;
            $senderName = $sender?->name ?: 'Someone';

            // In a group the chat name is the headline and the sender is
            // prefixed onto the body, matching how every messaging app does it
            // — otherwise you can't tell which group a message came from.
            $chatTitle = $isGroup ? ($chat->name ?: 'Group message') : $senderName;
            $fullBody = $isGroup
                ? $senderName . ': ' . $this->preview($message)
                : $this->preview($message);

            // Each recipient's "show preview" choice: full text, who it's
            // from only, or nothing identifying at all.
            $byPreview = [];
            foreach ($recipients as $id => $mode) {
                // Array keys that look numeric come back as ints; OneSignal
                // external ids are strings.
                $byPreview[$mode][] = (string) $id;
            }

            $data = [
                'type' => 'new_message',
                'chat_id' => (string) $chat->id,
                'message_id' => (string) $message->id,
                'sender_id' => (string) ($sender?->id ?? ''),
                'sender_name' => $senderName,
                'chat_type' => $chat->type,
                // Lets the app open straight to the right conversation.
                'chat_name' => $isGroup ? ($chat->name ?: '') : $senderName,
            ];

            foreach ($byPreview as $mode => $ids) {
                [$title, $body] = match ($mode) {
                    'none' => [config('app.name', 'New message'), 'New message'],
                    'name_only' => [$chatTitle, $isGroup ? "New message from {$senderName}" : 'New message'],
                    default => [$chatTitle, $fullBody],
                };
                $this->push->sendToUsers($ids, $title, $body, $data);
            }
        } catch (\Throwable $e) {
            Log::warning('Chat push notification failed', [
                'message_id' => $message->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Everyone who should be told about this message.
     *
     * Excludes the sender (they know), anyone who has left the chat, and
     * anyone who muted it — a muted conversation that still buzzes the phone
     * is the fastest way to get an app uninstalled.
     */
    /**
     * @return array<string, string> recipient id => their preview mode, minus
     *         anyone who turned off notifications for this kind of chat.
     */
    private function recipientsFor(Message $message, bool $isGroup): array
    {
        // `participants()` is a belongsToMany onto users, so these conditions
        // are stated against the pivot explicitly. Bare column names happen to
        // resolve today only because `users` has no column of the same name —
        // a fragile thing to depend on.
        return $message->chat
            ->participants()
            ->wherePivot('user_id', '!=', $message->sender_id)
            ->wherePivotNull('left_at')
            ->where(function ($query) {
                $query->whereNull('chat_participants.muted_until')
                      ->orWhere('chat_participants.muted_until', '<=', now());
            })
            ->get(['users.id', 'users.notification_settings'])
            ->mapWithKeys(function (User $user) use ($isGroup) {
                $settings = NotificationSettings::for($user);
                $wanted = $isGroup ? $settings['group_notifications'] : $settings['message_notifications'];
                return $wanted ? [(string) $user->id => $settings['notification_preview']] : [];
            })
            ->all();
    }

    /**
     * One line describing the message.
     *
     * Media gets a label rather than a blank line — a notification reading
     * just "Ahmad" with no body tells the user nothing.
     */
    private function preview(Message $message): string
    {
        $content = trim((string) $message->content);

        // The column is message_type; `type` was always null, so photos and
        // voice notes arrived as an empty "New message".
        $label = match ($message->message_type ?? 'text') {
            'image' => '📷 Photo',
            'video' => '🎥 Video',
            'voice', 'audio' => '🎤 Voice message',
            'file', 'document' => '📄 ' . ($message->file_name ?: 'Document'),
            'location' => '📍 Location',
            'contact' => '👤 Contact',
            default => null,
        };

        if ($label !== null) {
            // A captioned photo shows the caption after the label.
            return $content !== '' ? $label . ' · ' . $this->truncate($content) : $label;
        }

        return $content !== '' ? $this->truncate($content) : 'New message';
    }

    /**
     * Keep the body short. Both platforms truncate anyway, and a long preview
     * pushes the useful part off the lock screen.
     */
    private function truncate(string $text, int $limit = 120): string
    {
        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit - 1) . '…'
            : $text;
    }
}
