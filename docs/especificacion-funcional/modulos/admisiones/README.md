---
title: "Especificación detallada — Admisiones"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: DOCUMENTATION_STRUCTURE_AND_STATIC_INVENTORY
runtime_verified: false
---

# Admisiones

**Estado:** ficha de entrada; detalle pendiente de elaboración y contraste.

## Fuentes localizadas

- [Propósito, actores, recorrido y brechas actuales](../../../../app/Modulos/Admisiones/README.md).
- [Contratos de proceso existentes](../../../modulos/README.md).
- [Inventario estático de vistas](../../vistas/admisiones/INVENTARIO.md).
- [Perfiles de rol](../../roles/README.md).

## Trabajo a desarrollar

1. Identificar casos de uso, actor, entrada, estado inicial y resultado.
2. Documentar cada operación y su autorización en backend.
3. Vincular pantallas, formularios, campos, botones, diálogos y navegación.
4. Describir persistencia, relaciones, fecha/autoría, historia y continuidad.
5. Conciliar diferencias entre contrato e implementación.
6. Añadir pruebas y evidencias por criterio de aceptación.

## Registro del detalle

Usar [la plantilla de módulo](../../plantillas/MODULO.md) y crear fichas de vista a partir del inventario, diferenciando páginas de parciales/componentes. No copiar el diccionario BDD ni volver a definir reglas aprobadas.

## Pendientes de esta ficha

- Casos de uso desglosados y cobertura por rol.
- Fichas de todas las pantallas efectivamente conectadas.
- Matriz de campos, acciones, estados, efectos y pruebas.

Estos pendientes documentales no significan que el código no exista; deben comprobarse contra las fuentes enlazadas.
