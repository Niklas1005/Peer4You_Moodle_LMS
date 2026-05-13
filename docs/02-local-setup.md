# Local Setup Guide (Windows + Docker)

## 1) Prerequisites
- Docker Desktop installed and running.
- Git installed.
- Ports available: `8080` (Moodle), `8081` (phpMyAdmin).
- Runtime target: PHP `8.3+` (required by Moodle 5.2+).

## 2) Prepare local environment
1. In project root, copy `.env.example` to `.env`.
2. Update secure values in `.env`:
   - `MOODLE_DB_PASSWORD`
   - `MOODLE_DB_ROOT_PASSWORD`
3. Keep `.env` private and never commit secrets (`.env` is listed in `.gitignore`).
4. **phpMyAdmin** is optional: `.env.example` sets `COMPOSE_PROFILES=tools` so phpMyAdmin starts locally. Remove that line if you do not want phpMyAdmin.
5. For production-like Moodle URLs locally, set `MOODLE_WWWROOT=http://localhost:8080` (or copy `config.php.example` to `config.php` after install so PHP reads the same URL from the environment).

## 3) Start Moodle stack
Run in project root:

```bash
docker compose up -d --build
```

Recommended branch for productive baseline:

```bash
git checkout MOODLE_502_STABLE
```

## 4) Access services
- Moodle: `http://localhost:8080`
- phpMyAdmin: `http://localhost:8081` — only when `COMPOSE_PROFILES=tools` is set (see `.env.example`). Without that profile, phpMyAdmin does not run (safer default for servers).
- Note: this repository serves Moodle from `public/` as web root.

## 5) Complete Moodle web installer
In Moodle installer:
- DB type: `MariaDB` / `MySQL`.
- DB host: `db`.
- DB name/user/password from `.env`.
- Data directory: `/var/moodledata`.

After install, Moodle generates `config.php` in project root.
If the installer reports it cannot create `/var/moodledata`, restart stack:

```bash
docker compose up -d --build
```

The containers now enforce writable ownership for `www-data` automatically.

## 6) Cron validation
Cron container runs every minute and executes:
- `php /var/www/html/admin/cli/cron.php`
- Before first Moodle install, it waits until `config.php` exists.

Check logs:

```bash
docker compose logs -f cron
```

## 7) Stop and restart
- Stop: `docker compose down`
- Start: `docker compose up -d`

Data persists in Docker volumes (`mariadb_data`, `moodledata`).

## 8) Troubleshooting
- Web not reachable: check `docker compose ps` and logs.
- Installer DB errors: verify `.env` and `db` health.
- Permissions issues: ensure Docker has file access to project path.
- If you need a clean reinstall, reset local DB/data volumes:

```bash
docker compose down -v
docker compose up -d --build
```

- Install production Composer dependencies (required for Moodle check):

```bash
docker compose exec -T web composer install --no-dev --classmap-authoritative
```
