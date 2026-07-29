# Site Starter — WordPress

Starter genérico con **Configuration as Code**. Front mínimo: Hello world dentro del shell Barba. Sin CPTs ni UI de cliente.

## Happy path (Día 1)

```bash
npm install
npm run build:css

# ACF Pro ya está en wp-content/plugins/advanced-custom-fields-pro/

npm run wp:start
# Sitio:  http://127.0.0.1:8888   (preferir 127.0.0.1, no localhost)
# Admin:  http://127.0.0.1:8888/wp-admin

cp .env.example .env
npm run admin:rotate   # cambia el password default de wp-env
```

1. Verifica **Hello world** + Barba (scripts `barba` / `page-transitions` en la página).  
2. Admin → **Custom Fields** / **Site Settings** (Options Page Pro).  
3. **Paso 0:** nombra el sitio → rename completo del tema. Ver [docs/AGENT.md](docs/AGENT.md).  
4. Pide chrome, CPTs, fields, UI. Si hay diseño/front estático previo: [docs/REFERENCE-FRONT.md](docs/REFERENCE-FRONT.md).  
5. Deploy / túnel = [docs/DEPLOY.md](docs/DEPLOY.md).

Si el puerto 8888 está ocupado: `docker ps` y para el otro `wp-env` (`npm run wp:stop` en ese repo).

## ACF Pro

| Qué | Dónde |
|-----|--------|
| Plugin | `wp-content/plugins/advanced-custom-fields-pro/` (incluido en el repo) |
| wp-env | monta Pro; `afterStart` activa Pro (no instala free) |
| Site Settings | Options Page + `acf-json/group_starter_site_settings.json` |
| Pack | `ACF_PRO_SRC` en `.env` |

Happy path = **Pro**. Free solo emergencia.

## Scripts

| Comando | Qué hace |
|---------|----------|
| `npm run build:css` / `dev:css` | CSS del tema |
| `npm run wp:start` / `stop` | WordPress local |
| `npm run admin:rotate` | Password admin fuerte → `.env` |
| `npm run tunnel` | ngrok + Basic Auth → `127.0.0.1:8888` |
| `npm run pack:site` / `deploy:ftp` | Deploy etapa 2 |

## Vendors

- **Barba + page-transitions** — global (default del starter). Shell en `header.php` / `footer.php`.
- Evento `starter:pageview` — re-init de features JS.
- **GSAP / Swiper** — lazy (`starter_enqueue_gsap()`, etc.). GSAP también lo pide Barba para fades.

## Docs

- [docs/ONBOARDING.md](docs/ONBOARDING.md) — guía desde cero (Docker → Día 1)
- [docs/AGENT.md](docs/AGENT.md) — prompts, Paso 0, ACF, Barba, seeds  
- [docs/REFERENCE-FRONT.md](docs/REFERENCE-FRONT.md) — portar UI desde un front estático en el mismo workspace  
- [docs/DEPLOY.md](docs/DEPLOY.md) — túneles + pack  
- `.cursor/rules/` — reglas del agente
