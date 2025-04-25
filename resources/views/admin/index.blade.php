@extends('layouts.app')

@section('title', 'Manajemen Admin')

@section('styles')
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
<div x-data="{
    admins: [],
    bidangList: [],
    openDialog: false,
    selectedAdmin: null,
    formData: {
        username: '',
        password: '',
        email: '',
        nama: '',
        nip: '',
        id_bidang: '',
        role: 'admin'
    },
    snackbarOpen: false,
    snackbarMessage: '',
    snackbarSeverity: 'success',
    
    init() {
        this.fetchAdmins();
        this.fetchBidangList();
    },
    
    fetchBidangList() {
        fetch('/api/bidang', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                this.bidangList = result.data;
            } else {
                this.showSnackbar('Gagal mengambil data bidang', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching bidang:', error);
            this.showSnackbar('Gagal mengambil data bidang', 'error');
        });
    },
    
    fetchAdmins() {
        fetch('/api/admin', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            this.admins = data;
        })
        .catch(error => {
            console.error('Error fetching admins:', error);
            this.showSnackbar('Gagal mengambil data admin', 'error');
        });
    },
    
    openEditDialog(admin) {
        this.selectedAdmin = admin;
        this.formData = {
            username: admin.username,
            email: admin.email,
            nama: admin.nama,
            nip: admin.nip || '',
            id_bidang: admin.id_bidang || '',
            password: '',
            role: admin.role
        };
        this.openDialog = true;
    },
    
    openAddDialog() {
        this.selectedAdmin = null;
        this.formData = {
            username: '',
            password: '',
            email: '',
            nama: '',
            nip: '',
            id_bidang: '',
            role: 'admin'
        };
        this.openDialog = true;
    },
    
    showSnackbar(message, severity = 'success') {
        this.snackbarMessage = message;
        this.snackbarSeverity = severity;
        this.snackbarOpen = true;
        
        setTimeout(() => {
            this.snackbarOpen = false;
        }, 6000);
    },
    
    handleSubmit() {
        const formData = { ...this.formData };
        
        // Jika password kosong saat edit, hapus dari payload
        if (this.selectedAdmin && !formData.password) {
            delete formData.password;
        }
        
        const url = this.selectedAdmin 
            ? `/api/admin/${this.selectedAdmin.id_users}`
            : '/api/admin';
            
        const method = this.selectedAdmin ? 'PATCH' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            this.showSnackbar(
                this.selectedAdmin ? 'Admin berhasil diperbarui' : 'Admin berhasil ditambahkan'
            );
            this.openDialog = false;
            this.fetchAdmins();
        })
        .catch(error => {
            console.error('Error:', error);
            this.showSnackbar(error.message || 'Terjadi kesalahan', 'error');
        });
    },
    
    handleDelete(id_users) {
        if (confirm('Apakah Anda yakin ingin menghapus admin ini?')) {
            fetch(`/api/admin/${id_users}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                }
            })
            .then(response => {
                if (response.ok) {
                    this.showSnackbar('Admin berhasil dihapus');
                    this.fetchAdmins();
                } else {
                    throw new Error('Gagal menghapus admin');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                this.showSnackbar('Gagal menghapus admin', 'error');
            });
        }
    }
}" class="w-full">
    <!-- Header Section -->
    <div class="w-full rounded-xl mb-4 p-3 flex justify-between items-center overflow-hidden transition-all duration-300 animated-bg">
        <h1 class="text-2xl text-white font-bold">Manajemen Admin</h1>
        <button
            @click="openAddDialog()"
            class="px-4 py-2 bg-white text-teal-500 font-medium rounded-md hover:bg-gray-100 flex items-center"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            TAMBAH ADMIN
        </button>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="admin in admins" :key="admin.id_users">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="admin.nama"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="admin.nip || '-'"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="admin.username"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="admin.email"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="admin.nama_bidang || '-'"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <button 
                                @click="openEditDialog(admin)"
                                class="p-1 rounded-full text-blue-600 hover:bg-blue-100 mr-1"
                                title="Edit admin"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </button>
                            <button 
                                @click="handleDelete(admin.id_users)"
                                class="p-1 rounded-full text-red-600 hover:bg-red-100"
                                title="Hapus admin"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
                <template x-if="admins.length === 0">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data admin yang tersedia
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Form Dialog -->
    <div x-show="openDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" x-cloak>
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold" x-text="selectedAdmin ? 'Edit Admin' : 'Tambah Admin Baru'"></h2>
            </div>
            <form @submit.prevent="handleSubmit">
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input
                            type="text"
                            x-model="formData.nama"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                        <input
                            type="text"
                            x-model="formData.nip"
                            @input="formData.nip = $event.target.value.replace(/[^0-9]/g, '')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input
                            type="text"
                            x-model="formData.username"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input
                            type="email"
                            x-model="formData.email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            required
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bidang</label>
                        <select
                            x-model="formData.id_bidang"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            required
                        >
                            <option value="">Pilih Bidang</option>
                            <template x-for="bidang in bidangList" :key="bidang.id_bidang">
                                <option :value="bidang.id_bidang" x-text="bidang.nama_bidang"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input
                            type="password"
                            x-model="formData.password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            :required="!selectedAdmin"
                        >
                        <p x-show="selectedAdmin" class="text-xs text-gray-500 mt-1">
                            Kosongkan jika tidak ingin mengubah password
                        </p>
                    </div>
                </div>
                <div class="p-4 border-t flex justify-end space-x-2">
                    <button
                        type="button"
                        @click="openDialog = false"
                        class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-md"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700"
                    >
                        <span x-text="selectedAdmin ? 'Perbarui' : 'Tambah'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Snackbar -->
    <div
        x-show="snackbarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed bottom-4 right-4 z-50"
        x-cloak
    >
        <div 
            class="px-4 py-3 rounded-lg shadow-lg flex items-center gap-2"
            :class="snackbarSeverity === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
        >
            <svg 
                x-show="snackbarSeverity === 'success'"
                xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5" 
                viewBox="0 0 20 20" 
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <svg 
                x-show="snackbarSeverity === 'error'"
                xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5" 
                viewBox="0 0 20 20" 
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span x-text="snackbarMessage"></span>
            <button @click="snackbarOpen = false" class="ml-2 text-white hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</div>
@endsection