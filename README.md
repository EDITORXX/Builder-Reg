# Builder Partner Platform — Phase 1 MVP

Lead Lock & Channel Partner Management SaaS. Laravel 12 + MySQL/SQLite + Sanctum.

## Run on port 8011

```bash
# From project root (builder-platform)
php artisan serve --port=8011
```

Open **http://localhost:8011**. Set `APP_URL=http://localhost:8011` in `.env`.

## Setup

1. **Install dependencies**
   ```bash
   composer install
   ```

2. **Environment**
   - Copy `.env.example` to `.env`, set `APP_URL=http://localhost:8011`
   - Default uses SQLite (`DB_CONNECTION=sqlite`). For MySQL set `DB_CONNECTION=mysql`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

3. **Migrations & seed**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

4. **Seed users**
   - **Super Admin:** `super@builder.com` / `password`
   - **Builder Admin:** `admin@builder.com` / `password` (after login, create projects under Sample Builder)

## API (Base: `/api`)

- **Auth:** `POST /api/auth/login`, `POST /api/auth/logout`, `GET /api/auth/me`, `POST /api/auth/cp/register`
- **Builders:** `POST /api/builders`, `GET /api/builders/{id}` (super_admin)
- **Projects:** `GET /api/projects`, `POST /api/builders/{id}/projects`, `PATCH/DELETE /api/projects/{id}`
- **CP Applications:** `POST /api/cp/apply`, `GET /api/cp/my-applications`, `GET/POST approve|reject|needs-info /api/cp-applications/...`
- **Locks:** `GET /api/locks/check?project_id=&mobile=`, `GET /api/locks`, `POST /api/locks/{id}/force-unlock`
- **Leads:** `GET/POST /api/leads`, `GET /api/leads/{id}`, `PATCH /api/leads/{id}/status`, `POST /api/leads/{id}/assign`
- **Visits:** `POST /api/leads/{id}/visits`, `PATCH /api/visits/{id}/reschedule`, `POST /api/visits/{id}/cancel`, `POST /api/visits/{id}/otp/send`, `POST /api/visits/{id}/otp/verify`, `POST /api/visits/{id}/confirm`
- **Reports:** `GET /api/reports/leads`, `GET /api/reports/locks`, `GET /api/reports/cp-performance`, `GET /api/reports/conversion`

Use **Bearer token** (Sanctum) for authenticated routes.

## Lock expiry

Hourly cron: `php artisan schedule:run` (or system cron `* * * * * php /path/to/artisan schedule:run`). Runs `ExpireLocksJob` to mark expired locks.

## Core rule

**Lock key** = `project_id` + `customer_mobile`. Lock is created only when a **visit is confirmed** (OTP or manual). Same mobile + same project + active lock → lead registration blocked; CP name hidden, expiry date shown.

---

## Deploy (clone from GitHub)

Repo: **https://github.com/EDITORXX/Builder-Reg**

```bash
git clone https://github.com/EDITORXX/Builder-Reg.git
cd Builder-Reg
composer install --optimize-autoloader --no-dev
cp .env.example .env
php artisan key:generate
# Edit .env: APP_URL, DB_*, QUEUE_CONNECTION, etc.
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
# Point web server doc root to /public
# Optional: set cron * * * * * php /path/to/artisan schedule:run
```

**Production:** Set `APP_ENV=production`, `APP_DEBUG=false`, and use MySQL/PostgreSQL. For queue (notifications): `QUEUE_CONNECTION=database` or Redis and run `php artisan queue:work`.
