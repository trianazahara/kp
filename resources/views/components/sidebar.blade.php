<div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-gradient-to-b from-slate-50 to-slate-100 text-green-600 shadow-xl border-r border-r-slate-200/50 backdrop-blur-sm flex flex-col z-30 transition-transform duration-300 ease-in-out md:translate-x-0 -translate-x-full md:static md:h-screen">
    <!-- Logo and Title -->
    <div class="p-4 perspective shrink-0">
        <div class="flex items-center gap-3 hover:scale-105 transition-all duration-500 transform hover:translate-z-4 relative">
            <div class="relative transform transition-all duration-500 hover:rotate-y-180">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Dinas Pendidikan Sumbar" class="w-12 h-12 shadow-lg animate-float">
                <div class="absolute inset-0 bg-gradient-to-tr from-green-500/20 to-transparent rounded-xl animate-pulse-glow"></div>
            </div>
            <div class="transform transition-all duration-500 hover:translate-x-2">
                <h1 class="text-green-600 text-xl font-bold mb-1">PANDU</h1>
                <div class="text-gray-700 text-sm">
                    Platform Magang
                    <br>
                    Dinas Pendidikan Sumatera Barat
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="mt-6 perspective flex-1 overflow-y-auto scrollbar-hide px-2 pb-6">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center p-3 rounded-xl transform transition-all duration-500 hover:shadow-lg hover:shadow-green-500/10 focus:outline-none relative {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-green-200 to-emerald-100 shadow-md scale-105' : 'hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50' }} active:scale-95 mb-4">
            <div class="transform transition-all duration-500 p-2 rounded-lg bg-gradient-to-br from-green-100 to-green-50">
                <i class="fas fa-home w-6"></i>
            </div>
            <span class="ml-3 font-medium">Dashboard</span>
        </a>
        
        <!-- Data Magang with submenu -->
        <div class="mb-4 px-2">
            <button onclick="toggleSubmenu('data-magang-submenu')" class="w-full flex items-center justify-between p-3 rounded-xl transform transition-all duration-500 focus:outline-none hover:shadow-lg hover:shadow-green-500/10 relative bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 active:scale-95">
                <div class="flex items-center">
                    <div class="transform transition-all duration-500 p-2 rounded-lg bg-gradient-to-br from-green-100 to-green-50">
                        <i class="fas fa-clipboard-list w-6"></i>
                    </div>
                    <span class="ml-3 font-medium">Data Magang</span>
                </div>
                <i class="fas fa-chevron-down transform transition-all duration-500" id="data-magang-arrow"></i>
            </button>
            <div id="data-magang-submenu" class="ml-8 mt-2 space-y-2 transition-all duration-500 transform opacity-0 -translate-y-4 hidden">
                <a href="{{ route('interns.management') }}" class="block p-2 rounded-xl transform transition-all duration-500 hover:translate-x-2 relative hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 hover:shadow-md active:scale-95 {{ request()->routeIs('interns.management') ? 'bg-gradient-to-r from-green-200 to-emerald-100 shadow-md scale-105' : '' }}">
                    Manajemen Data
                </a>
                <a href="{{ route('interns.positions') }}" class="block p-2 rounded-xl transform transition-all duration-500 hover:translate-x-2 relative hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 hover:shadow-md active:scale-95 {{ request()->routeIs('interns.positions') ? 'bg-gradient-to-r from-green-200 to-emerald-100 shadow-md scale-105' : '' }}">
                    Cek Ketersediaan Posisi
                </a>
            </div>
        </div>
        
        <!-- Riwayat with submenu -->
        <div class="mb-4 px-2">
            <button onclick="toggleSubmenu('history-submenu')" class="w-full flex items-center justify-between p-3 rounded-xl transform transition-all duration-500 focus:outline-none hover:shadow-lg hover:shadow-green-500/10 relative bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 active:scale-95">
                <div class="flex items-center">
                    <div class="transform transition-all duration-500 p-2 rounded-lg bg-gradient-to-br from-green-100 to-green-50">
                        <i class="fas fa-history w-6"></i>
                    </div>
                    <span class="ml-3 font-medium">Riwayat</span>
                </div>
                <i class="fas fa-chevron-down transform transition-all duration-500" id="history-arrow"></i>
            </button>
            <div id="history-submenu" class="ml-8 mt-2 space-y-2 transition-all duration-500 transform opacity-0 -translate-y-4 hidden">
                <a href="{{ route('history.data') }}" class="block p-2 rounded-xl transform transition-all duration-500 hover:translate-x-2 relative hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 hover:shadow-md active:scale-95 {{ request()->routeIs('history.data') ? 'bg-gradient-to-r from-green-200 to-emerald-100 shadow-md scale-105' : '' }}">
                    Riwayat Data
                </a>
                <a href="{{ route('rekap-nilai.index') }}" class="block p-2 rounded-xl transform transition-all duration-500 hover:translate-x-2 relative hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 hover:shadow-md active:scale-95 {{ request()->routeIs('history.scores') ? 'bg-gradient-to-r from-green-200 to-emerald-100 shadow-md scale-105' : '' }}">
                    Rekap Nilai
                </a>
            </div>
        </div>
        
        @if(auth()->check() && auth()->user()->role === 'superadmin')
        <a href="{{ route('admin.index') }}" class="flex items-center p-3 rounded-xl transform transition-all duration-500 hover:shadow-lg hover:shadow-green-500/10 focus:outline-none relative {{ request()->routeIs('admin.*') ? 'bg-gradient-to-r from-green-200 to-emerald-100 shadow-md scale-105' : 'hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50' }} active:scale-95 mb-4">
            <div class="transform transition-all duration-500 p-2 rounded-lg bg-gradient-to-br from-green-100 to-green-50">
                <i class="fas fa-user-cog w-6"></i>
            </div>
            <span class="ml-3 font-medium">Manajemen Admin</span>
        </a>
        @endif
        
        <a href="{{ route('settings.index') }}" class="flex items-center p-3 rounded-xl transform transition-all duration-500 hover:shadow-lg hover:shadow-green-500/10 focus:outline-none relative {{ request()->routeIs('settings.*') ? 'bg-gradient-to-r from-green-200 to-emerald-100 shadow-md scale-105' : 'hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50' }} active:scale-95 mb-4">
            <div class="transform transition-all duration-500 p-2 rounded-lg bg-gradient-to-br from-green-100 to-green-50">
                <i class="fas fa-cog w-6"></i>
            </div>
            <span class="ml-3 font-medium">Pengaturan</span>
        </a>
        
    </nav>
    
    <!-- Copyright section -->
    <div class="p-4 mt-auto border-t border-slate-200/50">
        <div class="text-xs scale-90 text-gray-600 text-center">
            <p class="font-medium mb-1">Developed by:</p>
            <p>Dhiya Gustita Aqila, Triana Zahara Nurhaliza, Laura Iffa Razitta</p>
            <p class="mt-1 font-medium">Sistem Informasi 22 UNAND</p>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-20 hidden md:hidden"></div>

<!-- Include Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    // Mobile sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }
    
    // Toggle submenu function
    function toggleSubmenu(id) {
        const submenu = document.getElementById(id);
        const arrowId = id.replace('submenu', 'arrow');
        const arrow = document.getElementById(arrowId);
        
        submenu.classList.toggle('hidden');
        submenu.classList.toggle('opacity-0');
        submenu.classList.toggle('-translate-y-4');
        arrow.classList.toggle('rotate-180');
        
        // Set active menu based on current route
        if (id === 'data-magang-submenu' && (
            "{{ request()->routeIs('interns.*') }}" === "1"
        )) {
            submenu.classList.remove('hidden', 'opacity-0', '-translate-y-4');
            arrow.classList.add('rotate-180');
        }
        
        if (id === 'history-submenu' && (
            "{{ request()->routeIs('history.*') }}" === "1"
        )) {
            submenu.classList.remove('hidden', 'opacity-0', '-translate-y-4');
            arrow.classList.add('rotate-180');
        }
    }
    
    // Initialize submenus based on current route
    document.addEventListener('DOMContentLoaded', function() {
        if ("{{ request()->routeIs('interns.*') }}" === "1") {
            toggleSubmenu('data-magang-submenu');
        }
        
        if ("{{ request()->routeIs('history.*') }}" === "1") {
            toggleSubmenu('history-submenu');
        }
    });
    
    // Close sidebar when clicking overlay
    document.getElementById('sidebar-overlay')?.addEventListener('click', toggleSidebar);
</script>

<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>