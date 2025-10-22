<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BidangController extends Controller
{
    public function index()
    {
        // Get all bidangs with counts
        $bidangs = Bidang::withCount(['users as admin_count' => function ($query) {
                        $query->where('role', 'admin');
                    }])
                    ->withCount(['users as user_count' => function ($query) {
                        $query->where('role', 'user');
                    }])
                    ->get();
        
        return Inertia::render('SuperAdmin/BidangManagement', [
            'bidangs' => $bidangs,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama_bidang' => 'required|string|max:255|unique:bidangs',
            'description' => 'nullable|string',
        ]);
        
        // Create bidang
        Bidang::create($request->only('nama_bidang', 'description'));
        
        return redirect()->back()->with('success', 'Bidang berhasil ditambahkan.');
    }
    
    public function update(Request $request, Bidang $bidang)
    {
        $request->validate([
            'nama_bidang' => 'required|string|max:255|unique:bidangs,nama_bidang,' . $bidang->id,
            'description' => 'nullable|string',
        ]);
        
        // Update bidang
        $bidang->update($request->only('nama_bidang', 'description'));
        
        return redirect()->back()->with('success', 'Bidang berhasil diperbarui.');
    }
    
    public function destroy(Bidang $bidang)
    {
        // Check if bidang has users
        if ($bidang->users()->count() > 0) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus bidang yang masih memiliki pengguna.');
        }
        
        $bidang->delete();
        
        return redirect()->back()->with('success', 'Bidang berhasil dihapus.');
    }
}