# Cómo trabajar con el agente en este proyecto

Guía para pedir cambios al agente (Cursor) en este starter WordPress.  
**Tú editas contenido.** El agente crea/cambia **estructura** versionada en Git.

El starter nace vacío: Hello world + shell Barba. El **primer prompt de cliente** es el **Paso 0 (rename)**.

Preferir **`http://127.0.0.1:8888`** (no `localhost`) — ver [DEPLOY.md](./DEPLOY.md).

---

## Día 1 (checklist)

1. `npm install && npm run build:css && npm run wp:start` — ACF Pro ya está en el repo.
2. ¿Pro activo? (`wp plugin list`) — free no debe quedar instalado.
3. `cp .env.example .env` → `npm run admin:rotate`
4. Home: Hello world + Barba en network/scripts.
5. Admin → Site Settings (Options Page).
6. Paso 0 rename cuando nombres el sitio.
7. Si hay front/diseño estático previo → montar referencia y portar: [REFERENCE-FRONT.md](./REFERENCE-FRONT.md).

---

## Paso 0 — Rename del tema

```text
Este sitio se llama “{Nombre}” (slug: {slug}).
Antes de crear CPTs o UI:
1. Renombra el tema starter → {slug} (carpeta, text domain, prefijos, handles, ACF options, Barba markers en page-transitions.js).
2. Actualiza .wp-env.json, docs y cursor rules.
3. Activa el tema y blogname/blogdescription.
No dejes restos del prefijo “starter”.
```

Verificar: `rg -i starter` (salvo docs históricas).

---

## Front de referencia (port UI)

Si el cliente ya tiene un front estático (Eleventy, HTML, otro repo), **no** reinventes a ojo: monta la carpeta en el mismo workspace y porta con CaC.

Guía completa + prompt plantilla: [REFERENCE-FRONT.md](./REFERENCE-FRONT.md).

Resumen:

1. Paso 0 hecho.
2. `frontend/` (o similar) en la raíz del repo WP — gitignored por defecto.
3. Prompt: inventariar → mapa CPT/ACF → chrome → CPT principal → home → seeds.
4. Al estabilizar: retirar el front; producción = solo WP.

---

## ACF Pro (happy path)

- Asumir **Pro**: Options Page, Gallery, Repeater, Flexible Content, clones.
- Free = emergencia. No inventar Settings API / textarea “una línea” si Pro está en el stack.
- Local JSON en `acf-json/`. Site Settings de contacto ya viene de ejemplo.

### Copy del admin = para el cliente

Labels, `instructions`, choices y `description` de bloques los lee el **cliente**.  
No: “sección 2 del single”, tokens CSS, paths de Git. Sí: qué hace el campo y cómo se ve.

### Seed = shape del editor

WP-CLI / `wp eval` debe dejar el **mismo shape** que guardar desde el panel (repeaters planos + `_field` keys).  
Tras seedear: abrir el post en admin y comprobar filas/imágenes (prueba “parece hecho a mano”).

### Position + Gutenberg

Repeaters/galerías → `position: normal` (no `side`).  
Si el box sigue en sidebar tras cambiar JSON: user meta `meta-box-order_{post_type}` — resetear o arrastrar. Hard refresh.

### Categoría de bloques

Registrar categoría del tema (`block_categories_all`) o usar core (`design`, `media`, `text`).  
**Nunca** `category: 'layout'` (no existe → el bloque no aparece en el insertador).

---

## Cómo pedir un CPT

```text
Crea un CPT “Publicaciones” (slug: publication).
- Archive /publications/
- Campos ACF: año, subtítulo, enlace; position normal
- Classic editor si es field-heavy
- Local JSON + seed 3 ejemplos (shape de editor)
- Menús/links root-relative
```

---

## Bloques ACF — naming por composición

| Bien | Mal |
|------|-----|
| `hero`, `media-text`, `project-grid` | `index-hero`, `about-hero` |

Variantes vía ACF, no dos bloques por página.

---

## Barba / page transitions (default)

- Global: `barba.umd.js` + `page-transitions.js` + GSAP para fades.
- Shell: chrome **fuera** de `[data-barba="container"]`.
- Tras navegación: evento **`starter:pageview`** — features JS re-init con guard (`data-starter-inited`).
- Tras leave: colapsar current (`display:none`) para evitar pop-in.
- GSAP ScrollTrigger / Swiper: lazy por feature.

Ejemplo re-init:

```js
function init() {
  document.querySelectorAll("[data-my-feature]").forEach(function (el) {
    if (el.getAttribute("data-starter-inited") === "1") return;
    el.setAttribute("data-starter-inited", "1");
    // setup…
  });
}
document.addEventListener("DOMContentLoaded", init);
document.addEventListener("starter:pageview", init);
```

---

## CSS / Tailwind — barebones

Solo utilidades que la sección necesita. No purgar “unused CSS” en discovery.

---

## Seeds / URLs

Menús y campos Link ACF: **`/ruta/`**, nunca `http://localhost:8888/...` (rompe túneles).

---

## Preview (túnel)

```bash
npm run admin:rotate
npm run tunnel   # Basic Auth; ver DEPLOY.md
```

No `WP_HOME` del túnel en `.wp-env.json`.

---

## Qué NO pedir

| Pedido | Por qué |
|--------|---------|
| Solo en el admin | Se pierde al clonar |
| CPT UI plugin | CPT en código |
| `category: layout` | No aparece en el insertador |
| GSAP/Swiper globales “por si acaso” | Lazy; Barba es la excepción |
| Absolutas localhost en seeds | Rompe ngrok |
| Purga CSS mid-discovery | Frena maquetación |

---

## Comandos

```bash
npm run wp:start
npm run build:css
npm run admin:rotate
npm run tunnel
npx wp-env run cli wp plugin list
npx wp-env run cli wp rewrite flush --hard
```

Local: http://127.0.0.1:8888  
Docs: [README](../README.md) · [ONBOARDING](./ONBOARDING.md) · [REFERENCE-FRONT](./REFERENCE-FRONT.md) · [DEPLOY](./DEPLOY.md) · `.cursor/rules/`
