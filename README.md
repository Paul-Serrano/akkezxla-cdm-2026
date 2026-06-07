# ⚽ Akkezxla — Coupe du Monde 2026

A mobile-first betting app for friends to compete by predicting World Cup 2026 match results.

The goal is simple: **prove who knows football best** — and optionally align bets on Winamax based on the most popular group predictions.

---

## 🚀 Features

- 📅 Browse all World Cup matches on 18 pages (4 games per page), in chronological order
- 🧠 Place predictions (1 / N / 2) on each game
- 🏆 Public ranking leaderboard (visible without login)
- 📈 Consensus betting — see the most chosen outcome per match
- 💸 Winamax ticket management per Match Day page (total odds, amount bet, earning, status)
- 🔒 Automatic lock of game betting when linked Winamax ticket status is `Placed`, `Won`, or `Lost` (still open on `Pending`)
- 📋 Akkezxla-only Winamax Bet summary page with per-ticket details and global financial totals
- 👥 Role-based access (admin, akkezxla, regular, custom roles)
- 🛡️ Admin panel — manage users, roles, and app configuration
- 📊 Group standings per World Cup group (sourced from imported football-data standings)
- 🧪 Admin-only football-data API explorer page for endpoint/query testing
- ⚙️ Admin Import Data page to run migrations, migration rollback, and imports (teams, games, standings, players) without shell access
- 📱 Mobile-first UI with dark/light theme toggle

---

## 🛠️ Tech Stack

### Backend
- **Laravel 13** (PHP 8.3)
- **PostgreSQL 16** database
- **Livewire 4.3** for reactive UI components

### Frontend
- **Blade** templating
- **Tailwind CSS v4** + **DaisyUI 5.5**
- **Mary UI 2.8** component library
- **Vite 8** asset bundler

### Infrastructure
- **Docker** (separate dev and prod configs in `infra/docker/`)
- **Nginx** (prod only, config in `infra/nginx/prod/`)
- **Adminer** (dev only, database management UI at `localhost:8080`)
- **Render** (cloud hosting)

---

## 🐳 Local Development (Docker)

### Prerequisites
- Docker + Docker Compose

### Setup

```bash
git clone <your-repo-url>
cd <project-folder>
cp backend/.env.example backend/.env
# Edit backend/.env — set APP_KEY and DB credentials
```

### Start dev containers

```bash
docker compose -f infra/docker/dev/docker-compose.yml up -d --build
```

This starts:
- `app` — Laravel dev server at `http://localhost:8000`
- `postgres` — PostgreSQL 16 at `localhost:5432`
- `adminer` — DB UI at `http://localhost:8080`

### Run migrations

```bash
docker exec -it cdm_app php artisan migrate
```

### Adminer credentials (dev)
- System: `PostgreSQL`
- Server: `postgres`
- Username: `postgres`
- Password: `secret`
- Database: `worldcup`

### Frontend assets (hot reload)

```bash
cd backend && npm install && npm run dev
```

> **Note:** Tailwind v4 generates CSS at build time. If a class appears missing locally, make sure `npm run dev` is running — it scans blade/PHP files and emits only used classes.

---

## 🖼️ Logo

The app logo is served as a static asset (not processed by Vite).

Place it at:
```
backend/public/images/logo.png
```

It is referenced in:
- Browser tab favicon (`<link rel="icon">`)
- Mobile top nav brand slot
- Desktop sidebar header

---

## 🔑 Environment Variables

Key variables to set in `.env` / Render dashboard:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=                        # php artisan key:generate
APP_URL=https://your-app.onrender.com

DB_CONNECTION=pgsql
DB_HOST=<render-postgres-host>
DB_PORT=5432
DB_DATABASE=<db-name>
DB_USERNAME=<db-user>
DB_PASSWORD=<db-password>

