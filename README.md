# OpenSplit

**Open-source expense splitting application** - A Splitwise alternative built with enterprise-grade architecture.

[![CI](https://github.com/balaji-premkumar/opensplit-web/actions/workflows/ci.yml/badge.svg)](https://github.com/balaji-premkumar/opensplit-web/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Features

-   🔐 **Authentication** - API tokens with Laravel Sanctum + Social logins (Google, Facebook, X)
-   🏠 **Group Management** - Create groups, add/remove members
-   💰 **Expense Tracking** - Record expenses with flexible split options
-   ⚖️ **Balance Calculation** - Track who owes whom using paid_share/owed_share model
-   📊 **REST API** - Full CRUD operations with OpenAPI documentation
-   🐳 **Docker Ready** - Production-ready containerized deployment
-   🔒 **Secure Architecture** - Enterprise 3-tier network isolation
-   🧪 **BDD Testing** - 24 tests with 113 assertions

## Tech Stack

| Component      | Technology                  |
| -------------- | --------------------------- |
| Framework      | Laravel 11                  |
| Language       | PHP 8.4                     |
| Database       | PostgreSQL 15               |
| Authentication | Laravel Sanctum + Socialite |
| Containers     | Docker Compose              |
| API Docs       | Swagger/OpenAPI 3.0         |
| Testing        | PHPUnit (BDD-style)         |
| CI/CD          | GitHub Actions              |

## Quick Start

### Prerequisites

-   Docker & Docker Compose
-   Git

### Installation

```bash
# Clone the repository
git clone https://github.com/balaji-premkumar/opensplit-web.git
cd opensplit-web

# Copy environment file
cp .env.example .env

# Start the containers
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate

# Generate Swagger docs
docker compose exec app php artisan l5-swagger:generate
```

### Access

| Service     | URL                                     |
| ----------- | --------------------------------------- |
| Application | http://localhost:8080                   |
| Swagger UI  | http://localhost:8080/api/documentation |

## Authentication

### Register & Login

```bash
# Register
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name": "John", "email": "john@example.com", "password": "password123", "password_confirmation": "password123"}'

# Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "john@example.com", "password": "password123"}'
```

Response includes a `token` - use it for authenticated requests:

```bash
curl -H "Authorization: Bearer <token>" http://localhost:8080/api/groups
```

### Social Login

Redirect users to OAuth providers:

-   `GET /api/auth/google/redirect`
-   `GET /api/auth/facebook/redirect`
-   `GET /api/auth/twitter/redirect`

Configure credentials in `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
FACEBOOK_CLIENT_ID=...
TWITTER_CLIENT_ID=...
```

## API Endpoints

### Authentication (Public)

| Method | Endpoint                        | Description       |
| ------ | ------------------------------- | ----------------- |
| POST   | `/api/auth/register`            | Register new user |
| POST   | `/api/auth/login`               | Login, get token  |
| GET    | `/api/auth/{provider}/redirect` | OAuth redirect    |
| GET    | `/api/auth/{provider}/callback` | OAuth callback    |

### Authentication (Protected 🔒)

| Method | Endpoint           | Description      |
| ------ | ------------------ | ---------------- |
| POST   | `/api/auth/logout` | Revoke token     |
| GET    | `/api/auth/user`   | Get current user |

### Groups (Protected 🔒)

| Method | Endpoint                            | Description       |
| ------ | ----------------------------------- | ----------------- |
| GET    | `/api/groups`                       | List all groups   |
| POST   | `/api/groups`                       | Create a group    |
| GET    | `/api/groups/{id}`                  | Get group details |
| PUT    | `/api/groups/{id}`                  | Update group      |
| DELETE | `/api/groups/{id}`                  | Delete group      |
| POST   | `/api/groups/{id}/members`          | Add members       |
| DELETE | `/api/groups/{id}/members/{userId}` | Remove member     |

### Expenses (Protected 🔒)

| Method | Endpoint                    | Description                |
| ------ | --------------------------- | -------------------------- |
| POST   | `/api/expenses`             | Create expense with splits |
| GET    | `/api/expenses/{id}`        | Get expense details        |
| DELETE | `/api/expenses/{id}`        | Delete expense             |
| GET    | `/api/groups/{id}/expenses` | Get group expenses         |

## Architecture

```
┌─────────────────────────────────────────────────────┐
│                Service-Repository Pattern           │
├─────────────────────────────────────────────────────┤
│  Controller → Service → Repository → Database       │
│  (HTTP)       (Logic)   (Queries)    (PostgreSQL)   │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│              Docker Network Isolation               │
├─────────────────────────────────────────────────────┤
│  frontend_net: web ←→ app                           │
│  backend_net:  app ←→ db (internal only)            │
└─────────────────────────────────────────────────────┘
```

## Expense Split Model

```
Net Balance = paid_share - owed_share

Example: $300 dinner, User A pays, split 3 ways
┌─────────┬──────────┬───────────┬─────────────────┐
│ User    │ Paid     │ Owes      │ Net Balance     │
├─────────┼──────────┼───────────┼─────────────────┤
│ User A  │ $300     │ $100      │ +$200 (owed)    │
│ User B  │ $0       │ $100      │ -$100 (owes)    │
│ User C  │ $0       │ $100      │ -$100 (owes)    │
└─────────┴──────────┴───────────┴─────────────────┘
```

## Testing

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test suites
docker compose exec app php artisan test --filter=AuthenticationTest
docker compose exec app php artisan test --filter=GroupManagementTest
docker compose exec app php artisan test --filter=ExpenseManagementTest
```

**Test Coverage:** 24 tests, 113 assertions (BDD-style)

## CI/CD

### Automatic (on PR)

-   Runs tests and linting
-   Required for merging to `master`

### Manual (Docker Build)

-   Trigger from Actions tab → "Docker Build"
-   Builds and optionally pushes Docker images

See [docs/BRANCH_PROTECTION.md](docs/BRANCH_PROTECTION.md) for branch protection setup.

## Development

```bash
# Start development
docker compose up -d

# View logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan <command>

# Regenerate Swagger docs
docker compose exec app php artisan l5-swagger:generate

# Run tests
docker compose exec app php artisan test
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

> **Note:** Direct pushes to `master` are blocked. All changes require PR review.

## License

MIT License - see [LICENSE](LICENSE) for details.
