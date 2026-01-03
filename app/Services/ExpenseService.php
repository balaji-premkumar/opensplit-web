<?php

namespace App\Services;

use App\DTOs\ExpenseDTO;
use App\Exceptions\InvalidSplitException;
use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ExpenseService - Business Logic Layer
 * 
 * This service contains all business logic for expense management.
 * It uses the repository for data access and enforces business rules.
 */
class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $repository
    ) {}

    /**
     * Add a new expense with splits.
     * 
     * Business Rules:
     * 1. Sum of owed_shares MUST equal the total expense amount
     * 2. All database operations are wrapped in a transaction
     *
     * @param ExpenseDTO $dto
     * @return Expense
     * @throws InvalidSplitException If splits don't sum to total
     */
    public function addExpense(ExpenseDTO $dto): Expense
    {
        // Validate: Sum of owed_shares must equal total amount
        $this->validateSplitsSum($dto);

        // Wrap all DB operations in a transaction
        return DB::transaction(function () use ($dto) {
            // Create the expense
            $expense = $this->repository->create([
                'description' => $dto->description,
                'amount' => $dto->amount,
                'currency_code' => $dto->currencyCode,
                'paid_by' => $dto->paidBy,
                'group_id' => $dto->groupId,
                'expense_date' => $dto->expenseDate,
                'notes' => $dto->notes,
            ]);

            // Create the splits
            $this->repository->createSplits($expense->id, $dto->splits);

            // Load relationships for response
            return $expense->load(['payer', 'splits.user', 'group']);
        });
    }

    /**
     * Get an expense by ID.
     *
     * @param int $id
     * @return Expense|null
     */
    public function getExpense(int $id): ?Expense
    {
        $expense = $this->repository->find($id);
        
        return $expense?->load(['payer', 'splits.user', 'group']);
    }

    /**
     * Get all expenses for a group.
     *
     * @param int $groupId
     * @return Collection<int, Expense>
     */
    public function getExpensesByGroup(int $groupId): Collection
    {
        return $this->repository->getByGroup($groupId);
    }

    /**
     * Delete an expense.
     *
     * @param int $id
     * @return bool
     */
    public function deleteExpense(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Validate that the sum of owed_shares equals the total amount.
     *
     * @param ExpenseDTO $dto
     * @throws InvalidSplitException
     */
    private function validateSplitsSum(ExpenseDTO $dto): void
    {
        $totalOwed = '0.00';
        
        foreach ($dto->splits as $split) {
            $totalOwed = bcadd($totalOwed, $split['owed_share'], 2);
        }

        // Compare with 2 decimal precision
        if (bccomp($totalOwed, $dto->amount, 2) !== 0) {
            throw InvalidSplitException::splitsSumMismatch($dto->amount, $totalOwed);
        }
    }
}
