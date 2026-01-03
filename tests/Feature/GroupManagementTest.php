<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Group Management Feature Tests
 * 
 * BDD-style tests for the group management functionality.
 * Using Given/When/Then pattern for clarity.
 */
class GroupManagementTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | CREATE GROUP TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: User creates a new group
     * 
     * Given: A user exists in the system
     * When: User creates a group with valid data
     * Then: The group is created and user is auto-added as member
     */
    public function test_user_can_create_a_group(): void
    {
        // Given: A user exists (and is authenticated)
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // When: User creates a group
        $response = $this->postJson('/api/groups', [
            'name' => 'Trip to Paris',
            'description' => 'Expenses for our Paris vacation',
            'created_by' => $user->id,
        ]);

        // Then: Group is created successfully
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'created_by',
                    'members',
                ],
            ])
            ->assertJson([
                'message' => 'Group created successfully',
                'data' => [
                    'name' => 'Trip to Paris',
                ],
            ]);

        // Verify database state
        $this->assertDatabaseHas('groups', [
            'name' => 'Trip to Paris',
            'created_by' => $user->id,
        ]);

        // Verify creator was auto-added as member
        $this->assertDatabaseHas('group_user', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Scenario: Group creation fails with missing required fields
     * 
     * Given: No data provided
     * When: User attempts to create a group
     * Then: Validation errors are returned
     */
    public function test_group_creation_requires_name_and_creator(): void
    {
        // Authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // When: User submits empty request
        $response = $this->postJson('/api/groups', []);

        // Then: Validation errors for required fields
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'created_by']);
    }

    /*
    |--------------------------------------------------------------------------
    | READ GROUP TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: User retrieves a group by ID
     * 
     * Given: A group exists
     * When: User requests the group
     * Then: Group details with members are returned
     */
    public function test_user_can_get_group_by_id(): void
    {
        // Given: A group with members exists
        $creator = User::factory()->create();
        $member = User::factory()->create();
        
        $group = Group::factory()->create(['created_by' => $creator->id]);
        $group->members()->attach([$creator->id, $member->id]);

        // Authenticate
        Sanctum::actingAs($creator);

        // When: User requests the group
        $response = $this->getJson("/api/groups/{$group->id}");

        // Then: Group details are returned
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'members' => [
                        '*' => ['id', 'name', 'email'],
                    ],
                ],
            ]);
    }

    /**
     * Scenario: User requests non-existent group
     * 
     * Given: No group with ID 9999 exists
     * When: User requests that group
     * Then: 404 Not Found is returned
     */
    public function test_returns_404_for_nonexistent_group(): void
    {
        // Authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // When: User requests non-existent group
        $response = $this->getJson('/api/groups/9999');

        // Then: 404 is returned
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Group not found',
            ]);
    }

    /**
     * Scenario: User lists all groups
     * 
     * Given: Multiple groups exist
     * When: User requests all groups
     * Then: All groups are returned
     */
    public function test_user_can_list_all_groups(): void
    {
        // Given: Multiple groups exist
        $user = User::factory()->create();
        Group::factory()->count(3)->create(['created_by' => $user->id]);

        // Authenticate
        Sanctum::actingAs($user);

        // When: User lists all groups
        $response = $this->getJson('/api/groups');

        // Then: All groups are returned
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE GROUP TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: User updates a group
     * 
     * Given: A group exists
     * When: User updates the group name
     * Then: Group is updated successfully
     */
    public function test_user_can_update_group(): void
    {
        // Given: A group exists
        $user = User::factory()->create();
        $group = Group::factory()->create([
            'name' => 'Old Name',
            'created_by' => $user->id,
        ]);

        // Authenticate
        Sanctum::actingAs($user);

        // When: User updates the group
        $response = $this->putJson("/api/groups/{$group->id}", [
            'name' => 'New Name',
            'description' => 'Updated description',
        ]);

        // Then: Group is updated
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Group updated successfully',
                'data' => [
                    'name' => 'New Name',
                    'description' => 'Updated description',
                ],
            ]);

        // Verify database
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'New Name',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE GROUP TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: User deletes a group
     * 
     * Given: A group exists
     * When: User deletes the group
     * Then: Group is removed from the system
     */
    public function test_user_can_delete_group(): void
    {
        // Given: A group exists
        $user = User::factory()->create();
        $group = Group::factory()->create(['created_by' => $user->id]);

        // Authenticate
        Sanctum::actingAs($user);

        // When: User deletes the group
        $response = $this->deleteJson("/api/groups/{$group->id}");

        // Then: Group is deleted
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Group deleted successfully',
            ]);

        // Verify database
        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBER MANAGEMENT TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: User adds members to a group
     * 
     * Given: A group exists with one member
     * When: User adds two new members
     * Then: All three users are now members
     */
    public function test_user_can_add_members_to_group(): void
    {
        // Given: A group with creator as only member
        $creator = User::factory()->create();
        $newMember1 = User::factory()->create();
        $newMember2 = User::factory()->create();
        
        $group = Group::factory()->create(['created_by' => $creator->id]);
        $group->members()->attach($creator->id);

        // Authenticate
        Sanctum::actingAs($creator);

        // When: User adds new members
        $response = $this->postJson("/api/groups/{$group->id}/members", [
            'user_ids' => [$newMember1->id, $newMember2->id],
        ]);

        // Then: Members are added
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Members added successfully',
            ]);

        // Verify all 3 members exist
        $this->assertDatabaseCount('group_user', 3);
    }

    /**
     * Scenario: User removes a member from a group
     * 
     * Given: A group with multiple members
     * When: User removes one member
     * Then: That member is no longer in the group
     */
    public function test_user_can_remove_member_from_group(): void
    {
        // Given: A group with 2 members
        $creator = User::factory()->create();
        $member = User::factory()->create();
        
        $group = Group::factory()->create(['created_by' => $creator->id]);
        $group->members()->attach([$creator->id, $member->id]);

        // Authenticate
        Sanctum::actingAs($creator);

        // When: User removes a member
        $response = $this->deleteJson("/api/groups/{$group->id}/members/{$member->id}");

        // Then: Member is removed
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Member removed successfully',
            ]);

        // Verify member was removed
        $this->assertDatabaseMissing('group_user', [
            'group_id' => $group->id,
            'user_id' => $member->id,
        ]);

        // Verify creator still exists
        $this->assertDatabaseHas('group_user', [
            'group_id' => $group->id,
            'user_id' => $creator->id,
        ]);
    }
}
