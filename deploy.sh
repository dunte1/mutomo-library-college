#!/bin/bash
set -euo pipefail

# OLLMCHS Library — Production Deploy Script
# Usage: bash deploy.sh [environment]

ENV="${1:-production}"
APP_DIR="/var/www/ollmchs-library"
RELEASE_DIR="${APP_DIR}/releases/$(date +%Y%m%d%H%M%S)"
SHARED_DIR="${APP_DIR}/shared"
CURRENT_DIR="${APP_DIR}/current"

echo "==> Deploying OLLMCHS Library (${ENV})"

# Ensure shared dirs
mkdir -p "${SHARED_DIR}"/{storage,public/storage}
mkdir -p "${APP_DIR}"/releases

# Clone / pull
if [ -d "${APP_DIR}/repo" ]; then
    cd "${APP_DIR}/repo" && git pull
else
    git clone https://github.com/org/ollmchs-library.git "${APP_DIR}/repo"
fi

# Copy to release
mkdir -p "${RELEASE_DIR}"
cp -a "${APP_DIR}/repo/." "${RELEASE_DIR}"

# Symlink shared resources
ln -sf "${SHARED_DIR}/storage" "${RELEASE_DIR}/storage"
ln -sf "${SHARED_DIR}/public/storage" "${RELEASE_DIR}/public/storage"
ln -sf "${SHARED_DIR}/.env" "${RELEASE_DIR}/.env"

# Install dependencies
cd "${RELEASE_DIR}"
composer install --no-dev --no-interaction --optimize-autoloader
npm ci --no-audit --no-fund && npm run build

# Cache
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
php artisan event:cache --quiet

# Run migrations
php artisan migrate --force --quiet

# Validate release before switching symlink
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [ -f "${SCRIPT_DIR}/health-check-validation.sh" ]; then
    echo "==> Validating release before activation..."
    bash "${SCRIPT_DIR}/health-check-validation.sh" --release "${RELEASE_DIR}" || {
        echo "==> Release validation FAILED — aborting deployment"
        exit 1
    }
fi

# Activate release
ln -sfn "${RELEASE_DIR}" "${CURRENT_DIR}"

# Restart workers
php "${CURRENT_DIR}"/artisan queue:restart --quiet 2>/dev/null || true

# Health check the running application
if [ -f "${SCRIPT_DIR}/health-check-validation.sh" ]; then
    echo "==> Verifying application health after deployment..."
    bash "${SCRIPT_DIR}/health-check-validation.sh" --wait --url "http://localhost/health" || {
        echo "==> Health check FAILED — deployment may be unstable"
        echo "==> Run 'bash rollback.sh' to revert to the previous release"
        exit 1
    }
fi

# Cleanup — keep last 5 releases
cd "${APP_DIR}/releases"
ls -t1 | tail -n +6 | xargs rm -rf 2>/dev/null || true

echo "==> Deploy complete: ${RELEASE_DIR}"
