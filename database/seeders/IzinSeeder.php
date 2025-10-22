<?php

namespace Database\Seeders;

use App\Models\Izin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IzinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some sample users
        $users = User::where('role', 'user')->take(5)->get();
        
        foreach ($users as $index => $user) {
            // Create full leave for some users
            if ($index % 2 == 0) {
                Izin::create([
                    'user_id' => $user->id,
                    'tanggal_mulai' => date('Y-m-d'),
                    'tanggal_selesai' => date('Y-m-d'),
                    'jenis_izin' => 'penuh',
                    'keterangan' => 'Sakit demam',
                    'disetujui_oleh' => 'Admin',
                ]);
            } else {
                // Create partial leave for other users
                Izin::create([
                    'user_id' => $user->id,
                    'tanggal_mulai' => date('Y-m-d'),
                    'tanggal_selesai' => date('Y-m-d'),
                    'jenis_izin' => 'parsial',
                    'keterangan' => 'Mengurus dokumen penting',
                    'disetujui_oleh' => 'Admin',
                ]);
            }
        }
    }
}