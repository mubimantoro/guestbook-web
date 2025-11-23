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
            $table->string('nama');
            $table->string('nomor_hp', 20);
            $table->string('instansi');
            $table->dateTime('tanggal_kunjungan')->nullable();
            $table->foreignId('kategori_kunjungan')->references('id')->on('kategori_kunjungans')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->string('status');
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
