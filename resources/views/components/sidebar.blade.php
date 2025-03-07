<div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white shadow-md z-30 transition-transform duration-300 ease-in-out">
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
        
        <a href="{{ route('interns.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('interns.*') ? 'bg-green-50 text-primary border-l-4 border-primary' : 'text-gray-600 hover:bg-green-50 hover:text-primary hover:border-l-4 hover:border-primary' }}">
            <i class="fas fa-clipboard-list w-6"></i>
            <span class="ml-2">Data Magang</span>
        </a>
        
        <a href="{{ route('history.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('history.*') ? 'bg-green-50 text-primary border-l-4 border-primary' : 'text-gray-600 hover:bg-green-50 hover:text-primary hover:border-l-4 hover:border-primary' }}">
            <i class="fas fa-history w-6"></i>
            <span class="ml-2">Riwayat</span>
        </a>
        
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
    
    // Close sidebar when clicking overlay
    document.getElementById('sidebar-overlay')?.addEventListener('click', toggleSidebar);
</script>