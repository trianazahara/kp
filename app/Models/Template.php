<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    
    // Table name if it's not the default 'templates'
    protected $table = 'dokumen_template'; // Update this if your table has a different name
    
    // Primary key
    protected $primaryKey = 'id_dokumen';
    
    // Key type
    protected $keyType = 'string';
    
    // Disable auto-incrementing for UUID primary key
    public $incrementing = false;
    
    // Fillable fields based on the table structure
    protected $fillable = [
        'id_dokumen',
        'id_users',
        'file_path',
        'active',
        'created_by',
        'created_at',
        'updated_at'
    ];
    
    // Default values
    protected $attributes = [
        'active' => 1
    ];
    
    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_users', 'id_users');
    }
    
    // Relationship with creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_users');
    }
}