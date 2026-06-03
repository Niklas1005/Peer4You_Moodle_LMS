# Company → customer course handoff scripts

Export course backups (`.mbz`) from the **company reference** Moodle and restore them on the **customer** Moodle.

Requires: shell in the `web` container, working `config.php`, admin user.

## Setup

```bash
cd /var/www/html
cp scripts/handoff/courses.conf.example scripts/handoff/courses.conf
```

Edit `courses.conf`: one **course shortname** per line (comments with `#` allowed).

Example (one course, six module sections):

```
P4U-TEST
```

Exports are **content-only** (no users/enrolments). Customer admin creates users later.

## Export (source / company server)

```bash
bash scripts/handoff/export-company-courses.sh
```

Output directory (inside container):

`/var/moodledata/handoff/export-YYYYMMDD-HHMMSS/`

Copy that folder to your machine or directly to the customer server (encrypted).

## Import (target / customer server)

Place the export folder on the customer server, e.g.:

`/var/moodledata/handoff/import/export-20260519-120000/`

Then:

```bash
bash scripts/handoff/import-to-customer.sh /var/moodledata/handoff/import/export-20260519-120000
```

Optional: set target category id (default `1` = Miscellaneous):

```bash
CATEGORY_ID=2 bash scripts/handoff/import-to-customer.sh /path/to/export-dir
```

After import on customer VM:

```bash
php admin/cli/cfg.php --name=theme --set=p4u
php admin/cli/purge_caches.php
```

Create the customer admin account in the UI (not included in backup).

## Manual fallback

Course → **Sicherung** → download `.mbz`  
Customer → **Website-Administration → Kurse wiederherstellen**

See `docs/08-environments-and-customer-handoff.md`.
