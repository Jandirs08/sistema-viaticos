---
name: Viáticos ERP — Fundación Romero
description: Sistema de diseño para el ERP interno de solicitudes y rendiciones de viáticos.
colors:
  terracotta-flame: "#da5b3e"
  terracotta-ember: "#b84930"
  terracotta-mist: "#fde9e3"
  terracotta-ink: "#7f2d1d"
  paper: "#f3f6f9"
  surface: "#ffffff"
  surface-strong: "#f7f8fb"
  border: "#e4e7ec"
  border-light: "#edf1f5"
  ink: "#101828"
  ink-muted: "#667085"
  ink-light: "#98a2b3"
  archive-navy: "#17202b"
  archive-navy-hover: "#223042"
  state-pending-bg: "oklch(96% 0.06 80)"
  state-pending-ink: "oklch(34% 0.11 65)"
  state-approved-bg: "oklch(95% 0.06 150)"
  state-approved-ink: "oklch(32% 0.10 150)"
  state-observed-bg: "oklch(96% 0.05 52)"
  state-observed-ink: "oklch(37% 0.12 40)"
  state-rejected-bg: "oklch(96% 0.04 22)"
  state-rejected-ink: "oklch(35% 0.13 22)"
  state-review-bg: "oklch(95% 0.022 325)"
  state-review-ink: "oklch(38% 0.10 325)"
typography:
  display:
    fontFamily: "Sora, Manrope, sans-serif"
    fontSize: "1.75rem"
    fontWeight: 400
    lineHeight: 1.18
    letterSpacing: "0"
  title:
    fontFamily: "Manrope, sans-serif"
    fontSize: "0.975rem"
    fontWeight: 600
    lineHeight: 1.35
  body:
    fontFamily: "Manrope, -apple-system, 'Segoe UI', sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Manrope, sans-serif"
    fontSize: "0.6875rem"
    fontWeight: 700
    lineHeight: 1
    letterSpacing: "0.06em"
  mono:
    fontFamily: "ui-monospace, 'SF Mono', Menlo, Consolas, monospace"
    fontSize: "0.8125rem"
    fontWeight: 500
    lineHeight: 1.4
    letterSpacing: "-0.01em"
rounded:
  sm: "8px"
  md: "12px"
  lg: "18px"
  pill: "999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.terracotta-flame}"
    textColor: "{colors.surface}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  button-primary-hover:
    backgroundColor: "{colors.terracotta-ember}"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    padding: "8px 16px"
  button-ghost:
    backgroundColor: "transparent"
    textColor: "{colors.ink-muted}"
    rounded: "{rounded.sm}"
    padding: "6px 10px"
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.ink}"
    rounded: "{rounded.sm}"
    padding: "9px 12px"
  card:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.md}"
    padding: "20px"
  badge:
    rounded: "{rounded.pill}"
    padding: "4px 10px"
    textColor: "{colors.ink}"
  sidebar:
    backgroundColor: "{colors.archive-navy}"
    textColor: "{colors.surface}"
    width: "240px"
  topbar:
    backgroundColor: "{colors.surface}"
    height: "60px"
---

# Design System: Viáticos ERP

## 1. Overview

**Creative North Star: "El Expediente Formal"**

Cada solicitud de viático es un expediente: tiene carátula (badge de estado), contenido (monto, motivo, gastos), historial auditable y una decisión final. El sistema se comporta como un archivador premium, no como un dashboard SaaS. El archivador es oscuro (navy), el papel es claro (cool gray tintado), y el sello administrativo es terracota: marca quien aprueba, marca qué está pendiente, marca dónde mirar primero.

La densidad es alta pero no hostil. Admin ve la bandeja completa de una sola vista; colaborador ve su solicitud con jerarquía obvia. La paleta es **Restrained**: una sola familia cromática (terracota) carga la acción; todo lo demás son neutros tintados y estados codificados por hue. Sin gradientes decorativos, sin tarjetas-clichés, sin hero-metrics.

**Lo que el sistema rechaza explícitamente (PRODUCT.md anti-references):**
- Interfaces gubernamentales recargadas (formularios enormes, tablas sin jerarquía).
- Dashboards SaaS genéricos con gradientes, hero-metrics y tarjetas idénticas.
- Excel con estilos: celdas bordeadas sin espacio visual.

