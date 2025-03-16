<div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white shadow-md z-30 transition-transform duration-300 ease-in-out md:translate-x-0 -translate-x-full md:static md:h-screen">
    <!-- Logo and Title -->
    <div class="p-4 border-b">
        <div class="flex items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Dinas Pendidikan Sumbar" class="h-12 mr-3">
            <div>
                <h1 class="text-lg font-bold text-primary">PANDU</h1>
                <p class="text-xs text-gray-600 leading-tight">Platform Magang <br> Dinas Pendidikan <br> Sumbar</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="mt-5">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('dashboard') ? 'bg-green-50 text-primary border-l-4 border-primary' : 'text-gray-600 hover:bg-green-50 hover:text-primary hover:border-l-4 hover:border-primary' }}">
            <i class="fas fa-home w-6"></i>
            <span class="ml-2">Dashboard</span>
        </a>
        
        <!-- Data Magang with submenu -->
        <div class="relative">
            <button class="flex items-center justify-between w-full px-4 py-3 text-gray-600 hover:bg-green-50 hover:text-primary" id="data-magang-btn" onclick="toggleSubmenu('data-magang-submenu')">
                <div class="flex items-center">
                    <i class="fas fa-clipboard-list w-6"></i>
                    <span class="ml-2">Data Magang</span>
                </div>
                <i class="fas fa-chevron-down transition-transform" id="data-magang-arrow"></i>
            </button>
            
            <div id="data-magang-submenu" class="hidden pl-10 bg-gray-50">
                <a href="{{ route('interns.management') }}" class="block py-2 text-gray-600 hover:text-primary {{ request()->routeIs('interns.management') ? 'text-primary font-medium' : '' }}">
                    Manajemen Data
                </a>
                <a href="{{ route('interns.positions') }}" class="block py-2 text-gray-600 hover:text-primary {{ request()->routeIs('interns.positions') ? 'text-primary font-medium' : '' }}">
                    Cek Ketersediaan Posisi
                </a>
            </div>
        </div>
        
        <!-- Riwayat with submenu -->
        <div class="relative">
            <button class="flex items-center justify-between w-full px-4 py-3 text-gray-600 hover:bg-green-50 hover:text-primary" id="history-btn" onclick="toggleSubmenu('history-submenu')">
                <div class="flex items-center">
                    <i class="fas fa-history w-6"></i>
                    <span class="ml-2">Riwayat</span>
                </div>
                <i class="fas fa-chevron-down transition-transform" id="history-arrow"></i>
            </button>
            
            <div id="history-submenu" class="hidden pl-10 bg-gray-50">
                <a href="{{ route('history.data') }}" class="block py-2 text-gray-600 hover:text-primary {{ request()->routeIs('history.data') ? 'text-primary font-medium' : '' }}">
                    Riwayat Data
                </a>
                <a href="{{ route('history.scores') }}" class="block py-2 text-gray-600 hover:text-primary {{ request()->routeIs('history.scores') ? 'text-primary font-medium' : '' }}">
                    Rekap Nilai
                </a>
            </div>
        </div>
        
        @if(auth()->user()->role === 'superadmin')
        <a href="{{ route('admin.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.*') ? 'bg-green-50 text-primary border-l-4 border-primary' : 'text-gray-600 hover:bg-green-50 hover:text-primary hover:border-l-4 hover:border-primary' }}">
            <i class="fas fa-user-cog w-6"></i>
            <span class="ml-2">Manajemen Admin</span>
        </a>
        @endif
        
        <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('settings.*') ? 'bg-green-50 text-primary border-l-4 border-primary' : 'text-gray-600 hover:bg-green-50 hover:text-primary hover:border-l-4 hover:border-primary' }}">
            <i class="fas fa-cog w-6"></i>
            <span class="ml-2">Pengaturan</span>
        </a>
        
        <!-- Logout button -->
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center px-4 py-3 text-gray-600 hover:bg-green-50 hover:text-primary mt-auto border-t">
            <i class="fas fa-sign-out-alt w-6"></i>
            <span class="ml-2">Keluar</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </nav>
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
        arrow.classList.toggle('rotate-180');
        
        // Set active menu based on current route
        if (id === 'data-magang-submenu' && (
            "{{ request()->routeIs('interns.*') }}" === "1"
        )) {
            submenu.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        }
        
        if (id === 'history-submenu' && (
            "{{ request()->routeIs('history.*') }}" === "1"
        )) {
            submenu.classList.remove('hidden');
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