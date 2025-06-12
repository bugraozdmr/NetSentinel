#!/bin/sh
set -e

echo "Waiting for MySQL to be available..."
timeout=30
while ! mysqladmin ping -h"$DB_HOST" --silent; do
  timeout=$((timeout - 1))
  if [ $timeout -le 0 ]; then
    echo "MySQL did not become available in time"
    exit 1
  fi
  sleep 1
done

echo "MySQL ready, running migrations..."

php /var/www/html/app/migrations/10_06_25_create_servers.php

echo "Migration completed, starting Apache..."

exec "$@"
