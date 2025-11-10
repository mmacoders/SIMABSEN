<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    // Show attendance form
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get today's attendance record if exists
        $todayAttendance = $user->absensis()->where('tanggal', date('Y-m-d'))->first();
        
        // Check if user has leave permission for today
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        // Get system settings for location info
        $systemSettings = SystemSetting::first();
        
        // Check if user is in testing mode
        $testingModeDisabled = session('testing_mode_disabled', false);
        
        return inertia('User/Absensi', [
            'user' => $user,
            'todayAttendance' => $todayAttendance,
            'todayIzin' => $todayIzin,
            'systemSettings' => $systemSettings,
            'testingModeDisabled' => $testingModeDisabled,
        ]);
    }
    
    // Get today's attendance record for API
    public function getTodayAttendance()
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Get today's attendance record if exists
        $todayAttendance = $user->absensis()->where('tanggal', date('Y-m-d'))->first();
        
        // Check if user has leave permission for today
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        return response()->json([
            'attendance' => $todayAttendance,
            'izin' => $todayIzin,
            'can_check_in' => $this->canCheckIn($user),
        ]);
    }
    
    // Process check-in
    public function checkIn(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            Log::info('Check-in attempt', ['user_id' => $user->id, 'request_data' => $request->all()]);
            
            // Check if location validation is disabled (either globally or for testing)
            $systemSettings = SystemSetting::first();
            $locationValidationDisabled = ($systemSettings && $systemSettings->disable_location_validation) || $request->session()->get('testing_mode_disabled', false);
            
            // Validate GPS coordinates only if location validation is enabled
            if (!$locationValidationDisabled) {
                $request->validate([
                    'lat' => 'required|numeric',
                    'lng' => 'required|numeric',
                ]);
            } else {
                // When location validation is disabled, lat and lng are optional
                $request->validate([
                    'lat' => 'nullable|numeric',
                    'lng' => 'nullable|numeric',
                ]);
            }
            
            // Set default values for lat/lng if not provided and location validation is disabled
            $lat = $request->lat ?? 0;
            $lng = $request->lng ?? 0;
            
            // Check if user is within office radius (unless location validation is disabled)
            if (!$locationValidationDisabled) {
                if ($this->validateLocation($lat, $lng) !== 'valid') {
                    Log::warning('Location validation failed', ['user_id' => $user->id, 'lat' => $lat, 'lng' => $lng]);
                    return redirect()->back()->with('error', 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.');
                }
            }
            
            // Check if user can check in
            if (!$this->canCheckIn($user)) {
                Log::warning('User cannot check in', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'Anda tidak diizinkan melakukan check-in hari ini.');
            }
            
            // Get system settings for working hours
            $jamMasuk = $systemSettings ? $systemSettings->jam_masuk : '08:00:00';
            $jamPulang = $systemSettings ? $systemSettings->jam_pulang : '17:00:00';
            
            // Check current time
            $currentTime = date('H:i:s');
            $isLate = $currentTime > $jamMasuk && $currentTime <= $jamPulang; // Only late if within working hours
            $isEarly = $currentTime < $jamMasuk;
            $isOnTime = $currentTime == $jamMasuk;
            $isOutsideWorkingHours = $currentTime > $jamPulang;
            
            // Check if user has partial leave permission
            $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
                ->where('tanggal_selesai', '>=', date('Y-m-d'))
                ->first();
            $isPartialLeave = $todayIzin && $todayIzin->jenis_izin === 'parsial';
            
            // Prepare data for attendance record
            $data = [
                'waktu_masuk' => $currentTime,
                'lat_masuk' => $lat,
                'lng_masuk' => $lng,
                'status_lokasi_masuk' => $locationValidationDisabled ? 'valid' : $this->validateLocation($lat, $lng),
            ];
            
            // Set status based on arrival time
            if ($isPartialLeave) {
                // If user has partial leave, set appropriate status
                $data['status'] = 'Izin Parsial (Check-in)';
                $data['keterangan'] = $todayIzin->keterangan;
            } elseif ($isOutsideWorkingHours) {
                // If user is checking in after work hours, mark as absent
                $data['status'] = 'alpha';
                $data['keterangan'] = 'Check-in dilakukan di luar jam kerja';
            } elseif ($isEarly || $isOnTime) {
                // Early arrivals and on-time arrivals are considered "tepat waktu" (on time)
                $data['status'] = 'hadir'; // Early or on-time arrivals are marked as present
            } elseif ($isLate && $request->has('keterangan')) {
                // Late arrivals with explanation
                $data['keterangan'] = $request->keterangan;
                $data['status'] = 'terlambat';
            } elseif ($isLate) {
                // Late arrivals without explanation
                $data['status'] = 'terlambat';
            } else {
                // Fallback (should not happen with the above conditions)
                $data['status'] = 'hadir';
            }
            
            Log::info('Creating/updating attendance record', ['user_id' => $user->id, 'data' => $data]);
            
            // Use database transaction to ensure data consistency
            $attendance = DB::transaction(function () use ($user, $data) {
                // Check if user already has an attendance record for today
                $attendance = Absensi::where('user_id', $user->id)
                    ->where('tanggal', date('Y-m-d'))
                    ->first();
                    
                if ($attendance) {
                    // If attendance record exists, update it with check-in data
                    Log::info('Updating existing attendance record', ['attendance_id' => $attendance->id]);
                    $attendance->update($data);
                } else {
                    // If no attendance record exists, create one
                    Log::info('Creating new attendance record');
                    $attendance = Absensi::create(array_merge($data, [
                        'user_id' => $user->id,
                        'tanggal' => date('Y-m-d')
                    ]));
                }
                
                return $attendance;
            });
            
            Log::info('Attendance record saved', ['attendance_id' => $attendance->id]);
            
            // If user is late but didn't provide explanation yet, return with prompt
            // Only prompt for late arrivals, not early arrivals, outside working hours, or partial leave
            if ($isLate && !$request->has('keterangan') && !$attendance->keterangan && !$isPartialLeave && !$isOutsideWorkingHours) {
                return redirect()->back()->with('prompt_keterangan', 'Anda terlambat. Silakan berikan keterangan keterlambatan.');
            }
            
            // If user has partial leave, show appropriate message
            if ($isPartialLeave) {
                return redirect()->back()->with('success', 'Check-in izin parsial berhasil. Anda tidak wajib melakukan check-out.');
            }
            
            // If user checked in outside working hours, show appropriate message
            if ($isOutsideWorkingHours) {
                return redirect()->back()->with('success', 'Check-in berhasil dicatat. Anda telah ditandai sebagai tidak hadir (alpha) karena check-in dilakukan di luar jam kerja.');
            }
            
            return redirect()->back()->with('success', 'Check-in berhasil dicatat.');
        } catch (\Exception $e) {
            Log::error('Check-in error', ['user_id' => Auth::id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan check-in. Silakan coba lagi.');
        }
    }
    
    // Process check-out
    public function checkOut(Request $request)
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            Log::info('Check-out attempt', ['user_id' => $user->id, 'request_data' => $request->all()]);
            
            // Check if location validation is disabled (either globally or for testing)
            $systemSettings = SystemSetting::first();
            $locationValidationDisabled = ($systemSettings && $systemSettings->disable_location_validation) || $request->session()->get('testing_mode_disabled', false);
            
            // Validate GPS coordinates only if location validation is enabled
            if (!$locationValidationDisabled) {
                $request->validate([
                    'lat' => 'required|numeric',
                    'lng' => 'required|numeric',
                ]);
            } else {
                // When location validation is disabled, lat and lng are optional
                $request->validate([
                    'lat' => 'nullable|numeric',
                    'lng' => 'nullable|numeric',
                ]);
            }
            
            // Set default values for lat/lng if not provided and location validation is disabled
            $lat = $request->lat ?? 0;
            $lng = $request->lng ?? 0;
            
            // Check if user is within office radius (unless location validation is disabled)
            if (!$locationValidationDisabled) {
                if ($this->validateLocation($lat, $lng) !== 'valid') {
                    Log::warning('Location validation failed', ['user_id' => $user->id, 'lat' => $lat, 'lng' => $lng]);
                    return redirect()->back()->with('error', 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.');
                }
            }
            
            // Check if user can check out
            if (!$this->canCheckOut($user)) {
                Log::warning('User cannot check out', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'Anda tidak diizinkan melakukan check-out hari ini.');
            }
            
            // Get today's attendance record
            $attendance = $user->absensis()->where('tanggal', date('Y-m-d'))->first();
            
            if (!$attendance) {
                Log::warning('No attendance record found for check-out', ['user_id' => $user->id]);
                return redirect()->back()->with('error', 'Anda belum melakukan check-in hari ini.');
            }
            
            // Check if user has partial leave
            $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
                ->where('tanggal_selesai', '>=', date('Y-m-d'))
                ->first();
            $isPartialLeave = $todayIzin && $todayIzin->jenis_izin === 'parsial';
            
            // Prepare check-out data
            $checkoutData = [
                'waktu_keluar' => date('H:i:s'),
                'lat_keluar' => $lat,
                'lng_keluar' => $lng,
                'status_lokasi_keluar' => $locationValidationDisabled ? 'valid' : $this->validateLocation($lat, $lng),
            ];
            
            // If user has partial leave, update status
            if ($isPartialLeave) {
                $checkoutData['status'] = 'Izin Parsial (Selesai)';
            }
            
            Log::info('Updating attendance record for check-out', ['attendance_id' => $attendance->id, 'data' => $checkoutData]);
            
            // Use database transaction to ensure data consistency
            DB::transaction(function () use ($attendance, $checkoutData) {
                // Update check-out information
                $attendance->update($checkoutData);
            });
            
            Log::info('Attendance record updated for check-out', ['attendance_id' => $attendance->id]);
            
            // If user has partial leave, show appropriate message
            if ($isPartialLeave) {
                return redirect()->back()->with('success', 'Check-out berhasil dicatat. Izin parsial telah selesai.');
            }
            
            return redirect()->back()->with('success', 'Check-out berhasil dicatat.');
        } catch (\Exception $e) {
            Log::error('Check-out error', ['user_id' => Auth::id(), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat melakukan check-out. Silakan coba lagi.');
        }
    }
    
    // Process permission/leave request
    public function requestPermission(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'keterangan' => 'required|string|max:500',
            'jenis_izin' => 'required|in:penuh,parsial',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        
        // Create or update attendance record with permission status
        $attendance = Absensi::updateOrCreate(
            ['user_id' => $user->id, 'tanggal' => $request->tanggal_mulai],
            [
                'keterangan' => $request->keterangan,
                'status' => $request->jenis_izin === 'penuh' ? 'Izin (Valid)' : 'Izin',
            ]
        );
        
        // Create izin record
        Izin::updateOrCreate(
            [
                'user_id' => $user->id, 
                'tanggal' => $request->tanggal_mulai, // Include tanggal in search criteria
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai
            ],
            [
                'jenis_izin' => $request->jenis_izin,
                'keterangan' => $request->keterangan,
                'disetujui_oleh' => 'System', // In a real application, this would be set by an admin
            ]
        );
        
        return redirect()->back()->with('success', 'Permintaan izin berhasil diajukan.');
    }
    
    // Mark absent users - can be called via console command or API
    public function markAbsentUsers()
    {
        // Get system settings for working hours
        $systemSettings = SystemSetting::first();
        $jamPulang = $systemSettings ? $systemSettings->jam_pulang : '17:00:00';
        
        // Get today's date
        $today = Carbon::today()->format('Y-m-d');
        
        // Get all active users
        $users = User::where('status', 'active')->get();
        
        $absentCount = 0;
        
        foreach ($users as $user) {
            // Skip super admin users as they might not be required to attend
            if ($user->role === 'superadmin') {
                continue;
            }
            
            // Check if user already has an attendance record for today
            $attendance = Absensi::where('user_id', $user->id)
                ->where('tanggal', $today)
                ->first();
                
            // If no attendance record exists, mark as absent
            if (!$attendance) {
                Absensi::create([
                    'user_id' => $user->id,
                    'tanggal' => $today,
                    'status' => 'alpha', // Absent
                    'keterangan' => 'Tidak melakukan absensi masuk dan keluar',
                ]);
                
                $absentCount++;
                continue;
            }
            
            // If attendance record exists but has no check-in or check-out
            if (!$attendance->waktu_masuk && !$attendance->waktu_keluar) {
                // Update status to absent if not already set
                if ($attendance->status !== 'alpha') {
                    $attendance->update([
                        'status' => 'alpha',
                        'keterangan' => 'Tidak melakukan absensi masuk dan keluar',
                    ]);
                    
                    $absentCount++;
                }
                continue;
            }
            
            // If user has partial attendance (either check-in or check-out but not both)
            // And it's past the end of workday, mark as absent
            $currentTime = Carbon::now();
            $endOfWorkday = Carbon::today()->setTimeFromTimeString($jamPulang);
            
            if ($currentTime->greaterThan($endOfWorkday)) {
                // If user only checked in but not out
                if ($attendance->waktu_masuk && !$attendance->waktu_keluar) {
                    // Check if this is not a partial leave case
                    $isPartialLeave = $user->izins()
                        ->where('tanggal_mulai', '<=', $today)
                        ->where('tanggal_selesai', '>=', $today)
                        ->where('jenis_izin', 'parsial')
                        ->exists();
                        
                    if (!$isPartialLeave) {
                        $attendance->update([
                            'status' => 'alpha',
                            'keterangan' => 'Tidak melakukan absensi keluar',
                        ]);
                        
                        $absentCount++;
                    }
                }
                // If user only checked out but not in (unusual case)
                elseif (!$attendance->waktu_masuk && $attendance->waktu_keluar) {
                    $attendance->update([
                        'status' => 'alpha',
                        'keterangan' => 'Tidak melakukan absensi masuk',
                    ]);
                    
                    $absentCount++;
                }
            }
        }
        
        // Return appropriate response based on request type
        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Completed marking absent users',
                'absent_count' => $absentCount
            ]);
        }
        
        return redirect()->back()->with('success', "Completed marking absent users. {$absentCount} users marked as absent.");
    }
    
    // Check if user can check in
    private function canCheckIn($user)
    {
        // Check if user has full leave permission for today
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        // If user has full leave, they cannot check in
        if ($todayIzin && $todayIzin->jenis_izin === 'penuh') {
            return false;
        }
        
        // Check if user already has a completed attendance record
        $todayAttendance = $user->absensis()->where('tanggal', date('Y-m-d'))->first();
        if ($todayAttendance && $todayAttendance->waktu_masuk && $todayAttendance->waktu_keluar) {
            return false;
        }
        
        return true;
    }
    
    // Check if user can check out
    private function canCheckOut($user)
    {
        // Check if user has full leave permission for today
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        // If user has full leave, they cannot check out
        if ($todayIzin && $todayIzin->jenis_izin === 'penuh') {
            return false;
        }
        
        // Check if user has checked in
        $todayAttendance = $user->absensis()->where('tanggal', date('Y-m-d'))->first();
        if (!$todayAttendance || !$todayAttendance->waktu_masuk) {
            return false;
        }
        
        // If user already checked out, they cannot check out again
        if ($todayAttendance->waktu_keluar) {
            return false;
        }
        
        return true;
    }
    
    // Validate if current time is within working hours
    private function isWithinWorkingHours($type, $systemSettings)
    {
        // If no system settings, allow attendance (fallback)
        if (!$systemSettings) {
            return true;
        }
        
        $currentTime = Carbon::now();
        $currentHour = $currentTime->format('H:i:s');
        
        // For check-in, allow check-in from 1 hour before jam_masuk until 2 hours after jam_masuk
        if ($type === 'checkin') {
            $jamMasuk = $systemSettings->jam_masuk ?? '08:00:00';
            
            // Allow check-in from 1 hour before jam_masuk
            $startCheckInTime = Carbon::createFromTimeString($jamMasuk)->subHour()->format('H:i:s');
            
            // Allow check-in until 2 hours after jam_masuk
            $endCheckInTime = Carbon::createFromTimeString($jamMasuk)->addHours(2)->format('H:i:s');
            
            return $currentHour >= $startCheckInTime && $currentHour <= $endCheckInTime;
        }
        // For check-out, check if current time is between jam_masuk and 2 hours after jam_pulang
        elseif ($type === 'checkout') {
            $jamMasuk = $systemSettings->jam_masuk ?? '08:00:00';
            $jamPulang = $systemSettings->jam_pulang ?? '17:00:00';
            
            // Allow check-out from jam_masuk onwards until 2 hours after jam_pulang
            $endCheckOutTime = Carbon::createFromTimeString($jamPulang)->addHours(2)->format('H:i:s');
            
            return $currentHour >= $jamMasuk && $currentHour <= $endCheckOutTime;
        }
        
        return true;
    }
    
    // Validate user location against office coordinates
    private function validateLocation($lat, $lng)
    {
        // Get system settings for location validation
        $systemSettings = SystemSetting::first();
        
        // Use system settings or default values
        $poldaLat = $systemSettings ? $systemSettings->location_latitude : 0.524000504793017;
        $poldaLng = $systemSettings ? $systemSettings->location_longitude : 123.06047523547292;
        $radius = $systemSettings ? $systemSettings->location_radius : 100;
        
        // Convert to radians
        $lat1 = deg2rad($lat);
        $lng1 = deg2rad($lng);
        $lat2 = deg2rad($poldaLat);
        $lng2 = deg2rad($poldaLng);
        
        // Haversine formula
        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;
        
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        // Earth's radius in meters
        $earthRadius = 6371000;
        
        // Calculate distance
        $distance = $earthRadius * $c;
        
        // Valid if within specified radius
        return $distance <= $radius ? 'valid' : 'invalid';
    }
}