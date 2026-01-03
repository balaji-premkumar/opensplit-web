<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExpenseSplit>
 */
class ExpenseSplitFactory extends Factory
{
    protected $model = ExpenseSplit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'user_id' => User::factory(),
            'paid_share' => '0.00',
            'owed_share' => fake()->randomFloat(2, 10, 100),
        ];
    }
}
