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
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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
        
        return Inertia::render('User/Absensi', [
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
        
        return response()->json([
            'attendance' => $todayAttendance
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
            'jenis_izin' => 'required|in:penuh,parsial',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // 2MB max
        ]);
        
        try {
            // Handle file upload for full leave requests
            $filePath = null;
            if ($request->hasFile('file') && $request->jenis_izin === 'penuh') {
                $file = $request->file('file');
                $fileName = time() . '_' . $user->id . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('izin_files', $fileName, 'public');
            }
            
            // Create izin record
            $izin = Izin::create([
                'user_id' => $user->id,
                'tanggal' => $request->tanggal_mulai, // For backward compatibility
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jenis_izin' => $request->jenis_izin,
                'keterangan' => $request->keterangan,
                'disetujui_oleh' => null,
                'status' => 'pending',
                'catatan' => null,
                'file_path' => $filePath, // Add file path if uploaded
            ]);
            
            Log::info('Permission request created', ['izin_id' => $izin->id, 'user_id' => $user->id]);
            
            return redirect()->back()->with('success', 'Permintaan izin berhasil diajukan dan sedang menunggu persetujuan.');
        } catch (\Exception $e) {
            Log::error('Permission request error', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengajukan izin. Silakan coba lagi.');
        }
    }
    
    // Check if user can check in
    private function canCheckIn(User $user)
    {
        // Check if user already has an attendance record for today with check-in time
        $todayAttendance = $user->absensis()->where('tanggal', date('Y-m-d'))->first();
        
        // If user already checked in, they can't check in again
        if ($todayAttendance && $todayAttendance->waktu_masuk) {
            return false;
        }
        
        // Check if user has full leave permission for today
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
            
        if ($todayIzin && $todayIzin->jenis_izin === 'penuh') {
            return false; // User with full leave can't check in
        }
        
        return true;
    }
    
    private function canCheckOut(User $user)
    {
        // Check if user has an attendance record for today with check-in time
        $todayAttendance = $user->absensis()->where('tanggal', date('Y-m-d'))->first();
        
        // If user hasn't checked in, they can't check out
        if (!$todayAttendance || !$todayAttendance->waktu_masuk) {
            return false;
        }
        
        // If user already checked out, they can't check out again
        if ($todayAttendance->waktu_keluar) {
            return false;
        }
        
        // Check if user has partial leave and already completed it
        $todayIzin = $user->izins()->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
            
        if ($todayIzin && $todayIzin->jenis_izin === 'parsial' && 
            $todayAttendance->status === 'Izin Parsial (Selesai)') {
            return false; // User with completed partial leave can't check out again
        }
        
        return true;
    }
    
    private function validateLocation($lat, $lng)
    {
        // Get system settings for office location
        $systemSettings = SystemSetting::first();
        
        if (!$systemSettings) {
            // If no system settings, consider location as valid
            return 'valid';
        }
        
        // Get office coordinates
        $officeLat = $systemSettings->location_latitude;
        $officeLng = $systemSettings->location_longitude;
        $radius = $systemSettings->location_radius;
        
        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance($lat, $lng, $officeLat, $officeLng);
        
        // Check if user is within the allowed radius
        return $distance <= $radius ? 'valid' : 'invalid';
    }
    
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);
        
        // Haversine formula
        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)));
            
        // Earth's radius in meters
        $earthRadius = 6371000;
        
        return $angle * $earthRadius;
    }
}