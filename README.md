# OpenSplit

**Open-source expense splitting application** - A Splitwise alternative built with enterprise-grade architecture.

[![CI](https://github.com/balaji-premkumar/opensplit-web/actions/workflows/ci.yml/badge.svg)](https://github.com/balaji-premkumar/opensplit-web/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## Features

- 🏠 **Group Management** - Create groups, add/remove members
- 💰 **Expense Tracking** - Record expenses with flexible split options
- ⚖️ **Balance Calculation** - Track who owes whom using paid_share/owed_share model
- 📊 **REST API** - Full CRUD operations with OpenAPI documentation
- 🐳 **Docker Ready** - Production-ready containerized deployment
- 🔒 **Secure Architecture** - Enterprise 3-tier network isolation

## Tech Stack

| Component | Technology |
|-----------|------------|
| Framework | Laravel 11 |
| Language | PHP 8.4 |
| Database | PostgreSQL 15 |
| Containers | Docker Compose |
| API Docs | Swagger/OpenAPI 3.0 |
| Testing | PHPUnit (BDD-style) |
| CI/CD | GitHub Actions |

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

## Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/opensplit.git
cd opensplit

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

| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| Swagger UI | http://localhost:8080/api/documentation |
| Database | localhost:5432 (dev only) |

## API Endpoints

### Groups

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/groups` | List all groups |
| POST | `/api/groups` | Create a group |
| GET | `/api/groups/{id}` | Get group details |
| PUT | `/api/groups/{id}` | Update group |
| DELETE | `/api/groups/{id}` | Delete group |
| POST | `/api/groups/{id}/members` | Add members |
| DELETE | `/api/groups/{id}/members/{userId}` | Remove member |

### Expenses

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/expenses` | Create expense with splits |
| GET | `/api/expenses/{id}` | Get expense details |
| DELETE | `/api/expenses/{id}` | Delete expense |
| GET | `/api/groups/{id}/expenses` | Get group expenses |

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

# Run specific test suite
docker compose exec app php artisan test --filter=ExpenseManagementTest
docker compose exec app php artisan test --filter=GroupManagementTest
```

**Test Coverage:**
- 15 tests, 88 assertions
- BDD-style with Given/When/Then pattern

## Development

### Project Structure

```
opensplit/
├── app/
│   ├── DTOs/                 # Data Transfer Objects
│   ├── Exceptions/           # Custom exceptions
│   ├── Http/Controllers/     # API controllers
│   ├── Models/               # Eloquent models
│   ├── Repositories/         # Data access layer
│   └── Services/             # Business logic
├── database/migrations/      # Database schema
├── tests/Feature/            # BDD feature tests
├── docker/                   # Docker configs
└── .github/workflows/        # CI/CD pipelines
```

### Commands

```bash
# Start development
docker compose up -d

# Stop containers
docker compose down

# View logs
docker compose logs -f app

# Run artisan commands
docker compose exec app php artisan <command>

# Regenerate Swagger docs
docker compose exec app php artisan l5-swagger:generate
```

## CI/CD

### Automatic (on PR)
- **CI Workflow**: Runs tests and linting on every pull request
- Required for merging to `master`

### Manual
- **Docker Build**: Build and optionally push Docker images
- Trigger from Actions tab → "Run workflow"

See [docs/BRANCH_PROTECTION.md](docs/BRANCH_PROTECTION.md) for branch protection setup.

## Production Deployment

```bash
# Use production override (hides DB port)
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

> **Note:** Direct pushes to `master` are blocked. All changes require PR review.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Inspired by [Splitwise](https://www.splitwise.com/)
- Built with [Laravel](https://laravel.com/)
