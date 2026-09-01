#!/bin/bash
set -e

echo "==> Esperando a MySQL..."
until mysqladmin ping -h"${DB_HOST:-db}" -u"${DB_USER:-lockmaster}" -p"${DB_PASSWORD:-lockmaster}" --silent 2>/dev/null; do
    sleep 2
done
echo "==> MySQL listo."

# Claves JWT (config/jwt está en .gitignore, así que no vienen en el repo)
if [ ! -f config/jwt/private.pem ]; then
    echo "==> Generando par de claves JWT..."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
fi

echo "==> Aplicando migraciones..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

if [ "${LOAD_FIXTURES}" = "1" ]; then
    echo "==> Cargando fixtures (esto borra los datos actuales)..."
    php bin/console doctrine:fixtures:load --no-interaction
fi

php bin/console cache:clear --no-interaction
chown -R www-data:www-data var config/jwt

exec "$@"
