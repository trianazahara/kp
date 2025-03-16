<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'bidang';

    /**
     * Primary key tabel.
     *
     * @var string
     */
    protected $primaryKey = 'id_bidang';

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
        'id_bidang',
        'nama_bidang',
    ];

    /**
     * Timestamps default (created_at & updated_at).
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Mendapatkan relasi dengan peserta magang yang terkait dengan bidang ini.
     */
    public function pesertaMagang()
    {
        return $this->hasMany(PesertaMagang::class, 'id_bidang', 'id_bidang');
    }
}