<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Hitung jumlah hari kerja antara dua tanggal
     */
    private function calculateWorkingDays($startDate, $endDate)
    {
        if (!$startDate || !$endDate) return 0;
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;
        
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }
    
    /**
     * Export nilai peserta magang ke Excel
     */
    public function exportInternsScore(Request $request)
    {
        try {
            $bidang = $request->input('bidang');
            $end_date_start = $request->input('end_date_start');
            $end_date_end = $request->input('end_date_end');

            // Query untuk ambil data lengkap peserta
            $query = "
            SELECT 
                pm.nama,
                pm.jenis_peserta,
                CASE 
                    WHEN pm.jenis_peserta = 'mahasiswa' THEN m.nim
                    ELSE s.nisn
                END as nomor_induk,
                pm.nama_institusi,  
                b.nama_bidang,
                DATE_FORMAT(pm.tanggal_masuk, '%d-%m-%Y') as tanggal_masuk,
                DATE_FORMAT(pm.tanggal_keluar, '%d-%m-%Y') as tanggal_keluar,
                pm.status,
                CASE 
                    WHEN pm.jenis_peserta = 'mahasiswa' THEN m.fakultas
                    ELSE NULL
                END as fakultas,
                CASE 
                    WHEN pm.jenis_peserta = 'mahasiswa' THEN m.jurusan
                    ELSE s.jurusan
                END as jurusan,
                m.semester,
                s.kelas,
                p.nilai_teamwork,
                p.nilai_komunikasi,
                p.nilai_pengambilan_keputusan,
                p.nilai_kualitas_kerja,
                p.nilai_teknologi,
                p.nilai_disiplin,
                p.nilai_tanggungjawab,
                p.nilai_kerjasama,
                p.nilai_inisiatif,
                p.nilai_kejujuran,
                p.nilai_kebersihan,
                p.jumlah_hadir
            FROM peserta_magang pm
            LEFT JOIN bidang b ON pm.id_bidang = b.id_bidang
            LEFT JOIN data_mahasiswa m ON pm.id_magang = m.id_magang
            LEFT JOIN data_siswa s ON pm.id_magang = s.id_magang
            INNER JOIN penilaian p ON pm.id_magang = p.id_magang
            WHERE pm.status = 'selesai'
            AND p.id_magang IS NOT NULL
            ";

            // Tambahkan filter bidang dan tanggal
            $queryParams = [];
            if ($bidang) {
                $query .= " AND b.nama_bidang = ?";
                $queryParams[] = $bidang;
            }
            if ($end_date_start && $end_date_end) {
                $query .= " AND pm.tanggal_keluar BETWEEN ? AND ?";
                $queryParams[] = $end_date_start;
                $queryParams[] = $end_date_end;
            }

            $rows = DB::select($query, $queryParams);

            // Buat spreadsheet baru
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set header kolom
            $headers = [
                'Nama', 'Nomor Induk', 'Institusi', 'Bidang', 
                'Tanggal Masuk', 'Tanggal Keluar', 'Fakultas', 'Jurusan', 'Absensi',
                'Nilai Teamwork', 'Nilai Komunikasi', 'Nilai Pengambilan Keputusan',
                'Nilai Kualitas Kerja', 'Nilai Teknologi', 'Nilai Disiplin',
                'Nilai Tanggung Jawab', 'Nilai Kerjasama', 'Nilai Inisiatif',
                'Nilai Kejujuran', 'Nilai Kebersihan'
            ];
            
            // Tambahkan header
            $column = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($column, 1, $header);
                $column++;
            }
            
            // Tambahkan data
            $row = 2;
            foreach ($rows as $dataRow) {
                $sheet->setCellValueByColumnAndRow(1, $row, $dataRow->nama ?? '-');
                $sheet->setCellValueByColumnAndRow(2, $row, $dataRow->nomor_induk ?? '-');
                $sheet->setCellValueByColumnAndRow(3, $row, $dataRow->nama_institusi ?? '-');
                $sheet->setCellValueByColumnAndRow(4, $row, $dataRow->nama_bidang ?? '-');
                $sheet->setCellValueByColumnAndRow(5, $row, $dataRow->tanggal_masuk ?? '-');
                $sheet->setCellValueByColumnAndRow(6, $row, $dataRow->tanggal_keluar ?? '-');
                $sheet->setCellValueByColumnAndRow(7, $row, $dataRow->fakultas ?? '-');
                $sheet->setCellValueByColumnAndRow(8, $row, $dataRow->jurusan ?? '-');
                $sheet->setCellValueByColumnAndRow(9, $row, $dataRow->jumlah_hadir ?? '-');
                $sheet->setCellValueByColumnAndRow(10, $row, $dataRow->nilai_teamwork ?? '-');
                $sheet->setCellValueByColumnAndRow(11, $row, $dataRow->nilai_komunikasi ?? '-');
                $sheet->setCellValueByColumnAndRow(12, $row, $dataRow->nilai_pengambilan_keputusan ?? '-');
                $sheet->setCellValueByColumnAndRow(13, $row, $dataRow->nilai_kualitas_kerja ?? '-');
                $sheet->setCellValueByColumnAndRow(14, $row, $dataRow->nilai_teknologi ?? '-');
                $sheet->setCellValueByColumnAndRow(15, $row, $dataRow->nilai_disiplin ?? '-');
                $sheet->setCellValueByColumnAndRow(16, $row, $dataRow->nilai_tanggungjawab ?? '-');
                $sheet->setCellValueByColumnAndRow(17, $row, $dataRow->nilai_kerjasama ?? '-');
                $sheet->setCellValueByColumnAndRow(18, $row, $dataRow->nilai_inisiatif ?? '-');
                $sheet->setCellValueByColumnAndRow(19, $row, $dataRow->nilai_kejujuran ?? '-');
                $sheet->setCellValueByColumnAndRow(20, $row, $dataRow->nilai_kebersihan ?? '-');
                $row++;
            }
            
            // Atur lebar kolom
            $columnWidths = [30, 20, 30, 20, 15, 15, 20, 25, 15, 15, 25, 20, 15, 15, 20, 15, 15, 15, 15, 10];
            for ($i = 0; $i < count($columnWidths); $i++) {
                $sheet->getColumnDimensionByColumn($i + 1)->setWidth($columnWidths[$i]);
            }
            
            // Buat file
            $writer = new Xlsx($spreadsheet);
            $filename = 'Data_Anak_Magang.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $error) {
            \Log::error('Error exporting data: ' . $error->getMessage());
            return response()->json([
                'status' => "error",
                'message' => "Terjadi kesalahan saat export data",
                'error' => $error->getMessage()
            ], 500);
        }
    }

    /**
     * Generate sertifikat magang
     */
    public function generateCertificate($id_magang)
    {
        try {
            // Ambil data lengkap peserta
            $assessment = DB::select("
                SELECT p.*, pm.nama, pm.jenis_peserta,
                       i.nama_institusi, b.nama_bidang,
                       CASE 
                           WHEN pm.jenis_peserta = 'mahasiswa' THEN m.nim
                           ELSE s.nisn
                       END as nomor_induk,
                       m.fakultas, m.jurusan as jurusan_mahasiswa,
                       s.jurusan as jurusan_siswa, s.kelas,
                       pm.tanggal_masuk, pm.tanggal_keluar
                FROM penilaian p
                JOIN peserta_magang pm ON p.id_magang = pm.id_magang
                LEFT JOIN institusi i ON pm.id_institusi = i.id_institusi
                LEFT JOIN bidang b ON pm.id_bidang = b.id_bidang
                LEFT JOIN data_mahasiswa m ON pm.id_magang = m.id_magang
                LEFT JOIN data_siswa s ON pm.id_magang = s.id_magang
                WHERE p.id_magang = ?
            ", [$id_magang]);

            if (empty($assessment)) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Ambil template sertifikat
            $template = DB::select("
                SELECT file_path
                FROM dokumen_template
                WHERE jenis = 'sertifikat'
                AND active = true
                LIMIT 1
            ");

            if (empty($template)) {
                return response()->json([
                    'message' => 'Template sertifikat tidak ditemukan'
                ], 404);
            }

            $data = $assessment[0];
            
            // Buat PDF dari view
            $pdf = PDF::loadView('sertifikat.generate', [
                'peserta' => $data,
                'tanggal' => now()->format('d F Y')
            ]);
            
            $fileName = 'sertifikat_' . str_replace(' ', '_', $data->nama) . '.pdf';
            
            return $pdf->download($fileName);
            
        } catch (\Exception $error) {
            \Log::error('Error generating certificate: ' . $error->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    /**
     * Generate tanda terima magang
     */
    public function generateReceipt(Request $request)
    {
        try {
            $request->validate([
                'internIds' => 'required|array',
                'internIds.*' => 'string'
            ]);
            
            $internIds = $request->input('internIds');

            // Ambil data peserta yang dipilih
            $placeholders = implode(',', array_fill(0, count($internIds), '?'));
            $selectedInterns = DB::select("
                SELECT 
                    pm.nama,
                    pm.nama_institusi,
                    b.nama_bidang,
                    u.nama as nama_mentor,
                    DATE_FORMAT(pm.tanggal_masuk, '%d-%m-%Y') as tanggal_masuk,
                    DATE_FORMAT(pm.tanggal_keluar, '%d-%m-%Y') as tanggal_keluar
                FROM peserta_magang pm
                JOIN bidang b ON pm.id_bidang = b.id_bidang
                LEFT JOIN users u ON pm.mentor_id = u.id_users
                WHERE pm.id_magang IN ({$placeholders})
            ", $internIds);

            // Format tanggal saat ini
            $currentDate = Carbon::now()->locale('id')->isoFormat('D MMMM Y');
            
            // Generate PDF
            $pdf = PDF::loadView('reports.receipt', [
                'interns' => $selectedInterns,
                'currentDate' => $currentDate,
            ]);
            
            $pdf->setPaper('a4', 'landscape');
            
            return $pdf->download('tanda-terima-magang.pdf');

        } catch (\Exception $error) {
            \Log::error('Error generating receipt: ' . $error->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate receipt',
                'error' => $error->getMessage()
            ], 500);
        }
    }
}