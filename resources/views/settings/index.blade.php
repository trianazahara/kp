@extends('layouts.app')

@section('content')
<div class="flex">
    <!-- Sidebar is included from your layouts -->

    <!-- Main Content -->
    <div class="flex-1 p-6 bg-gray-100">
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-green-400 via-blue-400 to-green-400 rounded-lg mb-6 p-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-white">Pengaturan</h1>
        </div>

        <!-- Profile Settings Box -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-6">Profile Settings</h2>
            <div class="flex flex-wrap">
                <div class="w-full lg:w-2/3 pr-0 lg:pr-8">
                    <form action="{{ route('settings.update-profile') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" id="username" name="username" value="{{ $user->username }}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" value="{{ $user->email }}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div class="mb-6">
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <input type="text" id="nama" name="nama" value="{{ $user->nama }}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-md hover:bg-green-700">
                            Update Profile
                        </button>
                    </form>
                </div>
                <div class="w-full lg:w-1/3 mt-8 lg:mt-0 flex flex-col items-center">
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile" 
                            class="w-40 h-40 rounded-full object-cover border-4 border-white shadow-md">
                    @else
                        <div class="w-40 h-40 rounded-full bg-gray-200 flex items-center justify-center border-4 border-white shadow-md">
                            <span class="text-4xl text-gray-400">{{ substr($user->nama, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="mt-6 w-full space-y-3">
                        <form action="{{ route('settings.upload-photo') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="profile_picture" id="profile_picture" class="hidden" 
                                accept="image/*" onchange="this.form.submit()">
                            <button type="button" onclick="document.getElementById('profile_picture').click()" 
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                Unggah Foto Profil
                            </button>
                        </form>
                        @if($user->profile_picture)
                            <form action="{{ route('settings.delete-photo') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                                    Hapus Foto Profil
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Box -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-6">Change Password</h2>
            <form action="{{ route('settings.change-password') }}" method="POST" class="max-w-lg">
                @csrf
                <div class="mb-4">
                    <label for="oldPassword" class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                    <input type="password" id="oldPassword" name="oldPassword" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" id="newPassword" name="newPassword" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    Ubah Password
                </button>
            </form>
        </div>

        <!-- Template Upload Section -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-xl font-semibold mb-6">Change Template</h2>
    <div class="mb-8">
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8" id="dropzone">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="mt-2 text-gray-600">Choose a file or drag & drop it here.</p>
                <p class="text-xs text-gray-500 mt-1">DOC/DOCX up to 50 MB.</p>
                <form action="{{ route('settings.upload-template') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <input type="file" name="file" id="file-upload" class="hidden" accept=".doc,.docx" required
                        onchange="document.getElementById('uploadForm').submit()">
                    <button type="button" onclick="document.getElementById('file-upload').click()" 
                        class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Browse File
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Active Template Section -->
<div>
    <h3 class="text-lg font-medium mb-4">Template Aktif</h3>
    @if($templates->where('active', 1)->isNotEmpty())
        <div class="space-y-3">
            @foreach($templates->where('active', 1) as $template)
                <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg">
                    <div class="flex items-center flex-1 min-w-0">
                        <div class="bg-blue-100 text-blue-600 px-3 py-1 rounded text-xs uppercase mr-4 whitespace-nowrap">
                            @php
                                // Ambil ekstensi file
                                $filename = basename($template->file_path);
                                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                echo strtoupper($extension);
                            @endphp
                        </div>
                        <div class="min-w-0 truncate">
                            @php
                                // Ekstrak nama asli dari filename
                                $filename = pathinfo(basename($template->file_path), PATHINFO_FILENAME);
                                $parts = explode('---', $filename, 2);
                                $originalName = count($parts) > 1 ? $parts[1] : $filename;
                            @endphp
                            <p class="font-medium truncate" title="{{ $originalName }}">{{ $originalName }}</p>
                            <p class="text-sm text-gray-500">
                                @php
                                    try {
                                        $size = Storage::disk('public')->size($template->file_path);
                                        echo number_format($size / 1024, 2) . ' KB';
                                    } catch (\Exception $e) {
                                        echo '0.00 KB';
                                    }
                                @endphp
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center ml-4 space-x-2">
                    <!-- Tombol Preview -->
                    <button type="button" 
                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50"
                        onclick="window.open('https://docs.google.com/viewer?url={{ urlencode(asset('storage/' . $template->file_path)) }}&embedded=true', 'preview', 'width=1000,height=800')">
                        Preview
                    </button>
                                
                        <form action="{{ route('settings.delete-template', $template->id_dokumen) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50"
                                onclick="return confirm('Yakin hapus template ini?')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-gray-500 p-8 border border-dashed border-gray-300 rounded-lg">
            Belum ada template yang diupload
        </div>
    @endif
</div>

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe 
                    id="previewFrame"
                    class="w-full" 
                    style="height: 80vh; border: none;"
                    frameborder="0"
                    loading="lazy"
                ></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Script JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview Template Handler
    $('.preview-template-btn').on('click', function(e) {
        e.preventDefault();
        const templateId = $(this).data('template-id');
        const previewUrl = `/settings/preview-template/${templateId}`;
        
        // Update iframe source langsung
        $('#previewFrame').attr('src', previewUrl);
        $('#previewModal').modal('show');
    });

    // Reset iframe saat modal ditutup
    $('#previewModal').on('hidden.bs.modal', function() {
        $('#previewFrame').attr('src', '');
    });
});
</script>
@endsection