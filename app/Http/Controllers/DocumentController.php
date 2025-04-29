<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Spatie\PdfToText\Pdf;
use Dompdf\Dompdf;
use App\Models\Template;

class DocumentController extends Controller
{
    /**
     * Upload template dokumen baru
     */
    public function uploadTemplate(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:doc,docx|max:51200', // 50MB max
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Nonaktifkan template sebelumnya
            Template::where('id_users', $user->id_users)
                    ->update(['active' => 0]);

            $file = $request->file('file');
            $uuid = Str::uuid();
            $originalName = $file->getClientOriginalName();
            $storedName = $uuid . '---' . $originalName;
            $filePath = 'templates/' . $storedName;

            // Simpan file
            Storage::disk('public')->put($filePath, file_get_contents($file));

            // Simpan ke database
            Template::create([
                'id_dokumen' => $uuid,
                'id_users' => $user->id_users,
                'file_path' => $filePath,
                'active' => 1,
                'created_by' => $user->id_users
            ]);

            return redirect()->back()
                ->with('success', 'Template berhasil diupload!');

        } catch (\Exception $e) {
            \Log::error('Error uploading template: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal upload template: ' . $e->getMessage());
        }
    }

    /**
     * Upload template via API
     */
    public function uploadTemplateApi(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:doc,docx|max:51200', // 50MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Nonaktifkan template sebelumnya
            Template::where('id_users', $user->id_users)
                    ->update(['active' => 0]);

            $file = $request->file('file');
            $uuid = Str::uuid();
            $originalName = $file->getClientOriginalName();
            $storedName = $uuid . '---' . $originalName;
            $filePath = 'templates/' . $storedName;

            // Simpan file
            Storage::disk('public')->put($filePath, file_get_contents($file));

            // Simpan ke database
            Template::create([
                'id_dokumen' => $uuid,
                'id_users' => $user->id_users,
                'file_path' => $filePath,
                'active' => 1,
                'created_by' => $user->id_users
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diupload',
                'data' => [
                    'id_dokumen' => $uuid,
                    'file_path' => $filePath
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil daftar template aktif
     */
    public function getTemplates()
    {
        try {
            $user = Auth::user();
            $templates = Template::where('id_users', $user->id_users)
                                 ->where('active', 1)
                                 ->get();

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
     * Hapus template
     */
    public function deleteTemplate($id)
    {
        try {
            $user = Auth::user();
            $template = Template::where('id_dokumen', $id)
                              ->where('id_users', $user->id_users)
                              ->firstOrFail();

            // Hapus file fisik
            if (Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }

            // Hapus dari database
            $template->delete();

            return redirect()->back()
                ->with('success', 'Template berhasil dihapus');

        } catch (\Exception $e) {
            \Log::error('Error deleting template: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }

    /**
     * Hapus template via API
     */
    public function deleteTemplateApi($id)
    {
        try {
            $user = Auth::user();
            $template = Template::where('id_dokumen', $id)
                              ->where('id_users', $user->id_users)
                              ->firstOrFail();

            // Hapus file fisik
            if (Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }

            // Hapus dari database
            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus template',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview template
     */
    public function previewTemplate($id)
    {
        try {
            $user = Auth::user();
            $template = Template::where('id_dokumen', $id)
                              ->where('id_users', $user->id_users)
                              ->firstOrFail();
    
            if (!Storage::disk('public')->exists($template->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File template tidak ditemukan di storage'
                ], 404);
            }
    
            // Gunakan Google Docs Viewer untuk preview
            $fileUrl = asset('storage/' . $template->file_path);
            return redirect('https://docs.google.com/viewer?url=' . urlencode($fileUrl) . '&embedded=true');
            
        } catch (\Exception $e) {
            \Log::error('Error previewing template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template
     */
    public function downloadTemplate($id)
    {
        try {
            $user = Auth::user();
            $template = Template::where('id_dokumen', $id)
                              ->where('id_users', $user->id_users)
                              ->firstOrFail();

            // Validasi file
            if (!Storage::disk('public')->exists($template->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File template tidak ditemukan di storage'
                ], 404);
            }

            // Dapatkan nama file asli
            $pathParts = explode('---', $template->file_path);
            $originalName = count($pathParts) > 1 ? end($pathParts) : basename($template->file_path);

            // Kembalikan file untuk didownload
            return Storage::disk('public')->download(
                $template->file_path,
                $originalName
            );

        } catch (\Exception $e) {
            \Log::error('Error downloading template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunduh template: ' . $e->getMessage()
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
            $user = Auth::user();
            $template = Template::where('id_users', $user->id_users)
                              ->where('active', 1)
                              ->latest()
                              ->first();
   
            if (!$template) {
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
   
            // Generate sertifikat menggunakan template Word
            $templatePath = storage_path('app/public/' . $template->file_path);
            
            // Buat direktori untuk sertifikat jika belum ada
            $certificatesDir = public_path('certificates');
            if (!File::exists($certificatesDir)) {
                File::makeDirectory($certificatesDir, 0755, true);
            }
            
            // Generate nama file
            $docxName = 'sertifikat_' . Str::slug($peserta[0]->nama) . '_' . time() . '.docx';
            $docxPath = $certificatesDir . '/' . $docxName;
            
            // Proses template dengan data peserta
            try {
                $templateProcessor = new TemplateProcessor($templatePath);
                
                // Set data peserta ke template
                $templateProcessor->setValue('NAMA', $peserta[0]->nama ?? '');
                $templateProcessor->setValue('NIM', $peserta[0]->nomor_induk ?? '');
                $templateProcessor->setValue('JURUSAN', $peserta[0]->jurusan ?? '');
                $templateProcessor->setValue('INSTITUSI', $peserta[0]->institusi ?? '');
                $templateProcessor->setValue('TANGGAL_MULAI', $this->formatTanggal($peserta[0]->tanggal_mulai ?? now()));
                $templateProcessor->setValue('TANGGAL_SELESAI', $this->formatTanggal($peserta[0]->tanggal_selesai ?? now()));
                $templateProcessor->setValue('MENTOR', $peserta[0]->nama_mentor ?? '');
                $templateProcessor->setValue('NIP_MENTOR', $peserta[0]->nip_mentor ?? '');
                
                // Jika ada penilaian
                if (isset($peserta[0]->id_penilaian)) {
                    $rataRata = $this->calculateAverageScore($peserta[0]);
                    $templateProcessor->setValue('NILAI_RATA', $rataRata);
                    $templateProcessor->setValue('NILAI_AKREDITASI', $this->getAkreditasi($rataRata));
                }
                
                // Simpan dokumen hasil
                $templateProcessor->saveAs($docxPath);
                
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
                        'nama_peserta' => $peserta[0]->nama,
                        'download_url' => route('sertifikat.download', ['id' => $id_magang])
                    ]
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Error generating certificate: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses template sertifikat',
                    'error' => $e->getMessage()
                ], 500);
            }
   
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
                'SELECT sertifikat_path, nama FROM peserta_magang WHERE id_magang = ?',
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
            $fileName = 'Sertifikat_' . Str::slug($peserta[0]->nama) . '.docx';
            
            return response()->download($filePath, $fileName);

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
            $validator = Validator::make($request->all(), [
                'nomor_surat' => 'required|string',
                'tanggal' => 'required|date',
                'penerima' => 'required|string',
                'jabatan' => 'required|string',
                'departemen' => 'required|string',
                'daftar_barang' => 'required|array'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 400);
            }
           
            // Buat dokumen Word baru
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();
            
            // Judul
            $section->addText('TANDA TERIMA', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
            $section->addText('No: ' . $request->nomor_surat, ['size' => 12], ['alignment' => 'center']);
            $section->addTextBreak();
            
            // Data penerima
            $section->addText('Yang bertanda tangan di bawah ini:', ['size' => 12]);
            $section->addTextBreak();
            
            $tableStyle = ['borderSize' => 0, 'cellMargin' => 80];
            $table = $section->addTable($tableStyle);
            
            $table->addRow();
            $table->addCell(2000)->addText('Nama', ['size' => 12]);
            $table->addCell(500)->addText(':', ['size' => 12]);
            $table->addCell(5000)->addText($request->penerima, ['size' => 12]);
            
            $table->addRow();
            $table->addCell(2000)->addText('Jabatan', ['size' => 12]);
            $table->addCell(500)->addText(':', ['size' => 12]);
            $table->addCell(5000)->addText($request->jabatan, ['size' => 12]);
            
            $table->addRow();
            $table->addCell(2000)->addText('Departemen', ['size' => 12]);
            $table->addCell(500)->addText(':', ['size' => 12]);
            $table->addCell(5000)->addText($request->departemen, ['size' => 12]);
            
            $section->addTextBreak();
            $section->addText('Telah menerima barang-barang sebagai berikut:', ['size' => 12]);
            $section->addTextBreak();
            
            // Tabel daftar barang
            $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
            $tableHeaderStyle = ['bold' => true, 'size' => 12];
            $cellHeaderStyle = ['bgColor' => 'eeeeee', 'valign' => 'center'];
            
            $table = $section->addTable($tableStyle);
            
            // Header tabel
            $table->addRow();
            $table->addCell(800, $cellHeaderStyle)->addText('No', $tableHeaderStyle, ['alignment' => 'center']);
            $table->addCell(5000, $cellHeaderStyle)->addText('Nama Barang', $tableHeaderStyle, ['alignment' => 'center']);
            $table->addCell(1500, $cellHeaderStyle)->addText('Jumlah', $tableHeaderStyle, ['alignment' => 'center']);
            $table->addCell(2000, $cellHeaderStyle)->addText('Satuan', $tableHeaderStyle, ['alignment' => 'center']);
            
            // Isi tabel
            $no = 1;
            foreach ($request->daftar_barang as $barang) {
                $table->addRow();
                $table->addCell(800)->addText($no, ['size' => 12], ['alignment' => 'center']);
                $table->addCell(5000)->addText($barang['nama'] ?? '-', ['size' => 12]);
                $table->addCell(1500)->addText($barang['jumlah'] ?? '0', ['size' => 12], ['alignment' => 'center']);
                $table->addCell(2000)->addText($barang['satuan'] ?? '-', ['size' => 12], ['alignment' => 'center']);
                $no++;
            }
            
            $section->addTextBreak();
            
            // Tanggal dan tanda tangan
            $tanggal = Carbon::parse($request->tanggal)->format('d F Y');
            $section->addText($tanggal, ['size' => 12], ['alignment' => 'right']);
            $section->addTextBreak(3);
            $section->addText($request->penerima, ['size' => 12, 'bold' => true], ['alignment' => 'right']);
            
            // Simpan dokumen
            $fileName = 'tanda_terima_' . time() . '.docx';
            $filePath = 'receipts/' . $fileName;
            $fullPath = storage_path('app/public/' . $filePath);
            
            // Pastikan direktori ada
            $dir = storage_path('app/public/receipts');
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($fullPath);
            
            return response()->json([
                'success' => true,
                'message' => 'Tanda terima berhasil dibuat',
                'data' => [
                    'file_path' => $filePath,
                    'download_url' => asset('storage/' . $filePath)
                ]
            ]);

        } catch (\Exception $error) {
            \Log::error('Error generating receipt: ' . $error->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tanda terima',
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