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
        Schema::create('results', function (Blueprint $table) {

            // Primary Key
            $table->id('Result_ID');

            // Foreign Keys
            $table->unsignedBigInteger('User_ID');
            $table->unsignedBigInteger('Sample_ID');

            $table->foreign('User_ID')
                  ->references('User_ID')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('Sample_ID')
                  ->references('Sample_ID')
                  ->on('samples')
                  ->onDelete('cascade');

            // Result Data
            $table->text('Result_Value');
            $table->string('Unit', 50)->nullable();
            $table->string('Normal_Range', 100);
            $table->string('Test_Name', 100);
            $table->string('Method_Used', 100);
            $table->text('Interpretation')->nullable();
            $table->enum('Status', ['Pending', 'Verified', 'Approved']);

            // Dates
            $table->timestamp('Result_Date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
