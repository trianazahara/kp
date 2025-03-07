<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_users';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'username', 'password', 'email', 'nama', 'role', 
        'profile_picture', 'is_active', 'nip', 'id_bidang'
    ];

    protected $hidden = [
        'password', 'otp', 'otp_expires_at',
    ];

    // Relasi dengan bidang
    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'id_bidang', 'id_bidang');
    }
}