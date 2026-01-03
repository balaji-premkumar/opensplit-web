<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of the ExpenseRepositoryInterface.
 * 
 * This class handles all database operations for expenses.
 * Business logic should NOT be placed here - use ExpenseService instead.
 */
class ExpenseRepository implements ExpenseRepositoryInterface
{
    /**
     * Create a new expense.
     *
     * @param array<string, mixed> $data
     * @return Expense
     */
    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    /**
     * Find an expense by ID.
     *
     * @param int $id
     * @return Expense|null
     */
    public function find(int $id): ?Expense
    {
        return Expense::find($id);
    }

    /**
     * Find an expense by ID or throw exception.
     *
     * @param int $id
     * @return Expense
     */
    public function findOrFail(int $id): Expense
    {
        return Expense::findOrFail($id);
    }

    /**
     * Create expense splits in bulk.
     *
     * @param int $expenseId
     * @param array<int, array<string, mixed>> $splits
     * @return void
     */
    public function createSplits(int $expenseId, array $splits): void
    {
        foreach ($splits as $split) {
            ExpenseSplit::create([
                'expense_id' => $expenseId,
                'user_id' => $split['user_id'],
                'paid_share' => $split['paid_share'] ?? '0.00',
                'owed_share' => $split['owed_share'] ?? '0.00',
            ]);
        }
    }

    /**
     * Get all expenses for a group.
     *
     * @param int $groupId
     * @return Collection<int, Expense>
     */
    public function getByGroup(int $groupId): Collection
    {
        return Expense::where('group_id', $groupId)
            ->with(['payer', 'splits.user'])
            ->orderBy('expense_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Delete an expense.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return Expense::destroy($id) > 0;
    }
}
