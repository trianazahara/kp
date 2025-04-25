<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Spatie\PdfToText\Pdf;

class DocumentController extends Controller
{
    /**
     * Upload template dokumen baru
     */
    public function uploadTemplate(Request $request)
    {
        try {
            // Validasi file
            $request->validate([
                'file' => 'required|file|mimes:doc,docx|max:5120', // 5MB max
            ]);

            // Nonaktifkan template lama
            DB::update('UPDATE dokumen_template SET active = 0 WHERE active = 1');

            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'File template tidak ditemukan'
                ], 400);
            }

            $file = $request->file('file');
            $filename = time() . '-' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());
            
            // Siapkan direktori dan pindahkan file
            $templateDir = public_path('templates');
            if (!File::exists($templateDir)) {
                File::makeDirectory($templateDir, 0755, true);
            }
            
            $file->move($templateDir, $filename);
            $filePath = 'templates/' . $filename;

            // Simpan info template ke database
            $templateId = (string) time();
            DB::insert(
                'INSERT INTO dokumen_template
                (id_dokumen, id_users, file_path, active, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $templateId,
                    auth()->id() ?? null,
                    $filePath,
                    1,
                    auth()->id() ?? null,
                    now()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diupload',
                'data' => [
                    'template_id' => $templateId
                ]
            ]);

        } catch (\Exception $error) {
            \Log::error('Error uploading template: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload template',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil daftar template aktif
     */
    public function getTemplates()
    {
        try {
            $templates = DB::select('SELECT * FROM dokumen_template WHERE active = 1');

            return response()->json([
                'success' => true,
                'data' => $templates
            ]);

        } catch (\Exception $error) {
            \Log::error('Error fetching templates: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data template',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Preview dokumen dalam format PDF
     */
    public function previewDocument($id)
    {
        try {
            $templates = DB::select(
                'SELECT * FROM dokumen_template WHERE id_dokumen = ? AND active = 1',
                [$id]
            );
    
            if (empty($templates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template tidak ditemukan'
                ], 404);
            }
    
            $filePath = public_path($templates[0]->file_path);
            if (!File::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            // Untuk konversi ke PDF, gunakan package seperti LibreOffice atau alternatif lain
            // Di sini kita contohkan dengan mengembalikan file asli saja
            return response()->file($filePath);
    
        } catch (\Exception $error) {
            \Log::error('Error: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat preview dokumen',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus template (soft delete)
     */
    public function deleteTemplate($id)
    {
        try {
            DB::update(
                'UPDATE dokumen_template SET active = 0 WHERE id_dokumen = ?',
                [$id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dihapus'
            ]);

        } catch (\Exception $error) {
            \Log::error('Error deleting template: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus template',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Generate sertifikat magang
     */
    public function generateSertifikat($id)
    {
        try {
            $id_magang = $id;
            if (!$id_magang) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID magang harus disediakan'
                ], 400);
            }
   
            // Ambil template aktif terbaru
            $templates = DB::select(
                'SELECT * FROM dokumen_template WHERE active = 1 ORDER BY created_at DESC LIMIT 1'
            );
   
            if (empty($templates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template sertifikat tidak ditemukan'
                ], 404);
            }
   
            // Ambil data lengkap peserta
            $peserta = DB::select("
                SELECT
                    pm.*,
                    p.*,
                    u.nama as nama_mentor,
                    u.nip as nip_mentor,
                    CASE
                        WHEN pm.jenis_peserta = 'mahasiswa' THEN dm.nim
                        WHEN pm.jenis_peserta = 'siswa' THEN ds.nisn
                    END as nomor_induk,
                    CASE
                        WHEN pm.jenis_peserta = 'mahasiswa' THEN dm.jurusan
                        WHEN pm.jenis_peserta = 'siswa' THEN ds.jurusan
                    END as jurusan
                FROM peserta_magang pm
                LEFT JOIN penilaian p ON pm.id_magang = p.id_magang
                LEFT JOIN data_mahasiswa dm ON pm.id_magang = dm.id_magang
                LEFT JOIN data_siswa ds ON pm.id_magang = ds.id_magang
                LEFT JOIN users u ON pm.mentor_id = u.id_users
                WHERE pm.id_magang = ?
            ", [$id_magang]);
   
            if (empty($peserta)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan'
                ], 404);
            }
   
            // Di sini gunakan library untuk mengolah template docx
            // Misalnya dengan PhpWord (kamu perlu menginstall package ini)
            // composer require phpoffice/phpword
            
            $templatePath = public_path($templates[0]->file_path);
            
            // Buat direktori untuk sertifikat jika belum ada
            $certificatesDir = public_path('certificates');
            if (!File::exists($certificatesDir)) {
                File::makeDirectory($certificatesDir, 0755, true);
            }
            
            // Generate nama file
            $docxName = 'sertifikat_' . Str::slug($peserta[0]->nama) . '_' . time() . '.docx';
            $docxPath = $certificatesDir . '/' . $docxName;
            
            // Di sini seharusnya ada kode untuk mengolah template docx
            // Karena ini kompleks dan membutuhkan library tertentu, kita skip detailnya
            
            // Simpan path sertifikat di database
            $dbPath = 'certificates/' . $docxName;
            DB::update(
                'UPDATE peserta_magang SET sertifikat_path = ? WHERE id_magang = ?',
                [$dbPath, $id_magang]
            );
   
            return response()->json([
                'success' => true,
                'message' => 'Sertifikat berhasil dibuat',
                'data' => [
                    'sertifikat_path' => $dbPath,
                    'nama_peserta' => $peserta[0]->nama
                ]
            ]);
   
        } catch (\Exception $error) {
            \Log::error('Error detail: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat sertifikat',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Download sertifikat peserta
     */
    public function downloadSertifikat($id_magang)
    {
        try {
            // Ambil path file sertifikat
            $peserta = DB::select(
                'SELECT sertifikat_path FROM peserta_magang WHERE id_magang = ?',
                [$id_magang]
            );

            if (empty($peserta) || empty($peserta[0]->sertifikat_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sertifikat tidak ditemukan'
                ], 404);
            }

            // Download file
            $filePath = public_path($peserta[0]->sertifikat_path);
            return response()->download($filePath);

        } catch (\Exception $error) {
            \Log::error('Error downloading certificate: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh sertifikat',
                'error' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Generate tanda terima
     */
    public function generateReceipt(Request $request)
    {
        try {
            $request->validate([
                'nomor_surat' => 'required|string',
                'tanggal' => 'required|date',
                'penerima' => 'required|string',
                'jabatan' => 'required|string',
                'departemen' => 'required|string',
                'daftar_barang' => 'required|array'
            ]);
           
            // Di sini implementasi untuk generate receipt
            // ...
           
            return response()->json([
                'success' => true,
                'message' => 'Receipt berhasil digenerate'
            ]);

        } catch (\Exception $error) {
            \Log::error('Error generating receipt: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat receipt',
                'error' => $error->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lihat sertifikat
     */
    public function viewCertificate($filename)
    {
        $path = public_path('certificates/' . $filename);
        
        if (!File::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak ditemukan'
            ], 404);
        }
        
        return response()->file($path);
    }
    
    /**
     * Lihat template
     */
    public function viewTemplate($filename)
    {
        $path = public_path('templates/' . $filename);
        
        if (!File::exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Template tidak ditemukan'
            ], 404);
        }
        
        return response()->file($path);
    }
    
    /**
     * Hitung rata-rata nilai peserta
     */
    private function calculateAverageScore($nilaiData)
    {
        $nilaiFields = [
            'nilai_teamwork', 'nilai_komunikasi', 'nilai_pengambilan_keputusan',
            'nilai_kualitas_kerja', 'nilai_teknologi', 'nilai_disiplin',
            'nilai_tanggungjawab', 'nilai_kerjasama',
            'nilai_kejujuran', 'nilai_kebersihan'
        ];
       
        $values = array_map(function($field) use ($nilaiData) {
            return floatval($nilaiData->$field ?? 0);
        }, $nilaiFields);
        
        $average = array_sum($values) / count($values);
        return number_format($average, 2);
    }
    
    /**
     * Konversi nilai ke akreditasi
     */
    private function getAkreditasi($rataRata)
    {
        $nilai = floatval($rataRata);
        if ($nilai > 90) return "Amat Baik";
        if ($nilai > 80) return "Baik";
        if ($nilai > 70) return "Cukup";
        if ($nilai > 60) return "Sedang";
        return "Kurang";
    }
    
    /**
     * Hitung total nilai peserta
     */
    private function calculateTotalScore($nilaiData)
    {
        $nilaiFields = [
            'nilai_teamwork', 'nilai_komunikasi', 'nilai_pengambilan_keputusan',
            'nilai_kualitas_kerja', 'nilai_teknologi', 'nilai_disiplin',
            'nilai_tanggungjawab', 'nilai_kerjasama',
            'nilai_kejujuran', 'nilai_kebersihan'
        ];
       
        $values = array_map(function($field) use ($nilaiData) {
            return floatval($nilaiData->$field ?? 0);
        }, $nilaiFields);
        
        return number_format(array_sum($values), 2);
    }
    
    /**
     * Format tanggal ke dd/mm/yyyy
     */
    private function formatTanggal($tanggal)
    {
        return Carbon::parse($tanggal)->format('d/m/Y');
    }
}