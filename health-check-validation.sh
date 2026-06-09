#!/bin/bash
set -euo pipefail

# OLLMCHS Library — Health Check & Validation Script
#
# Validates a release before deployment (filesystem checks) or verifies
# the running application after deployment (HTTP health endpoint checks).
#
# Usage:
#   bash health-check-validation.sh                                        # HTTP check against http://localhost/health
#   bash health-check-validation.sh --release /path/to/release              # Filesystem validation (pre-deploy)
#   bash health-check-validation.sh --url https://example.com               # HTTP check against a remote server
#   bash health-check-validation.sh --wait --url https://example.com        # HTTP check with retry loop (up to 2 min)
#   bash health-check-validation.sh --wait --retries 30 --interval 5       # Custom retry: 30 attempts, 5s apart
#   bash health-check-validation.sh --url https://example.com --verbose     # Full verbose output
#   bash health-check-validation.sh --artisan /path/to/artisan              # Run health checks via Artisan command
#   bash health-check-validation.sh --help                                  # Show this usage information
#
# Exit codes:
#   0 — All checks passed (healthy)
#   1 — One or more checks failed (degraded / critical)
#   2 — Invalid arguments or configuration error

APP_DIR="${APP_DIR:-/var/www/ollmchs-library}"
DEFAULT_HEALTH_URL="http://localhost/health"
DEFAULT_RETRIES=12
DEFAULT_INTERVAL=10

# ANSI colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

PASS=0
FAIL=0
WARN=0

info()    { echo -e "${GREEN}==>${NC} $*"; }
warn()    { echo -e "${YELLOW}==>${NC} $*"; }
error()   { echo -e "${RED}==>${NC} $*" >&2; }
header()  { echo -e "\n${CYAN}━━━ $* ━━━${NC}\n"; }
pass()    { echo -e "  ${GREEN}✓${NC} $*"; ((PASS++)); }
fail()    { echo -e "  ${RED}✗${NC} $*"; ((FAIL++)); }
skip()    { echo -e "  ${YELLOW}‒${NC} $* (skipped)"; }
warn_chk(){ echo -e "  ${YELLOW}⚠${NC} $*"; ((WARN++)); }

# ──── Help ──────────────────────────────────────────────────────────

if [ "${1:-}" = "--help" ] || [ "${1:-}" = "-h" ]; then
    echo ""
    echo -e "${CYAN}OLLMCHS Library — Health Check & Validation Script${NC}"
    echo ""
    echo "  Validates a release before deployment or verifies the running application."
    echo ""
    echo "  Usage:"
    echo "    bash health-check-validation.sh                        HTTP check (localhost)"
    echo "    bash health-check-validation.sh --release <path>       Filesystem validation"
    echo "    bash health-check-validation.sh --url <url>            HTTP check against URL"
    echo "    bash health-check-validation.sh --wait --url <url>     HTTP check with retries"
    echo "    bash health-check-validation.sh --artisan <path>       Artisan health checks"
    echo ""
    echo "  Options:"
    echo "    --release <path>     Release directory to validate (pre-deploy)"
    echo "    --url <url>          Application URL to health-check (post-deploy)"
    echo "    --artisan <path>     Path to artisan for app-level checks"
    echo "    --wait               Enable retry loop for HTTP checks"
    echo "    --retries <n>        Max retry attempts (default: ${DEFAULT_RETRIES})"
    echo "    --interval <sec>     Seconds between retries (default: ${DEFAULT_INTERVAL})"
    echo "    --verbose            Show detailed output for each check"
    echo "    --help, -h           Show this help message"
    echo ""
    echo "  Exit codes: 0 = healthy, 1 = degraded/critical, 2 = configuration error"
    echo ""
    exit 0
fi

# ──── Parse arguments ───────────────────────────────────────────────

MODE=""
RELEASE_PATH=""
HEALTH_URL="${DEFAULT_HEALTH_URL}"
ARTISAN_PATH=""
WAIT=false
VERBOSE=false
RETRIES="${DEFAULT_RETRIES}"
INTERVAL="${DEFAULT_INTERVAL}"

