<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $admin = auth()->user();
        
        // Get leave requests for users in the same bidang
        $izinQuery = Izin::whereHas('user', function ($query) use ($admin) {
            $query->where('bidang_id', $admin->bidang_id);
        })->with('user');
        
        // Apply date filters if provided
        if ($request->filled('start_date')) {
            $izinQuery->where('tanggal_mulai', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $izinQuery->where('tanggal_selesai', '<=', $request->end_date);
        }
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $izinQuery->whereHas('user', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            });
        }
        
        $izins = $izinQuery->orderBy('created_at', 'desc')->paginate(10);
        
        return Inertia::render('Admin/Izin', [
            'izins' => $izins,
            'filters' => $request->only(['start_date', 'end_date', 'search']),
        ]);
    }
    
    public function update(Request $request, Izin $izin)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'catatan' => 'nullable|string|max:500',
        ]);
        
        // Check if the admin can update this izin (must be in the same bidang)
        if ($izin->user->bidang_id !== auth()->user()->bidang_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengubah permintaan izin ini.');
        }
        
        $izin->update([
            'status' => $request->status,
            'disetujui_oleh' => auth()->user()->name,
            'catatan' => $request->catatan,
        ]);
        
        return redirect()->back()->with('success', 'Status permintaan izin berhasil diperbarui.');
    }
}