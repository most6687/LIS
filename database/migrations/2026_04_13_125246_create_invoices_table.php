<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('patient_id');

            $table->unsignedBigInteger('order_id');

            $table->decimal('total_amount',10,2);

            $table->decimal('paid_amount',10,2)->default(0);

            $table->decimal('remaining_amount',10,2);

            $table->enum('status',['unpaid','partial','paid'])->default('unpaid');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