**Key Characteristics:**
- Sidebar navy fija (240px) + topbar claro (60px) + área de contenido con scroll único.
- Terracota aparece solo en: acciones primarias, estados activos de navegación, sellos de aprobación. ≤10% de superficie en cualquier pantalla.
- Badges pill con dot de color y 6 estados documentados por flujo (solicitud + rendición).
- Tipografía: Sora para títulos (display, expediente, secciones), Manrope para todo lo demás.
- Sombras cálidas tintadas (rgba 35,31,27) en vez de negro neutro.
- Radius escalonado: 8 / 12 / 18 px. Pill 999px para badges y chips.

## 2. Colors: La Paleta del Expediente

Paleta **Restrained**: un solo acento cargando la acción, neutros tintados para el resto, OKLCH para los estados del flujo (parity cromática entre solicitud y rendición).

### Primary

- **Terracotta Flame** (`#da5b3e`): El sello administrativo. Usado en: botón primario, icono de logo, borde activo de input, focus ring (`rgba(218,91,62,.15)`), dot del item activo de sidebar. Su aparición marca una acción que requiere decisión.
- **Terracotta Ember** (`#b84930`): Hover del primario. Shadow-glow en botones (`rgba(186,76,51,.22)`).
- **Terracotta Mist** (`#fde9e3`): Tint de superficie para paneles de estado solicitud, badges hover, chips contextuales. Nunca como fondo global.
- **Terracotta Ink** (`#7f2d1d`): Reservado para texto sobre fondo mist cuando se requiere máxima legibilidad.

### Neutral

- **Paper** (`#f3f6f9`): Fondo global (`#erp-content`). Gris frío tintado, no blanco crudo. Contrasta las cards blancas.
- **Surface** (`#ffffff`): Tarjetas, modales, inputs, topbar.
- **Surface Strong** (`#f7f8fb`): Filas pares de tabla, header de wizard, fondos de secciones internas.
- **Border** (`#e4e7ec`) y **Border Light** (`#edf1f5`): Divisores de card, tabla, sección.
- **Ink** (`#101828`): Texto primario. Casi negro pero con tinte navy.
- **Ink Muted** (`#667085`): Subtítulos, meta, labels.
- **Ink Light** (`#98a2b3`): Placeholder, separadores tipográficos.

### Archive (sidebar solo)

- **Archive Navy** (`#17202b`): Fondo del sidebar. Oscuro, estable, contenedor del árbol de navegación.
- **Archive Navy Hover** (`#223042`): Hover de items.
- Activo de nav: fondo `rgba(218,91,62,.18)` con texto `#f4a58f`. El único punto donde terracota entra al archivador.

### State (flow-coded, OKLCH)

Cada estado del flujo (pendiente → aprobada/observada/rechazada, no_iniciada → en_proceso → en_revision → aprobada/observada/rechazada) tiene un hue fijo. La terna bg/text/border mantiene la misma familia cromática con distinta luminosidad y chroma. Lista canónica:

| Estado | Hue (OKLCH) | Uso |
|---|---|---|
| Pendiente | `80` (amber) | Anticipo esperando decisión admin. |
| Aprobada | `150` (green) | Solicitud/rendición aceptada. |
| Observada | `52 / 65` (orange) | Requiere corrección del colaborador. |
| Rechazada | `22` (red) | Cerrada negativamente. |
| No iniciada | `60` (warm gray) | Rendición sin primer gasto. |
| En proceso | `42` (copper) | Rendición con gastos parciales. |
| En revisión | `325` (magenta) | Rendición finalizada esperando admin. |

### Named Rules

**The One Sello Rule.** Terracota aparece en ≤10% de cualquier pantalla. Su rareza es el punto: cuando el usuario la ve, sabe que es una acción o un estado que le concierne. Jamás usarla como fondo decorativo, gradiente de hero ni stripe accent.

**The Hue-Per-Estado Rule.** Cada estado de flujo tiene un hue único y estable en OKLCH. Nunca se pinta un estado con un hue prestado de otro. Agregar un estado nuevo obliga a registrar su terna bg/text/border en `app-layout-header.php` Y su label en `ViaticosEstadoUI`.

