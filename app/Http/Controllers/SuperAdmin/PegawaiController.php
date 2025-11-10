<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $usersQuery = User::where('role', 'user');
        
        // Apply search filter
        if ($request->search) {
            $search = $request->search;
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->status) {
            $usersQuery->where('status', $request->status);
        }
        
        // Paginate results
        $users = $usersQuery->orderBy('name')->paginate(12)->withQueryString();
        
        return Inertia::render('SuperAdmin/KelolaPegawai', [
            'users' => $users,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
    
    public function show(User $user)
    {
        // Ensure user is a pegawai (user role)
        if ($user->role !== 'user') {
            abort(404);
        }
        
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
        
        return Inertia::render('SuperAdmin/DetailPegawai', [
            'pegawai' => $user,
            'stats' => $stats,
            'attendances' => $attendances,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'no_hp' => 'nullable|string|max:20',
            'pangkat' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:50',
            'nrp' => 'nullable|string|max:50',
            'jabatan' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
        ]);
        
        // Handle NIP/NRP logic - only one should be filled
        $nip = $request->nip;
        $nrp = $request->nrp;
        
        // If both are provided, we'll use NIP and clear NRP
        if ($nip && $nrp) {
            $nrp = null;
        }
        
        // Create user with default password
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password'), // Default password
            'no_hp' => $request->no_hp,
            'pangkat' => $request->pangkat,
            'nip' => $nip,
            'nrp' => $nrp,
            'jabatan' => $request->jabatan,
            'status' => $request->status,
            'role' => 'user', // Pegawai role
        ]);
        
        return redirect()->back()->with('success', 'Pegawai berhasil ditambahkan.');
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
            'jabatan' => $request->jabatan,
            'status' => $request->status,
            'profile_pict' => $request->profile_pict,
        ]);
        
        return redirect()->back()->with('success', 'Data pegawai berhasil diperbarui.');
    }
    
    public function destroy(User $user)
    {
        // Ensure user is a pegawai (user role)
        if ($user->role !== 'user') {
            abort(404);
        }
        
        $user->delete();
        
        return redirect()->back()->with('success', 'Pegawai berhasil dihapus.');
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