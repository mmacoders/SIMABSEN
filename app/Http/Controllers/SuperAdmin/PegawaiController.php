<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Bidang;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        // Build query for users
        $usersQuery = User::where('role', 'user')->with('bidang');
        
        // Apply search filter
        if ($request->search) {
            $search = $request->search;
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhereHas('bidang', function ($q) use ($search) {
                          $q->where('nama_bidang', 'like', "%{$search}%");
                      });
            });
        }
        
        // Apply status filter
        if ($request->status) {
            $usersQuery->where('status', $request->status);
        }
        
        // Apply bidang filter
        if ($request->bidang_id) {
            $usersQuery->where('bidang_id', $request->bidang_id);
        }
        
        // Get all bidangs for filter dropdown
        $bidangs = Bidang::all();
        
        // Paginate results
        $users = $usersQuery->orderBy('name')->paginate(12)->withQueryString();
        
        return Inertia::render('SuperAdmin/KelolaPegawai', [
            'users' => $users,
            'bidangs' => $bidangs,
            'filters' => $request->only(['search', 'status', 'bidang_id']),
        ]);
    }
    
    public function show(User $user)
    {
        // Ensure user is a pegawai (user role)
        if ($user->role !== 'user') {
            abort(404);
        }
        
        // Load user with bidang
        $user->load('bidang');
        
        // Get attendance statistics for current month
        $currentMonth = now()->format('Y-m');
        $attendances = $user->absensis()
            ->where('tanggal', 'like', "{$currentMonth}%")
            ->get();
        
        // Calculate statistics
        $stats = [
            'hadir' => 0,
            'izin' => 0,
            'terlambat' => 0,
        ];
        
        foreach ($attendances as $attendance) {
            switch ($attendance->status) {
                case 'hadir':
                case 'Hadir':
                    $stats['hadir']++;
                    break;
                case 'izin':
                case 'Izin':
                case 'Izin (Valid)':
                    $stats['izin']++;
                    break;
                case 'terlambat':
                case 'Terlambat':
                    $stats['terlambat']++;
                    break;
            }
        }
        
        // Get all bidangs for edit form
        $bidangs = Bidang::all();
        
        return Inertia::render('SuperAdmin/DetailPegawai', [
            'pegawai' => $user,
            'stats' => $stats,
            'attendances' => $attendances,
            'bidangs' => $bidangs,
        ]);
    }
    
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'no_hp' => 'nullable|string|max:20',
            'pangkat' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:50',
            'nrp' => 'nullable|string|max:50',
            'bidang_id' => 'required|exists:bidangs,id',
            'jabatan' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
            'profile_pict' => 'nullable|string|max:255'
        ]);
        
        // Handle NIP/NRP logic - only one should be filled
        $nip = $request->nip;
        $nrp = $request->nrp;
        
        // If both are provided, we'll use NIP and clear NRP
        if ($nip && $nrp) {
            $nrp = null;
        }
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'pangkat' => $request->pangkat,
            'nip' => $nip,
            'nrp' => $nrp,
            'bidang_id' => $request->bidang_id,
            'jabatan' => $request->jabatan,
            'status' => $request->status,
            'profile_pict' => $request->profile_pict,
        ]);
        
        return redirect()->back()->with('success', 'Data pegawai berhasil diperbarui.');
    }
    
    public function toggleStatus(User $user)
    {
        // Ensure user is a pegawai (user role)
        if ($user->role !== 'user') {
            abort(404);
        }
        
        $user->update([
            'status' => $user->status === 'aktif' ? 'nonaktif' : 'aktif',
        ]);
        
        return redirect()->back()->with('success', 'Status pegawai berhasil diubah.');
    }
    
    public function resetPassword(User $user)
    {
        // Ensure user is a pegawai (user role)
        if ($user->role !== 'user') {
            abort(404);
        }
        
        $user->update([
            'password' => Hash::make('password'), // Default password
        ]);
        
        return redirect()->back()->with('success', 'Password pegawai berhasil direset.');
    }
}