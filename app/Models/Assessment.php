<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Assessment extends Model
{
    use HasFactory;

    protected $table = 'penilaian';
    protected $primaryKey = 'id_penilaian';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id_penilaian',
        'id_magang',
        'id_users',
        'nilai_teamwork',
        'nilai_komunikasi',
        'nilai_pengambilan_keputusan',
        'nilai_kualitas_kerja',
        'nilai_teknologi',
        'nilai_disiplin',
        'nilai_tanggungjawab',
        'nilai_kerjasama',
        'nilai_inisiatif',
        'nilai_kejujuran',
        'nilai_kebersihan',
        'created_by',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the intern that owns the assessment
     */
    public function intern()
    {
        return $this->belongsTo(Intern::class, 'id_magang', 'id_magang');
    }

    /**
     * Get the user who created the assessment
     */
    public function assessor()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    /**
     * Create a new assessment and update intern status
     */
    public static function createAssessment($data)
    {
        try {
            DB::beginTransaction();
            
            // Insert assessment
            $assessment = self::create($data);
            
            // Update status peserta magang
            Intern::where('id_magang', $data['id_magang'])
                ->update([
                    'status' => 'selesai',
                    'updated_at' => Carbon::now()
                ]);
            
            DB::commit();
            return $assessment->id_penilaian;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating assessment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find assessment by intern ID
     */
    public static function findByInternId($id_magang)
    {
        return self::where('id_magang', $id_magang)->first();
    }
    
    /**
     * Calculate average score
     */
    public function getAverageScore()
    {
        $fields = [
            'nilai_teamwork',
            'nilai_komunikasi',
            'nilai_pengambilan_keputusan',
            'nilai_kualitas_kerja',
            'nilai_teknologi',
            'nilai_disiplin',
            'nilai_tanggungjawab',
            'nilai_kerjasama',
            'nilai_inisiatif',
            'nilai_kejujuran',
            'nilai_kebersihan'
        ];
        
        $sum = 0;
        $count = 0;
        
        foreach ($fields as $field) {
            if (isset($this->$field) && $this->$field !== null) {
                $sum += $this->$field;
                $count++;
            }
        }
        
        return $count > 0 ? $sum / $count : 0;
    }
}