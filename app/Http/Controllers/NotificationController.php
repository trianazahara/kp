<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Buat notifikasi baru
     * 
     * @param string $userId - ID user yang menerima notifikasi
     * @param string $judul - Judul notifikasi
     * @param string $pesan - Isi pesan notifikasi
     * @param string|null $createdBy - ID user yang membuat notifikasi (opsional)
     * @return bool
     */
    public function createNotification($userId, $judul, $pesan, $createdBy = null)
    {
        try {
            DB::beginTransaction();
            
            $query = "
                INSERT INTO notifikasi (
                    id_notifikasi,
                    user_id,
                    judul,
                    pesan,
                    dibaca,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
            ";
            
            $values = [
                Str::uuid()->toString(), // Generate UUID
                $userId,
                $judul,
                $pesan,
                0 // Status belum dibaca
            ];
            
            DB::insert($query, $values);
            
            DB::commit();
            return true;
        } catch (\Exception $error) {
            DB::rollBack();
            \Log::error('Error creating notification: ' . $error->getMessage());
            return false;
        }
    }
    
    /**
     * API untuk membuat notifikasi
     */
    public function createNotificationApi(Request $request)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'user_id' => 'required|string',
                'judul' => 'required|string',
                'pesan' => 'required|string'
            ]);
            
            // Buat notifikasi
            $success = $this->createNotification(
                $validated['user_id'],
                $validated['judul'],
                $validated['pesan'],
                auth()->id()
            );
            
            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Notifikasi berhasil dibuat'
                ]);
            } else {
                throw new \Exception('Gagal membuat notifikasi');
            }
        } catch (\Exception $error) {
            return response()->json([
                'status' => 'error',
                'message' => $error->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ambil daftar notifikasi dengan paginasi
     */
    public function getNotifications(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $offset = ($page - 1) * $limit;
            $userId = auth()->id();
            
            // Query notifications with newest first
            $query = "
                SELECT 
                    n.*
                FROM notifikasi n
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $notifications = DB::select($query, [
                $userId,
                (int)$limit,
                (int)$offset
            ]);
            
            // Count total notifications for pagination
            $countResult = DB::select(
                'SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ?',
                [$userId]
            );
            
            $total = $countResult[0]->total;
            
            return response()->json([
                'status' => 'success',
                'data' => $notifications,
                'pagination' => [
                    'currentPage' => (int)$page,
                    'totalPages' => ceil($total / $limit),
                    'totalItems' => $total,
                    'limit' => (int)$limit
                ]
            ]);
        } catch (\Exception $error) {
            \Log::error('Error getting notifications: ' . $error->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil notifikasi'
            ], 500);
        }
    }
    
    /**
     * Get unread notification count for the current user
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $userId = auth()->id();
            
            $result = DB::select(
                'SELECT COUNT(*) as count FROM notifikasi WHERE user_id = ? AND dibaca = 0',
                [$userId]
            );
            
            return response()->json([
                'status' => 'success',
                'count' => $result[0]->count
            ]);
        } catch (\Exception $error) {
            \Log::error('Error getting unread count: ' . $error->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil jumlah notifikasi belum dibaca'
            ], 500);
        }
    }
    
    /**
     * Tandai satu notifikasi sebagai sudah dibaca
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $userId = auth()->id();
            
            // Update status dibaca
            $affected = DB::update(
                'UPDATE notifikasi SET dibaca = 1 WHERE id_notifikasi = ? AND user_id = ?',
                [$id, $userId]
            );
            
            if ($affected === 0) {
                throw new \Exception('Notifikasi tidak ditemukan');
            }
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi telah ditandai sebagai dibaca'
            ]);
        } catch (\Exception $error) {
            DB::rollBack();
            \Log::error('Error marking notification as read: ' . $error->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => $error->getMessage() ?: 'Terjadi kesalahan saat menandai notifikasi'
            ], 500);
        }
    }
    
    /**
     * Tandai semua notifikasi sebagai sudah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $userId = auth()->id();
            
            // Update semua notifikasi user
            DB::update(
                'UPDATE notifikasi SET dibaca = 1 WHERE user_id = ?',
                [$userId]
            );
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Semua notifikasi telah ditandai sebagai dibaca'
            ]);
        } catch (\Exception $error) {
            DB::rollBack();
            \Log::error('Error marking all notifications as read: ' . $error->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menandai semua notifikasi'
            ], 500);
        }
    }

    /**
     * Display notifications for the current user in web view
     */
    public function index()
    {
        $userId = auth()->id();
        
        $notifications = DB::table('notifikasi')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('notifications.index', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Show a specific notification
     */
    public function show($id)
    {
        $notification = DB::table('notifikasi')
            ->where('id_notifikasi', $id)
            ->where('user_id', auth()->id())
            ->first();
            
        if (!$notification) {
            return redirect()->route('dashboard')
                ->with('error', 'Notifikasi tidak ditemukan');
        }
        
        // Mark as read if unread
        if ($notification->dibaca == 0) {
            DB::table('notifikasi')
                ->where('id_notifikasi', $id)
                ->update(['dibaca' => 1]);
        }
        
        return view('notifications.show', [
            'notification' => $notification
        ]);
    }

}