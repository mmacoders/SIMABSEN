<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('izins', function (Blueprint $table) {
            // Check if tanggal_selesai column exists before adding it
            if (!Schema::hasColumn('izins', 'tanggal_selesai')) {
                $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
            }
            
            // If tanggal column exists (from original migration), we might want to remove it
            // But for now, let's just work with what we have
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izins', function (Blueprint $table) {
            if (Schema::hasColumn('izins', 'tanggal_selesai')) {
                $table->dropColumn('tanggal_selesai');
            }
        });
    }
};