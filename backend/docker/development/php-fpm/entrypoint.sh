#!/bin/sh
set -e

# Check if $UID and $GID are set, else fallback to default (1000:1000)
USER_ID=${UID:-1000}
GROUP_ID=${GID:-1000}

# (Removed chown -R /var/www as it is very slow on Windows/Mac bind mounts and fails anyway)

# Clear configurations in development, but do not block php-fpm startup if artisan fails.
echo "Clearing configurations..."
if [ -f /var/www/artisan ]; then
  php artisan config:clear || echo "Warning: config:clear failed, continuing startup."
  php artisan route:clear || echo "Warning: route:clear failed, continuing startup."
  php artisan view:clear || echo "Warning: view:clear failed, continuing startup."
else
  echo "Warning: /var/www/artisan not found, skipping cache clear commands."
fi

# Run the default command (e.g., php-fpm or bash)
exec "$@"
