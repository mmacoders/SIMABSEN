<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all bidangs
        $bidangs = Bidang::all();
        
        // Create superadmin
        User::firstOrCreate(
            ['email' => 'superadmin@polda.go.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'status' => 'active',
            ]
        );
        
        // Create admins for each bidang
        foreach ($bidangs as $index => $bidang) {
            User::firstOrCreate(
                ['email' => 'admin' . ($index + 1) . '@polda.go.id'],
                [
                    'name' => 'Admin ' . $bidang->nama_bidang,
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'bidang_id' => $bidang->id,
                    'status' => 'active',
                ]
            );
        }
        
        // Create sample users for each bidang
        foreach ($bidangs as $bidang) {
            for ($i = 1; $i <= 5; $i++) {
                User::firstOrCreate(
                    ['email' => strtolower($bidang->nama_bidang) . $i . '@polda.go.id'],
                    [
                        'name' => 'Pegawai ' . $bidang->nama_bidang . ' ' . $i,
                        'password' => Hash::make('password'),
                        'role' => 'user',
                        'bidang_id' => $bidang->id,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}