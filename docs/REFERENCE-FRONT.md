# Front de referencia en el mismo workspace

Método de trabajo para proyectos **cliente** derivados de este starter: montar un frontend estático (Eleventy, HTML exportado, otro repo) en una carpeta del **mismo workspace** para que el agente porte UI + modelo de contenido a WordPress (Configuration as Code).

**No** es código runtime del starter. El starter nace vacío (Hello world + Barba). El front de referencia se añade **por proyecto**, después del [Paso 0 rename](./AGENT.md#paso-0--rename-del-tema).

---

## Idea en una frase

El front estático es la **fuente de verdad visual y de contenido de partida**. WordPress es el **destino**. Mientras portas, ambos viven en el mismo workspace. Cuando el port está estable, **retiras** la carpeta del front. Producción = solo WordPress.

---

## Por qué funciona

| Sin front en el workspace | Con front en el workspace |
|---------------------------|---------------------------|
| El agente inventa markup o pide capturas | Lee HTML/Liquid/NJK, clases Tailwind, JS y estructura real |
| El contenido hay que dictarlo a mano | Mapea listados hardcodeados → CPTs, campos, seeds WP-CLI |
| Tokens/CSS se adivinan | Copia tokens, fonts e iconos desde el front |
| Riesgo de “dos apps” eternas | Contrato: front = referencia **temporal** |

El agente **no** debe limitarse a “pegar HTML en PHP”. Debe:

1. Inferir el **modelo de contenido** (CPT, taxonomía, Options, bloque).
2. Versionar estructura en Git (`inc/`, `acf-json/`, templates, componentes).
3. Reutilizar componentes PHP; naming de bloques por composición.
4. Seedear demo en el **shape del editor** (ver [AGENT.md](./AGENT.md)).

---

## Prerrequisitos en este starter

1. Día 1 OK — [ONBOARDING.md](./ONBOARDING.md) (`wp:start`, ACF Pro, `admin:rotate`).
2. **Paso 0** — tema renombrado (no portes encima de `starter`).
3. Cursor abierto en la **raíz del repo WP** (tema + front en el mismo contexto).

Al maquetar CSS del tema: `npm run dev:css` en otra terminal.

---

## Cómo montarlo

### 1. Repo WP abierto

```bash
cd ~/Desktop/dev/mi-cliente-wp
```

### 2. Traer el front (elige una)

**A. Copiar** (snapshot fijo)

```bash
cp -R ~/Desktop/dev/mi-cliente-front ./frontend
```

**B. Clonar**

```bash
git clone <url-del-front> frontend
```

**C. Symlink** (el front sigue vivo en su repo)

```bash
ln -s ~/Desktop/dev/mi-cliente-front ./frontend
```

**Nombre sugerido:** `frontend/`, `{cliente}-front/`, o `reference-front/`.  
No mezcles el build del front con `wp-content/`.

Por defecto el starter ignora `frontend/`, `reference-front/` y `*-front/` en Git (ver `.gitignore`).

### 3. ¿Entra en Git?

| Caso | Recomendación |
|------|----------------|
| Snapshot solo para el port | Dejar gitignored (default) o borrar al cerrar |
| Equipo necesita el mismo snapshot un tiempo | Commit temporal OK; borrar en un PR al estabilizar |
| Symlink | No commitear un symlink roto; documentar la ruta local |

Media scrapeada / pesada: gitignore. Media de verdad = Media Library de WP.

### 4. Prompt inicial (plantilla)

```text
Actúa como arquitecto WP moderno (Configuration as Code).

Fuente de verdad visual y de contenido de partida:
  ./frontend/   (estático — NO es la app en producción)

Destino:
  wp-content/themes/{tema}/

Objetivo:
- Portar chrome (header/footer), luego CPTs/taxonomías/ACF según el front
- Templates + componentes PHP; bloques ACF por composición (no por página)
- Seeds WP-CLI con el shape que el editor guarda
- Tailwind/tokens desde el front → tema; vendors lazy (excepto Barba global)
- Re-init JS en el evento {slug}:pageview (tras Barba)

NO:
- Mantener el front como app paralela a largo plazo
- Copiar HTML a ciegas sin modelo de contenido
- Configurar estructura solo en el admin
- Inventar otra composición visual

Empieza por: inventariar páginas/componentes del front y proponer el mapa CPT/campos.
Luego implementa por incrementos (chrome → CPT principal → home → resto).
```

Ajusta la ruta si la carpeta no se llama `frontend/`.

---

## Orden de trabajo (referencia)

1. **Inventario del front** — layouts, partials, páginas, JS (GSAP/Swiper), tokens, contenido hardcodeado.
2. **Chrome** — header/footer fuera de `[data-barba="container"]` + menús + Site Settings.
3. **CPT principal** — el que estructura el sitio + tax + ACF Local JSON + archive/single.
4. **Home y piezas reutilizables** — componentes/bloques; no duplicar por página.
5. **Resto de tipos** — según el front.
6. **Contenido** — seed WP-CLI; comprobar en admin que “parece editado a mano”.
7. **Vendors / polish** — Barba ya global; lazy GSAP/Swiper; `*:pageview`; túnel si aplica.
8. **Retirar el front** — borrar o dejar de trackear cuando WP es la fuente de verdad.

---

## Qué pedirle al agente en cada incremento

| Quieres… | Di algo como… |
|----------|----------------|
| Mapear dominio | “Lee `frontend/src/` y propón CPTs, taxonomías y campos ACF; no implementes aún.” |
| Portar una página | “Porta el archive desde `frontend/...` a `archive-{cpt}.php` + componente card; campos en Local JSON.” |
| Traer contenido | “Seedear N items desde el front con WP-CLI; shape de editor; imágenes a Media Library.” |
| Paridad visual | “Compara markup/clases con el partial X del front; no inventes otra composición.” |
| Cierre | “Port estable: elimina `frontend/` y actualiza docs para que no se asuma esa carpeta.” |

---

## Reglas para el agente

- Front = **referencia de lectura**, no segundo runtime.
- Preferir **componentes / bloques** sobre pegar páginas enteras.
- Estructura en **Git**; contenido editorial en **admin** (tras seed).
- Labels ACF = copy para el **cliente**, no notas del front (“sección 2 del index”).
- Menús/links: **root-relative** (`/proyectos/`), nunca `http://localhost:8888/...`.
- Al terminar: **no** reintroducir un front estático paralelo en el repo WP.

---

## Checklist al cerrar el port

- [ ] WP refleja UI y rutas del front (o desviaciones documentadas)
- [ ] CPTs / ACF / templates versionados
- [ ] Contenido demo editable en admin
- [ ] `frontend/` eliminado o sigue gitignored; docs sin asumir esa carpeta
- [ ] README/AGENT no dicen “abre el front estático para producción”

---

## Docs relacionadas

- [ONBOARDING.md](./ONBOARDING.md) — Docker → Día 1
- [AGENT.md](./AGENT.md) — Paso 0, prompts, ACF, Barba, seeds
- [DEPLOY.md](./DEPLOY.md) — túneles + pack
- [README](../README.md) — happy path
