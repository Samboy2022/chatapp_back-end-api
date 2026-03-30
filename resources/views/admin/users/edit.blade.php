@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
    <div class="max-w-3xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-green-700 transition-colors">
                <i class="ph ph-arrow-left text-lg"></i>
                <span>Back to User Profile</span>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-lg font-semibold text-gray-900">Edit User Information</h2>
                <p class="text-sm text-gray-500 mt-1">Update the user's account details</p>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Avatar Upload -->
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div id="avatar-preview" class="w-20 h-20 rounded-full bg-gradient-to-br from-green-700 to-green-500 flex items-center justify-center text-white text-2xl font-semibold shadow-lg overflow-hidden">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        <label for="avatar" class="absolute -bottom-1 -right-1 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center cursor-pointer hover:bg-gray-50 transition-colors shadow-sm">
                            <i class="ph ph-camera text-gray-600"></i>
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Profile Photo</h3>
                        <p class="text-sm text-gray-500">JPG, PNG. Max 2MB</p>
                    </div>
                </div>

                @error('avatar')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all @error('name') border-red-300 @enderror"
                           placeholder="Enter full name">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all @error('email') border-red-300 @enderror"
                           placeholder="user@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone Number -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">Country Code</label>
                        <input type="text" id="country_code" name="country_code" value="{{ old('country_code', $user->country_code) }}"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all"
                               placeholder="+1">
                    </div>
                    <div class="col-span-2">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all @error('phone_number') border-red-300 @enderror"
                               placeholder="1234567890">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Password (Optional) -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password <span class="text-gray-400">(optional)</span></label>
                        <input type="password" id="password" name="password"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all @error('password') border-red-300 @enderror"
                               placeholder="Leave blank to keep current">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all"
                               placeholder="Confirm new password">
                    </div>
                </div>

                <!-- About -->
                <div>
                    <label for="about" class="block text-sm font-medium text-gray-700 mb-2">About</label>
                    <textarea id="about" name="about" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all resize-none"
                              placeholder="Brief description about the user...">{{ old('about', $user->about) }}</textarea>
                    @error('about')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Privacy Settings -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Privacy Settings</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="last_seen_privacy" class="block text-sm font-medium text-gray-700 mb-2">Last Seen</label>
                            <select id="last_seen_privacy" name="last_seen_privacy"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all">
                                <option value="everyone" {{ old('last_seen_privacy', $user->last_seen_privacy) === 'everyone' ? 'selected' : '' }}>Everyone</option>
                                <option value="contacts" {{ old('last_seen_privacy', $user->last_seen_privacy) === 'contacts' ? 'selected' : '' }}>Contacts</option>
                                <option value="nobody" {{ old('last_seen_privacy', $user->last_seen_privacy) === 'nobody' ? 'selected' : '' }}>Nobody</option>
                            </select>
                        </div>
                        <div>
                            <label for="profile_photo_privacy" class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                            <select id="profile_photo_privacy" name="profile_photo_privacy"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all">
                                <option value="everyone" {{ old('profile_photo_privacy', $user->profile_photo_privacy) === 'everyone' ? 'selected' : '' }}>Everyone</option>
                                <option value="contacts" {{ old('profile_photo_privacy', $user->profile_photo_privacy) === 'contacts' ? 'selected' : '' }}>Contacts</option>
                                <option value="nobody" {{ old('profile_photo_privacy', $user->profile_photo_privacy) === 'nobody' ? 'selected' : '' }}>Nobody</option>
                            </select>
                        </div>
                        <div>
                            <label for="about_privacy" class="block text-sm font-medium text-gray-700 mb-2">About</label>
                            <select id="about_privacy" name="about_privacy"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all">
                                <option value="everyone" {{ old('about_privacy', $user->about_privacy) === 'everyone' ? 'selected' : '' }}>Everyone</option>
                                <option value="contacts" {{ old('about_privacy', $user->about_privacy) === 'contacts' ? 'selected' : '' }}>Contacts</option>
                                <option value="nobody" {{ old('about_privacy', $user->about_privacy) === 'nobody' ? 'selected' : '' }}>Nobody</option>
                            </select>
                        </div>
                        <div>
                            <label for="status_privacy" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status_privacy" name="status_privacy"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all">
                                <option value="everyone" {{ old('status_privacy', $user->status_privacy) === 'everyone' ? 'selected' : '' }}>Everyone</option>
                                <option value="contacts" {{ old('status_privacy', $user->status_privacy) === 'contacts' ? 'selected' : '' }}>Contacts</option>
                                <option value="close_friends" {{ old('status_privacy', $user->status_privacy) === 'close_friends' ? 'selected' : '' }}>Close Friends</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="read_receipts_enabled" value="0">
                            <input type="checkbox" name="read_receipts_enabled" value="1" 
                                   {{ old('read_receipts_enabled', $user->read_receipts_enabled ?? true) ? 'checked' : '' }}
                                   class="w-5 h-5 rounded border-gray-300 text-green-700 focus:ring-green-200">
                            <span class="text-sm text-gray-700">Enable Read Receipts</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.show', $user) }}" class="px-6 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-colors font-medium shadow-lg shadow-green-700/30">
                        <i class="ph ph-floppy-disk mr-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview');
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush


