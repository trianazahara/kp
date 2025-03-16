@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center">
            <a href="{{ route('interns.management') }}" class="text-white mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-white text-xl md:text-2xl font-bold">Detail Peserta Magang</h1>
        </div>
    </div>
    
    <!-- Profile Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row">
            <div class="w-full md:w-1/4 mb-4 md:mb-0">
                <!-- Profile Image Placeholder -->
                <div class="bg-gray-200 rounded-lg flex items-center justify-center h-40 w-40 mx-auto md:mx-0">
                    <span class="text-3xl text-gray-500">{{ strtoupper(substr($intern->nama, 0, 1)) }}</span>
                </div>
            </div>
            
            <div class="w-full md:w-3/4">
                <h2 class="text-2xl font-bold mb-2">{{ $intern->nama }}</h2>
                <p class="text-gray-600 mb-4">{{ $intern->jenis_peserta == 'mahasiswa' ? 'Mahasiswa' : 'Siswa' }} {{ $intern->nama_institusi }}</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Basic Info -->
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">
                            @if($intern->status == 'aktif')
                                <span class="text-green-600">Aktif</span>
                            @elseif($intern->status == 'selesai')
                                <span class="text-blue-600">Selesai</span>
                            @elseif($intern->status == 'menunggu')
                                <span class="text-yellow-600">Belum Mulai</span>
                            @else
                                <span class="text-red-600">{{ ucfirst($intern->status) }}</span>
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Ruang Penempatan</p>
                        <p class="font-medium">{{ $intern->nama_bidang ?? 'UMUM' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Mulai</p>
                        <p class="font-medium">{{ date('d/m/Y', strtotime($intern->tanggal_masuk)) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Selesai</p>
                        <p class="font-medium">{{ date('d/m/Y', strtotime($intern->tanggal_keluar)) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="font-medium">{{ $intern->email ?: '-' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Nomor HP</p>
                        <p class="font-medium">{{ $intern->no_hp ?: '-' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Pembimbing Mentor</p>
                        <p class="font-medium">{{ $intern->mentor_name ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Info Sections -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Academic Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Informasi {{ $intern->jenis_peserta == 'mahasiswa' ? 'Akademik' : 'Sekolah' }}</h3>
            
            @if($intern->jenis_peserta == 'mahasiswa')
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">NIM</p>
                        <p class="font-medium">{{ $intern->dataMahasiswa->nim ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Fakultas</p>
                        <p class="font-medium">{{ $intern->dataMahasiswa->fakultas ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Jurusan</p>
                        <p class="font-medium">{{ $intern->dataMahasiswa->jurusan ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Semester</p>
                        <p class="font-medium">{{ $intern->dataMahasiswa->semester ?? '-' }}</p>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">NISN</p>
                        <p class="font-medium">{{ $intern->dataSiswa->nisn ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Kelas</p>
                        <p class="font-medium">{{ $intern->dataSiswa->kelas ?? '-' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Jurusan</p>
                        <p class="font-medium">{{ $intern->dataSiswa->jurusan ?? '-' }}</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Institution Advisor Info -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4 pb-2 border-b">Informasi Pembimbing dari Institusi</h3>
            
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Nama Pembimbing</p>
                    <p class="font-medium">{{ $intern->nama_pembimbing ?: '-' }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Telepon Pembimbing</p>
                    <p class="font-medium">{{ $intern->telp_pembimbing ?: '-' }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Jenis Institusi</p>
                    <p class="font-medium">{{ $intern->jenis_institusi ?: '-' }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Nama Institusi</p>
                    <p class="font-medium">{{ $intern->nama_institusi ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('interns.edit', $intern->id_magang) }}" class="px-5 py-2.5 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-300">
            Edit
        </a>
        
        <!-- Conditionally show Print Certificate button if status is 'selesai' -->
        @if($intern->status == 'selesai')
            <a href="#" class="px-5 py-2.5 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-300">
                Cetak Sertifikat
            </a>
        @endif
    </div>
</div>
@endsection