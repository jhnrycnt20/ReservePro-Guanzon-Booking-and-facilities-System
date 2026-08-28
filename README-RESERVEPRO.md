# ReservePro — Resort Management System

Laravel 10 + Blade + Bootstrap 5 resort booking and facilities system for Guanzon.

## Requirements

- PHP 8.2
- Composer
- MySQL 8 / MariaDB (XAMPP)
- Node.js (optional; UI uses Bootstrap CDN)

## Setup

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
```

Configure `.env`:

```
APP_NAME=ReservePro
DB_DATABASE=reservepro
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000

## Demo accounts

Password for all: `password`

| Role | Email |
|------|-------|
| Admin | admin@reservepro.test |
| Front Desk | frontdesk@reservepro.test |
| Security | security@reservepro.test |
| Guest | guest@reservepro.test |

## Workflows implemented

1. Guest register/login → browse → availability → reservation queue → status tracking  
2. Front desk approve/reject → payment verify → check-in/out → walk-ins → receipts  
3. Guest incident/broken amenity reports  
4. Security verify/invalid investigations  
5. Front desk resolve verified reports  
6. Admin monitoring, users, accommodations, pricing, analytics, settings  
7. Internal database notifications + audit logs  

## Mobile app

See **[README-MOBILE.md](README-MOBILE.md)** for the Android app (Capacitor) and production deployment without tunnel.

## Architecture

- Controllers + Form Requests + Policies + Middleware (`role`, `active`)
- Services: Booking, Availability, Payment, CheckIn, CheckOut, IncidentReport, Notification, Audit
- Double-booking prevention on create and approve
- Server-side booking total calculation
