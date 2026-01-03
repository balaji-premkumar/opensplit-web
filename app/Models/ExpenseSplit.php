<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExpenseSplit - Tracks how an expense is divided among users.
 * 
 * Balance Logic:
 * - paid_share: Amount this user contributed to paying the expense
 * - owed_share: Amount this user owes from the expense
 * - Net Balance = paid_share - owed_share
 *   - Positive: User is owed money
 *   - Negative: User owes money
 */
class ExpenseSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'user_id',
        'paid_share',
        'owed_share',
    ];

    protected $casts = [
        'paid_share' => 'decimal:2',
        'owed_share' => 'decimal:2',
    ];

    /**
     * Get the expense this split belongs to.
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the user this split is for.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the net balance for this split.
     * Positive = user is owed money, Negative = user owes money
     */
    public function getNetBalanceAttribute(): string
    {
        return bcsub($this->paid_share, $this->owed_share, 2);
    }
}
