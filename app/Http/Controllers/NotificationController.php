namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    // Buat notifikasi baru
    public function createNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'judul' => 'required|string',
            'pesan' => 'required|string',
        ]);

        $notifikasi = Notifikasi::create([
            'id_notifikasi' => Str::uuid(),
            'user_id' => $request->user_id,
            'judul' => $request->judul,
            'pesan' => $request->pesan,
            'dibaca' => false,
        ]);

        return response()->json(['status' => 'success', 'data' => $notifikasi], 201);
    }

    // Ambil daftar notifikasi dengan paginasi
    public function getNotifications(Request $request)
    {
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $offset = ($page - 1) * $limit;
        $userId = $request->user()->id;

        $notifications = Notifikasi::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = Notifikasi::where('user_id', $userId)->count();

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
    }

    // Hitung jumlah notifikasi yang belum dibaca
    public function getUnreadCount(Request $request)
    {
        $userId = $request->user()->id;

        $count = Notifikasi::where('user_id', $userId)
            ->where('dibaca', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'count' => $count
        ]);
    }

    // Tandai satu notifikasi sebagai sudah dibaca
    public function markAsRead(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $userId = $request->user()->id;

            $notifikasi = Notifikasi::where('id_notifikasi', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$notifikasi) {
                return response()->json(['status' => 'error', 'message' => 'Notifikasi tidak ditemukan'], 404);
            }

            $notifikasi->update(['dibaca' => true]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Notifikasi telah ditandai sebagai dibaca']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menandai notifikasi'], 500);
        }
    }

    // Tandai semua notifikasi sebagai sudah dibaca
    public function markAllAsRead(Request $request)
    {
        DB::beginTransaction();
        try {
            $userId = $request->user()->id;

            Notifikasi::where('user_id', $userId)
                ->update(['dibaca' => true]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Semua notifikasi telah ditandai sebagai dibaca']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menandai semua notifikasi'], 500);
        }
    }
}