ODDS_API_KEY=                   # optional, for future odds integration
FOOTBALL_DATA_API_KEY=          # optional, for future match data integration
```

`FOOTBALL_DATA_API_KEY` is required for import commands and the API explorer.

---

## 🔁 CI/CD (GitHub Actions + Render)

### Workflows

- **`CI`** (`ci.yml`) — runs on every push and pull request to `main`:
  - PHP 8.3 + Laravel tests
  - Node 22 frontend build

- **`Deploy to Render`** (`deploy-render.yml`) — triggered **manually** via GitHub Actions (`workflow_dispatch`):
  - Calls the Render deploy hook
  - Waits for warm-up, then polls `/up` (up to 12 × 10s attempts) to confirm the deploy succeeded

### Required GitHub Secrets

| Secret | Where to find it |
|---|---|
| `RENDER_DEPLOY_HOOK_URL` | Render service → Settings → Deploy Hook |
| `APP_HEALTHCHECK_URL` | Your app URL + `/up` (e.g. `https://your-app.onrender.com/up`) |

### Render Environment Variables

Set all variables from the [Environment Variables](#-environment-variables) section above in your Render service dashboard.

### Deploy entrypoint behaviour

The production entrypoint (`infra/docker/prod/entrypoint.sh`) supports these optional env flags on deploy:

| Variable | Default | Effect |
|---|---|---|
| `RUN_MIGRATIONS` | `true` on Render | Runs `php artisan migrate --force` |
| `RUN_SEEDER` | `false` | Seeds the database |
| `RUN_IMPORTS` | `false` | Runs `import:teams` + `import:games` artisan commands |

### Admin import workflow (no shell)

For environments like Render where shell access is limited, admins can open:

- `/admin/import-data`

From this page you can run:

- Migrations
- `migrate:rollback`
- `import:teams`
- `import:games --season=2026`
- `import:standings --season=2026`
- `import:players`

## 🎟️ Winamax Bet Flow

- Winamax users can save one Winamax ticket per Match Day page (4 linked games)
- Required fields: `totalOdds`, `amountBet`, `earning`, `status`
- Allowed statuses: `Pending`, `Placed`, `Won`, `Lost`
- When a ticket exists for a game page, player betting on linked games is allowed only if ticket status is `Pending`
- Akkezxla users can access `/winamax-bet` to review all tickets, statuses, and totals:
  - Total money placed (sum of `amountBet`)
  - Total earnings on won tickets (sum of `earning` where status = `Won`)
  - Total earned (`won earnings - amount bet`)

### Standings import behavior

`import:standings` stores standings metrics directly on each team (`standingPosition`, `standingPoints`, goals, etc.), and the standings UI reads those persisted fields.

When `--season=2026` returns an ungrouped shape from football-data, the command automatically retries the grouped endpoint (without season query) to preserve Group A/B/C ordering.

---

## 🟢 Uptime Monitoring (UptimeRobot)

The `/up` health endpoint (built into Laravel) is used to monitor production uptime.

### Setup

1. Create a free account at [uptimerobot.com](https://uptimerobot.com)
2. Add a new monitor:
   - **Type:** `HTTP(s)`
   - **Friendly Name:** `CDM 2026`
   - **URL:** `https://your-app.onrender.com/up`
   - **Monitoring Interval:** `5 minutes`
3. Optionally add an alert contact (email / Telegram / Slack) to get notified on downtime

> The `/up` endpoint returns HTTP 200 when the app and database are reachable, and HTTP 500 otherwise — making it a reliable health signal.

---

## ⚠️ Disclaimer

This project is for **entertainment purposes only**.  
Bet responsibly. No real money handling is managed by the application.

## ⏰ Render Cron Service

Use the same environment variables as the web service for the cron service:

- `APP_ENV`
- `APP_DEBUG`
- `APP_KEY`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `FOOTBALL_DATA_API_KEY`

Recommended cron-specific overrides:

- `RUN_MIGRATIONS=false`
- `RUN_IMPORTS=false`
- `RUN_SEEDER=false`

The scheduler should run every 5 minutes. Laravel currently schedules:

- `import:live-games --season=2026`
- `import:standings --season=2026`

When no games are in progress, `import:live-games` exits quickly.

### Recommended Branch Protection

Enable branch protection on `main` and require the `CI` workflow to pass before merge.

### Deployment Flow

1. Open PR to `main`.
2. `CI` runs tests and frontend build.
3. Merge into `main`.
4. `Deploy to Render` triggers automatically.
5. Health check validates `/up`.

---

## 👨‍💻 Author

Paul Serrano  
Backend Developer (Laravel / Symfony)
