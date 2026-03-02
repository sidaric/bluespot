#!/usr/bin/env bash
set -euo pipefail

# ------------------------------------------------------------
# Auto installer + runner (Ubuntu/WSL) for Laravel + Vite + SQLite
# - Installs missing deps via apt (requires sudo)
# - Ensures Node.js >= 20 (NodeSource)
# - Sets up .env, SQLite, runs migrations
# - Starts php artisan serve + npm run dev
# ------------------------------------------------------------

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

info() { echo -e "\033[1;34m[INFO]\033[0m $*"; }
warn() { echo -e "\033[1;33m[WARN]\033[0m $*"; }
err()  { echo -e "\033[1;31m[ERR ]\033[0m $*"; }

has_cmd() { command -v "$1" >/dev/null 2>&1; }

cleanup() {
  info "Stopping dev processes..."
  if [[ -n "${PHP_PID:-}" ]] && kill -0 "$PHP_PID" >/dev/null 2>&1; then kill "$PHP_PID" >/dev/null 2>&1 || true; fi
  if [[ -n "${VITE_PID:-}" ]] && kill -0 "$VITE_PID" >/dev/null 2>&1; then kill "$VITE_PID" >/dev/null 2>&1 || true; fi
}
trap cleanup EXIT INT TERM

require_sudo() {
  if ! has_cmd sudo; then
    err "sudo is required for auto-install. Please install sudo or run in an environment with sudo."
    exit 1
  fi
  sudo -v
}

apt_install() {
  require_sudo
  sudo apt-get update -y
  sudo apt-get install -y "$@"
}

ensure_node_20() {
  if has_cmd node; then
    local major
    major="$(node -v | sed 's/^v//' | cut -d. -f1 || echo 0)"
    if [[ "${major:-0}" -ge 20 ]]; then
      return 0
    fi
    warn "Node.js is installed but too old: $(node -v). Upgrading to Node 20..."
  else
    info "Node.js not found. Installing Node 20..."
  fi

  require_sudo
  # NodeSource setup for Node 20
  if ! has_cmd curl; then
    apt_install curl
  fi
  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
  sudo apt-get install -y nodejs

  if ! has_cmd node; then
    err "Node install failed."
    exit 1
  fi

  local major_after
  major_after="$(node -v | sed 's/^v//' | cut -d. -f1 || echo 0)"
  if [[ "${major_after:-0}" -lt 20 ]]; then
    err "Node version is still < 20 after install: $(node -v)"
    exit 1
  fi
}

set_env_kv() {
  # set_env_kv KEY VALUE
  local key="$1"
  local value="$2"
  if grep -qE "^${key}=" .env; then
    # use | delimiter to avoid escaping slashes
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    echo "${key}=${value}" >> .env
  fi
}

# ------------------------
# 0) Sanity check
# ------------------------
if [[ ! -f artisan ]]; then
  err "artisan not found. Run this script from the Laravel project root."
  exit 1
fi

# ------------------------
# 1) System dependencies
# ------------------------
info "Checking system dependencies..."

if ! has_cmd git || ! has_cmd unzip || ! has_cmd curl; then
  info "Installing base tools (git, unzip, curl)..."
  apt_install git unzip curl
fi

if ! has_cmd php; then
  info "Installing PHP + extensions..."
  apt_install php php-cli php-mbstring php-xml php-curl php-sqlite3
else
  # Best-effort: ensure extensions
  missing_ext=()
  php -m | grep -qi mbstring || missing_ext+=("php-mbstring")
  php -m | grep -qi xml       || missing_ext+=("php-xml")
  php -m | grep -qi curl      || missing_ext+=("php-curl")
  php -m | grep -qi sqlite3   || missing_ext+=("php-sqlite3")
  if ((${#missing_ext[@]})); then
    info "Installing missing PHP extensions: ${missing_ext[*]}"
    apt_install "${missing_ext[@]}"
  fi
fi

if ! has_cmd composer; then
  info "Installing Composer..."
  apt_install composer
fi

ensure_node_20

if ! has_cmd npm; then
  # NodeSource nodejs includes npm, but just in case
  info "Installing npm..."
  apt_install npm
fi

info "Dependencies OK:"
info "  php:      $(php -v | head -n 1)"
info "  composer: $(composer --version | head -n 1)"
info "  node:     $(node -v)"
info "  npm:      $(npm -v)"

# ------------------------
# 2) Project dependencies
# ------------------------
info "Installing PHP dependencies (composer install)..."
composer install --no-interaction

info "Installing JS dependencies (npm install)..."
npm install

# ------------------------
# 3) Environment setup
# ------------------------
if [[ ! -f .env ]]; then
  if [[ -f .env.example ]]; then
    info "Creating .env from .env.example..."
    cp .env.example .env
  else
    err ".env.example not found. Cannot create .env."
    exit 1
  fi
else
  info ".env already exists - keeping it."
fi

info "Generating APP_KEY..."
php artisan key:generate --force

# Ensure SQLite + file cache (avoid DB cache table issues during setup)
info "Configuring SQLite database..."
mkdir -p database
DB_FILE="database/database.sqlite"
if [[ ! -f "$DB_FILE" ]]; then
  info "Creating SQLite database file: $DB_FILE"
  touch "$DB_FILE"
fi

set_env_kv "DB_CONNECTION" "sqlite"
set_env_kv "DB_DATABASE" "${DB_FILE}"

# Make installer robust even if someone set DB cache in .env
set_env_kv "CACHE_STORE" "file"
set_env_kv "SESSION_DRIVER" "file"

# Clear config cache (safe)
php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear  >/dev/null 2>&1 || true

# ------------------------
# 4) Migrations
# ------------------------
info "Running migrations..."
php artisan migrate --force

# ------------------------
# 5) Start servers
# ------------------------
info "Starting PHP dev server on http://127.0.0.1:8000 ..."
php artisan serve --host=127.0.0.1 --port=8000 >/dev/null 2>&1 &
PHP_PID=$!

info "Starting Vite dev server (HMR)..."
npm run dev >/dev/null 2>&1 &
VITE_PID=$!

info "All set ✅"
info "Open: http://127.0.0.1:8000"
info "Stop: Ctrl+C"

wait