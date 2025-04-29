<!-- resources/views/interns/add.blade.php (file lengkap) -->
@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 lg:p-8 w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-yellow-200 rounded-lg shadow-md p-4 mb-6">
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
                        <option value="" selected disabled>BIdang Penempatan</option>
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
<div id="confirmationModal" class="custom-modal-overlay">
    <div class="custom-modal">
        <div class="custom-modal-header">
            <h3 class="custom-modal-title">Konfirmasi</h3>
        </div>
        <div class="custom-modal-body">
            <div id="confirmation-warning-icon" class="mb-4 flex justify-center hidden">
                <div class="bg-red-100 rounded-full p-3">
                    <svg class="w-10 h-10 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <p id="confirmationMessage" class="text-center">Perhatian! Saat ini terisi: 50 dari 50 slot. Slot sudah penuh untuk tanggal ini. Apakah Anda tetap ingin menambahkan peserta magang?</p>
            <p id="confirmationExtraWarning" class="mt-3 text-center text-red-600 font-semibold hidden">
                Menambahkan peserta melebihi slot yang tersedia dapat menimbulkan masalah dalam pengelolaan magang!
            </p>
        </div>
        <div class="custom-modal-footer">
            <button class="custom-modal-btn custom-modal-btn-secondary" id="cancelBtn">Batal</button>
            <button class="custom-modal-btn custom-modal-btn-primary" id="confirmBtn">Ya, Lanjutkan</button>
        </div>
    </div>
