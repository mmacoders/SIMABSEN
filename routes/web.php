<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Test route for database write
Route::get('/test-db-write', function () {
    try {
        $attendance = \App\Models\Absensi::create([
            'user_id' => 1,
            'tanggal' => date('Y-m-d'),
            'waktu_masuk' => date('H:i:s'),
            'lat_masuk' => 0.52400050,
            'lng_masuk' => 123.06047523,
            'status_lokasi_masuk' => 'valid',
            'status' => 'hadir',
        ]);
        
        return response()->json(['success' => true, 'attendance' => $attendance]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});

// User routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [App\Http\Controllers\User\DashboardController::class, 'index'])->name('user.dashboard');
    Route::get('/user/absensi', [App\Http\Controllers\User\AbsensiController::class, 'index'])->name('user.absensi');
    Route::post('/user/absensi/check-in', [App\Http\Controllers\User\AbsensiController::class, 'checkIn'])->name('user.absensi.checkin');
    Route::post('/user/absensi/check-out', [App\Http\Controllers\User\AbsensiController::class, 'checkOut'])->name('user.absensi.checkout');
    Route::post('/user/absensi/permission', [App\Http\Controllers\User\AbsensiController::class, 'requestPermission'])->name('user.absensi.permission');
    Route::get('/user/profil', [App\Http\Controllers\User\ProfilController::class, 'index'])->name('user.profil');
    Route::match(['patch', 'post'], '/user/profil', [App\Http\Controllers\User\ProfilController::class, 'update'])->name('user.profil.update');
    Route::patch('/user/profil/password', [App\Http\Controllers\User\ProfilController::class, 'updatePassword'])->name('user.profil.password');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/pegawai', [App\Http\Controllers\Admin\PegawaiController::class, 'index'])->name('admin.pegawai');
    Route::get('/admin/pegawai/{user}', [App\Http\Controllers\Admin\PegawaiController::class, 'show'])->name('admin.pegawai.show');
    Route::patch('/admin/pegawai/{user}/toggle-status', [App\Http\Controllers\Admin\PegawaiController::class, 'toggleStatus'])->name('admin.pegawai.toggle-status');
    Route::post('/admin/pegawai/{user}/reset-password', [App\Http\Controllers\Admin\PegawaiController::class, 'resetPassword'])->name('admin.pegawai.reset-password');
    Route::get('/admin/laporan', [App\Http\Controllers\Admin\LaporanController::class, 'index'])->name('admin.laporan');
    Route::post('/admin/laporan/export', [App\Http\Controllers\Admin\LaporanController::class, 'export'])->name('admin.laporan.export');
    // Izin & Cuti routes
    Route::get('/admin/izin', [App\Http\Controllers\Admin\IzinController::class, 'index'])->name('admin.izin');
    Route::get('/admin/izin/create', [App\Http\Controllers\Admin\IzinController::class, 'create'])->name('admin.izin.create');
    Route::post('/admin/izin', [App\Http\Controllers\Admin\IzinController::class, 'store'])->name('admin.izin.store');
    Route::get('/admin/izin/{izin}', [App\Http\Controllers\Admin\IzinController::class, 'show'])->name('admin.izin.show');
    Route::get('/admin/izin/{izin}/edit', [App\Http\Controllers\Admin\IzinController::class, 'edit'])->name('admin.izin.edit');
    Route::patch('/admin/izin/{izin}', [App\Http\Controllers\Admin\IzinController::class, 'update'])->name('admin.izin.update');
    Route::delete('/admin/izin/{izin}', [App\Http\Controllers\Admin\IzinController::class, 'destroy'])->name('admin.izin.destroy');
    // Route for marking absent users
    Route::post('/admin/absensi/mark-absent', [App\Http\Controllers\User\AbsensiController::class, 'markAbsentUsers'])->name('admin.absensi.mark-absent');
    // Profil routes
    Route::get('/admin/profil', [App\Http\Controllers\Admin\ProfilController::class, 'index'])->name('admin.profil');
    Route::match(['patch', 'post'], '/admin/profil', [App\Http\Controllers\Admin\ProfilController::class, 'update'])->name('admin.profil.update');
    Route::patch('/admin/profil/password', [App\Http\Controllers\Admin\ProfilController::class, 'updatePassword'])->name('admin.profil.password');
});

