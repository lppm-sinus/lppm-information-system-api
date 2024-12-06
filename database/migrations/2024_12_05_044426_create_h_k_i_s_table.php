<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hkis', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_permohonan', 4)->nullable();
            $table->string('nomor_permohonan', 50)->nullable();
            $table->string('kategori', 50)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('pemegang_paten', 255)->nullable();
            $table->string('inventor', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('nomor_publikasi', 50)->nullable();
            $table->date('tanggal_publikasi')->nullable();
            $table->date('filing_date')->nullable();
            $table->date('reception_date')->nullable();
            $table->string('nomor_registrasi', 50)->nullable();
            $table->date('tanggal_registrasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hkis');
    }
};
