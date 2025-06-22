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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_type_id')->constrained();
            $table->foreignId('from_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3);
            $table->decimal('fee_amount', 10, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->text('description')->nullable();
            $table->string('reference_number', 50)->unique();
            $table->string('external_reference', 100)->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['from_account_id']);
            $table->index(['to_account_id']);
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['reference_number']);
            $table->index(['external_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
