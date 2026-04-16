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

## 🧩 Arquitectura

- SPA dentro de WordPress
- vistas separadas por rol
- comunicación vía fetch + REST
- WordPress solo como base (auth, data, roles)

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
- soluciones “mágicas” no explícitas