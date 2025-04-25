@extends('layouts.app')

@section('title', 'Riwayat Data Anak Magang')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-gradient-to-r from-green-400 to-blue-500 text-white p-6 rounded-lg shadow-lg mb-6">
        <h1 class="text-2xl font-bold">Riwayat Data Anak Magang</h1>
    </div>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <input type="text" placeholder="Search nama/institusi" class="w-full px-4 py-2 border border-gray-300 rounded-md">
        </div>
        <div>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <option value="">Semua Bidang</option>
                @foreach($bidangs as $bidang)
                    <option value="{{ $bidang->id_bidang }}">{{ $bidang->nama_bidang }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select class="w-full px-4 py-2 border border-gray-300 rounded-md">
                <option value="">Semua Status</option>
                <option value="selesai">Selesai</option>
                <option value="almost">Hampir Selesai</option>
                <option value="missing">Missing</option>
            </select>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institusi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Masuk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Keluar</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($interns as $intern)
                <tr class="hover:bg-gray-100">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $intern->nama }}
                        @if(isset($intern->has_scores) && $intern->has_scores)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500 inline-block ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $intern->nama_institusi }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $intern->nama_bidang }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ date('d/m/Y', strtotime($intern->tanggal_masuk)) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ date('d/m/Y', strtotime($intern->tanggal_keluar)) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $statusClass = match($intern->status) {
                                'selesai' => 'bg-blue-100 text-blue-800 border-blue-800',
                                'missing' => 'bg-red-100 text-red-800 border-red-800',
                                'almost' => 'bg-yellow-100 text-yellow-800 border-yellow-800',
                                default => 'bg-gray-100 text-gray-800 border-gray-800'
                            };
                            $statusLabel = match($intern->status) {
                                'selesai' => 'Selesai',
                                'missing' => 'Missing',
                                'almost' => 'Hampir Selesai',
                                default => ucfirst($intern->status)
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @if($intern->status !== 'missing')
                            @if(isset($intern->has_scores) && $intern->has_scores)
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
                            @else
                                <button 
                                    class="p-1 rounded-full text-blue-600 hover:bg-blue-100 add-score-btn"
                                    title="Tambah penilaian"
                                    data-id="{{ $intern->id_magang }}"
                                    data-name="{{ $intern->nama }}"
                                    data-start="{{ $intern->tanggal_masuk }}"
                                    data-end="{{ $intern->tanggal_keluar }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data yang tersedia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-between">
        <div>
            <span class="text-sm text-gray-700">
                Menampilkan {{ $interns->count() }} dari total {{ $interns->total() }} data
            </span>
        </div>
        <div class="flex space-x-2">
            @if ($interns->onFirstPage())
                <button disabled class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-gray-100 text-gray-400">
                    Previous
                </button>
            @else
                <a href="{{ $interns->previousPageUrl() }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-700 hover:bg-gray-50">
                    Previous
                </a>
            @endif
            
            <span class="px-4 py-2 bg-blue-50 text-blue-600 rounded-md text-sm font-medium">
                {{ $interns->currentPage() }}
            </span>
            
            @if ($interns->hasMorePages())
                <a href="{{ $interns->nextPageUrl() }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-white text-gray-700 hover:bg-gray-50">
                    Next
                </a>
            @else
                <button disabled class="px-4 py-2 border border-gray-300 rounded-md text-sm bg-gray-100 text-gray-400">
                    Next
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Modal Form Input Nilai -->
<div id="nilaiModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Edit Nilai - <span id="internName"></span></h2>
        </div>
        <form id="scoreForm" method="POST" action="">
            @csrf
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Attendance field -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">JUMLAH KEHADIRAN</label>
                    <input
                        type="number"
                        name="jumlah_hadir"
                        id="jumlah_hadir"
                        min="0"
                        step="1"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <p class="text-xs text-gray-500 mt-1">Total hari kerja: <span id="totalWorkingDays">0</span> hari</p>
                </div>

                <!-- Score fields -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">TEAMWORK</label>
                    <input type="number" name="nilai_teamwork" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">KOMUNIKASI</label>
                    <input type="number" name="nilai_komunikasi" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PENGAMBILAN KEPUTUSAN</label>
                    <input type="number" name="nilai_pengambilan_keputusan" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">KUALITAS KERJA</label>
                    <input type="number" name="nilai_kualitas_kerja" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">TEKNOLOGI</label>
                    <input type="number" name="nilai_teknologi" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DISIPLIN</label>
                    <input type="number" name="nilai_disiplin" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">TANGGUNG JAWAB</label>
                    <input type="number" name="nilai_tanggungjawab" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">KERJASAMA</label>
                    <input type="number" name="nilai_kerjasama" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">KEJUJURAN</label>
                    <input type="number" name="nilai_kejujuran" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">KEBERSIHAN</label>
                    <input type="number" name="nilai_kebersihan" min="0" max="100" step="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            <div class="p-4 border-t flex justify-end gap-2">
                <button type="button" id="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                    Simpan
                </button>
            </div>
        </form>
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
@endsection

@section('scripts')
<script>
    // Fungsi untuk menghitung hari kerja
    function calculateWorkingDays(startDate, endDate) {
        if (!startDate || !endDate) return 0;
        
        const start = new Date(startDate);
        const end = new Date(endDate);
        
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

    // Fungsi untuk menampilkan toast notification
    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        
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

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('nilaiModal');
        const closeModalBtn = document.getElementById('closeModal');
        const scoreForm = document.getElementById('scoreForm');
        const internName = document.getElementById('internName');
        const totalWorkingDays = document.getElementById('totalWorkingDays');
        
        // Close modal when clicking the close button
        closeModalBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
        
        // Setup add score buttons
        document.querySelectorAll('.add-score-btn').forEach(button => {
            button.addEventListener('click', function() {
                const internId = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const startDate = this.getAttribute('data-start');
                const endDate = this.getAttribute('data-end');
                
                // Set form action URL
                scoreForm.action = `/api/assessments/add-score/${internId}`;
                
                // Set intern name in modal title
                internName.textContent = name;
                
                // Calculate working days
                const workingDays = calculateWorkingDays(startDate, endDate);
                totalWorkingDays.textContent = workingDays;
                
                // Reset form
                scoreForm.reset();
                
                // Show modal
                modal.classList.remove('hidden');
            });
        });
        
        // Handle form submission
        scoreForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {};
            
            // Convert FormData to JSON object
            for (const [key, value] of formData.entries()) {
                data[key] = Number(value);
            }
            
            // Send data using fetch
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    showToast('Nilai berhasil disimpan');
                    modal.classList.add('hidden');
                    
                    // Reload page after successful submission
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(result.message || 'Terjadi kesalahan', false);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan pada server', false);
            });
        });
    });
</script>
@endsection