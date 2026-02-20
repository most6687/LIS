<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_patient', function (Blueprint $table) {
            $table->unsignedBigInteger('Doctor_ID');
            $table->unsignedBigInteger('Patient_ID');
            $table->timestamps();

            $table->primary(['Doctor_ID', 'Patient_ID']);

            $table->foreign('Doctor_ID')
                  ->references('Doctor_ID')
                  ->on('doctors')
                  ->onDelete('cascade');

            $table->foreign('Patient_ID')
                  ->references('Patient_ID')
                  ->on('patients')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_patient');
    }
};
