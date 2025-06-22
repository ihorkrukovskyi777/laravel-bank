<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('name');
            $table->string('last_name')->after('first_name');
            $table->string('phone', 20)->unique()->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_recovery_codes');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('last_login_at');
            
            // Видаляємо поле name, оскільки тепер у нас є first_name та last_name
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->dropColumn([
                'first_name',
                'last_name', 
                'phone',
                'phone_verified_at',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_enabled',
                'last_login_at',
                'status'
            ]);
        });
    }
};
