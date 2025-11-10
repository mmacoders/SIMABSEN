<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Izin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Get all users (since we're removing bidang filter)
        $users = User::all();
        
        // Get today's attendance for all users
        $todayAttendances = Absensi::where('tanggal', date('Y-m-d'))->with('user')->get();
        
        // Calculate statistics
        $totalUsers = $users->count();
        $presentToday = $todayAttendances->whereNotNull('waktu_masuk')->count();
        $lateToday = $todayAttendances->filter(function ($attendance) {
            // Assuming late is after 08:00
            return $attendance->waktu_masuk && $attendance->waktu_masuk > '08:00:00';
        })->count();
        $absentToday = $totalUsers - $presentToday;
        
        // Get leave requests for today
        $leaveToday = Izin::where('tanggal_mulai', '<=', date('Y-m-d'))
          ->where('tanggal_selesai', '>=', date('Y-m-d'))
          ->count();
        
        // Prepare data for the attendance table
        $attendanceData = $this->prepareAttendanceData($users, $todayAttendances);
        
        return Inertia::render('Admin/Dashboard', [
            'admin' => $admin,
            'stats' => [
                'present' => $presentToday,
                'late' => $lateToday,
                'leave' => $leaveToday,
                'absent' => $absentToday,
            ],
            'attendanceData' => $attendanceData,
        ]);
    }
    
    private function prepareAttendanceData($users, $attendances)
    {
        $data = [];
        
        foreach ($users as $user) {
            $attendance = $attendances->firstWhere('user_id', $user->id);
            
            $data[] = [
                'name' => $user->name,
                'checkIn' => $attendance ? $attendance->waktu_masuk : '-',
                'checkOut' => $attendance ? $attendance->waktu_keluar : '-',
                'status' => $this->determineStatus($attendance, $user),
                'keterangan' => $attendance ? $attendance->keterangan : '-',
            ];
        }
        
        return $data;
    }
    
    private function determineStatus($attendance, $user)
    {
        // Check if user has an active leave request
        $hasLeave = Izin::where('user_id', $user->id)
            ->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->exists();
            
        if ($hasLeave) {
            return 'Izin';
        }
        
        if (!$attendance) {
            return 'Tidak Hadir';
        }
        
        if ($attendance->waktu_masuk) {
            // Check if late (after 08:00)
            if ($attendance->waktu_masuk > '08:00:00') {
                return 'Terlambat';
            }
            return 'Hadir';
        }
        
        return 'Tidak Hadir';
    }
}