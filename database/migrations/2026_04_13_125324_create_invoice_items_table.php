<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('invoice_id');

            $table->unsignedBigInteger('test_id');

            $table->decimal('price',10,2);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
