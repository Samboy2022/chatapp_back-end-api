@extends('layouts.admin')

@section('title', 'My Profile')
@section('page-title', 'Profile Settings')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900">My Profile</h2>
            <p class="text-gray-500 mt-1">Manage your account settings and update your profile information</p>
        </div>

        <!-- Status Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-100 rounded-xl flex items-start gap-3">
                <i class="ph-bold ph-check-circle text-green-600 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium text-green-800">Success</p>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                <i class="ph-bold ph-warning-circle text-red-600 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium text-red-800">Error</p>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                <i class="ph-bold ph-warning-circle text-red-600 text-xl mt-0.5"></i>
                <div>
                    <p class="text-sm font-medium text-red-800">Validation Error</p>
                    <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Profile Card -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm text-green-600">
                    <i class="ph ph-user-circle text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Profile Information</h3>
                    <p class="text-xs text-gray-500">Update your account's profile information and email address</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <!-- Avatar Section -->
                <div class="flex items-center gap-6 mb-8 pb-8 border-b border-gray-100">
                    <div class="relative">
                        <div id="avatarPreview" class="w-24 h-24 rounded-2xl border-2 border-gray-200 flex items-center justify-center bg-gray-50 overflow-hidden">
                            @if($admin->avatar_url)
                                <img src="{{ $admin->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-green-700 flex items-center justify-center">
                                    <span class="text-white text-3xl font-bold">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-1">Profile Photo</h4>
                        <p class="text-sm text-gray-500 mb-3">JPG, PNG or GIF. Max 2MB.</p>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:border-green-600 hover:text-green-700 transition-all text-sm font-medium cursor-pointer">
                                <i class="ph ph-upload-simple"></i>
                                Change
                                <input type="file" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                            </label>
                            @if($admin->avatar_url)
                                <button type="button" onclick="document.getElementById('removeAvatar').value = '1'; document.getElementById('avatarPreview').innerHTML = '<div class=\'w-full h-full bg-green-700 flex items-center justify-center\'><span class=\'text-white text-3xl font-bold\'>{{ strtoupper(substr($admin->name, 0, 1)) }}</span></div>';" 
                                        class="flex items-center gap-2 px-4 py-2 bg-white border border-red-200 text-red-600 rounded-xl hover:bg-red-50 transition-all text-sm font-medium">
                                    <i class="ph ph-trash"></i>
                                    Remove
                                </button>
                            @endif
                        </div>
                        <input type="hidden" name="remove_avatar" id="removeAvatar" value="0">
                    </div>
                </div>

                <!-- Name Field -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-user text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $admin->name) }}"
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 focus:bg-white transition-all text-sm @error('name') border-red-300 bg-red-50 @enderror"
                               placeholder="Enter your full name"
                               required>
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                            <i class="ph ph-warning-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-envelope text-gray-400"></i>
                        </div>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $admin->email) }}"
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 focus:bg-white transition-all text-sm @error('email') border-red-300 bg-red-50 @enderror"
                               placeholder="Enter your email address"
                               required>
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                            <i class="ph ph-warning-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="flex items-center gap-2 px-6 py-3 bg-green-700 hover:bg-green-800 text-white font-semibold rounded-xl transition-all shadow-lg shadow-green-700/30">
                        <i class="ph ph-floppy-disk"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Card -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                <div class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm text-orange-600">
                    <i class="ph ph-lock text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Update Password</h3>
                    <p class="text-xs text-gray-500">Ensure your account is using a secure password</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.profile.password') }}" class="p-6">
                @csrf
                @method('PUT')

                <!-- Current Password -->
                <div class="mb-6">
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-lock text-gray-400"></i>
                        </div>
                        <input type="password" 
                               id="current_password" 
                               name="current_password"
                               class="w-full pl-11 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 focus:bg-white transition-all text-sm @error('current_password') border-red-300 bg-red-50 @enderror"
                               placeholder="Enter current password"
                               required>
                        <button type="button" onclick="togglePasswordVisibility('current_password')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <i class="ph ph-eye text-gray-400 hover:text-gray-600 transition-colors" id="current_password_icon"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                            <i class="ph ph-warning-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-lock-key text-gray-400"></i>
                        </div>
                        <input type="password" 
                               id="password" 
                               name="password"
                               class="w-full pl-11 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 focus:bg-white transition-all text-sm @error('password') border-red-300 bg-red-50 @enderror"
                               placeholder="Enter new password (min 8 characters)"
                               required>
                        <button type="button" onclick="togglePasswordVisibility('password')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <i class="ph ph-eye text-gray-400 hover:text-gray-600 transition-colors" id="password_icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                            <i class="ph ph-warning-circle"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="ph ph-lock-key text-gray-400"></i>
                        </div>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation"
                               class="w-full pl-11 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 focus:bg-white transition-all text-sm"
                               placeholder="Confirm new password"
                               required>
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <i class="ph ph-eye text-gray-400 hover:text-gray-600 transition-colors" id="password_confirmation_icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="flex items-center gap-2 px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-orange-600/30">
                        <i class="ph ph-key"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Account Info -->
        <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <i class="ph ph-info text-gray-400"></i>
                <span>Account created on {{ $admin->created_at->format('F j, Y') }} • Last updated {{ $admin->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').innerHTML = `<img src="${e.target.result}" alt="Avatar" class="w-full h-full object-cover">`;
            document.getElementById('removeAvatar').value = '0';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('ph-eye');
        icon.classList.add('ph-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('ph-eye-slash');
        icon.classList.add('ph-eye');
    }
}
</script>
@endpush
