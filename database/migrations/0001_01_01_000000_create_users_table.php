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
        Schema::create('users', function (Blueprint $table) {
            $table->id('User_ID');
            $table->string('Username', 50)->unique();
            $table->string('Password', 250);
            $table->enum('Role', ['Admin', 'Receptionist', 'Technician', 'Billing', 'Inventory']);
            $table->string('Full_Name', 100);
            $table->string('Email', 100)->unique();
            $table->string('Phone', 20)->nullable();
            $table->string('Department', 30)->nullable();
            $table->date('Hire_Date')->nullable();
            $table->boolean('Is_Active')->default(true);
            $table->timestamp('Last_Login')->nullable();
            $table->text('Permissions')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();


        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
