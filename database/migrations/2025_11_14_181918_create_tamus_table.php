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
        Schema::create('tamus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kunjungan')->unique();
            $table->string('nama_lengkap');
            $table->string('nomor_hp', 20);
            $table->string('instansi');
            $table->dateTime('tanggal_kunjungan')->nullable();
            $table->foreignId('kategori_kunjungan_id')->references('id')->on('kategori_kunjungans')->cascadeOnDelete();
            $table->foreignid('penanggung_jawab_id')->nullable()->references('id')->on('penanggung_jawabs')->nullOnDelete();
            $table->text('catatan');
            $table->string('status');
            $table->dateTime('waktu_temu')->nullable();
            $table->text('alasan_batal')->nullable();
            $table->boolean('is_rescheduled')->default(false);
            $table->integer('reschedule_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tamus');
    }
};