**The Warm Neutral Rule.** Ninguna sombra usa negro puro. Todas tintan hacia `rgba(35,31,27, α)` para preservar la calidez del sistema sobre el cool-gray del fondo.

## 3. Typography

**Display Font:** Sora (con Manrope, sans-serif como fallback)
**Body Font:** Manrope (con -apple-system, 'Segoe UI', sans-serif)
**Mono Font:** ui-monospace system stack (para cta. contable, montos en liquidación, RUC)

**Character:** Sora aporta autoridad institucional sin rigidez de serif; Manrope hace el peso del contenido con buena lectura en tablas densas. Tabular nums (`font-feature-settings: "tnum" 1`) en todo el sistema: los montos se alinean por posición sin tricks.

### Hierarchy

- **Display** (400, `1.75rem` / 28px, line-height `1.18`): Title de página (`.page-header-left h1`). Sora. Peso 400 deliberado: autoridad sin grasa.
- **Title** (600, `0.975rem`, line-height `1.35`): Encabezado de card, secciones de modal. Manrope.
- **Body** (400, `0.875rem` / 14px, line-height `1.6`): Texto general, celdas de tabla, campos de formulario.
- **Label** (700, `0.6875rem` / 11px, letter-spacing `0.06em`, UPPERCASE): Meta-labels (`di-label`, `gaf-label`, `sidebar-section-label`). Función de señalización, no de lectura.
- **Mono** (500, `0.8125rem`, letter-spacing `-0.01em`, `tabular-nums`): Códigos de cuenta contable, montos en liquidación, números de comprobante.

### Named Rules

**The Tabular Money Rule.** Todo monto se renderiza con `font-variant-numeric: tabular-nums`. En tablas alineadas a la derecha, la coma decimal cae siempre en el mismo píxel sin importar la cifra.

**The Label As Signal Rule.** Los labels uppercase `0.6875rem` no son texto de lectura; son señales visuales. Nunca encadenar dos labels seguidos sin contenido entre ellos: rompe la jerarquía.

## 4. Elevation

El sistema es mayormente **plano con sombras ambientales cálidas**. La profundidad viene del contraste entre fondo paper y surface blanco, reforzada por una sombra sutil en cards y una sombra ambient-medium en hero/destacados. Nunca profundidad decorativa: cada sombra responde a un rol funcional (card, hover, modal).

### Shadow Vocabulary

- **shadow-sm** (`0 1px 2px rgba(35,31,27,.04), 0 1px 1px rgba(35,31,27,.03)`): Descanso. Cards, topbar, filas destacadas.
- **shadow-md** (`0 6px 18px rgba(35,31,27,.06), 0 2px 4px rgba(35,31,27,.03)`): Hover de card, hero premium del detalle de rendición.
- **shadow-lg** (`0 16px 40px rgba(35,31,27,.08), 0 4px 10px rgba(35,31,27,.05)`): Solo modales.

### Named Rules

**The Warm-Shadow Rule.** Todas las sombras se tintan hacia `rgba(35,31,27, α)`. Negro puro (`rgba(0,0,0, α)`) está prohibido: rompe la calidez sobre el fondo paper.

**The Flat-By-Default Rule.** Al reposo, solo cards llevan shadow-sm. Shadow-md aparece en hover o en elementos marcados como hero. Shadow-lg aparece solo al abrir un modal. Nunca apilar sombras.

## 5. Components

### Buttons

- **Shape:** Radius `8px` (`--radius-sm`). Padding `8px 16px`. Font 13px / 600. Transition `0.2s ease`.
- **Primary:** Fondo `terracotta-flame`, texto `#fff`. Hover: `terracotta-ember` + `box-shadow 0 2px 8px rgba(186,76,51,.22)`. Para acciones irreversibles del flujo (Aprobar, Finalizar).
- **Secondary:** Fondo `surface`, texto `ink`, border `border`. Hover: fondo `paper`. Para acciones reversibles o cancelar.
- **Ghost:** Transparente, texto `ink-muted`, padding reducido (`6px 10px`). Para acciones menores en toolbars.
- **Success / Warning / Danger:** Fondos pastel OKLCH (success `oklch(95% 0.07 150)`, warning `#FFF7ED`, danger `#FEF2F2`) con texto en misma familia más oscura. No hay outline color-on-white; siempre fill pastel.
- **sm modifier:** Padding `5px 11px`, font `12px`.
- **Focus:** Hereda anillo del primary (`box-shadow: 0 0 0 3px rgba(218,91,62,.15)`) cuando aplica en formulario.

