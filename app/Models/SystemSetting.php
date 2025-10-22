<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'location_latitude',
        'location_longitude',
        'location_radius',
        'jam_masuk',
        'jam_pulang',
    ];

    protected $casts = [
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
        'location_radius' => 'integer',
    ];
    
    // Remove the datetime casting for time fields as they're stored as TIME type in database
}