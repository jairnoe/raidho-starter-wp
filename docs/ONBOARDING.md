# Guía de onboarding — raidho-starter-wp

Guía desde cero para desarrolladores nuevos. Objetivo: tener WordPress local corriendo en el Día 1.

**Stack:** Docker → Node/npm → `@wordpress/env` (wp-env) → tema `starter` + ACF Pro.

**URL local:** siempre `http://127.0.0.1:8888` (no `localhost` — en macOS IPv6/`[::1]` puede colgar la home).

---

## 0. Qué es este repo

Starter WordPress genérico con **Configuration as Code**: CPT, taxonomías, ACF JSON, templates y bloques viven en Git. El front arranca vacío (Hello world + shell Barba). No hay CPTs ni UI de cliente hasta que se piden.

Documentación relacionada:

| Doc | Para qué |
|-----|----------|
| [README.md](../README.md) | Happy path Día 1 |
| [docs/AGENT.md](./AGENT.md) | Cómo pedir cambios al agente / Paso 0 |
| [docs/REFERENCE-FRONT.md](./REFERENCE-FRONT.md) | Portar UI desde un front estático (mismo workspace) |
| [docs/DEPLOY.md](./DEPLOY.md) | Túneles y deploy |
| `.cursor/rules/` | Reglas del agente en Cursor |

---

## 1. Requisitos previos

| Herramienta | Para qué | Notas |
|-------------|----------|--------|
| **Docker Desktop** | Contenedores de WordPress (wp-env) | Obligatorio |
| **Node.js 18+** (ideal LTS 20/22) | npm, build CSS, scripts | Incluye npm |
| **Git** | Clonar el repo | — |
| **ngrok** (opcional) | Preview remoto | Solo si usas `npm run tunnel` |

**ACF Pro** ya viene en el repo en `wp-content/plugins/advanced-custom-fields-pro/`. No hace falta copiarlo.

---

## 2. Instalar Docker (macOS)

