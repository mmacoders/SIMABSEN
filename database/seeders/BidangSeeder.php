<?php

namespace Database\Seeders;

use App\Models\Bidang;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bidangs = [
            ['nama_bidang' => 'Jarkom', 'description' => 'Bidang Jaringan Komunikasi'],
            ['nama_bidang' => 'Yankom', 'description' => 'Bidang Pelayanan Komunikasi'],
            ['nama_bidang' => 'Harkam', 'description' => 'Bidang Pengamanan dan Kamtibmas'],
            ['nama_bidang' => 'Renmin', 'description' => 'Bidang Perencanaan dan Evaluasi'],
        ];

        foreach ($bidangs as $bidang) {
            Bidang::firstOrCreate(
                ['nama_bidang' => $bidang['nama_bidang']],
                $bidang
            );
        }
    }
}