---
name: remembermind-alert-engine
description: "Diseñar e implementar detección y ciclo de alertas de RememberMind con reglas aprobadas, eventos e idempotencia aplicable. Usar al integrar un registro confirmado con alerta/seguimiento; no inventa severidad ni interpreta preview como persistencia."
---

# Purpose

Conectar condiciones aprobadas con alertas trazables y atención profesional.

# Use when

Detección clínica/operativa, registro de eventos, reconocimiento/asignación/seguimiento/cierre cuando existan en contrato, coordinación y notificaciones.

# Do not use when

Crear umbrales desde UI, cerrar historia borrando alertas, rediseñar catálogos o confundir alerta con notificación o log.

# Mandatory sources

Baseline/diccionario/decisiones de alertas y del registro origen; AlertasService, DeteccionAlertasService, ServicioDecisionAlertaClinica y tests. Contrato de permisos de cada acción.

# Domain assumptions

Condición es evidencia evaluada; alerta es recurso operativo; evento registra su trayectoria; notificación distribuye; Activitylog audita técnicamente. No son sustitutos.

# Workflow

1. Localizar fuente, versión, entradas y significado de regla/severidad aprobada; diferenciar evaluación operativa de sistema experto futuro.
2. Mapear ciclo conceptual detectar/crear/reconocer/asignar/intervenir/seguir/cerrar a estados y eventos existentes; marcar pasos no definidos sin inventarlos.
3. Mantener preview puro: sin alertas/eventos/audit de mutación. Al confirmar, backend evalúa y registra origen/alerta/evento de forma atómica cuando corresponde.
4. Revalidar actor/competencia, residente, estado y motivo aplicable en cada transición.
5. Revisar duplicados, reintentos, simultaneidad y origen; idempotencia conforme a claves/regla vigentes, sin agregar estructura silenciosa.
6. Preservar eventos y cambios de estado; coordinar continuidad y notificar tras resultado confirmado sin fingir recepción/atención.
7. Comparar rutas alternativas para evitar ciclos parciales; entregar evidencia a gates finales.

# Invariants

No clasificación inventada, alerta desde preview, evento clínico perdido, cierre destructivo o éxito al fallar creación. Administrar una alerta no concede intervenir clínicamente.

# Failure conditions

Alerta sin origen/ciclo exigido, transición inválida, notificación confundida con evento, reintento duplicador o UI calcula severidad divergente del backend.

# Escalation rules

Umbrales, ciclo/catálogo o competencias nuevos requieren decisión; error técnico contra contrato se corrige en alcance. Extensión de estructura sigue gobernanza BDD.

# Tests required

Preview cero efectos; confirmación positiva/origen y evento; negativo por actor/estado/recurso cruzado; reintento y cierre conservan eventos; rollback y concurrencia PostgreSQL si pertinente.

# Definition of Done

Condición, alerta, evento, notificación y audit separados; reglas y transiciones autorizadas, historia y continuidad verificadas.
