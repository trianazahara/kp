<!-- resources/views/interns/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center">
            <a href="{{ route('interns.management') }}" class="text-white mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-white text-xl md:text-2xl font-bold">Edit Peserta Magang</h1>
        </div>
    </div>
    
    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="edit-intern-form">
            @csrf
            <input type="hidden" name="id_magang" value="{{ $intern->id_magang }}">
            
            <!-- Basic Information Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 pb-2 border-b">Informasi Dasar</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="nama" name="nama" value="{{ $intern->nama }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    
                    <!-- Institution Type -->
                    <div>
                        <label for="jenis_institusi" class="block mb-2 text-sm font-medium text-gray-700">Jenis Institusi <span class="text-red-500">*</span></label>
                        <select id="jenis_institusi" name="jenis_institusi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                            <option value="">Pilih jenis institusi</option>
                            <option value="Sekolah" {{ $intern->jenis_institusi == 'Sekolah' ? 'selected' : '' }}>Sekolah</option>
                            <option value="Universitas" {{ $intern->jenis_institusi == 'Universitas' ? 'selected' : '' }}>Universitas</option>
                            <option value="Politeknik" {{ $intern->jenis_institusi == 'Politeknik' ? 'selected' : '' }}>Politeknik</option>
                            <option value="Institut" {{ $intern->jenis_institusi == 'Institut' ? 'selected' : '' }}>Institut</option>
                            <option value="Lainnya" {{ $intern->jenis_institusi == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    
                    <!-- Institution Name -->
                    <div>
                        <label for="nama_institusi" class="block mb-2 text-sm font-medium text-gray-700">Nama Institusi <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_institusi" name="nama_institusi" value="{{ $intern->nama_institusi }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    
                    <!-- Participant Type -->
                    <div>
                        <label for="jenis_peserta" class="block mb-2 text-sm font-medium text-gray-700">Jenis Peserta <span class="text-red-500">*</span></label>
                        <select id="jenis_peserta" name="jenis_peserta" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                            <option value="">Pilih jenis peserta</option>
                            <option value="mahasiswa" {{ $intern->jenis_peserta == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="siswa" {{ $intern->jenis_peserta == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        </select>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ $intern->email }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Phone Number -->
                    <div>
                        <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700">Nomor HP</label>
                        <input type="text" id="no_hp" name="no_hp" value="{{ $intern->no_hp }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Department -->
                    <div>
                        <label for="bidang_id" class="block mb-2 text-sm font-medium text-gray-700">Bidang Penempatan</label>
                        <select id="bidang_id" name="bidang_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                            <option value="">UMUM</option>
                            @foreach($bidangs as $bidang)
                                <option value="{{ $bidang->id_bidang }}" {{ $intern->id_bidang == $bidang->id_bidang ? 'selected' : '' }}>{{ $bidang->nama_bidang }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Mentor -->
                    <div>
                        <label for="mentor_id" class="block mb-2 text-sm font-medium text-gray-700">Mentor / Pembimbing</label>
                        <select id="mentor_id" name="mentor_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                            <option value="">Pilih mentor</option>
                            @foreach($mentors as $mentor)
                                <option value="{{ $mentor->id_users }}" {{ $intern->mentor_id == $mentor->id_users ? 'selected' : '' }}>{{ $mentor->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Start Date -->
                    <div>
                        <label for="tanggal_masuk" class="block mb-2 text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="{{ date('Y-m-d', strtotime($intern->tanggal_masuk)) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    
                    <!-- End Date -->
                    <div>
                        <label for="tanggal_keluar" class="block mb-2 text-sm font-medium text-gray-700">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggal_keluar" name="tanggal_keluar" value="{{ date('Y-m-d', strtotime($intern->tanggal_keluar)) }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                </div>
            </div>
            
            <!-- Advisor Information Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 pb-2 border-b">Informasi Pembimbing dari Institusi</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Advisor Name -->
                    <div>
                        <label for="nama_pembimbing" class="block mb-2 text-sm font-medium text-gray-700">Nama Pembimbing</label>
                        <input type="text" id="nama_pembimbing" name="nama_pembimbing" value="{{ $intern->nama_pembimbing }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Advisor Phone -->
                    <div>
                        <label for="telp_pembimbing" class="block mb-2 text-sm font-medium text-gray-700">Telepon Pembimbing</label>
                        <input type="text" id="telp_pembimbing" name="telp_pembimbing" value="{{ $intern->telp_pembimbing }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                </div>
            </div>
            
            <!-- Student Specific Information Section -->
            <div id="mahasiswa-section" class="mb-8 {{ $intern->jenis_peserta == 'mahasiswa' ? '' : 'hidden' }}">
                <h2 class="text-lg font-semibold mb-4 pb-2 border-b">Detail Mahasiswa</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- NIM -->
                    <div>
                        <label for="nim" class="block mb-2 text-sm font-medium text-gray-700">NIM <span class="text-red-500">*</span></label>
                        <input type="text" id="nim" name="nim" value="{{ $intern->dataMahasiswa->nim ?? '' }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Faculty -->
                    <div>
                        <label for="fakultas" class="block mb-2 text-sm font-medium text-gray-700">Fakultas</label>
                        <input type="text" id="fakultas" name="fakultas" value="{{ $intern->dataMahasiswa->fakultas ?? '' }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Major -->
                    <div>
                        <label for="jurusan_mahasiswa" class="block mb-2 text-sm font-medium text-gray-700">Jurusan <span class="text-red-500">*</span></label>
                        <input type="text" id="jurusan_mahasiswa" name="jurusan_mahasiswa" value="{{ $intern->dataMahasiswa->jurusan ?? '' }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Semester -->
                    <div>
                        <label for="semester" class="block mb-2 text-sm font-medium text-gray-700">Semester</label>
                        <input type="number" id="semester" name="semester" value="{{ $intern->dataMahasiswa->semester ?? '' }}" min="1" max="14" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                </div>
            </div>
            
            <!-- Student Specific Information Section -->
            <div id="siswa-section" class="mb-8 {{ $intern->jenis_peserta == 'siswa' ? '' : 'hidden' }}">
                <h2 class="text-lg font-semibold mb-4 pb-2 border-b">Detail Siswa</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- NISN -->
                    <div>
                        <label for="nisn" class="block mb-2 text-sm font-medium text-gray-700">NISN <span class="text-red-500">*</span></label>
                        <input type="text" id="nisn" name="nisn" value="{{ $intern->dataSiswa->nisn ?? '' }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Class -->
                    <div>
                        <label for="kelas" class="block mb-2 text-sm font-medium text-gray-700">Kelas</label>
                        <input type="text" id="kelas" name="kelas" value="{{ $intern->dataSiswa->kelas ?? '' }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Major -->
                    <div>
                        <label for="jurusan_siswa" class="block mb-2 text-sm font-medium text-gray-700">Jurusan <span class="text-red-500">*</span></label>
                        <input type="text" id="jurusan_siswa" name="jurusan_siswa" value="{{ $intern->dataSiswa->jurusan ?? '' }}" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('interns.management') }}" class="px-5 py-2.5 bg-gray-300 text-gray-800 font-medium rounded-lg hover:bg-gray-400 focus:outline-none focus:ring-4 focus:ring-gray-300">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-green-500 text-white font-medium rounded-lg hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-300">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM elements
        const form = document.getElementById('edit-intern-form');
        const jenisPesertaSelect = document.getElementById('jenis_peserta');
        const mahasiswaSection = document.getElementById('mahasiswa-section');
        const siswaSection = document.getElementById('siswa-section');
        const tanggalMasukInput = document.getElementById('tanggal_masuk');
        const tanggalKeluarInput = document.getElementById('tanggal_keluar');
        
        // Show/hide sections based on participant type
        jenisPesertaSelect.addEventListener('change', function() {
            mahasiswaSection.classList.add('hidden');
            siswaSection.classList.add('hidden');
            
            if (this.value === 'mahasiswa') {
                mahasiswaSection.classList.remove('hidden');
                resetFields(siswaSection);
            } else if (this.value === 'siswa') {
                siswaSection.classList.remove('hidden');
                resetFields(mahasiswaSection);
            }
        });
        
        // Make sure end date is after start date
        tanggalMasukInput.addEventListener('change', function() {
            tanggalKeluarInput.min = this.value;
            if (tanggalKeluarInput.value && tanggalKeluarInput.value < this.value) {
                tanggalKeluarInput.value = this.value;
            }
        });
        
      // Form submit handler pada edit.blade.php
// Form submit handler pada edit.blade.php
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Buat objek untuk detail_peserta
    const detailPeserta = {};
    
    // Ambil jenis peserta
    const jenisPeserta = document.getElementById('jenis_peserta').value;
    
    // Validasi dan isi detail_peserta sesuai jenis peserta
    if (jenisPeserta === 'mahasiswa') {
        detailPeserta.nim = document.getElementById('nim').value;
        detailPeserta.jurusan = document.getElementById('jurusan_mahasiswa').value;
        detailPeserta.fakultas = document.getElementById('fakultas').value || null;
        detailPeserta.semester = document.getElementById('semester').value ? parseInt(document.getElementById('semester').value) : null;
    } else if (jenisPeserta === 'siswa') {
        detailPeserta.nisn = document.getElementById('nisn').value;
        detailPeserta.jurusan = document.getElementById('jurusan_siswa').value;
        detailPeserta.kelas = document.getElementById('kelas').value || null;
    }
    
    // Ubah FormData menjadi objek JSON
    const postData = {
        _token: document.querySelector('input[name="_token"]').value,
        nama: document.getElementById('nama').value,
        jenis_institusi: document.getElementById('jenis_institusi').value,
        nama_institusi: document.getElementById('nama_institusi').value,
        jenis_peserta: jenisPeserta,
        email: document.getElementById('email').value || '',
        no_hp: document.getElementById('no_hp').value || '',
        bidang_id: document.getElementById('bidang_id').value || '',
        mentor_id: document.getElementById('mentor_id').value || '',
        tanggal_masuk: document.getElementById('tanggal_masuk').value,
        tanggal_keluar: document.getElementById('tanggal_keluar').value,
        nama_pembimbing: document.getElementById('nama_pembimbing').value || '',
        telp_pembimbing: document.getElementById('telp_pembimbing').value || '',
        detail_peserta: detailPeserta
    };
    
    // Kirim data dengan format JSON
    fetch('/api/interns/update/{{ $intern->id_magang }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(postData)
    })
    .then(response => {
        // Handle response
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Terjadi kesalahan server');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            alert('Data peserta magang berhasil diperbarui');
            window.location.href = '/dashboard/interns';
        } else {
            alert('Gagal memperbarui data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Terjadi kesalahan saat memperbarui data');
    });
});
        
        // Helper function to reset fields in a section
        function resetFields(section) {
            const inputs = section.querySelectorAll('input');
            inputs.forEach(input => {
                input.value = '';
            });
        }
    });
</script>
@endsection