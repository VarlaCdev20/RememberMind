---
title: "Plantilla — modulo"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: DOCUMENTATION_STRUCTURE_AND_STATIC_INVENTORY
runtime_verified: false
---

# Plantilla de módulo

## Identificación y fuentes

Nombre, propietario de código, alcance, versión/corte y fuentes aprobadas. Para el desglose de 36 secciones reutilizar [la plantilla existente](../../modulos/_PLANTILLA_CONTRATO_MODULO.md), sin crear otra metodología.

## Procesos y actores

| Caso de uso | Actor | Entrada/estado inicial | Acción | Salida/estado final | Fuente aprobada |
|---|---|---|---|---|---|

## Operaciones y autorización

Por operación: permiso, Policy, cuenta/personal, competencia, residente/contexto, relaciones y negativos. No inferirlos desde el sidebar.

## Vistas y navegación

Ruta y método → clase → Blade → componentes → fichas de vista → variantes por rol. Documentar ida, vuelta, accesos directos y recuperación de error.

## Datos, validación y persistencia

Entradas, procedencia, fecha/autoría, formato, validación del backend, relaciones, transacción, idempotencia, concurrencia, corrección/anulación y recuperación. Enlazar diccionario; no proponer columnas silenciosamente.

## Integraciones y continuidad

Módulos consumidores, eventos reales, archivos, notificaciones, alertas, pendientes y siguiente actor. Diferenciar efectos transaccionales de externos.

## Brechas y aceptación

Requerido → observado → prueba → evidencia → pendiente → criterio de cierre. Registrar decisiones nuevas como propuestas separadas.