1. Descarga [Docker Desktop](https://www.docker.com/products/docker-desktop/).
2. Instálalo y ábrelo. Espera a que diga que el engine está corriendo.
3. Verifica en terminal:

```bash
docker --version
docker ps
```

Si `docker ps` falla, Docker no está listo o no tiene permisos.

**Recursos recomendados (Docker Desktop → Settings → Resources):** ~4 GB RAM, 2+ CPUs.

---

## 3. Instalar Node.js

Opción A — [nodejs.org](https://nodejs.org/) (LTS).

Opción B — con nvm:

```bash
nvm install --lts
nvm use --lts
node -v   # >= 18
npm -v
```

---

## 4. Clonar el repositorio

```bash
git clone <url-del-repo> raidho-starter-wp
cd raidho-starter-wp
```

---

## 5. Instalar dependencias y CSS

```bash
npm install
npm run build:css
```

- `npm install` trae Tailwind, Barba, GSAP, `@wordpress/env`, etc.
- `build:css` genera una vez `wp-content/themes/starter/assets/css/output.css` (necesario al clonar o antes de un commit).

### Ver cambios de CSS mientras maquetas

WordPress sirve el CSS **compilado**. Editar `src/input.css` (o clases en PHP) **no se refleja** en el navegador hasta recompilar.

En otra terminal, deja corriendo el watch:

```bash
npm run dev:css
```

Eso recompila `output.css` en cada cambio. Sin `dev:css` (o un `build:css` manual), los estilos no se actualizan en `http://127.0.0.1:8888`.

Flujo típico de maquetación: `wp:start` en una terminal + `dev:css` en otra.
---

## 6. ACF Pro (ya incluido)

Viene en el clone:

```text
wp-content/plugins/advanced-custom-fields-pro/
```

`.wp-env.json` monta ese path y, al arrancar (`afterStart`), activa Pro y quita el free si apareciera. Happy path = **Pro**; free solo emergencia.

---

## 7. Arrancar WordPress

```bash
npm run wp:start
```

La primera vez descarga imágenes Docker y puede tardar varios minutos.

Cuando termine:

| Qué | URL |
|-----|-----|
| Sitio | http://127.0.0.1:8888 |
| Admin | http://127.0.0.1:8888/wp-admin |

Credenciales **default de wp-env** (antes de rotar): usuario `admin` / password `password`.

### Puerto 8888 ocupado

Otro proyecto con wp-env suele estar usando el puerto:

```bash
docker ps
# En el otro repo:
npm run wp:stop
```

---

## 8. Configurar `.env` y rotar admin

```bash
cp .env.example .env
npm run admin:rotate
```

`admin:rotate` genera un password fuerte y lo guarda en `.env` (`WP_ADMIN_PASSWORD`). **Úsalo** para entrar al admin a partir de ahora.

Variables útiles en `.env`:

| Variable | Uso |
|----------|-----|
| `LOCAL_URL` | `http://127.0.0.1:8888` |
| `THEME_SLUG` | `starter` hasta el Paso 0 |
| `ACF_PRO_SRC` | Path local de Pro |
| `WP_ADMIN_*` | Tras `admin:rotate` |
| `NGROK_*` | Solo preview con túnel |

**Nunca** commits `.env`.

---

## 9. Checklist Día 1 (verificar que todo OK)

1. Home muestra **Hello world**.
2. En DevTools → Network: scripts `barba` y `page-transitions`.
3. Admin → **Custom Fields** y **Site Settings** (Options Page de Pro).
4. Pro activo, free no instalado:

```bash
npx wp-env run cli wp plugin list
```

5. Cuando el sitio tenga nombre: **Paso 0 — rename** (ver abajo / [AGENT.md](./AGENT.md)).

---

## 10. Comandos del día a día

| Comando | Qué hace |
|---------|----------|
| `npm run wp:start` | Levanta WordPress |
| `npm run wp:stop` | Para contenedores |
| `npm run wp:destroy` | Borra el entorno (pierdes BD local) |
| `npm run build:css` | Compila CSS una vez (clone / CI / sin watch) |
| `npm run dev:css` | **Watch CSS** — obligatorio al maquetar para ver cambios en el browser |
| `npm run admin:rotate` | Nuevo password admin → `.env` |
| `npm run tunnel` | Preview ngrok + Basic Auth |
| `npx wp-env run cli wp …` | WP-CLI dentro del contenedor |

Ejemplos WP-CLI:

```bash
npx wp-env run cli wp plugin list
npx wp-env run cli wp rewrite flush --hard
npx wp-env run cli wp theme list
```

---

## 11. Paso 0 — Rename (antes de maquetar / CPTs)

No construyas encima del prefijo `starter`. Cuando el cliente tenga nombre:

```text
Este sitio se llama "{Nombre}" (slug: {slug}).
Antes de crear CPTs o UI:
1. Renombra el tema starter → {slug} (carpeta, text domain, prefijos, handles, ACF options, Barba markers).
2. Actualiza .wp-env.json, docs y cursor rules.
3. Activa el tema y blogname/blogdescription.
No dejes restos del prefijo "starter".
```

Verificar: `rg -i starter` (salvo docs históricas). Detalle en [AGENT.md](./AGENT.md).

---

## 12. Dónde va cada cosa (mapa rápido)

| Qué | Dónde |
|-----|--------|
| CPT / tax | `inc/post-types/`, `inc/taxonomies/` |
| ACF | `acf-json/` + `inc/acf.php` |
| Site Settings | Options `starter-site-settings` + JSON |
| Componentes | `components/{n}/{n}.php` |
| Bloques ACF | `blocks/{slug}/` + `inc/blocks.php` |
| Shell Barba | `header.php` / `footer.php` (chrome fuera del container) |
| Túnel URLs | `mu-plugins/tunnel-urls.php` |

Principio: **CaC en Git**. No configures solo en el admin (se pierde al clonar).

---

## 13. Preview remoto (opcional)

```bash
# .env con NGROK_BASIC_USER / NGROK_BASIC_PASS y password rotado
npm run tunnel
```

Comparte la URL de ngrok **y** el Basic Auth por canal privado. No pongas `WP_HOME` del túnel en `.wp-env.json`. Más detalle: [DEPLOY.md](./DEPLOY.md).

---

## 14. Troubleshooting

| Problema | Qué hacer |
|----------|-----------|
| `docker ps` falla | Abrir Docker Desktop y esperar al engine |
| `wp:start` falla | Docker corriendo; puerto 8888 libre; reintentar |
| Home cuelga con `localhost` | Usar `http://127.0.0.1:8888` |
| Puerto 8888 en uso | `docker ps` + `npm run wp:stop` en el otro repo |
| No aparece Site Settings | ¿Existe `wp-content/plugins/advanced-custom-fields-pro/`? `npx wp-env run cli wp plugin list` |
| CSS no cambia en el browser | ¿Está corriendo `npm run dev:css`? Sin watch, corre `npm run build:css` y hard-refresh |
| Entorno “roto” | `npm run wp:destroy` → `npm run wp:start` (borra BD local) |

---

## 15. Flujo mental del proyecto

```text
Docker + Node
    → npm install / build:css
    → npm run wp:start   (ACF Pro ya en el repo → afterStart lo activa)
    → .env + admin:rotate
    → Verificar Hello world + Site Settings
    → Paso 0 rename
    → (opc) montar frontend/ de referencia — ver REFERENCE-FRONT.md
    → Maquetar: npm run dev:css (otra terminal) para ver cambios CSS
    → Pedir CPTs / ACF / UI / chrome (CaC)
    → (opc) tunnel / deploy
```
