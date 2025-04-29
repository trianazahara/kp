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

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none'"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none'"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <div class="font-bold">Terdapat kesalahan pada input Anda:</div>
                <ul class="list-disc list-inside mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentElement.parentElement.style.display='none'"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                </span>
            </div>
        @endif

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
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" value="{{ $user->email }}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="mb-6">
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <input type="text" id="nama" name="nama" value="{{ $user->nama }}" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="bg-green-600 text-white px-5 py-2 rounded-md hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
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
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Unggah Foto Profil
                            </button>
                        </form>
                        @if($user->profile_picture)
                            <form action="{{ route('settings.delete-photo') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    onclick="return confirm('Yakin ingin menghapus foto profil?')">
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" id="newPassword" name="newPassword" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Ubah Password
                </button>
            </form>
        </div>

        <!-- Template Upload Section -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-6">Change Template</h2>
            <div class="mb-8">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 hover:bg-gray-50 transition-colors" id="dropzone">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="mt-2 text-gray-600">Choose a file or drag & drop it here.</p>
                        <p class="text-xs text-gray-500 mt-1">DOC/DOCX up to 50 MB.</p>
                        <form action="{{ route('templates.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                            @csrf
                            <input type="file" name="file" id="file-upload" class="hidden" accept=".doc,.docx" required
                                onchange="document.getElementById('uploadForm').submit()">
                            <button type="button" onclick="document.getElementById('file-upload').click()" 
                                class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                </svg>
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
                            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
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
                                        class="text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 btn-preview-template"
                                        data-id="{{ $template->id_dokumen }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        Preview
                                    </button>
                                    
                                    <!-- Tombol Download -->
                                    <a href="{{ route('templates.download', $template->id_dokumen) }}" 
                                        class="text-green-600 hover:text-green-800 px-2 py-1 rounded hover:bg-green-50 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        Download
                                    </a>
                                        
                                    <form action="{{ route('templates.delete', $template->id_dokumen) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
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
        </div>
    </div>
</div>

<!-- Modal Preview Template -->
<div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl w-11/12 md:w-4/5 lg:w-3/4 h-5/6 flex flex-col">
        <div class="flex justify-between items-center border-b p-4">
            <h3 class="text-xl font-semibold">Preview Template</h3>
            <div class="flex items-center space-x-2">
                <button id="printPreview" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0v3H7V4h6zm-6 8v4h6v-4H7z" clip-rule="evenodd" />
                    </svg>
                    Print
                </button>
                <button id="downloadPreview" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Download
                </button>
                <button id="closePreview" class="text-gray-500 hover:text-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="flex-1 p-0">
            <div id="loadingPreview" class="flex flex-col items-center justify-center h-full">
                <svg class="animate-spin h-12 w-12 text-blue-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600">Memuat preview...</p>
            </div>
            <iframe id="previewFrame" class="w-full h-full" style="display: none;"></iframe>
        </div>
    </div>
</div>

<!-- Script untuk Drag & Drop Upload -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file-upload');
        const uploadForm = document.getElementById('uploadForm');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            dropzone.classList.add('bg-gray-100', 'border-blue-500');
        }

        function unhighlight() {
            dropzone.classList.remove('bg-gray-100', 'border-blue-500');
        }

        dropzone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                fileInput.files = files;
                uploadForm.submit();
            }
        }
    });
</script>

<!-- Script untuk Modal Preview Template -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const modal = document.getElementById('previewModal');
    const closeBtn = document.getElementById('closePreview');
    const printBtn = document.getElementById('printPreview');
    const downloadBtn = document.getElementById('downloadPreview');
    const iframe = document.getElementById('previewFrame');
    const loading = document.getElementById('loadingPreview');
    let currentTemplateId = null;

    // Tombol preview
    document.querySelectorAll('.btn-preview-template').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            currentTemplateId = this.getAttribute('data-id');
            openPreviewModal(currentTemplateId);
        });
    });

    // Fungsi untuk membuka modal
    function openPreviewModal(templateId) {
        // Tampilkan modal
        modal.style.display = 'flex';
        // Tampilkan loading
        loading.style.display = 'flex';
        iframe.style.display = 'none';
        
        // Set iframe source
        iframe.src = `/templates/preview/${templateId}`;
        
        // Disable scrolling pada body
        document.body.style.overflow = 'hidden';
        
        // Event listener untuk iframe
        iframe.onload = function() {
            loading.style.display = 'none';
            iframe.style.display = 'block';
        };
    }

    // Fungsi untuk menutup modal
    function closePreviewModal() {
        modal.style.display = 'none';
        iframe.src = '';
        document.body.style.overflow = '';
    }

    // Event listeners
    closeBtn.addEventListener('click', closePreviewModal);

    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closePreviewModal();
        }
    });

    printBtn.addEventListener('click', function() {
        if (iframe.contentWindow) {
            iframe.contentWindow.print();
        }
    });

    downloadBtn.addEventListener('click', function() {
        if (currentTemplateId) {
            window.location.href = `/templates/download/${currentTemplateId}`;
        }
    });

    // Close dengan Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closePreviewModal();
        }
    });
});
</script>
@endsection