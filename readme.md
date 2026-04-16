# ERP de Viáticos – Fundación Romero

Sistema interno de gestión de viáticos construido como SPA ligera sobre WordPress.

## 🧠 Filosofía

- Sin frameworks JS (solo Vanilla JS)
- SPA dentro de WordPress
- DRY (no duplicar lógica)
- Evolución por fases (no sobreingeniería)
- Backend simple con REST API personalizada

## 🧱 Stack

- Backend: WordPress (PHP)
- Frontend: HTML + CSS + Vanilla JS
- Data: CPT + ACF + taxonomías
- API: /viaticos/v1/
- Seguridad: Nonces + roles WP

## 🧩 Estructura

- page-dashboard.php → router por roles
- view-colaborador.php → SPA colaborador
- view-admin.php → SPA admin
- api-endpoints.php → lógica REST
- acf-fields.php → campos
- cpt-setup.php → CPTs

## 📦 Entidades

### solicitud_viatico
Representa el anticipo.

### gasto_rendicion
Representa cada gasto dentro de la rendición.

## 🔄 Flujo

1. Solicitud de anticipo
2. Aprobación
3. Registro de rendición (gastos)
4. Finalización de rendición
5. Revisión admin
6. Aprobación / observación / rechazo

## 🧭 Estado actual

Sistema funcional end-to-end:
- anticipos
- rendiciones
- adjuntos
- liquidación
- trazabilidad

## 🚀 En desarrollo

- mejora UX
- exportación
- refactor técnico