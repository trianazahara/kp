<header class="bg-white shadow-sm px-4 py-3 flex justify-between items-center h-16">
    <!-- Left side - Mobile Menu Toggle -->
    <div class="md:hidden">
        <button type="button" onclick="toggleSidebar()" class="text-gray-500 hover:text-primary focus:outline-none">
            <i class="fas fa-bars text-lg"></i>
        </button>
    </div>
    
    <!-- Sound toggle button on large screens -->
    <div class="hidden md:block">
        <button type="button" class="text-gray-500 hover:text-primary focus:outline-none">
            <i class="fas fa-volume-up text-lg"></i>
        </button>
    </div>
    
    <!-- Right side - Notification & Profile -->
    <div class="flex items-center space-x-4">
        <!-- Notification Dropdown -->
        <div class="relative">
            <button type="button" class="text-gray-500 hover:text-primary focus:outline-none relative">
                <i class="fas fa-bell text-lg"></i>
                @php
                    // Definisikan variabel notifikasi dengan nilai default jika belum ada
                    $notificationCount = $notificationCount ?? 0;
                    $notifications = $notifications ?? [];
                @endphp
                
                @if($notificationCount > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs w-4 h-4 flex items-center justify-center">
                    {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                </span>
                @endif
            </button>
            
            <!-- Notification Dropdown Menu -->
            <div class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg overflow-hidden z-50 hidden" id="notification-dropdown">
                <div class="py-2 px-4 bg-gray-50 border-b flex justify-between items-center">
                    <span class="font-medium text-gray-700">Notifikasi</span>
                    @if($notificationCount > 0)
                    <a href="{{ route('notifications.markAllAsRead') }}" class="text-xs text-primary hover:underline">Tandai semua dibaca</a>
                    @endif
                </div>
                
                <div class="max-h-64 overflow-y-auto">
                    @forelse($notifications as $notification)
                    <a href="{{ route('notifications.show', $notification->id_notifikasi) }}" class="block px-4 py-3 hover:bg-gray-50 border-b {{ $notification->dibaca ? '' : 'bg-green-50' }}">
                        <div class="flex">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-primary">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-gray-700">{{ $notification->pesan }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="px-4 py-6 text-center text-gray-500">
                        <p>Tidak ada notifikasi baru</p>
                    </div>
                    @endforelse
                </div>
                
                @if($notificationCount > 5)
                <div class="py-2 px-4 bg-gray-50 border-t text-center">
                    <a href="{{ route('notifications.index') }}" class="text-xs text-primary hover:underline">Lihat semua notifikasi</a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Profile Dropdown -->
        <div class="relative" id="profile-menu">
            <button type="button" class="flex items-center space-x-3 focus:outline-none">
                <div class="md:block hidden text-right">
                    <div class="text-sm font-medium text-gray-700">{{ auth()->user()->nama }}</div>
                    <div class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
                <div class="h-9 w-9 rounded-full overflow-hidden bg-primary text-white flex items-center justify-center">
                    @if(auth()->user()->profile_picture)
                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="Profile" class="h-full w-full object-cover">
                    @else
                        <span class="text-sm font-medium">{{ substr(auth()->user()->nama, 0, 1) }}</span>
                    @endif
                </div>
            </button>
            
            <!-- Profile Dropdown Menu -->
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg overflow-hidden z-50 hidden" id="profile-dropdown">
                <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-user mr-2 text-gray-500"></i> Profil Saya
                </a>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-sign-out-alt mr-2 text-gray-500"></i> Keluar
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</header>

<script>
    // Toggle notification dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const notificationButton = document.querySelector('#notification-dropdown').previousElementSibling;
        const notificationDropdown = document.querySelector('#notification-dropdown');
        
        notificationButton.addEventListener('click', function() {
            notificationDropdown.classList.toggle('hidden');
            // Hide profile dropdown when showing notification dropdown
            document.querySelector('#profile-dropdown').classList.add('hidden');
        });
        
        // Toggle profile dropdown
        const profileButton = document.querySelector('#profile-menu > button');
        const profileDropdown = document.querySelector('#profile-dropdown');
        
        profileButton.addEventListener('click', function() {
            profileDropdown.classList.toggle('hidden');
            // Hide notification dropdown when showing profile dropdown
            notificationDropdown.classList.add('hidden');
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!notificationButton.contains(event.target) && !notificationDropdown.contains(event.target)) {
                notificationDropdown.classList.add('hidden');
            }
            
            if (!profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.add('hidden');
            }
        });
    });
</script>