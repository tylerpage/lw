# Lindsey Wegmann Portfolio

Laravel 13 portfolio CMS for Lindsey Wegmann — public site, Filament admin, modular page builder, insights blog, case studies, and contact management.

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+
- SQLite (local) or PostgreSQL (production)

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve
```

Admin panel: [http://localhost:8000/admin](http://localhost:8000/admin)

Default admin user (seeded):

- Email: `admin@example.com`
- Password: `password`

Seeded pages are published for local browsing, but all copy is marked `[DRAFT]` until Lindsey approves it.

## Queue & scheduler

Contact form notifications are queued. Run a worker locally:

```bash
php artisan queue:work
```

Production should run a queue worker and `php artisan schedule:run` via cron.

## Environment variables

See `.env.example` for database, mail, storage, and analytics settings.

| Variable | Purpose |
|----------|---------|
| `ANALYTICS_PLAUSIBLE_DOMAIN` | Plausible site domain (preferred) |
| `ANALYTICS_GTM_ID` | Optional Google Tag Manager container |
| `ANALYTICS_DEBUG` | Log analytics events to browser console |

Event schema: [docs/analytics-events.md](docs/analytics-events.md)

## Storage

Local development uses the `public` disk. Production should configure S3-compatible storage for media uploads.

## Testing

```bash
php artisan test
vendor/bin/pint
npm run build
```

## Editor notes

- All seeded demo copy is prefixed with `[DRAFT]`.
- Career timeline uses verified Irish Titan and Surdyk's content from the requirements spec.
- Do not publish fictional case studies or demo posts as real work.
- Headshot is at `public/images/lw.jpeg`.

## Documentation

- [Build requirements](docs/lindsey-wegmann-portfolio-requirements.md)
- [Implementation decisions](docs/decisions.md)
- [Analytics events](docs/analytics-events.md)
