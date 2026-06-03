# Environments, Git vs database, and customer handoff

This document is the **single reference** for what travels with Git, what lives in Moodle’s database/files, and how to move finished courses from your **company reference Moodle** to a **customer production Moodle**.

## Three environments (planned workflow)

| Environment | Role | Typical host |
|-------------|------|----------------|
| **Local** | Development (PHP/JS/theme, CLI scripts) | `localhost:8080` (Docker on dev PC) |
| **Company reference** | You and your teammate configure **modules 1–6**, quizzes, course structure, branding tests | Company network (e.g. Coolify on Hetzner: `https://lms.schulerwebdesign.de`) |
| **Customer production** | Live LMS for the customer’s employees | Customer network (separate install, later) |

Each environment has its **own** MariaDB volume and **own** `moodledata` volume. They do not sync automatically.

## What `git push` + Coolify deploy changes

| Stored in | Examples | Updated when you push to Git? |
|-----------|----------|-------------------------------|
| **Repository** | `public/local/p4u/*`, `public/theme/p4u`, `local/p4u/cli/*.php`, Docker, docs | **Yes** — after Coolify rebuild |
| **MariaDB** | Courses, sections, quiz questions (UI), users, enrolments, grades, site name | **No** |
| **moodledata** | Uploads, embedded files, some H5P, cache | **No** |
| **config.php** | `wwwroot`, DB password, `sslproxy` | **No** (gitignored; per server) |

### Rule of thumb

- **Code** (interactive modules, theme, CLI) → Git → deploy to every server that runs this repo.
- **Content you click together in Moodle** → database + moodledata → **backup/restore** (or rebuild with CLI + manual work).

### P4U modules 1–6 specifically

| Part | Where it lives |
|------|----------------|
| Interactive UI (`module1.php`, `module1.js`, placeholders) | **Git** |
| CLI skeleton (`create_modul1.php`, `create_training_modules.php`) | **Git** — run on each server to create sections/URL activities |
| Wording, quiz banks, extra activities, enrolments, completion rules | **Database** (configure on company server, then export) |
| PDFs/images attached in Moodle | **moodledata** (included in course `.mbz` backup when you include user/files) |

## Current plan (agreed workflow)

1. **Deploy code** to the company reference server (Coolify + this repo).
2. **Configure modules 1–6** on that live Moodle (UI + CLI as needed). Treat it as the **golden content** instance.
3. When content is ready, run the **handoff export** (see below) to produce `.mbz` backup files.
4. On the **customer** Moodle (same codebase version, fresh or empty courses):
   - Deploy the **same Git tag/branch** as company reference.
   - Run **import** script or Moodle restore UI.
   - Adjust customer-specific settings (site name, support email, `wwwroot`, users).

Do **not** rely on copying the whole database to the customer unless you intentionally want a full clone (users, logs, wrong URLs). Prefer **course backups** (`.mbz`).

## Handoff: company Moodle → customer Moodle

Automated helpers live in `scripts/handoff/`:

- `export-company-courses.sh` — backup listed courses on the **source** server
- `import-to-customer.sh` — restore those backups on the **target** server
- `courses.conf.example` — list course shortnames to export (copy to `courses.conf`)

### Prerequisites (both sides)

- Same **Moodle major version** (e.g. 5.2) and same **plugin set** (especially `local_p4u`, theme `p4u` if used).
- Customer server: codebase deployed from Git **before** restore.
- Shell access to `web` container (Coolify terminal or SSH + `docker compose exec web bash`).
- On source: note each course **shortname** (e.g. `P4U-TEST`) in `courses.conf`.

### Export (company server)

```bash
# Inside web container, from /var/www/html
cp scripts/handoff/courses.conf.example scripts/handoff/courses.conf
# Edit courses.conf: one shortname per line

bash scripts/handoff/export-company-courses.sh
# Archives appear under /var/moodledata/handoff/export-YYYYMMDD/
```

Copy the export folder off the server (SCP, SFTP, encrypted storage). **Do not commit `.mbz` files to Git** (size + may contain user data).

### Import (customer server)

```bash
# Copy export folder into customer web container, e.g. /var/moodledata/handoff/import/
bash scripts/handoff/import-to-customer.sh /var/moodledata/handoff/import/export-YYYYMMDD
```

Or per course via UI: **Website-Administration → Kurse wiederherstellen**.

### After import on customer

1. `config.php`: customer `https://` URL, `sslproxy` (Coolify), no `reverseproxy`.
2. `php admin/cli/purge_caches.php`
3. Smoke test: open each module link, quiz attempt, file download.
4. Create customer admin/users; enrol employees (no need to migrate company test users).

## Alternative: manual UI backup

