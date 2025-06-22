<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
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
        'min_balance',
        'monthly_fee',
        'interest_rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_balance' => 'decimal:2',
        'monthly_fee' => 'decimal:2',
        'interest_rate' => 'decimal:4',
    ];

    /**
     * Get the accounts of this type.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Check if this account type is a savings account.
     */
    public function isSavings(): bool
    {
        return $this->name === 'savings';
    }

    /**
     * Check if this account type is a credit account.
     */
    public function isCredit(): bool
    {
        return $this->name === 'credit';
    }

    /**
     * Check if this account type is a current account.
     */
    public function isCurrent(): bool
    {
        return $this->name === 'current';
    }

    /**
     * Get the annual interest rate as percentage.
     */
    public function getAnnualInterestRateAttribute(): float
    {
        return $this->interest_rate * 100;
    }
}
