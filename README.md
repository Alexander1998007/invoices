# Invoice Test Assignment

Starter project for the Full-stack Invoice Test Assignment.

## Stack

- Frontend: Nuxt 4.0.3 + Vue 3.5.18 + Tailwind CSS 4 + vee-validate + Zod
- Backend: Laravel 12 + PHP 8.3
- Database: MySQL 8.4
- Environment: Docker Compose
- Node.js: 20 LTS

## Requirements

- Docker Desktop / Docker Engine with Compose v2
- Node.js 20 LTS + npm

Node.js/npm are required locally **only for frontend dependency installation and IDE/TypeScript support**. PHP and Composer are not required locally.

## Quick start

### Windows PowerShell

From the project root:

```powershell
.\scripts\setup.ps1
```

The script:

1. checks Node.js/npm;
2. runs `npm install` in `frontend/`, creating a local `node_modules` that your IDE can index;
3. starts Docker Compose.

### macOS / Linux / Git Bash

```bash
./scripts/setup.sh
```

## Manual start

If you prefer to run the steps manually:

```bash
cd frontend
npm install
cd ..
docker compose up --build
```

After the first backend start, Laravel is bootstrapped automatically into `./backend` and migrations are run when the database is ready.

Open:

- Frontend: http://localhost:3000
- Backend: http://localhost:8000
- MySQL: localhost:3307

The frontend expects the API at `http://localhost:8000/api`.

## Why is `npm install` also run locally?

The Docker Compose setup keeps frontend `node_modules` in a Docker volume so container dependencies are isolated. That is useful for Docker, but an IDE running on the host cannot see packages inside that Docker volume. As a result, TypeScript may report errors such as:

- `Cannot find module '@tailwindcss/vite'`
- `Cannot find name 'defineNuxtConfig'`
- `Cannot find name 'process'`

Running `npm install` in `frontend/` creates host-side dependencies for IDE autocomplete, TypeScript and Nuxt typings. The Docker container remains responsible for running the application.

After installation, if the IDE still shows old TypeScript errors, restart its TypeScript server or restart the IDE.

## Reset Docker environment

If you need a completely clean Docker state:

```bash
docker compose down -v
docker compose up --build
```

`-v` removes the MySQL and frontend dependency volumes. This will reset the local database.

## Notes

The Laravel application is intentionally bootstrapped by the backend container on first start so no local PHP/Composer installation is required. After the first run, the generated Laravel files remain in `./backend` and can be edited normally.

See `docs/TEST_PLAN.md` for a suggested implementation order.
