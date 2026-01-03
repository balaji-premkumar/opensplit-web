<?php

namespace App\DTOs;

/**
 * Data Transfer Object for creating expenses.
 * 
 * DTOs ensure type safety and provide a clean contract
 * between the controller and service layers.
 */
readonly class ExpenseDTO
{
    /**
     * @param string $description Expense description
     * @param string $amount Total expense amount (string for precision)
     * @param int $paidBy User ID who paid
     * @param int $groupId Group ID for the expense
     * @param array<int, array{user_id: int, paid_share: string, owed_share: string}> $splits
     * @param string|null $currencyCode Currency code (default: USD)
     * @param string|null $expenseDate Date of expense (Y-m-d)
     * @param string|null $notes Additional notes
     */
    public function __construct(
        public string $description,
        public string $amount,
        public int $paidBy,
        public int $groupId,
        public array $splits,
        public ?string $currencyCode = 'USD',
        public ?string $expenseDate = null,
        public ?string $notes = null,
    ) {}

    /**
     * Create from array (useful for creating from validated request data).
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            description: $data['description'],
            amount: (string) $data['amount'],
            paidBy: (int) $data['paid_by'],
            groupId: (int) $data['group_id'],
            splits: $data['splits'],
            currencyCode: $data['currency_code'] ?? 'USD',
            expenseDate: $data['expense_date'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
