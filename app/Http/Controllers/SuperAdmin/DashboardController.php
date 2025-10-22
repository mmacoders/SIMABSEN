<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Bidang;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalUsers = User::where('role', 'user')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalBidangs = Bidang::count();
        
        // Get today's attendance summary
        $todayAttendances = Absensi::where('tanggal', date('Y-m-d'))->get();
        $presentToday = $todayAttendances->whereNotNull('waktu_masuk')->count();
        $absentToday = $totalUsers - $presentToday;
        
        // Get attendance by bidang
        $bidangStats = Bidang::withCount(['users as total_users' => function ($query) {
                        $query->where('role', 'user');
                    }])
                    ->withCount(['users as present_today' => function ($query) {
                        $query->where('role', 'user')
                              ->whereHas('absensis', function ($subQuery) {
                                  $subQuery->where('tanggal', date('Y-m-d'))
                                           ->whereNotNull('waktu_masuk');
                              });
                    }])
                    ->get();
        
        // Get weekly attendance data for charts
        $weeklyAttendance = $this->getWeeklyAttendanceData();
        
        return Inertia::render('SuperAdmin/Dashboard', [
            'totalUsers' => $totalUsers,
            'totalAdmins' => $totalAdmins,
            'totalBidangs' => $totalBidangs,
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'bidangStats' => $bidangStats,
            'weeklyAttendance' => $weeklyAttendance,
        ]);
    }
    
    private function getWeeklyAttendanceData()
    {
        $dates = [];
        $presentData = [];
        $absentData = [];
        
        // Get total users for absent calculation
        $totalUsers = User::where('role', 'user')->count();
        
        // Get data for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $formattedDate = $date->format('Y-m-d');
            $displayDate = $date->format('d M');
            
            $dates[] = $displayDate;
            
            // Get present count for this date
            $presentCount = Absensi::where('tanggal', $formattedDate)
                                 ->whereNotNull('waktu_masuk')
                                 ->count();
            
            $presentData[] = $presentCount;
            $absentData[] = $totalUsers - $presentCount;
        }
        
        return [
            'dates' => $dates,
            'present' => $presentData,
            'absent' => $absentData
        ];
    }
}