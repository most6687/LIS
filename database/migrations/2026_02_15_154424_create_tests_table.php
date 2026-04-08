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
        Schema::create('tests', function (Blueprint $table) {
            $table->id('Order_ID');
            $table->foreignId('Patient_ID')->constrained('patients', 'Patient_ID')->onDelete('cascade');
            $table->foreignId('Doctor_ID')->nullable()->constrained('users', 'User_ID')->nullOnDelete();
            $table->foreignId('User_ID')->constrained('users', 'User_ID');
            $table->timestamp('Order_Date')->useCurrent();
            $table->enum('Priority', ['Urgent', 'Routine'])->default('Routine');
            $table->enum('Status', ['Pending', 'Collected', 'Processing', 'Completed'])->default('Pending');
            $table->decimal('Total_Amount', 10, 2)->default(0);
            $table->text('Requested_Tests')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
