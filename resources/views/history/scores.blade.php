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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Detail view button - Mengubah menjadi redirect ke halaman detail
    const viewDetailButtons = document.querySelectorAll('.view-detail');
    viewDetailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            console.log('View detail for ID:', id);
            // Redirect ke halaman detail
            window.location.href = `/dashboard/interns/detail/${id}`;
        });
    });

    // Event handlers for edit and generate certificate buttons
    document.querySelectorAll('.edit-score').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            console.log('Edit score for ID:', id);
            window.location.href = `/nilai/edit/${id}`;
        });
    });
    
    document.querySelectorAll('.generate-cert').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            console.log('Generate certificate for ID:', id);
            window.open(`/sertifikat/generate/${id}`, '_blank');
        });
    });

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
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Memuat data...</td></tr>';
        
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
                    tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Error: ' + (data.message || 'Terjadi kesalahan') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Kesalahan fetch:', error);
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-red-500">Terjadi kesalahan saat mengambil data: ' + error.message + '</td></tr>';
            });
    }
    
    // Fungsi untuk menampilkan data di tabel
    function renderTable(data) {
        console.log('Rendering table with data:', data);
        
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
        
        // Add event listeners for buttons
        document.querySelectorAll('.view-detail').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                console.log('View detail for ID:', id);
                // Redirect ke halaman detail
                window.location.href = `/dashboard/interns/detail/${id}`;
            });
        });
        
        document.querySelectorAll('.edit-score').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                console.log('Edit score for ID:', id);
                window.location.href = `/nilai/edit/${id}`;
            });
        });
        
        document.querySelectorAll('.generate-cert').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                console.log('Generate certificate for ID:', id);
                window.open(`/sertifikat/generate/${id}`, '_blank');
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
});
</script>
@endsection