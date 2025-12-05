<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - {{ $appSettings['app_name'] ?? 'Admin' }}</title>
    
    {{-- Vite CSS --}}
    @vite(['resources/css/admin.css'])
    
    {{-- Phosphor Icons --}}
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    @stack('styles')
  </head>
  <body class="bg-gray-50 font-sans antialiased">
    <!-- Overlay -->
    <div id="overlay" class="fixed inset-0 bg-black/50 z-40 hidden"></div>

    <!-- Sidebar -->
    <aside
      class="sidebar fixed left-0 top-0 h-full w-56 bg-white border-r border-gray-200 z-50 flex flex-col md:translate-x-0"
    >
      <!-- Logo -->
      <div class="h-14 flex items-center px-4 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center space-x-2">
          @if(isset($appSettings['logo_url']) && $appSettings['logo_url'])
            <img src="{{ $appSettings['logo_url'] }}" alt="Logo" class="w-7 h-7 rounded-lg object-cover">
          @else
            <div class="w-7 h-7 bg-green-700 rounded-lg flex items-center justify-center">
              <span class="text-white text-xs font-bold">{{ substr($appSettings['app_name'] ?? 'A', 0, 1) }}</span>
            </div>
          @endif
          <span class="font-bold text-green-900 text-sm">{{ $appSettings['app_name'] ?? 'Admin' }}</span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto py-3 px-3" style="scrollbar-width: thin;">
        <a
          href="{{ route('admin.dashboard') }}"
          class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph-bold ph-squares-four text-base"></i>
          <span>Dashboard</span>
        </a>
        <a
          href="{{ route('admin.users.index') }}"
          class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-users-three text-base"></i>
          <span>Users</span>
        </a>
        <a
          href="{{ route('admin.chats.index') }}"
          class="nav-item {{ request()->routeIs('admin.chats.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-chat-circle-dots text-base"></i>
          <span>Chats</span>
        </a>
        <a
          href="{{ route('admin.messages.index') }}"
          class="nav-item {{ request()->routeIs('admin.messages.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-envelope text-base"></i>
          <span>Messages</span>
        </a>
        <a
          href="{{ route('admin.statuses.index') }}"
          class="nav-item {{ request()->routeIs('admin.statuses.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-camera text-base"></i>
          <span>Status Updates</span>
        </a>
        <a
          href="{{ route('admin.calls.index') }}"
          class="nav-item {{ request()->routeIs('admin.calls.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-phone text-base"></i>
          <span>Calls</span>
        </a>
        <a
          href="{{ route('admin.reports.index') }}"
          class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-file-text text-base"></i>
          <span>Reports</span>
        </a>
        <a
          href="{{ route('admin.realtime-settings.index') }}"
          class="nav-item {{ request()->routeIs('admin.realtime-settings.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-broadcast text-base"></i>
          <span>Realtime</span>
        </a>
        <a
          href="{{ route('admin.settings.index') }}"
          class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg text-sm text-gray-700 mb-1"
        >
          <i class="ph ph-gear-six text-base"></i>
          <span>Settings</span>
        </a>
      </nav>

      <!-- User Section -->
      <div class="p-3 border-t border-gray-200">
        <div class="flex items-center space-x-2 p-2 bg-green-50 rounded-xl">
          @if(session('admin_user') && isset(session('admin_user')['avatar_url']))
            <img
                src="{{ session('admin_user')['avatar_url'] }}"
                alt="User"
                class="w-7 h-7 rounded-full border-2 border-green-700"
            />
          @else
            <div class="w-7 h-7 bg-green-700 rounded-full flex items-center justify-center text-white text-xs">
                {{ substr(session('admin_user')['name'] ?? 'A', 0, 1) }}
            </div>
          @endif
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-gray-900 truncate">{{ session('admin_user')['name'] ?? 'Admin User' }}</p>
            <p class="text-xs text-gray-500 truncate">{{ session('admin_user')['email'] ?? 'admin@example.com' }}</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="md:ml-56">
      <!-- Sticky Header -->
      <header
        class="sticky top-0 z-30 bg-white border-b border-gray-200 h-14 flex items-center px-4 md:px-6"
      >
        <div class="flex items-center justify-between w-full">
          <!-- Left: Menu + Search -->
          <div class="flex items-center space-x-3">
            <button
              id="menuToggle"
              class="md:hidden p-1.5 hover:bg-gray-100 rounded-lg transition"
            >
              <i class="ph-bold ph-list text-xl text-gray-700"></i>
            </button>
            <div class="hidden sm:flex items-center space-x-2 bg-gray-100 px-3 py-1.5 rounded-xl">
              <i class="ph ph-magnifying-glass text-gray-500"></i>
              <input
                type="text"
                placeholder="Search..."
                class="bg-transparent border-none outline-none text-sm text-gray-700 w-48"
              />
            </div>
          </div>

          <!-- Right: Actions -->
          <div class="flex items-center space-x-2">
            <button class="p-1.5 hover:bg-gray-100 rounded-lg transition relative">
              <i class="ph-bold ph-bell text-lg text-gray-700"></i>
              <span
                class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"
              ></span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative">
              <button
                id="profileBtn"
                class="flex items-center space-x-2 p-1.5 hover:bg-gray-100 rounded-lg transition"
              >
                @if(session('admin_user') && isset(session('admin_user')['avatar_url']))
                    <img
                    src="{{ session('admin_user')['avatar_url'] }}"
                    alt="User"
                    class="w-7 h-7 rounded-full border-2 border-green-700"
                    />
                @else
                    <div class="w-7 h-7 bg-green-700 rounded-full flex items-center justify-center text-white text-xs">
                        {{ substr(session('admin_user')['name'] ?? 'A', 0, 1) }}
                    </div>
                @endif
                <i class="ph ph-caret-down text-gray-600 text-sm hidden sm:block transition-transform" id="caretIcon"></i>
              </button>

              <!-- Dropdown -->
              <div
                id="profileDropdown"
                class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2"
              >
                <a
                  href="{{ route('admin.profile.index') }}"
                  class="dropdown-item flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 rounded-lg mx-2"
                >
                  <i class="ph ph-user text-base"></i>
                  <span>My Profile</span>
                </a>
                <a
                  href="{{ route('admin.settings.index') }}"
                  class="dropdown-item flex items-center space-x-2 px-3 py-2 text-sm text-gray-700 rounded-lg mx-2"
                >
                  <i class="ph ph-gear-six text-base"></i>
                  <span>Settings</span>
                </a>
                <div class="border-t border-gray-200 my-2"></div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button
                      type="submit"
                      class="w-full dropdown-item flex items-center space-x-2 px-3 py-2 text-sm text-red-600 rounded-lg mx-2"
                    >
                      <i class="ph ph-sign-out text-base"></i>
                      <span>Logout</span>
                    </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-4 md:p-6">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg animate-fadeIn">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="ph-bold ph-check-circle text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="text-green-400 hover:text-green-600" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg animate-fadeIn">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="ph-bold ph-warning-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                    <div class="ml-auto">
                        <button type="button" class="text-red-400 hover:text-red-600" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
      </main>
    </div>

    <script>
      // Mobile menu toggle
      const menuToggle = document.getElementById("menuToggle");
      const sidebar = document.querySelector(".sidebar");
      const overlay = document.getElementById("overlay");

      if (menuToggle) {
        menuToggle.addEventListener("click", () => {
          sidebar.classList.toggle("open");
          overlay.classList.toggle("hidden");
        });

        overlay.addEventListener("click", () => {
          sidebar.classList.remove("open");
          overlay.classList.add("hidden");
        });
      }

      // Profile dropdown
      const profileBtn = document.getElementById("profileBtn");
      const profileDropdown = document.getElementById("profileDropdown");
      const caretIcon = document.getElementById("caretIcon");

      if (profileBtn) {
        profileBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          profileDropdown.classList.toggle("show");
          if (caretIcon) {
            caretIcon.style.transform = profileDropdown.classList.contains("show")
              ? "rotate(180deg)"
              : "rotate(0deg)";
          }
        });

        document.addEventListener("click", () => {
          profileDropdown.classList.remove("show");
          if (caretIcon) {
            caretIcon.style.transform = "rotate(0deg)";
          }
        });
      }
    </script>
    @stack('scripts')
  </body>
</html>