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
   - `MOODLE_SSL_PROXY=1` when Coolify terminates HTTPS in front of this stack (see `config.php.example`).
   - **Do not** add `MOODLE_REVERSE_PROXY` — removed from compose; enabling reverseproxy triggers `reverseproxyabused` on Coolify.
   - **Do not** set `COMPOSE_PROFILES=tools` on the shared/staging server unless you intentionally want phpMyAdmin and accept the risk.

5. **`config.php` on the server** — Either:
   - Copy `config.php.example` to `config.php` in the deployment working tree (e.g. one-off file on the server / init step), **or**
   - Complete the Moodle web installer once; then align `wwwroot` and DB settings with production.

6. **TLS** — Let Coolify terminate HTTPS. `MOODLE_WWWROOT` must use `https://` and match the browser hostname exactly.

7. **Coexistence with other sites** — Coolify routes by hostname; this stack only needs ports 80/443 on the proxy. Avoid publishing raw MariaDB/phpMyAdmin ports to the public internet.

8. **After deploy** — Smoke test: login, course page, file upload, run cron once (`docker compose exec` or Coolify console) if needed.

## Moodle web installer (first production deploy)

Use these values when Coolify has started the stack but Moodle has no `config.php` yet.

| Installer field | Value |
|-----------------|--------|
| Datenverzeichnis | `/var/moodledata` (not `/var/www/moodledata`) |
| Datenbank-Server | `db` (not `localhost`) |
| Datenbank-Name / Nutzer | `moodle` / `moodle` |
| Datenbank-Kennwort | Same as `MOODLE_DB_PASSWORD` in Coolify |
| Datenbank-Port | `3306` |
| Unix Socket | leave empty |

**Webadresse** is greyed out by design. Moodle guesses it from the request. If it shows `http://` while users access `https://`, finish install, then set `MOODLE_WWWROOT` and `MOODLE_SSL_PROXY=1` in Coolify and align `/var/www/html/config.php` (or redeploy from `config.php.example`).

**Site admin account** — not predefined in this repo. Choose your own username (avoid predictable `admin` if possible), a strong password, and a real email you control. Store credentials in your team password manager; do not commit them. You can add more administrators later in Moodle.

Suggested for a German production site: timezone `Europe/Berlin`; email visibility for the main admin often **hidden** or **only visible to other users**.

### DB error: `Access denied for user 'moodle'@'…' (1045)`

MariaDB sets the `moodle` password only when the `mariadb_data` volume is first created. Changing `MOODLE_DB_PASSWORD` in Coolify later does not update an existing volume.

**Option A (empty site):** delete the `mariadb_data` volume in Coolify, set a strong `MOODLE_DB_PASSWORD`, redeploy, run the installer again.

**Option B (keep volume):** shell into the `db` container, connect as root (`MOODLE_DB_ROOT_PASSWORD`), then:

```sql
ALTER USER 'moodle'@'%' IDENTIFIED BY 'same-as-MOODLE_DB_PASSWORD-in-Coolify';
FLUSH PRIVILEGES;
```

Verify from the `web` container: `mariadb -h db -u moodle -p moodle`.

### Unstyled pages (no CSS — plain HTML only)

Usually **mixed content** or wrong `wwwroot`:

1. You open the site as `https://…` but `config.php` has `$CFG->wwwroot = 'http://…'` (common right after the web installer).
2. The browser blocks theme CSS/JS loaded over HTTP.

**Fix** — in Coolify set:

```env
MOODLE_WWWROOT=https://lms.schulerwebdesign.de
MOODLE_SSL_PROXY=1
```

Then shell into the **`web`** container and edit `/var/www/html/config.php` (installer-generated file):

```php
$CFG->wwwroot = 'https://lms.schulerwebdesign.de';  // no trailing slash, no /public
$CFG->sslproxy = true;
// Do not set $CFG->reverseproxy on Coolify (see "reverseproxyabused" below).
```

Remove `$CFG->reverseproxy = true;` if the installer or an earlier edit added it.

Purge caches:

```bash
php /var/www/html/admin/cli/purge_caches.php
```

Hard-refresh the browser (Ctrl+F5). In DevTools → Network, a theme CSS URL should return **200** (e.g. `…/theme/boost/style/moodle.css`), not blocked or 404.

`wwwroot` must **not** end with `/public` — Apache already uses `DocumentRoot …/public`.

If CSS still 404s after HTTPS fix, run once in `web`:  
`composer install --no-dev --classmap-authoritative`  
then purge caches again.

### Error: „Der Reverse Proxy ist aktiviert …“ (`reverseproxyabused`)

You enabled `$CFG->reverseproxy` while Coolify forwards the **same** public hostname to Moodle. That Moodle option is for load balancers where the internal `Host` differs from `wwwroot` — not for normal Coolify/Traefik.

**Fix:** delete the `MOODLE_REVERSE_PROXY` variable in Coolify (do not just disable runtime), recreate `config.php` from the current `config.php.example` (no `reverseproxy` block), keep `$CFG->sslproxy = true` and `https://` in `wwwroot`, then `php admin/cli/purge_caches.php`.

After each redeploy, `entrypoint-web.sh` copies `config.php.example` → `config.php` when missing — so the example must never set `reverseproxy` on Coolify.

## Moodle HQ site registration (after first login)

Right after install, admins often land on **Website-Administration → Website registrieren** (`/admin/registration/index.php`). This is **optional**.

### What it is

- Voluntary registration of your site with **Moodle HQ** (the organisation behind moodle.org), not part of hosting or Coolify.
- Moodle may send **anonymised usage statistics** (version, user/course counts, etc.) to help Moodle development and global statistics.
- You can opt in to **security/update emails** and marketing from Moodle partners.
- Optional listing on a public directory of Moodle sites in your country (usually unwanted for companies).

Your LMS **works fully without registering**. Courses, users, and plugins do not depend on it.

### Private company / employee training (recommended)

For an internal training site (e.g. Diakonie, P4U):

| Action | Recommendation |
|--------|----------------|
| Register now? | **No** — click **Überspringen** (Skip) unless the organisation explicitly wants Moodle HQ update emails. |
| Publish site publicly? | **No** — keep **„Meine Website nicht veröffentlichen“** if you ever register. |
| Expert tips / partner mail? | **No** for production unless desired. |
| Security release emails | Optional — your team can follow [moodle.org security](https://moodle.org) or upgrade via your own process. |

You can register or change settings later under **Website-Administration → Registrierung** (or dismiss the dashboard banner).

### If you skip registration

Nothing breaks. Continue with: create courses, enrol employees (manual upload or LDAP later), theme/plugins, cron already running in the `cron` container.

## Watchouts

- Changing `MOODLE_WWWROOT` after install without updating Moodle’s config can break logins and links; set it correctly before going live.
- **Shared Moodle = one codebase on disk** — Git push + Coolify redeploy updates PHP/plugins for everyone; UI changes in Moodle still apply instantly via DB/moodledata.
- Rotate any passwords that ever appeared in samples or old commits.
