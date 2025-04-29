@extends('layouts.app')

@section('title', 'Rekap Nilai Peserta Magang')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Updated header with export button -->
    <div class="bg-gradient-to-r from-green-400 to-blue-500 text-white p-6 rounded-lg shadow-lg mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Rekap Nilai Peserta Magang</h1>
            <button id="exportButton" class="bg-white hover:bg-gray-100 text-green-600 font-semibold py-2 px-4 rounded-md flex items-center text-sm">
                <i class="fas fa-file-export mr-2"></i> Export Excel
            </button>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <input type="text" id="searchInput" placeholder="Cari nama/institusi..." class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
            <select id="bidangFilter" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                <option value="">Semua Bidang</option>
                @foreach($bidang as $b)
                    <option value="{{ $b->id_bidang }}">{{ $b->nama_bidang }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institusi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Keluar</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="scoreTableBody" class="bg-white divide-y divide-gray-200">
                @if(count($initialData) > 0)
                    @foreach($initialData as $data)
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $data->nama }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $data->nama_institusi }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $data->nama_bidang }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($data->tanggal_masuk)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($data->tanggal_keluar)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @php
                                    $nilai = [
                                        $data->nilai_teamwork ?? 0,
                                        $data->nilai_komunikasi ?? 0,
                                        $data->nilai_pengambilan_keputusan ?? 0,
                                        $data->nilai_kualitas_kerja ?? 0,
                                        $data->nilai_teknologi ?? 0,
                                        $data->nilai_disiplin ?? 0,
                                        $data->nilai_tanggungjawab ?? 0,
                                        $data->nilai_kerjasama ?? 0,
                                        $data->nilai_kejujuran ?? 0,
                                        $data->nilai_kebersihan ?? 0
                                    ];
                                    $rataRata = count($nilai) > 0 ? array_sum($nilai) / count($nilai) : 0;
                                @endphp
                                {{ number_format($rataRata, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button class="p-1 rounded-full text-blue-600 hover:bg-blue-100 view-detail" data-id="{{ $data->id_magang }}" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="p-1 rounded-full text-green-600 hover:bg-green-100 edit-score" data-id="{{ $data->id_magang }}" title="Edit Nilai">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="p-1 rounded-full text-yellow-600 hover:bg-yellow-100 generate-cert" data-id="{{ $data->id_magang }}" title="Cetak Sertifikat">
                                    <i class="fas fa-certificate"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada data penilaian</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Nilai -->
<div id="editScoreModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Edit Nilai Peserta Magang</h2>
                <button id="closeEditModal" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="editFormError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 hidden"></div>
            
            <form id="editScoreForm">
                @csrf
                <input type="hidden" id="edit_id_penilaian" name="id_penilaian">
                <input type="hidden" id="edit_id_magang" name="id_magang">
                
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Nama Peserta:</label>
                    <p id="edit_nama_peserta" class="text-gray-800 font-medium"></p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="mb-4">
                        <label for="jumlah_hadir" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kehadiran</label>
                        <input type="number" id="jumlah_hadir" name="jumlah_hadir" min="0" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="text-sm text-gray-500 mt-1">Total hari kerja: <span id="totalHariKerja">0</span> hari</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label for="nilai_teamwork" class="block text-sm font-medium text-gray-700 mb-1">Teamwork</label>
                        <input type="number" id="nilai_teamwork" name="nilai_teamwork" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_komunikasi" class="block text-sm font-medium text-gray-700 mb-1">Komunikasi</label>
                        <input type="number" id="nilai_komunikasi" name="nilai_komunikasi" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_pengambilan_keputusan" class="block text-sm font-medium text-gray-700 mb-1">Pengambilan Keputusan</label>
                        <input type="number" id="nilai_pengambilan_keputusan" name="nilai_pengambilan_keputusan" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kualitas_kerja" class="block text-sm font-medium text-gray-700 mb-1">Kualitas Kerja</label>
                        <input type="number" id="nilai_kualitas_kerja" name="nilai_kualitas_kerja" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_teknologi" class="block text-sm font-medium text-gray-700 mb-1">Teknologi</label>
                        <input type="number" id="nilai_teknologi" name="nilai_teknologi" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_disiplin" class="block text-sm font-medium text-gray-700 mb-1">Disiplin</label>
                        <input type="number" id="nilai_disiplin" name="nilai_disiplin" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_tanggungjawab" class="block text-sm font-medium text-gray-700 mb-1">Tanggung Jawab</label>
                        <input type="number" id="nilai_tanggungjawab" name="nilai_tanggungjawab" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kerjasama" class="block text-sm font-medium text-gray-700 mb-1">Kerjasama</label>
                        <input type="number" id="nilai_kerjasama" name="nilai_kerjasama" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kejujuran" class="block text-sm font-medium text-gray-700 mb-1">Kejujuran</label>
                        <input type="number" id="nilai_kejujuran" name="nilai_kejujuran" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kebersihan" class="block text-sm font-medium text-gray-700 mb-1">Kebersihan</label>
                        <input type="number" id="nilai_kebersihan" name="nilai_kebersihan" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div class="flex justify-end mt-6 space-x-3">
                    <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notification Toast -->
<div id="toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg hidden">
    <div class="flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <span id="toastMessage"></span>
    </div>
</div>


<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Export Data</h2>
            <button id="closeExportModal" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="mb-6">
            <div class="space-y-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="exportType" value="all" class="form-radio h-5 w-5 text-green-600" checked>
                    <span class="ml-2 text-gray-700">Export Semua Data</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="exportType" value="filtered" class="form-radio h-5 w-5 text-green-600">
                    <span class="ml-2 text-gray-700">Export Berdasarkan Filter Tanggal</span>
                </label>
                
                <!-- Date filter fields, shown only when "Export Berdasarkan Filter Tanggal" is selected -->
                <div id="dateFilterFields" class="hidden mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" id="startDate" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" id="endDate" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3">
            <button id="cancelExportBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">
                Batal
            </button>
            <button id="confirmExportBtn" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                Export
            </button>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Modal elements
    const editScoreModal = document.getElementById('editScoreModal');
    const closeEditModalBtn = document.getElementById('closeEditModal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const editScoreForm = document.getElementById('editScoreForm');
    const editFormError = document.getElementById('editFormError');
    
    // Toast notification
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    
    // Fungsi untuk menampilkan toast notification
    function showToast(message, isSuccess = true) {
        // Set warna berdasarkan jenis pesan
        toast.className = isSuccess 
            ? 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg'
            : 'fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg';
            
        toastMessage.textContent = message;
        toast.classList.remove('hidden');
        
        // Otomatis hilangkan toast setelah 3 detik
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }
    
    // Detail view button - Redirect ke halaman detail
    document.querySelectorAll('.view-detail').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            console.log('View detail for ID:', id);
            window.location.href = `/dashboard/interns/detail/${id}`;
        });
    });

   // Export Modal functionality
   const exportButton = document.getElementById('exportButton');
    const exportModal = document.getElementById('exportModal');
    const closeExportModal = document.getElementById('closeExportModal');
    const cancelExportBtn = document.getElementById('cancelExportBtn');
    const confirmExportBtn = document.getElementById('confirmExportBtn');
    const exportTypeRadios = document.querySelectorAll('input[name="exportType"]');
    const dateFilterFields = document.getElementById('dateFilterFields');
    
    // Show export modal
    exportButton.addEventListener('click', function() {
        exportModal.classList.remove('hidden');
    });
    
    // Hide export modal
    function hideExportModal() {
        exportModal.classList.add('hidden');
    }
    
    closeExportModal.addEventListener('click', hideExportModal);
    cancelExportBtn.addEventListener('click', hideExportModal);
    
    // Toggle date filter fields based on export type
    exportTypeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'filtered') {
                dateFilterFields.classList.remove('hidden');
            } else {
                dateFilterFields.classList.add('hidden');
            }
        });
    });
    
    // Handle export confirmation
    confirmExportBtn.addEventListener('click', function() {
        const exportType = document.querySelector('input[name="exportType"]:checked').value;
        
        // Change this URL to match your route in api.php
        let url = '/api/interns/export';
        
        // Build query parameters
        const params = new URLSearchParams();
        
        // Add bidang filter if selected
        const bidangFilter = document.getElementById('bidangFilter');
        if (bidangFilter && bidangFilter.value) {
            params.append('bidang', bidangFilter.value);
        }
        
        // Add date range if "filtered" option is selected
        if (exportType === 'filtered') {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                alert('Silakan pilih tanggal mulai dan tanggal akhir');
                return;
            }
            
            params.append('end_date_start', startDate);
            params.append('end_date_end', endDate);
        }
        
        // Create final URL with parameters
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        // Debug to console
        console.log('Export URL:', url);
        
        // Redirect to download URL
        window.location.href = url;
        
        // Hide modal
        hideExportModal();
    });
    
    // Close modal when clicking outside
    exportModal.addEventListener('click', function(e) {
        if (e.target === exportModal) {
            hideExportModal();
        }
    });

    // Edit score button - BUKA MODAL (bukan redirect)
    document.querySelectorAll('.edit-score').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            console.log('Edit score for ID:', id);
            loadAssessmentData(id);
        });
    });
    
    // Generate certificate button
    document.querySelectorAll('.generate-cert').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            console.log('Generate certificate for ID:', id);
            window.open(`/api/assessments/certificate/${id}`, '_blank');
        });
    });

    // Modal functions
    function hideEditModal() {
        editScoreModal.classList.add('hidden');
    }

    function showEditModal() {
        editScoreModal.classList.remove('hidden');
    }

    // Close modal events
    if (closeEditModalBtn) {
        closeEditModalBtn.addEventListener('click', hideEditModal);
    }

    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', hideEditModal);
    }
    
    // Close modal when clicking outside
    editScoreModal.addEventListener('click', function(e) {
        if (e.target === editScoreModal) {
            hideEditModal();
        }
    });

    // Load assessment data for editing
    function loadAssessmentData(id) {
        console.log('Loading assessment data for ID:', id);
        
        if (editFormError) editFormError.classList.add('hidden');
        
        // Show loading state in form fields
        if (editScoreForm) {
            const formInputs = editScoreForm.querySelectorAll('input[type="number"]');
            formInputs.forEach(input => {
                input.value = '';
                input.placeholder = 'Loading...';
                input.disabled = true;
            });
        }
        
        // Show modal early with loading state
        showEditModal();
        
        // Fetch assessment data
        fetch(`/api/assessments/intern/${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Assessment data loaded:', data);
                populateEditForm(data);
                
                // Enable form inputs
                const formInputs = editScoreForm.querySelectorAll('input[type="number"]');
                formInputs.forEach(input => {
                    input.disabled = false;
                    input.placeholder = '';
                });
            })
            .catch(error => {
                console.error('Error loading assessment data:', error);
                if (editFormError) {
                    editFormError.textContent = `Error: ${error.message}`;
                    editFormError.classList.remove('hidden');
                } else {
                    showToast(`Error loading data: ${error.message}`, false);
                }
                
                // Enable form inputs
                const formInputs = editScoreForm.querySelectorAll('input[type="number"]');
                formInputs.forEach(input => {
                    input.disabled = false;
                    input.placeholder = '';
                });
            });
    }

    // Tambahkan fungsi ini di bagian awal script
    function calculateWorkingDays(startDate, endDate) {
        if (!startDate || !endDate) return 0;
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        // Periksa validitas tanggal
        if (isNaN(start.getTime()) || isNaN(end.getTime())) {
            console.error('Invalid date format:', { startDate, endDate });
            return 'N/A';
        }
        
        let workingDays = 0;
        let current = new Date(start);

        while (current <= end) {
            // 0 = Sunday, 6 = Saturday
            if (current.getDay() !== 0 && current.getDay() !== 6) {
                workingDays++;
            }
            current.setDate(current.getDate() + 1);
        }

        return workingDays;
    }

    // Populate form with data
    function populateEditForm(data) {
        if (!editScoreForm) return;
        
        document.getElementById('edit_id_penilaian').value = data.id_penilaian;
        document.getElementById('edit_id_magang').value = data.id_magang;
        document.getElementById('edit_nama_peserta').textContent = data.nama || 'Tidak diketahui';
        
        document.getElementById('jumlah_hadir').value = data.jumlah_hadir || 0;
        document.getElementById('nilai_teamwork').value = data.nilai_teamwork || 0;
        document.getElementById('nilai_komunikasi').value = data.nilai_komunikasi || 0;
        document.getElementById('nilai_pengambilan_keputusan').value = data.nilai_pengambilan_keputusan || 0;
        document.getElementById('nilai_kualitas_kerja').value = data.nilai_kualitas_kerja || 0;
        document.getElementById('nilai_teknologi').value = data.nilai_teknologi || 0;
        document.getElementById('nilai_disiplin').value = data.nilai_disiplin || 0;
        document.getElementById('nilai_tanggungjawab').value = data.nilai_tanggungjawab || 0;
        document.getElementById('nilai_kerjasama').value = data.nilai_kerjasama || 0;
        document.getElementById('nilai_kejujuran').value = data.nilai_kejujuran || 0;
        document.getElementById('nilai_kebersihan').value = data.nilai_kebersihan || 0;
        
        console.log('Tanggal masuk:', data.tanggal_masuk);
        console.log('Tanggal keluar:', data.tanggal_keluar);
        
        // Ganti bagian ini dalam populateEditForm
        if (data.tanggal_masuk && data.tanggal_keluar) {
            console.log('Tanggal masuk:', data.tanggal_masuk);
            console.log('Tanggal keluar:', data.tanggal_keluar);
            
            // Gunakan fungsi yang sudah terbukti berhasil
            const workingDays = calculateWorkingDays(data.tanggal_masuk, data.tanggal_keluar);
            console.log('Working days calculated:', workingDays);
            document.getElementById('totalHariKerja').textContent = workingDays;
        } else {
            document.getElementById('totalHariKerja').textContent = 'N/A';
        }
    }

    // Handle form submission
    if (editScoreForm) {
        editScoreForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('edit_id_penilaian').value;
            if (!id) {
                if (editFormError) {
                    editFormError.textContent = 'Error: ID penilaian tidak ditemukan';
                    editFormError.classList.remove('hidden');
                }
                return;
            }
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Tampilkan loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 'Menyimpan...';
            submitButton.disabled = true;
            
            // Submit form data
            fetch(`/api/assessments/update-nilai/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                
                if (result.status === 'success') {
                    hideEditModal();
                    showToast('Nilai berhasil diperbarui');
                    loadData(); // Reload table data
                } else {
                    throw new Error(result.message || 'Terjadi kesalahan');
                }
            })
            .catch(error => {
                console.error('Error updating score:', error);
                
                // Reset button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
                
                if (editFormError) {
                    editFormError.textContent = error.message;
                    editFormError.classList.remove('hidden');
                } else {
                    showToast(`Error: ${error.message}`, false);
                }
            });
        });
    }

    // Data loading and table handling
    const searchInput = document.getElementById('searchInput');
    const bidangSelect = document.getElementById('bidangFilter');
    const tableBody = document.getElementById('scoreTableBody');
    
    console.log('Elements:', {
        searchInput: searchInput ? 'Found' : 'Not Found',
        bidangSelect: bidangSelect ? 'Found' : 'Not Found',
        tableBody: tableBody ? 'Found' : 'Not Found'
    });
    
    // Fungsi untuk memuat data
    function loadData() {
        console.log('Loading data...');
        
        const search = searchInput ? searchInput.value : '';
        const bidang = bidangSelect ? bidangSelect.value : '';
        
        console.log('Parameters:', { search, bidang });
        
        // Tampilkan loading state
        if (tableBody) {
            tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Memuat data...</td></tr>';
        }
        
        // Panggil API dengan URL yang benar
        fetch('/api/history/scores?search=' + encodeURIComponent(search) + '&bidang=' + encodeURIComponent(bidang))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Respons jaringan tidak ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    renderTable(data.data);
                } else {
                    // Tangani respons error
                    if (tableBody) {
                        tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Error: ' + (data.message || 'Terjadi kesalahan') + '</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('Kesalahan fetch:', error);
                if (tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat mengambil data: ' + error.message + '</td></tr>';
                }
            });
    }
    
    // Fungsi untuk menampilkan data di tabel
    function renderTable(data) {
        console.log('Rendering table with data:', data);
        
        if (!tableBody) return;
        
        if (!data || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada data penilaian</td></tr>';
            return;
        }
        
        let html = '';
        
        data.forEach(item => {
            // Calculate average score
            const nilai = [
                parseFloat(item.nilai_teamwork) || 0,
                parseFloat(item.nilai_komunikasi) || 0,
                parseFloat(item.nilai_pengambilan_keputusan) || 0,
                parseFloat(item.nilai_kualitas_kerja) || 0,
                parseFloat(item.nilai_teknologi) || 0,
                parseFloat(item.nilai_disiplin) || 0,
                parseFloat(item.nilai_tanggungjawab) || 0,
                parseFloat(item.nilai_kerjasama) || 0,
                parseFloat(item.nilai_kejujuran) || 0,
                parseFloat(item.nilai_kebersihan) || 0
            ];
            
            const average = nilai.reduce((sum, value) => sum + value, 0) / nilai.length;
            
            // Format dates
            const tanggalMasuk = new Date(item.tanggal_masuk).toLocaleDateString('id');
            const tanggalKeluar = new Date(item.tanggal_keluar).toLocaleDateString('id');
            
            html += `
                <tr class="hover:bg-gray-100">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.nama}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.nama_institusi}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.nama_bidang}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${tanggalMasuk}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${tanggalKeluar}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${average.toFixed(2)}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button class="p-1 rounded-full text-blue-600 hover:bg-blue-100 view-detail" data-id="${item.id_magang}" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="p-1 rounded-full text-green-600 hover:bg-green-100 edit-score" data-id="${item.id_magang}" title="Edit Nilai">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="p-1 rounded-full text-yellow-600 hover:bg-yellow-100 generate-cert" data-id="${item.id_magang}" title="Cetak Sertifikat">
                            <i class="fas fa-certificate"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
        
        // Initialize event listeners for new buttons
        initButtons();
    }
    
    // Initialize all buttons
    function initButtons() {
        // Detail view
        document.querySelectorAll('.view-detail').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                window.location.href = `/dashboard/interns/detail/${id}`;
            });
        });
        
        // Edit score - open modal
        document.querySelectorAll('.edit-score').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                loadAssessmentData(id);
            });
        });
        
        // Generate certificate
        document.querySelectorAll('.generate-cert').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                window.open(`/api/assessments/certificate/${id}`, '_blank');
            });
        });
    }
    
    // Event listener untuk input pencarian
    if (searchInput) {
        searchInput.addEventListener('input', debounce(loadData, 500));
    }
    
    // Event listener untuk filter bidang
    if (bidangSelect) {
        bidangSelect.addEventListener('change', loadData);
    }
    
    // Fungsi debounce untuk mencegah terlalu banyak request
    function debounce(func, delay) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), delay);
        };
    }
    
    // Load data saat halaman dimuat
    loadData();
    
    // Initialize buttons on page load
    initButtons();
});
</script>
@endsection