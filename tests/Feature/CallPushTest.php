<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\OneSignalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The incoming-call push must be a visible notification: OneSignal only hands
 * pushes to Flutter in the foreground, so a silent data-only ring never
 * reached a backgrounded or closed app. The Android app's native extension
 * turns it into the full-screen call UI.
 */
class CallPushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('onesignal_app_id', 'test-app');
        Setting::set('onesignal_rest_api_key', 'test-key');
        Http::fake(['api.onesignal.com/*' => Http::response(['id' => 'n1', 'recipients' => 1])]);
    }

    public function test_incoming_call_push_is_visible_and_carries_the_call()
    {
        $sent = app(OneSignalService::class)->sendCallNotification('42', [
            'type' => 'incoming_call',
            'call_id' => '7',
            'caller_name' => 'Ada',
            'call_type' => 'video',
        ]);

        $this->assertTrue($sent);
        Http::assertSent(function (Request $request) {
            return $request['headings'] === ['en' => 'Ada']
                && $request['contents'] === ['en' => 'Incoming video call']
                && $request['data']['type'] === 'incoming_call'
                && $request['data']['call_id'] === '7'
                && $request['priority'] === 10
                && !isset($request['android_background_data']);
        });
    }

    public function test_call_cancellations_stay_silent()
    {
        app(OneSignalService::class)->sendCallNotification('42', [
            'type' => 'call_ended',
            'call_id' => '7',
        ]);

        Http::assertSent(fn (Request $request) => !isset($request['contents'])
            && !isset($request['headings'])
            && $request['data']['type'] === 'call_ended');
    }
}
