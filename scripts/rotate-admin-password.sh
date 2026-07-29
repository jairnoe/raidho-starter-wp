#!/usr/bin/env bash
# Rotate the default wp-env administrator password (admin / password → strong).
# Writes WP_ADMIN_PASSWORD into .env.
#
# Usage: npm run admin:rotate

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -f .env ]]; then
	set -a
	# shellcheck disable=SC1091
	source .env
	set +a
fi

ADMIN_USER="${WP_ADMIN_USER:-admin}"
ADMIN_PASS="${WP_ADMIN_PASSWORD:-}"

if [[ -z "$ADMIN_PASS" ]]; then
	if command -v openssl >/dev/null 2>&1; then
		ADMIN_PASS="$(openssl rand -base64 18 | tr -d '/+=' | head -c 24)"
	else
		ADMIN_PASS="$(LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 24)"
	fi
fi

php_str() {
	python3 -c 'import sys; print(sys.argv[1].replace("\\", "\\\\").replace("'\''", "\\'\''"))' "$1"
}

U="$(php_str "$ADMIN_USER")"
P="$(php_str "$ADMIN_PASS")"

npx wp-env run cli wp eval "
\$login = '$U';
\$pass  = '$P';
\$user  = get_user_by('login', \$login);
if (!\$user) {
  fwrite(STDERR, 'User not found: ' . \$login . PHP_EOL);
  exit(1);
}
wp_set_password(\$pass, \$user->ID);
echo 'Rotated password for ' . \$login . ' ID=' . \$user->ID . PHP_EOL;
"

upsert_env() {
	local key="$1"
	local val="$2"
	if [[ ! -f .env ]]; then
		return 1
	fi
	python3 - "$key" "$val" <<'PY'
import sys
from pathlib import Path
key, val = sys.argv[1], sys.argv[2]
path = Path(".env")
lines = path.read_text().splitlines() if path.exists() else []
out = []
found = False
for line in lines:
    if line.startswith(key + "="):
        out.append(f"{key}={val}")
        found = True
    else:
        out.append(line)
if not found:
    if out and out[-1] != "":
        out.append("")
    out.append(f"{key}={val}")
path.write_text("\n".join(out) + "\n")
PY
}

if [[ -f .env ]]; then
	upsert_env "WP_ADMIN_USER" "$ADMIN_USER"
	upsert_env "WP_ADMIN_PASSWORD" "$ADMIN_PASS"
	echo "Updated .env with WP_ADMIN_*"
else
	echo "No .env — guarda: WP_ADMIN_USER=$ADMIN_USER WP_ADMIN_PASSWORD=$ADMIN_PASS"
fi

echo ""
echo "Admin:"
echo "  user: $ADMIN_USER"
echo "  pass: $ADMIN_PASS"
echo "  local:  http://127.0.0.1:8888/wp-admin"
echo "  túnel:  (misma user/pass tras Basic Auth de ngrok)"
echo "El default password de wp-env ya no aplica."
