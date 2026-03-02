#!/usr/bin/env bash
set -euo pipefail

# ---------------------------------------------
# Laravel + Vite local installer & runner (SQLite)
# ---------------------------------------------
# Usage:
#   chmod +x install-and-run.sh
#   ./install-and-run.sh
#
# Starts:
#   - PHP dev server: http://127.0.0.1:8000
#   - Vite dev server: http://localhost:5173
# Stop with Ctrl+C.
# ---------------------------------------------

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

info() { echo -e "\033[1;34m[INFO]\033[0m $*"; }
warn() { echo -e "\033[1;33m[WARN]\033[0m $*"; }
err()  { echo -e "\033[1;31m[ERR ]\033[0m $*"; }

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    err "Missing dependency: '$1'. Please install it and re-run."
    exit 1
  fi
}

cleanup() {
  info "Stopping dev processes..."
  if [[ -n "${PHP_PID:-}" ]] && kill -0 "$PHP_PID" >/dev/null 2>&1; then kill "$PHP_PID" >/dev/null 2>&1 || true; fi
  if [[ -n "${VITE_PID:-}" ]] && kill -0 "$VITE_PID" >/dev/null 2>&1; then kill "$VITE_PID" >/dev/null 2>&1 || true; fi
}
trap cleanup EXIT INT TERM

info "Checking dependencies..."
require_cmd php
require_cmd composer
require_cmd node
require_cmd npm

if [[ ! -f artisan ]]; then
  err "artisan not found. Run this script from the Laravel project root."
  exit 1
fi

info "Installing PHP dependencies (composer install)..."
composer install --no-interaction

info "Installing JS dependencies (npm install)..."
npm install

# Create .env if missing
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

info "Generating APP_KEY (if missing)..."
php artisan key:generate --force

# SQLite setup
info "Configuring SQLite database..."
mkdir -p database
DB_FILE="database/database.sqlite"
if [[ ! -f "$DB_FILE" ]]; then
  info "Creating SQLite database file: $DB_FILE"
  touch "$DB_FILE"
fi

# Ensure DB config in .env
# Use relative path for portability
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

# Build assets once (optional). Dev server will handle HMR.
info "Building frontend (npm run build)..."
npm run build

info "Starting PHP dev server on http://127.0.0.1:8000 ..."
php artisan serve --host=127.0.0.1 --port=8000 >/dev/null 2>&1 &
PHP_PID=$!

info "Starting Vite dev server (HMR)..."
npm run dev >/dev/null 2>&1 &
VITE_PID=$!

info "All set ✅"
info "Open: http://127.0.0.1:8000"
info "Stop: Ctrl+C"

# Wait forever until Ctrl+C
wait