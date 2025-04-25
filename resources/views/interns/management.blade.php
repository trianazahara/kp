@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-yellow-200 rounded-lg shadow-md p-4 mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-white text-xl md:text-2xl font-bold">Manajemen Data Peserta Magang</h1>
            <a href="{{ route('interns.add') }}" class="bg-white hover:bg-gray-100 text-emerald-500 font-semibold py-2 px-4 rounded-md flex items-center text-sm">
                <span class="mr-1">+</span> Tambah Peserta Magang
            </a>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="relative grow">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" id="search-input" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full pl-10 p-2.5" placeholder="Search">
        </div>
        
        <div class="w-full md:w-64">
            <select id="bidang-filter" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                <option value="">Ruang Penempatan</option>
                @foreach($bidangs as $bidang)
                    <option value="{{ $bidang->id_bidang }}">{{ $bidang->nama_bidang }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="w-full md:w-64">
        <select id="status-filter" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
    <option value="aktif,not_yet,almost">Semua Status Aktif</option>
    <option value="aktif">Aktif</option>
    <option value="almost">Hampir Selesai</option>
    <option value="not_yet">Belum Mulai</option>
</select>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="p-4">
                        <div class="flex items-center">
                            <input id="select-all" type="checkbox" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500">
                        </div>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institusi</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruang Penempatan</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Masuk</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Keluar</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mentor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody id="interns-table-body" class="bg-white divide-y divide-gray-200">
                <!-- Table rows will be populated with JavaScript -->
            </tbody>
        </table>

        <!-- Empty state -->
        <div id="empty-state" class="hidden text-center py-8">
            <p class="text-gray-500">Tidak ada data peserta magang yang ditemukan.</p>
        </div>

        <!-- Loading indicator -->
        <div id="loading-indicator" class="text-center py-8">
            <div role="status">
                <svg aria-hidden="true" class="inline w-8 h-8 mr-2 text-gray-200 animate-spin fill-green-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-600">
            <span id="total-items">0</span> item(s) found
        </div>
        <div class="flex space-x-2">
            <button id="prev-page" class="px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div id="pagination-numbers" class="flex space-x-1">
                <!-- Pagination numbers will be dynamically inserted -->
            </div>
            <button id="next-page" class="px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
    <div class="flex justify-between items-center mt-4">
    <div class="flex items-center">
        <span class="text-sm text-gray-600 mr-2">Tampilkan:</span>
        <select id="limit-selector" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block p-2.5">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="all">Semua</option>
        </select>
    </div>
    <div class="text-sm text-gray-600">
        <span id="total-items">0</span> item(s) found
    </div>
    <div class="flex space-x-2">
        <button id="prev-page" class="px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div id="pagination-numbers" class="flex space-x-1">
            <!-- Pagination numbers will be dynamically inserted -->
        </div>
        <button id="next-page" class="px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>

    <!-- Batch Actions -->
    <div class="mt-4 p-4 bg-white rounded-lg shadow-md">
        <button id="generate-surat" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md">
            Generate Tanda Terima
        </button>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Hapus</h3>
            <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menghapus data peserta magang ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-delete" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">Batal</button>
                <button id="confirm-delete" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">Hapus</button>
            </div>
            <input type="hidden" id="delete-intern-id">
        </div>
    </div>

    <!-- Missing Status Confirmation Modal -->
    <div id="missing-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Status Missing</h3>
            <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menandai peserta magang ini sebagai missing?</p>
            <div class="flex justify-end space-x-3">
                <button id="cancel-missing" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">Batal</button>
                <button id="confirm-missing" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md">Konfirmasi</button>
            </div>
            <input type="hidden" id="missing-intern-id">
        </div>
    </div>
</div>
<!-- Corner Notification -->
<div id="corner-notification" class="fixed bottom-4 right-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-md shadow-md flex items-center hidden">
    <span id="notification-icon" class="mr-2 flex-shrink-0">
        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
    </span>
    <p id="notification-message" class="text-sm font-medium"></p>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // State variables
    let currentPage = 1;
    let currentLimit = 10;
    let currentSearch = '';
    let currentBidang = '';
    let currentStatus = 'aktif,not_yet,almost';
    let totalPages = 1;
    let internData = [];
    let selectedInterns = new Set();

    // DOM elements
    const tableBody = document.getElementById('interns-table-body');
    const loadingIndicator = document.getElementById('loading-indicator');
    const emptyState = document.getElementById('empty-state');
    const paginationNumbers = document.getElementById('pagination-numbers');
    const totalItemsElem = document.getElementById('total-items');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const searchInput = document.getElementById('search-input');
    const bidangFilter = document.getElementById('bidang-filter');
    const statusFilter = document.getElementById('status-filter');
    const selectAllCheckbox = document.getElementById('select-all');
    const generateSuratBtn = document.getElementById('generate-surat');
    const limitSelector = document.getElementById('limit-selector');
    
    
    // Delete modal elements
    const deleteModal = document.getElementById('delete-modal');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const deleteInternId = document.getElementById('delete-intern-id');

    // Missing modal elements
    const missingModal = document.getElementById('missing-modal');
    const cancelMissingBtn = document.getElementById('cancel-missing');
    const confirmMissingBtn = document.getElementById('confirm-missing');
    const missingInternId = document.getElementById('missing-intern-id');

    // Initial data load
    statusFilter.value = 'aktif,not_yet,almost';
loadInterns(); 

    // Event listeners
    searchInput.addEventListener('input', debounce(function() {
        currentSearch = this.value;
        currentPage = 1;
        loadInterns();
    }, 500));

    bidangFilter.addEventListener('change', function() {
        currentBidang = this.value;
        currentPage = 1;
        loadInterns();
    });

    statusFilter.addEventListener('change', function() {
    currentStatus = this.value;
    currentPage = 1;
    loadInterns();
});
    prevPageBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadInterns();
        }
    });

    nextPageBtn.addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            loadInterns();
        }
    });
    
    limitSelector.addEventListener('change', function() {
    currentLimit = this.value === 'all' ? 1000 : parseInt(this.value);
    currentPage = 1; // Reset ke halaman pertama saat limit diubah
    loadInterns();
});
    selectAllCheckbox.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            const internId = checkbox.getAttribute('data-id');
            if (this.checked) {
                selectedInterns.add(internId);
            } else {
                selectedInterns.delete(internId);
            }
        });
        updateBatchActionsState();
    });

    // Modifikasi event listener untuk tombol generate surat
    generateSuratBtn.addEventListener('click', function() {
        if (selectedInterns.size > 0) {
            // Langsung download tanda terima tanpa perlu halaman pilih lagi
            const internIds = Array.from(selectedInterns);
            
            // Buat form untuk submit POST request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('interns.download-receipt') }}";
            form.style.display = 'none';
            
            // Tambahkan CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = "{{ csrf_token() }}";
            form.appendChild(csrfToken);
            
            // Tambahkan ID peserta yang dipilih
            internIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'intern_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            // Tambahkan form ke body dan submit
            document.body.appendChild(form);
            form.submit();
        }
    });

    cancelDeleteBtn.addEventListener('click', function() {
        deleteModal.classList.add('hidden');
    });

    confirmDeleteBtn.addEventListener('click', function() {
        const id = deleteInternId.value;
        if (id) {
            deleteIntern(id);
        }
    });

    // Missing modal event listeners
    cancelMissingBtn.addEventListener('click', function() {
        missingModal.classList.add('hidden');
    });

    confirmMissingBtn.addEventListener('click', function() {
        const id = missingInternId.value;
        if (id) {
            setMissingStatus(id);
        }
    });

    function showCornerNotification(message, type = 'success') {
    const notification = document.getElementById('corner-notification');
    const messageEl = document.getElementById('notification-message');
    const iconEl = document.getElementById('notification-icon');
    
    // Set message
    messageEl.textContent = message;
    
    // Set styling based on type
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
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);
}

   // Function to load interns data
   // Di fungsi loadInterns() tambahkan console log di beberapa tempat
