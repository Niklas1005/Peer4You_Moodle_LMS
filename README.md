# Peer4You LMS

A Moodle-based e-learning platform for training peer debt counselors, developed for a partner NGO.

---

## Philosophy

- **Upgrade-safe customization** — no Moodle core modifications. All project code lives in `public/theme/p4u/` and `public/local/p4u/`. Upstream Moodle can be rebased without touching custom code.
- **Docker-first** — the full local development stack starts with a single `docker compose up`. Production deployment mirrors the same image.
- **Moodle as infrastructure** — course structure, sequential availability, and completion tracking use standard Moodle APIs; custom JS handles interactivity within that framework.

---

## Quick Start

See [`docs/02-local-setup.md`](docs/02-local-setup.md) for the complete setup guide.

```bash
cp .env.example .env          # adjust passwords
docker compose up -d --build
```

After the Moodle installer finishes:

```bash
docker compose exec web php admin/cli/cfg.php --name=theme --set=p4u
docker compose exec web php local/p4u/cli/create_training_modules.php
docker compose exec web php admin/cli/purge_caches.php
```

Moodle runs at `http://localhost:8080`. The web root is `public/`, not the repo root.

---

## Repository Structure

```
.
├── public/
│   ├── theme/p4u/          # Custom Moodle theme (Boost child)
│   └── local/p4u/          # Custom plugin: interactive modules, progress APIs
├── local/p4u/cli/          # CLI scripts — course structure provisioning
├── docker/                 # PHP/Apache image, entrypoint script
├── docker-compose.yml
├── scripts/handoff/        # .mbz export/import for production handoff
└── docs/                   # Living documentation
```

Everything else under `public/` is upstream Moodle core (GPL). See [Moodle Core](#moodle-core).

---

## Custom Code

### `public/theme/p4u/` — Theme `theme_p4u`

Boost child theme implementing the full client brand.

| Area | Details |
|------|---------|
| Layouts | Custom login, dashboard (`mydashboard`), frontpage, drawer layout |
| Templates | Mustache overrides: `login.mustache`, `mydashboard.mustache`, `loginform.mustache` |
| Styles | SCSS — `scss/p4u.scss`, `scss/login.scss`; brand color `#009BDC`, typeface DM Sans |
| Entry points | `config.php`, `lib.php`, `version.php` |

Purge the theme cache after any CSS or template change — see [`docs/05-theme-p4u-runbook.md`](docs/05-theme-p4u-runbook.md).

### `public/local/p4u/` — Plugin `local_p4u`

Core application logic plugin. Renders all six training modules and manages progress persistence.

| File | Purpose |
|------|---------|
| `module1.php` / `module1.js` | Module 1 — Introduction & Role Understanding (interactive) |
| `module2.php` / `module2.js` | Module 2 — Financial Knowledge (interactive) |
| `module5.php` / `module5.js` | Module 5 — Crisis Recognition (interactive) |
| `module_placeholder.php` | Modules 3, 4, 6 — placeholder pages (content not yet delivered) |
| `module_state.php` | REST-style API endpoint — reads and writes per-user progress |
| `progress_store.php` | Server-side persistence via Moodle User Preferences |
| `module-progress.js` | Client-side progress manager — syncs server state with localStorage cache |
| `complete_module.php` | Triggers Moodle course completion (requires quiz score ≥ 60 %) |
| `dashboard.php` | Custom learner dashboard view |

Progress is stored on two levels: per-station state in the database (`local_p4u_module{N}_progress`), with localStorage as a read-through cache. Module completion flows through standard Moodle `course_modules_completion`.

Full architecture: [`docs/09-interactive-modules-architecture.md`](docs/09-interactive-modules-architecture.md)

### `local/p4u/cli/` — CLI Scripts

| Script | Purpose |
|--------|---------|
| `create_training_modules.php` | Creates course `P4U-TEST` with 6 sections and a sequential availability chain (M1→M2→M5 for demo; full M1→M6 in production) |

Run after every fresh install or git-deploy to a new environment.

### `scripts/handoff/` — Production Handoff

Course content, quiz questions, and user accounts are **not** in Git — they travel as Moodle `.mbz` backups.

| Script | Purpose |
|--------|---------|
| `export-company-courses.sh` | Exports `.mbz` backups from the reference server |
| `export-course-content-only.php` | Exports course content without user data |
| `import-to-customer.sh` | Imports `.mbz` into the client's Moodle instance |

See [`docs/08-environments-and-customer-handoff.md`](docs/08-environments-and-customer-handoff.md).

### `docker/` — Container Setup

Three services: `web` (PHP 8.3 / Apache), `db` (MariaDB 11.4), `cron` (Moodle scheduled tasks).  
Optional `phpmyadmin` available via `COMPOSE_PROFILES=tools` (not started by default).

Custom image: `docker/php/Dockerfile` + `docker/php/entrypoint-web.sh`.

---

## Tech Stack

| Component | Version |
|-----------|---------|
| Moodle | 5.0.2 (`MOODLE_502_STABLE`) |
| PHP | 8.3 |
| Database | MariaDB 11.4 |
| Web server | Apache (containerized) |
| Runtime | Docker Compose |

---

## Documentation

| Document | Content |
|----------|---------|
| [`docs/README.md`](docs/README.md) | Documentation index |
| [`docs/01-project-overview.md`](docs/01-project-overview.md) | Scope, architecture, delivery principles |
| [`docs/02-local-setup.md`](docs/02-local-setup.md) | Step-by-step local environment setup |
| [`docs/03-customization-strategy.md`](docs/03-customization-strategy.md) | Upgrade-safe customization approach |
| [`docs/04-deployment-plan.md`](docs/04-deployment-plan.md) | Production deployment baseline |
| [`docs/05-theme-p4u-runbook.md`](docs/05-theme-p4u-runbook.md) | Theme operational runbook |
| [`docs/06-module1-implementation-notes.md`](docs/06-module1-implementation-notes.md) | Module 1 implementation notes |
| [`docs/07-coolify-hetzner-handoff.md`](docs/07-coolify-hetzner-handoff.md) | Staging server setup and watchouts |
| [`docs/08-environments-and-customer-handoff.md`](docs/08-environments-and-customer-handoff.md) | Git vs. DB vs. moodledata; production handoff |
| [`docs/09-interactive-modules-architecture.md`](docs/09-interactive-modules-architecture.md) | Module architecture, progress persistence, API reference |
| [`docs/UEBERSICHT_EIGENE_ENTWICKLUNG.md`](docs/UEBERSICHT_EIGENE_ENTWICKLUNG.md) | Component matrix and module status (working doc) |

---

## Moodle Core

This repository includes the full Moodle upstream tree (GPL). Code under `public/lib/`, `public/admin/`, `public/mod/`, and the standard themes is authored by Moodle HQ and the community — not by this project.

Original Moodle README: [`docs/upstream-moodle-readme.md`](docs/upstream-moodle-readme.md)

**License:** Moodle core — GNU GPL v3. See [moodledev.io/general/license](https://moodledev.io/general/license).  
Custom plugins (`theme_p4u`, `local_p4u`) — GNU GPL v3 (required by Moodle plugin convention).

---

*`.agents/skills/` and `skills-lock.json` are Cursor IDE development aids — not required to run Moodle.*
