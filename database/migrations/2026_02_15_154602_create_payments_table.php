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
        Schema::create('payments', function (Blueprint $table) {
            $table->id('Payment_ID');
            $table->foreignId('Order_ID')->constrained('tests', 'Order_ID')->onDelete('cascade');
            $table->foreignId('Patient_ID')->constrained('patients', 'Patient_ID')->onDelete('cascade');
            $table->foreignId('User_ID')->constrained('users', 'User_ID');
            $table->timestamp('Payment_Date')->useCurrent();
            $table->decimal('Amount', 10, 2);
            $table->enum('Payment_Method', ['Cash', 'Card', 'Electronic'])->default('Cash');
            $table->enum('Payment_Status', ['Paid', 'Unpaid', 'Partial'])->default('Unpaid');
            $table->string('Transaction_ID', 50)->nullable();
            $table->string('Invoice_Number', 50)->nullable();
            $table->date('Billing_Date')->nullable();
            $table->date('Due_Date')->nullable();
            $table->text('Notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
