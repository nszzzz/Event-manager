# Docker README

This guide contains the Docker commands for this project.

## Compose Files
- Development: `compose.dev.yaml`
- Production: `compose.prod.yaml`

## Prerequisites
1. Install Docker Desktop (Docker Engine + Compose plugin).
2. Create backend environment file if missing:
   - `cp backend/.env.example backend/.env`

## Development Commands

### Start
- Build and run all dev services:
  - `docker compose -f compose.dev.yaml up -d --build`
- Run without rebuild:
  - `docker compose -f compose.dev.yaml up -d`

### Live Reload
- Frontend source is bind-mounted (`./frontend:/app`), so Vite hot reload works without `docker compose watch`.
- After changing dependencies (`package.json`), run:
  - `docker compose -f compose.dev.yaml exec frontend npm install`
- If module resolution still looks stale, recreate frontend:
  - `docker compose -f compose.dev.yaml up -d --build --force-recreate frontend`

### Stop / Remove
- Stop containers:
  - `docker compose -f compose.dev.yaml stop`
- Stop and remove containers/network:
  - `docker compose -f compose.dev.yaml down`
- Stop and remove containers + volumes (deletes DB data):
  - `docker compose -f compose.dev.yaml down -v`

### Status / Logs
- List running services:
  - `docker compose -f compose.dev.yaml ps`
- Stream all logs:
  - `docker compose -f compose.dev.yaml logs -f`
- Stream logs for one service:
  - `docker compose -f compose.dev.yaml logs -f web`
  - `docker compose -f compose.dev.yaml logs -f php-fpm`
  - `docker compose -f compose.dev.yaml logs -f frontend`
  - `docker compose -f compose.dev.yaml logs -f postgres`

### Rebuild / Restart
- Rebuild one service:
  - `docker compose -f compose.dev.yaml up -d --build frontend`
- Restart one service:
  - `docker compose -f compose.dev.yaml restart php-fpm`

### Shell Access
- Backend workspace shell:
  - `docker compose -f compose.dev.yaml exec workspace sh`
- PHP container shell:
  - `docker compose -f compose.dev.yaml exec php-fpm sh`
- Frontend container shell:
  - `docker compose -f compose.dev.yaml exec frontend sh`
- PostgreSQL shell:
  - `docker compose -f compose.dev.yaml exec postgres psql -U laravel -d app`

### Laravel / Backend Commands
- Run migrations:
  - `docker compose -f compose.dev.yaml exec workspace php artisan migrate`
- Fresh migrations + seed:
  - `docker compose -f compose.dev.yaml exec workspace php artisan migrate:fresh --seed`
- Run tests:
  - `docker compose -f compose.dev.yaml exec workspace php artisan test`
- Install PHP dependencies:
  - `docker compose -f compose.dev.yaml exec workspace composer install`

### Frontend Commands
- Install frontend dependencies:
  - `docker compose -f compose.dev.yaml exec frontend npm install`
- Run frontend tests (if configured):
  - `docker compose -f compose.dev.yaml exec frontend npm test`

## Production Commands

### Start
- Build and run production services:
  - `docker compose -f compose.prod.yaml up -d --build`
- Run without rebuild:
  - `docker compose -f compose.prod.yaml up -d`

### Stop / Remove
- Stop containers:
  - `docker compose -f compose.prod.yaml stop`
- Stop and remove containers/network:
  - `docker compose -f compose.prod.yaml down`
- Stop and remove containers + volumes (deletes DB/storage data):
  - `docker compose -f compose.prod.yaml down -v`

### Status / Logs
- List running services:
  - `docker compose -f compose.prod.yaml ps`
- Stream all logs:
  - `docker compose -f compose.prod.yaml logs -f`
- Stream one service log:
  - `docker compose -f compose.prod.yaml logs -f web`
  - `docker compose -f compose.prod.yaml logs -f php-fpm`
  - `docker compose -f compose.prod.yaml logs -f frontend`
  - `docker compose -f compose.prod.yaml logs -f postgres`

### Laravel Production Commands
- Run migrations in production mode:
  - `docker compose -f compose.prod.yaml exec php-fpm php artisan migrate --force`
- Clear/cache config routes views:
  - `docker compose -f compose.prod.yaml exec php-fpm php artisan optimize:clear`
  - `docker compose -f compose.prod.yaml exec php-fpm php artisan optimize`

## Validation / Debugging
- Validate compose file:
  - `docker compose -f compose.dev.yaml config`
  - `docker compose -f compose.prod.yaml config`
- Show container resource usage:
  - `docker stats`
- Show project volumes:
  - `docker volume ls`

## Cleanup Commands
- Remove unused images/containers/networks (safe cleanup):
  - `docker system prune`
- Remove unused images/containers/networks/volumes (destructive):
  - `docker system prune -a --volumes`

## Notes
- This project uses **PostgreSQL only** (no Redis service).
- Default exposed ports:
  - Backend web: `${BACKEND_PORT:-8000}`
  - Frontend dev: `${FRONTEND_PORT:-5173}`
  - Frontend prod: `${FRONTEND_PORT:-8080}`
  - PostgreSQL: `${POSTGRES_PORT:-5432}`
