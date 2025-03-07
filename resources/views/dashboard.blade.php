@extends('layouts.app')

@section('title', 'Dashboard - PANDU')

@section('content')
@php
    // Definisikan variabel $stats dengan nilai default jika tidak ada
    $stats = $stats ?? [
        'activeInterns' => [
            'total' => 0,
            'students' => [
                'mahasiswa' => 0,
                'siswa' => 0
            ],
            'byDepartment' => []
        ],
        'completedInterns' => 0,
        'totalInterns' => 0,
        'completingSoon' => [
            'count' => 0,
            'interns' => []
        ]
    ];
@endphp

<div class="dashboard-container space-y-6">
    <!-- Top Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Active Interns -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 relative overflow-hidden">
            <h3 class="text-primary font-medium text-base mb-2">Total Peserta Magang Aktif</h3>
            <div class="text-primary text-4xl font-bold" id="active-interns-count">{{ $stats['activeInterns']['total'] ?? 0 }}</div>
            <div class="absolute top-1/2 -translate-y-1/2 right-5">
                <div class="h-14 w-14 rounded-full bg-green-50 flex items-center justify-center text-primary">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Completed Interns -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 relative overflow-hidden">
            <h3 class="text-primary font-medium text-base mb-2">Total Peserta Magang Selesai</h3>
            <div class="text-primary text-4xl font-bold" id="completed-interns-count">{{ $stats['completedInterns'] ?? 0 }}</div>
            <div class="absolute top-1/2 -translate-y-1/2 right-5">
                <div class="h-14 w-14 rounded-full bg-green-50 flex items-center justify-center text-primary">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Total Interns -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 relative overflow-hidden">
            <h3 class="text-primary font-medium text-base mb-2">Total Peserta magang</h3>
            <div class="text-primary text-4xl font-bold" id="total-interns-count">{{ $stats['totalInterns'] ?? 0 }}</div>
            <div class="absolute top-1/2 -translate-y-1/2 right-5">
                <div class="h-14 w-14 rounded-full bg-green-50 flex items-center justify-center text-primary">
                    <i class="fas fa-cube text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Middle Section - Active Interns by Department and Type -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Active Interns by Type -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 relative overflow-hidden">
            <h3 class="text-primary font-medium text-base mb-4">Peserta Magang Aktif Per Bidang</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Mahasiswa</span>
                    <span class="font-semibold">{{ $stats['activeInterns']['students']['mahasiswa'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Siswa</span>
                    <span class="font-semibold">{{ $stats['activeInterns']['students']['siswa'] ?? 0 }}</span>
                </div>
            </div>
            <div class="absolute top-1/2 -translate-y-1/2 right-5">
                <div class="h-14 w-14 rounded-full bg-green-50 flex items-center justify-center text-primary">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
            </div>
        </div>
        
        <!-- Active Interns by Department -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 relative overflow-hidden">
            <h3 class="text-primary font-medium text-base mb-4">Peserta Magang Aktif Per Bidang</h3>
            <div class="space-y-3">
                @foreach($stats['activeInterns']['byDepartment'] ?? [] as $department => $count)
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">{{ ucfirst($department) }}</span>
                    <span class="font-semibold">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            <div class="absolute top-1/2 -translate-y-1/2 right-5">
                <div class="h-14 w-14 rounded-full bg-green-50 flex items-center justify-center text-primary">
                    <i class="fas fa-building text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Section - Interns Completing Soon -->
    <div>
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Peserta Magang yang akan selesai dalam 7 hari</h2>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if(isset($stats['completingSoon']['interns']) && count($stats['completingSoon']['interns']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Nama</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Institusi</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Bidang</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Tanggal Selesai</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($stats['completingSoon']['interns'] as $intern)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $intern->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $intern->nama_institusi ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $intern->nama_bidang ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if(isset($intern->tanggal_keluar))
                                    {{ \Carbon\Carbon::parse($intern->tanggal_keluar)->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if(isset($intern->id_magang))
                                <a href="{{ route('interns.show', $intern->id_magang) }}" class="text-primary hover:underline">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-8 text-center text-gray-500">
                <p>Tidak ada peserta magang yang akan selesai dalam 7 hari kedepan</p>
            </div>
            @endif
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
                    
                    // Update stats counts
                    document.getElementById('active-interns-count').textContent = stats.activeInterns.total;
                    document.getElementById('completed-interns-count').textContent = stats.completedInterns;
                    document.getElementById('total-interns-count').textContent = stats.totalInterns;
                    
                    // You could add more code here to update other parts of the dashboard dynamically
                })
                .catch(function(error) {
                    console.error('Error fetching dashboard data:', error);
                });
        }
        
        // Refresh data every 60 seconds
        setInterval(refreshDashboardData, 60000);
    });
</script>
@endsection