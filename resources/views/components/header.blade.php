<?php
// resources/views/layouts/partials/header.blade.php

// Fetch notifications from the controller
$notificationCount = 0;
$notifications = [];

// Try to get notifications data if user is authenticated
if (auth()->check()) {
    try {
        // Get unread notification count
        $unreadCountResponse = app(App\Http\Controllers\NotificationController::class)
            ->getUnreadCount(request());
            
        if ($unreadCountResponse->getStatusCode() == 200) {
            $responseData = json_decode($unreadCountResponse->getContent());
            if ($responseData && isset($responseData->count)) {
                $notificationCount = $responseData->count;
            }
        }
        
        // Get latest notifications (limit to 5 for dropdown)
        $request = new Illuminate\Http\Request();
        $request->merge(['limit' => 5]);
        $notificationsResponse = app(App\Http\Controllers\NotificationController::class)
            ->getNotifications($request);
            
        if ($notificationsResponse->getStatusCode() == 200) {
            $responseData = json_decode($notificationsResponse->getContent());
            if ($responseData && isset($responseData->data)) {
                $notifications = $responseData->data;
            }
        }
    } catch (\Exception $e) {
        \Log::error('Error fetching notifications for header: ' . $e->getMessage());
        // Silently fail - use default empty values
    }
}
?>

<header class="fixed top-0 right-0 left-64 bg-white border-b z-50">
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
                    <span id="notification-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center {{ $notificationCount > 0 ? '' : 'hidden' }}">
                        {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                    </span>
                </button>

                <!-- Notification Dropdown Menu -->
                <div class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200 hidden" id="notification-menu">
                    <div class="px-4 py-2 border-b border-gray-200">
                        <h3 class="text-lg font-semibold">Notifikasi</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto" id="notification-container">
                        @if(count($notifications) === 0)
                        <div class="p-4 text-center text-gray-500" id="empty-notification">
                            Tidak ada notifikasi
                        </div>
                        @else
                        @foreach($notifications as $notification)
                        <a
                            href="javascript:void(0);"
                            onclick="markAsRead('{{ $notification->id_notifikasi }}')"
                            class="block p-4 border-b border-gray-100 hover:bg-gray-50 {{ $notification->dibaca ? '' : 'bg-blue-50' }}"
                        >
                            <div class="font-semibold">{{ $notification->judul }}</div>
                            <div class="text-sm text-gray-600">{{ $notification->pesan }}</div>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                            </div>
                        </a>
                        @endforeach
                        @endif
                    </div>
                    <div class="p-2 text-center border-t border-gray-200">
                        <a href="javascript:void(0);" onclick="markAllAsRead()" class="text-blue-600 hover:text-blue-800 text-sm font-medium mr-4">
                            Tandai Semua Dibaca
                        </a>
                        <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Lihat Semua
                        </a>
                    </div>
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

<style>
/* Add this style section to ensure popup appears on top */
#notification-dropdown,
#profile-menu {
    position: relative;
    z-index: 100;
}

#notification-menu,
#profile-dropdown {
    z-index: 1000;
}

/* Optional: add a backdrop to prevent interactions with underlying elements */
.dropdown-backdrop {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 90;
    background-color: transparent;
}
</style>

