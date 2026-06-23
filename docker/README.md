# Docker — Peer4You Dev-Stack

Lokale Entwicklungsumgebung für Moodle 5.2. Produktions-Deploy läuft über **Coolify** auf Hetzner (gleiches Image-Konzept).

## Services (`docker-compose.yml`)

| Service | Image / Build | Zweck |
|---------|---------------|-------|
| `web` | `docker/php/Dockerfile` | Apache + PHP 8.3, Moodle-Code |
| `db` | `mariadb:11.4` | MariaDB für Moodle |
| `cron` | gleiches Dockerfile | `admin/cli/cron.php` jede Minute |
| `phpmyadmin` | optional (`COMPOSE_PROFILES=tools`) | DB-Admin lokal |

## Umgebungsvariablen (`.env`)

| Variable | Bedeutung |
|----------|-----------|
| `MOODLE_HTTP_PORT` | Host-Port (Standard: `8080`) |
| `MOODLE_WWWROOT` | Öffentliche URL (`http://localhost:8080` lokal) |
| `MOODLE_SSL_PROXY` | `1` hinter Reverse-Proxy (Coolify/Traefik) — setzt `$CFG->sslproxy` |
| `MOODLE_DB_*` | Datenbank-Zugangsdaten |
| `MOODLE_DATAROOT` | Pfad zu moodledata (`/var/moodledata` im Container) |

## Unterschied Referenzserver

| Aspekt | Lokal | Referenzserver (Coolify) |
|--------|-------|--------------------------|
| TLS | meist HTTP | HTTPS via Traefik |
| `MOODLE_SSL_PROXY` | `0` | `1` |
| `MOODLE_WWWROOT` | `http://localhost:8080` | `https://lms.schulerwebdesign.de` |
| Deploy | `docker compose up` | Git-Push → Coolify Rebuild |

Apache in `docker/php/moodle.apache.conf` wertet `X-Forwarded-Proto` aus, damit Moodle hinter dem Proxy HTTPS erkennt.

## Dateien in `docker/php/`

| Datei | Zweck |
|-------|-------|
| `Dockerfile` | PHP-Extensions, Apache, Composer, Repo-Copy |
| `entrypoint-web.sh` | moodledata-Rechte, `config.php` aus Example |
| `moodle.apache.conf` | DocumentRoot `/var/www/html/public` |
| `entrypoint-cron.sh` | Cron-Container (wartet auf `config.php`) |

## Schnellstart

Siehe [docs/02-local-setup.md](../docs/02-local-setup.md).
