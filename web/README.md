# Sentinel Web Dashboard

Laravel-based admin panel for managing access control.

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- SQLite (default) or MySQL/PostgreSQL

## Installation

```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Build assets
npm run build

# Start server
php artisan serve
```

## Default Accounts

| Role | Email | Password |
|------|-------|----------|
| DCFM (Admin) | admin@sentinel.com | password |
| SOC (Security) | soc@sentinel.com | password |
| Vendor | vendor1@example.com | password |

## Development

```bash
npm run dev
```
