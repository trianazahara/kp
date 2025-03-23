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

        <!-- Template Upload Box -->
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
                        <form action="{{ route('settings.upload-template') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" id="file-upload" class="hidden" accept=".doc,.docx">
                            <button type="button" onclick="document.getElementById('file-upload').click()" 
                                class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Browse File
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-medium mb-4">Template Aktif</h3>
                @if($templates->where('active', 1)->count() > 0)
                    <div class="space-y-3">
                        @foreach($templates->where('active', 1) as $template)
                            <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg">
                                <div class="flex items-center">
                                    <div class="bg-blue-100 text-blue-600 px-3 py-1 rounded text-xs uppercase mr-4">
                                        DOCX
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ $template->name ?? pathinfo($template->file_path, PATHINFO_BASENAME) }}</p>
                                        <p class="text-sm text-gray-500">{{ number_format(($template->size ?? 0) / 1024, 0) }} KB</p>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                <button type="button" class="text-blue-600 hover:text-blue-800 mr-4 preview-template-btn" 
                                    data-template-id="{{ $template->id_dokumen }}">
                                    Click to preview
                                </button>
                                    <form action="{{ route('settings.delete-template', $template->id_dokumen) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus template ini?')">
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

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-md" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any()))
            <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-md" role="alert">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<!-- Modal untuk Preview -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="previewModalBody">
                <!-- Konten preview akan dimuat di sini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Script JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Ketika tombol preview diklik
        $('.preview-template-btn').on('click', function(e) {
            e.preventDefault();
            const templateId = $(this).data('template-id');

            // Ambil data preview dari backend
            $.ajax({
                url: `/settings/preview-template/${templateId}`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        if (response.fileUrl) {
                            // Tampilkan file PDF di iframe
                            $('#previewModalBody').html(`<iframe src="${response.fileUrl}" width="100%" height="500px"></iframe>`);
                            // Tampilkan modal
                            $('#previewModal').modal('show');
                        }
                    } else {
                        alert(response.message); // Tampilkan pesan error jika bukan PDF
                    }
                },
                error: function(xhr) {
                    alert('Gagal memuat preview.');
                }
            });
        });
    });
</script>
@endsection