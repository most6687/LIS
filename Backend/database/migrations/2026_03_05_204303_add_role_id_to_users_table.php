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
    Schema::table('users', function (Blueprint $table) {
        $table->unsignedBigInteger('Role_ID')->nullable();

        $table->foreign('Role_ID')
              ->references('Role_ID')
              ->on('roles');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['Role_ID']);
        $table->dropColumn('Role_ID');
    });
}
};