// SuperAdmin routes
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/dashboard', [App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
    Route::get('/superadmin/admin', [App\Http\Controllers\SuperAdmin\AdminController::class, 'index'])->name('superadmin.admin');
    Route::post('/superadmin/admin', [App\Http\Controllers\SuperAdmin\AdminController::class, 'store'])->name('superadmin.admin.store');
    Route::patch('/superadmin/admin/{user}', [App\Http\Controllers\SuperAdmin\AdminController::class, 'update'])->name('superadmin.admin.update');
    Route::delete('/superadmin/admin/{user}', [App\Http\Controllers\SuperAdmin\AdminController::class, 'destroy'])->name('superadmin.admin.destroy');
    Route::patch('/superadmin/admin/{user}/toggle-status', [App\Http\Controllers\SuperAdmin\AdminController::class, 'toggleStatus'])->name('superadmin.admin.toggle-status');
    Route::patch('/superadmin/admin/{user}/transfer', [App\Http\Controllers\SuperAdmin\AdminController::class, 'transfer'])->name('superadmin.admin.transfer');
    
    // Pegawai Management
    Route::get('/superadmin/pegawai', [App\Http\Controllers\SuperAdmin\PegawaiController::class, 'index'])->name('superadmin.pegawai');
    Route::post('/superadmin/pegawai', [App\Http\Controllers\SuperAdmin\PegawaiController::class, 'store'])->name('superadmin.pegawai.store');
    Route::get('/superadmin/pegawai/{user}', [App\Http\Controllers\SuperAdmin\PegawaiController::class, 'show'])->name('superadmin.pegawai.show');
    Route::patch('/superadmin/pegawai/{user}', [App\Http\Controllers\SuperAdmin\PegawaiController::class, 'update'])->name('superadmin.pegawai.update');
    Route::delete('/superadmin/pegawai/{user}', [App\Http\Controllers\SuperAdmin\PegawaiController::class, 'destroy'])->name('superadmin.pegawai.destroy');
    Route::patch('/superadmin/pegawai/{user}/toggle-status', [App\Http\Controllers\SuperAdmin\PegawaiController::class, 'toggleStatus'])->name('superadmin.pegawai.toggle-status');
    Route::post('/superadmin/pegawai/{user}/reset-password', [App\Http\Controllers\SuperAdmin\PegawaiController::class, 'resetPassword'])->name('superadmin.pegawai.reset-password');
    
    // Laporan Global
    Route::get('/superadmin/laporan', [App\Http\Controllers\SuperAdmin\LaporanController::class, 'index'])->name('superadmin.laporan');
    Route::post('/superadmin/laporan/export/excel', [App\Http\Controllers\SuperAdmin\LaporanController::class, 'exportExcel'])->name('superadmin.laporan.export.excel');
    Route::post('/superadmin/laporan/export/pdf', [App\Http\Controllers\SuperAdmin\LaporanController::class, 'exportPDF'])->name('superadmin.laporan.export.pdf');
    
    // System Settings
    Route::get('/superadmin/settings', [App\Http\Controllers\SuperAdmin\SystemSettingController::class, 'index'])->name('superadmin.settings');
    Route::post('/superadmin/settings', [App\Http\Controllers\SuperAdmin\SystemSettingController::class, 'store'])->name('superadmin.settings.store');
    Route::put('/superadmin/settings', [App\Http\Controllers\SuperAdmin\SystemSettingController::class, 'update'])->name('superadmin.settings.update');
    Route::delete('/superadmin/settings/{setting}', [App\Http\Controllers\SuperAdmin\SystemSettingController::class, 'destroy'])->name('superadmin.settings.destroy');
    
    // Route for marking absent users (also available for super admin)
    Route::post('/superadmin/absensi/mark-absent', [App\Http\Controllers\User\AbsensiController::class, 'markAbsentUsers'])->name('superadmin.absensi.mark-absent');
    
    // Profil routes
    Route::get('/superadmin/profil', [App\Http\Controllers\SuperAdmin\ProfilController::class, 'index'])->name('superadmin.profil');
    Route::match(['patch', 'post'], '/superadmin/profil', [App\Http\Controllers\SuperAdmin\ProfilController::class, 'update'])->name('superadmin.profil.update');
    Route::patch('/superadmin/profil/password', [App\Http\Controllers\SuperAdmin\ProfilController::class, 'updatePassword'])->name('superadmin.profil.password');
});

// API routes for system settings - accessible by users for testing
Route::middleware(['auth'])->group(function () {
    Route::post('/api/system-settings/toggle-test-mode', [App\Http\Controllers\API\SystemSettingController::class, 'toggleTestMode'])->name('api.system-settings.toggle-test');
});

// API routes for system settings - restricted to superadmin
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/api/system-settings', [App\Http\Controllers\API\SystemSettingController::class, 'index'])->name('api.system-settings.index');
    Route::put('/api/system-settings', [App\Http\Controllers\API\SystemSettingController::class, 'update'])->name('api.system-settings.update');
    Route::post('/api/system-settings/toggle', [App\Http\Controllers\API\SystemSettingController::class, 'toggleLocationValidation'])->name('api.system-settings.toggle');
});

Route::get('/dashboard', function () {
    // Redirect based on user role
    if (auth()->check()) {
        switch (auth()->user()->role) {
            case 'user':
                return redirect()->route('user.dashboard');
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'superadmin':
                return redirect()->route('superadmin.dashboard');
            default:
                return Inertia::render('Dashboard');
        }
    }
    
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';