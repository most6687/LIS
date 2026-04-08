<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('Report_ID');
            $table->foreignId('Patient_ID')->constrained('patients', 'Patient_ID')->onDelete('cascade');
            $table->foreignId('Doctor_ID')->nullable()->constrained('users', 'User_ID')->nullOnDelete();
            $table->foreignId('User_ID')->constrained('users', 'User_ID');
            $table->foreignId('Order_ID')->constrained('tests', 'Order_ID')->onDelete('cascade'); // <===== مضاف
            $table->timestamp('Generated_Date')->useCurrent();
            $table->enum('Type', ['Preliminary', 'Final'])->default('Final');
            $table->enum('Report_Status', ['Draft', 'Finalized'])->default('Draft');
            $table->enum('Report_Format', ['Digital', 'Pdf'])->default('Digital');
            $table->string('File_Path', 255)->nullable();
            $table->text('Notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
