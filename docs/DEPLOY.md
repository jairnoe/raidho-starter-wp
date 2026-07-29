# Deploy — etapa 2 + preview

Happy path día 1: `wp:start` → **`admin:rotate`** → Paso 0 rename → CaC.  
Deploy a un host compartido es **opcional**.

Usa **`http://127.0.0.1:8888`**, no `localhost` (en macOS, IPv6/`[::1]` puede colgar la home). El mu-plugin `fix-ipv6-localhost-redirect.php` mitiga redirects rotos.

Si 8888 está ocupado: otro `wp-env` — `docker ps` / `npm run wp:stop` en ese repo.

## Preferido: staging ya instalado

1. Sube solo el tema (`wp-content/themes/{slug}/`).
2. Sube ACF Pro si aplica.
3. Sync `acf-json/` via el tema.
4. Permalinks → Guardar.

## Pack completo (primera instalación / reset)

```bash
cp .env.example .env   # DEPLOY_URL, THEME_SLUG, ACF_PRO_SRC, FTP…
npm run pack:site
npm run deploy:ftp     # opcional
```

| Automatizable | Manual |
|---------------|--------|
| Pack WP + tema + Pro + uploads + SQL | Crear BD |
| Upload FTP | Importar SQL / `wp-config.php` / permalinks |

## Preview remoto (túneles)

**Happy path con auth:**

```bash
# .env: NGROK_BASIC_USER, NGROK_BASIC_PASS, WP_ADMIN_PASSWORD (tras admin:rotate)
npm run tunnel
```

Comparte la URL de ngrok **+** Basic Auth por un canal privado.  
`/wp-admin` por el túnel usa el password rotado, no el default `password` de wp-env.

Alternativa: `cloudflared tunnel --url http://127.0.0.1:8888` (+ Access preferible).

### Reglas del túnel

- Mu-plugin `tunnel-urls.php`: reescribe `home` / `siteurl` / assets / **srcset** / menús / ACF link / `the_content`.
- **No** pongas `WP_HOME` / `WP_SITEURL` del túnel en `.wp-env.json` (wp-env añade `:8888` y rompe la URL).
- Seeds/menús/links ACF: paths **root-relative** (`/contacto/`), nunca `http://localhost:8888/...`.
- Es la misma instancia local: apaga el túnel al terminar la demo.
