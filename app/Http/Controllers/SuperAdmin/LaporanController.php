<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PDF;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Get all bidangs
        $bidangs = Bidang::all();
        
        // Build query for attendances
        $attendancesQuery = Absensi::with(['user.bidang'])->orderBy('tanggal', 'desc');
        
        // Apply filters
        if ($request->bidang_id) {
            $attendancesQuery->whereHas('user', function ($query) use ($request) {
                $query->where('bidang_id', $request->bidang_id);
            });
        }
        
        if ($request->start_date) {
            $attendancesQuery->where('tanggal', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $attendancesQuery->where('tanggal', '<=', $request->end_date);
        }
        
        // Paginate results
        $attendances = $attendancesQuery->paginate(10)->withQueryString();
        
        return Inertia::render('SuperAdmin/LaporanGlobal', [
            'attendances' => $attendances,
            'bidangs' => $bidangs,
        ]);
    }
    
    public function exportExcel(Request $request)
    {
        // Build query for attendances
        $attendancesQuery = Absensi::with(['user.bidang'])->orderBy('tanggal', 'desc');
        
        // Apply filters
        if ($request->bidang_id) {
            $attendancesQuery->whereHas('user', function ($query) use ($request) {
                $query->where('bidang_id', $request->bidang_id);
            });
        }
        
        if ($request->start_date) {
            $attendancesQuery->where('tanggal', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $attendancesQuery->where('tanggal', '<=', $request->end_date);
        }
        
        // Get all results
        $attendances = $attendancesQuery->get();
        
        // Create CSV content
        $csvData = "Nama,Bidang,Tanggal,Masuk,Keluar,Status,Keterangan\n";
        
        foreach ($attendances as $attendance) {
            $csvData .= '"' . $attendance->user->name . '",';
            $csvData .= '"' . ($attendance->user->bidang->nama_bidang ?? '-') . '",';
            $csvData .= '"' . $attendance->tanggal . '",';
            $csvData .= '"' . ($attendance->waktu_masuk ?? '-') . '",';
            $csvData .= '"' . ($attendance->waktu_keluar ?? '-') . '",';
            $csvData .= '"' . $this->getStatusText($attendance) . '",';
            $csvData .= '"' . ($attendance->keterangan ?? '-') . '"' . "\n";
        }
        
        // Return CSV download
        return response($csvData)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="laporan-absensi.csv"');
    }
    
    public function exportPDF(Request $request)
    {
        // Build query for attendances
        $attendancesQuery = Absensi::with(['user.bidang'])->orderBy('tanggal', 'desc');
        
        // Apply filters
        if ($request->bidang_id) {
            $attendancesQuery->whereHas('user', function ($query) use ($request) {
                $query->where('bidang_id', $request->bidang_id);
            });
        }
        
        if ($request->start_date) {
            $attendancesQuery->where('tanggal', '>=', $request->start_date);
        }
        
        if ($request->end_date) {
            $attendancesQuery->where('tanggal', '<=', $request->end_date);
        }
        
        // Get all results
        $attendances = $attendancesQuery->get();
        
        // For now, we'll return a simple PDF response
        // In a real application, you would use a PDF library like DomPDF or TCPDF
        $pdfContent = "Laporan Absensi\n\n";
        $pdfContent .= "Tanggal: " . date('d/m/Y H:i:s') . "\n\n";
        $pdfContent .= "Nama\tBidang\tTanggal\tMasuk\tKeluar\tStatus\tKeterangan\n";
        
        foreach ($attendances as $attendance) {
            $pdfContent .= $attendance->user->name . "\t";
            $pdfContent .= ($attendance->user->bidang->nama_bidang ?? '-') . "\t";
            $pdfContent .= $attendance->tanggal . "\t";
            $pdfContent .= ($attendance->waktu_masuk ?? '-') . "\t";
            $pdfContent .= ($attendance->waktu_keluar ?? '-') . "\t";
            $pdfContent .= $this->getStatusText($attendance) . "\t";
            $pdfContent .= ($attendance->keterangan ?? '-') . "\n";
        }
        
        // Return PDF download
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="laporan-absensi.pdf"');
    }
    
    // Helper function to get status text
    private function getStatusText($attendance)
    {
        // Handle all possible status values from the database
        switch ($attendance->status) {
            case 'izin':
            case 'Izin':
            case 'Izin (Valid)':
                return 'Izin';
            case 'sakit':
                return 'Sakit';
            case 'terlambat':
            case 'Terlambat':
                return 'Terlambat';
            case 'hadir':
            case 'Hadir':
                return $attendance->waktu_masuk ? 'Hadir' : 'Belum Absen';
            case 'Izin Parsial (Check-in)':
                return 'Izin Parsial (Check-in)';
            case 'Izin Parsial (Selesai)':
                return 'Izin Parsial (Selesai)';
            default:
                // Handle any other status values or null status
                if ($attendance->waktu_masuk && $attendance->waktu_keluar) {
                    return 'Hadir';
                } else if ($attendance->waktu_masuk) {
                    return 'Sudah Check-in';
                } else {
                    return $attendance->status ?: 'Belum Absen';
                }
        }
    }
}