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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_terbit', 4);
            $table->string('isbn', 50);
            $table->string('kategori', 50);
            $table->string('title', 255);
            $table->string('creators', 255)->nullable();
            $table->string('tempat_terbit', 100);
            $table->string('penerbit', 255);
            $table->string('page', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
