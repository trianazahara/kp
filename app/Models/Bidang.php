<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSiswa extends Model
{
    use HasFactory;

    protected $table = 'data_siswa';
    protected $primaryKey = 'id_siswa';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id_siswa',
        'id_magang',
        'nisn',
        'jurusan',
        'kelas',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the intern that owns this data
     */
    public function pesertaMagang()
    {
        return $this->belongsTo(Intern::class, 'id_magang', 'id_magang');
    }
}