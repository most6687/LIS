<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('role_permissions', function (Blueprint $table) {

        $table->id();

        $table->unsignedBigInteger('Role_ID');
        $table->unsignedBigInteger('Permission_ID');

        $table->foreign('Role_ID')->references('Role_ID')->on('roles')->onDelete('cascade');
        $table->foreign('Permission_ID')->references('Permission_ID')->on('permissions')->onDelete('cascade');

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
