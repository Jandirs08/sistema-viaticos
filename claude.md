# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Reglas de desarrollo – ERP de Viáticos

## 🎯 Objetivo

Mantener el sistema simple, consistente y evolutivo sin romper el flujo actual.

---

## 🧠 Filosofía

- NO sobreingeniería
- NO refactors grandes sin pedirlo
- cambios pequeños y controlados
- priorizar claridad sobre complejidad

---

## ⚙️ Stack obligatorio

- WordPress (PHP)
- Vanilla JS (sin frameworks)
- REST API personalizada existente

---

## 🚫 Restricciones

- NO usar React, Vue u otros frameworks
- NO crear nuevos CPT sin necesidad
- NO duplicar lógica
- NO dejar código muerto
- NO mover archivos sin motivo claro
- NO cambiar endpoints existentes sin justificar
- NO mezclar lógica de negocio en vistas

---

## ✅ Buenas prácticas obligatorias

- reutilizar helpers existentes
- centralizar lógica (estado, acciones, UI)
- mantener separación:
  - lógica → helpers
  - render → vistas
- mantener consistencia entre admin y colaborador
- usar nombres claros y coherentes

---

## 🔄 Forma de trabajar

- implementar por fases
- no tocar partes fuera del alcance
- si detectas mejoras grandes → reportar, no aplicar directo
- explicar siempre:
  - qué cambiaste
  - qué no tocaste

---

## 🧠 Prioridad

1. claridad UX
2. consistencia
3. mantenibilidad
4. luego optimización

---

## ⚠️ Evitar

- hotfixes rápidos sin estructura
- lógica repetida
- condiciones complejas en templates
- soluciones "mágicas" no explícitas

---

# Arquitectura

Este repo es un **tema de WordPress** (`theme-administracion`) que hospeda una SPA en Vanilla JS para el ERP de viáticos de Fundación Romero. No hay build step, ni package.json, ni pipeline de tests: todo se sirve directo desde PHP/JS.

## Entry point y ruteo

Todo el front-end vive en `/` (home). `index.php` y `front-page.php` delegan en `theme_administracion_render_front_app()` (definido en `functions.php`), que:

1. Si el usuario no está logueado → renderiza `theme_administracion_render_login_screen()` (login nativo de WP con estilos embebidos).
2. Si está logueado → arma los `$dashboard_args` (nombre, iniciales, `rest_nonce`, `api_base`, rol, DNI, cargo, área) y carga en orden:
   - `template-parts/dashboard/app-layout-header.php` (shell: `<head>`, tokens CSS, sidebar, topbar, abre `<main id="erp-content">`, expone `window.ViaticosEstadoUI` / `ViaticosTimelineUI` / `ViaticosGastoUI` / `ViaticosLiquidacion`)
   - `view-admin.php` **o** `view-colaborador.php` según `dashboard_role` (SPA completa del rol: vistas + modales + `<script>` con `window.ViaticosApp`)
   - `app-layout-footer.php` (cierra `<main>`, `</body>`, `</html>`)

`page-dashboard.php` es legacy: sólo hace `wp_safe_redirect(home_url('/'))`. El filtro `template_redirect` (en `functions.php`) manda cualquier URL que no sea `/` de vuelta a home, así que **la app tiene un único entrypoint público**.

## Módulos PHP (`includes/`)

Cargados en este orden desde `functions.php`:

- `cpt-setup.php` — registra los dos CPTs: `solicitud_viatico` (anticipo) y `gasto_rendicion` (gastos rendidos; vive como submenú del anterior).
- `acf-fields.php` — registra todos los field groups vía `acf_add_local_field_group()`. **Todos los datos de negocio viven en ACF**, no en `post_meta` propio salvo excepciones (ver abajo).
- `user-taxonomies.php` — `viaticos_cargo` y `viaticos_area` como taxonomías sobre el objeto `user`.
- `roles-setup.php` — define roles `colaborador_viaticos` y `admin_viaticos`, y bloquea edición desde el admin de WP cuando `estado_solicitud` está en `pendiente|aprobada|rechazada|rendida` (hook `save_post_solicitud_viatico`).
- `api-endpoints.php` — namespace `viaticos/v1`, registra todos los endpoints REST + helpers + permission callbacks. **Es el único lugar donde se toca la lógica de negocio del back**.

## REST API (`viaticos/v1`)

Todas las rutas están en `viaticos_registrar_endpoints()`. Permisos vía `viaticos_permission_logueado` o `viaticos_permission_admin` (admite `administrator`, `admin_viaticos` o `edit_others_posts`). Rutas actuales:

- **Colaborador**: `GET /mis-solicitudes`, `GET /mis-rendiciones`, `POST /nueva-solicitud`, `POST /nuevo-gasto`, `POST /finalizar-rendicion`.
- **Admin**: `GET /todas-solicitudes`, `GET /detalle-rendicion-admin/{id}`, `POST /actualizar-estado`, `POST /decidir-rendicion`.
- **Adjuntos** (cualquiera logueado, valida ownership dentro del callback): `GET /gasto-adjuntos/{id_gasto}`, `POST /gasto-adjunto`, `DELETE /gasto-adjunto/{id_adjunto}`.

El front autentica pasando `X-WP-Nonce: <rest_nonce>` en cada `fetch`. `api_base` y `rest_nonce` se inyectan desde PHP al JS vía el objeto `CONFIG` dentro de cada vista.

## Modelo de datos

