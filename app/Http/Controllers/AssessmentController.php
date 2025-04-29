<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\NotificationController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;



class AssessmentController extends Controller
{
    // Fungsi untuk membuat notifikasi ke seluruh user saat ada perubahan nilai
    private function createInternNotification($userId, $internName, $action)
    {
        try {
            $userData = DB::select('SELECT nama FROM users WHERE id_users = ?', [$userId]);
            $nama = $userData[0]->nama ?? 'Unknown User';
            
            // Ambil semua user untuk notifikasi
            $allUsers = DB::select('SELECT id_users FROM users');
            
            $query = "
                INSERT INTO notifikasi (
                    id_notifikasi,
                    user_id,
                    judul,
                    pesan,
                    dibaca,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ";
            
            // Kirim notifikasi ke setiap user
            foreach ($allUsers as $user) {
                $values = [
                    Str::uuid()->toString(),
                    $user->id_users,
                    'Aktivitas Penilaian',
                    "{$nama} telah {$action} nilai untuk peserta magang: {$internName}",
                    0
                ];
                
                DB::statement($query, $values);
            }
            
        } catch (\Exception $error) {
            \Log::error('Error creating notification: ' . $error->getMessage());
            throw $error;
        }
    }


    // Tambah nilai baru untuk peserta magang
    public function addScore(Request $request, $id)
    {
        try {
            \Log::info('=== ADD SCORE DEBUG ===');
            \Log::info('ID dari URL: ' . $id);
            \Log::info('Request data: ' . json_encode($request->all()));
            \Log::info('Content-Type: ' . $request->header('Content-Type'));
    
            // Cek autentikasi user
            if (!auth()->check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: User authentication required'
                ], 401);
            }
    
            DB::beginTransaction();
    
            $userId = auth()->id();
            
            // Validasi request - pastikan ini bisa menangani JSON
            $data = $request->json()->all();
            if (empty($data)) {
                $data = $request->all(); // fallback ke form biasa jika bukan JSON
            }
            
            \Log::info('Data setelah parsing: ' . json_encode($data));
            
            // Validasi data
            $validator = Validator::make($data, [
                'nilai_teamwork' => 'required|numeric',
                'nilai_komunikasi' => 'required|numeric',
                'nilai_pengambilan_keputusan' => 'required|numeric',
                'nilai_kualitas_kerja' => 'required|numeric',
                'nilai_teknologi' => 'required|numeric',
                'nilai_disiplin' => 'required|numeric',
                'nilai_tanggungjawab' => 'required|numeric',
                'nilai_kerjasama' => 'required|numeric',
                'nilai_kejujuran' => 'required|numeric',
                'nilai_kebersihan' => 'required|numeric',
                'jumlah_hadir' => 'required|numeric',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $id_magang = $id; // Ambil ID dari parameter URL, bukan dari body
    
            // Cek keberadaan peserta
            $pesertaExists = DB::select(
                'SELECT nama FROM peserta_magang WHERE id_magang = ?',
                [$id_magang]
            );
    
            if (empty($pesertaExists)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data peserta magang tidak ditemukan'
                ], 404);
            }
    
            // Cek duplikasi penilaian
            $existingScore = DB::select(
                'SELECT id_penilaian FROM penilaian WHERE id_magang = ?',
                [$id_magang]
            );
    
            if (!empty($existingScore)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Penilaian untuk peserta ini sudah ada'
                ], 400);
            }
    
            $id_penilaian = Str::uuid()->toString();
    
            // Insert data penilaian baru
            DB::insert("
                INSERT INTO penilaian (
                    id_penilaian,
                    id_magang,
                    id_users,
                    nilai_teamwork,
                    nilai_komunikasi,
                    nilai_pengambilan_keputusan,
                    nilai_kualitas_kerja,
                    nilai_teknologi,
                    nilai_disiplin,
                    nilai_tanggungjawab,
                    nilai_kerjasama,
                    nilai_kejujuran,
                    nilai_kebersihan,
                    jumlah_hadir,        
                    created_by,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ", [
                $id_penilaian,
                $id_magang,
                $userId,
                $data['nilai_teamwork'] ?? 0,
                $data['nilai_komunikasi'] ?? 0,
                $data['nilai_pengambilan_keputusan'] ?? 0,
                $data['nilai_kualitas_kerja'] ?? 0,
                $data['nilai_teknologi'] ?? 0,
                $data['nilai_disiplin'] ?? 0,
                $data['nilai_tanggungjawab'] ?? 0,
                $data['nilai_kerjasama'] ?? 0,
                $data['nilai_kejujuran'] ?? 0,
                $data['nilai_kebersihan'] ?? 0,
                $data['jumlah_hadir'] ?? 0,  
                $userId
            ]);
    
            // Update status peserta jadi selesai
            DB::update("
                UPDATE peserta_magang
                SET status = 'selesai',
                    updated_by = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id_magang = ?
            ", [$userId, $id_magang]);
    
            // Buat notifikasi penilaian baru
            $this->createInternNotification(
                $userId,
                $pesertaExists[0]->nama,
                'menambahkan'
            );
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Penilaian berhasil disimpan',
                'data' => [
                    'id_penilaian' => $id_penilaian,
                    'id_magang' => $id_magang,
                    'created_by' => $userId,
                    'created_at' => now()->toISOString()
                ]
            ], 201);
    
        } catch (\Exception $error) {
            DB::rollback();
            \Log::error('Error creating assessment: ' . $error->getMessage());
            \Log::error('Stack trace: ' . $error->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $error->getMessage(),
                'error' => config('app.debug') ? $error->getMessage() : null
            ], 500);
        }
    }

    // Ambil rekap nilai dengan filter dan paginasi
    public function getRekapNilai(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $bidang = $request->input('bidang');
            $search = $request->input('search');
    
            \Log::info('=== DEBUG INFO ===');
            \Log::info('Page: ' . $page);
            \Log::info('Limit: ' . $limit);
            \Log::info('Bidang: ' . $bidang);
            \Log::info('Search: ' . $search);
    
            $offset = ($page - 1) * $limit;
            $params = [];
            
            // Query dasar untuk rekap nilai
            $query = "
                SELECT 
                    pm.nama,
                    pm.nama_institusi,
                    b.nama_bidang,
                    pm.tanggal_masuk,
                    pm.tanggal_keluar,
                    p.*
                FROM penilaian p
                LEFT JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                LEFT JOIN bidang b ON pm.id_bidang = b.id_bidang
                WHERE 1=1
            ";
    
            // Filter untuk admin
            if (auth()->user()->role === 'admin') {
                $query .= " AND pm.mentor_id = ?";
                $params[] = auth()->id();
            }
              
            // Filter berdasarkan bidang
            if (!empty($bidang)) {
                $query .= " AND pm.id_bidang = ?";
                $params[] = $bidang;
            }
    
            // Filter pencarian
            if (!empty($search)) {
                $query .= " AND (pm.nama LIKE ? OR pm.nama_institusi LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
    
            // Debug query total
            $debugQuery = "
                SELECT COUNT(*) as total
                FROM penilaian p
                LEFT JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                LEFT JOIN bidang b ON pm.id_bidang = b.id_bidang
                WHERE 1=1
            ";
            
            if (auth()->user()->role === 'admin') {
                $debugQuery .= " AND pm.mentor_id = ?";
                $debugParams = [auth()->id()];
            } else {
                $debugParams = [];
            }
            
            $debugResult = DB::select($debugQuery, $debugParams);
            \Log::info('Debug Query Result: ' . json_encode($debugResult));
    
            // Tambah paginasi ke query
            $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
    
            \Log::info('=== QUERY INFO ===');
            \Log::info('Final Query: ' . $query);
            \Log::info('Parameters: ' . json_encode($params));
    
            $rows = DB::select($query, $params);
            \Log::info('=== RESULT INFO ===');
            \Log::info('Number of rows returned: ' . (is_array($rows) ? count($rows) : 0));
            \Log::info('First row: ' . (is_array($rows) && count($rows) > 0 ? json_encode($rows[0]) : 'No data'));
    
            if (empty($rows)) {
                \Log::info('No data found in query result');
                return response()->json([
                    'status' => "success",
                    'data' => [],
                    'pagination' => [
                        'currentPage' => (int)$page,
                        'totalPages' => 0,
                        'totalData' => 0,
                        'limit' => (int)$limit,
                    ],
                ], 200);
            }
    
            // Hitung total data untuk paginasi
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM penilaian p
                LEFT JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                LEFT JOIN bidang b ON pm.id_bidang = b.id_bidang
                WHERE 1=1
            ";
            
            $countParams = [];
            
            if (auth()->user()->role === 'admin') {
                $countQuery .= " AND pm.mentor_id = ?";
                $countParams[] = auth()->id();
            }
            
            if (!empty($bidang)) {
                $countQuery .= " AND pm.id_bidang = ?";
                $countParams[] = $bidang;
            }
            
            if (!empty($search)) {
                $countQuery .= " AND (pm.nama LIKE ? OR pm.nama_institusi LIKE ?)";
                $countParams[] = "%{$search}%";
                $countParams[] = "%{$search}%";
            }
            
            $countRows = DB::select($countQuery, $countParams);
            $totalData = $countRows[0]->total ?? 0;
            $totalPages = ceil($totalData / $limit);
    
            \Log::info('=== PAGINATION INFO ===');
            \Log::info('Total Data: ' . $totalData);
            \Log::info('Total Pages: ' . $totalPages);
    
            return response()->json([
                'status' => "success",
                'data' => $rows,
                'pagination' => [
                    'currentPage' => (int)$page,
                    'totalPages' => $totalPages,
                    'totalData' => $totalData,
                    'limit' => (int)$limit,
                ],
            ], 200);
    
        } catch (\Exception $error) {
            \Log::error('=== ERROR INFO ===');
            \Log::error('Error Message: ' . $error->getMessage());
            \Log::error('Error Stack: ' . $error->getTraceAsString());
            return response()->json([
                'status' => "error",
                'message' => 'Terjadi kesalahan server',
                'error' => $error->getMessage(),
            ], 500);
        }
    }


    // Update nilai peserta magang
    public function updateScore(Request $request, $id)
    {
        try {
            // Validasi user
            if (!auth()->check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: User authentication required'
                ], 401);
            }
            
            DB::beginTransaction();
            
            // Coba dulu dengan id_penilaian
            $internData = DB::select("
                SELECT p.id_penilaian, pm.nama 
                FROM penilaian p 
                JOIN peserta_magang pm ON p.id_magang = pm.id_magang 
                WHERE p.id_penilaian = ?
            ", [$id]);
            
            // Jika tidak ditemukan, coba dengan id_magang
            if (empty($internData)) {
                $internData = DB::select("
                    SELECT p.id_penilaian, pm.nama 
                    FROM penilaian p 
                    JOIN peserta_magang pm ON p.id_magang = pm.id_magang 
                    WHERE p.id_magang = ?
                ", [$id]);
                
                // Masih tidak ditemukan
                if (empty($internData)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data penilaian tidak ditemukan'
                    ], 404);
                }
                
                // Gunakan id_penilaian yang ditemukan untuk update
                $id = $internData[0]->id_penilaian;
            }
    
            // Validasi request
            $validated = $request->validate([
                'nilai_teamwork' => 'required|numeric',
                'nilai_komunikasi' => 'required|numeric',
                'nilai_pengambilan_keputusan' => 'required|numeric',
                'nilai_kualitas_kerja' => 'required|numeric',
                'nilai_teknologi' => 'required|numeric',
                'nilai_disiplin' => 'required|numeric',
                'nilai_tanggungjawab' => 'required|numeric',
                'nilai_kerjasama' => 'required|numeric',
                'nilai_kejujuran' => 'required|numeric',
                'nilai_kebersihan' => 'required|numeric',
                'jumlah_hadir' => 'required|numeric'
            ]);
    
            // Update nilai
            DB::update("
                UPDATE penilaian 
                SET 
                    updated_at = CURRENT_TIMESTAMP,
                    updated_by = ?,
                    nilai_teamwork = ?,
                    nilai_komunikasi = ?,
                    nilai_pengambilan_keputusan = ?,
                    nilai_kualitas_kerja = ?,
                    nilai_teknologi = ?,
                    nilai_disiplin = ?,
                    nilai_tanggungjawab = ?,
                    nilai_kerjasama = ?,
                    nilai_kejujuran = ?,
                    nilai_kebersihan = ?,
                    jumlah_hadir = ?
                WHERE id_penilaian = ?
            ", [
                auth()->id(),
                $request->nilai_teamwork,
                $request->nilai_komunikasi,
                $request->nilai_pengambilan_keputusan,
                $request->nilai_kualitas_kerja,
                $request->nilai_teknologi,
                $request->nilai_disiplin,
                $request->nilai_tanggungjawab,
                $request->nilai_kerjasama,
                $request->nilai_kejujuran,
                $request->nilai_kebersihan,
                $request->jumlah_hadir,
                $id
            ]);
    
            // Kirim notifikasi update nilai
            $this->createInternNotification(
                auth()->id(),
                $internData[0]->nama,
                'memperbarui'
            );
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Nilai berhasil diupdate'
            ], 200);
    
        } catch (\Exception $error) {
            DB::rollback();
            \Log::error('Error updating nilai: ' . $error->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengupdate nilai'
            ], 500);
        }
    }

    // Ambil detail nilai berdasarkan ID magang
    public function getByInternId($id_magang)
    {
        try {
            // Log untuk debugging
            \Log::info('getByInternId dipanggil dengan ID: ' . $id_magang);
            
            // Query sederhana dulu untuk tes
            $assessment = DB::select("
                SELECT p.*, pm.nama 
                FROM penilaian p
                JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                WHERE p.id_magang = ?
            ", [$id_magang]);
    
            \Log::info('Hasil query: ' . json_encode(['count' => count($assessment)]));
    
            if (empty($assessment)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Penilaian tidak ditemukan'
                ], 404);
            }
    
            \Log::info('Data assessment: ' . json_encode(['data' => $assessment[0]]));
            return response()->json($assessment[0]);
        } catch (\Exception $error) {
            \Log::error('Error getting assessment: ' . $error->getMessage());
            \Log::error('Error trace: ' . $error->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server: ' . $error->getMessage()
            ], 500);
        }
    }

    // Generate sertifikat magang
    public function generateCertificate($id_magang)
{
    try {
        // Cek apakah ZipArchive tersedia
        if (!class_exists('ZipArchive')) {
            \Log::error('ZipArchive class tidak tersedia. Silakan aktifkan PHP extension zip.');
            return response()->json([
                'message' => 'PHP Extension zip tidak tersedia. Silakan aktifkan extension zip di php.ini dan restart web server.'
            ], 500);
        }

        // Ambil data lengkap peserta
        $assessment = DB::select("
            SELECT 
                p.*, 
                pm.nama, 
                pm.jenis_peserta,
                pm.nama_institusi, 
                b.nama_bidang,
                CASE 
                    WHEN pm.jenis_peserta = 'mahasiswa' THEN m.nim
                    ELSE s.nisn
                END as nomor_induk,
                m.fakultas, 
                m.jurusan as jurusan_mahasiswa,
                s.jurusan as jurusan_siswa, 
                s.kelas,
                pm.tanggal_masuk, 
                pm.tanggal_keluar
            FROM penilaian p
            JOIN peserta_magang pm ON p.id_magang = pm.id_magang
            LEFT JOIN bidang b ON pm.id_bidang = b.id_bidang
            LEFT JOIN data_mahasiswa m ON pm.id_magang = m.id_magang
            LEFT JOIN data_siswa s ON pm.id_magang = s.id_magang
            WHERE p.id_magang = ?
        ", [$id_magang]);

        if (empty($assessment)) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        // Ambil template dari database
        $template = DB::table('dokumen_template')
            ->where('active', 1)
            ->first();

        if (!$template) {
            return response()->json([
                'message' => 'Template sertifikat tidak ditemukan'
            ], 404);
        }

        $peserta = $assessment[0];
        
        // Hitung rata-rata nilai
        $nilai = [
            $peserta->nilai_teamwork ?? 0,
            $peserta->nilai_komunikasi ?? 0,
            $peserta->nilai_pengambilan_keputusan ?? 0,
            $peserta->nilai_kualitas_kerja ?? 0,
            $peserta->nilai_teknologi ?? 0,
            $peserta->nilai_disiplin ?? 0,
            $peserta->nilai_tanggungjawab ?? 0,
            $peserta->nilai_kerjasama ?? 0,
            $peserta->nilai_kejujuran ?? 0,
            $peserta->nilai_kebersihan ?? 0
        ];
        
        $jumlah = array_sum($nilai);
        $rata_rata = $jumlah / count($nilai);
        
        // Cari template di berbagai lokasi yang mungkin
        $possiblePaths = [
            // Path relatif dari storage/app/public/templates (hanya nama file)
            storage_path('app/public/templates/' . basename($template->file_path)),
            
            // Path relatif dari storage/app/public
            storage_path('app/public/' . $template->file_path),
            
            // Path relatif dari public
            public_path($template->file_path),
            
            // Path absolut (jika disimpan sebagai path absolut)
            $template->file_path,
            
            // Opsi terakhir: cari semua file .docx di folder templates dan ambil yang pertama
            storage_path('app/public/templates')
        ];

        $templatePath = null;
        $lastDir = null; // Untuk mencari file .docx jika opsi terakhir
        
        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                // Jika path adalah direktori, simpan untuk pencarian file nanti
                $lastDir = $path;
                continue;
            }
            
            if (file_exists($path)) {
                $templatePath = $path;
                \Log::info('Template ditemukan di: ' . $path);
                break;
            }
        }
        
        // Jika masih tidak ditemukan, cari file .docx di direktori templates
        if (!$templatePath && $lastDir) {
            $files = glob($lastDir . '/*.docx');
            if (!empty($files)) {
                $templatePath = $files[0]; // Ambil file .docx pertama yang ditemukan
                \Log::info('Menggunakan template alternatif: ' . $templatePath);
            }
        }
        
        // Jika masih tidak ditemukan juga, kembalikan error yang jelas
        if (!$templatePath || !file_exists($templatePath)) {
            \Log::error('Template tidak ditemukan di semua lokasi yang dicoba');
            return response()->json([
                'message' => 'Template sertifikat tidak ditemukan di semua lokasi yang dicoba.',
                'paths_checked' => $possiblePaths,
                'template_id' => $template->id ?? 'unknown',
                'template_path' => $template->file_path ?? 'unknown'
            ], 404);
        }
        
        // Buat file temp dengan pemeriksaan direktori yang tepat
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $tempFile = $tempDir . '/temp_' . uniqid() . '.docx';
        
        try {
            // Coba membuat template processor
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
            
            // Mengatur bahasa Indonesia untuk format tanggal
            Carbon::setLocale('id');
            setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'Indonesia');
            
            // Membuat fungsi untuk mengubah nilai numerik menjadi teks dalam Bahasa Indonesia
            $nilaiKeText = function($nilai) {
                // Handle nilai khusus
                if ($nilai == 0) return "NOL";
                if ($nilai == 10) return "SEPULUH";
                if ($nilai == 98) return "SEMBILAN PULUH DELAPAN";
                if ($nilai == 100) return "SERATUS";
                
                // Untuk nilai standar 1-9
                $penilaian = [
                    1 => "SATU", 
                    2 => "DUA", 
                    3 => "TIGA", 
                    4 => "EMPAT", 
                    5 => "LIMA", 
                    6 => "ENAM", 
                    7 => "TUJUH", 
                    8 => "DELAPAN", 
                    9 => "SEMBILAN"
                ];
                
                // Check apakah nilai ada di daftar
                if (isset($penilaian[(int)$nilai])) {
                    return $penilaian[(int)$nilai];
                }
                
                // Jika tidak ada di daftar, tampilkan angka asli
                return (string) $nilai;
            };
            
            // Format tanggal dengan bahasa Indonesia (mengganti "March" menjadi "Maret", dll)
            $formatTanggalIndonesia = function($tanggal) {
                if (!$tanggal) return '-';
                
                $date = Carbon::parse($tanggal);
                
                $bulan = [
                    'January' => 'Januari',
                    'February' => 'Februari',
                    'March' => 'Maret',
                    'April' => 'April',
                    'May' => 'Mei',
                    'June' => 'Juni',
                    'July' => 'Juli',
                    'August' => 'Agustus',
                    'September' => 'September',
                    'October' => 'Oktober',
                    'November' => 'November',
                    'December' => 'Desember'
                ];
                
                $tanggalFormatted = $date->format('d F Y');
                
                // Ganti nama bulan Inggris ke Indonesia
                foreach ($bulan as $eng => $indo) {
                    $tanggalFormatted = str_replace($eng, $indo, $tanggalFormatted);
                }
                
                return $tanggalFormatted;
            };
            
            // Ambil nama institusi dari database jika diperlukan
            $nama_institusi = $peserta->nama_institusi;
            
            // Debug
            \Log::info('Data Peserta:', [
                'nama' => $peserta->nama,
                'nomor_induk' => $peserta->nomor_induk,
                'institusi' => $nama_institusi,
                'nilai_teamwork' => $peserta->nilai_teamwork,
                'nilai_komunikasi' => $peserta->nilai_komunikasi,
                'nilai_kerjasama' => $peserta->nilai_kerjasama,
                'nilai_kebersihan' => $peserta->nilai_kebersihan,
                'nilai_kejujuran' => $peserta->nilai_kejujuran
            ]);
            
            // Ganti placeholder dengan data aktual - sesuai format {placeholder}
            $templateProcessor->setValue('{nama}', $peserta->nama);
            $templateProcessor->setValue('{nomor_induk}', $peserta->nomor_induk ?? '-');
            $templateProcessor->setValue('{institusi}', $nama_institusi ?? '-');
            $templateProcessor->setValue('{tanggal_masuk}', $formatTanggalIndonesia($peserta->tanggal_masuk));
            $templateProcessor->setValue('{tanggal_keluar}', $formatTanggalIndonesia($peserta->tanggal_keluar));
            $templateProcessor->setValue('{jurusan}', $peserta->jenis_peserta == 'mahasiswa' ? 
                                    ($peserta->jurusan_mahasiswa ?? '-') : 
                                    ($peserta->jurusan_siswa ?? '-'));
            
            // Nomor dan tanggal naskah
            $templateProcessor->setValue('{nomor_naskah}', 'SKT/' . date('Y') . '/' . $id_magang);
            $templateProcessor->setValue('{tanggal_naskah}', $formatTanggalIndonesia(Carbon::now()));
            $templateProcessor->setValue('{ttd_pengirim}', '');
            
            // JANGAN BATASI NILAI - gunakan nilai asli dari database
            $nilai_teamwork = $peserta->nilai_teamwork ?? 0;
            $nilai_komunikasi = $peserta->nilai_komunikasi ?? 0;
            $nilai_pengambilan_keputusan = $peserta->nilai_pengambilan_keputusan ?? 0;
            $nilai_kualitas_kerja = $peserta->nilai_kualitas_kerja ?? 0;
            $nilai_teknologi = $peserta->nilai_teknologi ?? 0;
            $nilai_disiplin = $peserta->nilai_disiplin ?? 0;
            $nilai_tanggungjawab = $peserta->nilai_tanggungjawab ?? 0;
            $nilai_kerjasama = $peserta->nilai_kerjasama ?? 0;
            $nilai_kejujuran = $peserta->nilai_kejujuran ?? 0;
            $nilai_kebersihan = $peserta->nilai_kebersihan ?? 0;
            
            // Hitung jumlah dan rata-rata
            $nilai = [
                $nilai_teamwork,
                $nilai_komunikasi,
                $nilai_pengambilan_keputusan,
                $nilai_kualitas_kerja,
                $nilai_teknologi,
                $nilai_disiplin,
                $nilai_tanggungjawab,
                $nilai_kerjasama,
                $nilai_kejujuran,
                $nilai_kebersihan
            ];
            
            $jumlah = array_sum($nilai);
            $rata_rata = $jumlah / count($nilai);
            
            // Ganti nilai-nilai - gunakan format yang konsisten dengan 2 desimal
            $templateProcessor->setValue('{nilai_teamwork}', number_format($nilai_teamwork, 2));
            $templateProcessor->setValue('{nilai_komunikasi}', number_format($nilai_komunikasi, 2));
            $templateProcessor->setValue('{nilai_pengambilan_keputusan}', number_format($nilai_pengambilan_keputusan, 2));
            $templateProcessor->setValue('{nilai_kualitas_kerja}', number_format($nilai_kualitas_kerja, 2));
            $templateProcessor->setValue('{nilai_teknologi}', number_format($nilai_teknologi, 2));
            $templateProcessor->setValue('{nilai_disiplin}', number_format($nilai_disiplin, 2));
            $templateProcessor->setValue('{nilai_tanggungjawab}', number_format($nilai_tanggungjawab, 2));
            $templateProcessor->setValue('{nilai_kerjasama}', number_format($nilai_kerjasama, 2));
            $templateProcessor->setValue('{nilai_kejujuran}', number_format($nilai_kejujuran, 2));
            $templateProcessor->setValue('{nilai_kebersihan}', number_format($nilai_kebersihan, 2));
            
            // Nilai dalam bentuk teks
            $templateProcessor->setValue('{nilai_teamwork_teks}', $nilaiKeText($nilai_teamwork));
            $templateProcessor->setValue('{nilai_komunikasi_teks}', $nilaiKeText($nilai_komunikasi));
            $templateProcessor->setValue('{nilai_pengambilan_keputusan_teks}', $nilaiKeText($nilai_pengambilan_keputusan));
            $templateProcessor->setValue('{nilai_kualitas_kerja_teks}', $nilaiKeText($nilai_kualitas_kerja));
            $templateProcessor->setValue('{nilai_teknologi_teks}', $nilaiKeText($nilai_teknologi));
            $templateProcessor->setValue('{nilai_disiplin_teks}', $nilaiKeText($nilai_disiplin));
            $templateProcessor->setValue('{nilai_tanggungjawab_teks}', $nilaiKeText($nilai_tanggungjawab));
            $templateProcessor->setValue('{nilai_kerjasama_teks}', $nilaiKeText($nilai_kerjasama));
            $templateProcessor->setValue('{nilai_kejujuran_teks}', $nilaiKeText($nilai_kejujuran));
            $templateProcessor->setValue('{nilai_kebersihan_teks}', $nilaiKeText($nilai_kebersihan));
            
            // Jumlah dan rata-rata
            $templateProcessor->setValue('{jumlah}', number_format($jumlah, 2));
            $templateProcessor->setValue('{jumlah_teks}', number_format($jumlah, 2));
            $templateProcessor->setValue('{rata_rata}', number_format($rata_rata, 2));
            $templateProcessor->setValue('{rata_rata_teks}', number_format($rata_rata, 2));
            
            // Nilai akreditasi berdasarkan rata-rata
            $akreditasi = "";
            if ($rata_rata >= 85) {
                $akreditasi = "Sangat Memuaskan";
            } elseif ($rata_rata >= 75) {
                $akreditasi = "Memuaskan";
            } elseif ($rata_rata >= 60) {
                $akreditasi = "Cukup";
            } else {
                $akreditasi = "Kurang";
            }
            $templateProcessor->setValue('{akreditasi}', $akreditasi);
            
            // Simpan dokumen yang sudah diproses
            $templateProcessor->saveAs($tempFile);
            
            // Periksa ketersediaan LibreOffice untuk konversi PDF
            $libreOfficeAvailable = function_exists('exec') && !in_array('exec', explode(',', ini_get('disable_functions')));
            
            if ($libreOfficeAvailable) {
                try {
                    // Tentukan path output PDF
                    $pdfFile = $tempDir . '/sertifikat-' . Str::slug($peserta->nama) . '.pdf';
                    
                    // Konversi DOCX ke PDF menggunakan LibreOffice
                    $command = 'libreoffice --headless --convert-to pdf --outdir ' . escapeshellarg($tempDir) . ' ' . escapeshellarg($tempFile);
                    $output = [];
                    $returnVar = 0;
                    
                    exec($command, $output, $returnVar);
                    
                    // Periksa apakah konversi berhasil
                    $conversionPdfFile = str_replace('.docx', '.pdf', $tempFile);
                    
                    if (file_exists($conversionPdfFile)) {
                        // Rename file jika perlu
                        if ($conversionPdfFile != $pdfFile) {
                            rename($conversionPdfFile, $pdfFile);
                        }
                        
                        // Kirim file PDF
                        $response = response(file_get_contents($pdfFile), 200, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'attachment; filename="sertifikat-' . Str::slug($peserta->nama) . '.pdf"'
                        ]);
                        
                        // Bersihkan file temporary
                        @unlink($tempFile);
                        @unlink($pdfFile);
                        
                        return $response;
                    } else {
                        // Catat upaya konversi yang gagal
                        \Log::error('Konversi PDF gagal. Command: ' . $command);
                        \Log::error('Output: ' . implode(", ", $output));
                        \Log::error('Return code: ' . $returnVar);
                        
                        // Fallback ke DOCX jika konversi gagal
                        throw new \Exception('Konversi ke PDF gagal.');
                    }
                } catch (\Exception $pdfException) {
                    \Log::error('PDF conversion exception: ' . $pdfException->getMessage());
                    // Lanjutkan ke fallback DOCX
                }
            }
            
            // Fallback: Kembalikan file DOCX jika PDF gagal atau LibreOffice tidak tersedia
            $filename = 'sertifikat-' . Str::slug($peserta->nama) . '.docx';
            $response = response(file_get_contents($tempFile), 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
            
            // Bersihkan
            @unlink($tempFile);
            
            return $response;
            
        } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
            \Log::error('PHPWord Exception: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error saat memproses template: ' . $e->getMessage()
            ], 500);
        }
        
    } catch (\Exception $error) {
        \Log::error('Error generating certificate: ' . $error->getMessage());
        \Log::error('Error trace: ' . $error->getTraceAsString());
        return response()->json([
            'message' => 'Terjadi kesalahan server: ' . $error->getMessage(),
            'trace' => config('app.debug') ? $error->getTraceAsString() : null
        ], 500);
    }
}

    public function getHistoryScores(Request $request)
{
    try {
        \Log::info('API getHistoryScores dipanggil', [
            'request' => $request->all()
        ]);
        
        // Ambil parameter
        $search = $request->input('search', '');
        $bidang = $request->input('bidang', '');
        
        \Log::info('Parameter:', [
            'search' => $search,
            'bidang' => $bidang
        ]);
        
        // Query database
        $query = DB::table('penilaian as p')
            ->join('peserta_magang as pm', 'p.id_magang', '=', 'pm.id_magang')
            ->leftJoin('bidang as b', 'pm.id_bidang', '=', 'b.id_bidang')
            ->select(
                'pm.id_magang',
                'pm.nama',
                'pm.nama_institusi',
                'b.nama_bidang',
                'pm.tanggal_masuk',
                'pm.tanggal_keluar',
                'p.nilai_teamwork',
                'p.nilai_komunikasi',
                'p.nilai_pengambilan_keputusan',
                'p.nilai_kualitas_kerja',
                'p.nilai_teknologi',
                'p.nilai_disiplin',
                'p.nilai_tanggungjawab',
                'p.nilai_kerjasama',
                'p.nilai_kejujuran',
                'p.nilai_kebersihan'
            );
        
        // Filter pencarian
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('pm.nama', 'like', '%' . $search . '%')
                  ->orWhere('pm.nama_institusi', 'like', '%' . $search . '%');
            });
        }
        
        // Filter bidang
        if (!empty($bidang)) {
            $query->where('pm.id_bidang', $bidang);
        }
        
        // Dapatkan data
        $data = $query->orderBy('p.created_at', 'desc')->get();
        
        \Log::info('Query berhasil, jumlah data:', ['count' => count($data)]);
        
        // Return data dalam format JSON dengan header yang benar
        return response()->json([
            'status' => 'success',
            'data' => $data
        ])->header('Content-Type', 'application/json');
        
    } catch (\Exception $e) {
        \Log::error('Error di getHistoryScores:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500)->header('Content-Type', 'application/json');
    }  
    
}

