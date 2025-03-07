<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Intern extends Model
{
    use HasFactory;

    protected $table = 'peserta_magang';
    protected $primaryKey = 'id_magang';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id_magang',
        'nama',
        'jenis_peserta',
        'nama_institusi',
        'jenis_institusi',
        'email',
        'no_hp',
        'id_bidang',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'nama_pembimbing',
        'telp_pembimbing',
        'mentor_id',
        'created_by',
        'updated_by'
    ];

    protected $dates = [
        'tanggal_masuk',
        'tanggal_keluar',
        'created_at',
        'updated_at'
    ];

    /**
     * Get related mahasiswa data
     */
    public function dataMahasiswa()
    {
        return $this->hasOne(DataMahasiswa::class, 'id_magang', 'id_magang');
    }

    /**
     * Get related siswa data
     */
    public function dataSiswa()
    {
        return $this->hasOne(DataSiswa::class, 'id_magang', 'id_magang');
    }

    /**
     * Get related bidang
     */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'id_bidang', 'id_bidang');
    }

    /**
     * Get mentor user
     */
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id', 'id_users');
    }

    /**
     * Find all interns with pagination and filtering
     */
    public static function findAllWithFilters($options)
    {
        $page = $options['page'] ?? 1;
        $limit = $options['limit'] ?? 10;
        $status = $options['status'] ?? null;
        $bidang = $options['bidang'] ?? null;
        $search = $options['search'] ?? null;
        $endDate = $options['endDate'] ?? null;
        
        $offset = ($page - 1) * $limit;

        $query = DB::table('peserta_magang as p')
            ->leftJoin('bidang as b', 'p.id_bidang', '=', 'b.id_bidang')
            ->leftJoin('data_mahasiswa as m', 'p.id_magang', '=', 'm.id_magang')
            ->leftJoin('data_siswa as s', 'p.id_magang', '=', 's.id_magang')
            ->leftJoin('users as u', 'p.mentor_id', '=', 'u.id_users')
            ->select([
                'p.*',
                'b.nama_bidang',
                'u.nama as mentor_nama',
                'u.nip as mentor_nip',
                DB::raw('CASE 
                    WHEN p.jenis_peserta = "mahasiswa" THEN m.nim
                    ELSE s.nisn
                END as nomor_induk'),
                DB::raw('CASE 
                    WHEN p.jenis_peserta = "mahasiswa" THEN m.fakultas
                    ELSE s.kelas
                END as detail_pendidikan'),
                DB::raw('CASE 
                    WHEN p.jenis_peserta = "mahasiswa" THEN m.jurusan
                    ELSE s.jurusan
                END as jurusan'),
                DB::raw('CASE
                    WHEN p.nama IS NULL OR p.nama = "" OR 
                         p.nama_institusi IS NULL OR p.nama_institusi = "" OR
                         p.email IS NULL OR p.email = "" OR
                         p.no_hp IS NULL OR p.no_hp = ""
                    THEN true
                    ELSE false
                END as is_incomplete')
            ]);

        // Apply filters
        if ($status) {
            $query->where('p.status', $status);
        }

        if ($bidang) {
            $query->where('p.id_bidang', $bidang);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('p.nama', 'like', "%{$search}%")
                  ->orWhere('p.nama_institusi', 'like', "%{$search}%")
                  ->orWhere(DB::raw('CASE 
                        WHEN p.jenis_peserta = "mahasiswa" THEN m.nim
                        ELSE s.nisn
                    END'), 'like', "%{$search}%");
            });
        }

        if ($endDate) {
            $query->where('p.tanggal_keluar', '<=', $endDate);
        }

        // Get total count
        $total = $query->count();

        // Get paginated data
        $data = $query->orderBy('p.created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => (int)$page,
                'totalPages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Find intern by ID with all details
     */
    public static function findDetailById($id)
    {
        $intern = DB::table('peserta_magang as p')
            ->select([
                'p.*',
                'b.nama_bidang',
                'u.nama as mentor_nama',
                'u.nip as mentor_nip',
                'u.email as mentor_email',
                DB::raw('CASE 
                    WHEN p.jenis_peserta = "mahasiswa" THEN (
                        SELECT JSON_OBJECT(
                            "nim", m.nim,
                            "fakultas", m.fakultas,
                            "jurusan", m.jurusan,
                            "semester", m.semester
                        )
                    )
                    ELSE (
                        SELECT JSON_OBJECT(
                            "nisn", s.nisn,
                            "jurusan", s.jurusan,
                            "kelas", s.kelas
                        )
                    )
                END as detail_peserta')
            ])
            ->leftJoin('bidang as b', 'p.id_bidang', '=', 'b.id_bidang')
            ->leftJoin('data_mahasiswa as m', 'p.id_magang', '=', 'm.id_magang')
            ->leftJoin('data_siswa as s', 'p.id_magang', '=', 's.id_magang')
            ->leftJoin('users as u', 'p.mentor_id', '=', 'u.id_users')
            ->where('p.id_magang', $id)
            ->first();

        if ($intern && isset($intern->detail_peserta)) {
            $intern->detail_peserta = json_decode($intern->detail_peserta);
        }

        return $intern;
    }

    /**
     * Get statistics about interns
     */
    public static function getStats()
    {
        $activeCount = DB::table('peserta_magang')
            ->where('status', 'aktif')
            ->count();

        $completedCount = DB::table('peserta_magang')
            ->where('status', 'selesai')
            ->count();

        $totalCount = DB::table('peserta_magang')->count();

        $completingSoon = DB::table('peserta_magang')
            ->where('status', 'aktif')
            ->whereRaw('tanggal_keluar <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)')
            ->count();

        return [
            'activeInterns' => $activeCount,
            'completedInterns' => $completedCount,
            'totalInterns' => $totalCount,
            'completingSoon' => $completingSoon
        ];
    }
}