</div>
<div id="successModal" class="custom-modal-overlay">
    <div class="custom-modal custom-modal-success">
        <div class="custom-modal-header">
            <div class="custom-modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <div class="custom-modal-body">
            <h3 class="custom-modal-title">Berhasil</h3>
            <p id="successMessage">Data peserta magang berhasil ditambahkan</p>
        </div>
        <div class="custom-modal-footer">
            <button class="custom-modal-btn custom-modal-btn-primary" id="successBtn">OK</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi modal
        function showConfirmationModal(message, onConfirm, onCancel, isWarning = false) {
    const modal = document.getElementById('confirmationModal');
    const messageEl = document.getElementById('confirmationMessage');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const warningIcon = document.getElementById('confirmation-warning-icon');
    const extraWarning = document.getElementById('confirmationExtraWarning');
    
    // Set message
    messageEl.textContent = message;
    
    // Tampilkan ikon warning dan pesan tambahan jika diperlukan
    if (isWarning) {
        warningIcon.classList.remove('hidden');
        extraWarning.classList.remove('hidden');
        confirmBtn.classList.add('bg-red-500', 'hover:bg-red-600');
        confirmBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
    } else {
        warningIcon.classList.add('hidden');
        extraWarning.classList.add('hidden');
        confirmBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
        confirmBtn.classList.add('bg-green-500', 'hover:bg-green-600');
    }
    
    // Show modal
    modal.classList.add('active');
    
    // Setup event handlers
    const handleConfirm = () => {
        modal.classList.remove('active');
        confirmBtn.removeEventListener('click', handleConfirm);
        cancelBtn.removeEventListener('click', handleCancel);
        if (onConfirm) onConfirm();
    };
    
    const handleCancel = () => {
        modal.classList.remove('active');
        confirmBtn.removeEventListener('click', handleConfirm);
        cancelBtn.removeEventListener('click', handleCancel);
        if (onCancel) onCancel();
    };
    
    confirmBtn.addEventListener('click', handleConfirm);
    cancelBtn.addEventListener('click', handleCancel);
}
        function showSuccessModal(message, onClose) {
            const modal = document.getElementById('successModal');
            const messageEl = document.getElementById('successMessage');
            const okBtn = document.getElementById('successBtn');
            
            // Set message
            messageEl.textContent = message;
            
            // Show modal
            modal.classList.add('active');
            
            // Setup event handler
            const handleClose = () => {
                modal.classList.remove('active');
                okBtn.removeEventListener('click', handleClose);
                if (onClose) onClose();
            };
            
            okBtn.addEventListener('click', handleClose);
        }

        // DOM elements
        const form = document.getElementById('add-intern-form');
        const jenisPesertaSelect = document.getElementById('jenis_peserta');
        const mahasiswaSection = document.getElementById('mahasiswa-section');
        const siswaSection = document.getElementById('siswa-section');
        const tanggalMasukInput = document.getElementById('tanggal_masuk');
        const tanggalKeluarInput = document.getElementById('tanggal_keluar');
        const SLOT_LIMIT = 50; // Sesuai dengan batas maksimum slot
        const jenisInstitusiSelect = document.getElementById('jenis_institusi');

        // Tambahkan event listener untuk jenis institusi
        jenisInstitusiSelect.addEventListener('change', function() {
            // Auto-select jenis peserta berdasarkan jenis institusi
            if (this.value === 'Universitas') {
                jenisPesertaSelect.value = 'mahasiswa';
                // Trigger event change untuk menampilkan form sesuai jenis peserta
                const event = new Event('change');
                jenisPesertaSelect.dispatchEvent(event);
            } else if (this.value === 'Sekolah') {
                jenisPesertaSelect.value = 'siswa';
                // Trigger event change untuk menampilkan form sesuai jenis peserta
                const event = new Event('change');
                jenisPesertaSelect.dispatchEvent(event);
            }
        });
        
        // Set min date for tanggal_masuk to today
        //const today = new Date().toISOString().split('T')[0];
        //tanggalMasukInput.min = today;
        
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
            
            // Cek ketersediaan slot saat tanggal masuk berubah
            checkAvailability(this.value);
        });
        
        // Fungsi untuk memeriksa ketersediaan slot
        function checkAvailability(date) {
            if (!date) return;
            
            // Tambahkan indikator loading
            const loadingEl = document.createElement('div');
            loadingEl.id = 'slot-loading';
            loadingEl.className = 'mt-2 text-gray-600 text-sm';
            loadingEl.innerHTML = 'Memeriksa ketersediaan slot...';
            
            // Hapus peringatan atau loading sebelumnya jika ada
            const existingWarning = document.getElementById('slot-warning');
            const existingLoading = document.getElementById('slot-loading');
            if (existingWarning) {
                existingWarning.remove();
            }
            if (existingLoading) {
                existingLoading.remove();
            }
            
            // Tambahkan indikator loading
            tanggalMasukInput.parentNode.appendChild(loadingEl);
            
            fetch(`/api/interns/check-availability?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    // Hapus indikator loading
                    document.getElementById('slot-loading').remove();
                    
                    if (!data.available) {
                        // Tampilkan peringatan jika slot penuh
                        const warningEl = document.createElement('div');
                        warningEl.id = 'slot-warning';
                        warningEl.className = 'mt-2 p-3 bg-red-50 text-red-700 border border-red-200 rounded-md';
                        warningEl.innerHTML = `
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                <strong>Perhatian!</strong> 
                            </div>
                            <p class="mt-1 ml-7">Saat ini terisi: ${data.totalOccupied} dari ${SLOT_LIMIT} slot. Slot sudah penuh untuk tanggal ini.</p>
                        `;
                        
                        // Tambahkan peringatan setelah input tanggal
                        tanggalMasukInput.parentNode.appendChild(warningEl);
                    } else {
                        // Tampilkan informasi ketersediaan slot
                        const infoEl = document.createElement('div');
                        infoEl.id = 'slot-warning'; // Gunakan ID yang sama untuk kemudahan penghapusan
                        infoEl.className = 'mt-2 p-3 bg-green-50 text-green-700 border border-green-200 rounded-md';
                        infoEl.innerHTML = `
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>Tersedia ${SLOT_LIMIT - data.totalOccupied} slot dari total ${SLOT_LIMIT} slot</span>
                            </div>
                        `;
                        
                        // Tambahkan info setelah input tanggal
                        tanggalMasukInput.parentNode.appendChild(infoEl);
                    }
                })
                .catch(error => {
                    // Hapus indikator loading
                    if (document.getElementById('slot-loading')) {
                        document.getElementById('slot-loading').remove();
                    }
                    
                    console.error('Error checking availability:', error);
                    
                    // Tampilkan pesan error
                    const errorEl = document.createElement('div');
                    errorEl.id = 'slot-warning';
                    errorEl.className = 'mt-2 p-3 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-md';
                    errorEl.innerHTML = `
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Gagal memeriksa ketersediaan slot. Silahkan coba lagi.</span>
                        </div>
                    `;
                    
                    // Tambahkan pesan error setelah input tanggal
                    tanggalMasukInput.parentNode.appendChild(errorEl);
                });
        }
        
        // Form submit handler
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Ambil nilai jenis peserta
            const jenisPeserta = jenisPesertaSelect.value;
            const tanggalMasukValue = document.getElementById('tanggal_masuk').value;
            
            // Lakukan validasi dasar
            if (jenisPeserta === '' || jenisPeserta === null) {
                showConfirmationModal('Silahkan pilih jenis peserta', () => {});
                return;
            }
            
            // Pastikan validasi form lainnya
            const namaValue = document.getElementById('nama').value;
            const jenisInstitusiValue = document.getElementById('jenis_institusi').value;
            const namaInstitusiValue = document.getElementById('nama_institusi').value;
            const tanggalKeluarValue = document.getElementById('tanggal_keluar').value;
            
            if (!namaValue || !jenisInstitusiValue || !namaInstitusiValue || !tanggalMasukValue || !tanggalKeluarValue) {
                showConfirmationModal('Data isian utama wajib diisi', () => {});
                return;
            }
            
            // Periksa ketersediaan slot sebelum mengirim form
            fetch(`/api/interns/check-availability?date=${tanggalMasukValue}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.available) {
                        // Tampilkan konfirmasi jika slot penuh
                        showConfirmationModal(
    `Perhatian! Saat ini terisi: ${data.totalOccupied} dari ${SLOT_LIMIT} slot. Slot sudah penuh untuk tanggal ini. Apakah Anda tetap ingin menambahkan peserta magang?`,
    () => {
        // Lanjutkan dengan pengiriman form jika user mengkonfirmasi
        submitFormData();
    },
    null,  // parameter onCancel
    true   // parameter isWarning = true
);
                    } else {
                        // Langsung kirim form jika slot tersedia
                        submitFormData();
                    }
                })
                .catch(error => {
                    console.error('Error checking availability:', error);
                    
                    // Jika gagal cek, tampilkan konfirmasi
                    showConfirmationModal('Gagal memeriksa ketersediaan slot. Apakah Anda tetap ingin melanjutkan?', () => {
                        submitFormData();
                    });
                });
        });
        
        // Fungsi untuk mengirim data form
        function submitFormData() {
            const jenisPeserta = jenisPesertaSelect.value;
            
            // Buat FormData dari form
            const formData = new FormData(form);
            
            // Buat objek detail_peserta sesuai jenis peserta
            const detailPeserta = {};
            
            if (jenisPeserta === 'mahasiswa') {
                const nim = document.getElementById('nim').value;
                const jurusan = document.getElementById('jurusan_mahasiswa').value;
                
                if (!nim || !jurusan) {
                    showConfirmationModal('NIM dan Jurusan wajib diisi untuk mahasiswa', () => {});
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
                    showConfirmationModal('NISN dan Jurusan wajib diisi untuk siswa', () => {});
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
                    showSuccessModal('Data peserta magang berhasil ditambahkan', () => {
                        window.location.href = "{{ route('interns.management') }}";
                    });
                } else {
                    showConfirmationModal('Gagal menambahkan data: ' + data.message, () => {});
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.message) {
                    showConfirmationModal('Error: ' + error.message, () => {});
                } else if (error.status && error.message) {
                    showConfirmationModal(error.message, () => {});
                } else {
                    showConfirmationModal('Terjadi kesalahan saat menambahkan data', () => {});
                }
            });
        }
        
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