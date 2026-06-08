# Theme P4U Runbook

## Purpose
This runbook documents how to operate and evolve the prototype-aligned Moodle frontend delivered as `theme_p4u`.

## Scope
- Upgrade-safe frontend customization for:
  - Frontpage
  - Dashboard (`/my/`)
  - Login (`/login/index.php`)
- Based on Moodle `MOODLE_502_STABLE` and Boost parent theme.

## Source Structure
- Theme root: `public/theme/p4u`
- Main files:
  - `version.php`
  - `config.php`
  - `lib.php`
  - `scss/p4u.scss`
  - `layout/frontpage.php`
  - `layout/mydashboard.php`
  - `layout/login.php`
  - `templates/frontpage.mustache`
  - `templates/mydashboard.mustache`
  - `templates/login.mustache`
  - `templates/core/login_layout.mustache`
  - `templates/core/loginform.mustache`
  - `scss/login.scss`

## Container paths (Coolify / Docker)

Apache `DocumentRoot` is `/var/www/html/public` — Moodle `dirroot` is **`/var/www/html/public`**, not `/var/www/html`.

| What | Path in `web` container |
|------|-------------------------|
| Moodle code (themes, login, …) | `/var/www/html/public/` |
| Theme P4U | `/var/www/html/public/theme/p4u/` |
| CLI scripts | `/var/www/html/admin/cli/` (repo root; loads config → `public`) |
| Repo root (docker, docs, …) | `/var/www/html/` — **no** `theme/` here |

Verify login theme files after deploy:

```bash
grep -A4 "'login'" /var/www/html/public/theme/p4u/config.php
ls -la /var/www/html/public/theme/p4u/layout/login.php
grep plugin->version /var/www/html/public/theme/p4u/version.php
```

## Diakonie colour tokens (light mode, diakonie-bad-ra.de)
- Brand primary: `#5A2572` (`--p4u-brand-600`)
- Brand hover/depth: `#481E5B` (`--p4u-brand-700`)
- Brand mid: `#8C4B9E` (`--p4u-brand-500`)
- Soft tint: `#F4EDF7` (`--p4u-brand-50`), border tint `#DED3E3` (`--p4u-brand-100`)
- Accent gold: `#FABA00` (`--p4u-accent`)
- Warm surfaces: `#FDFBF9`, `#F5F0EB`, `#E8E4DF`
- Text base: `#333333` (`--p4u-ink`), muted `#6B7280`
- Typography: `"DM Sans", system-ui, -apple-system, sans-serif`
- Login background: light warm gradient (`--p4u-warm-50` → white → `--p4u-brand-50`)

## Login Page

The login layout overrides Boost's split-screen via theme templates only (no core edits):

- `config.php`: `login` layout uses `theme_p4u` (no Boost delegation).
- `layout/login.php` + `templates/login.mustache`: minimal page shell, no Boost footer.
- `templates/core/login_layout.mustache`: centered white card on brand gradient with plus-pattern and glow orbs (aligned with `Dateien/entwuerfe_und_artefakte/prototype/index.html`).
- `templates/core/loginform.mustache`: P4U form styling; Moodle features retained (forgot password, guest, OAuth/identity providers, cookies notice, language menu, signup).
- `scss/login.scss`: `.pagelayout-login` background, `.p4u-login-card`, inputs/buttons; respects `prefers-reduced-motion`.

After login template/CSS changes:

```bash
docker compose exec -T web php /var/www/html/admin/cli/upgrade.php --non-interactive
docker compose exec -T web php /var/www/html/admin/cli/purge_caches.php
docker compose exec -T web php /var/www/html/admin/cli/build_theme_css.php --themes=p4u
```

Login styles live in `scss/login.scss` (SCSS pipeline) **and** `style/p4u.css` (static `$THEME->sheets` fallback). If only templates render but login looks unstyled, purge caches and rebuild theme CSS; the static sheet must include `.p4u-login-*` rules.

Validation: open `/login/index.php` logged out — light Diakonie background, centered white card, styled inputs/buttons, Diakonie footer text below card.

## Enable / Re-Enable Theme
Run in project root:

```bash
docker compose exec -T web php /var/www/html/admin/cli/cfg.php --name=theme --set=p4u
docker compose exec -T web php /var/www/html/admin/cli/purge_caches.php
```

## Install Theme After New Files
If new plugin files are added:

```bash
docker compose exec -T web php /var/www/html/admin/cli/upgrade.php --non-interactive
docker compose exec -T web php /var/www/html/admin/cli/maintenance.php --disable
docker compose exec -T web php /var/www/html/admin/cli/purge_caches.php
```

## Dashboard/Frontpage Composition Notes
- Frontpage content remains Moodle-driven via `{{{ output.main_content }}}` and can be composed in Site Administration.
- Dashboard (`/my/`) uses **Boost drawers** layout (`drawers.php`) — standard Moodle blocks and `block_myoverview`, no custom `mydashboard` overlay.
- `local_p4u` dashboard JS overlay is disabled; block styling via `local/p4u/dashboard.css` remains optional.
- To adapt widgets quickly, prefer block placement/configuration before adding custom plugin logic.

## Validation Checklist
- Theme active: `admin/cli/cfg.php --name=theme` returns `p4u`.
- PHP syntax validation:
  - `php -l` across `public/theme/p4u/**/*.php`
- Role checks:
  - Admin view
  - Teacher view
  - Student view
- Responsive check:
  - Mobile breakpoint under `991px`
- No core file changes outside `public/theme/p4u`.

## Rollback
Switch back to Boost:

```bash
docker compose exec -T web php /var/www/html/admin/cli/cfg.php --name=theme --set=boost
docker compose exec -T web php /var/www/html/admin/cli/purge_caches.php
```
