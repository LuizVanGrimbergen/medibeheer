#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
