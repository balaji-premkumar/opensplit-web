# GitHub Copilot Instructions

This document provides context for GitHub Copilot to understand the OpenSplit project structure and coding conventions.

## Project Overview

**OpenSplit** is an open-source expense splitting application (Splitwise alternative) built with:

-   **Framework**: Laravel 11 (PHP 8.4)
-   **Database**: PostgreSQL 15
-   **Authentication**: Laravel Sanctum (API tokens) + Socialite (OAuth)
-   **Architecture**: Service-Repository Pattern
-   **Testing**: PHPUnit with BDD-style tests

## Architecture Patterns

### Service-Repository Pattern

```
Controller → Service → Repository → Model
```

-   **Controllers** (`app/Http/Controllers/`): Handle HTTP, validation, return JSON
-   **Services** (`app/Services/`): Business logic, transactions, validation rules
-   **Repositories** (`app/Repositories/`): Database queries via Eloquent
-   **DTOs** (`app/DTOs/`): Immutable data transfer objects

### Example Flow

```php
// Controller validates and delegates
$dto = ExpenseDTO::fromArray($validated);
$expense = $this->expenseService->addExpense($dto);

// Service handles business logic
DB::transaction(function () {
    $this->repository->create($data);
    $this->repository->createSplits($expenseId, $splits);
});

// Repository handles queries
Expense::create($data);
```

## Code Conventions

### Controllers

```php
class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenseService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([...]);
        // Delegate to service
        return response()->json([...], 201);
    }
}
```

### Services

```php
class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $repository
    ) {}

    public function addExpense(ExpenseDTO $dto): Expense
    {
        return DB::transaction(function () use ($dto) {
            // Business logic here
        });
    }
}
```

### Repository Interfaces

Always code to interfaces for testability:

```php
interface ExpenseRepositoryInterface
{
    public function create(array $data): Expense;
    public function find(int $id): ?Expense;
}
```

### OpenAPI Annotations

Use PHPDoc-style annotations for Swagger:

```php
/**
 * @OA\Post(
 *     path="/expenses",
 *     tags={"Expenses"},
 *     summary="Create expense",
 *     security={{"sanctum": {}}},
 *     @OA\Response(response=201, description="Created")
 * )
 */
```

## Testing Conventions

### BDD-Style Tests

Use Given/When/Then pattern:

```php
public function test_user_can_create_expense(): void
{
    // Given: Setup preconditions
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // When: Perform action
    $response = $this->postJson('/api/expenses', [...]);

    // Then: Assert outcomes
    $response->assertStatus(201);
    $this->assertDatabaseHas('expenses', [...]);
}
```

### Authentication in Tests

Always use Sanctum for protected routes:

```php
use Laravel\Sanctum\Sanctum;

Sanctum::actingAs($user);
$this->getJson('/api/protected-route');
```

## Database Conventions

### Migrations

-   Use `foreignId()` with `constrained()->onDelete('cascade')`
-   Use `decimal()` for money (not float)
-   Add indexes for frequently queried columns

```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
$table->decimal('amount', 10, 2);
$table->index(['group_id', 'created_at']);
```

### Models

-   Define `$fillable` explicitly
-   Add relationships with return types
-   Use casts for dates and decimals

```php
protected $fillable = ['name', 'amount', 'user_id'];

protected function casts(): array
{
    return ['amount' => 'decimal:2'];
}

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

## API Response Format

### Success

```json
{
    "message": "Resource created successfully",
    "data": { ... }
}
```

### Error

```json
{
    "message": "Validation failed",
    "errors": {
        "field": ["Error message"]
    }
}
```

## File Structure

```
app/
├── DTOs/                 # Data Transfer Objects
├── Exceptions/           # Custom exceptions
├── Http/Controllers/     # API controllers
├── Models/               # Eloquent models
├── Providers/            # Service providers
├── Repositories/
│   ├── Contracts/        # Repository interfaces
│   └── *.php             # Implementations
└── Services/             # Business logic

tests/Feature/            # BDD feature tests
database/migrations/      # Database schema
routes/api.php            # API routes
```

## Common Commands

```bash
# Run tests
docker compose exec app php artisan test

# Generate Swagger docs
docker compose exec app php artisan l5-swagger:generate

# Run migrations
docker compose exec app php artisan migrate

# Create model with migration, factory, controller
docker compose exec app php artisan make:model ModelName -mfc
```

## Important Rules

1. **Always use dependency injection** via constructor
2. **Repository pattern** for all database operations
3. **Services** contain business logic, not controllers
4. **DTOs** for complex data transfer between layers
5. **Sanctum** for all protected API routes
6. **BDD-style tests** with clear Given/When/Then structure
7. **OpenAPI annotations** for all public endpoints
