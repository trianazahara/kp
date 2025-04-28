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
    public function index()
    {
        return view('interns.rekap-nilai');
    }

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
    
            // Cek data penilaian
            $internData = DB::select("
                SELECT pm.nama 
                FROM penilaian p 
                JOIN peserta_magang pm ON p.id_magang = pm.id_magang 
                WHERE p.id_penilaian = ?
            ", [$id]);
    
            if (empty($internData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data penilaian tidak ditemukan'
                ], 404);
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
            // Query untuk ambil detail nilai dan data peserta
            $assessment = DB::select("
                SELECT p.*, pm.nama, pm.jenis_peserta,
                       i.nama_institusi, b.nama_bidang,
                       CASE 
                           WHEN pm.jenis_peserta = 'mahasiswa' THEN m.nim
                           ELSE s.nisn
                       END as nomor_induk,
                       m.fakultas, m.jurusan as jurusan_mahasiswa,
                       s.jurusan as jurusan_siswa, s.kelas
                FROM penilaian p
                JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                LEFT JOIN institusi i ON pm.id_institusi = i.id_institusi
                LEFT JOIN bidang b ON pm.id_bidang = b.id_bidang
                LEFT JOIN data_mahasiswa m ON pm.id_magang = m.id_magang
                LEFT JOIN data_siswa s ON pm.id_magang = s.id_magang
                WHERE p.id_magang = ?
            ", [$id_magang]);

            if (empty($assessment)) {
                return response()->json([
                    'message' => 'Penilaian tidak ditemukan'
                ], 404);
            }

            return response()->json($assessment[0]);
        } catch (\Exception $error) {
            \Log::error('Error getting assessment: ' . $error->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server'], 500);
        }
    }

    // Generate sertifikat magang
    public function generateCertificate($id_magang)
    {
        try {
            // Ambil data lengkap peserta
            $assessment = DB::select("
                SELECT p.*, pm.nama, pm.jenis_peserta,
                       i.nama_institusi, b.nama_bidang,
                       CASE 
                           WHEN pm.jenis_peserta = 'mahasiswa' THEN m.nim
                           ELSE s.nisn
                       END as nomor_induk,
                       m.fakultas, m.jurusan as jurusan_mahasiswa,
                       s.jurusan as jurusan_siswa, s.kelas,
                       pm.tanggal_masuk, pm.tanggal_keluar
                FROM penilaian p
                JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                LEFT JOIN institusi i ON pm.id_institusi = i.id_institusi
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

            // Ambil template sertifikat aktif
            $template = DB::select("
                SELECT file_path
                FROM dokumen_template
                WHERE jenis = 'sertifikat'
                AND active = true
                LIMIT 1
            ");

            if (empty($template)) {
                return response()->json([
                    'message' => 'Template sertifikat tidak ditemukan'
                ], 404);
            }

            // Untuk implementasi PDF di Laravel, bisa menggunakan Package seperti TCPDF, DOMPDF, Snappy
            // Berikut contoh menggunakan DOMPDF
            
            // Persiapkan data untuk view
            $data = [
                'peserta' => $assessment[0],
                'tanggal' => Carbon::now()->locale('id')->isoFormat('D MMMM Y')
            ];

            // Generate PDF
            $pdf = \PDF::loadView('sertifikat.template', $data);
            
            return $pdf->download('sertifikat-magang.pdf');
            
        } catch (\Exception $error) {
            \Log::error('Error generating certificate: ' . $error->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server'], 500);
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