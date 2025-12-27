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

## Gate Management

Gates represent physical access points (doors) in the data center.

### Features
- Create/edit gates with name, location, and description
- Link gates to physical doors via `door_id` (SOC only)
- Configure solenoid IP address for multi-door support
- View real-time access logs per gate
- Monitor door integration status (Online/Offline)

### Door Integration (SOC Only)

SOC users can configure door integration in the gate edit page:

1. **Door ID**: Enter the `DEVICE_ID` from the client's `.env` file
2. **Solenoid IP**: Enter the IP address of the ESP8266 device

Once configured, the gate will show integration status:
- 🟢 **Online**: Connected and receiving heartbeats
- 🟡 **Offline**: Integrated but no recent heartbeat (>5 min)
- ⚪ **Not Integrated**: No door_id assigned

### Live Access Logs

The gate detail page shows real-time access events:
- **Entry**: Door was unlocked for a vendor
- **Exit**: Door lock sequence completed
- **Denied**: Access was refused (no valid task)

Logs update automatically every 5 seconds via polling.

## API Endpoints

### Door Access Logging (Internal)

Used by the Python server to log access events:

```
POST /api/doors/log-access
POST /api/doors/heartbeat
GET /api/doors/{door_id}/info
```

### Gate Access Logs (Authenticated)

```
GET /api/gates/{gate}/access-logs
```

Returns recent access logs for website polling.

## Development

```bash
npm run dev
```

