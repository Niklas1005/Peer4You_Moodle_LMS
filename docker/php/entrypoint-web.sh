#!/bin/sh
set -e

if [ ! -f /var/www/html/public/index.php ]; then
  echo "FATAL: /var/www/html/public/index.php is missing. Check Git clone / base directory." >&2
  echo "--- /var/www/html (top) ---" >&2
  ls -la /var/www/html 2>&1 | head -30 >&2 || true
  echo "--- /var/www/html/public ---" >&2
  ls -la /var/www/html/public 2>&1 | head -20 >&2 || true
fi

mkdir -p /var/moodledata/sessions /var/moodledata/cache /var/moodledata/temp /var/moodledata/localcache

if [ ! -f /var/www/html/config.php ] && [ -f /var/www/html/config.php.example ]; then
  cp /var/www/html/config.php.example /var/www/html/config.php
fi

chown -R www-data:www-data /var/moodledata /var/www/html

exec apache2-foreground
