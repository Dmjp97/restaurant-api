# 🍔 Restaurant API Platform

> **Multi-tenant REST API** for restaurant chain management, built with **PHP 8.2 + CodeIgniter 4**.  
> Designed to scale across multiple restaurant brands with strict tenant isolation, role-based access, and a clean layered architecture.

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)](https://php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.4-EF4223?logo=codeigniter)](https://codeigniter.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?logo=redis)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker)](https://docker.com)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit_10-6C78AF)](https://phpunit.de)

---

## 📐 Architecture

```
┌─────────────────────────────────────────────────┐
│                  HTTP Request                   │
└──────────────────────┬──────────────────────────┘
                       │
            ┌──────────▼──────────┐
            │      Filters        │  JWT · RBAC · RateLimit
            └──────────┬──────────┘
                       │
            ┌──────────▼──────────┐
            │    Controller       │  Input validation only
            └──────────┬──────────┘
                       │
            ┌──────────▼──────────┐
            │      Service        │  Business rules + Events
            └──────────┬──────────┘
                       │
            ┌──────────▼──────────┐
            │    Repository       │  All DB queries
            └──────────┬──────────┘
                       │
            ┌──────────▼──────────┐
            │  MySQL + Redis      │  Persistence + Cache
            └─────────────────────┘
```

**Design patterns used:** Repository, Service Layer, Strategy (state machine), Observer (events/listeners), Dependency Injection.

---

## ⚡ Quick Start

### With Docker (recommended)

```bash
git clone https://github.com/yourusername/restaurant-api-platform.git
cd restaurant-api-platform

cp .env.example .env

docker-compose up -d

docker-compose exec app php spark migrate
docker-compose exec app php spark db:seed MainSeeder
```

API available at: **http://localhost:8080/api/v1**  
phpMyAdmin at: **http://localhost:8081**

### Without Docker

```bash
composer install
cp .env.example .env
# Edit .env with your local MySQL/Redis credentials

php spark migrate
php spark db:seed MainSeeder
php spark serve
```

### Railway

This project is ready for Railway using the included `Dockerfile` and `railway.json`.

Required service variables:

```bash
CI_ENVIRONMENT=production
JWT_SECRET=<strong-random-secret-at-least-32-chars>
```

Attach Railway MySQL and Redis services to the API service. The app reads Railway's default plugin variables automatically:

```bash
MYSQLHOST
MYSQLPORT
MYSQLDATABASE
MYSQLUSER
MYSQLPASSWORD
REDISHOST
REDISPORT
REDISPASSWORD
```

`railway.json` runs migrations before deploy with `php spark migrate --all`, starts Apache with Railway's injected `PORT`, and health-checks `GET /health`.

---

## 🔐 Authentication

The API uses **JWT access + refresh token pairs**.

- Access token → valid **15 minutes**
- Refresh token → valid **7 days** (rotated on use, revocable)

### Seed credentials

| Role        | Email                        | Password     |
|-------------|------------------------------|--------------|
| superadmin  | superadmin@platform.com      | password123  |
| manager     | manager@burgerhouse.com      | password123  |
| cashier     | cashier@burgerhouse.com      | password123  |
| kitchen     | kitchen@pizzalab.com         | password123  |

---

## 🗺️ API Reference

All endpoints are prefixed with `/api/v1`.  
Protected routes require `Authorization: Bearer <access_token>`.

---

### Auth

#### `POST /auth/login`
```json
// Request
{ "email": "manager@burgerhouse.com", "password": "password123" }

// Response 200
{
  "status": "success",
  "data": {
    "access_token":  "eyJ...",
    "refresh_token": "eyJ...",
    "token_type":    "Bearer",
    "expires_in":    900,
    "user": { "id": 2, "name": "Ana García", "role": "manager", "tenant_id": 1 }
  }
}
```

#### `POST /auth/refresh`
```json
// Request
{ "refresh_token": "eyJ..." }
```

#### `POST /auth/logout`  🔒
```json
{ "refresh_token": "eyJ..." }
```

#### `GET /auth/me`  🔒
Returns the authenticated user's profile.

---

### Products

#### `GET /products`  🔒
```
Query params:
  page      (int)     default: 1
  limit     (int)     default: 20, max: 100
  category  (string)  filter by category
  tenant_id (int)     superadmin only
```

#### `GET /products/:id`  🔒

#### `POST /products`  🔒 manager, superadmin
```json
{
  "name":         "Smash Burger XL",
  "sku":          "BH-006",
  "category":     "burgers",
  "price":        13.90,
  "description":  "Double smashed patty with aged cheddar",
  "is_available": true
}
```

#### `PUT /products/:id`  🔒 manager, superadmin

#### `DELETE /products/:id`  🔒 manager, superadmin

---

### Orders

#### `GET /orders`  🔒
```
Query params:
  status  (string)  pending|confirmed|preparing|ready|delivered|cancelled
  from    (date)    YYYY-MM-DD
  to      (date)    YYYY-MM-DD
  page    (int)
  limit   (int)
```

#### `GET /orders/:id`  🔒
Returns full order with all items.

#### `GET /orders/:id/timeline`  🔒
```json
// Response
{
  "status": "success",
  "data": [
    { "status": "pending",   "changed_by_name": "Luis Martínez", "created_at": "2024-01-15 12:00:00" },
    { "status": "confirmed", "changed_by_name": "Ana García",    "created_at": "2024-01-15 12:02:00" },
    { "status": "preparing", "changed_by_name": "Ana García",    "created_at": "2024-01-15 12:03:00" }
  ]
}
```

#### `POST /orders`  🔒
```json
{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 4, "quantity": 1 }
  ],
  "notes": "No onions on the burger please"
}
```

#### `PUT /orders/:id/status`  🔒
```json
{ "status": "preparing" }
```

**Valid state machine transitions:**
```
pending → confirmed → preparing → ready → delivered
   └──────────────────────────────────────→ cancelled
```

#### `DELETE /orders/:id`  🔒
Cancels the order (if cancellable).

---

### Reports  🔒 manager, superadmin

#### `GET /reports/sales?period=monthly`
```
period: daily | weekly | monthly | yearly
from, to: YYYY-MM-DD (optional, override period)
```

#### `GET /reports/top-products?period=monthly&limit=10`

#### `GET /reports/orders-by-status`

#### `GET /reports/revenue-by-tenant`  (superadmin only)

---

## 🛡️ Security Features

| Feature | Implementation |
|---------|----------------|
| Authentication | JWT HS256 with access + refresh token rotation |
| Authorization | Role-based (superadmin / manager / cashier / kitchen) |
| Tenant Isolation | Enforced at filter and service layer |
| Rate Limiting | 60 req/min per IP (Redis sliding window) |
| Token Revocation | Revoked JTIs stored in Redis until expiry |
| Input Validation | CI4 Validation rules on every mutating endpoint |
| SQL Injection | Query Builder + parameterised queries throughout |

---

## 🧪 Running Tests

```bash
# All tests
composer test

# Unit tests only
./vendor/bin/phpunit --testsuite Unit

# With coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 📁 Project Structure

```
app/
├── Config/
│   ├── Routes.php          ← versioned API routes
│   └── Events.php          ← event listener registration
├── Filters/
│   ├── JWTAuthFilter.php   ← JWT validation middleware
│   ├── RoleFilter.php      ← RBAC + tenant isolation
│   └── RateLimitFilter.php ← Redis-backed rate limiter
├── Libraries/
│   └── JWTHandler.php      ← token generation & validation
├── Repositories/
│   ├── Interfaces/         ← contracts (for DI & mocking)
│   ├── OrderRepository.php
│   └── ProductRepository.php
├── Services/
│   ├── AuthService.php     ← login, refresh, logout
│   ├── OrderService.php    ← state machine + business rules
│   └── ReportService.php   ← aggregated metrics + caching
├── Controllers/Api/V1/
│   ├── BaseApiController.php ← standardised JSON responses
│   ├── AuthController.php
│   ├── OrderController.php
│   ├── ProductController.php
│   └── ReportController.php
├── Events/
│   └── OrderStatusChanged.php ← domain event
├── Listeners/
│   └── NotifyKitchenListener.php
├── Exceptions/
│   ├── BusinessException.php
│   └── UnauthorizedException.php
└── Database/
    ├── Migrations/
    └── Seeds/

tests/
├── Unit/
│   └── OrderServiceTest.php
└── Feature/
```

---

## 🧩 Design Decisions

**Why Repository pattern?**  
Decouples business logic from the database. Services work against interfaces, making them fully testable with mocks — no database required in unit tests.

**Why a Service layer?**  
Controllers only handle HTTP concerns (validation, response format). All business logic — state machine transitions, tenant ownership checks, total calculations — lives in services, making it reusable and independently testable.

**Why token rotation on refresh?**  
Refresh token rotation limits the damage of a stolen token: each refresh invalidates the previous token. Combined with Redis-backed revocation lists, logout is immediate and effective.

**Why Redis for rate limiting?**  
File-based cache doesn't work correctly across multiple PHP workers. Redis gives atomic increment operations, making the sliding window counter accurate under concurrent load.

---

## 📄 License

MIT
