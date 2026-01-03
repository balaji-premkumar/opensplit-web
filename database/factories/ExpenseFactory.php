<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(4),
            'amount' => fake()->randomFloat(2, 10, 500),
            'currency_code' => 'USD',
            'paid_by' => User::factory(),
            'group_id' => Group::factory(),
            'expense_date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