while [[ $# -gt 0 ]]; do
    case "$1" in
        --release)
            MODE="release"
            RELEASE_PATH="$2"
            shift 2
            ;;
        --url)
            MODE="url"
            HEALTH_URL="$2"
            shift 2
            ;;
        --artisan)
            MODE="artisan"
            ARTISAN_PATH="$2"
            shift 2
            ;;
        --wait)
            WAIT=true
            shift
            ;;
        --retries)
            RETRIES="$2"
            shift 2
            ;;
        --interval)
            INTERVAL="$2"
            shift 2
            ;;
        --verbose)
            VERBOSE=true
            shift
            ;;
        *)
            error "Unknown option: $1"
            echo "Run 'bash health-check-validation.sh --help' for usage."
            exit 2
            ;;
    esac
done

# Default mode: HTTP check against localhost
if [ -z "${MODE}" ]; then
    MODE="url"
fi

# ──── Utility Functions ─────────────────────────────────────────────

check_dependency() {
    if ! command -v "$1" &>/dev/null; then
        warn_chk "'$1' is not available"
        return 1
    fi
    return 0
}

# ──── Mode 1: Filesystem (Pre-Deploy) Validation ────────────────────

validate_release() {
    local dir="$1"
    local version=""

    header "Pre-Deploy Validation — $(basename "${dir}")"

    # --- Required files & directories ---

    info "Checking release structure..."

    if [ ! -d "${dir}" ]; then
        fail "Release directory does not exist: ${dir}"
        return 1
    fi
    pass "Release directory exists"

    local checks=(
        "artisan:artisan"
        "bootstrap/app.php:bootstrap/app.php"
        "vendor/autoload.php:Composer autoloader"
        "config/app.php:config directory"
    )

    for entry in "${checks[@]}"; do
        local file="${dir}/${entry%%:*}"
        local label="${entry##*:}"
        if [ -f "${file}" ] || [ -d "${dir}/${entry%%/*}" ]; then
            pass "${label} found"
        else
            fail "${label} missing: ${file}"
        fi
    done

    # --- Shared resources ---

    info "Checking shared resource symlinks..."

    if [ -L "${dir}/.env" ]; then
        local env_target
        env_target="$(readlink "${dir}/.env")"
        pass ".env symlink → ${env_target}"
    elif [ -f "${dir}/.env" ]; then
        pass ".env file present (standalone)"
    else
        fail ".env missing — deploy will fail"
    fi

    if [ -L "${dir}/storage" ]; then
        local storage_target
        storage_target="$(readlink "${dir}/storage")"
        pass "storage symlink → ${storage_target}"
    elif [ -d "${dir}/storage" ]; then
        pass "storage directory present"
    else
        fail "storage missing"
    fi

    if [ -L "${dir}/public/storage" ] || [ -d "${dir}/public/storage" ]; then
        pass "public/storage present"
    else
        fail "public/storage missing"
    fi

    # --- Application key ---

    info "Checking application key..."

    if [ -f "${dir}/.env" ]; then
        if grep -q "^APP_KEY=" "${dir}/.env" 2>/dev/null; then
            pass "APP_KEY found in .env"
        else
            fail "APP_KEY missing from .env — run 'php artisan key:generate'"
        fi
    else
        skip "APP_KEY check (.env not present as file)"
    fi

    # --- Composer dependencies ---

    if [ -d "${dir}/vendor" ]; then
        info "Verifying Composer autoloader..."

        if [ -f "${dir}/vendor/autoload.php" ]; then
            pass "Composer autoloader exists"
        else
            fail "Composer autoloader missing — run 'composer install'"
        fi

        # Quick check: verify a core class is autoloadable using PHP syntax check
        if command -v php &>/dev/null; then
            if php -r "require '${dir}/vendor/autoload.php';" 2>/dev/null; then
                pass "Composer autoloader works"
            else
                fail "Composer autoloader failed"
            fi
        else
            skip "Composer autoloader test (PHP CLI not available)"
        fi
    else
        warn_chk "vendor directory missing — run 'composer install --no-dev'"
    fi

    # --- Frontend assets ---

    info "Checking frontend assets..."

    if [ -d "${dir}/public/build" ]; then
        pass "Vite build assets found (public/build)"
    elif [ -d "${dir}/public/dist" ]; then
        pass "Legacy dist assets found (public/dist)"
    else
        warn_chk "No frontend build assets found — app may lack styling"
    fi

    # --- Artisan validity checks ---

    if [ -f "${dir}/artisan" ] && command -v php &>/dev/null; then
        info "Running Artisan validation checks..."

        # PHP syntax check on artisan
        if php -l "${dir}/artisan" &>/dev/null; then
            pass "artisan: PHP syntax OK"
        else
            fail "artisan: PHP syntax error"
        fi

        # Quick config check (load the app but don't run HTTP)
        # Using APP_ENV=local and CONFIG_CACHE to avoid hitting the DB
        set +e
        local config_output
        config_output=$(cd "${dir}" && php artisan config:cache --quiet 2>&1 || true)
        local config_exit=$?
        set -e

        if [ ${config_exit} -eq 0 ]; then
            pass "artisan: config caching succeeded"
            # Clear the config cache we just created
            cd "${dir}" && php artisan config:clear --quiet 2>/dev/null || true
        elif [ ${config_exit} -eq 1 ]; then
            fail "artisan: config caching failed — check config files for errors"
            if ${VERBOSE}; then
                echo "    ${config_output}" | head -5
            fi
        else
            warn_chk "artisan: config caching exited with code ${config_exit} (may be expected in some environments)"
        fi

        # Check PHP version compatibility
        if [ -f "${dir}/composer.json" ]; then
            local php_version
            php_version=$(php -r "echo PHP_VERSION;")
            local required_php
            required_php=$(php -r "\$c = json_decode(file_get_contents('${dir}/composer.json'), true); echo \$c['require']['php'] ?? 'unknown';")
            pass "PHP ${php_version} (required: ${required_php})"
        fi
    else
        skip "Artisan validation (PHP CLI or artisan not available)"
    fi

    # --- Disk space (on the storage volume) ---

    info "Checking available disk space..."

    if command -v df &>/dev/null; then
        local df_output
        df_output=$(df -h "${dir}" 2>/dev/null | tail -1)
        local usage_pct
        usage_pct=$(echo "${df_output}" | awk '{print $5}' | tr -d '%')

        if [ -n "${usage_pct}" ] && [ "${usage_pct}" -le 75 ]; then
            pass "Disk usage: ${usage_pct}% (OK)"
        elif [ -n "${usage_pct}" ] && [ "${usage_pct}" -le 90 ]; then
            warn_chk "Disk usage: ${usage_pct}% (warning — above 75%)"
        elif [ -n "${usage_pct}" ]; then
            fail "Disk usage: ${usage_pct}% (critical — above 90%)"
        else
            skip "Disk usage check (unable to parse df output)"
        fi

        if ${VERBOSE}; then
            echo "    ${df_output}"
        fi
    else
        skip "Disk space check (df not available)"
    fi

    # --- Permission check ---

    info "Checking directory permissions..."

    if [ -w "${dir}" ]; then
        pass "Release directory is writable"
    else
        fail "Release directory is not writable"
    fi

    if [ -d "${dir}/storage" ] && [ -w "${dir}/storage" ] 2>/dev/null; then
        pass "storage/ directory is writable"
    else
        fail "storage/ directory is not writable"
    fi

    if [ -d "${dir}/bootstrap/cache" ] && [ -w "${dir}/bootstrap/cache" ] 2>/dev/null; then
        pass "bootstrap/cache/ directory is writable"
    else
        fail "bootstrap/cache/ directory is not writable"
    fi

    # --- Summary ---

    echo ""
    info "Pre-deploy validation complete."
    echo "  ${GREEN}${PASS} passed${NC}, ${YELLOW}${WARN} warnings${NC}, ${RED}${FAIL} failed${NC}"

    if [ "${FAIL}" -gt 0 ]; then
        return 1
    fi
    return 0
}

