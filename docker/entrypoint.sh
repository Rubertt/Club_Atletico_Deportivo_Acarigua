#!/bin/bash
# ===========================================================================
# Entrypoint del contenedor app:
#   1. Espera a que MySQL/MariaDB esté listo.
#   2. Si la BD está vacía y AUTO_INSTALL_DB=true → ejecuta database/install.php
#   3. Arranca Apache (apache2-foreground, via CMD)
# ===========================================================================
set -e

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-club_atletico_db_normalized}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
AUTO_INSTALL_DB="${AUTO_INSTALL_DB:-false}"

log() { echo "[entrypoint] $*"; }
err() { echo "[entrypoint][ERROR] $*" >&2; }

# --------------------------------------------------------------------------
# 1. Esperar a MySQL
# --------------------------------------------------------------------------
log "Esperando a MySQL en $DB_HOST:$DB_PORT..."
MAX_WAIT=60
i=0
until mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" -u"$DB_USER" -p"$DB_PASS" --silent 2>/dev/null; do
    i=$((i + 1))
    if [ "$i" -ge "$MAX_WAIT" ]; then
        err "MySQL no respondió tras ${MAX_WAIT}s. Abortando."
        exit 1
    fi
    sleep 1
done
log "MySQL está listo ✓"

# --------------------------------------------------------------------------
# 2. Crear .env a partir de variables de entorno si no existe
# --------------------------------------------------------------------------
if [ ! -f /var/www/html/.env ]; then
    log "Generando .env desde variables de entorno..."
    cat > /var/www/html/.env <<EOF
APP_NAME="${APP_NAME:-Club Atlético Deportivo Acarigua}"
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost:8080}
APP_TIMEZONE=${APP_TIMEZONE:-America/Caracas}

DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}

JWT_SECRET=${JWT_SECRET:-change_me_to_64_hex_chars_in_production}
JWT_TTL=${JWT_TTL:-7200}
JWT_REFRESH_TTL=${JWT_REFRESH_TTL:-604800}

UPLOAD_MAX_SIZE=${UPLOAD_MAX_SIZE:-2097152}
UPLOAD_ALLOWED_MIME=${UPLOAD_ALLOWED_MIME:-image/jpeg,image/png,image/webp}
EOF
    chown www-data:www-data /var/www/html/.env
    chmod 640 /var/www/html/.env
fi

# --------------------------------------------------------------------------
# 3. Instalación automática de BD (primera vez)
# --------------------------------------------------------------------------
if [ "$AUTO_INSTALL_DB" = "true" ]; then
    # Para la instalación usamos root (tiene permisos completos de DDL).
    # Tras instalar, la app conecta como DB_USER (usuario limitado).
    ROOT_USER="root"
    ROOT_PASS="${DB_ROOT_PASSWORD:-}"

    TABLE_COUNT=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u"$ROOT_USER" -p"$ROOT_PASS" \
        -Nse "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_NAME'" 2>/dev/null || echo "0")

    if [ -z "$TABLE_COUNT" ] || [ "$TABLE_COUNT" = "0" ]; then
        log "Base de datos vacía. Instalando schema + seeds como root..."

        # Schema (quitamos DROP/CREATE DATABASE + USE, la BD ya existe)
        sed -e '/^DROP DATABASE/d' -e '/^CREATE DATABASE/d' -e '/^USE /d' \
            /var/www/html/database/normalized_schema.sql \
            | mysql -h "$DB_HOST" -P "$DB_PORT" -u"$ROOT_USER" -p"$ROOT_PASS" "$DB_NAME"

        # Seeds SQL
        for seed in /var/www/html/database/seeds/*.sql; do
            [ -f "$seed" ] || continue
            log "Seed: $(basename "$seed")"
            mysql -h "$DB_HOST" -P "$DB_PORT" -u"$ROOT_USER" -p"$ROOT_PASS" "$DB_NAME" < "$seed"
        done

        # Admin con bcrypt generado dinámicamente
        ADMIN_EMAIL="${ADMIN_EMAIL:-admin@cada.com}"
        ADMIN_PASS="${ADMIN_PASSWORD:-Admin2026!}"
        HASH=$(php -r "echo password_hash(\$_ENV['ADMIN_PASSWORD'] ?? 'Admin2026!', PASSWORD_BCRYPT, ['cost' => 12]);")
        mysql -h "$DB_HOST" -P "$DB_PORT" -u"$ROOT_USER" -p"$ROOT_PASS" "$DB_NAME" <<SQL
INSERT INTO usuarios (email, password, rol_id, estatus)
VALUES ('$ADMIN_EMAIL', '$HASH', 1, 'Activo')
ON DUPLICATE KEY UPDATE password = VALUES(password), estatus = 'Activo';
SQL

        log "✓ Base de datos instalada con éxito"
        log "  Email admin:    $ADMIN_EMAIL"
        log "  Password admin: $ADMIN_PASS"
    else
        log "Base de datos con $TABLE_COUNT tabla(s). Saltando instalación."
    fi
fi

# --------------------------------------------------------------------------
# 4. Ajustar permisos (por si los volúmenes vinieron con otros dueños)
# --------------------------------------------------------------------------
chown -R www-data:www-data /var/www/html/storage /var/www/html/public/assets/uploads 2>/dev/null || true

log "Listo. Levantando Apache..."
exec "$@"
