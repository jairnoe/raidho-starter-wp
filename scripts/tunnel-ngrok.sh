#!/usr/bin/env bash
# Start ngrok → local wp-env (8888) with HTTP Basic Auth (Traffic Policy).
# Credentials: NGROK_BASIC_USER / NGROK_BASIC_PASS in .env (never commit).
#
# Usage: npm run tunnel

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -f .env ]]; then
	set -a
	# shellcheck disable=SC1091
	source .env
	set +a
fi

if ! command -v ngrok >/dev/null 2>&1; then
	echo "ngrok no está instalado o no está en PATH." >&2
	exit 1
fi

USER_NAME="${NGROK_BASIC_USER:-starter-preview}"
PASS="${NGROK_BASIC_PASS:-}"

if [[ -z "$PASS" ]]; then
	if command -v openssl >/dev/null 2>&1; then
		PASS="$(openssl rand -base64 18 | tr -d '/+=' | head -c 20)"
	else
		PASS="$(LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 20)"
	fi
	echo ""
	echo "NGROK_BASIC_PASS no estaba en .env — generado para esta sesión:"
	echo "  NGROK_BASIC_USER=$USER_NAME"
	echo "  NGROK_BASIC_PASS=$PASS"
	echo "Añádelos a .env para reutilizar la misma contraseña."
	echo ""
fi

POLICY_FILE="$(mktemp -t starter-ngrok-policy.XXXXXX.yml)"
cleanup() {
	rm -f "$POLICY_FILE"
}
trap cleanup EXIT

CREDS_YAML="$(python3 -c 'import json,sys; print(json.dumps(sys.argv[1] + ":" + sys.argv[2]))' "$USER_NAME" "$PASS")"

cat >"$POLICY_FILE" <<EOF
on_http_request:
  - actions:
      - type: basic-auth
        config:
          credentials:
            - ${CREDS_YAML}
          enforce: true
EOF

echo "Túnel con Basic Auth → http://127.0.0.1:8888"
echo "Usuario túnel: $USER_NAME"
echo "Comparte la URL de ngrok + estas credenciales por un canal privado."
echo "Ctrl+C para cerrar el túnel."
echo ""

exec ngrok http 8888 --traffic-policy-file="$POLICY_FILE"
