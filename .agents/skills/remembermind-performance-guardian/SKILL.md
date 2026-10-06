---
name: remembermind-performance-guardian
description: "Medir y corregir consultas/payloads lentos de RememberMind preservando permisos, datos y reglas. Usar ante N+1, reportes, históricos o regresión medible; no prescribe cache/índices sin evidencia ni convierte cada tarea en optimización global."
---

# Purpose

Reducir costo operativo demostrado sin alterar el contrato institucional.

# Use when

N+1, listas grandes, reportes/exports, históricos/gráficas, query loops o regresión de latencia/memoria.

# Do not use when

Microoptimizar por intuición, ampliar a todo repositorio, añadir índices fuera del esquema aprobado, cachear permisos/disponibilidad sin invalidez definida.

# Mandatory sources

Contrato del flujo y permisos, [baseline técnico](../../../docs/arquitectura/REMEMBERMIND_BASELINE_TECNICO.md), consultas/Models/consumidores reales, evidencia de medición y baseline BDD si estructura.

# Domain assumptions

Consulta rápida con datos incorrectos o filtración es una regresión. Medición sintética representativa sirve para técnica, no certifica producción.

# Workflow

1. Definir operación, volumen sintético, entorno, métrica y baseline: número de queries, latencia/payload/memoria relevantes.
2. Localizar N+1, SQL en loops/vistas, eager loading faltante/excesivo, select *, counts repetidos, filtros en memoria o listas sin paginar.
3. Corregir capa responsable mediante capacidades existentes y payload mínimo; filtros/scope antes de serialización; paginación determinista y fechas coherentes.
4. Para reportes/gráficas preservar fuente, filtros y reproducción de resultado; usar data-visualization-ux solo para su contrato visible.
5. Cache solo con clave/ámbito/expiración/invalidation documentados; especial cuidado con permisos, cama, prescripción y alertas.
6. Si índice necesario: query/plan medido, impacto y propuesta; requerir aprobación cuando fuera del congelado.
7. Comparar después y probar igualdad funcional/autorización. Detener cuando riesgo concreto resuelto.

# Invariants

No omitir filas/scope para acelerar, mezclar usuarios en cache, alterar clínica, eliminar historia ni crear índices/esquema por inferencia.

# Failure conditions

Mejora no medida, cache stale de disponibilidad/permiso, eager loading masivo de expediente o paginación que rompe contrato export.

# Escalation rules

Estructura, dependencia externa o privacidad nueva se elevan con evidencia. Reutilizar consultas y corregir N+1 dentro del encargo autorizado.

# Tests required

Comparación resultados/orden/filtros/permisos; presupuesto de queries si estable y significativo; cache invalidada ante cambio relevante; medición antes/después sin umbral inventado de producción.

# Definition of Done

Costo reducido con medición comparable, conducta/permisos preservados y alcance/limitaciones claros.
