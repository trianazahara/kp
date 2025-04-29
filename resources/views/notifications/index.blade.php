{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Notifikasi</h1>
        
        @if($notifications->isEmpty())
            <div class="text-center py-10 text-gray-500">
                <i class="fas fa-bell-slash text-5xl mb-4"></i>
                <p>Tidak ada notifikasi</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($notifications as $notification)
                    <div class="p-4 border rounded-lg {{ $notification->dibaca ? 'bg-white' : 'bg-blue-50' }} hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-lg">{{ $notification->judul }}</h3>
                                <p class="text-gray-700 mt-1">{{ $notification->pesan }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y, H:i') }}
                                </p>
                            </div>
                            @if(!$notification->dibaca)
                                <form action="{{ route('api.notifications.markAsRead', $notification->id_notifikasi) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Tandai Dibaca
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
            
            <div class="mt-6 text-center">
                <form action="{{ route('api.notifications.markAllAsRead') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Tandai Semua Dibaca
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection

{{-- resources/views/notifications/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="mb-4">
            <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Notifikasi
            </a>
        </div>
        
        <div class="p-4 border rounded-lg">
            <h1 class="text-2xl font-bold mb-2">{{ $notification->judul }}</h1>
            <p class="text-gray-500 text-sm mb-4">
                {{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y, H:i') }}
            </p>
            <div class="border-t pt-4">
                <p class="text-gray-800">{{ $notification->pesan }}</p>
            </div>
        </div>
    </div>
</div>
@endsection