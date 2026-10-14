<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Settings → Devices: the phones signed in to this account (one Sanctum
 * token each), and logging them out.
 */
class DeviceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentId = $user->currentAccessToken()?->id;

        $devices = $user->tokens()
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name === 'auth_token' ? 'Phone' : $token->name,
                'is_current' => $token->id === $currentId,
                'last_active_at' => ($token->last_used_at ?? $token->created_at)?->toISOString(),
                'signed_in_at' => $token->created_at?->toISOString(),
            ])
            ->values();

        return response()->json(['success' => true, 'data' => $devices]);
    }

    /** Log one other device out. */
    public function destroy(Request $request, $tokenId): JsonResponse
    {
        $user = $request->user();
        if ((int) $tokenId === (int) $user->currentAccessToken()?->id) {
            return response()->json([
                'success' => false,
                'message' => 'Use Log out to sign out of this phone'
            ], 422);
        }

        $deleted = $user->tokens()->where('id', $tokenId)->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? 'Device logged out' : 'Device not found',
        ], $deleted > 0 ? 200 : 404);
    }

    /** Log out every device except this one. */
    public function destroyOthers(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = $user->tokens()->where('id', '!=', $user->currentAccessToken()?->id)->delete();

        return response()->json([
            'success' => true,
            'data' => ['logged_out' => $count],
            'message' => $count === 1 ? '1 device logged out' : "{$count} devices logged out",
        ]);
    }
}
