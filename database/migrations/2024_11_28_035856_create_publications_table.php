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
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('accreditation', 50)->nullable();
            $table->string('identifier', 50)->nullable();
            $table->string('quartile', 50)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('journal', 255)->nullable();
            $table->string('publication_name', 255)->nullable();
            $table->text('creators')->nullable();
            $table->year('year');
            $table->string('citation', 10);
            $table->enum('category', ['google', 'scopus'])->default('google');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
