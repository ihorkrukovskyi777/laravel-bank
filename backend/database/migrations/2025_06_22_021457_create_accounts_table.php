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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_type_id')->constrained();
            $table->string('account_number', 34)->unique();
            $table->string('iban', 34)->unique()->nullable();
            $table->string('currency', 3)->default('UAH');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('available_balance', 15, 2)->default(0.00);
            $table->enum('status', ['active', 'blocked', 'closed', 'pending'])->default('pending');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['account_number']);
            $table->index(['iban']);
            $table->index(['status']);
            $table->index(['currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
