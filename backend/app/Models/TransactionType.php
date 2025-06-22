<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'fee_percentage',
        'fee_fixed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fee_percentage' => 'decimal:4',
        'fee_fixed' => 'decimal:2',
    ];

    /**
     * Get the transactions of this type.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if this transaction type is a transfer.
     */
    public function isTransfer(): bool
    {
        return $this->name === 'transfer';
    }

    /**
     * Check if this transaction type is a payment.
     */
    public function isPayment(): bool
    {
        return $this->name === 'payment';
    }

    /**
     * Check if this transaction type is a deposit.
     */
    public function isDeposit(): bool
    {
        return $this->name === 'deposit';
    }

    /**
     * Check if this transaction type is a withdrawal.
     */
    public function isWithdrawal(): bool
    {
        return $this->name === 'withdrawal';
    }

    /**
     * Calculate fee for a given amount.
     */
    public function calculateFee(float $amount): float
    {
        $percentageFee = $amount * $this->fee_percentage;
        return $percentageFee + $this->fee_fixed;
    }

    /**
     * Get the fee percentage as percentage.
     */
    public function getFeePercentageAttribute(): float
    {
        return $this->attributes['fee_percentage'] * 100;
    }
}
