@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'Users Management')

@section('content')
    <!-- Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <!-- Search & Filter Toggle -->
        <div class="flex items-center gap-2 flex-1 max-w-2xl">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                <input type="text" 
                       form="filter-form"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search users by name, email or phone..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 transition-all text-sm">
            </div>
            <button type="button" 
                    onclick="document.getElementById('filter-panel').classList.toggle('hidden')"
                    class="p-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-700 transition-colors text-gray-600">
                <i class="ph ph-funnel text-lg"></i>
            </button>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.export', request()->query()) }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl hover:border-green-600 hover:text-green-600 transition-all text-sm font-medium text-gray-700">
                <i class="ph ph-download-simple text-lg"></i>
                <span class="hidden sm:inline">Export</span>
            </a>
            <a href="{{ route('admin.users.create') }}" 
               class="flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl transition-all text-sm font-medium shadow-lg shadow-green-700/30">
                <i class="ph ph-plus text-lg"></i>
                <span class="hidden sm:inline">Add User</span>
            </a>
        </div>
    </div>

    <!-- Filter Panel (Hidden by default) -->
    <div id="filter-panel" class="hidden bg-white border border-gray-100 rounded-2xl p-5 mb-6 shadow-sm">
        <form id="filter-form" method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Preserve search if typed in main bar -->
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Role</label>
                <select name="role" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Joined After</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-600 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors text-sm font-medium">
                    Apply Filters
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover ring-2 ring-white shadow-sm">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-700 to-green-500 flex items-center justify-center text-white font-semibold shadow-sm shadow-green-700/20">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $user->is_admin ? 'bg-purple-50 text-purple-600 border border-purple-100' : 'bg-gray-50 text-gray-600 border border-gray-100' }}">
                                <i class="ph {{ $user->is_admin ? 'ph-shield-check' : 'ph-user' }}"></i>
                                {{ $user->is_admin ? 'Admin' : 'User' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $user->is_banned ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100' }}">
                                <i class="ph {{ $user->is_banned ? 'ph-prohibit' : 'ph-check-circle' }}"></i>
                                {{ $user->is_banned ? 'Banned' : 'Active' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ $user->created_at->format('M j, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $user->created_at->format('g:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($user->last_seen_at)
                                <div class="flex items-center gap-1.5">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $user->last_seen_at->diffInMinutes(now()) < 5 ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                    <span class="text-sm text-gray-600">{{ $user->last_seen_at->diffForHumans() }}</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-400">Never</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 transition-opacity">
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="p-2 text-gray-500 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors"
                                   title="View Details">
                                    <i class="ph ph-eye text-lg"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                   title="Edit User">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Delete User"
                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="ph ph-users text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No Users Found</h3>
                                <p class="text-sm text-gray-500 mb-4">Try adjusting your search or filters</p>
                                <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition-colors text-sm font-medium">
                                    Add New User
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $users->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
    dropdowns.forEach(dropdown => {
        if (!dropdown.contains(e.target) && !e.target.closest('[onclick*="toggleDropdown"]')) {
            dropdown.classList.add('hidden');
        }
    });
});

function toggleDropdown(userId) {
    const dropdown = document.getElementById(`dropdown-${userId}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');

    // Close all other dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== `dropdown-${userId}`) {
            d.classList.add('hidden');
        }
    });

    // Toggle current dropdown
    dropdown.classList.toggle('hidden');
}

function toggleBlock(userId, action) {
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}/toggle-block`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PATCH';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function resetPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}/reset-password`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'POST';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

