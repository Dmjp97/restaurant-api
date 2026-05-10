#!/bin/sh
set -eu

# Remove any MPMs other than prefork before Apache starts.
a2dismod -f mpm_worker mpm_event mpm_itk 2>/dev/null || true
find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' ! -name 'mpm_prefork.load' -delete 2>/dev/null || true
find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.conf' ! -name 'mpm_prefork.conf' -delete 2>/dev/null || true
a2enmod mpm_prefork rewrite >/dev/null

exec apache2-foreground