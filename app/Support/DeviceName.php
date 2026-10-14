<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * A readable name for the device a login came from ("Android 14 phone"),
 * stored as the Sanctum token's name so Settings → Devices can list real
 * sessions. The app sends it as X-Device-Name.
 */
class DeviceName
{
    public static function from(Request $request): string
    {
        $name = trim(strip_tags((string) $request->header('X-Device-Name', '')));
        if ($name !== '') {
            return mb_substr($name, 0, 100);
        }

        $agent = (string) $request->userAgent();
        return match (true) {
            str_contains($agent, 'Android') => 'Android device',
            str_contains($agent, 'iPhone'), str_contains($agent, 'iOS') => 'iPhone',
            str_contains($agent, 'Dart') => 'Phone',
            default => 'Unknown device',
        };
    }
}
