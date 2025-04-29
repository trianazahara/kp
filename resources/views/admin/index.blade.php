@extends('layouts.app')

@section('title', 'Manajemen Admin')

@section('styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  @keyframes gradient {
    0% {
      background-position: 0% 50%;
      background-size: 100% 100%;
    }
    25% {
      background-size: 200% 200%;
    }
    50% {
      background-position: 100% 50%;
      background-size: 100% 100%;
    }
    75% {
      background-size: 200% 200%;
    }
    100% {
      background-position: 0% 50%;
      background-size: 100% 100%;
    }
  }

  .animated-bg {
    background: linear-gradient(
      90deg, 
      #BCFB69 0%,
      #26BBAC 33%,
      #20A4F3 66%,
      #BCFB69 100%
    );
    background-size: 300% 100%;
    animation: gradient 8s ease-in-out infinite;
    transition: all 0.3s ease;
  }

  .animated-bg:hover {
    transform: scale(1.005);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
</style>
@endsection

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Error Alerts - Tampilkan validation errors -->
    @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <strong class="font-bold">Error!</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-yellow-200 rounded-lg shadow-md p-4 mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-white text-xl md:text-2xl font-bold">Manajemen Admin</h1>
            <button id="add-admin-btn" class="bg-white hover:bg-gray-100 text-emerald-500 font-semibold py-2 px-4 rounded-md flex items-center text-sm">
                <span class="mr-1">+</span> TAMBAH ADMIN
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($admins as $admin)
                <tr class="hover:bg-gray-100 transition-colors duration-200">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $admin->nama }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->nip ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->username }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $admin->nama_bidang ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        <button class="text-blue-500 hover:text-blue-700 edit-admin px-1" data-id="{{ $admin->id_users }}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="text-red-500 hover:text-red-700 delete-admin px-1" data-id="{{ $admin->id_users }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div id="admin-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-title">Tambah Admin Baru</h3>
            
            <form id="admin-form" method="POST" action="{{ route('admin.store') }}" onsubmit="return validateForm()">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required>
                    </div>
                    
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                        <input type="text" id="nip" name="nip" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required>
                    </div>
                    
                    <div>
                        <label for="id_bidang" class="block text-sm font-medium text-gray-700">Bidang</label>
                        <select id="id_bidang" name="id_bidang" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required>
                            <option value="">Pilih Bidang</option>
                            @foreach ($bidangList as $bidang)
                            <option value="{{ $bidang->id_bidang }}">{{ $bidang->nama_bidang }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" minlength="6" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required>
                        <p id="password-help" class="text-xs text-gray-500 mt-1">Password minimal 6 karakter</p>
                        <p id="password-error" class="text-xs text-red-500 mt-1 hidden">Password harus minimal 6 karakter!</p>
                    </div>
                    
                    <input type="hidden" id="admin_id" name="admin_id">
                    <input type="hidden" name="role" value="admin">
                </div>
                
                <div class="mt-5 flex justify-end">
                    <button type="button" id="cancel-btn" class="mr-3 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Batal
                    </button>
                    <button type="submit" id="submit-btn" class="px-4 py-2 text-sm font-medium text-white bg-emerald-500 hover:bg-emerald-600 rounded-md">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-500 mb-6" id="delete-message">Apakah Anda yakin ingin menghapus admin ini?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-delete" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">Batal</button>
                <button id="confirm-delete" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">Hapus</button>
            </div>
            <input type="hidden" id="delete-id">
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="fixed bottom-4 right-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-md shadow-md flex items-center hidden">
        <span id="notification-icon" class="mr-2 flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
        </span>
        <p id="notification-message" class="text-sm font-medium"></p>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const adminModal = document.getElementById('admin-modal');
    const adminForm = document.getElementById('admin-form');
    const modalTitle = document.getElementById('modal-title');
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const addAdminBtn = document.getElementById('add-admin-btn');
    const passwordHelp = document.getElementById('password-help');
    const passwordError = document.getElementById('password-error');
    const deleteModal = document.getElementById('delete-modal');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const deleteIdInput = document.getElementById('delete-id');
    const deleteMessage = document.getElementById('delete-message');
    
    // State
    let isEditMode = false;
    let selectedAdmin = null;
    
    // Form validation
    window.validateForm = function() {
        const passwordField = document.getElementById('password');
        
        // Cek password hanya pada mode tambah admin atau jika password diisi pada mode edit
        if (!isEditMode || passwordField.value.trim() !== '') {
            if (passwordField.value.length < 6) {
                passwordError.classList.remove('hidden');
                return false;
            } else {
                passwordError.classList.add('hidden');
            }
        }
        
        return true;
    };
    
    // Event Listeners
    addAdminBtn.addEventListener('click', function() {
        openModal();
    });
    
    document.querySelectorAll('.edit-admin').forEach(btn => {
        btn.addEventListener('click', function() {
            const adminId = this.getAttribute('data-id');
            const admin = {!! json_encode($admins) !!}.find(a => a.id_users == adminId);
            openModal(admin);
        });
    });
    
    document.querySelectorAll('.delete-admin').forEach(btn => {
        btn.addEventListener('click', function() {
            const adminId = this.getAttribute('data-id');
            const admin = {!! json_encode($admins) !!}.find(a => a.id_users == adminId);
            openDeleteModal(admin);
        });
    });
    
    cancelBtn.addEventListener('click', function() {
        closeModal();
    });
    
    // Perubahan utama: Gunakan submit form standar untuk form tambah
    adminForm.addEventListener('submit', function(e) {
        if (!isEditMode) {
            // Mode tambah: biarkan form submit normal jika validasi berhasil
            return validateForm();
        }
        
        // Mode edit: gunakan AJAX
        e.preventDefault();
        if (validateForm()) {
            submitForm();
        }
    });
    
    // Tambahkan event listener untuk validasi password saat ketik
    document.getElementById('password').addEventListener('input', function() {
        if (this.value.length < 6) {
            passwordError.classList.remove('hidden');
        } else {
            passwordError.classList.add('hidden');
        }
    });
    
    cancelDeleteBtn.addEventListener('click', function() {
        closeDeleteModal();
    });
    
    confirmDeleteBtn.addEventListener('click', function() {
        deleteAdmin();
    });
    
    // NIP input - restrict to numbers only
    document.getElementById('nip').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Functions
    function openModal(admin = null) {
        isEditMode = !!admin;
        selectedAdmin = admin;
        
        // Reset error messages
        passwordError.classList.add('hidden');
        
        if (isEditMode) {
            // Edit Mode
            modalTitle.textContent = 'Edit Admin';
            document.getElementById('nama').value = admin.nama;
            document.getElementById('nip').value = admin.nip || '';
            document.getElementById('username').value = admin.username;
            document.getElementById('email').value = admin.email;
            document.getElementById('id_bidang').value = admin.id_bidang;
            document.getElementById('password').value = '';
            document.getElementById('password').removeAttribute('required');
            document.getElementById('password').removeAttribute('minlength');
            document.getElementById('admin_id').value = admin.id_users;
            passwordHelp.textContent = 'Kosongkan jika tidak ingin mengubah password';
            submitBtn.textContent = 'Perbarui';
            
            // Ubah action form untuk edit
            adminForm.action = "{{ url('/admin') }}/" + admin.id_users;
            adminForm.method = "POST";
            
            // Tambahkan method spoofing untuk PUT
            let methodInput = document.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                adminForm.appendChild(methodInput);
            }
            methodInput.value = 'PUT';
        } else {
            // Add Mode
            modalTitle.textContent = 'Tambah Admin Baru';
            adminForm.reset();
            document.getElementById('password').setAttribute('required', 'required');
            document.getElementById('password').setAttribute('minlength', '6');
            passwordHelp.textContent = 'Password minimal 6 karakter';
            submitBtn.textContent = 'Tambah';
            
            // Reset action form untuk tambah
            adminForm.action = "{{ route('admin.store') }}";
            adminForm.method = "POST";
            
            // Hapus method spoofing jika ada
            const methodInput = document.querySelector('input[name="_method"]');
            if (methodInput) {
                methodInput.remove();
            }
        }
        
        adminModal.classList.remove('hidden');
    }
    
    function closeModal() {
        adminModal.classList.add('hidden');
        adminForm.reset();
    }
    
    // Function untuk edit via AJAX
    function submitForm() {
        console.log('Form submission started for EDIT');
        
        // Kumpulkan data form
        const formData = new FormData(adminForm);
        
        // Debug: Tampilkan data yang akan dikirim
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // Tentukan URL untuk update
        const url = "{{ url('/admin') }}/" + selectedAdmin.id_users;
        
        // Jika password kosong, hapus dari form data
        const passwordField = document.getElementById('password');
        if (!passwordField.value) {
            formData.delete('password');
        }
        
        console.log(`Sending PUT request to: ${url}`);
        
        // Kirim dengan fetch API
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json,text/html'
            },
            credentials: 'same-origin',
            redirect: 'follow'
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            // Cek jika ini redirect (biasanya sukses untuk Laravel)
            if (response.redirected) {
                console.log('Redirected to:', response.url);
                window.location.href = response.url;
                return null;
            }
            
            // Coba parse sebagai JSON, jika gagal ambil sebagai text
            if (response.headers.get('content-type')?.includes('application/json')) {
                return response.json();
            } else {
                return response.text();
            }
        })
        .then(data => {
            if (data === null) return; // Sudah dihandle sebagai redirect
            
            console.log('Response data:', data);
            
            // Tampilkan notifikasi sukses dan reload
            showNotification('Admin berhasil diperbarui', 'success');
            setTimeout(() => { window.location.reload(); }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan: ' + error.message, 'error');
        });
        
        // Prevent default form submission
        return false;
    }
    
    function openDeleteModal(admin) {
        deleteIdInput.value = admin.id_users;
        deleteMessage.textContent = `Apakah Anda yakin ingin menghapus admin "${admin.nama}"?`;
        deleteModal.classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        deleteModal.classList.add('hidden');
    }
    
    function deleteAdmin() {
        const id = deleteIdInput.value;
        
        // Create form and submit
        const deleteForm = document.createElement('form');
        deleteForm.method = 'POST';
        deleteForm.action = "{{ url('/admin') }}/" + id;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        deleteForm.appendChild(csrfInput);
        
        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        deleteForm.appendChild(methodInput);
        
        // Append to body and submit
        document.body.appendChild(deleteForm);
        deleteForm.submit();
    }
    
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const messageEl = document.getElementById('notification-message');
        const iconEl = document.getElementById('notification-icon');
        
        // Set message
        messageEl.textContent = message;
        
        // Set style based on type
        if (type === 'success') {
            notification.className = 'fixed bottom-4 right-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-md shadow-md flex items-center';
            iconEl.innerHTML = `
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            `;
        } else {
            notification.className = 'fixed bottom-4 right-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-md shadow-md flex items-center';
            iconEl.innerHTML = `
                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            `;
        }
        
        // Show notification
        notification.classList.remove('hidden');
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }
    
    // Check for flash messages
    @if(session('success'))
        showNotification("{{ session('success') }}", 'success');
    @endif
    
    @if(session('error'))
        showNotification("{{ session('error') }}", 'error');
    @endif
});
</script>
@endsection