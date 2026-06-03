#!/usr/bin/env bash
# Export Moodle courses listed in courses.conf to .mbz files (company reference server).
# Run inside the web container from Moodle root: bash scripts/handoff/export-company-courses.sh

set -euo pipefail

MOODLE_ROOT="${MOODLE_ROOT:-/var/www/html}"
CONF="${MOODLE_ROOT}/scripts/handoff/courses.conf"
STAMP="$(date +%Y%m%d-%H%M%S)"
DEST="${MOODLE_DATA:-/var/moodledata}/handoff/export-${STAMP}"

cd "$MOODLE_ROOT"

if [[ ! -f "$CONF" ]]; then
  echo "Missing ${CONF}. Copy courses.conf.example to courses.conf and list course shortnames." >&2
  exit 1
fi

mkdir -p "$DEST"
echo "Export directory: ${DEST}"

while IFS= read -r line || [[ -n "$line" ]]; do
  line="${line%%#*}"
  line="$(echo "$line" | xargs)"
  [[ -z "$line" ]] && continue

  outfile="${DEST}/${line}.mbz"
  echo "Backing up course shortname: ${line} -> ${outfile}"

  # Content only: no users/enrolments (customer creates accounts locally).
  php scripts/handoff/export-course-content-only.php \
    --courseshortname="$line" \
    --destination="$DEST"

  # Moodle names file from course; ensure predictable name if needed
  latest="$(ls -t "$DEST"/*.mbz 2>/dev/null | head -1 || true)"
  if [[ -n "$latest" && "$latest" != "$outfile" && -f "$latest" ]]; then
    mv -f "$latest" "$outfile"
  fi
done < "$CONF"

cat > "${DEST}/README.txt" <<EOF
P4U company course export
Created: ${STAMP}
Moodle root: ${MOODLE_ROOT}

Restore on customer server:
  bash scripts/handoff/import-to-customer.sh ${DEST}

Or upload each .mbz via Website-Administration -> Kurse wiederherstellen.
EOF

echo "Done. Files:"
ls -la "$DEST"
