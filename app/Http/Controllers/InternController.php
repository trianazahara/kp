<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PesertaMagang;
use App\Models\DataMahasiswa;
use App\Models\DataSiswa;
use App\Models\Bidang;
use App\Models\User;
use App\Models\Notifikasi;
use App\Models\Penilaian;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\NotificationController;

class InternController extends Controller
{
    /**
     * Menentukan status peserta berdasarkan tanggal
     */
    private function determineStatus($tanggal_masuk, $tanggal_keluar)
    {
        $current = Carbon::now();
        $masuk = Carbon::parse($tanggal_masuk);
        $keluar = Carbon::parse($tanggal_keluar);
        $sevenDaysBefore = (clone $keluar)->subDays(7);

        if ($current < $masuk) {
            return 'not_yet';
        } elseif ($current > $keluar) {
            return 'selesai';
        } elseif ($current->between($sevenDaysBefore, $keluar)) {
            return 'almost';
        } else {
            return 'aktif';
        }
    }

    /**
     * Buat notifikasi saat ada perubahan data peserta
     */
    private function createInternNotification($userId, $internName, $action = 'menambah')
    {
        try {
            // Ambil nama user
            $userData = User::find($userId);
            $nama = $userData ? $userData->nama : 'Unknown User';

            // Notifikasi untuk semua user
            $allUsers = User::select('id_users')->get();

            // Kirim notifikasi ke setiap user
            foreach ($allUsers as $user) {
                Notifikasi::create([
                    'id_notifikasi' => Str::uuid(),
                    'user_id' => $user->id_users,
                    'judul' => 'Aktivitas Peserta Magang',
                    'pesan' => "{$nama} telah {$action} data peserta magang: {$internName}",
                    'dibaca' => 0,
                    'created_at' => Carbon::now()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update status semua peserta magang
     */
    private function updateInternStatuses()
    {
        DB::statement("
            UPDATE peserta_magang
            SET status = CASE
                WHEN status = 'missing' THEN 'missing'
                WHEN CURRENT_DATE < tanggal_masuk THEN 'not_yet'
                WHEN CURRENT_DATE > tanggal_keluar THEN 'selesai'
                WHEN CURRENT_DATE BETWEEN DATE_SUB(tanggal_keluar, INTERVAL 7 DAY) AND tanggal_keluar THEN 'almost'
                ELSE 'aktif'
            END
            WHERE status != 'selesai'
        ");
    }

    /**
     * Set status peserta menjadi missing
     */
    public function setMissingStatus(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $updateResult = PesertaMagang::where('id_magang', $id)
                ->update([
                    'status' => 'missing',
                    'updated_by' => auth()->id(),
                    'updated_at' => Carbon::now()
                ]);

            if ($updateResult === 0) {
                throw new \Exception('Gagal mengupdate status peserta magang');
            }

            // Buat notifikasi
            $peserta = PesertaMagang::where('id_magang', $id)->first();
            $this->createInternNotification(
                auth()->id(),
                $peserta->nama,
                'menandai sebagai missing'
            );

            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Status peserta magang berhasil diubah menjadi missing'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting missing status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage() ?: 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Ambil daftar mentor yang aktif
     */
    public function getMentors(Request $request)
    {
        try {
            $user = auth()->user();

            if ($user->role === 'superadmin') {
                $mentors = User::where('role', 'admin')
                    ->where('is_active', 1)
                    ->select('id_users', 'nama')
                    ->get();
            } else {
                $mentors = User::where('id_users', $user->id)
                    ->where('is_active', 1)
                    ->select('id_users', 'nama')
                    ->get();
            }

            return response()->json($mentors);
        } catch (\Exception $e) {
            Log::error('Error getting mentors: ' . $e->getMessage());
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Ambil statistik detail peserta magang
     */
    public function getDetailedStats(Request $request)
    {
        try {
            $this->updateInternStatuses();
    
            $baseWhere = '1=1';
            $params = [];
            
            // Filter untuk admin
            if (auth()->user()->role === 'admin') {
                $baseWhere .= ' AND p.mentor_id = ?';
                $params[] = auth()->id();
            }
    
            // Hitung total peserta per status
            $basicStats = DB::select("
                SELECT
                    COUNT(CASE WHEN status IN ('aktif', 'almost') THEN 1 END) as active_count,
                    COUNT(CASE WHEN status = 'selesai' THEN 1 END) as completed_count,
                    COUNT(CASE WHEN status = 'almost' THEN 1 END) as completing_soon_count,
                    COUNT(CASE WHEN status = 'missing' THEN 1 END) as missing_count,
                    COUNT(*) as total_count
                FROM peserta_magang p
                WHERE {$baseWhere}
            ", $params);
    
            // Pastikan data tidak kosong
            $activeCount = $basicStats[0]->active_count ?? 0;
            $completedCount = $basicStats[0]->completed_count ?? 0;
            $totalCount = $basicStats[0]->total_count ?? 0;
    
            // Hitung per jenis peserta
            $educationStats = DB::select("
                SELECT
                    jenis_peserta,
                    COUNT(*) as count
                FROM peserta_magang p
                WHERE {$baseWhere}
                AND status IN ('aktif', 'almost')
                GROUP BY jenis_peserta
            ", $params);
    
            // Hitung per bidang
            $departmentStats = DB::select("
                SELECT
                    b.nama_bidang,
                    COUNT(*) as count
                FROM peserta_magang p
                JOIN bidang b ON p.id_bidang = b.id_bidang
                WHERE {$baseWhere}
                AND p.status IN ('aktif', 'almost')
                GROUP BY b.id_bidang, b.nama_bidang
            ", $params);
    
            // Ambil peserta yang hampir selesai
            $completingSoon = DB::select("
                SELECT
                    p.nama,
                    p.nama_institusi,
                    b.nama_bidang,
                    p.tanggal_keluar,
                    p.id_magang
                FROM peserta_magang p
                LEFT JOIN bidang b ON p.id_bidang = b.id_bidang
                WHERE {$baseWhere}
                AND p.status = 'almost'
                ORDER BY p.tanggal_keluar ASC
            ", $params);
    
            // Format respons
            $response = [
                'activeInterns' => [
                    'total' => $activeCount,
                    'students' => [
                        'siswa' => collect($educationStats)->firstWhere('jenis_peserta', 'siswa')->count ?? 0,
                        'mahasiswa' => collect($educationStats)->firstWhere('jenis_peserta', 'mahasiswa')->count ?? 0
                    ],
                    'byDepartment' => collect($departmentStats)->mapWithKeys(function ($item) {
                        return [strtolower($item->nama_bidang ?? 'unknown') => $item->count];
                    })->toArray()
                ],
                'completedInterns' => $completedCount,
                'totalInterns' => $totalCount,
                'completingSoon' => [
                    'count' => $basicStats[0]->completing_soon_count ?? 0,
                    'interns' => $completingSoon
                ]
            ];
    
            return response()->json($response);
    
        } catch (\Exception $e) {
            Log::error('Error getting detailed stats: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server saat mengambil statistik detail'
            ], 500);
        }
    }
    

    /**
     * Ambil semua data peserta magang dengan filter dan paginasi
     */
    public function getAll(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $status = $request->input('status');
            $bidang = $request->input('bidang');
            $search = $request->input('search');
            $excludeStatus = $request->input('excludeStatus');

            $offset = ($page - 1) * $limit;
            
            // Query dasar dengan pengecekan data lengkap
            $query = PesertaMagang::leftJoin('bidang as b', 'peserta_magang.id_bidang', '=', 'b.id_bidang')
                ->select([
                    'peserta_magang.*',
                    'b.nama_bidang',
                    DB::raw('CASE 
                        WHEN peserta_magang.email IS NULL 
                        OR peserta_magang.no_hp IS NULL
                        OR peserta_magang.nama_pembimbing IS NULL
                        OR peserta_magang.telp_pembimbing IS NULL
                        OR (
                            CASE 
                                WHEN peserta_magang.jenis_peserta = "mahasiswa" THEN 
                                    EXISTS(
                                        SELECT 1 FROM data_mahasiswa m 
                                        WHERE m.id_magang = peserta_magang.id_magang 
                                        AND (m.fakultas IS NULL OR m.semester IS NULL)
                                    )
                                ELSE 
                                    EXISTS(
                                        SELECT 1 FROM data_siswa s 
                                        WHERE s.id_magang = peserta_magang.id_magang 
                                        AND s.kelas IS NULL
                                    )
                            END
                        )
                        THEN true 
                        ELSE false 
                    END as has_incomplete_data')
                ]);

            // Filter untuk admin
            if (auth()->user()->role === 'admin') {
                $query->where('peserta_magang.mentor_id', auth()->id());
            }

            // Filter status yang diexclude
            if ($excludeStatus) {
                $statusesToExclude = explode(',', $excludeStatus);
                $query->whereNotIn('peserta_magang.status', $statusesToExclude);
            }

            // Filter status
            if ($status) {
                $query->where('peserta_magang.status', $status);
            }

            // Filter bidang
            if ($bidang) {
                $query->where('peserta_magang.id_bidang', $bidang);
            }

            // Filter pencarian
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('peserta_magang.nama', 'like', "%{$search}%")
                      ->orWhere('peserta_magang.nama_institusi', 'like', "%{$search}%");
                });
            }

            // Hitung total data
            $total = $query->count();

            // Tambah paginasi
            $rows = $query->orderBy('peserta_magang.created_at', 'desc')
                          ->limit($limit)
                          ->offset($offset)
                          ->get();

            return response()->json([
                'status' => 'success',
                'data' => $rows,
                'pagination' => [
                    'total' => $total,
                    'totalPages' => ceil($total / $limit),
                    'page' => (int)$page,
                    'limit' => (int)$limit
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting interns: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Cek ketersediaan slot magang
     */
    public function checkAvailability(Request $request)
    {
        try {
            $inputDate = $request->query('date');

            if (!$inputDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter tanggal diperlukan'
                ], 400);
            }

            $formattedDate = $inputDate;
            $SLOT_LIMIT = 50;

            // Hitung peserta aktif
            $activeInternsCount = PesertaMagang::where('status', 'aktif')
                ->where(function($query) use ($formattedDate) {
                    $query->where('tanggal_masuk', '<=', $formattedDate)
                          ->where('tanggal_keluar', '>=', $formattedDate);
                })
                ->count();

            // Hitung peserta yang akan datang
            $upcomingInternsCount = PesertaMagang::where('status', 'not_yet')
                ->where('tanggal_masuk', '<=', $formattedDate)
                ->count();

            // Ambil info peserta yang akan selesai
            $leavingInterns = PesertaMagang::select([
                    'peserta_magang.id_magang',
                    'peserta_magang.nama',
                    DB::raw('DATE_FORMAT(peserta_magang.tanggal_keluar, "%Y-%m-%d") as tanggal_keluar'),
                    'b.nama_bidang'
                ])
                ->leftJoin('bidang as b', 'peserta_magang.id_bidang', '=', 'b.id_bidang')
                ->where('peserta_magang.status', 'almost')
                ->where('peserta_magang.tanggal_keluar', '>=', $formattedDate)
                ->where('peserta_magang.tanggal_keluar', '<=', Carbon::parse($formattedDate)->addDays(7))
                ->orderBy('peserta_magang.tanggal_keluar', 'asc')
                ->get();

            // Hitung total slot terisi
            $totalActive = $activeInternsCount;
            $totalUpcoming = $upcomingInternsCount;
            $totalOccupied = $totalActive + $totalUpcoming;

            $message = '';
            if ($totalOccupied >= $SLOT_LIMIT) {
                $message = "Saat ini terisi: {$totalOccupied} dari {$SLOT_LIMIT} slot";
            } else {
                $availableSlots = $SLOT_LIMIT - $totalOccupied;
                $message = "Tersedia {$availableSlots} slot dari total {$SLOT_LIMIT} slot";
            }

            return response()->json([
                'success' => true,
                'available' => $totalOccupied < $SLOT_LIMIT,
                'totalOccupied' => $totalOccupied,
                'currentActive' => $totalActive,
                'upcomingInterns' => $totalUpcoming,
                'message' => $message,
                'date' => $formattedDate
            ]);

        } catch (\Exception $e) {
            Log::error('Error pada pengecekan ketersediaan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek ketersediaan',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Tambah peserta magang baru
     */
    public function add(Request $request)
    {
        try {
            // Validasi autentikasi
            if (!auth()->check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: User authentication required'
                ], 401);
            }
    
            DB::beginTransaction();
            $created_by = auth()->id();
    
            $validated = $request->validate([
                'nama' => 'required|string',
                'jenis_peserta' => 'required|in:mahasiswa,siswa',
                'nama_institusi' => 'required|string',
                'jenis_institusi' => 'required|string',
                'email' => 'nullable|email',
                'no_hp' => 'nullable|string',
                'bidang_id' => 'nullable|string',
                'tanggal_masuk' => 'required|date',
                'tanggal_keluar' => 'required|date|after:tanggal_masuk',
                'detail_peserta' => 'required|array',
                'nama_pembimbing' => 'nullable|string',
                'telp_pembimbing' => 'nullable|string'
            ]);
    
            // Validasi detail peserta
            if (!isset($validated['detail_peserta'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Detail peserta wajib diisi'
                ], 400);
            }
    
            // Validasi data mahasiswa/siswa
            if ($validated['jenis_peserta'] === 'mahasiswa') {
                if (!isset($validated['detail_peserta']['nim']) || !isset($validated['detail_peserta']['jurusan'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'NIM dan jurusan wajib diisi untuk mahasiswa'
                    ], 400);
                }
            } else if ($validated['jenis_peserta'] === 'siswa') {
                if (!isset($validated['detail_peserta']['nisn']) || !isset($validated['detail_peserta']['jurusan'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'NISN dan jurusan wajib diisi untuk siswa'
                    ], 400);
                }
            }
    
            $status = $this->determineStatus($validated['tanggal_masuk'], $validated['tanggal_keluar']);
    
            // Insert data peserta magang
            $id_magang = Str::uuid();
            $pesertaMagang = PesertaMagang::create([
                'id_magang' => $id_magang,
                'nama' => $validated['nama'],
                'jenis_peserta' => $validated['jenis_peserta'],
                'nama_institusi' => $validated['nama_institusi'],
                'jenis_institusi' => $validated['jenis_institusi'],
                'email' => $validated['email'] ?? null,
                'no_hp' => $validated['no_hp'] ?? null,
                'id_bidang' => $validated['bidang_id'] ?? null,
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'tanggal_keluar' => $validated['tanggal_keluar'],
                'status' => $status,
                'nama_pembimbing' => $validated['nama_pembimbing'] ?? null,
                'telp_pembimbing' => $validated['telp_pembimbing'] ?? null,
                'mentor_id' => $request->input('mentor_id') ?? null,
                'created_by' => $created_by,
                'created_at' => Carbon::now()
            ]);
    
            // Insert detail peserta (mahasiswa/siswa)
            if ($validated['jenis_peserta'] === 'mahasiswa') {
                $detail = $validated['detail_peserta'];
                DataMahasiswa::create([
                    'id_mahasiswa' => Str::uuid(),
                    'id_magang' => $id_magang,
                    'nim' => $detail['nim'],
                    'fakultas' => $detail['fakultas'] ?? null,
                    'jurusan' => $detail['jurusan'],
                    'semester' => $detail['semester'] ?? null,
                    'created_at' => Carbon::now()
                ]);
            } else if ($validated['jenis_peserta'] === 'siswa') {
                $detail = $validated['detail_peserta'];
                DataSiswa::create([
                    'id_siswa' => Str::uuid(),
                    'id_magang' => $id_magang,
                    'nisn' => $detail['nisn'],
                    'jurusan' => $detail['jurusan'],
                    'kelas' => $detail['kelas'] ?? null,
                    'created_at' => Carbon::now()
                ]);
            }
    
            // Buat notifikasi
            $this->createInternNotification(
                auth()->id(),
                $validated['nama'],
                'menambah'
            );
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Data peserta magang berhasil ditambahkan',
                'data' => [
                    'id_magang' => $id_magang,
                    'nama' => $validated['nama'],
                    'jenis_peserta' => $validated['jenis_peserta'],
                    'nama_institusi' => $validated['nama_institusi'],
                    'status' => $status,
                    'created_by' => $created_by,
                    'created_at' => Carbon::now()->toISOString()
                ]
            ], 201);
    
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Error adding intern: ' . $e->getMessage());
    
            if ($e->getCode() == 23000) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data sudah ada dalam sistem'
                ], 400);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan server',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding intern: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Ambil detail peserta magang
     */
    public function getDetail($id)
    {
        try {
            $peserta = PesertaMagang::select([
                    'peserta_magang.*',
                    'b.nama_bidang',
                    'peserta_magang.nama_pembimbing',
                    'peserta_magang.telp_pembimbing',
                    'peserta_magang.mentor_id'
                ])
                ->leftJoin('bidang as b', 'peserta_magang.id_bidang', '=', 'b.id_bidang')
                ->where('peserta_magang.id_magang', $id)
                ->first();

            if (!$peserta) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data peserta magang tidak ditemukan'
                ], 404);
            }

            // Tambahkan detail peserta
            if ($peserta->jenis_peserta === 'mahasiswa') {
                $detail = DataMahasiswa::where('id_magang', $id)->first();
                if ($detail) {
                    $peserta->detail_peserta = [
                        'nim' => $detail->nim,
                        'fakultas' => $detail->fakultas,
                        'jurusan' => $detail->jurusan,
                        'semester' => $detail->semester
                    ];
                }
            } else {
                $detail = DataSiswa::where('id_magang', $id)->first();
                if ($detail) {
                    $peserta->detail_peserta = [
                        'nisn' => $detail->nisn,
                        'jurusan' => $detail->jurusan,
                        'kelas' => $detail->kelas
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $peserta
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting intern detail: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * Update status semua peserta
     */
    public function updateStatuses()
    {
        try {
            DB::beginTransaction();
            $this->updateInternStatuses();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status peserta magang berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating statuses: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server'], 500);
        }
    }

    /**
     * Update data peserta magang
     */
    public function update(Request $request, $id)
{
    try {
        // Validasi autentikasi
        if (!auth()->check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: User authentication required'
            ], 401);
        }

        DB::beginTransaction();

        $updated_by = auth()->id();
        
        $validated = $request->validate([
            'nama' => 'required|string',
            'jenis_peserta' => 'required|in:mahasiswa,siswa',
            'nama_institusi' => 'required|string',
            'jenis_institusi' => 'required|string',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string',
            'bidang_id' => 'nullable|string',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'required|date|after:tanggal_masuk',
            'detail_peserta' => 'required|array',
            'nama_pembimbing' => 'nullable|string',
            'telp_pembimbing' => 'nullable|string'
        ]);

        // Cek keberadaan peserta
        $existingIntern = PesertaMagang::with([
            'dataMahasiswa', 
            'dataSiswa'
        ])->where('id_magang', $id)->first();

        if (!$existingIntern) {
            throw new \Exception('Data peserta magang tidak ditemukan');
        }

        // Validasi bidang
        if (!empty($validated['bidang_id'])) {
            $bidangExists = Bidang::where('id_bidang', $validated['bidang_id'])->exists();
            if (!$bidangExists) {
                throw new \Exception('Bidang yang dipilih tidak valid');
            }
        }

        // Hitung status baru
        $newStatus = $this->determineStatus($validated['tanggal_masuk'], $validated['tanggal_keluar']);

        // Update data utama
        $existingIntern->update([
            'nama' => $validated['nama'],
            'jenis_peserta' => $validated['jenis_peserta'] ?? $existingIntern->jenis_peserta,
            'nama_institusi' => $validated['nama_institusi'],
            'jenis_institusi' => $validated['jenis_institusi'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'id_bidang' => $validated['bidang_id'] ?? null,
            'tanggal_masuk' => $validated['tanggal_masuk'],
            'tanggal_keluar' => $validated['tanggal_keluar'],
            'status' => $newStatus,
            'nama_pembimbing' => $validated['nama_pembimbing'],
            'telp_pembimbing' => $validated['telp_pembimbing'],
            'mentor_id' => $request->input('mentor_id') ?? null,
            'updated_by' => $updated_by,
            'updated_at' => Carbon::now()
        ]);

        $currentJenisPeserta = $validated['jenis_peserta'] ?? $existingIntern->jenis_peserta;

        // Update detail peserta
        DataMahasiswa::where('id_magang', $id)->delete();
        DataSiswa::where('id_magang', $id)->delete();

        if ($currentJenisPeserta === 'mahasiswa') {
            $detail = $validated['detail_peserta'] ?? [];
            
            if (!isset($detail['nim']) || !isset($detail['jurusan'])) {
                throw new \Exception('NIM dan jurusan wajib diisi untuk mahasiswa');
            }

            DataMahasiswa::create([
                'id_mahasiswa' => Str::uuid(),
                'id_magang' => $id,
                'nim' => $detail['nim'],
                'fakultas' => $detail['fakultas'] ?? null,
                'jurusan' => $detail['jurusan'],
                'semester' => $detail['semester'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

        } else if ($currentJenisPeserta === 'siswa') {
            $detail = $validated['detail_peserta'] ?? [];
            
            if (!isset($detail['nisn']) || !isset($detail['jurusan'])) {
                throw new \Exception('NISN dan jurusan wajib diisi untuk siswa');
            }

            DataSiswa::create([
                'id_siswa' => Str::uuid(),
                'id_magang' => $id,
                'nisn' => $detail['nisn'],
                'jurusan' => $detail['jurusan'],
                'kelas' => $detail['kelas'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        // Buat notifikasi
        $this->createInternNotification(
            auth()->id(),
            $validated['nama'],
            'mengupdate'
        );

        // Ambil data terbaru
        $updatedData = PesertaMagang::with(['dataMahasiswa', 'dataSiswa'])
            ->where('id_magang', $id)
            ->first();

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Data peserta magang berhasil diperbarui',
            'data' => $updatedData
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating intern: ' . $e->getMessage());

        if (strpos($e->getMessage(), 'wajib') !== false) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        } else if ($e instanceof \Illuminate\Database\QueryException && $e->getCode() == 23000) {
            return response()->json([
                'status' => 'error',
                'message' => 'NIM/NISN sudah terdaftar'
            ], 400);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

    /**
 * Hapus data peserta magang
 */
public function delete($id)
{
    try {
        DB::beginTransaction();
        
        // Ambil nama peserta untuk notifikasi
        $internData = PesertaMagang::where('id_magang', $id)->first();

        if (!$internData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data peserta magang tidak ditemukan'
            ], 404);
        }

        $internName = $internData->nama;

        // Hapus data terkait sesuai jenis peserta
        if ($internData->jenis_peserta === 'mahasiswa') {
            DataMahasiswa::where('id_magang', $id)->delete();
        } else if ($internData->jenis_peserta === 'siswa') {
            DataSiswa::where('id_magang', $id)->delete();
        }

        // Hapus data utama
        $deleted = PesertaMagang::where('id_magang', $id)->delete();

        if (!$deleted) {
            throw new \Exception('Gagal menghapus data peserta magang');
        }

        // Buat notifikasi penghapusan
        $this->createInternNotification(
            auth()->id(),
            $internName,
            'menghapus'
        );

        DB::commit();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data peserta magang berhasil dihapus'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting intern: ' . $e->getMessage());
        
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage() ?: 'Terjadi kesalahan server'
        ], 500);
    }
}
    
    /**
 * Ambil daftar peserta yang akan selesai dalam 7 hari
 */
public function getCompletingSoon()
{
    try {
        $this->updateInternStatuses();

        $interns = PesertaMagang::select('peserta_magang.*', 'b.nama_bidang')
            ->leftJoin('bidang as b', 'peserta_magang.id_bidang', '=', 'b.id_bidang')
            ->whereBetween('peserta_magang.tanggal_keluar', [
                Carbon::now()->format('Y-m-d'),
                Carbon::now()->addDays(7)->format('Y-m-d')
            ])
            ->orderBy('peserta_magang.tanggal_keluar', 'asc')
            ->get();

        if ($interns->isEmpty()) {
            return response()->json([]);
        }

        return response()->json($interns);
        
    } catch (\Exception $e) {
        Log::error('Error getting completing soon interns: ' . $e->getMessage());
        return response()->json(['message' => 'Terjadi kesalahan server'], 500);
    }
}

/**
 * Ambil riwayat peserta magang yang sudah selesai/missing
 */
public function getHistory(Request $request)
{
    try {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $status = $request->input('status', 'selesai,missing,almost');
        $bidang = $request->input('bidang');
        $search = $request->input('search');

        $offset = ($page - 1) * $limit;
        $statusArray = explode(',', $status);

        // Query dasar riwayat
        $query = PesertaMagang::select([
                'peserta_magang.id_magang', 
                'peserta_magang.nama', 
                'peserta_magang.nama_institusi',
                'b.nama_bidang',
                'peserta_magang.status',
                'peserta_magang.tanggal_masuk', 
                'peserta_magang.tanggal_keluar',
                DB::raw('EXISTS (
                    SELECT 1 FROM penilaian p 
                    WHERE p.id_magang = peserta_magang.id_magang
                ) as has_scores')
            ])
            ->leftJoin('bidang as b', 'peserta_magang.id_bidang', '=', 'b.id_bidang')
            ->whereIn('peserta_magang.status', $statusArray);

        // Filter untuk admin
        if (auth()->user()->role === 'admin') {
            $query->where('peserta_magang.mentor_id', auth()->id());
        }

        // Filter bidang
        if ($bidang) {
            $query->where('peserta_magang.id_bidang', $bidang);
        }

        // Filter pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('peserta_magang.nama', 'like', "%{$search}%")
                  ->orWhere('peserta_magang.nama_institusi', 'like', "%{$search}%");
            });
        }
        
        // Hitung total data
        $totalData = $query->count();
        
        // Tambahkan paginasi
        $rows = $query->orderBy('peserta_magang.tanggal_keluar', 'desc')
                    ->limit($limit)
                    ->offset($offset)
                    ->get();

        $totalPages = ceil($totalData / $limit);

        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'pagination' => [
                'currentPage' => (int)$page,
                'totalPages' => $totalPages,
                'totalData' => $totalData,
                'limit' => (int)$limit,
            ],
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error fetching history: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan server',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
