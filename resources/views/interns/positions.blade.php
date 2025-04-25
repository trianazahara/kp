<!-- resources/views/interns/positions.blade.php -->
@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-yellow-200 rounded-lg shadow-md p-4 mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-white text-xl md:text-2xl font-bold">Cek Ketersediaan Posisi Magang</h1>
        </div>
    </div>
    
    <!-- Date selection form -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-64">
                <label for="check-date" class="block mb-2 text-sm font-medium text-gray-700">Pilih Tanggal</label>
                <input type="date" id="check-date" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" value="{{ $defaultDate }}">
            </div>
            
            <button id="check-button" class="px-4 py-2.5 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-300">
                Cek Ketersediaan
            </button>
        </div>
    </div>
    
    <!-- Results card -->
    <div id="results-container" class="bg-white rounded-lg shadow-md p-4 mb-6 hidden">
        <h2 class="text-xl font-semibold mb-4">Hasil Pengecekan <span id="result-date" class="text-gray-600 text-lg"></span></h2>
        
        <div id="availability-status" class="mb-6 p-4 rounded-lg"></div>
        
        <!-- Stats cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <!-- Total Occupied -->
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-gray-500 text-sm uppercase">Total Terisi</h3>
                <p class="text-3xl font-bold" id="total-occupied">0</p>
                <div class="flex justify-between mt-2">
                    <span class="text-sm text-gray-500">Dari 50 slot</span>
                    <span class="text-sm" id="percentage-occupied">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                    <div class="bg-green-500 h-2.5 rounded-full" id="progress-bar" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Active Interns -->
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-gray-500 text-sm uppercase">Peserta Aktif</h3>
                <p class="text-3xl font-bold" id="current-active">0</p>
                <div class="flex justify-between mt-2">
                    <span class="text-sm text-gray-500">Saat ini</span>
                </div>
            </div>
            
            <!-- Upcoming Interns -->
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-gray-500 text-sm uppercase">Akan Bergabung</h3>
                <p class="text-3xl font-bold" id="upcoming-interns">0</p>
                <div class="flex justify-between mt-2">
                    <span class="text-sm text-gray-500">Belum mulai</span>
                </div>
            </div>
        </div>
        
        <!-- Interns completing soon -->
        <div id="completing-soon-container" class="mb-6 hidden">
            <h3 class="text-lg font-semibold mb-3">Peserta yang Akan Selesai dalam 7 Hari</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                        </tr>
                    </thead>
                    <tbody id="completing-soon-body" class="bg-white divide-y divide-gray-200">
                        <!-- Will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Loading indicator -->
        <div id="loading-indicator" class="text-center py-8 hidden">
            <div role="status">
                <svg aria-hidden="true" class="inline w-8 h-8 mr-2 text-gray-200 animate-spin fill-green-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM elements
        const checkDateInput = document.getElementById('check-date');
        const checkButton = document.getElementById('check-button');
        const resultsContainer = document.getElementById('results-container');
        const resultDate = document.getElementById('result-date');
        const availabilityStatus = document.getElementById('availability-status');
        const totalOccupied = document.getElementById('total-occupied');
        const percentageOccupied = document.getElementById('percentage-occupied');
        const progressBar = document.getElementById('progress-bar');
        const currentActive = document.getElementById('current-active');
        const upcomingInterns = document.getElementById('upcoming-interns');
        const completingSoonContainer = document.getElementById('completing-soon-container');
        const completingSoonBody = document.getElementById('completing-soon-body');
        const loadingIndicator = document.getElementById('loading-indicator');
        
        // Check availability on page load
        checkAvailability();
        
        // Event listeners
        checkButton.addEventListener('click', checkAvailability);
        
        // Function to check availability
        function checkAvailability() {
            showLoading();
            
            const date = checkDateInput.value;
            const url = "{{ route('api.interns.checkAvailability') }}";
            
            fetch(`${url}?date=${date}`, {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    // Show results container
                    resultsContainer.classList.remove('hidden');
                    
                    // Update date
                    resultDate.textContent = formatDate(data.date);
                    
                    // Update availability status
                    if (data.available) {
                        availabilityStatus.className = 'mb-6 p-4 rounded-lg bg-green-100 text-green-800';
                        availabilityStatus.innerHTML = `
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium">${data.message}</span>
                            </div>`;
                    } else {
                        availabilityStatus.className = 'mb-6 p-4 rounded-lg bg-red-100 text-red-800';
                        availabilityStatus.innerHTML = `
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="font-medium">${data.message}</span>
                            </div>`;
                    }
                    
                    // Update stats
                    totalOccupied.textContent = data.totalOccupied;
                    currentActive.textContent = data.currentActive;
                    upcomingInterns.textContent = data.upcomingInterns;
                    
                    // Calculate and update percentage
                    const percentage = Math.min(100, Math.round((data.totalOccupied / 50) * 100));
                    percentageOccupied.textContent = `${percentage}%`;
                    progressBar.style.width = `${percentage}%`;
                    
                    // Progress bar color based on occupancy
                    if (percentage < 60) {
                        progressBar.className = 'bg-green-500 h-2.5 rounded-full';
                    } else if (percentage < 85) {
                        progressBar.className = 'bg-yellow-500 h-2.5 rounded-full';
                    } else {
                        progressBar.className = 'bg-red-500 h-2.5 rounded-full';
                    }
                    
                    // Load completing soon interns
                    loadCompletingSoon();
                } else {
                    // Show error message
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error checking availability:', error);
                alert('Terjadi kesalahan saat mengecek ketersediaan.');
            });
        }
        
        // Function to load completing soon interns
        function loadCompletingSoon() {
            fetch("{{ route('api.interns.getCompletingSoon') }}", {
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    completingSoonContainer.classList.remove('hidden');
                    completingSoonBody.innerHTML = '';
                    
                    data.forEach(intern => {
                        const row = document.createElement('tr');
                        
                        // Name cell
                        const nameCell = document.createElement('td');
                        nameCell.className = 'px-6 py-4 whitespace-nowrap';
                        nameCell.textContent = intern.nama;
                        row.appendChild(nameCell);
                        
                        // Department cell
                        const deptCell = document.createElement('td');
                        deptCell.className = 'px-6 py-4 whitespace-nowrap';
                        deptCell.textContent = intern.nama_bidang || 'UMUM';
                        row.appendChild(deptCell);
                        
                        // End date cell
                        const endDateCell = document.createElement('td');
                        endDateCell.className = 'px-6 py-4 whitespace-nowrap';
                        endDateCell.textContent = formatDate(intern.tanggal_keluar);
                        row.appendChild(endDateCell);
                        
                        completingSoonBody.appendChild(row);
                    });
                } else {
                    completingSoonContainer.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading completing soon interns:', error);
                completingSoonContainer.classList.add('hidden');
            });
        }
        
        // Helper function to show loading state
        function showLoading() {
            loadingIndicator.classList.remove('hidden');
        }
        
        // Helper function to hide loading state
        function hideLoading() {
            loadingIndicator.classList.add('hidden');
        }
        
        // Helper function to format date
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        }
    });
</script>
@endsection