<script>
    // Toggle notification dropdown
    function toggleNotificationDropdown() {
        const notificationMenu = document.getElementById('notification-menu');
        const profileMenu = document.getElementById('profile-dropdown');
        
        // Toggle notification menu
        notificationMenu.classList.toggle('hidden');
        
        // Add/remove backdrop when opening/closing the dropdown
        if (!notificationMenu.classList.contains('hidden')) {
            // Create backdrop if it doesn't exist
            if (!document.querySelector('.dropdown-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.classList.add('dropdown-backdrop');
                document.body.appendChild(backdrop);
            }
            
            // Close profile dropdown if open
            if (profileMenu) {
                profileMenu.classList.add('hidden');
                document.getElementById('profile-arrow').classList.remove('rotate-180');
            }
            
            // Refresh notifications
            refreshNotifications();
        } else {
            // Remove backdrop when closing
            const backdrop = document.querySelector('.dropdown-backdrop');
            if (backdrop) backdrop.remove();
        }
    }
    
    // Toggle profile dropdown
    function toggleProfileDropdown() {
        const profileMenu = document.getElementById('profile-dropdown');
        const notificationMenu = document.getElementById('notification-menu');
        const profileArrow = document.getElementById('profile-arrow');
        
        // Toggle profile menu
        profileMenu.classList.toggle('hidden');
        
        // Add/remove backdrop when opening/closing the dropdown
        if (!profileMenu.classList.contains('hidden')) {
            // Create backdrop if it doesn't exist
            if (!document.querySelector('.dropdown-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.classList.add('dropdown-backdrop');
                document.body.appendChild(backdrop);
            }
            
            // Close notification dropdown if open
            notificationMenu.classList.add('hidden');
            
            // Toggle arrow rotation
            profileArrow.classList.toggle('rotate-180');
        } else {
            // Remove backdrop when closing
            const backdrop = document.querySelector('.dropdown-backdrop');
            if (backdrop) backdrop.remove();
            
            // Reset arrow rotation
            profileArrow.classList.remove('rotate-180');
        }
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notificationDropdown = document.getElementById('notification-dropdown');
        const profileDropdown = document.getElementById('profile-menu');
        const notificationMenu = document.getElementById('notification-menu');
        const profileMenu = document.getElementById('profile-dropdown');
        const backdrop = document.querySelector('.dropdown-backdrop');

        // If clicking outside both dropdowns
        if ((!notificationDropdown || !notificationDropdown.contains(event.target)) && 
            (!profileDropdown || !profileDropdown.contains(event.target)) &&
            backdrop) {
            
            // Close notification menu
            if (notificationMenu) {
                notificationMenu.classList.add('hidden');
            }
            
            // Close profile menu
            if (profileMenu) {
                profileMenu.classList.add('hidden');
            }
            
            // Reset profile arrow
            const profileArrow = document.getElementById('profile-arrow');
            if (profileArrow) {
                profileArrow.classList.remove('rotate-180');
            }
            
            // Remove backdrop
            backdrop.remove();
        }
    });

    // Rest of your existing JavaScript functions...
    
    // Refresh notifications
    function refreshNotifications() {
        fetch('/api/notifications')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateNotificationUI(data.data);
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
            
        // Also refresh unread count
        fetch('/api/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updateUnreadBadge(data.count);
                }
            })
            .catch(error => console.error('Error fetching unread count:', error));
    }
    
    // Update notification UI
    function updateNotificationUI(notifications) {
        const container = document.getElementById('notification-container');
        const emptyNotice = document.getElementById('empty-notification');
        
        if (notifications.length === 0) {
            container.innerHTML = '<div class="p-4 text-center text-gray-500">Tidak ada notifikasi</div>';
            return;
        }
        
        if (emptyNotice) {
            emptyNotice.remove();
        }
        
        let html = '';
        notifications.forEach(notification => {
            const readClass = notification.dibaca ? '' : 'bg-blue-50';
            const formattedDate = new Date(notification.created_at).toLocaleString('id-ID');
            
            html += `
            <a href="javascript:void(0);" onclick="markAsRead('${notification.id_notifikasi}')" 
               class="block p-4 border-b border-gray-100 hover:bg-gray-50 ${readClass}">
                <div class="font-semibold">${notification.judul}</div>
                <div class="text-sm text-gray-600">${notification.pesan}</div>
                <div class="text-xs text-gray-400 mt-1">${formattedDate}</div>
            </a>`;
        });
        
        container.innerHTML = html;
    }
    
    // Update unread badge
    function updateUnreadBadge(count) {
        const badge = document.getElementById('notification-badge');
        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    
    // Mark notification as read
    function markAsRead(notificationId) {
        fetch(`/api/notifications/${notificationId}/mark-as-read`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                refreshNotifications();
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }
    
    // Mark all notifications as read
    function markAllAsRead() {
        fetch('/api/notifications/mark-all-as-read', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                refreshNotifications();
            }
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    }

    // Load notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh notifications every 30 seconds
        setInterval(refreshNotifications, 30000);
        
        // Debug function
        checkProfileImage();
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
</script>