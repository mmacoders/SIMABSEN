<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index()
    {
        // Get all admins with pagination
        $admins = User::where('role', 'admin')->with('bidang')->paginate(10);
        
        // Get all bidangs
        $bidangs = Bidang::all();
        
        return Inertia::render('SuperAdmin/KelolaAdmin', [
            'admins' => $admins,
            'bidangs' => $bidangs,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'bidang_id' => 'required|exists:bidangs,id',
        ]);
        
        // Create admin user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'bidang_id' => $request->bidang_id,
        ]);
        
        return redirect()->back()->with('success', 'Admin berhasil ditambahkan.');
    }
    
    public function update(Request $request, $id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        // Ensure we're updating an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'bidang_id' => 'required|exists:bidangs,id',
            'status' => 'required|in:active,inactive',
        ]);
        
        // Update admin
        $admin->update($request->only('name', 'email', 'bidang_id', 'status'));
        
        return redirect()->back()->with('success', 'Admin berhasil diperbarui.');
    }
    
    public function destroy($id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        // Ensure we're deleting an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        $admin->delete();
        
        return redirect()->back()->with('success', 'Admin berhasil dihapus.');
    }
    
    public function toggleStatus($id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        // Ensure we're toggling an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        // Toggle status
        $admin->update([
            'status' => $admin->status === 'active' ? 'inactive' : 'active'
        ]);
        
        $statusText = $admin->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->back()->with('success', "Admin berhasil {$statusText}.");
    }
    
    public function transfer(Request $request, $id)
    {
        // Find the admin user
        $admin = User::findOrFail($id);
        
        $request->validate([
            'bidang_id' => 'required|exists:bidangs,id',
        ]);
        
        // Ensure we're transferring an admin
        if ($admin->role !== 'admin') {
            abort(403);
        }
        
        // Update bidang
        $admin->update([
            'bidang_id' => $request->bidang_id
        ]);
        
        $bidang = Bidang::find($request->bidang_id);
        
        return redirect()->back()->with('success', "Admin berhasil dipindahkan ke bidang {$bidang->nama_bidang}.");
    }
}