### Inputs / Fields

- **Style:** Border `1px solid #CBD5E0`, radius `8px`, padding `9px 12px`, font `13.5px`, fondo `surface`.
- **Focus:** Border `terracotta-flame` + anillo `box-shadow: 0 0 0 3px rgba(218,91,62,.15)`. Sin transform ni glow decorativo.
- **Invalid (post-interacción):** Border `#FC8181`.
- **Prefix wrap:** Caja de prefijo gris claro (`#F1F5F9`) unida al input sin gap para S/., RUC, etc.
- **Textarea:** `min-height: 88px`, resize vertical.

### Cards / Containers

- **Corner Style:** Radius `12px` (`--radius-md`) por defecto, `14px` en paneles de estado, `18px` en cards signature (hero rendición).
- **Background:** `surface` (blanco) sobre `paper`. El contraste es el que genera elevación, no sombra extra.
- **Border:** `1px solid border` siempre. Nunca border+stripe.
- **Shadow:** `shadow-sm` descanso. No hay tarjetas nested dentro de tarjetas.
- **Internal Padding:** Header `16px 20px`, body `18-20px`. Pagina usa `28px` (`16px` en mobile).

### Badges

- **Shape:** Pill (`999px`), padding `4px 10px`, font `0.6875rem / 700`.
- **Leading Dot:** Pseudo-elemento de `6px` en `currentColor`. Señal redundante al color (accesibilidad: WCAG AA sin dependencia exclusiva de color).
- **Terna:** `bg` pastel, `text` oscuro en misma familia, `border` 1px en tono medio. Las ternas están tokenizadas por estado.
- **Group Panel:** `.estado-group` agrupa label + badge en un contenedor (padding `14px 16px`, radius `14px`, min-height `94px`). Dos panels lado a lado: tinte cálido para solicitud, tinte magenta para rendición.

### Tables

- **Style:** `width: 100%`, `border-collapse: collapse`. Header `0.6875rem / 600 / UPPERCASE / 0.06em`. Fondo header `surface-strong`.
- **Rows:** Zebra con `surface-strong` en pares. Hover `#eef2f7`. Fila clickeable (`worktray-row`) recibe tinte izquierdo cálido cuando está en estado `is-needs-action` (gradient from `rgba(218,91,62,.09)` a transparente). Esto NO es side-stripe: es tinte de fondo que decae horizontalmente, no un border.
- **Truncate:** `max-width: 200px; text-overflow: ellipsis` para celdas de motivo.
- **Mobile:** `.table-wrap { overflow-x: auto }` preserva la tabla en viewports angostos.

### Accordion (Gasto item)

- **Componente firma** del módulo Rendición. Fila colapsada: tipo (pill terracota mist), resumen, importe. Abierta: grid de campos auto-fill `minmax(180px, 1fr)` + panel de adjuntos.
- **Transición:** `max-height` 0 → 2000px con `transition 0.25s ease`. Chevron rota 90° con transform.
- **Hover body closed:** bg `#EDF2F7`.
- **Adjuntos:** Chips de archivo con icon cuadrado 30×30 coloreado por mime (PDF rojo, XML ámbar, image verde, otros gris slate).

### Wizard Modal (Rendir Gasto, 2 pasos)

- **Shell:** Ancho `680px`, min-height `560px`, max-height `95vh`, flex column.
- **Topbar:** Consolida stepper + referencia de solicitud + close en ~60px. Los pasos usan conectores horizontales de 1.5px con fill animado.
- **Body:** `overflow: hidden` en contenedor, scroll en `.wizard-panel` (scrollbar fino custom).
- **Footer:** Pegado, acciones alineadas a la derecha.
- **Short viewport** (`max-height: 720px`): Wizard compacta chrome y reduce min-heights de dropzone/textarea.

