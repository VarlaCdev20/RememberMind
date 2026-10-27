---
title: "Inventario de vistas y fichas de pantalla"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: DOCUMENTATION_STRUCTURE_AND_STATIC_INVENTORY
runtime_verified: false
---

# Vistas y componentes

Inventario estático del árbol local del **25/10/2026**: **573 plantillas Blade**. Incluye páginas, parciales, modales, componentes y plantillas del framework. **No equivale a 573 pantallas operativas ni a cobertura visual.** Cada ficha detallada debe identificar qué ruta/clase consume realmente la plantilla.

| Propietario | Plantillas | Inventario |
|---|---:|---|
| Admisiones | 10 | [Abrir](admisiones/INVENTARIO.md) |
| Residentes | 27 | [Abrir](residentes/INVENTARIO.md) |
| Identidad | 48 | [Abrir](identidad/INVENTARIO.md) |
| Institucion | 29 | [Abrir](institucion/INVENTARIO.md) |
| Clinica | 71 | [Abrir](clinica/INVENTARIO.md) |
| Cuidados | 68 | [Abrir](cuidados/INVENTARIO.md) |
| Medicacion | 15 | [Abrir](medicacion/INVENTARIO.md) |
| Valoraciones | 18 | [Abrir](valoraciones/INVENTARIO.md) |
| Social | 7 | [Abrir](social/INVENTARIO.md) |
| Alertas | 22 | [Abrir](alertas/INVENTARIO.md) |
| Reportes | 24 | [Abrir](reportes/INVENTARIO.md) |
| ApoyoDecision | 44 | [Abrir](apoyo-decision/INVENTARIO.md) |
| Compartidas y framework | 190 | [Abrir](compartidas/INVENTARIO.md) |

## Cómo documentar cada pantalla

1. Identificar ruta/nombre de ruta, método HTTP, componente/controller y roles autorizados.
2. Separar página, parcial y componente compartido; evitar una ficha de pantalla duplicada por rol.
3. Usar [la plantilla de vista](../plantillas/VISTA.md) y detallar los elementos con [la plantilla de componente/acción](../plantillas/COMPONENTE.md).
4. Vincular variantes de rol, datos permitidos, permisos y contexto.
5. Registrar referencia visual, tokens, estados y pruebas; la lectura de Blade no acredita una revisión en navegador.

Las columnas de controles son declaraciones estáticas, incluidas ramas condicionales. No demuestran que el control esté conectado ni que el usuario lo vea. La ruta y el comportamiento deben revisarse individualmente.
