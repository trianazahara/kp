@extends('layouts.app')

@section('title', 'Dashboard - PANDU')

@section('content')
<div class="p-6 animate-fadeIn">
    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <!-- Anak Magang Aktif Card -->
        <div class="group perspective">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transform transition-all duration-500 hover:scale-110 hover:rotate-2 animate-slideRight hover:shadow-xl hover:shadow-emerald-200/50 group-hover:z-10">
                <div class="flex justify-between items-start">
                    <div class="transform transition-all duration-500 group-hover:translate-x-2">
                        <p class="text-emerald-500 text-lg font-medium mb-2">Total Peserta Magang Aktif</p>
                        <h3 class="text-4xl font-bold text-emerald-500" id="active-interns-count">{{ $stats['activeInterns']['total'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-emerald-100 p-3 rounded-lg transform transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <i class="fas fa-users text-emerald-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Selesai Card -->
        <div class="group perspective">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transform transition-all duration-500 hover:scale-110 hover:rotate-2 animate-slideUp delay-100 hover:shadow-xl hover:shadow-rose-200/50 group-hover:z-10">
                <div class="flex justify-between items-start">
                    <div class="transform transition-all duration-500 group-hover:translate-x-2">
                        <p class="text-emerald-500 text-lg font-medium mb-2">Total Peserta Magang Selesai</p>
                        <h3 class="text-4xl font-bold text-emerald-500" id="completed-interns-count">{{ $stats['completedInterns'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-rose-100 p-3 rounded-lg transform transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <i class="fas fa-check-circle text-rose-500"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Keseluruhan Card -->
        <div class="group perspective">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transform transition-all duration-500 hover:scale-110 hover:rotate-2 animate-slideLeft delay-200 hover:shadow-xl hover:shadow-amber-200/50 group-hover:z-10">
                <div class="flex justify-between items-start">
                    <div class="transform transition-all duration-500 group-hover:translate-x-2">
                        <p class="text-emerald-500 text-lg font-medium mb-2">Total Peserta Magang</p>
                        <h3 class="text-4xl font-bold text-emerald-500" id="total-interns-count">{{ $stats['totalInterns'] ?? 0 }}</h3>
                    </div>
                    <div class="bg-amber-100 p-3 rounded-lg transform transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <i class="fas fa-cube text-amber-500"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Cards with Enhanced Animations -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <!-- Card Berdasarkan Jenis Peserta -->
        <div class="group perspective">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transform transition-all duration-500 hover:scale-105 hover:rotate-1 animate-slideRight delay-300 hover:shadow-xl hover:shadow-blue-200/50">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-emerald-500 text-xl font-medium transform transition-all duration-500 group-hover:translate-x-2">Peserta Magang Aktif Berdasarkan Jenis</p>
                    <div class="bg-blue-100 p-3 rounded-lg transform transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <i class="fas fa-user-graduate text-blue-500"></i>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-2 rounded-lg transform transition-all duration-300 hover:bg-blue-50 hover:translate-x-2">
                        <span class="text-gray-600">Mahasiswa</span>
                        <span class="text-lg font-semibold" id="mahasiswa-count">{{ $stats['activeInterns']['students']['mahasiswa'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center p-2 rounded-lg transform transition-all duration-300 hover:bg-blue-50 hover:translate-x-2">
                        <span class="text-gray-600">Siswa</span>
                        <span class="text-lg font-semibold" id="siswa-count">{{ $stats['activeInterns']['students']['siswa'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Berdasarkan Bidang -->
        <div class="group perspective">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 transform transition-all duration-500 hover:scale-105 hover:rotate-1 animate-slideLeft delay-300 hover:shadow-xl hover:shadow-purple-200/50">
                <div class="flex justify-between items-start mb-4">
                    <p class="text-emerald-500 text-xl font-medium transform transition-all duration-500 group-hover:translate-x-2">Peserta Magang Aktif Per Bidang</p>
                    <div class="bg-purple-100 p-3 rounded-lg transform transition-all duration-500 group-hover:rotate-12 group-hover:scale-110">
                        <i class="fas fa-briefcase text-purple-500"></i>
                    </div>
                </div>
                <div class="space-y-3" id="departments-container">
                    @foreach($stats['activeInterns']['byDepartment'] ?? [] as $department => $count)
                    <div class="flex justify-between items-center p-2 rounded-lg transform transition-all duration-300 hover:bg-purple-50 hover:translate-x-2">
                        <span class="text-gray-600 capitalize">{{ $department }}</span>
                        <span class="text-lg font-semibold">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Section -->
    <div class="mt-8 animate-slideUp delay-400">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Peserta Magang yang Akan Selesai dalam 7 Hari</h2>
            <div class="bg-yellow-100 px-3 py-1 rounded-lg transform transition-all duration-300 hover:scale-105">
                <span class="text-yellow-700 font-medium" id="completing-soon-count">{{ $stats['completingSoon']['count'] ?? 0 }} Orang</span>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 transform transition-all duration-300 hover:shadow-lg">
            <div id="completing-soon-table-container">
                @if(isset($stats['completingSoon']['interns']) && 
                    (is_array($stats['completingSoon']['interns']) || $stats['completingSoon']['interns'] instanceof \Countable) && 
                    count($stats['completingSoon']['interns']) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($stats['completingSoon']['interns'] as $intern)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">{{ is_object($intern) ? $intern->nama : ($intern['nama'] ?? '-') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ is_object($intern) ? $intern->nama_bidang : ($intern['nama_bidang'] ?? '-') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(is_object($intern) && isset($intern->tanggal_keluar))
                                        {{ \Carbon\Carbon::parse($intern->tanggal_keluar)->format('d M Y') }}
                                    @elseif(is_array($intern) && isset($intern['tanggal_keluar']))
                                        {{ \Carbon\Carbon::parse($intern['tanggal_keluar'])->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-gray-500">
                    <p>Tidak ada peserta magang yang akan selesai dalam 7 hari ke depan</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh dashboard data periodically
        function refreshDashboardData() {
            axios.get('/api/dashboard/refresh')
                .then(function(response) {
                    const stats = response.data;
                    
                    // Debug output to console
                    console.log('Dashboard refresh data:', stats);
                    console.log('Completing soon data:', stats.completingSoon);
                    
                    // Update stats counts
                    document.getElementById('active-interns-count').textContent = stats.activeInterns.total;
                    document.getElementById('completed-interns-count').textContent = stats.completedInterns;
                    document.getElementById('total-interns-count').textContent = stats.totalInterns;
                    document.getElementById('mahasiswa-count').textContent = stats.activeInterns.students.mahasiswa;
                    document.getElementById('siswa-count').textContent = stats.activeInterns.students.siswa;
                    document.getElementById('completing-soon-count').textContent = stats.completingSoon.count + ' Orang';
                    
                    // Update departments
                    const departmentsContainer = document.getElementById('departments-container');
                    departmentsContainer.innerHTML = '';
                    
                    // Check if byDepartment exists and has data
                    if (stats.activeInterns.byDepartment && Object.keys(stats.activeInterns.byDepartment).length > 0) {
                        Object.entries(stats.activeInterns.byDepartment).forEach(([department, count]) => {
                            const departmentDiv = document.createElement('div');
                            departmentDiv.className = 'flex justify-between items-center p-2 rounded-lg transform transition-all duration-300 hover:bg-purple-50 hover:translate-x-2';
                            departmentDiv.innerHTML = `
                                <span class="text-gray-600 capitalize">${department}</span>
                                <span class="text-lg font-semibold">${count}</span>
                            `;
                            departmentsContainer.appendChild(departmentDiv);
                        });
                    } else {
                        departmentsContainer.innerHTML = '<div class="text-center py-4 text-gray-500"><p>Tidak ada data departemen</p></div>';
                    }
                    
                    // Update completing soon table
                    const completingSoonTableContainer = document.getElementById('completing-soon-table-container');
                    
                    if (stats.completingSoon.interns && stats.completingSoon.interns.length > 0) {
                        let tableHTML = `
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bidang</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                        `;
                        
                        stats.completingSoon.interns.forEach(intern => {
                            const tanggalKeluar = new Date(intern.tanggal_keluar);
                            const formattedDate = tanggalKeluar.toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric'
                            });
                            
                            tableHTML += `
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">${intern.nama || '-'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${intern.nama_bidang || '-'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${formattedDate}</td>
                                </tr>
                            `;
                        });
                        
                        tableHTML += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                        
                        completingSoonTableContainer.innerHTML = tableHTML;
                    } else {
                        completingSoonTableContainer.innerHTML = '<div class="text-center py-4 text-gray-500"><p>Tidak ada peserta magang yang akan selesai dalam 7 hari ke depan</p></div>';
                    }
                })
                .catch(function(error) {
                    console.error('Error fetching dashboard data:', error);
                });
        }
        
        // Refresh data every 60 seconds
        setInterval(refreshDashboardData, 60000);
        
        // Also refresh immediately when page loads
        refreshDashboardData();
    });
</script>
@endsection