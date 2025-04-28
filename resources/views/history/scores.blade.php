@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="bg-gradient-to-r from-green-500 to-blue-500 p-4 mb-4 rounded-xl">
        <h1 class="text-2xl font-bold text-white">Rekap Nilai Peserta Magang</h1>
    </div>

    <div class="bg-white rounded-xl shadow-md p-4">
        <div class="mb-4">
            <!-- Filter section -->
            <div class="flex flex-col md:flex-row gap-4 mb-4">
                <div class="w-full md:w-1/3">
                    <input type="text" id="searchInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Cari nama/institusi...">
                </div>
                <div class="w-full md:w-1/3">
                    <select id="bidangFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Bidang</option>
                        @foreach($bidang as $b)
                            <option value="{{ $b->id_bidang }}">{{ $b->nama_bidang }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Table section -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 text-left border-b">Nama</th>
                            <th class="p-3 text-left border-b">Institusi</th>
                            <th class="p-3 text-left border-b">Bidang</th>
                            <th class="p-3 text-left border-b">Tanggal Masuk</th>
                            <th class="p-3 text-left border-b">Tanggal Keluar</th>
                            <th class="p-3 text-left border-b">Rata-rata</th>
                            <th class="p-3 text-left border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="scoreTableBody">
                        @if(count($initialData) > 0)
                            @foreach($initialData as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="p-3 border-b">{{ $data->nama }}</td>
                                    <td class="p-3 border-b">{{ $data->nama_institusi }}</td>
                                    <td class="p-3 border-b">{{ $data->nama_bidang }}</td>
                                    <td class="p-3 border-b">{{ \Carbon\Carbon::parse($data->tanggal_masuk)->format('d/m/Y') }}</td>
                                    <td class="p-3 border-b">{{ \Carbon\Carbon::parse($data->tanggal_keluar)->format('d/m/Y') }}</td>
                                    <td class="p-3 border-b">
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
                                    <td class="p-3 border-b flex gap-2">
                                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded view-detail" data-id="{{ $data->id_magang }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded edit-score" data-id="{{ $data->id_magang }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded generate-cert" data-id="{{ $data->id_magang }}">
                                            <i class="fas fa-certificate"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="p-3 text-center border-b">Tidak ada data penilaian</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal Edit Nilai -->
<!-- Tambahkan ini di akhir section content pada file history/scores.blade.php -->
<div id="editScoreModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-4xl max-h-screen overflow-y-auto">
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
                    <!-- Di bagian form edit (sekitar baris 63-67) -->
<div class="mb-4">
    <label for="jumlah_hadir" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kehadiran</label>
    <input type="number" id="jumlah_hadir" name="jumlah_hadir" min="0" 
           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
    <p class="text-sm text-gray-500 mt-1">Total hari kerja: <span id="totalHariKerja">0</span> hari</p>
</div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label for="nilai_teamwork" class="block text-sm font-medium text-gray-700 mb-1">Teamwork</label>
                        <input type="number" id="nilai_teamwork" name="nilai_teamwork" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_komunikasi" class="block text-sm font-medium text-gray-700 mb-1">Komunikasi</label>
                        <input type="number" id="nilai_komunikasi" name="nilai_komunikasi" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_pengambilan_keputusan" class="block text-sm font-medium text-gray-700 mb-1">Pengambilan Keputusan</label>
                        <input type="number" id="nilai_pengambilan_keputusan" name="nilai_pengambilan_keputusan" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kualitas_kerja" class="block text-sm font-medium text-gray-700 mb-1">Kualitas Kerja</label>
                        <input type="number" id="nilai_kualitas_kerja" name="nilai_kualitas_kerja" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_teknologi" class="block text-sm font-medium text-gray-700 mb-1">Teknologi</label>
                        <input type="number" id="nilai_teknologi" name="nilai_teknologi" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_disiplin" class="block text-sm font-medium text-gray-700 mb-1">Disiplin</label>
                        <input type="number" id="nilai_disiplin" name="nilai_disiplin" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_tanggungjawab" class="block text-sm font-medium text-gray-700 mb-1">Tanggung Jawab</label>
                        <input type="number" id="nilai_tanggungjawab" name="nilai_tanggungjawab" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kerjasama" class="block text-sm font-medium text-gray-700 mb-1">Kerjasama</label>
                        <input type="number" id="nilai_kerjasama" name="nilai_kerjasama" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kejujuran" class="block text-sm font-medium text-gray-700 mb-1">Kejujuran</label>
                        <input type="number" id="nilai_kejujuran" name="nilai_kejujuran" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="nilai_kebersihan" class="block text-sm font-medium text-gray-700 mb-1">Kebersihan</label>
                        <input type="number" id="nilai_kebersihan" name="nilai_kebersihan" min="0" max="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg mr-2 hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
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
    
    // Detail view button - Redirect ke halaman detail
    document.querySelectorAll('.view-detail').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            console.log('View detail for ID:', id);
            window.location.href = `/dashboard/interns/detail/${id}`;
        });
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
            })
            .catch(error => {
                console.error('Error loading assessment data:', error);
                if (editFormError) {
                    editFormError.textContent = `Error: ${error.message}`;
                    editFormError.classList.remove('hidden');
                } else {
                    alert(`Error loading data: ${error.message}`);
                }
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
                if (result.status === 'success') {
                    hideEditModal();
                    alert('Nilai berhasil diperbarui');
                    loadData(); // Reload table data
                } else {
                    throw new Error(result.message || 'Terjadi kesalahan');
                }
            })
            .catch(error => {
                console.error('Error updating score:', error);
                if (editFormError) {
                    editFormError.textContent = error.message;
                    editFormError.classList.remove('hidden');
                } else {
                    alert(`Error: ${error.message}`);
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
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Memuat data...</td></tr>';
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
                        tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Error: ' + (data.message || 'Terjadi kesalahan') + '</td></tr>';
                    }
                }
            })
            .catch(error => {
                console.error('Kesalahan fetch:', error);
                if (tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Terjadi kesalahan saat mengambil data: ' + error.message + '</td></tr>';
                }
            });
    }
    
    // Fungsi untuk menampilkan data di tabel
    function renderTable(data) {
        console.log('Rendering table with data:', data);
        
        if (!tableBody) return;
        
        if (!data || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Tidak ada data penilaian</td></tr>';
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
                <tr class="hover:bg-gray-50">
                    <td class="p-3 border-b">${item.nama}</td>
                    <td class="p-3 border-b">${item.nama_institusi}</td>
                    <td class="p-3 border-b">${item.nama_bidang}</td>
                    <td class="p-3 border-b">${tanggalMasuk}</td>
                    <td class="p-3 border-b">${tanggalKeluar}</td>
                    <td class="p-3 border-b">${average.toFixed(2)}</td>
                    <td class="p-3 border-b flex gap-2">
                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded view-detail" data-id="${item.id_magang}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded edit-score" data-id="${item.id_magang}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded generate-cert" data-id="${item.id_magang}">
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