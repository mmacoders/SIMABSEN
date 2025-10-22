<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'waktu_masuk',
        'waktu_keluar',
        'lat_masuk',
        'lng_masuk',
        'lat_keluar',
        'lng_keluar',
        'status_lokasi_masuk',
        'status_lokasi_keluar',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_masuk' => 'datetime:H:i:s',
        'waktu_keluar' => 'datetime:H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}