<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'data_mahasiswa';
    protected $primaryKey = 'id_mahasiswa';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id_mahasiswa',
        'id_magang',
        'nim',
        'fakultas',
        'jurusan',
        'semester',
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