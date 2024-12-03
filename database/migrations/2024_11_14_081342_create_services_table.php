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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ketua', 255);
            $table->string('nidn_ketua', 100);
            $table->string('afiliasi_ketua', 255);
            $table->string('kd_pt_ketua', 50);
            $table->string('judul', 255);
            $table->string('nama_singkat_skema', 50);
            $table->string('thn_pertama_usulan', 4);
            $table->string('thn_usulan_kegiatan', 4);
            $table->string('thn_pelaksanaan_kegiatan', 4);
            $table->string('lama_kegiatan', 4);
            $table->string('bidang_fokus', 100);
            $table->string('nama_skema', 100);
            $table->string('status_usulan', 50);
            $table->decimal('dana_disetujui', 12, 2);
            $table->string('afiliasi_sinta_id', 4);
            $table->string('nama_institusi_penerima_dana', 255);
            $table->string('target_tkt', 20);
            $table->string('nama_program_hibah', 100);
            $table->string('kategori_sumber_dana', 50);
            $table->string('negara_sumber_dana', 50);
            $table->string('sumber_dana', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
