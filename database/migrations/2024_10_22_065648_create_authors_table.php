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
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('sinta_id', 20);
            $table->string('nidn', 20)->unique();
            $table->string('name', 100);
            $table->string('affiliation', 100);
            $table->foreignId('study_program_id')->nullable()->constrained('study_programs')->onDelete('cascade');
            $table->string('last_education', 20)->nullable();
            $table->string('functional_position', 50)->nullable();
            $table->string('title_prefix', 50)->nullable();
            $table->string('title_suffix', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
