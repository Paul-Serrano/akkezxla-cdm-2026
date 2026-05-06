# ⚽ World Cup 2026 Betting App

A mobile-first web application built with Laravel that allows friends to compete by predicting matches, results, and odds during the 2026 FIFA World Cup.

The goal is simple: **prove who knows football best** — and optionally align bets on Winamax based on the most popular predictions.

---

## 🚀 Features

- 📅 View upcoming World Cup matches
- 📊 Display odds, results, and game data via external API
- 🧠 Place predictions (winner, score, etc.)
- 🏆 Leaderboard to rank players based on performance
- 👥 Private group betting with friends
- 📈 Consensus betting (most chosen bet per match)
- 📱 Mobile-first UI for seamless usage during matches

---

## 🛠️ Tech Stack

### Backend
- Laravel (PHP)
- PostgreSQL database
- REST API integrations

### Frontend
- Blade templating
- Tailwind CSS
- MaryUI components

### DevOps / Tools
- Docker (containerized environment)
- Adminer (database management UI)

### External API
- https://the-odds-api.com/
  Used to fetch:
  - Matches
  - Results
  - Betting odds

---

## 🐳 Docker Setup

### Prerequisites
- Docker
- Docker Compose

### Installation

```bash
git clone <your-repo-url>
cd <project-folder>
cp .env.example .env
```

Update your `.env` with PostgreSQL credentials:

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=worldcup
DB_USERNAME=postgres
DB_PASSWORD=secret
```

### Start containers

```bash
docker-compose up -d --build
```

### Run migrations

```bash
docker exec -it app php artisan migrate
```

---

## 🗄️ Database Access (Adminer)

Adminer will be available at:

http://localhost:8080

Example credentials:
- System: PostgreSQL
- Server: postgres
- Username: postgres
- Password: secret
- Database: worldcup

---

## 🔑 API Configuration

Create an account on https://the-odds-api.com/ and add your key:

```env
ODDS_API_KEY=your_api_key_here
```

---

## 📱 Frontend

The UI is built with:
- Blade templates
- Tailwind CSS
- MaryUI components

The application is designed **mobile-first**, optimized for quick access during live matches.

---

## 🎯 Project Goal

This project is designed for a group of friends to:

- Predict World Cup match outcomes
- Compete on a leaderboard
- Compare knowledge of football
- Optionally place real bets on Winamax based on group consensus

---

## 🧪 Future Improvements

- 🔔 Real-time notifications (match start, results)
- 📊 Advanced statistics (accuracy, streaks)
- 💬 Chat between players
- 🧾 Bet history tracking
- 🔐 Authentication & private leagues

---

## ⚠️ Disclaimer

This project is for **entertainment purposes only**.  
Bet responsibly. No real money handling is managed by the application.

---

## 🔁 CI/CD (GitHub Actions + Render)

This repository now supports a CI/CD workflow with GitHub Actions:

- `CI` workflow:
  - Runs on pull requests to `main` and pushes to `main`
  - Executes Laravel tests with PHP 8.3
  - Builds frontend assets with Node.js 22

- `Deploy to Render` workflow:
  - Runs automatically after `CI` succeeds on `main`
  - Triggers a Render deploy hook
  - Verifies app health via `/up`

### Required GitHub Secrets

In your GitHub repository, add these secrets:

- `RENDER_DEPLOY_HOOK_URL`
  - Found in Render service settings: Deploy Hook
- `APP_HEALTHCHECK_URL`
  - Example: `https://your-service.onrender.com/up`

### Required Render Environment Variables

Set these in your Render service:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY=<generated-laravel-key>`
- `APP_URL=https://your-service.onrender.com`
- `DB_CONNECTION=pgsql`
- `DB_HOST=<render-postgres-host>`
- `DB_PORT=5432`
- `DB_DATABASE=<render-postgres-db>`
- `DB_USERNAME=<render-postgres-user>`
- `DB_PASSWORD=<render-postgres-password>`
- `ODDS_API_KEY=<your-api-key>`

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
