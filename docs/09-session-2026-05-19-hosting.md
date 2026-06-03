# Session log — 2026-05-19: Company Moodle on Coolify/Hetzner

Summary of work and decisions from the deployment/debugging session. Full handoff workflow: `08-environments-and-customer-handoff.md`.

## Environment

| Item | Value |
|------|--------|
| **Role** | Company reference Moodle (configure modules 1–6 here before customer VM) |
| **URL** | `https://lms.schulerwebdesign.de` |
| **Stack** | Docker Compose (`web`, `db`, `cron`) via Coolify on Hetzner |
| **Repo** | `Dateien/LMS` |

## Completed today

### Moodle web installer (first production install)

| Step | Setting / action |
|------|------------------|
| Datenverzeichnis | `/var/moodledata` (not `/var/www/moodledata`) |
| DB host | `db` (not `localhost`) |
| DB credentials | Match `MOODLE_DB_PASSWORD` in Coolify |
| DB password mismatch (1045) | **Fix B:** `ALTER USER 'moodle'@'%'` in `db` container |
| Site admin | Created manually (not in Git) |
| Site settings | e.g. Peers for You, `Europe/Berlin`, self-registration off |
| Moodle HQ registration | **Skipped** (optional) |

### `config.php` / HTTPS / CSS

| Issue | Fix |
|-------|-----|
| Unstyled pages (no CSS) | `wwwroot` must be `https://…`; set `$CFG->sslproxy = true` |
| `reverseproxyabused` error | **Do not** set `$CFG->reverseproxy` on Coolify; only `sslproxy` |
| Coolify env | `MOODLE_WWWROOT`, `MOODLE_SSL_PROXY=1`; **no** `MOODLE_REVERSE_PROXY` |
| After edit | `php admin/cli/purge_caches.php`, hard refresh browser |

### Apache (repo change)

- `docker/php/moodle.apache.conf`: `SetEnvIf X-Forwarded-Proto "https" HTTPS=on` for future installs.

### Documentation and scripts added/updated

| File | Purpose |
|------|---------|
| `docs/07-coolify-hetzner-handoff.md` | Installer, DB, CSS, reverseproxy, HQ registration |
| `docs/08-environments-and-customer-handoff.md` | Git vs DB; company → customer; agreed decisions |
| `docs/04-deployment-plan.md` | Git vs volumes (pointer to 08) |
| `scripts/handoff/export-company-courses.sh` | Export courses from `courses.conf` |
| `scripts/handoff/export-course-content-only.php` | `.mbz` without users/enrolments |
| `scripts/handoff/import-to-customer.sh` | Restore on customer VM |
| `config.php.example` | sslproxy only (no reverseproxy) |

## Agreed product / process decisions

| Topic | Decision |
|-------|----------|
| Course structure | **One course** (`P4U-TEST`), **six module sections** |
| Handoff content | **Content-only** backup; customer admin creates users |
| Customer deploy | **VM** on customer network, same 3 containers, `git clone` + import `.mbz` |
| Theme `p4u` | Files via **Git**; enable with `admin/cli/cfg.php --name=theme --set=p4u` |
| Local dev content | **Not** on server via Git — use export or configure on company server |

## Open decision: quiz content model (Type A vs B)

**Pending manager approval** — tracked in `Notizen/Entwicklung To-Do.md`.

| Type | Where questions live | How they reach customer |
|------|----------------------|-------------------------|
| **A** | `public/local/p4u/module1.js` (custom interactive page) | Git deploy |
| **B** | Moodle **Quiz** activities in the course | Course `.mbz` export |

**Team tendency:** Type **B** (standard Moodle quizzes, aligned with “modules like quiz activities”).  
**Current implementation:** Modul 1 still has Type A quiz in JS; Type B requires building quizzes in Moodle UI + content-only export.

See FAQ in `08-environments-and-customer-handoff.md`.

## Next steps (team)

1. Configure modules 1–6 on **company** Moodle (`lms.schulerwebdesign.de`).
2. Manager decides Type A vs B for assessments → update implementation plan.
3. Enable/test theme `p4u` on company server.
4. When content is signed off: run `scripts/handoff/export-company-courses.sh`.
5. Customer VM: clone repo, Docker up, manual admin, import `.mbz`, theme CLI.

## Related Obsidian

- [[02_Aktivitaetslog/AKTIVITAETSLOG]] — entry 2026-05-19
- [[Entwicklung To-Do]] — open decision quiz type
- [[03_Projektwissen/AKTUELLER_STAND]]
