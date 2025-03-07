<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id_notifikasi',
        'user_id',
        'judul',
        'pesan',
        'type',
        'reference_id',
        'dibaca',
        'is_read',
        'created_at',
        'updated_at'
    ];

    /**
     * Get user that owns this notification
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_users');
    }

    /**
     * Get notifications by user with pagination and filtering
     */
    public static function getByUser($userId, $options = [])
    {
        try {
            $page = $options['page'] ?? 1;
            $limit = $options['limit'] ?? 10;
            $status = $options['status'] ?? 'all';
            $offset = ($page - 1) * $limit;
            
            $query = self::select('notifikasi.*', 'u.nama as user_name')
                ->join('users as u', 'notifikasi.user_id', '=', 'u.id_users')
                ->where('notifikasi.user_id', $userId);
            
            if ($status !== 'all') {
                $isRead = ($status === 'read') ? 1 : 0;
                $query->where('notifikasi.is_read', $isRead);
            }
            
            $total = $query->count();
            
            $notifications = $query->orderBy('notifikasi.created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get();
            
            return [
                'data' => $notifications,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getByUser: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a notification
     */
    public static function createNotification($userId, $type, $content, $referenceId = null)
    {
        try {
            $notification = self::create([
                'id_notifikasi' => \Illuminate\Support\Str::uuid(),
                'user_id' => $userId,
                'type' => $type,
                'pesan' => $content,
                'reference_id' => $referenceId,
                'dibaca' => 0,
                'is_read' => 0,
                'created_at' => Carbon::now()
            ]);
            
            return $notification->id_notifikasi;
        } catch (\Exception $e) {
            \Log::error('Error in createNotification: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationId, $userId)
    {
        try {
            $result = self::where('id_notifikasi', $notificationId)
                ->where('user_id', $userId)
                ->update([
                    'is_read' => 1,
                    'dibaca' => 1,
                    'updated_at' => Carbon::now()
                ]);
                
            return $result > 0;
        } catch (\Exception $e) {
            \Log::error('Error in markAsRead: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check for new events and create notifications
     */
    public static function checkAndCreateInternshipNotifications()
    {
        try {
            // Get admin IDs
            $admins = User::where('role', 'admin')->get(['id_users']);
            $adminIds = $admins->pluck('id_users')->toArray();
            
            // 1. Check new interns (last 24 hours)
            $newInterns = DB::table('peserta_magang as pm')
                ->join('users as u', 'pm.created_by', '=', 'u.id_users')
                ->where('pm.status', 'aktif')
                ->where('pm.created_at', '>=', Carbon::now()->subDay())
                ->get(['pm.*', 'u.nama']);
            
            // 2. Check interns ending soon (next 7 days)
            $endingSoonInterns = DB::table('peserta_magang as pm')
                ->join('users as u', 'pm.created_by', '=', 'u.id_users')
                ->where('pm.status', 'aktif')
                ->whereBetween('pm.tanggal_keluar', [Carbon::now(), Carbon::now()->addDays(7)])
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('notifikasi')
                        ->whereColumn('reference_id', 'pm.id_magang')
                        ->where('type', 'ending_soon')
                        ->where('created_at', '>=', Carbon::now()->subDays(7));
                })
                ->get(['pm.*', 'u.nama']);
            
            // 3. Check completed interns (last 24 hours)
            $completedInterns = DB::table('peserta_magang as pm')
                ->join('users as u', 'pm.created_by', '=', 'u.id_users')
                ->where('pm.status', 'selesai')
                ->where('pm.updated_at', '>=', Carbon::now()->subDay())
                ->get(['pm.*', 'u.nama']);
            
            // 4. Check completed evaluations (last 24 hours)
            $completedEvaluations = DB::table('penilaian as e')
                ->join('peserta_magang as pm', 'e.id_magang', '=', 'pm.id_magang')
                ->join('users as u1', 'pm.created_by', '=', 'u1.id_users')
                ->join('users as u2', 'e.created_by', '=', 'u2.id_users')
                ->where('e.created_at', '>=', Carbon::now()->subDay())
                ->get([
                    'e.*',
                    'pm.id_magang as intern_id',
                    'u1.nama as intern_name',
                    'u2.nama as evaluator_name'
                ]);
            
            // Create notifications for each admin
            foreach ($adminIds as $adminId) {
                // New interns notifications
                foreach ($newInterns as $intern) {
                    $notifId = self::createNotification(
                        $adminId,
                        'new_intern',
                        "Peserta magang baru: {$intern->nama} telah bergabung",
                        $intern->id_magang
                    );
                }
                
                // Ending soon notifications
                foreach ($endingSoonInterns as $intern) {
                    $daysRemaining = Carbon::now()->diffInDays(Carbon::parse($intern->tanggal_keluar), false);
                    
                    $notifId = self::createNotification(
                        $adminId,
                        'ending_soon',
                        "Peserta magang {$intern->nama} akan menyelesaikan magang dalam {$daysRemaining} hari",
                        $intern->id_magang
                    );
                }
                
                // Completed interns notifications
                foreach ($completedInterns as $intern) {
                    $notifId = self::createNotification(
                        $adminId,
                        'completed_intern',
                        "Peserta magang {$intern->nama} telah menyelesaikan program magang",
                        $intern->id_magang
                    );
                }
                
                // Completed evaluations notifications
                foreach ($completedEvaluations as $evaluation) {
                    $notifId = self::createNotification(
                        $adminId,
                        'evaluation_completed',
                        "{$evaluation->evaluator_name} telah memberikan evaluasi untuk {$evaluation->intern_name}",
                        $evaluation->id_penilaian
                    );
                }
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error in checkAndCreateInternshipNotifications: ' . $e->getMessage());
            throw $e;
        }
    }
}