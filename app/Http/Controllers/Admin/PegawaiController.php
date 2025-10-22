<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PegawaiController extends Controller
{
    public function index()
    {
        $admin = auth()->user();
        
        // Get users in the same bidang
        $users = User::where('bidang_id', $admin->bidang_id)
                    ->where('role', 'user')
                    ->with('bidang')
                    ->get();
        
        return Inertia::render('Admin/Pegawai', [
            'users' => $users,
        ]);
    }
    
    public function show(User $user)
    {
        $admin = auth()->user();
        
        // Ensure user belongs to the same bidang
        if ($user->bidang_id !== $admin->bidang_id) {
            abort(403);
        }
        
        // Load attendance records
        $attendances = $user->absensis()->orderBy('tanggal', 'desc')->get();
        
        return Inertia::render('Admin/PegawaiDetail', [
            'pegawai' => $user,
            'attendances' => $attendances,
        ]);
    }
}