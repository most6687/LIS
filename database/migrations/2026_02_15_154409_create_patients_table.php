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
        Schema::create('patients', function (Blueprint $table) {
            $table->id('Patient_ID');
            $table->string('Full_Name', 100);
            $table->enum('Gender', ['M', 'F'])->nullable();
            $table->date('Date_of_Birth')->nullable();
            $table->string('Phone', 20)->nullable();
            $table->string('Address', 200)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('Insurance_Info', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