### Navigation (Sidebar)

- **Shell:** Ancho `240px` (`--sidebar-w`), fondo `archive-navy`, height `calc(100vh - wp-admin-bar-h)`.
- **Logo:** Icon cuadrado terracota `36×36` + strong blanco + span `rgba(255,255,255,.45)`.
- **Nav item:** Padding `10px 12px`, radius `sm`, color `rgba(255,255,255,.62)`. Hover: fondo `archive-navy-hover`, color `#fff`.
- **Active:** Fondo `rgba(218,91,62,.18)`, color `#f4a58f`. El terracota entra al archivador solo aquí.
- **Mobile (<768px):** Sidebar se esconde a `left: -100%`, se abre vía `.open`.

### Timeline (Historial de Expediente)

- Línea vertical con dots terracota (`linear-gradient(var(--primary), var(--primary-dark))`), cada dot con halo blanco + halo terracota suave (`0 0 0 3px surface, 0 0 0 4px rgba(218,91,62,.18)`).
- Eventos ordenados ascendente. Cada item: título (Manrope 800, `.9rem`) + meta (fecha + usuario).
- Signal component: define el lenguaje de "expediente con historial auditable".

### Liquidación Document

- **Signature component.** Documento formal de rendición que se imprime. No es una card: es un documento (A4 width, tipografía ajustada, tabla completa con header repetido en print).
- Header institucional + grid de metadatos + tabla con 11 columnas + totales (Solicitado / Rendido / Saldo) + footer con estado.
- Print: el body entra en `liq-printing` class, se renderiza sin chrome.

## 6. Do's and Don'ts

### Do

- **Do** usar terracota (`#da5b3e`) exclusivamente en: acción primaria, estado activo de nav, dot de badge hover/focus, logo. ≤10% de superficie.
- **Do** codificar estados con hue OKLCH único por estado (amber 80, green 150, orange 52, red 22, gray 60, copper 42, magenta 325). Registrarlos en `app-layout-header.php` Y `ViaticosEstadoUI`.
- **Do** tintar sombras hacia `rgba(35,31,27, α)`. Preserva la calidez sobre el fondo paper.
- **Do** usar `font-variant-numeric: tabular-nums` en toda cifra monetaria. Los montos se alinean sin trucos.
- **Do** añadir dot de 6px en currentColor a cada badge: accesibilidad AA sin depender solo del color.
- **Do** usar Sora solo en title de página y liquidación; Manrope para el resto.
- **Do** wrappear tablas en `.table-wrap { overflow-x: auto }` para proteger viewports angostos.
- **Do** centralizar badges/estados vía `ViaticosEstadoUI` (la fuente única de verdad estado→label→clase).

### Don't

- **Don't** usar `#fff` ni `#000` literales. Todo neutro se tinta.
- **Don't** crear "Interfaces gubernamentales recargadas": formularios enormes, tablas sin jerarquía, borders en todas las celdas.
- **Don't** caer en "Dashboards SaaS genéricos": gradientes de hero, hero-metrics, tarjetas idénticas repetidas en grid.
- **Don't** replicar "Excel con estilos": bordes en cada celda, sin espacio visual, sin zebra.
- **Don't** usar gradient-text (`background-clip: text` con gradiente). Prohibido en este sistema.
- **Don't** usar side-stripe borders (border-left >1px como acento). El tinte de fila `is-needs-action` usa gradient-fade horizontal, no stripe; no reemplazarlo por stripe.
- **Don't** usar glassmorphism decorativo. El único `backdrop-filter: blur(3px)` legítimo está en el overlay de modal.
- **Don't** apilar sombras. Una capa por elemento.
- **Don't** animar width/height/top/left. Solo `transform`, `opacity`, `max-height` (para reveals).
- **Don't** usar em dash (`—` ni `--`) en copy. Usar coma, dos puntos, o paréntesis.
- **Don't** nestear tarjetas dentro de tarjetas. Usar secciones o paneles sin card-en-card.
- **Don't** duplicar estados/badges entre admin y colaborador: registrar en `app-layout-header.php` y consumir vía `ViaticosEstadoUI`.
