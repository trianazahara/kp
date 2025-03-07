<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $table = 'dokumen_template';
    protected $primaryKey = 'id_dokumen';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id_dokumen',
        'judul',
        'jenis',
        'konten',
        'active',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the user who created the document
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }

    /**
     * Find active template by type
     */
    public static function findActiveTemplate($jenis)
    {
        return self::where('jenis', $jenis)
            ->where('active', true)
            ->first();
    }

    /**
     * Create a new template and deactivate old ones
     */
    public static function createTemplate($data)
    {
        try {
            DB::beginTransaction();
            
            // Deactivate current active templates of this type
            self::where('jenis', $data['jenis'])
                ->where('active', true)
                ->update([
                    'active' => false,
                    'updated_at' => Carbon::now()
                ]);
            
            // Ensure data has ID if not provided
            if (!isset($data['id_dokumen'])) {
                $data['id_dokumen'] = Str::uuid();
            }
            
            // Set active flag
            $data['active'] = true;
            
            // Insert new template
            $template = self::create($data);
            
            DB::commit();
            return $template->id_dokumen;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating document template: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all templates by type
     */
    public static function getAllTemplatesByType($jenis)
    {
        return self::where('jenis', $jenis)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Activate a specific template
     */
    public static function activateTemplate($id)
    {
        try {
            DB::beginTransaction();
            
            $template = self::findOrFail($id);
            
            // Deactivate all templates of the same type
            self::where('jenis', $template->jenis)
                ->where('active', true)
                ->update([
                    'active' => false,
                    'updated_at' => Carbon::now()
                ]);
            
            // Activate selected template
            $template->active = true;
            $template->updated_at = Carbon::now();
            $template->save();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error activating template: ' . $e->getMessage());
            throw $e;
        }
    }
}