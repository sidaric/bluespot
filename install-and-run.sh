#!/usr/bin/env bash
set -euo pipefail

# ------------------------------------------------------------
# Auto installer + runner (Ubuntu/WSL) for Laravel + Vite + SQLite
# - Installs missing deps via apt (requires sudo)
# - Sets up .env, SQLite, migrations
# - Starts php artisan serve + npm run dev
# ------------------------------------------------------------

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

info() { echo -e "\033[1;34m[INFO]\033[0m $*"; }
warn() { echo -e "\033[1;33m[WARN]\033[0m $*"; }
err()  { echo -e "\033[1;31m[ERR ]\033[0m $*"; }

has_cmd() { command -v "$1" >/dev/null 2>&1; }

need_sudo=false
ensure_sudo() {
  if [[ "$need_sudo" == true ]]; then
    if ! has_cmd sudo; then
      err "sudo is required but not available. Install sudo or run in an environment with sudo."
      exit 1
    fi
    # Ask for sudo upfront
    sudo -v
  fi
}

apt_install() {
  local pkgs=("$@")
  need_sudo=true
  ensure_sudo
  info "Installing via apt: ${pkgs[*]}"
  sudo apt-get update -y
  sudo apt-get install -y "${pkgs[@]}"
}

cleanup() {
  info "Stopping dev processes..."
  if [[ -n "${PHP_PID:-}" ]] && kill -0 "$PHP_PID" >/dev/null 2>&1; then kill "$PHP_PID" >/dev/null 2>&1 || true; fi
  if [[ -n "${VITE_PID:-}" ]] && kill -0 "$VITE_PID" >/dev/null 2>&1; then kill "$VITE_PID" >/dev/null 2>&1 || true; fi
}
trap cleanup EXIT INT TERM

# ------------------------
# 0) Sanity checks
# ------------------------
if [[ ! -f artisan ]]; then
  err "artisan not found. Run this script from the Laravel project root."
  exit 1
fi

# ------------------------
# 1) Install missing system dependencies
# ------------------------
info "Checking system dependencies..."

# curl + git often needed
missing_pkgs=()
if ! has_cmd curl; then missing_pkgs+=("curl"); fi
if ! has_cmd git; then missing_pkgs+=("git"); fi
if ((${#missing_pkgs[@]})); then
  apt_install "${missing_pkgs[@]}"
fi

# PHP + extensions for Laravel
# (sqlite + mbstring + xml + curl are common needs)
if ! has_cmd php; then
  apt_install php php-cli php-mbstring php-xml php-curl php-sqlite3 unzip
else
  # Ensure required extensions exist (best-effort)
  # If missing, install them.
  ext_missing=()
  php -m | grep -qi mbstring || ext_missing+=("php-mbstring")
  php -m | grep -qi xml       || ext_missing+=("php-xml")
  php -m | grep -qi curl      || ext_missing+=("php-curl")
  php -m | grep -qi sqlite3   || ext_missing+=("php-sqlite3")
  if ((${#ext_missing[@]})); then
    apt_install "${ext_missing[@]}"
  fi
fi

# Composer
if ! has_cmd composer; then
  apt_install composer
fi

# Node + npm
if ! has_cmd node || ! has_cmd npm; then
  apt_install nodejs npm
fi

# Version hint (optional)
NODE_MAJOR="$(node -v 2>/dev/null | sed 's/v//' | cut -d. -f1 || echo 0)"
if [[ "${NODE_MAJOR:-0}" -lt 18 ]]; then
  warn "Your Node.js version seems old (node -v = $(node -v))."
  warn "If Vite/build fails, install a newer Node (18+ or 20+) and re-run."
fi

info "Dependencies OK:"
info "  php:      $(php -v | head -n 1)"
info "  composer: $(composer --version | head -n 1)"
info "  node:     $(node -v)"
info "  npm:      $(npm -v)"

# ------------------------
# 2) Install project dependencies
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

# SQLite DB
info "Configuring SQLite database..."
mkdir -p database
DB_FILE="database/database.sqlite"
if [[ ! -f "$DB_FILE" ]]; then
  info "Creating SQLite database file: $DB_FILE"
  touch "$DB_FILE"
fi

# Ensure DB config in .env (portable relative path)
if grep -qE '^DB_CONNECTION=' .env; then
  sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
else
  echo "DB_CONNECTION=sqlite" >> .env
fi

if grep -qE '^DB_DATABASE=' .env; then
  sed -i "s#^DB_DATABASE=.*#DB_DATABASE=${DB_FILE}#" .env
else
  echo "DB_DATABASE=${DB_FILE}" >> .env
fi

info "Clearing caches..."
php artisan optimize:clear

info "Running migrations..."
php artisan migrate --force

# ------------------------
# 4) Start servers
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