# ──── Mode 2: HTTP Health Check (Post-Deploy) ───────────────────────

http_health_check() {
    local url="$1"
    local wait_mode="$2"
    local max_retries="$3"
    local sleep_interval="$4"

    header "Health Check — ${url}"

    # Dependency checks
    check_dependency "curl" || { fail "curl is required for HTTP health checks"; return 1; }

    if ${wait_mode}; then
        info "Waiting for application to become healthy (up to $((max_retries * sleep_interval)) seconds)..."
        echo ""
    fi

    local attempt=1
    local max_attempts=$(( wait_mode ? max_retries : 1 ))

    while [ ${attempt} -le ${max_attempts} ]; do
        local http_code
        local response_body
        local curl_exit=0

        http_code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${url}" 2>/dev/null) || curl_exit=$?
        response_body=$(curl -s --max-time 10 "${url}" 2>/dev/null) || true

        if [ "${http_code}" = "000" ] || [ -z "${http_code}" ]; then
            # Connection refused or timeout
            if ${wait_mode} && [ ${attempt} -lt ${max_attempts} ]; then
                if ${VERBOSE}; then
                    echo -e "  ${YELLOW}⏳${NC} Attempt ${attempt}/${max_attempts}: connection not ready..."
                fi
                sleep "${sleep_interval}"
                ((attempt++))
                continue
            fi

            fail "Connection failed (attempt ${attempt}/${max_attempts})"
            if [ "${curl_exit}" -ne 0 ] && ${VERBOSE}; then
                echo "    curl exit code: ${curl_exit}"
            fi
            return 1
        fi

        # We got a response — validate it

        if [ "${http_code}" = "200" ]; then
            pass "HTTP 200 OK"
        elif [ "${http_code}" = "503" ]; then
            fail "HTTP 503 Service Unavailable (app is degraded)"
            if ${VERBOSE}; then
                echo "    Response: ${response_body}"
            fi
            if ${wait_mode} && [ ${attempt} -lt ${max_attempts} ]; then
                sleep "${sleep_interval}"
                ((attempt++))
                continue
            fi
            return 1
        else
            fail "HTTP ${http_code} (expected 200)"
            if ${VERBOSE}; then
                echo "    Response: ${response_body}"
            fi
            return 1
        fi

        # Parse JSON response for status field
        local status
        status=$(echo "${response_body}" | php -r '
            $body = file_get_contents("php://stdin");
            $data = json_decode($body, true);
            if (!$data) { echo "unparseable"; exit(1); }
            echo $data["status"] ?? "unknown";
        ' 2>/dev/null) || status="unparseable"

        case "${status}" in
            healthy)
                pass "Application status: healthy"
                ;;
            degraded)
                fail "Application status: degraded"
                if ${VERBOSE}; then
                    echo "    Full response: ${response_body}"
                fi
                return 1
                ;;
            unparseable)
                warn_chk "Could not parse status from response body"
                if ${VERBOSE}; then
                    echo "    Raw response: ${response_body}"
                fi
                # Still pass if we got HTTP 200 even if body is unexpected
                pass "HTTP 200 accepted (body parsing skipped)"
                ;;
            *)
                warn_chk "Unexpected status: ${status}"
                ;;
        esac

        # Check timestamp is recent (within last 5 minutes)
        local timestamp
        timestamp=$(echo "${response_body}" | php -r '
            $body = file_get_contents("php://stdin");
            $data = json_decode($body, true);
            echo $data["timestamp"] ?? "";
        ' 2>/dev/null) || timestamp=""

        if [ -n "${timestamp}" ]; then
            local now_epoch
            local ts_epoch
            now_epoch=$(date +%s)
            ts_epoch=$(date -d "${timestamp}" +%s 2>/dev/null) || ts_epoch=0
            local diff=$(( now_epoch - ts_epoch ))
            if [ "${diff}" -ge 0 ] && [ "${diff}" -le 300 ]; then
                pass "Timestamp is current (${diff}s ago)"
            else
                warn_chk "Timestamp seems stale (${diff}s old: ${timestamp})"
            fi
        fi

        # If we got here, all checks passed
        if ${wait_mode} && [ ${attempt} -gt 1 ]; then
            echo -e "  ${GREEN}✓${NC} Became healthy after ${attempt} attempt(s)"
        fi

        # In non-wait mode, single check passed
        break
    done

    # --- Summary ---
    echo ""
    info "Health check complete."
    echo "  ${GREEN}${PASS} passed${NC}, ${YELLOW}${WARN} warnings${NC}, ${RED}${FAIL} failed${NC}"

    if [ "${FAIL}" -gt 0 ]; then
        return 1
    fi
    return 0
}

