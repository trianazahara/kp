<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PesertaMagang;
use App\Models\Notifikasi;
use App\Models\Bidang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page
     */
    public function index()
    {
        try {
            // Buat instance InternController
            $internController = new InternController();
            
            // Ambil data statistik
            $response = $internController->getDetailedStats(new Request());
            
            // Konversi response JSON menjadi array PHP
            $statsData = json_decode($response->getContent(), true);
            
            // Log untuk debugging
            Log::info('Dashboard Stats Data:', ['data' => $statsData ?? ['empty' => true]]);
            
            // Periksa apakah data statistik valid
            if (!is_array($statsData) || !isset($statsData['activeInterns'])) {
                // Jika data tidak valid, gunakan data default
                Log::warning('Stats data invalid, using default values');
                $stats = $this->getDefaultStats();
            } else {
                // Data valid, gunakan data yang diterima
                $stats = $statsData;
            }
            
            // Ambil data peserta magang aktif terbaru (5 data)
            $activeInterns = $this->getActiveInterns();
            
            // Kirim data ke view
            return view('dashboard', [
                'stats' => $stats,
                'activeInterns' => $activeInterns
            ]);
            
        } catch (\Exception $e) {
            // Log error
            Log::error('Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Gunakan data default jika terjadi error
            $stats = $this->getDefaultStats();
            
            // Kirim data default ke view
            return view('dashboard', [
                'stats' => $stats,
                'activeInterns' => []
            ])->with('error', 'Terjadi kesalahan saat memuat data dashboard.');
        }
    }
    
    /**
     * Mendapatkan data peserta magang aktif untuk ditampilkan di dashboard
     */
    private function getActiveInterns()
    {
        try {
            // Ambil 5 peserta magang aktif terbaru
            $activeInterns = PesertaMagang::select('peserta_magang.*', 'b.nama_bidang')
                ->leftJoin('bidang as b', 'peserta_magang.id_bidang', '=', 'b.id_bidang')
                ->whereIn('peserta_magang.status', ['aktif', 'almost'])
                ->orderBy('peserta_magang.created_at', 'desc')
                ->limit(5)
                ->get();
                
            return $activeInterns;
        } catch (\Exception $e) {
            Log::error('Error getting active interns: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mendapatkan data peserta magang per bidang
     */
    private function getInternsByDepartment()
    {
        try {
            $departments = DB::table('peserta_magang as p')
                ->select('b.nama_bidang', DB::raw('COUNT(*) as count'))
                ->join('bidang as b', 'p.id_bidang', '=', 'b.id_bidang')
                ->whereIn('p.status', ['aktif', 'almost'])
                ->groupBy('b.id_bidang', 'b.nama_bidang')
                ->get();
            
            $result = [];
            foreach ($departments as $dept) {
                $result[strtolower($dept->nama_bidang)] = $dept->count;
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error getting interns by department: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mendapatkan data peserta magang yang akan selesai dalam 7 hari
     */
    private function getCompletingSoonInterns()
    {
        try {
            $completingSoon = PesertaMagang::select('peserta_magang.*', 'b.nama_bidang')
                ->leftJoin('bidang as b', 'peserta_magang.id_bidang', '=', 'b.id_bidang')
                ->where('peserta_magang.status', 'almost')
                ->orderBy('peserta_magang.tanggal_keluar', 'asc')
                ->get();
            
            return $completingSoon;
        } catch (\Exception $e) {
            Log::error('Error getting completing soon interns: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * API endpoint untuk refresh data dashboard secara asinkron
     */
    public function refreshData()
    {
        try {
            // Buat instance InternController
            $internController = new InternController();
            
            // Ambil data statistik
            $response = $internController->getDetailedStats(new Request());
            
            // Ambil data JSON dari response
            $stats = json_decode($response->getContent(), true);
            
            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Error refreshing dashboard data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to refresh dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mendapatkan data statistik default jika terjadi error
     */
    private function getDefaultStats()
    {
        return [
            'activeInterns' => [
                'total' => 0,
                'students' => [
                    'mahasiswa' => 0,
                    'siswa' => 0
                ],
                'byDepartment' => []
            ],
            'completedInterns' => 0,
            'totalInterns' => 0,
            'completingSoon' => [
                'count' => 0,
                'interns' => []
            ]
        ];
    }
}