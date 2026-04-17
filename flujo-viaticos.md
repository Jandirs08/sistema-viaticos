# Flujo de Viáticos

## 🧠 Conceptos

### Solicitud (Anticipo)
Es la solicitud de dinero antes del viaje.

### Rendición
Es la justificación del dinero mediante gastos.

---

## 🔄 Flujo completo

1. Colaborador crea solicitud
2. Admin aprueba / observa / rechaza
3. Si se aprueba:
   - colaborador registra gastos
4. Colaborador finaliza rendición
5. Admin revisa rendición:
   - aprueba
   - observa
   - rechaza

---

## 📊 Estados

### Solicitud
- pendiente
- aprobada
- observada
- rechazada

### Rendición
- no_iniciada
- en_proceso
- en_revision
- observada
- aprobada
- rechazada

---

## 👤 Colaborador

- ve sus solicitudes
- registra gastos
- finaliza rendición
- espera revisión

---

## 👨‍💼 Admin

Separado en:

### Anticipos
- aprueba solicitudes

### Rendiciones
- revisa rendiciones finalizadas

---

## 🧾 Liquidación

- resumen final de la rendición
- incluye:
  - datos de solicitud
  - gastos
  - totales
  - saldo

---

## 🧠 Trazabilidad

Cada solicitud registra eventos:
- creación
- aprobación
- inicio de rendición
- finalización
- decisión final

---

## 🚀 Estado actual

- flujo completo funcional
- UX en mejora
- base lista para exportación