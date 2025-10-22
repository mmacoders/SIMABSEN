<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default system settings
        SystemSetting::firstOrCreate(
            [], // No specific conditions since we only want one record
            [
                'location_latitude' => 0.52400050,
                'location_longitude' => 123.06047523,
                'location_radius' => 100,
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '16:00:00',
            ]
        );
    }
}