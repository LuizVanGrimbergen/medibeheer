#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

LAN_IP="$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || echo "")"

echo "Medibeheer op iPhone (zelfde WiFi)"
echo ""
echo "Mac IP: ${LAN_IP:-kon niet worden bepaald}"
echo ""
echo "Stappen:"
echo "  1. Laat deze tunnel draaien (onderstaand)."
echo "  2. Kopieer de Public URL en zet in .env:"
echo "       APP_URL=<Public URL>"
echo "       VAPID_SUBJECT=<zelfde Public URL>"
echo "       SESSION_SECURE_COOKIE=true"
echo "  3. php artisan config:clear"
echo "  4. npm run build   (geen Vite nodig op de telefoon)"
echo "  5. Open de Public URL in Safari op je iPhone."
echo "  6. In een extra terminal op de Mac:"
echo "       php artisan schedule:work"
echo "     (zonder scheduler komen echte herinneringen niet; test-push wel)"
echo ""
echo "Na testen zet je APP_URL terug naar https://medibeheer.test"
echo ""
echo "Start tunnel…"

exec herd share https://medibeheer.test
