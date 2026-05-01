<?php

namespace Database\Seeders;

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
                'value' => 'Farmers Network',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Application Name',
                'description' => 'The name of your application displayed across all pages',
                'is_public' => true
            ],
            [
                'key' => 'app_description',
                'value' => 'Connect & Collaborate',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Application Description',
                'description' => 'Short tagline or description for your application',
                'is_public' => true
            ],
            [
                'key' => 'app_url',
                'value' => config('app.url', 'http://localhost'),
                'type' => 'string',
                'group' => 'general',
                'label' => 'Application URL',
                'description' => 'The base URL of your application',
                'is_public' => true
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@farmersnetwork.com',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Admin Email',
                'description' => 'Primary admin contact email',
                'is_public' => false
            ],
            [
                'key' => 'logo_url',
                'value' => '',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Logo URL',
                'description' => 'URL to your application logo',
                'is_public' => true
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Maintenance Mode',
                'description' => 'Enable to put the application in maintenance mode',
                'is_public' => false
            ],

            // ========== UI COLORS SETTINGS ==========
            // Primary Colors
            [
                'key' => 'color_primary',
                'value' => '#15803d',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Primary Color',
                'description' => 'Main brand color used for buttons, links, and accents (default: green-700)',
                'is_public' => true
            ],
            [
                'key' => 'color_primary_hover',
                'value' => '#166534',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Primary Hover Color',
                'description' => 'Darker shade for hover states (default: green-800)',
                'is_public' => true
            ],
            [
                'key' => 'color_primary_light',
                'value' => '#dcfce7',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Primary Light Color',
                'description' => 'Light shade for backgrounds (default: green-100)',
                'is_public' => true
            ],
            // Secondary Colors
            [
                'key' => 'color_secondary',
                'value' => '#1f2937',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Secondary Color',
                'description' => 'Secondary color for text and elements (default: gray-800)',
                'is_public' => true
            ],
            [
                'key' => 'color_secondary_light',
                'value' => '#f3f4f6',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Secondary Light Color',
                'description' => 'Light gray for backgrounds (default: gray-100)',
                'is_public' => true
            ],
            // Accent Colors
            [
                'key' => 'color_accent',
                'value' => '#2563eb',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Accent Color',
                'description' => 'Accent color for highlights (default: blue-600)',
                'is_public' => true
            ],
            [
                'key' => 'color_success',
                'value' => '#16a34a',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Success Color',
                'description' => 'Color for success states (default: green-600)',
                'is_public' => true
            ],
            [
                'key' => 'color_warning',
                'value' => '#ea580c',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Warning Color',
                'description' => 'Color for warning states (default: orange-600)',
                'is_public' => true
            ],
            [
                'key' => 'color_danger',
                'value' => '#dc2626',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Danger Color',
                'description' => 'Color for error/danger states (default: red-600)',
                'is_public' => true
            ],
            // Text Colors
            [
                'key' => 'color_text_primary',
                'value' => '#111827',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Primary Text Color',
                'description' => 'Main text color (default: gray-900)',
                'is_public' => true
            ],
            [
                'key' => 'color_text_secondary',
                'value' => '#6b7280',
                'type' => 'color',
                'group' => 'colors',
                'label' => 'Secondary Text Color',
                'description' => 'Muted text color (default: gray-500)',
                'is_public' => true
            ],

            // ========== LANDING PAGE - HERO SECTION ==========
            [
                'key' => 'landing_hero_badge',
                'value' => 'Trusted by 250,000+ farmers worldwide',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Hero Badge Text',
                'description' => 'Small badge text above the main headline',
                'is_public' => true
            ],
            [
                'key' => 'landing_hero_title',
                'value' => 'Where Farmers Connect, Share & Grow',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Hero Title',
                'description' => 'Main headline on the landing page',
                'is_public' => true
            ],
            [
                'key' => 'landing_hero_highlight',
                'value' => 'Farmers',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Hero Highlight Text',
                'description' => 'Highlighted/colored part of the title',
                'is_public' => true
            ],
            [
                'key' => 'landing_hero_subheadline',
                'value' => 'Share crops, ideas & success with your farming community',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Hero Subheadline',
                'description' => 'Short tagline below the main headline',
                'is_public' => true
            ],
            [
                'key' => 'landing_hero_description',
                'value' => 'Connect with fellow farmers, get real-time market tips, and build your agricultural network. Simple messaging built for the field.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Hero Description',
                'description' => 'Paragraph text below the subheadline',
                'is_public' => true
            ],
            [
                'key' => 'landing_hero_cta_primary',
                'value' => 'Join the Community',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Primary CTA Button',
                'description' => 'Text for the main call-to-action button',
                'is_public' => true
            ],
            [
                'key' => 'landing_hero_cta_secondary',
                'value' => 'See How It Works',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Secondary CTA Button',
                'description' => 'Text for the secondary button',
                'is_public' => true
            ],
            [
                'key' => 'landing_hero_image',
                'value' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=600&h=500&fit=crop',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Hero Image URL',
                'description' => 'Main hero section image',
                'is_public' => true
            ],

            // ========== LANDING PAGE - STATS ==========
            [
                'key' => 'landing_stat_users',
                'value' => '250K+',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: Active Users',
                'description' => 'Number of active users to display',
                'is_public' => true
            ],
            [
                'key' => 'landing_stat_users_label',
                'value' => 'Active Farmers',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: Users Label',
                'description' => 'Label for users stat',
                'is_public' => true
            ],
            [
                'key' => 'landing_stat_countries',
                'value' => '120+',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: Countries',
                'description' => 'Number of countries',
                'is_public' => true
            ],
            [
                'key' => 'landing_stat_countries_label',
                'value' => 'Regions',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: Countries Label',
                'description' => 'Label for countries stat',
                'is_public' => true
            ],
            [
                'key' => 'landing_stat_rating',
                'value' => '4.9',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: App Rating',
                'description' => 'App store rating',
                'is_public' => true
            ],
            [
                'key' => 'landing_stat_rating_label',
                'value' => 'Farmer Satisfaction',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: Rating Label',
                'description' => 'Label for rating stat',
                'is_public' => true
            ],
            [
                'key' => 'landing_stat_messages',
                'value' => '10M+',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: Messages Sent',
                'description' => 'Number of messages sent',
                'is_public' => true
            ],
            [
                'key' => 'landing_stat_messages_label',
                'value' => 'Messages Sent',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Stat: Messages Label',
                'description' => 'Label for messages stat',
                'is_public' => true
            ],

            // ========== LANDING PAGE - FEATURES SECTION ==========
            [
                'key' => 'landing_features_badge',
                'value' => 'Powerful Features',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Features Badge',
                'description' => 'Badge text above features section',
                'is_public' => true
            ],
            [
                'key' => 'landing_features_title',
                'value' => 'Everything You Need to Connect',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Features Title',
                'description' => 'Main title for features section',
                'is_public' => true
            ],
            [
                'key' => 'landing_features_description',
                'value' => 'Our platform provides all the tools you need to communicate, collaborate, and grow your farming network.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Features Description',
                'description' => 'Description text for features section',
                'is_public' => true
            ],
            // Feature 1
            [
                'key' => 'landing_feature_1_title',
                'value' => 'Real-time Messaging',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 1 Title',
                'description' => 'Title for first feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_1_description',
                'value' => 'Instant messaging with read receipts, typing indicators, and seamless synchronization across all devices.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Feature 1 Description',
                'description' => 'Description for first feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_1_icon',
                'value' => 'ph-chat-circle-dots',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 1 Icon',
                'description' => 'Phosphor icon class for first feature',
                'is_public' => true
            ],
            // Feature 2
            [
                'key' => 'landing_feature_2_title',
                'value' => 'Group Communities',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 2 Title',
                'description' => 'Title for second feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_2_description',
                'value' => 'Create and join farming communities with up to 256 members. Share knowledge and collaborate on projects.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Feature 2 Description',
                'description' => 'Description for second feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_2_icon',
                'value' => 'ph-users-three',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 2 Icon',
                'description' => 'Phosphor icon class for second feature',
                'is_public' => true
            ],
            // Feature 3
            [
                'key' => 'landing_feature_3_title',
                'value' => 'Video & Voice Calls',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 3 Title',
                'description' => 'Title for third feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_3_description',
                'value' => 'Crystal-clear HD video and voice calls. Connect face-to-face with farmers around the world.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Feature 3 Description',
                'description' => 'Description for third feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_3_icon',
                'value' => 'ph-video-camera',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 3 Icon',
                'description' => 'Phosphor icon class for third feature',
                'is_public' => true
            ],
            // Feature 4
            [
                'key' => 'landing_feature_4_title',
                'value' => 'Media Sharing',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 4 Title',
                'description' => 'Title for fourth feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_4_description',
                'value' => 'Share photos, videos, and documents easily. Perfect for sharing crop updates and farming techniques.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Feature 4 Description',
                'description' => 'Description for fourth feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_4_icon',
                'value' => 'ph-image',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 4 Icon',
                'description' => 'Phosphor icon class for fourth feature',
                'is_public' => true
            ],
            // Feature 5
            [
                'key' => 'landing_feature_5_title',
                'value' => 'Status Updates',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 5 Title',
                'description' => 'Title for fifth feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_5_description',
                'value' => 'Share your daily farming activities with status updates that disappear after 24 hours.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Feature 5 Description',
                'description' => 'Description for fifth feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_5_icon',
                'value' => 'ph-broadcast',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 5 Icon',
                'description' => 'Phosphor icon class for fifth feature',
                'is_public' => true
            ],
            // Feature 6
            [
                'key' => 'landing_feature_6_title',
                'value' => 'End-to-End Encryption',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 6 Title',
                'description' => 'Title for sixth feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_6_description',
                'value' => 'Your conversations are protected with industry-standard encryption. Your data stays private.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Feature 6 Description',
                'description' => 'Description for sixth feature',
                'is_public' => true
            ],
            [
                'key' => 'landing_feature_6_icon',
                'value' => 'ph-shield-check',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Feature 6 Icon',
                'description' => 'Phosphor icon class for sixth feature',
                'is_public' => true
            ],

            // ========== LANDING PAGE - COMMUNITY SECTION ==========
            [
                'key' => 'landing_community_title',
                'value' => 'Join Our Growing Community',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Community Section Title',
                'description' => 'Title for the community/stats section',
                'is_public' => true
            ],
            [
                'key' => 'landing_community_description',
                'value' => 'Thousands of farmers trust our platform every day to connect and collaborate.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Community Section Description',
                'description' => 'Description for community section',
                'is_public' => true
            ],

            // ========== LANDING PAGE - DOWNLOAD SECTION ==========
            [
                'key' => 'landing_download_badge',
                'value' => 'Available on all platforms',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Download Badge',
                'description' => 'Badge text for download section',
                'is_public' => true
            ],
            [
                'key' => 'landing_download_title',
                'value' => 'Download Today',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Download Title',
                'description' => 'Title for download section (app name is prepended)',
                'is_public' => true
            ],
            [
                'key' => 'landing_download_description',
                'value' => 'Get started in minutes. Download our app and join the largest agricultural community in the world.',
                'type' => 'text',
                'group' => 'landing',
                'label' => 'Download Description',
                'description' => 'Description for download section',
                'is_public' => true
            ],
            [
                'key' => 'landing_download_note',
                'value' => 'Free to download • No credit card required',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Download Note',
                'description' => 'Small note below download buttons',
                'is_public' => true
            ],
            [
                'key' => 'landing_download_image',
                'value' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400&h=500&fit=crop',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Download Section Image',
                'description' => 'Image for download section',
                'is_public' => true
            ],
            [
                'key' => 'landing_appstore_url',
                'value' => '#',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'App Store URL',
                'description' => 'Link to App Store listing',
                'is_public' => true
            ],
            [
                'key' => 'landing_playstore_url',
                'value' => '#',
                'type' => 'string',
                'group' => 'landing',
                'label' => 'Play Store URL',
                'description' => 'Link to Google Play Store listing',
                'is_public' => true
            ],

            // ========== INTEGRATIONS SECTION ==========
            [
                'key' => 'agora_app_id',
                'value' => '',
                'type' => 'string',
                'group' => 'integrations',
                'label' => 'Agora App ID',
                'description' => 'The App ID for your Agora RTC project',
                'is_public' => false
            ],
            [
                'key' => 'agora_app_certificate',
                'value' => '',
                'type' => 'string',
                'group' => 'integrations',
                'label' => 'Agora App Certificate',
                'description' => 'The App Certificate for your Agora RTC project (required for secure tokens)',
                'is_public' => false
            ],
            [
                'key' => 'firebase_credentials',
                'value' => 'storage/app/firebase/firebase-credentials.json',
                'type' => 'string',
                'group' => 'integrations',
                'label' => 'Firebase Credentials Path',
                'description' => 'Relative path to your Firebase Admin SDK service account JSON file',
                'is_public' => false
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
