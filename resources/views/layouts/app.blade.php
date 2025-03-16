<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PANDU - Platform Magang Dinas Pendidikan Sumbar') }}</title>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tambahkan CDN Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tambahkan CDN Axios jika dibutuhkan -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Include the sidebar component -->
        @include('components.sidebar')
        
        <div class="flex-1 h-full overflow-auto flex flex-col">
            <!-- Include the header component -->
            @include('components.header')
            
            <!-- Top navigation bar for mobile -->
            <nav class="bg-white shadow-sm md:hidden p-4 flex justify-between items-center">
                <button onclick="toggleSidebar()" class="text-gray-600 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center space-x-2">
                    <span>{{ auth()->user()->nama }}</span>
                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user text-gray-600"></i>
                    </div>
                </div>
            </nav>
            
            <!-- Content area with added top margin -->
            <div class="container mx-auto px-4 flex-grow mt-6">
                @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                
                @if(session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- JavaScript functions -->
    <script>
        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }
        
        // Initialize active submenus
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            
            if (currentPath.includes('/dashboard/interns')) {
                const submenu = document.getElementById('data-magang-submenu');
                const arrow = document.getElementById('data-magang-arrow');
                
                if (submenu && arrow) {
                    submenu.classList.remove('hidden');
                    arrow.classList.add('rotate-180');
                }
            }
            
            if (currentPath.includes('/dashboard/history')) {
                const submenu = document.getElementById('history-submenu');
                const arrow = document.getElementById('history-arrow');
                
                if (submenu && arrow) {
                    submenu.classList.remove('hidden');
                    arrow.classList.add('rotate-180');
                }
            }
            
            // Close sidebar when clicking overlay on mobile
            const overlay = document.getElementById('sidebar-overlay');
            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>