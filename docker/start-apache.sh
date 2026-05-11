#!/bin/bash
set -e

# ─────────────────────────────────────────────────────────────────
# start-apache.sh
#
# Ensures Apache starts cleanly inside Docker / Railway by:
#   1. Removing any conflicting MPM module symlinks
#   2. Enabling mpm_prefork (compatible with php mod)
#   3. Enabling mod_rewrite (required by CI4 router)
#   4. Setting correct file permissions on writable/
#   5. Starting Apache in the foreground (Docker requirement)
# ─────────────────────────────────────────────────────────────────

echo "[start-apache] Configuring Apache MPM..."

PORT="${PORT:-80}"

# Railway injects PORT and uses it for health checks. Apache must listen on it.
echo "[start-apache] Configuring Apache to listen on port ${PORT}..."
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf 2>/dev/null || true
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Remove any non-prefork MPM configs that could conflict
for conf in /etc/apache2/mods-enabled/mpm_*.conf; do
    [ -f "$conf" ] && [ "$(basename "$conf")" != "mpm_prefork.conf" ] && rm -f "$conf"
done
for load in /etc/apache2/mods-enabled/mpm_*.load; do
    [ -f "$load" ] && [ "$(basename "$load")" != "mpm_prefork.load" ] && rm -f "$load"
done

# Enable required modules
a2enmod mpm_prefork rewrite 2>/dev/null || true

echo "[start-apache] Setting writable directory permissions..."
mkdir -p /var/www/html/writable/{cache,logs,session,uploads,debugbar}
chown -R www-data:www-data /var/www/html/writable
chmod -R 755 /var/www/html/writable

echo "[start-apache] Starting Apache..."
exec apache2-foreground