For one course without scripts:

1. Course → **Mehr** → **Sicherung** → export `.mbz`.
2. Customer site → **Kurse wiederherstellen** → upload.

Use the same options each time (include activities; include user data only if you need enrolments on customer — usually **no** for go-live).

## What NOT to do

| Avoid | Why |
|-------|-----|
| Expect `git push` to copy courses | Courses are in MariaDB |
| Copy `mariadb_data` volume to customer without planning | Brings wrong users, URLs, site name |
| Restore company `.mbz` without matching code version | Restore errors or missing plugins |
| Commit `.mbz` / passwords to Git | Security and repo size |

## Agreed handoff decisions (project)

| Topic | Decision |
|-------|----------|
| **Course structure** | **One course** (shortname `P4U-TEST`) with **six module sections** inside it — not six separate courses. |
| **Users in backup** | **Content only** — no learners, no enrolments. Customer company admin creates employees later. |
| **Admin on customer** | **One admin** created manually at go-live (not part of import). |
| **Customer infrastructure** | VM (or similar) on customer network with the **same 3 Docker services** (`web`, `db`, `cron`). Initial setup: `git clone` (or HTTP download) of this repo, then `docker compose up`. Git is for **deploying code**, not for moving course content. |
| **Theme / redesign** | Theme `p4u` lives in **Git** (`public/theme/p4u`). Activate on each server with CLI (see below). Fine-tuning that cannot be imported is done at the **on-site appointment** with the customer. |

### Theme on company vs customer server

| What | How it travels |
|------|----------------|
| Theme **files** (SCSS, templates, `theme/p4u`) | **Git clone / deploy** on customer VM |
| Theme **activated** (`$CFG->theme = p4u`) | Database setting — run on customer after install: `php admin/cli/cfg.php --name=theme --set=p4u` then `purge_caches.php` |
| Site name, logo upload, front page blocks | Mostly **database / moodledata** — optional in course backup; customer-specific items often set at appointment |

**Low-effort path:** configure theme on company server for testing; on customer VM deploy same Git tag + two CLI commands to enable `p4u`. Any customer-only logo/colours → appointment.

### Two kinds of “questions” (see FAQ below)

- **Interactive module** (`module1.js`, etc.) → **Git**
- **Moodle Quiz** activities in the course → **`.mbz` export**

## Customer VM setup (outline)

1. Install Docker on customer VM.
2. `git clone <repo-url>` (or download release archive) — use the **same tag/commit** as company sign-off.
3. Copy `.env.example` → configure secrets, `MOODLE_WWWROOT`, `MOODLE_SSL_PROXY` if needed.
4. `docker compose up -d --build`
5. Complete Moodle install **or** copy `config.php` pattern from company (customer URL).
6. Create **customer admin** (installer or manual).
7. Enable theme: `php admin/cli/cfg.php --name=theme --set=p4u`
8. Import course: `bash scripts/handoff/import-to-customer.sh …`
9. Appointment: site name, logo, anything not in backup.

## FAQ: Where do quiz questions live? (Type A vs Type B)

| | **Type A — Custom JS module** | **Type B — Moodle Quiz activity** |
|---|-------------------------------|-----------------------------------|
| **Where** | `public/local/p4u/module1.js` (etc.) | Course activity “Test” / Quiz in Moodle UI |
| **To customer** | **Git** clone / deploy | **`.mbz`** content export |
| **Who edits** | Developers in repo | Trainers in Moodle (or dev + export) |
| **Gradebook / Moodle tracking** | Limited unless integrated | Native Moodle |

**Project status (2026-05-19):** Team **prefers Type B** for modules (standard Moodle quizzes), but **manager decision pending** — see `Notizen/Entwicklung To-Do.md`.

**Current codebase:** Modul 1 interactive page still uses **Type A** quiz logic in JS. Moving to Type B means building quizzes as Moodle activities in the `P4U-TEST` course (included in content-only export).

**If manager chooses Type B:** keep interactive pages for learning steps; use Moodle Quiz for end-of-module tests; rely on handoff export, not Git, for question text.

**If manager chooses Type A:** keep questions in JS; Git deploy to customer VM is enough for quiz text changes.

## Open items (optional)

- **Version pinning:** tag Git when company content is signed off; customer VM uses that tag.
- **Shared question banks** outside the course — only relevant if you use global question categories (not typical for one course).

## Related docs

- `04-deployment-plan.md` — deployment checklist and Git vs volume table
- `07-coolify-hetzner-handoff.md` — Coolify install, CSS, DB, registration
- `06-module1-implementation-notes.md` — Modul 1 didactic/technical notes
- `scripts/handoff/README.md` — script usage
