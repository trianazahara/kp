<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesertaMagang extends Model
{
    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'peserta_magang';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_magang';

    /**
     * Tipe data primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Mematikan auto-increment karena primary key adalah string.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Kolom-kolom yang dapat diisi (fillable).
     *
     * @var array
     */
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
        'created_by',
        'updated_by',
        'mentor_id',
        'sertifikat_path'
    ];

    /**
     * Timestamps default (created_at & updated_at).
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Mendapatkan bidang terkait.
     */
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'id_bidang', 'id_bidang');
    }

    /**
     * Mendapatkan detail mahasiswa jika jenis peserta adalah mahasiswa.
     */
    public function dataMahasiswa()
    {
        return $this->hasOne(DataMahasiswa::class, 'id_magang', 'id_magang');
    }

    /**
     * Mendapatkan detail siswa jika jenis peserta adalah siswa.
     */
    public function dataSiswa()
    {
        return $this->hasOne(DataSiswa::class, 'id_magang', 'id_magang');
    }

    /**
     * Mendapatkan user yang menjadi mentor.
     */
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id', 'id_users');
    }

    /**
     * Mendapatkan user yang membuat data peserta.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    /**
     * Mendapatkan user yang terakhir memperbarui data peserta.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_users');
    }
}