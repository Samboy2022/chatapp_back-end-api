<?php

namespace App\Support;

use App\Models\User;

/**
 * A user's notification preferences: what's stored, over the defaults.
 */
class NotificationSettings
{
    public const DEFAULTS = [
        'message_notifications' => true,
        'group_notifications' => true,
        'call_notifications' => true,
        'status_notifications' => true,
        // name_and_message | name_only | none
        'notification_preview' => 'name_and_message',
        'in_app_sounds' => true,
        'in_app_vibrate' => true,
    ];

    public const PREVIEWS = ['name_and_message', 'name_only', 'none'];

    public static function for(User $user): array
    {
        $stored = $user->notification_settings;
        if (!is_array($stored)) {
            $stored = [];
        }
        return array_merge(self::DEFAULTS, array_intersect_key($stored, self::DEFAULTS));
    }

    public static function update(User $user, array $changes): array
    {
        $merged = array_merge(self::for($user), array_intersect_key($changes, self::DEFAULTS));
        $user->forceFill(['notification_settings' => $merged])->save();
        return $merged;
    }
}