function loadInterns() {
    console.log('loadInterns() called'); // Cek apakah fungsi dipanggil
    showLoading();
    
    const url = "{{ route('api.interns.getAll') }}";
    
    // Tentukan nilai default untuk status
    const statusToUse = currentStatus === '' ? 'aktif,not_yet,almost' : currentStatus;
    
    const params = new URLSearchParams({
        page: currentPage,
        limit: currentLimit,
        search: currentSearch,
        bidang: currentBidang,
        status: statusToUse
    });

    console.log('Request URL:', `${url}?${params.toString()}`); // Cek URL request
    
    fetch(`${url}?${params.toString()}`, {
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status); // Cek status HTTP response
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Cek data response
        if (data.status === 'success') {
            console.log('Data count:', data.data.length); // Cek jumlah data
            renderTable(data.data);
            updatePagination(data.pagination);
            totalItemsElem.textContent = data.pagination.total;
            hideLoading();
        } else {
            console.error('Error loading data:', data.message);
            showEmptyState();
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        showEmptyState();
    });
}

    // Function to render table rows
    function renderTable(data) {
        internData = data;
        
        if (data.length === 0) {
            showEmptyState();
            return;
        }

        tableBody.innerHTML = '';
        emptyState.classList.add('hidden');
        
        data.forEach(intern => {
            const row = document.createElement('tr');
            row.className = intern.has_incomplete_data ? 'bg-yellow-50' : '';
            
            const checkboxCell = document.createElement('td');
            checkboxCell.className = 'p-4';
            
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500';
            checkbox.setAttribute('data-id', intern.id_magang);
            checkbox.checked = selectedInterns.has(intern.id_magang);
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    selectedInterns.add(intern.id_magang);
                } else {
                    selectedInterns.delete(intern.id_magang);
                    selectAllCheckbox.checked = false;
                }
                updateBatchActionsState();
            });
            
            checkboxCell.appendChild(checkbox);
            row.appendChild(checkboxCell);
            
            // Name cell
            const nameCell = document.createElement('td');
            nameCell.className = 'px-6 py-4 whitespace-nowrap';
            nameCell.textContent = intern.nama;
            row.appendChild(nameCell);
            
            // Institution cell
            const institutionCell = document.createElement('td');
            institutionCell.className = 'px-6 py-4 whitespace-nowrap';
            institutionCell.textContent = intern.nama_institusi;
            row.appendChild(institutionCell);
            
            // Department cell
            const deptCell = document.createElement('td');
            deptCell.className = 'px-6 py-4 whitespace-nowrap';
            deptCell.textContent = intern.nama_bidang || 'UMUM';
            row.appendChild(deptCell);
            
            // Start date cell
            const startDateCell = document.createElement('td');
            startDateCell.className = 'px-6 py-4 whitespace-nowrap';
            startDateCell.textContent = formatDate(intern.tanggal_masuk);
            row.appendChild(startDateCell);
            
            // End date cell
            const endDateCell = document.createElement('td');
            endDateCell.className = 'px-6 py-4 whitespace-nowrap';
            endDateCell.textContent = formatDate(intern.tanggal_keluar);
            row.appendChild(endDateCell);
            
            // Status cell
            const statusCell = document.createElement('td');
            statusCell.className = 'px-6 py-4 whitespace-nowrap';
            
            const statusBadge = document.createElement('span');
            statusBadge.className = 'px-2 py-1 text-xs font-semibold rounded-full ' + getStatusClass(intern.status);
            statusBadge.textContent = getStatusText(intern.status);
            
            statusCell.appendChild(statusBadge);
            row.appendChild(statusCell);
            
            // Mentor cell
            const mentorCell = document.createElement('td');
            mentorCell.className = 'px-6 py-4 whitespace-nowrap';
            mentorCell.textContent = intern.mentor_name || 'Belum Diassign';
            row.appendChild(mentorCell);
            
            // Actions cell
            const actionsCell = document.createElement('td');
            actionsCell.className = 'px-6 py-4 whitespace-nowrap';
            
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'flex space-x-2';
            
            // Edit button
            const editBtn = document.createElement('a');
            editBtn.href = "{{ url('dashboard/interns/edit') }}/" + intern.id_magang;
            editBtn.className = 'text-blue-500 hover:text-blue-700';
            editBtn.innerHTML = '<i class="fas fa-edit"></i>';
            actionsDiv.appendChild(editBtn);
            
            // View button
            const viewBtn = document.createElement('a');
            viewBtn.href = "{{ url('dashboard/interns/detail') }}/" + intern.id_magang;
            viewBtn.className = 'text-green-500 hover:text-green-700';
            viewBtn.innerHTML = '<i class="fas fa-eye"></i>';
            actionsDiv.appendChild(viewBtn);
            
            // Delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'text-red-500 hover:text-red-700';
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
            deleteBtn.addEventListener('click', function() {
                showDeleteModal(intern.id_magang, intern.nama);
            });
            actionsDiv.appendChild(deleteBtn);
            
            // Missing flag button (only for active interns)
            if (intern.status === 'aktif' || intern.status === 'almost') {
                const missingBtn = document.createElement('button');
                missingBtn.className = 'text-orange-500 hover:text-orange-700';
                missingBtn.innerHTML = '<i class="fas fa-flag"></i>';
                missingBtn.title = 'Mark as Missing';
                missingBtn.addEventListener('click', function() {
                    showMissingModal(intern.id_magang, intern.nama);
                });
                actionsDiv.appendChild(missingBtn);
            }
            
            actionsCell.appendChild(actionsDiv);
            row.appendChild(actionsCell);
            
            tableBody.appendChild(row);
        });
    }

    // Function to update pagination
    function updatePagination(pagination) {
        totalPages = pagination.totalPages;
        paginationNumbers.innerHTML = '';
        
        // Enable/disable prev/next buttons
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPages;
        
        // Generate pagination numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = i === currentPage 
                ? 'px-3 py-1 bg-green-500 text-white rounded-md' 
                : 'px-3 py-1 bg-white text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50';
            pageBtn.addEventListener('click', function() {
                currentPage = i;
                loadInterns();
            });
            paginationNumbers.appendChild(pageBtn);
        }
    }

    // Function to show missing confirmation modal
    function showMissingModal(id, name) {
        missingInternId.value = id;
        document.querySelector('#missing-modal p').textContent = 
            `Apakah Anda yakin ingin menandai peserta magang "${name}" sebagai missing?`;
        missingModal.classList.remove('hidden');
    }

    // Function to set an intern status to missing
    function setMissingStatus(id) {
    fetch("{{ url('api/interns/missing') }}/" + id, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            missingModal.classList.add('hidden');
            showCornerNotification('Status berhasil diubah menjadi missing', 'success');
            loadInterns();
        } else {
            showCornerNotification('Gagal mengubah status: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error marking as missing:', error);
        showCornerNotification('Terjadi kesalahan saat mengubah status', 'error');
    });
}


    // Function to show delete confirmation modal
    function showDeleteModal(id, name) {
        deleteInternId.value = id;
        document.querySelector('#delete-modal p').textContent = 
            `Apakah Anda yakin ingin menghapus data peserta magang "${name}"? Tindakan ini tidak dapat dibatalkan.`;
        deleteModal.classList.remove('hidden');
    }

    // Function to delete an intern
    function deleteIntern(id) {
    fetch("{{ url('api/interns/delete') }}/" + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            deleteModal.classList.add('hidden');
            showCornerNotification('Data berhasil dihapus', 'success');
            loadInterns();
        } else {
            showCornerNotification('Gagal menghapus data: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting intern:', error);
        showCornerNotification('Terjadi kesalahan saat menghapus data', 'error');
    });
}

    // Helper function to format date
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    // Helper function to get status text
    function getStatusText(status) {
        switch (status) {
            case 'aktif': return 'Aktif';
            case 'almost': return 'Hampir Selesai';
            case 'not_yet': return 'Belum Mulai';
            case 'selesai': return 'Selesai';
            case 'missing': return 'Missing';
            default: return status;
        }
    }

    // Helper function to get status badge class
    function getStatusClass(status) {
        switch (status) {
            case 'aktif': return 'bg-green-100 text-green-800';
            case 'almost': return 'bg-orange-100 text-orange-800';
            case 'not_yet': return 'bg-blue-100 text-blue-800';
            case 'selesai': return 'bg-gray-100 text-gray-800';
            case 'missing': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    // Helper function to show loading state
    function showLoading() {
        tableBody.innerHTML = '';
        loadingIndicator.classList.remove('hidden');
        emptyState.classList.add('hidden');
    }

    // Helper function to hide loading state
    function hideLoading() {
        loadingIndicator.classList.add('hidden');
    }

    // Helper function to show empty state
    function showEmptyState() {
        tableBody.innerHTML = '';
        loadingIndicator.classList.add('hidden');
        emptyState.classList.remove('hidden');
    }

    // Helper function to update batch actions state
    function updateBatchActionsState() {
        generateSuratBtn.disabled = selectedInterns.size === 0;
        generateSuratBtn.classList.toggle('opacity-50', selectedInterns.size === 0);
    }

    // Debounce function for search input
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        }
    }
});
</script>
@endsection