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
        Schema::create('riwayat_reschedule_kunjungans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tamu_id')->constrained('tamus')->cascadeOnDelete();
            $table->dateTime('jadwal_lama')->nullable();
            $table->dateTime('jadwal_baru');
            $table->text('alasan_reschedule');
            $table->foreignId('reschedule_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 50);
            $table->boolean('whatsapp_sent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_reschedule_kunjungans');
    }
};
