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
        Schema::create('inventory', function (Blueprint $table) {
            $table->id('Item_ID');
            $table->string('Item_Name', 100);
            $table->integer('Quantity')->default(0);
            $table->integer('Min_Level')->default(10);
            $table->date('Expiry_Date')->nullable();
            $table->string('Supplier_Info', 200)->nullable();
            $table->string('Category', 50)->nullable();
            $table->date('Last_Restock_Date')->nullable();
            $table->boolean('Needs_Restock')->default(false);
            $table->string('Storage_Location', 50)->nullable();
            $table->decimal('Unit_Price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
