<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_doctors_table.php

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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id('Doctor_ID');
            $table->string('Full_Name', 100);
            $table->string('Specialty', 100)->nullable();
            $table->string('License_Number', 50)->unique();
            $table->string('Phone', 20)->nullable();
            $table->string('Email', 100)->unique()->nullable();
            $table->string('Clinic_Address', 200)->nullable();
            $table->boolean('Is_External')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
