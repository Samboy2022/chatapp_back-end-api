@extends('layouts.admin')

@section('title', 'Create User')
@section('page-title', 'Create New User')

@section('content')
    <div class="max-w-3xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-green-700 transition-colors">
                <i class="ph ph-arrow-left text-lg"></i>
                <span>Back to Users</span>
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-lg font-semibold text-gray-900">User Information</h2>
                <p class="text-sm text-gray-500 mt-1">Fill in the details to create a new user account</p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Avatar Upload -->
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div id="avatar-preview" class="w-20 h-20 rounded-full bg-gradient-to-br from-green-700 to-green-500 flex items-center justify-center text-white text-2xl font-semibold shadow-lg">
                            <i class="ph ph-user text-3xl"></i>
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
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all @error('name') border-red-300 @enderror"
                           placeholder="Enter full name">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
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
                        <input type="text" id="country_code" name="country_code" value="{{ old('country_code', '+1') }}"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all"
                               placeholder="+1">
                    </div>
                    <div class="col-span-2">
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all @error('phone_number') border-red-300 @enderror"
                               placeholder="1234567890">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all @error('password') border-red-300 @enderror"
                               placeholder="Min 8 characters">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all"
                               placeholder="Confirm password">
                    </div>
                </div>

                <!-- About -->
                <div>
                    <label for="about" class="block text-sm font-medium text-gray-700 mb-2">About</label>
                    <textarea id="about" name="about" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all resize-none"
                              placeholder="Brief description about the user...">{{ old('about') }}</textarea>
                    @error('about')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-colors font-medium shadow-lg shadow-green-700/30">
                        <i class="ph ph-plus mr-2"></i>
                        Create User
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
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full rounded-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush


