<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PesertaMagang;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            
            // Ambil data statistik (ini mengembalikan response JSON)
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
            
            // Kirim data ke view
            return view('dashboard', compact('stats'));
            
        } catch (\Exception $e) {
            // Log error
            Log::error('Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Gunakan data default jika terjadi error
            $stats = $this->getDefaultStats();
            
            // Kirim data default ke view
            return view('dashboard', compact('stats'));
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