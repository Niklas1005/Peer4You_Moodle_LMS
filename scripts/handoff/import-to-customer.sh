#!/usr/bin/env bash
# Restore .mbz course backups from an export directory (customer production server).
# Usage: bash scripts/handoff/import-to-customer.sh /var/moodledata/handoff/import/export-YYYYMMDD-HHMMSS

set -euo pipefail

MOODLE_ROOT="${MOODLE_ROOT:-/var/www/html}"
IMPORT_DIR="${1:-}"
CATEGORY_ID="${CATEGORY_ID:-1}"

cd "$MOODLE_ROOT"

if [[ -z "$IMPORT_DIR" || ! -d "$IMPORT_DIR" ]]; then
  echo "Usage: $0 /path/to/export-directory" >&2
  echo "Example: $0 /var/moodledata/handoff/import/export-20260519-120000" >&2
  exit 1
fi

shopt -s nullglob
files=("$IMPORT_DIR"/*.mbz)
if [[ ${#files[@]} -eq 0 ]]; then
  echo "No .mbz files in ${IMPORT_DIR}" >&2
  exit 1
fi

echo "Restoring ${#files[@]} backup(s) to category id ${CATEGORY_ID}"

for mbz in "${files[@]}"; do
  echo "Restoring: $(basename "$mbz")"
  php admin/cli/restore_backup.php \
    --file="$mbz" \
    --categoryid="$CATEGORY_ID"
done

php admin/cli/purge_caches.php
echo "Done. Verify courses in the UI and test module links."
