#!/bin/bash
set -euo pipefail

# OLLMCHS Library — Rollback Script
# Reverts the 'current' symlink to the previous release.
#
# Usage:
#   bash rollback.sh                # Rollback to the previous release
#   bash rollback.sh --list         # List all available releases
#   bash rollback.sh <release>      # Rollback to a specific release name
#   bash rollback.sh --dry-run      # Show what would be done without doing it
#   bash rollback.sh --help         # Show this usage information

APP_DIR="/var/www/ollmchs-library"
RELEASES_DIR="${APP_DIR}/releases"
CURRENT_DIR="${APP_DIR}/current"

# ANSI colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

info()  { echo -e "${GREEN}==>${NC} $*"; }
warn()  { echo -e "${YELLOW}==>${NC} $*"; }
error() { echo -e "${RED}==>${NC} $*" >&2; }

# --------------- Help ---------------

if [ "${1:-}" = "--help" ] || [ "${1:-}" = "-h" ]; then
    echo ""
    echo -e "${CYAN}OLLMCHS Library — Rollback Script${NC}"
    echo ""
    echo "  Reverts the 'current' symlink to the previous release."
    echo ""
    echo "  Usage:"
    echo "    bash rollback.sh                     Rollback to previous release"
    echo "    bash rollback.sh --list              List all releases"
    echo "    bash rollback.sh <release-name>      Rollback to a specific release"
    echo "    bash rollback.sh --dry-run           Preview rollback without applying"
    echo ""
    exit 0
fi

# --------------- Preflight ---------------

if [ ! -d "${RELEASES_DIR}" ]; then
    error "Releases directory not found: ${RELEASES_DIR}"
    exit 1
fi

mapfile -t RELEASES < <(ls -t1 "${RELEASES_DIR}" 2>/dev/null || true)

