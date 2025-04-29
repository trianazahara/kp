<?php
// Inisialisasi variabel dengan nilai default jika tidak ada
$notificationCount = $notificationCount ?? 0;
$notifications = $notifications ?? [];
?>

<header class="fixed top-0 right-0 left-64 bg-white border-b z-8">
    <div class="flex justify-end items-center p-1 mr-4">
        <div class="flex items-center gap-3">
            <!-- Notification Dropdown -->
            <div class="relative" id="notification-dropdown">
                <button
                    type="button"
                    class="relative p-2 rounded-full hover:bg-gray-100 focus:outline-none"
                    onclick="toggleNotificationDropdown()"
                >
                    <i class="fas fa-bell text-lg"></i>
                    @if($notificationCount > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                    </span>
                    @endif
                </button>

                <!-- Notification Dropdown Menu -->
                <div class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 hidden" id="notification-menu">
                    <div class="px-4 py-2 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Notifikasi</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        @if(count($notifications) === 0)
                        <div class="p-4 text-center text-gray-500">
                            Tidak ada notifikasi
                        </div>
                        @else
                        @foreach($notifications as $notification)
                        <a
                            href="{{ route('notifications.show', $notification->id_notifikasi) }}"
                            class="block p-4 border-b border-gray-100 hover:bg-gray-50 {{ $notification->dibaca ? '' : 'bg-blue-50' }}"
                        >
                            <div class="font-semibold">{{ $notification->judul }}</div>
                            <div class="text-sm text-gray-600">{{ $notification->pesan }}</div>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ \Carbon\Carbon::parse($notification->created_at)->toLocaleString('id-ID') }}
                            </div>
                        </a>
                        @endforeach
                        @endif
                    </div>
                    @if(count($notifications) > 0)
                    <div class="p-2 text-center border-t border-gray-200">
                        <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Profile Dropdown -->
            <div class="relative" id="profile-menu">
                <button
                    type="button"
                    class="flex items-center gap-3 cursor-pointer focus:outline-none"
                    onclick="toggleProfileDropdown()"
                >
                    <div class="text-right">
                        <p class="text-gray-700">{{ auth()->user()->nama }}</p>
                        <p class="text-gray-500 text-sm">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <div class="flex flex-col items-center">
                        @if(auth()->user()->profile_picture)
                            <img
                                src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                                alt="Profile"
                                class="w-10 h-10 rounded-full object-cover border border-gray-200"
                                onerror="this.onerror=null; this.src='{{ asset('images/default-avatar.png') }}'"
                            />
                        @else
                            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center">
                                <span class="text-sm font-medium">{{ substr(auth()->user()->nama, 0, 1) }}</span>
                            </div>
                        @endif
                        <i class="fas fa-chevron-down text-gray-400 text-xs transform transition-transform duration-200 mt-1" id="profile-arrow"></i>
                    </div>
                </button>

                <!-- Profile Dropdown Menu -->
                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 hidden" id="profile-dropdown">
                    <a
                        href="{{ route('settings.index') }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                        <i class="fas fa-user mr-2 text-gray-500"></i> Profil Saya
                    </a>
                    <a
                        href="#"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                    >
                        <i class="fas fa-sign-out-alt mr-2 text-gray-500"></i> Keluar
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Toggle notification dropdown
    function toggleNotificationDropdown() {
        const notificationMenu = document.getElementById('notification-menu');
        const profileMenu = document.getElementById('profile-dropdown');
        notificationMenu.classList.toggle('hidden');
        profileMenu.classList.add('hidden');
        document.getElementById('profile-arrow').classList.remove('rotate-180');
    }

    // Toggle profile dropdown
    function toggleProfileDropdown() {
        const profileMenu = document.getElementById('profile-dropdown');
        const notificationMenu = document.getElementById('notification-menu');
        const profileArrow = document.getElementById('profile-arrow');
        profileMenu.classList.toggle('hidden');
        notificationMenu.classList.add('hidden');
        profileArrow.classList.toggle('rotate-180');
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notificationDropdown = document.getElementById('notification-dropdown');
        const profileDropdown = document.getElementById('profile-menu');
        const notificationMenu = document.getElementById('notification-menu');
        const profileMenu = document.getElementById('profile-dropdown');

        if (notificationDropdown && !notificationDropdown.contains(event.target)) {
            if (notificationMenu) {
                notificationMenu.classList.add('hidden');
            }
        }

        if (profileDropdown && !profileDropdown.contains(event.target)) {
            if (profileMenu) {
                profileMenu.classList.add('hidden');
            }
            const profileArrow = document.getElementById('profile-arrow');
            if (profileArrow) {
                profileArrow.classList.remove('rotate-180');
            }
        }
    });

    // Debug function untuk membantu troubleshooting
    function checkProfileImage() {
        const user = {
            nama: "{{ auth()->user()->nama }}",
            profile_picture: "{{ auth()->user()->profile_picture }}",
            profile_path: "{{ auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : 'tidak ada' }}"
        };
        console.log('User data:', user);
    }
    
    // Panggil fungsi ini saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        checkProfileImage();
    });
</script>