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
     * Find a user by phone number or email without adding them.
     *
     * The app calls this first so it can tell the difference between
     * "they're on Farmers Network — start chatting" and "not here yet — send
     * them an invite", instead of failing with a generic error.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required_without:email|nullable|string|max:32',
            'email' => 'required_without:phone_number|nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a phone number or an email address',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $this->findUserByIdentifier($request->phone_number, $request->email);

        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => ['on_network' => false, 'user' => null],
                'message' => 'That person is not on Farmers Network yet',
            ]);
        }

        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'That is your own account',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'on_network' => true,
                'user' => $user->only([
                    'id', 'name', 'phone_number', 'country_code', 'email', 'avatar_url', 'is_online',
                ]),
                'already_added' => Contact::where('user_id', Auth::id())
                    ->where('contact_user_id', $user->id)
                    ->exists(),
            ],
            'message' => 'User found',
        ]);
    }

    /**
     * Add someone to the signed-in user's contacts by phone number or email.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required_without:email|nullable|string|max:32',
            'email' => 'required_without:phone_number|nullable|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a phone number or an email address',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $this->findUserByIdentifier($request->phone_number, $request->email);

        if (!$user) {
            // 404 with a machine-readable flag so the client can offer to
            // invite rather than showing a failure.
            return response()->json([
                'success' => false,
                'data' => ['on_network' => false],
                'message' => 'That person is not on Farmers Network yet — send them an invite',
            ], 404);
        }

        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot add yourself as a contact',
            ], 422);
        }

        $contact = Contact::updateOrCreate(
            ['user_id' => Auth::id(), 'contact_user_id' => $user->id],
            [
                // Prefer the name the adder typed; fall back to the profile name.
                'contact_name' => $request->filled('name') ? $request->name : $user->name,
                'added_at' => now(),
            ]
        );

        $contact->load('contactUser:id,name,phone_number,country_code,avatar_url,last_seen_at,is_online');

        return response()->json([
            'success' => true,
            'data' => ['contact' => $contact, 'on_network' => true],
            'message' => "{$contact->contact_name} has been added to your contacts",
        ], 201);
    }

    /**
     * Rename a contact.
     *
     * This only changes how that person appears in *this* user's contact list
     * and chat headers — their actual profile name is untouched, exactly like
     * renaming someone in a phone's address book.
     */
    public function rename(Request $request, $contactUserId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Enter a name for this contact',
                'errors' => $validator->errors(),
            ], 422);
        }

        $contact = Contact::where('user_id', Auth::id())
            ->where('contact_user_id', $contactUserId)
            ->first();

        if (!$contact) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found',
            ], 404);
        }

        $contact->update(['contact_name' => trim($request->contact_name)]);
        $contact->load('contactUser:id,name,phone_number,country_code,avatar_url,about,last_seen_at,is_online');

        return response()->json([
            'success' => true,
            'data' => ['contact' => $contact],
            'message' => 'Contact renamed',
        ]);
    }

    /**
     * Remove someone from the signed-in user's contacts.
     */
    public function destroy($contactUserId): JsonResponse
    {
        $deleted = Contact::where('user_id', Auth::id())
            ->where('contact_user_id', $contactUserId)
            ->delete();

        return response()->json([
            'success' => (bool) $deleted,
            'message' => $deleted ? 'Contact removed' : 'Contact not found',
        ], $deleted ? 200 : 404);
    }

    /**
     * Resolve a user from a phone number or email.
     *
     * Phone numbers are matched on their trailing digits so a number stored as
     * `08031234567` still matches one entered as `+234 803 123 4567` — people
     * do not type country codes consistently, and a strict equality check
     * would make the feature look broken.
     */
    private function findUserByIdentifier(?string $phoneNumber, ?string $email): ?User
    {
        if (filled($email)) {
            $user = User::where('email', trim(strtolower($email)))->first();
            if ($user) {
                return $user;
            }
        }

        if (blank($phoneNumber)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phoneNumber);
        if (strlen($digits) < 7) {
            return null;
        }

        // Compare the last 9 digits — enough to be unique in practice while
        // tolerating a leading 0 or a country code on either side.
        $tail = substr($digits, -9);

        return User::whereRaw(
            "RIGHT(REGEXP_REPLACE(COALESCE(phone_number, ''), '[^0-9]', ''), 9) = ?",
            [$tail]
        )->first();
    }

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
                // `phone_number` lives on users, not contacts — querying it
                // here threw a "column not found" and broke contact search.
                $contactsQuery->where(function ($query) use ($search) {
                    $query->where('contact_name', 'LIKE', "%{$search}%")
                          ->orWhereHas('contactUser', function ($q) use ($search) {
                              $q->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('phone_number', 'LIKE', "%{$search}%")
                                ->orWhere('email', 'LIKE', "%{$search}%");
                          });
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
            'contacts' => 'required|array|max:2000',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.phone' => 'required|string|max:32',
            'contacts.*.email' => 'nullable|email|max:255',
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
                // Exact string matching used to be the rule here, so a number
                // saved on the phone as "0803 123 4567" never matched an
                // account stored as "+2348031234567" — sync silently found
                // nobody. Reuse the same tolerant matcher as lookup/add.
                $appUser = $this->findUserByIdentifier(
                    $deviceContact['phone'],
                    $deviceContact['email'] ?? null
                );

                if (!$appUser || $appUser->id === Auth::id()) {
                    continue;
                }

                $existingContact = Contact::where('user_id', Auth::id())
                    ->where('contact_user_id', $appUser->id)
                    ->first();

                if ($existingContact) {
                    continue;
                }

                $contact = Contact::create([
                    'user_id' => Auth::id(),
                    'contact_user_id' => $appUser->id,
                    // Prefer the name as saved on the device — that's what the
                    // owner recognises.
                    'contact_name' => $deviceContact['name'],
                    'added_at' => now(),
                ]);

                $contact->load('contactUser:id,name,phone_number,avatar_url,is_online');

                $newContactsCount++;
                $syncedContacts[] = $contact;
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