if [ ${#RELEASES[@]} -eq 0 ]; then
    error "No releases found in ${RELEASES_DIR}"
    exit 1
fi

# --------------- List mode ---------------

if [ "${1:-}" = "--list" ]; then
    echo -e "\n${CYAN}Available releases:${NC}\n"
    CURRENT_TARGET=""
    if [ -L "${CURRENT_DIR}" ]; then
        CURRENT_TARGET="$(readlink "${CURRENT_DIR}")"
    fi
    for r in "${RELEASES[@]}"; do
        FULL="${RELEASES_DIR}/${r}"
        if [ "${FULL}" = "${CURRENT_TARGET}" ]; then
            echo -e "  ${GREEN}* ${r} (current)${NC}"
        else
            echo "    ${r}"
        fi
    done
    echo ""
    exit 0
fi

# --------------- Resolve target ---------------

if [ ! -L "${CURRENT_DIR}" ] && [ ! -d "${CURRENT_DIR}" ]; then
    error "No 'current' symlink or directory found at ${CURRENT_DIR}"
    exit 1
fi

CURRENT_TARGET=""
if [ -L "${CURRENT_DIR}" ]; then
    CURRENT_TARGET="$(readlink "${CURRENT_DIR}")"
fi

CURRENT_NAME=""
if [ -n "${CURRENT_TARGET}" ]; then
    CURRENT_NAME="$(basename "${CURRENT_TARGET}")"
fi

if [ -n "${1:-}" ] && [ "${1}" != "--dry-run" ]; then
    # Rollback to a specific named release
    ROLLBACK_NAME="${1}"
    ROLLBACK_PATH="${RELEASES_DIR}/${ROLLBACK_NAME}"
    if [ ! -d "${ROLLBACK_PATH}" ]; then
        error "Release '${ROLLBACK_NAME}' not found in ${RELEASES_DIR}"
        echo "Run 'bash rollback.sh --list' to see available releases."
        exit 1
    fi
    info "Targeting specific release: ${ROLLBACK_NAME}"
else
    # Find the release immediately before the current one
    if [ -z "${CURRENT_NAME}" ]; then
        error "Cannot determine current release (symlink is missing or broken)"
        exit 1
    fi

    ROLLBACK_NAME=""
    FOUND_CURRENT=false
    for r in "${RELEASES[@]}"; do
        if [ "${FOUND_CURRENT}" = true ]; then
            ROLLBACK_NAME="${r}"
            break
        fi
        if [ "${r}" = "${CURRENT_NAME}" ]; then
            FOUND_CURRENT=true
        fi
    done

    if [ -z "${ROLLBACK_NAME}" ]; then
        if [ "${FOUND_CURRENT}" = false ]; then
            error "Current release '${CURRENT_NAME}' is not in the releases list."
            echo "    It may have been manually removed or replaced."
            echo "    Run 'bash rollback.sh --list' to see available releases."
        else
            error "No previous release found to rollback to (current is the oldest release)."
        fi
        exit 1
    fi

    ROLLBACK_PATH="${RELEASES_DIR}/${ROLLBACK_NAME}"
    info "Rolling back from '${CURRENT_NAME}' to '${ROLLBACK_NAME}'"
fi

# --------------- Dry-run mode ---------------

if [ "${1:-}" = "--dry-run" ]; then
    echo ""
    echo -e "${CYAN}Dry-run summary:${NC}"
    echo "  Current symlink: ${CURRENT_TARGET:-<none>}"
    echo "  New symlink:     ${ROLLBACK_PATH}"
    echo "  Command:         ln -sfn ${ROLLBACK_PATH} ${CURRENT_DIR}"
    echo ""
    exit 0
fi

# --------------- Locate health-check script ---------------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
HEALTH_SCRIPT="${SCRIPT_DIR}/health-check-validation.sh"

# --------------- Perform rollback ---------------

info "Verifying release integrity..."

if [ ! -f "${ROLLBACK_PATH}/artisan" ]; then
    error "Release '${ROLLBACK_NAME}' is corrupted (missing artisan)"
    exit 1
fi

if [ ! -f "${ROLLBACK_PATH}/bootstrap/app.php" ]; then
    error "Release '${ROLLBACK_NAME}' is corrupted (missing bootstrap/app.php)"
    exit 1
fi

if [ ! -d "${ROLLBACK_PATH}/vendor" ]; then
    warn "Vendor directory missing — running 'composer install --no-dev'..."
    cd "${ROLLBACK_PATH}"
    composer install --no-dev --no-interaction --optimize-autoloader
fi

if [ ! -d "${ROLLBACK_PATH}/public/build" ] && [ ! -d "${ROLLBACK_PATH}/public/dist" ]; then
    warn "Frontend assets missing — running 'npm ci && npm run build'..."
    cd "${ROLLBACK_PATH}"
    npm ci --no-audit --no-fund 2>/dev/null && npm run build 2>/dev/null || \
        warn "Frontend build skipped (npm not available or build failed)"
fi

# Rebuild cache for the rollback release
info "Rebuilding cache for rolled-back release..."
cd "${ROLLBACK_PATH}"
php artisan config:cache --quiet
php artisan route:cache --quiet
php artisan view:cache --quiet
php artisan event:cache --quiet || true

# Run any pending migrations
info "Running pending migrations..."
php artisan migrate --force --quiet || \
    warn "Migration check completed (may already be up-to-date)"

# Validate release before switching symlink
if [ -f "${HEALTH_SCRIPT}" ]; then
    info "Validating rollback release before activation..."
    bash "${HEALTH_SCRIPT}" --release "${ROLLBACK_PATH}" || {
        error "Release validation FAILED — aborting rollback"
        exit 1
    }
fi

# Switch the symlink (atomic symlink swap)
info "Switching symlink..."
ln -sfn "${ROLLBACK_PATH}" "${CURRENT_DIR}"

# Verify the switch
NEW_TARGET="$(readlink "${CURRENT_DIR}")"
if [ "${NEW_TARGET}" != "${ROLLBACK_PATH}" ]; then
    error "Symlink switch failed — expected ${ROLLBACK_PATH}, got ${NEW_TARGET}"
    exit 1
fi

# Restart queue workers so they pick up the rolled-back code
info "Restarting queue workers..."
php "${CURRENT_DIR}/artisan" queue:restart --quiet 2>/dev/null || true

# Reload PHP-FPM to clear opcache
if command -v service &>/dev/null; then
    info "Reloading PHP-FPM..."
    # Try common service names across different OS distributions
    service php8.3-fpm reload 2>/dev/null || \
    service php8.2-fpm reload 2>/dev/null || \
    service php8.1-fpm reload 2>/dev/null || \
    service php-fpm reload 2>/dev/null || true
fi

# Health check the running application after rollback
if [ -f "${HEALTH_SCRIPT}" ]; then
    info "Verifying application health after rollback..."
    bash "${HEALTH_SCRIPT}" --wait --url "http://localhost/health" || {
        warn "Health check FAILED — the application may be unstable"
        warn "Run 'bash rollback.sh ${CURRENT_NAME}' to revert"
    }
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Rollback complete!${NC}"
echo -e "${GREEN}  Now active: ${ROLLBACK_NAME}${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