public function editPage($id)
{
    try {
        // Cek apakah penilaian ada berdasarkan id_magang
        $assessment = DB::select("
            SELECT p.*, pm.nama, pm.jenis_peserta, pm.tanggal_masuk, pm.tanggal_keluar
            FROM penilaian p
            JOIN peserta_magang pm ON p.id_magang = pm.id_magang
            WHERE p.id_magang = ?
        ", [$id]);
        
        if (empty($assessment)) {
            // Coba cari berdasarkan id_penilaian jika tidak ditemukan berdasarkan id_magang
            $assessment = DB::select("
                SELECT p.*, pm.nama, pm.jenis_peserta, pm.tanggal_masuk, pm.tanggal_keluar
                FROM penilaian p
                JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                WHERE p.id_penilaian = ?
            ", [$id]);
            
            if (empty($assessment)) {
                return redirect()->route('history.scores')
                    ->with('error', 'Data penilaian tidak ditemukan');
            }
        }
        
        // Log untuk debugging
        \Log::info('Assessment data found:', ['data' => $assessment[0]]);
        
        // Jika menggunakan halaman terpisah (tidak direkomendasikan karena sudah pakai modal)
        // return view('assessment.edit', ['id_magang' => $id, 'assessment' => $assessment[0]]);
        
        // Redirect kembali ke halaman scores dengan flash data
        return redirect()->route('history.scores')
            ->with('edit_data', json_encode($assessment[0]));
        
    } catch (\Exception $error) {
        \Log::error('Error accessing edit page: ' . $error->getMessage());
        \Log::error('Stack trace: ' . $error->getTraceAsString());
        
        return redirect()->route('history.scores')
            ->with('error', 'Terjadi kesalahan saat mengakses halaman edit: ' . $error->getMessage());
    }
}

public function scoresIndex()
{
    try {
        \Log::info('Memasuki method scoresIndex');
        
        // Ambil data bidang untuk filter
        try {
            $bidang = DB::select('SELECT id_bidang, nama_bidang FROM bidang ORDER BY nama_bidang');
            \Log::info('Berhasil mengambil data bidang', ['count' => count($bidang)]);
        } catch (\Exception $e) {
            \Log::error('Error query bidang: ' . $e->getMessage());
            $bidang = [];
        }
        
        // Ambil data awal (10 data terbaru) - ini akan memberikan data awal tanpa perlu AJAX
        try {
            $initialData = DB::table('penilaian as p')
                ->leftJoin('peserta_magang as pm', 'p.id_magang', '=', 'pm.id_magang')
                ->leftJoin('bidang as b', 'pm.id_bidang', '=', 'b.id_bidang')
                ->select(
                    'pm.id_magang',
                    'pm.nama',
                    'pm.nama_institusi',
                    'b.nama_bidang',
                    'pm.tanggal_masuk',
                    'pm.tanggal_keluar',
                    'p.nilai_teamwork',
                    'p.nilai_komunikasi',
                    'p.nilai_pengambilan_keputusan',
                    'p.nilai_kualitas_kerja',
                    'p.nilai_teknologi',
                    'p.nilai_disiplin',
                    'p.nilai_tanggungjawab',
                    'p.nilai_kerjasama',
                    'p.nilai_kejujuran',
                    'p.nilai_kebersihan'
                )
                ->orderBy('p.created_at', 'desc')
                ->limit(10)
                ->get();
            
            \Log::info('Berhasil mengambil data awal', ['count' => count($initialData)]);
        } catch (\Exception $e) {
            \Log::error('Error query data awal: ' . $e->getMessage());
            $initialData = [];
        }
        
        // Return view dengan data awal
        return view('history.scores', [
            'bidang' => $bidang,
            'initialData' => $initialData
        ]);
        
    } catch (\Exception $error) {
        \Log::error('Error di scoresIndex: ' . $error->getMessage());
        \Log::error('Stack trace: ' . $error->getTraceAsString());
        
        return redirect()->route('dashboard')
            ->with('error', 'Terjadi kesalahan saat memuat halaman rekap nilai');
    }
}
}