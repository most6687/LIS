<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id('Sample_ID');
            $table->foreignId('Order_ID')->constrained('tests', 'Order_ID')->onDelete('cascade');
            $table->foreignId('User_ID')->constrained('users', 'User_ID');
            $table->string('Sample_Type', 50);
            $table->timestamp('Collection_Date')->useCurrent();
            $table->enum('Status', ['Pending', 'Collected', 'In_Analysis', 'Completed'])->default('Pending');
            $table->string('Storage_Location', 50)->nullable();
            $table->date('Expiration_Date')->nullable();
            $table->string('Container_Type', 20)->nullable();
            $table->string('Volume', 20)->nullable();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
