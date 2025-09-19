<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'ChatWave',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Application Name',
                'description' => 'The name of your application',
                'is_public' => true
            ],
            [
                'key' => 'app_description',
                'value' => 'A modern real-time messaging application with voice and video calling',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Application Description',
                'description' => 'Brief description of your application',
                'is_public' => true
            ],
            [
                'key' => 'app_url',
                'value' => config('app.url'),
                'type' => 'string',
                'group' => 'general',
                'label' => 'Application URL',
                'description' => 'The base URL of your application',
                'is_public' => false
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@chatwave.com',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Administrator Email',
                'description' => 'Primary email for system notifications',
                'is_public' => false
            ],
            [
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Timezone',
                'description' => 'Default timezone for the application',
                'options' => [
                    'UTC' => 'UTC',
                    'America/New_York' => 'Eastern Time',
                    'America/Chicago' => 'Central Time',
                    'America/Denver' => 'Mountain Time',
                    'America/Los_Angeles' => 'Pacific Time',
                    'Europe/London' => 'London',
                    'Europe/Paris' => 'Paris',
                    'Asia/Tokyo' => 'Tokyo'
                ],
                'is_public' => true
            ],
            [
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Date Format',
                'description' => 'Default date format for the application',
                'options' => [
                    'Y-m-d' => '2024-12-06',
                    'm/d/Y' => '12/06/2024',
                    'd/m/Y' => '06/12/2024',
                    'F j, Y' => 'December 6, 2024'
                ],
                'is_public' => true
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Time Format',
                'description' => 'Default time format for the application',
                'options' => [
                    'H:i:s' => '24 Hour (14:30:00)',
                    'g:i A' => '12 Hour (2:30 PM)'
                ],
                'is_public' => true
            ],

            // File Upload Settings
            [
                'key' => 'enable_file_upload',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'file',
                'label' => 'Enable File Uploads',
                'description' => 'Allow users to upload files',
                'is_public' => true
            ],
            [
                'key' => 'max_file_size',
                'value' => '10',
                'type' => 'integer',
                'group' => 'file',
                'label' => 'Maximum File Size (MB)',
                'description' => 'Maximum file size allowed for uploads',
                'is_public' => true
            ],
            [
                'key' => 'allowed_file_types',
                'value' => 'jpg,jpeg,png,gif,pdf,doc,docx,txt,mp3,mp4,mov',
                'type' => 'string',
                'group' => 'file',
                'label' => 'Allowed File Types',
                'description' => 'Comma-separated file extensions',
                'is_public' => true
            ],
            [
                'key' => 'enable_voice_messages',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'file',
                'label' => 'Enable Voice Messages',
                'description' => 'Allow users to send voice messages',
                'is_public' => true
            ],

            // Chat Settings
            [
                'key' => 'max_group_size',
                'value' => '256',
                'type' => 'integer',
                'group' => 'chat',
                'label' => 'Maximum Group Size',
                'description' => 'Maximum number of participants in a group chat',
                'is_public' => true
            ],
            [
                'key' => 'message_retention_days',
                'value' => '365',
                'type' => 'integer',
                'group' => 'chat',
                'label' => 'Message Retention (Days)',
                'description' => 'How long to keep messages before deletion',
                'is_public' => false
            ],
            [
                'key' => 'auto_delete_messages',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'chat',
                'label' => 'Auto Delete Old Messages',
                'description' => 'Automatically delete old messages',
                'is_public' => false
            ],
            [
                'key' => 'enable_message_encryption',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'chat',
                'label' => 'Message Encryption',
                'description' => 'Enable end-to-end message encryption',
                'is_public' => true
            ],
            [
                'key' => 'enable_video_calls',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'chat',
                'label' => 'Video Calls',
                'description' => 'Enable video calling feature',
                'is_public' => true
            ],
            [
                'key' => 'enable_group_calls',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'chat',
                'label' => 'Group Calls',
                'description' => 'Enable group calling feature',
                'is_public' => true
            ],
            [
                'key' => 'enable_status_updates',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'chat',
                'label' => 'Status Updates',
                'description' => 'Enable status/story updates',
                'is_public' => true
            ],

            // User Management Settings
            [
                'key' => 'enable_user_registration',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'user',
                'label' => 'Enable User Registration',
                'description' => 'Allow new users to register',
                'is_public' => true
            ],
            [
                'key' => 'require_email_verification',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'user',
                'label' => 'Require Email Verification',
                'description' => 'Require users to verify their email address',
                'is_public' => false
            ],

            // Notification Settings
            [
                'key' => 'enable_push_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notification',
                'label' => 'Push Notifications',
                'description' => 'Enable push notifications',
                'is_public' => true
            ],
            [
                'key' => 'enable_email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notification',
                'label' => 'Email Notifications',
                'description' => 'Enable email notifications',
                'is_public' => false
            ],
            [
                'key' => 'enable_sms_notifications',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'notification',
                'label' => 'SMS Notifications',
                'description' => 'Enable SMS notifications',
                'is_public' => false
            ],

            // System Settings
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Maintenance Mode',
                'description' => 'Enable maintenance mode',
                'is_public' => false
            ],
            [
                'key' => 'debug_mode',
                'value' => config('app.debug') ? '1' : '0',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Debug Mode',
                'description' => 'Enable debug mode',
                'is_public' => false
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