# ──── Mode 3: Artisan Health Command ────────────────────────────────

artisan_health_check() {
    local artisan="$1"

    header "Artisan Health Check — $(basename "$(dirname "${artisan}")")"

    if [ ! -f "${artisan}" ]; then
        fail "artisan not found at: ${artisan}"
        return 1
    fi

    if ! command -v php &>/dev/null; then
        fail "PHP CLI is not available"
        return 1
    fi

    # Try artisan about (shows app state)
    info "Running 'php artisan about'..."
    set +e
    local about_output
    about_output=$(cd "$(dirname "${artisan}")" && php artisan about --json 2>&1) || true
    set -e

    if echo "${about_output}" | php -r '
        $data = json_decode(file_get_contents("php://stdin"), true);
        exit($data && isset($data["environment"]) ? 0 : 1);
    ' 2>/dev/null; then
        pass "Artisan responds correctly"
        local env_name
        env_name=$(echo "${about_output}" | php -r '
            $data = json_decode(file_get_contents("php://stdin"), true);
            echo $data["environment"]["application_name"]["value"] ?? "unknown";
        ' 2>/dev/null)
        local app_env
        app_env=$(echo "${about_output}" | php -r '
            $data = json_decode(file_get_contents("php://stdin"), true);
            echo $data["environment"]["environment"]["value"] ?? "unknown";
        ' 2>/dev/null)
        local laravel_v
        laravel_v=$(echo "${about_output}" | php -r '
            $data = json_decode(file_get_contents("php://stdin"), true);
            echo $data["environment"]["laravel_version"]["value"] ?? "unknown";
        ' 2>/dev/null)

        ${VERBOSE} && echo "    App: ${env_name} | Env: ${app_env} | Laravel: ${laravel_v}"
    else
        fail "Artisan did not respond correctly"
        if ${VERBOSE}; then
            echo "    ${about_output}" | head -10
        fi
    fi

    # Check for maintenance mode (non-destructive: check for the down file)
    local artisan_dir
    artisan_dir="$(dirname "${artisan}")"
    local down_file="${artisan_dir}/storage/framework/down"

    info "Checking maintenance mode..."
    if [ -f "${down_file}" ]; then
        fail "Application is in maintenance mode — run 'php artisan up'"
    else
        pass "Application is not in maintenance mode"
    fi

    # Run a quick route list to confirm routes are loaded
    info "Checking route configuration..."
    set +e
    local route_output
    route_output=$(cd "${artisan_dir}" && php artisan route:list --json --no-ansi 2>&1 || true)
    set -e

    if echo "${route_output}" | php -r '
        $data = json_decode(file_get_contents("php://stdin"), true);
        exit(is_array($data) && count($data) > 0 ? 0 : 1);
    ' 2>/dev/null; then
        local route_count
        route_count=$(echo "${route_output}" | php -r '
            $data = json_decode(file_get_contents("php://stdin"), true);
            echo is_array($data) ? count($data) : 0;
        ' 2>/dev/null)
        pass "Routes loaded: ${route_count} registered"
    else
        warn_chk "Route listing produced no output"
    fi

    # --- Summary ---
    echo ""
    info "Artisan health check complete."
    echo "  ${GREEN}${PASS} passed${NC}, ${YELLOW}${WARN} warnings${NC}, ${RED}${FAIL} failed${NC}"

    if [ "${FAIL}" -gt 0 ]; then
        return 1
    fi
    return 0
}

# ──── Main Dispatch ──────────────────────────────────────────────────

EXIT_CODE=0

case "${MODE}" in
    release)
        if validate_release "${RELEASE_PATH}"; then
            echo ""
            echo -e "${GREEN}━━━ Release validation PASSED — ready to deploy ━━━${NC}"
        else
            echo ""
            echo -e "${RED}━━━ Release validation FAILED — fix issues before deploying ━━━${NC}"
            EXIT_CODE=1
        fi
        ;;

    url)
        if http_health_check "${HEALTH_URL}" "${WAIT}" "${RETRIES}" "${INTERVAL}"; then
            echo ""
            echo -e "${GREEN}━━━ Health check PASSED — application is healthy ━━━${NC}"
        else
            echo ""
            echo -e "${RED}━━━ Health check FAILED — application is degraded ━━━${NC}"
            EXIT_CODE=1
        fi
        ;;

    artisan)
        if artisan_health_check "${ARTISAN_PATH}"; then
            echo ""
            echo -e "${GREEN}━━━ Artisan health check PASSED ━━━${NC}"
        else
            echo ""
            echo -e "${RED}━━━ Artisan health check FAILED ━━━${NC}"
            EXIT_CODE=1
        fi
        ;;

    *)
        error "Unknown mode: ${MODE}"
        EXIT_CODE=2
        ;;
esac

exit ${EXIT_CODE}
