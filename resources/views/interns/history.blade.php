@extends('layouts.app')

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
<script>
  // Untuk debugging
  document.addEventListener('alpine:init', () => {
    console.log('Alpine.js loaded successfully');
  });
</script>
@endsection

@section('title', 'Riwayat Data Anak Magang')

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
    data: [],
    bidangList: [],
    search: '',
    bidang: '',
    statusFilter: '',
    page: 0,
    limit: 10,
    total: 0,
    openDialog: false,
    selectedIntern: null,
    scoreForm: {
        nilai_teamwork: 0,
        nilai_komunikasi: 0,
        nilai_pengambilan_keputusan: 0,
        nilai_kualitas_kerja: 0,
        nilai_teknologi: 0,
        nilai_disiplin: 0,
        nilai_tanggungjawab: 0,
        nilai_kerjasama: 0,
        nilai_kejujuran: 0,
        nilai_kebersihan: 0,
        jumlah_hadir: 0
    },
    calculatedWorkingDays: 0,
    snackbarOpen: false,
    snackbarMessage: '',
    snackbarSeverity: 'success',
    
    init() {
        this.fetchBidangList();
        this.fetchData();
    },
    
    fetchBidangList() {
        fetch('/api/bidang', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(response => {
            this.bidangList = response.data || [];
        })
        .catch(error => {
            console.error('Error fetching bidang:', error);
            this.showSnackbar('Gagal mengambil data bidang', 'error');
        });
    },
    
    fetchData() {
        const params = new URLSearchParams({
            page: this.page + 1,
            limit: this.limit,
            bidang: this.bidang,
            search: this.search,
            search_type: 'nama_institusi',
            status: this.statusFilter ? this.statusFilter : ['selesai', 'missing', 'almost'].join(',')
        });
        
        fetch(`/api/intern/riwayat-data?${params}`, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(response => {
            this.data = response.data;
            this.total = response.pagination.total;
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            this.showSnackbar('Error mengambil data', 'error');
        });
    },
    
    calculateWorkingDays(startDate, endDate) {
        if (!startDate || !endDate) return 0;
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        let workingDays = 0;
        let current = new Date(start);

        while (current <= end) {
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        return workingDays;
    },
    
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID');
    },
    
    getStatusLabel(status) {
        const labels = {
            'selesai': 'Selesai',
            'completed': 'Selesai',
            'missing': 'Missing',
            'almost': 'Hampir Selesai'
        };
        return labels[status?.toLowerCase()] || status;
    },
    
    getStatusStyle(status) {
        const styles = {
            'selesai': {
                bg: '#dbeafe',
                color: '#1e40af',
                border: '#1e40af'
            },
            'missing': {
                bg: '#fee2e2', 
                color: '#991b1b', 
                border: '#991b1b'
            },
            'almost': {            
                bg: '#fef9c3',      
                color: '#854d0e',   
                border: '#854d0e'   
            }
        };

        return styles[status?.toLowerCase()] || styles['selesai'];
    },
    
    showSnackbar(message, severity = 'success') {
        this.snackbarMessage = message;
        this.snackbarSeverity = severity;
        this.snackbarOpen = true;
        
        setTimeout(() => {
            this.snackbarOpen = false;
        }, 6000);
    },
    
    openScoreDialog(intern) {
        this.selectedIntern = intern;
        this.scoreForm = {
            nilai_teamwork: 0,
            nilai_komunikasi: 0,
            nilai_pengambilan_keputusan: 0,
            nilai_kualitas_kerja: 0,
            nilai_teknologi: 0,
            nilai_disiplin: 0,
            nilai_tanggungjawab: 0,
            nilai_kerjasama: 0,
            nilai_kejujuran: 0,
            nilai_kebersihan: 0,
            jumlah_hadir: 0
        };
        this.calculatedWorkingDays = this.calculateWorkingDays(intern.tanggal_masuk, intern.tanggal_keluar);
        this.openDialog = true;
    },
    
    submitScore() {
        const scoreData = {
            id_magang: this.selectedIntern?.id_magang,
            ...this.scoreForm,
            nilai_teamwork: Number(this.scoreForm.nilai_teamwork),
            nilai_komunikasi: Number(this.scoreForm.nilai_komunikasi),
            nilai_pengambilan_keputusan: Number(this.scoreForm.nilai_pengambilan_keputusan),
            nilai_kualitas_kerja: Number(this.scoreForm.nilai_kualitas_kerja),
            nilai_teknologi: Number(this.scoreForm.nilai_teknologi),
            nilai_disiplin: Number(this.scoreForm.nilai_disiplin),
            nilai_tanggungjawab: Number(this.scoreForm.nilai_tanggungjawab),
            nilai_kerjasama: Number(this.scoreForm.nilai_kerjasama),
            nilai_kejujuran: Number(this.scoreForm.nilai_kejujuran),
            nilai_kebersihan: Number(this.scoreForm.nilai_kebersihan),
            jumlah_hadir: Number(this.scoreForm.jumlah_hadir)
        };

        fetch(`/api/assessments/add-score/${this.selectedIntern.id_magang}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(scoreData)
        })
        .then(response => response.json())
        .then(data => {
            this.showSnackbar('Nilai berhasil disimpan');
            this.openDialog = false;
            
            // Update local data to reflect changes
            this.data = this.data.map(item => 
                item.id_magang === this.selectedIntern.id_magang 
                ? { ...item, has_scores: true }
                : item
            );
            
            this.fetchData(); // Refresh data
        })
        .catch(error => {
            console.error('Submit error:', error);
            this.showSnackbar(error.response?.data?.message || 'Error menyimpan nilai', 'error');
        });
    }
}" class="w-full">
    <!-- Header -->
    <div class="w-full rounded-xl mb-4 p-3 flex justify-between items-center overflow-hidden transition-all duration-300 animated-bg">
        <h1 class="text-2xl text-white font-bold">Riwayat Data Anak Magang</h1>
    </div>

    <!-- Filter Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <input 
                type="text" 
                x-model="search" 
                @input="page = 0; fetchData()"
                placeholder="Search nama/institusi" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
            >
        </div>
        <div>
            <select 
                x-model="bidang" 
                @change="page = 0; fetchData()"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
            >
                <option value="">Semua Bidang</option>
                <template x-for="item in bidangList" :key="item.id_bidang">
                    <option :value="item.id_bidang" x-text="item.nama_bidang"></option>
                </template>
            </select>
        </div>
        <div>
            <select 
                x-model="statusFilter" 
                @change="page = 0; fetchData()"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
            >
                <option value="">Semua Status</option>
                <option value="selesai">Selesai</option>
                <option value="almost">Hampir Selesai</option>
                <option value="missing">Missing</option>
            </select>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-lg shadow overflow-x-auto" style="max-width: 950px">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-[20%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="w-[20%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institusi</th>
                    <th class="w-[15%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                    <th class="w-[15%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Masuk</th>
                    <th class="w-[15%] px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Keluar</th>
                    <th class="w-[10%] px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="w-[10%] px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="intern in data" :key="intern.id_magang">
                    <tr class="hover:bg-gray-100 transition-colors duration-200" :class="intern.has_scores ? 'bg-blue-50' : ''">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 relative group">
                            <span x-text="intern.nama"></span>
                            <span x-show="intern.has_scores" class="text-blue-500 ml-2 inline-block" title="Sudah memiliki penilaian">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="intern.nama_institusi"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="intern.nama_bidang"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(intern.tanggal_masuk)"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(intern.tanggal_keluar)"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span 
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium"
                                :style="{
                                    backgroundColor: getStatusStyle(intern.status).bg,
                                    color: getStatusStyle(intern.status).color,
                                    borderColor: getStatusStyle(intern.status).border,
                                    borderWidth: '1px'
                                }"
                                x-text="getStatusLabel(intern.status)"
                            ></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <template x-if="intern.status?.toLowerCase() !== 'missing'">
                                <div>
                                    <template x-if="intern.has_scores">
                                        <button 
                                            disabled
                                            title="Sudah dinilai"
                                            class="p-1 rounded-full text-green-600 opacity-50 cursor-not-allowed"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template x-if="!intern.has_scores">
                                        <button 
                                            @click="openScoreDialog(intern)"
                                            title="Tambah penilaian"
                                            class="p-1 rounded-full text-blue-600 hover:bg-blue-100"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination Section -->
    <div class="flex items-center justify-between bg-white px-4 py-3 rounded-b-lg">
        <div class="flex items-center gap-2">
            <select
                x-model="limit"
                @change="page = 0; fetchData()"
                class="px-3 py-1 border border-gray-300 rounded-md text-sm"
            >
                <template x-for="size in [5, 10, 25, 50]">
                    <option :value="size" x-text="`${size} items`"></option>
                </template>
            </select>
        </div>
        <div class="flex gap-2">
            <button
                @click="page = Math.max(0, page - 1); fetchData()"
                :disabled="page === 0"
                :class="`px-4 py-2 text-sm font-medium rounded-md ${
                    page === 0
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                }`"
            >
                Previous
            </button>
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md" x-text="page + 1"></span>
            <button
                @click="page = page + 1; fetchData()"
                :disabled="page >= Math.ceil(total / limit) - 1"
                :class="`px-4 py-2 text-sm font-medium rounded-md ${
                    page >= Math.ceil(total / limit) - 1
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                }`"
            >
                Next
            </button>
        </div>
    </div>

    <!-- Score Dialog -->
    <div x-show="openDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" x-cloak>
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold" x-text="`Edit Nilai - ${selectedIntern?.nama}`"></h2>
            </div>
            <form @submit.prevent="submitScore">
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Attendance field -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">JUMLAH KEHADIRAN</label>
                        <input
                            type="number"
                            x-model="scoreForm.jumlah_hadir"
                            :max="calculatedWorkingDays"
                            min="0"
                            step="1"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md"
                            :class="Number(scoreForm.jumlah_hadir) > calculatedWorkingDays ? 'border-red-500' : ''"
                        >
                        <p class="text-xs text-gray-500 mt-1" x-text="`Total hari kerja: ${calculatedWorkingDays} hari`"></p>
                    </div>

                    <!-- Score fields -->
                    <template x-for="(value, key) in scoreForm">
                        <template x-if="key !== 'jumlah_hadir'">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" x-text="key.split('_').slice(1).join(' ').toUpperCase()"></label>
                                <input
                                    type="number"
                                    x-model="scoreForm[key]"
                                    min="0"
                                    max="100"
                                    step="1"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                    :class="Number(value) < 0 || Number(value) > 100 ? 'border-red-500' : ''"
                                >
                                <p x-show="Number(value) < 0 || Number(value) > 100" class="text-xs text-red-500 mt-1">
                                    Nilai harus antara 0-100
                                </p>
                            </div>
                        </template>
                    </template>
                </div>
                <div class="p-4 border-t flex justify-end gap-2">
                    <button @click="openDialog = false" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Simpan
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