# Life OS — Personal Life Organizer

Self-hosted Laravel 11 + Filament 3 admin panel to organize Work, Goals, Finance, Marriage, and a PhD outreach pipeline (Scholarships + Professors). Bilingual EN/AR with RTL support, multi-currency (SAR / EGP / USD), live dashboard widgets with period filters.

## Features

- **Work** — Sectors → Projects → Tasks → Sub-tasks (assignable, with milestones)
- **Goals** — Long-term/quarterly with milestones, each milestone has its own tasks
- **Finance** — Transactions, budgets, categories (3 currencies, no FX mixing)
- **Marriage** — Anniversaries, gratitude journal, sharing toggle
- **PhD** — Scholarships pipeline + Professor outreach tracker with follow-up reminders
- **Bilingual** — English + Arabic with live language switcher, RTL automatic
- **Dynamic dashboard** — Period selector (week/month/quarter/year), polling widgets, comparison stats
- **Two-user sharing** — `is_shared` flag per record lets a partner see selected items

## Stack

- PHP 8.2 · Laravel 11 · Filament 3 · Livewire
- MySQL 8
- Tailwind + Vite (custom Filament theme)
- `bezhansalleh/filament-language-switch` for the locale switcher

## Setup on a fresh machine

```bash
# 1. Clone + install
git clone <repo-url> life-os
cd life-os
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
# Edit .env: set DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 3. Database
mysql -u root -e "CREATE DATABASE tasks_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed

# 4. Publish Filament translations (gitignored — must publish locally)
php artisan vendor:publish --tag=filament-translations
php artisan vendor:publish --tag=filament-panels-translations
php artisan vendor:publish --tag=filament-forms-translations
php artisan vendor:publish --tag=filament-tables-translations
php artisan vendor:publish --tag=filament-actions-translations

# 5. Build assets
npm run build

# 6. Run
php artisan serve --port=8088
```

Open http://127.0.0.1:8088/app — default seeded users (all password `password`):

| Email | Role | Lang |
|-------|------|------|
| `a.a@najidalqimam.sa` | owner | EN |
| `partner@local` | partner | EN |

## Local dev

```bash
npm run dev       # Vite hot-reload
php artisan serve --port=8088
```

## Project structure

```
app/
  Filament/
    Pages/            # Dashboard, EditProfile
    Resources/        # 11 CRUD resources (Sector, Project, Task, Goal,
                      # Transaction, Budget, Category, Anniversary, GratitudeLog,
                      # Scholarship, Professor)
    Widgets/          # Stats, charts, activity, balances-by-currency
  Models/
    Concerns/
      BelongsToOwner.php   # Global scope: own records + shared
  Support/
    Money.php         # Currency formatting (SAR/EGP/USD)
    PeriodFilter.php  # Week/Month/Quarter/Year + previous range + delta
  Http/Middleware/
    SetLocale.php     # Apply user.locale -> app()->setLocale()

lang/
  en.json, ar.json    # Custom UI strings

resources/css/filament/admin/theme.css   # Custom Filament theme
```

## Sharing model

Most models include an `is_shared` flag. The `App\Models\Concerns\BelongsToOwner` trait adds a global scope that returns rows where `user_id = current_user` OR `is_shared = true`. Records are created with the current user's id automatically.

## Deploy to Laravel Cloud

The repo is deploy-ready. Filament translations re-publish automatically on `composer install` (via `post-install-cmd`), so the gitignored `lang/vendor/` rebuilds on every deploy.

### One-time setup at cloud.laravel.com

1. **Sign up** at <https://cloud.laravel.com> with your GitHub account.
2. **New project** → connect repo `Mohamed-Elredeny/personal-life-organizer` → branch `main`.
3. **Add a MySQL database** in the same project (cloud.laravel.com provisions one in a click). Note: Laravel Cloud auto-injects the DB connection env vars.
4. **Set environment variables** (Settings → Environment):
    - `APP_NAME` — `Life OS`
    - `APP_ENV` — `production`
    - `APP_DEBUG` — `false`
    - `APP_TIMEZONE` — `Asia/Riyadh`
    - `APP_URL` — your cloud URL (e.g. `https://life-os-xxx.laravel.cloud`)
    - `APP_KEY` — leave empty; Cloud's first deploy generates one. Or run locally: `php artisan key:generate --show` and paste.
    - `LOG_LEVEL` — `warning`
    - `SESSION_DRIVER` — `database`
    - `CACHE_STORE` — `database`
    - `QUEUE_CONNECTION` — `database`
    - DB vars are auto-injected from the linked MySQL.
5. **Build & deploy commands** (Cloud usually auto-detects, but verify):
    - Install: `composer install --no-dev --optimize-autoloader` then `npm ci && npm run build`
    - Release (runs each deploy): `php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan filament:cache-components`
6. **Trigger first deploy** — every `git push origin main` re-deploys automatically.

### Seed the database (one-time, after first deploy)

Open the Cloud project's **Console / Shell** tab and run:

```bash
php artisan db:seed --force
```

This creates the two default users from `DatabaseSeeder.php`. **Change their passwords immediately** via Profile.

### Common gotchas

- **First-time 500 with "No APP_KEY"** — generate one locally with `php artisan key:generate --show`, paste into Cloud env, redeploy.
- **Assets 404** — make sure `npm run build` ran in the build pipeline. Cloud should auto-detect Vite.
- **Filament panel missing translations** — verify `composer install` ran the `post-install-cmd` (check deploy logs for `Publishing [filament-translations] assets`).
- **Sessions / login fail** — `SESSION_DRIVER=database` requires the `sessions` table (created by `php artisan migrate`).

## License

Private — for personal use.