- `solicitud_viatico` (post): campos ACF (monto, fechas, CECO, motivo, `estado_solicitud`, etc.) + dos `post_meta` no-ACF que **hay que tratar como parte del contrato**:
  - `rendicion_finalizada` (`'1'` o vacío) — ver `viaticos_es_rendicion_finalizada()`.
  - `estado_rendicion` — ver `viaticos_get_estado_rendicion()`.
  - `viaticos_historial_solicitud` — array append-only de eventos, escrito sólo vía `registrarEventoSolicitud()`. Los eventos válidos están en `viaticos_get_eventos_historial_validos()` (`solicitud_creada`, `solicitud_aprobada`, `solicitud_observada`, `solicitud_rechazada`, `rendicion_iniciada`, `rendicion_finalizada`, `rendicion_aprobada`, `rendicion_observada`, `rendicion_rechazada`).
- `gasto_rendicion` (post): relación con la solicitud via ACF `id_solicitud_padre`; campos por tipo de comprobante (ver `viaticos_obtener_gastos_solicitud()` para la forma canónica del DTO que se envía al front).

## Flujo de negocio (crítico)

Ver `flujo-viaticos.md` para detalle. Estados válidos:

- `estado_solicitud`: `pendiente` → `aprobada` | `observada` | `rechazada`
- `estado_rendicion`: `no_iniciada` → `en_proceso` → `en_revision` → `aprobada` | `observada` | `rechazada`

El admin tiene dos módulos separados en la SPA: **Anticipos** (decide sobre solicitudes pendientes) y **Rendiciones** (decide sobre rendiciones finalizadas, endpoint `/decidir-rendicion`).

## Arquitectura del front (SPA)

Cada `view-*.php` es un archivo auto-contenido: markup de vistas + markup de modales + un único `<script>` IIFE al final. La comunicación entre vistas del mismo rol es interna al IIFE; **no hay un bus global**.

Los helpers JS compartidos entre admin y colaborador se exponen desde `app-layout-header.php` en el `window`:

- `ViaticosEstadoUI` — resolución de estados y render de badges (`resolveEstadoSolicitud`, `resolveEstadoRendicion`, `renderBadgeEstado`, `renderEstadoGrupo`, `getLabelEstado`). Fuente única de verdad para el mapeo estado → label/clase CSS.
- `ViaticosTimelineUI` — render del historial de eventos.
- `ViaticosGastoUI` — componentes para filas/tarjetas de gasto.
- `ViaticosLiquidacion` — arma el DTO y el HTML del documento de liquidación final.

Cada vista también expone `window.ViaticosApp = { navigate: navigateTo }` para que botones inline (`onclick="ViaticosApp.navigate(...)"`) puedan cambiar de sección sin acoplarse al IIFE.

**Si vas a añadir lógica compartida entre admin y colaborador**, registrarla como módulo `window.ViaticosXxx` en `app-layout-header.php`. No dupliques funciones entre las dos vistas.

## Design tokens

Todos los colores, sombras, radios y fuentes viven como CSS custom properties en `:root` dentro de `app-layout-header.php` (`--primary`, `--badge-*`, `--radius-*`, `--font-display`, etc.). **Los badges de estado tienen tokens dedicados** (`--badge-solicitud-*`, `--badge-rendicion-*`); al agregar o renombrar estados hay que actualizar estos tokens y el mapeo en `ViaticosEstadoUI`.

---

# Convenciones concretas (que no son obvias leyendo el código)

- **Archivos gigantes por diseño.** `view-admin.php` (~880 líneas), `view-colaborador.php` (~1000 líneas) y `app-layout-header.php` (~2100 líneas) mezclan markup, CSS y JS a propósito — así se evita toolchain. No los partas en archivos JS separados sin pedirlo; rompería la carga del tema.
- **Sin assets compilados.** `viaticos_enqueue_assets()` sólo encola `style.css` (vacío). Todo el CSS está inline en `app-layout-header.php`.
- **ACF es requerido.** El tema asume el plugin ACF activo (`function_exists('get_field')` protege accesos puntuales, pero las `acf_add_local_field_group()` son incondicionales en `acf-fields.php`).
- **Historial: no escribir `post_meta` directo.** Siempre vía `registrarEventoSolicitud($id, $evento, $usuario_id)`; hace dedupe del último evento idéntico.
- **Nunca cambiar la shape del DTO** que devuelve `viaticos_obtener_gastos_solicitud()` sin actualizar los consumidores en las dos vistas + `ViaticosLiquidacion`.
- **Nuevos estados** requieren tocar: `acf-fields.php` (select), `ViaticosEstadoUI` (labels + colores), tokens CSS de badges, `viaticos_get_eventos_historial_validos()` si aplica, y los `enum` de los endpoints REST que aceptan decisiones.

---

# Comandos de desarrollo

No hay build, linter, ni tests configurados. El ciclo de trabajo es:

- Editar PHP/JS/CSS en su sitio.
- El servidor es Local by Flywheel (`C:\Users\JSanchezT\Local Sites\administracionwp`); no hace falta reiniciar nada — refrescar el browser basta.
- Tras cambiar CPTs, taxonomías o reglas de rewrite: visitar **Ajustes → Enlaces permanentes** en wp-admin y guardar para que WordPress regenere las reglas.
- Tras cambiar roles/capabilities en `roles-setup.php`: el usuario existente ya tiene el rol cacheado; revisar al menos un login limpio.

Git local:

```bash
git status
git log --oneline -20
```

No hay remote configurado para deploy automático; los commits son locales.
