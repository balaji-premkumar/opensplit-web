<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authentication Feature Tests
 * 
 * BDD-style tests for user authentication (register, login, logout).
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | REGISTRATION TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: User registers with valid data
     * 
     * Given: Valid registration data
     * When: User submits registration
     * Then: User is created and token is returned
     */
    public function test_user_can_register_with_valid_data(): void
    {
        // When: User registers
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Then: Registration is successful
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        // Verify database
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Scenario: Registration fails with existing email
     * 
     * Given: A user with email already exists
     * When: Another user tries to register with same email
     * Then: Validation error is returned
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        // Given: User exists
        User::factory()->create(['email' => 'john@example.com']);

        // When: Registration with same email
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Then: Validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Scenario: Registration requires password confirmation
     */
    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: User logs in with valid credentials
     * 
     * Given: A registered user
     * When: User logs in with correct credentials
     * Then: Token is returned
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        // Given: User exists
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        // When: User logs in
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Then: Login successful
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
                'token',
            ]);
    }

    /**
     * Scenario: Login fails with wrong password
     */
    public function test_login_fails_with_wrong_password(): void
    {
        // Given: User exists
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        // When: User logs in with wrong password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        // Then: Error returned
        $response->assertStatus(422);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: Authenticated user can logout
     * 
     * Given: An authenticated user
     * When: User logs out
     * Then: Token is revoked
     */
    public function test_authenticated_user_can_logout(): void
    {
        // Given: Authenticated user
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // When: User logs out
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        // Then: Logout successful
        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout successful']);
    }

    /*
    |--------------------------------------------------------------------------
    | PROTECTED ROUTES TESTS
    |--------------------------------------------------------------------------
    */

    /**
     * Scenario: Unauthenticated user cannot access protected routes
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        // When: Accessing protected route without token
        $response = $this->getJson('/api/groups');

        // Then: 401 Unauthorized
        $response->assertStatus(401);
    }

    /**
     * Scenario: Authenticated user can access protected routes
     */
    public function test_authenticated_user_can_access_protected_routes(): void
    {
        // Given: Authenticated user
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // When: Accessing protected route with token
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/groups');

        // Then: Access granted
        $response->assertStatus(200);
    }

    /**
     * Scenario: User can get their own profile
     */
    public function test_user_can_get_own_profile(): void
    {
        // Given: Authenticated user
        $user = User::factory()->create(['name' => 'John Doe']);
        $token = $user->createToken('test-token')->plainTextToken;

        // When: Getting profile
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/user');

        // Then: Profile returned
        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'John Doe');
    }
}
