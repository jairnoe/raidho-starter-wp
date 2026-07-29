#!/usr/bin/env bash
# Pack a clean WordPress install + theme (+ optional ACF Pro) + uploads + SQL.
# Etapa 2 — not the day-1 happy path. See docs/DEPLOY.md.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ -f "${ROOT}/.env" ]]; then
	set -a
	# shellcheck disable=SC1091
	source "${ROOT}/.env"
	set +a
fi

REMOTE_URL="${DEPLOY_URL:-https://example.com}"
LOCAL_URL="${LOCAL_URL:-http://localhost:8888}"
THEME_SLUG="${THEME_SLUG:-starter}"
OUT="${ROOT}/dist/pack"
CACHE="${ROOT}/dist/.cache"
THEME_SRC="${ROOT}/wp-content/themes/${THEME_SLUG}"
PLUGIN_SRC="${ACF_PRO_SRC:-${ROOT}/wp-content/plugins/advanced-custom-fields-pro}"
WP_ZIP_URL="${WP_ZIP_URL:-https://wordpress.org/latest.zip}"
WP_ZIP="${CACHE}/wordpress-latest.zip"
SQL_NAME="${THEME_SLUG}-pack.sql"

if [[ ! -d "${THEME_SRC}" ]]; then
	echo "Theme not found: ${THEME_SRC}"
	echo "Set THEME_SLUG in .env (after Paso 0 rename)."
	exit 1
fi

echo "==> Build CSS"
npm run build:css

echo "==> Prepare ${OUT}"
rm -rf "${OUT}"
mkdir -p "${OUT}" "${CACHE}"

echo "==> Download clean WordPress (${WP_ZIP_URL})"
if [[ ! -f "${WP_ZIP}" ]] || [[ "${WP_FORCE_DOWNLOAD:-}" == "1" ]]; then
	curl -fL --retry 3 -o "${WP_ZIP}.tmp" "${WP_ZIP_URL}"
	mv "${WP_ZIP}.tmp" "${WP_ZIP}"
else
	echo "    (using cached ${WP_ZIP})"
fi

echo "==> Extract WordPress"
TMP_WP="$(mktemp -d)"
unzip -q "${WP_ZIP}" -d "${TMP_WP}"
if [[ -d "${TMP_WP}/wordpress" ]]; then
	rsync -a "${TMP_WP}/wordpress/" "${OUT}/"
else
	rsync -a "${TMP_WP}/" "${OUT}/"
fi
rm -rf "${TMP_WP}"
rm -f "${OUT}/wp-config.php"

echo "==> Copy theme (${THEME_SLUG})"
mkdir -p "${OUT}/wp-content/themes"
rsync -a --delete \
	--exclude 'node_modules' \
	--exclude '.git' \
	--exclude 'src' \
	--exclude '*.map' \
	--exclude '.pack.sql' \
	"${THEME_SRC}/" "${OUT}/wp-content/themes/${THEME_SLUG}/"

if [[ -d "${PLUGIN_SRC}" ]]; then
	echo "==> Copy ACF Pro"
	mkdir -p "${OUT}/wp-content/plugins"
	rsync -a --delete \
		--exclude '.git' \
		"${PLUGIN_SRC}/" "${OUT}/wp-content/plugins/$(basename "${PLUGIN_SRC}")/"
else
	echo "!! ACF Pro not found at ${PLUGIN_SRC} — skip (install free ACF or set ACF_PRO_SRC)"
fi

rm -rf "${OUT}/wp-content/plugins/hello.php" "${OUT}/wp-content/plugins/akismet"

echo "==> Export uploads from wp-env"
if npx wp-env run cli sh -c 'test -d /var/www/html/wp-content/uploads'; then
	npx wp-env run cli sh -c 'tar -C /var/www/html/wp-content -cf - uploads' \
		| tar -C "${OUT}/wp-content" -xf -
else
	echo "!! No uploads directory in wp-env — skip"
fi

echo "==> Export database (${LOCAL_URL} → ${REMOTE_URL})"
TMP_SQL="${THEME_SRC}/.pack.sql"
rm -f "${TMP_SQL}"
npx wp-env run cli wp search-replace \
	"${LOCAL_URL}" \
	"${REMOTE_URL}" \
	--all-tables \
	--report-changed-only \
	--export="/var/www/html/wp-content/themes/${THEME_SLUG}/.pack.sql"
mv "${TMP_SQL}" "${OUT}/${SQL_NAME}"

cat > "${OUT}/README-DEPLOY.txt" <<EOF
WordPress pack (etapa 2)
========================
Target: ${REMOTE_URL}
Theme:  ${THEME_SLUG}

Contents:
- WordPress core (latest.zip)
- Theme ${THEME_SLUG}
- Optional ACF Pro (if present locally)
- uploads from local wp-env
- ${SQL_NAME} (URLs already point to ${REMOTE_URL})

Manual steps on the server
--------------------------
1) Create an empty MySQL database and user.
2) Upload the pack contents EXCEPT ${SQL_NAME} and README-DEPLOY.txt
   (or upload then delete them from the public web root).
3) Copy wp-config-sample.php → wp-config.php with DB credentials
   (or let the WP installer create it, then import SQL).
4) Import ${SQL_NAME} into the database.
5) Visit ${REMOTE_URL}/wp-admin/
   - Activate theme + ACF if needed
   - Settings → Permalinks → Save

Prefer staging with WP already installed? Sync only the theme folder instead
of this full pack. See docs/DEPLOY.md.

Commands:
  npm run pack:site
  npm run deploy:ftp   # optional FTP upload (.env)

Force WP re-download:
  WP_FORCE_DOWNLOAD=1 npm run pack:site
EOF

echo "==> Done: ${OUT}"
du -sh "${OUT}" "${OUT}/${SQL_NAME}" "${WP_ZIP}" 2>/dev/null || true
