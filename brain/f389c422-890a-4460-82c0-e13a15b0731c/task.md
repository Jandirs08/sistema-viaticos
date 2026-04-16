# Task: Frontend Quality Fixes

## 1. Unify badges in view-admin.php
- [x] Remove duplicated `badgeHTML` / `estadoRendicionBadge` from first IIFE (lines 99-111)
- [x] Fix `renderSolicitudesTable` to call `badgeHTML(s.estado)` consistently (not the pre-resolved state)
- [x] Ensure single source for both badge functions in AdminApp IIFE only

## 2. Extract business logic from view templates
- [x] Extract `puedeDecidir` logic from `renderDetalle` into named helper `puedeDecidirRendicion(estadoRend)`
- [x] Extract action-building logic from `renderSolicitudesTable` into named helper `buildAccionAdmin(s, estado)`
- [x] Views should only call helper functions

## 3. Unify `en_proceso` estado
- [x] Confirm single source in `ViaticosEstadoUI.resolveEstadoRendicion` (app-layout-header.php) — already correct
- [x] Fix `view-admin.php` first IIFE `estadoRendicionBadge` calls to use consistent field pass-through
- [x] Remove dead subtitle map override in `view-colaborador.php` (lines 810-815 duplicate logic)
