<?php

namespace App\Repositories\Contracts;

use App\Models\Expense;
use Illuminate\Support\Collection;

/**
 * Interface for Expense data access operations.
 * 
 * The repository pattern separates data access logic from business logic,
 * making the codebase more testable and maintainable.
 */
interface ExpenseRepositoryInterface
{
    /**
     * Create a new expense.
     *
     * @param array<string, mixed> $data
     * @return Expense
     */
    public function create(array $data): Expense;

    /**
     * Find an expense by ID.
     *
     * @param int $id
     * @return Expense|null
     */
    public function find(int $id): ?Expense;

    /**
     * Find an expense by ID or throw exception.
     *
     * @param int $id
     * @return Expense
     */
    public function findOrFail(int $id): Expense;

    /**
     * Create expense splits in bulk.
     *
     * @param int $expenseId
     * @param array<int, array<string, mixed>> $splits
     * @return void
     */
    public function createSplits(int $expenseId, array $splits): void;

    /**
     * Get all expenses for a group.
     *
     * @param int $groupId
     * @return Collection<int, Expense>
     */
    public function getByGroup(int $groupId): Collection;

    /**
     * Delete an expense.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
