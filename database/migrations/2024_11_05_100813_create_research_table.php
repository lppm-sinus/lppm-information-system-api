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
        Schema::create('research', function (Blueprint $table) {
            $table->id();
            $table->string('leader_name', 100);
            $table->string('leaders_nidn', 100);
            $table->string('leaders_institution', 100);
            $table->string('title', 255);
            $table->string('scheme_short_name', 50);
            $table->string('scheme_name', 100);
            $table->decimal('approved_funds', 12, 2);
            $table->string('proposed_year', 4);
            $table->string('implementation_year', 4);
            $table->string('focus_area', 100);
            $table->string('funded_institution_name', 100);
            $table->string('grant_program', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('research');
    }
};
