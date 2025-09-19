<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Get user's contacts list
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);
            $search = $request->get('search');
            
            $contactsQuery = Contact::where('user_id', Auth::id())
                ->with(['contactUser:id,name,phone_number,country_code,avatar_url,last_seen_at,is_online'])
                ->orderBy('is_favorite', 'desc')
                ->orderBy('contact_name');

            if ($search) {
                $contactsQuery->where(function($query) use ($search) {
                    $query->where('contact_name', 'LIKE', "%{$search}%")
                          ->orWhere('phone_number', 'LIKE', "%{$search}%");
                });
            }

            $contacts = $contactsQuery->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $contacts,
                'message' => 'Contacts retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync device contacts with app users
     */
    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contacts' => 'required|array',
            'contacts.*.name' => 'required|string',
            'contacts.*.phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $deviceContacts = $request->contacts;
        $syncedContacts = [];
        $newContactsCount = 0;

        DB::beginTransaction();

        try {
            foreach ($deviceContacts as $deviceContact) {
                $cleanPhone = preg_replace('/[^0-9+]/', '', $deviceContact['phone']);

                $appUser = User::where('phone_number', $cleanPhone)->first();

                if ($appUser) {
                    $existingContact = Contact::where('user_id', Auth::id())
                        ->where('contact_user_id', $appUser->id)
                        ->first();

                    if (!$existingContact) {
                        $contact = Contact::create([
                            'user_id' => Auth::id(),
                            'contact_user_id' => $appUser->id,
                            'contact_name' => $deviceContact['name'],
                        ]);
                        $newContactsCount++;
                        $syncedContacts[] = $contact;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'synced_contacts' => $syncedContacts,
                    'total_synced' => count($deviceContacts),
                    'new_contacts' => $newContactsCount,
                    'app_users_found' => count($syncedContacts)
                ],
                'message' => 'Contacts synced successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error syncing contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Block a contact/user
     */
    public function block($contactId): JsonResponse
    {
        try {
            // Check if it's a contact or direct user
            $contact = Contact::where('user_id', Auth::id())
                ->where(function($query) use ($contactId) {
                    $query->where('id', $contactId)
                          ->orWhere('contact_user_id', $contactId);
                })
                ->first();

            $userToBlock = null;

            if ($contact && $contact->contact_user_id) {
                $userToBlock = User::find($contact->contact_user_id);
            } else {
                // Try to find user directly
                $userToBlock = User::find($contactId);
            }

            if (!$userToBlock) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            DB::beginTransaction();

            // Update or create contact record with blocked status
            $contactRecord = Contact::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'contact_user_id' => $userToBlock->id
                ],
                [
                    'contact_name' => $userToBlock->name,
                    'is_blocked' => true,
                    'added_at' => now()
                ]
            );

            // Also create reverse blocking record to prevent the blocked user from contacting this user
            Contact::updateOrCreate(
                [
                    'user_id' => $userToBlock->id,
                    'contact_user_id' => Auth::id()
                ],
                [
                    'contact_name' => Auth::user()->name,
                    'is_blocked' => true,
                    'added_at' => now()
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $contactRecord,
                'message' => 'User blocked successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error blocking user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unblock a contact/user
     */
    public function unblock($contactId): JsonResponse
    {
        try {
            // Find the blocked contact
            $contact = Contact::where('user_id', Auth::id())
                ->where(function($query) use ($contactId) {
                    $query->where('id', $contactId)
                          ->orWhere('contact_user_id', $contactId);
                })
                ->where('is_blocked', true)
                ->first();

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Blocked contact not found'
                ], 404);
            }

            DB::beginTransaction();

            // Unblock the contact
            $contact->update([
                'is_blocked' => false
            ]);

            // Remove reverse blocking record
            if ($contact->contact_user_id) {
                Contact::where('user_id', $contact->contact_user_id)
                    ->where('contact_user_id', Auth::id())
                    ->update(['is_blocked' => false]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $contact,
                'message' => 'User unblocked successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error unblocking user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle favorite status for a contact
     */
    public function toggleFavorite($contactId): JsonResponse
    {
        try {
            $contact = Contact::where('user_id', Auth::id())
                ->where(function($query) use ($contactId) {
                    $query->where('id', $contactId)
                          ->orWhere('contact_user_id', $contactId);
                })
                ->first();

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }

            $contact->update([
                'is_favorite' => !$contact->is_favorite
            ]);

            $contact->load('contactUser:id,name,phone_number,country_code,avatar_url,last_seen_at,is_online');

            return response()->json([
                'success' => true,
                'data' => $contact,
                'message' => $contact->is_favorite ? 'Contact added to favorites' : 'Contact removed from favorites'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating favorite status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get blocked contacts
     */
    public function getBlocked(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);
            
            $blockedContacts = Contact::where('user_id', Auth::id())
                ->where('is_blocked', true)
                ->with(['contactUser:id,name,phone_number,country_code,avatar_url'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $blockedContacts,
                'message' => 'Blocked contacts retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving blocked contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get favorite contacts
     */
    public function getFavorites(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 50);
            
            $favoriteContacts = Contact::where('user_id', Auth::id())
                ->where('is_favorite', true)
                ->with(['contactUser:id,name,phone_number,country_code,avatar_url,last_seen_at,is_online'])
                ->orderBy('contact_name')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $favoriteContacts,
                'message' => 'Favorite contacts retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving favorite contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for users by phone number or name
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:3'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = $request->get('query', '');
            $perPage = $request->get('per_page', 20);

            // Search for users by name or phone number
            $users = User::where('id', '!=', Auth::id())
                ->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('phone_number', 'LIKE', "%{$query}%");
                })
                ->select('id', 'name', 'phone_number', 'country_code', 'avatar_url', 'last_seen_at', 'is_online')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Users found successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching users: ' . $e->getMessage()
            ], 500);
        }
    }
}
