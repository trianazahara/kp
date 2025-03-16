<!-- resources/views/interns/add.blade.php (file lengkap) -->
@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-400 to-green-500 rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center">
            <a href="{{ route('interns.management') }}" class="text-white mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-white text-xl md:text-2xl font-bold">Tambah Peserta Magang</h1>
        </div>
    </div>
    
    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="add-intern-form" method="POST" action="{{ route('api.interns.add') }}">
            @csrf
            
            <!-- Basic Information Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold mb-4 pb-2 border-b">Informasi Dasar</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="nama" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="nama" name="nama" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    
                    <!-- Institution Type -->
                    <div>
                        <label for="jenis_institusi" class="block mb-2 text-sm font-medium text-gray-700">Jenis Institusi <span class="text-red-500">*</span></label>
                        <select id="jenis_institusi" name="jenis_institusi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                            <option value="" selected disabled>Pilih jenis institusi</option>
                            <option value="Sekolah">Sekolah</option>
                            <option value="Universitas">Universitas</option>
                            <option value="Politeknik">Politeknik</option>
                            <option value="Institut">Institut</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <!-- Institution Name -->
                    <div>
                        <label for="nama_institusi" class="block mb-2 text-sm font-medium text-gray-700">Nama Institusi <span class="text-red-500">*</span></label>
                        <input type="text" id="nama_institusi" name="nama_institusi" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    
                    <!-- Participant Type -->
                    <div>
                        <label for="jenis_peserta" class="block mb-2 text-sm font-medium text-gray-700">Jenis Peserta <span class="text-red-500">*</span></label>
                        <select id="jenis_peserta" name="jenis_peserta" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                            <option value="" selected disabled>Pilih jenis peserta</option>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="siswa">Siswa</option>
                        </select>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Phone Number -->
                    <div>
                        <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-700">Nomor HP</label>
                        <input type="text" id="no_hp" name="no_hp" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Department -->
                    <div>
                        <label for="bidang_id" class="block mb-2 text-sm font-medium text-gray-700">Bidang Penempatan</label>
                        <select id="bidang_id" name="bidang_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                            <option value="">UMUM</option>
                            @foreach($bidangs as $bidang)
                                <option value="{{ $bidang->id_bidang }}">{{ $bidang->nama_bidang }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Mentor -->
                    <div>
                        <label for="mentor_id" class="block mb-2 text-sm font-medium text-gray-700">Mentor / Pembimbing</label>
                        <select id="mentor_id" name="mentor_id" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                            <option value="">Pilih mentor</option>
                            @foreach($mentors as $mentor)
                                <option value="{{ $mentor->id_users }}">{{ $mentor->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Start Date -->
                    <div>
                        <label for="tanggal_masuk" class="block mb-2 text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggal_masuk" name="tanggal_masuk" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    
                    <!-- End Date -->
                    <div>
                        <label for="tanggal_keluar" class="block mb-2 text-sm font-medium text-gray-700">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggal_keluar" name="tanggal_keluar" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
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
                        <input type="text" id="nama_pembimbing" name="nama_pembimbing" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                    
                    <!-- Advisor Phone -->
                    <div>
                        <label for="telp_pembimbing" class="block mb-2 text-sm font-medium text-gray-700">Telepon Pembimbing</label>
                        <input type="text" id="telp_pembimbing" name="telp_pembimbing" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    </div>
                </div>
            </div>
            
           <!-- resources/views/interns/add.blade.php (bagian form saja) -->
<!-- Student Specific Information Section -->
<div id="mahasiswa-section" class="mb-8 hidden">
    <h2 class="text-lg font-semibold mb-4 pb-2 border-b">Detail Mahasiswa</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- NIM -->
        <div>
            <label for="nim" class="block mb-2 text-sm font-medium text-gray-700">NIM <span class="text-red-500">*</span></label>
            <input type="text" id="nim" name="nim" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
        </div>
        
        <!-- Faculty -->
        <div>
            <label for="fakultas" class="block mb-2 text-sm font-medium text-gray-700">Fakultas</label>
            <input type="text" id="fakultas" name="fakultas" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
        </div>
        
        <!-- Major -->
        <div>
            <label for="jurusan_mahasiswa" class="block mb-2 text-sm font-medium text-gray-700">Jurusan <span class="text-red-500">*</span></label>
            <input type="text" id="jurusan_mahasiswa" name="jurusan" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
        </div>
        
        <!-- Semester -->
        <div>
            <label for="semester" class="block mb-2 text-sm font-medium text-gray-700">Semester</label>
            <input type="number" id="semester" name="semester" min="1" max="14" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
        </div>
    </div>
</div>

<!-- Student Specific Information Section -->
<div id="siswa-section" class="mb-8 hidden">
    <h2 class="text-lg font-semibold mb-4 pb-2 border-b">Detail Siswa</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- NISN -->
        <div>
            <label for="nisn" class="block mb-2 text-sm font-medium text-gray-700">NISN <span class="text-red-500">*</span></label>
            <input type="text" id="nisn" name="nisn" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
        </div>
        
        <!-- Class -->
        <div>
            <label for="kelas" class="block mb-2 text-sm font-medium text-gray-700">Kelas</label>
            <input type="text" id="kelas" name="kelas" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
        </div>
        
        <!-- Major -->
        <div>
            <label for="jurusan_siswa" class="block mb-2 text-sm font-medium text-gray-700">Jurusan <span class="text-red-500">*</span></label>
            <input type="text" id="jurusan_siswa" name="jurusan" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
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
        const form = document.getElementById('add-intern-form');
        const jenisPesertaSelect = document.getElementById('jenis_peserta');
        const mahasiswaSection = document.getElementById('mahasiswa-section');
        const siswaSection = document.getElementById('siswa-section');
        const tanggalMasukInput = document.getElementById('tanggal_masuk');
        const tanggalKeluarInput = document.getElementById('tanggal_keluar');
        
        // Set min date for tanggal_masuk to today
        const today = new Date().toISOString().split('T')[0];
        tanggalMasukInput.min = today;
        
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
        
        // Form submit handler
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Ambil nilai jenis peserta
    const jenisPeserta = jenisPesertaSelect.value;
    
    // Lakukan validasi
    if (jenisPeserta === '' || jenisPeserta === null) {
        alert('Silahkan pilih jenis peserta');
        return;
    }
    
    // Pastikan validasi form lainnya
    const namaValue = document.getElementById('nama').value;
    const jenisInstitusiValue = document.getElementById('jenis_institusi').value;
    const namaInstitusiValue = document.getElementById('nama_institusi').value;
    const tanggalMasukValue = document.getElementById('tanggal_masuk').value;
    const tanggalKeluarValue = document.getElementById('tanggal_keluar').value;
    
    if (!namaValue || !jenisInstitusiValue || !namaInstitusiValue || !tanggalMasukValue || !tanggalKeluarValue) {
        alert('Data isian utama wajib diisi');
        return;
    }
    
    // Buat FormData dari form
    const formData = new FormData(form);
    
    // Buat objek detail_peserta sesuai jenis peserta
    const detailPeserta = {};
    
    if (jenisPeserta === 'mahasiswa') {
        const nim = document.getElementById('nim').value;
        const jurusan = document.getElementById('jurusan_mahasiswa').value;
        
        if (!nim || !jurusan) {
            alert('NIM dan Jurusan wajib diisi untuk mahasiswa');
            return;
        }
        
        detailPeserta.nim = nim;
        detailPeserta.jurusan = jurusan;
        detailPeserta.fakultas = document.getElementById('fakultas').value || '';
        detailPeserta.semester = document.getElementById('semester').value || '';
        
        // Hapus field siswa dari FormData
        formData.delete('nisn');
        formData.delete('kelas');
        formData.delete('jurusan_siswa');
    } else if (jenisPeserta === 'siswa') {
        const nisn = document.getElementById('nisn').value;
        const jurusan = document.getElementById('jurusan_siswa').value;
        
        if (!nisn || !jurusan) {
            alert('NISN dan Jurusan wajib diisi untuk siswa');
            return;
        }
        
        detailPeserta.nisn = nisn;
        detailPeserta.jurusan = jurusan;
        detailPeserta.kelas = document.getElementById('kelas').value || '';
        
        // Hapus field mahasiswa dari FormData
        formData.delete('nim');
        formData.delete('fakultas');
        formData.delete('jurusan_mahasiswa');
        formData.delete('semester');
    }
    
    // Hapus semua field detail_peserta yang sudah ada
    formData.delete('detail_peserta');
    
    // Tambahkan detail_peserta sebagai JSON string
    formData.append('detail_peserta', JSON.stringify(detailPeserta));
    
    // Debug
    console.log('Data yang dikirim:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Kirim data menggunakan fetch API
    fetch("{{ route('api.interns.add') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        },
        body: formData
    })
    .then(response => {
        // Coba menangani respons non-JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.indexOf('application/json') !== -1) {
            return response.json().then(data => {
                if (!response.ok) {
                    throw data;
                }
                return data;
            });
        } else {
            // Handle non-JSON response (like HTML error page)
            return response.text().then(text => {
                throw new Error('Received non-JSON response: ' + text.substring(0, 100) + '...');
            });
        }
    })
    .then(data => {
        if (data.status === 'success') {
            alert('Data peserta magang berhasil ditambahkan');
            window.location.href = "{{ route('interns.management') }}";
        } else {
            alert('Gagal menambahkan data: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.message) {
            alert('Error: ' + error.message);
        } else if (error.status && error.message) {
            alert(error.message);
        } else {
            alert('Terjadi kesalahan saat menambahkan data');
        }
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