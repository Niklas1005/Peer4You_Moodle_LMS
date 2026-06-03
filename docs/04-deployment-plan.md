# Deployment Plan (Local to Production)

## Environments
- **Local**: developer setup with Docker.
- **Staging**: production-like environment for validation.
- **Production**: company usage.

## Migration Checklist
1. Provision server (Linux recommended).
2. Install web server, PHP runtime, DB server, TLS certificates.
3. Deploy Moodle code (tagged release/approved branch).
4. Create production `moodledata` path outside web root.
5. Import database from staging or initialize fresh.
6. Configure `config.php` for production domain and paths.
7. Configure cron (`admin/cli/cron.php` every minute).
8. Set backup jobs:
   - DB backups
   - `moodledata` backups
9. Run smoke tests (login, course access, uploads, notifications).

## Coolify / Docker Compose staging

- Prefer the compose defaults: **no** `COMPOSE_PROFILES=tools` on internet-facing servers (phpMyAdmin stays off).
- Use `config.php.example` plus Coolify environment variables for `MOODLE_WWWROOT` and database credentials (see `07-coolify-hetzner-handoff.md`).

## What Git deploy updates vs what stays in Docker volumes

> **Full workflow (company server → customer):** see `08-environments-and-customer-handoff.md` and `scripts/handoff/`.

Coolify redeploy = new **code image** from the repository. It does **not** copy your local Moodle **database** or **moodledata** to the server.

| Lives in | Examples | Arrives on server when you `git push`? |
|----------|----------|----------------------------------------|
| **Git repo** | `public/local/p4u/` (module pages, JS, CSS), `public/theme/p4u`, `local/p4u/cli/*.php`, Docker/PHP config | **Yes** — after Coolify rebuild/redeploy |
| **MariaDB volume** (`mariadb_data`) | Courses, sections, quiz questions created in the UI, users, enrolments, grades, most site settings | **No** — separate per environment (local vs Hetzner) |
| **moodledata volume** | Uploaded PDFs/images, cached files, draft content files, H5P packages stored as files | **No** — separate per environment |
| **`config.php`** | `wwwroot`, DB password, `sslproxy` | **No** (gitignored) — configure per server |

Production was installed **fresh** on Hetzner, so its database is empty of your local courses unless you **migrate** them.

### Typical P4U / Modul 1 split

- **In Git:** interactive UI (`public/local/p4u/module1.php`, `module1.js`), theme, CLI scripts (`create_modul1.php`).
- **In DB (local only until migrated):** course shell `P4U-TEST`, activity placement, enrolments, any quiz/questions built with Moodle’s quiz editor, site name, user accounts.
- **In moodledata:** files attached to courses, module intro images, etc.

Push + redeploy makes **code** available on the server; you still run CLI and/or restore a backup to recreate **course structure**, and import DB/backup for **UI-authored content**.

## Moving local content to production

Pick one approach:

### A) Course backup (recommended for one course)

1. **Local:** course → **Sicherung** → export `.mbz` (include activities and user data only if needed).
2. **Server:** as admin → **Website-Administration → Kurse wiederherstellen** → upload `.mbz`.
3. Fix links if needed; enrol users on production.

### B) Full database copy (clone entire local site)

1. Local: `docker compose exec db mariadb-dump -u moodle -p moodle > moodle-backup.sql`
2. Copy SQL to server; import into production `db` (often after emptying or on a fresh volume).
3. Update `config.php` `wwwroot` to production HTTPS URL; run `php admin/cli/purge_caches.php`.

Use only when you want a **full clone**. Risk: local URLs and user emails in DB; test on staging first.

### C) Rebuild structure with CLI on the server (code already deployed)

```bash
php /var/www/html/local/p4u/cli/create_modul1.php
# optional: php /var/www/html/local/p4u/cli/create_training_modules.php
```

Creates/reuses course `P4U-TEST` and the URL activity pointing at `/local/p4u/module1.php`. Does **not** copy quiz bank rows you added only in the local UI unless that content lives in the JS/PHP in Git.

### D) Recreate manually on production

Same clicks as local; no migration file.

**Practical order for this project:** deploy Git (Coolify) → run `create_modul1.php` on server → restore `.mbz` if you used Moodle quiz/UI content → purge caches.

## Security Baseline
- HTTPS enforced.
- Strong admin credentials + MFA if available.
- Least-privilege DB users.
- Regular patch cycle for OS, PHP, DB, Moodle, plugins.
- Log monitoring and alerting.

## Rollback Strategy
- Keep previous application build and DB backup snapshot.
- Document exact rollback sequence before release.
- Validate rollback in staging at least once.
