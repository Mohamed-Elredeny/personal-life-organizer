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

## License

Private — for personal use.
