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

## Prototype Token Mapping
- Brand primary: `#009BDC` (`--p4u-brand-600`)
- Brand hover/depth: `#0080B8` (`--p4u-brand-700`)
- Light accent: `#E8F7FD` (`--p4u-brand-50`)
- Warm surfaces: `#FDFBF9`, `#F5F0EB`, `#E8E4DF`
- Text base: `#1E1B2E`
- Typography baseline: `"DM Sans", system-ui, -apple-system, sans-serif`
- Display/headings (login): `"Outfit", system-ui, sans-serif`
- Login background gradient: `#004768` → `#0A1929` → `#0E2A3E`

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
```

Validation: open `/login/index.php` logged out — centered card, no split-screen, Diakonie footer text below card.

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
- Dashboard content remains Moodle block/activity driven and is framed with prototype-aligned wrapper sections.
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
