<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_type_id',
        'account_number',
        'iban',
        'currency',
        'balance',
        'available_balance',
        'status',
        'opened_at',
        'closed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the account.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account type.
     */
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    /**
     * Get the transactions where this account is the sender.
     */
    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    /**
     * Get the transactions where this account is the receiver.
     */
    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    /**
     * Get all transactions for this account.
     */
    public function transactions()
    {
        return Transaction::where('from_account_id', $this->id)
            ->orWhere('to_account_id', $this->id)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if account is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Check if account is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if account has sufficient balance for a transaction.
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }

    /**
     * Get the formatted balance.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2) . ' ' . $this->currency;
    }

    /**
     * Get the formatted available balance.
     */
    public function getFormattedAvailableBalanceAttribute(): string
    {
        return number_format($this->available_balance, 2) . ' ' . $this->currency;
    }
}
