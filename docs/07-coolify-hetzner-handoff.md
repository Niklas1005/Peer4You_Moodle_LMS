# Coolify on Hetzner — handoff checklist

Use this after the repo changes for Docker profiles, `config.php.example`, and `.env` handling.

## What the repository already encodes

- **phpMyAdmin** is gated behind Compose profile `tools`. Production deploys should **not** set `COMPOSE_PROFILES=tools` so phpMyAdmin never starts or exposes a port.
- **`config.php.example`** supports production URLs and DB passwords via environment variables (set in Coolify for the `web` and `cron` services to match the variables already wired in `docker-compose.yml`).
- **`/.env` is gitignored** — copy from `.env.example` locally; on the server use Coolify secrets / environment UI.

## Your steps (server + Coolify)

1. **Git** — Push a branch (e.g. `main`) that includes these changes so Coolify clones an up-to-date compose file.

2. **Coolify project** — Create a Docker Compose application, point it at this repository and the compose file at the repo root.

3. **Volumes** — Ensure deploys keep persistent volumes for `mariadb_data` and `moodledata` (same names as in `docker-compose.yml`). First deploy creates them; avoid deleting them on redeploy.

4. **Environment variables in Coolify** (minimum for Moodle once `config.php` uses the example pattern):
   - `MOODLE_WWWROOT` — exact public URL, e.g. `https://lms.example.com` (no trailing slash).
   - `MOODLE_DB_PASSWORD` — strong password; must match `MARIADB_PASSWORD` / `MOODLE_DB_PASSWORD` used for the `db` service.
   - `MOODLE_DB_NAME`, `MOODLE_DB_USER`, `MOODLE_DB_HOST` (usually `db`), `MOODLE_DB_PORT` (`3306`) if non-default.
   - `MOODLE_REVERSE_PROXY=1` and `MOODLE_SSL_PROXY=1` when Coolify terminates HTTPS in front of this stack (see `config.php.example`).
   - **Do not** set `COMPOSE_PROFILES=tools` on the shared/staging server unless you intentionally want phpMyAdmin and accept the risk.

5. **`config.php` on the server** — Either:
   - Copy `config.php.example` to `config.php` in the deployment working tree (e.g. one-off file on the server / init step), **or**
   - Complete the Moodle web installer once; then align `wwwroot` and DB settings with production.

6. **TLS** — Let Coolify terminate HTTPS. `MOODLE_WWWROOT` must use `https://` and match the browser hostname exactly.

7. **Coexistence with other sites** — Coolify routes by hostname; this stack only needs ports 80/443 on the proxy. Avoid publishing raw MariaDB/phpMyAdmin ports to the public internet.

8. **After deploy** — Smoke test: login, course page, file upload, run cron once (`docker compose exec` or Coolify console) if needed.

## Watchouts

- Changing `MOODLE_WWWROOT` after install without updating Moodle’s config can break logins and links; set it correctly before going live.
- **Shared Moodle = one codebase on disk** — Git push + Coolify redeploy updates PHP/plugins for everyone; UI changes in Moodle still apply instantly via DB/moodledata.
- Rotate any passwords that ever appeared in samples or old commits.
