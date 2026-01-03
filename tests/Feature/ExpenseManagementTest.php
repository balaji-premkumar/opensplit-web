<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Expense Management Feature Tests
 * 
 * BDD-style tests for the expense management functionality.
 * Using Given/When/Then pattern for clarity.
 */
class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Scenario A: User adds an expense with equal splits (201 OK)
     * 
     * Given: A group with 3 members
     * When: User adds a $300 expense split equally ($100 each)
     * Then: The expense is created successfully with 201 status
     */
    public function test_user_can_add_expense_with_valid_equal_splits(): void
    {
        // Given: A group with 3 members (authenticated as creator)
        $creator = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        
        $group = Group::factory()->create(['created_by' => $creator->id]);
        $group->members()->attach([$creator->id, $member1->id, $member2->id]);

        // Authenticate as the creator
        Sanctum::actingAs($creator);

        // When: User adds a $300 expense split equally
        $response = $this->postJson('/api/expenses', [
            'description' => 'Dinner at restaurant',
            'amount' => '300.00',
            'group_id' => $group->id,
            'paid_by' => $creator->id,
            'currency_code' => 'USD',
            'expense_date' => '2026-01-03',
            'splits' => [
                [
                    'user_id' => $creator->id,
                    'paid_share' => '300.00',  // Creator paid the full amount
                    'owed_share' => '100.00',  // But only owes 1/3
                ],
                [
                    'user_id' => $member1->id,
                    'paid_share' => '0.00',
                    'owed_share' => '100.00',
                ],
                [
                    'user_id' => $member2->id,
                    'paid_share' => '0.00',
                    'owed_share' => '100.00',
                ],
            ],
        ]);

        // Then: The expense is created successfully
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'description',
                    'amount',
                    'currency_code',
                    'paid_by',
                    'group_id',
                    'splits' => [
                        '*' => ['id', 'user_id', 'paid_share', 'owed_share'],
                    ],
                ],
            ])
            ->assertJson([
                'message' => 'Expense created successfully',
                'data' => [
                    'description' => 'Dinner at restaurant',
                    'amount' => '300.00',
                ],
            ]);

        // Verify database state
        $this->assertDatabaseHas('expenses', [
            'description' => 'Dinner at restaurant',
            'amount' => '300.00',
            'group_id' => $group->id,
        ]);

        $this->assertDatabaseCount('expense_splits', 3);
    }

    /**
     * Scenario B: User adds expense where splits don't sum to total (422 Error)
     * 
     * Given: A group with 3 members
     * When: User adds a $300 expense but splits only sum to $250
     * Then: The request is rejected with 422 Unprocessable Entity
     */
    public function test_expense_is_rejected_when_splits_do_not_sum_to_total(): void
    {
        // Given: A group with 3 members
        $creator = User::factory()->create();
        $member1 = User::factory()->create();
        $member2 = User::factory()->create();
        
        $group = Group::factory()->create(['created_by' => $creator->id]);
        $group->members()->attach([$creator->id, $member1->id, $member2->id]);

        // Authenticate
        Sanctum::actingAs($creator);

        // When: User adds a $300 expense but splits only sum to $250
        $response = $this->postJson('/api/expenses', [
            'description' => 'Movie tickets',
            'amount' => '300.00',  // Total is $300
            'group_id' => $group->id,
            'paid_by' => $creator->id,
            'splits' => [
                [
                    'user_id' => $creator->id,
                    'paid_share' => '300.00',
                    'owed_share' => '100.00',  // $100
                ],
                [
                    'user_id' => $member1->id,
                    'paid_share' => '0.00',
                    'owed_share' => '75.00',   // $75
                ],
                [
                    'user_id' => $member2->id,
                    'paid_share' => '0.00',
                    'owed_share' => '75.00',   // $75
                ],
                // Total owed: $250 ≠ $300 (INVALID!)
            ],
        ]);

        // Then: The request is rejected with validation error
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['splits'],
            ]);

        // Verify no expense was created
        $this->assertDatabaseMissing('expenses', [
            'description' => 'Movie tickets',
        ]);

        $this->assertDatabaseCount('expense_splits', 0);
    }

    /**
     * Test validation for required fields
     */
    public function test_expense_requires_all_mandatory_fields(): void
    {
        // Authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // When: User submits an empty request
        $response = $this->postJson('/api/expenses', []);

        // Then: Validation errors are returned
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'description',
                'amount',
                'group_id',
                'paid_by',
                'splits',
            ]);
    }

    /**
     * Test that expense splits must have at least one entry
     */
    public function test_expense_requires_at_least_one_split(): void
    {
        // Given: A user and group
        $user = User::factory()->create();
        $group = Group::factory()->create(['created_by' => $user->id]);

        // Authenticate
        Sanctum::actingAs($user);

        // When: User submits expense with empty splits array
        $response = $this->postJson('/api/expenses', [
            'description' => 'Test expense',
            'amount' => '100.00',
            'group_id' => $group->id,
            'paid_by' => $user->id,
            'splits' => [],
        ]);

        // Then: Validation error for splits
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['splits']);
    }
}
