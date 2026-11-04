---
title: "Inventario de vistas y fichas de pantalla"
status: CURRENT
version: "1.1"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: false
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
---

# Vistas y componentes

Inventario estático del **04/11/2026:593 plantillas Blade** (398 modulares y195 compartidas/framework). Incluye páginas, parciales, modales y componentes; no equivale a593 pantallas operativas ni a cobertura visual. [Rutas, clases y versiones actuales](../../sistema/INVENTARIO_ACTUAL.md).

| Propietario | Plantillas | Inventario |
|---|---:|---|
| Admisiones | 10 | [Abrir](admisiones/INVENTARIO.md) |
| Residentes | 29 | [Abrir](residentes/INVENTARIO.md) |
| Identidad | 53 | [Abrir](identidad/INVENTARIO.md) |
| Institucion | 33 | [Abrir](institucion/INVENTARIO.md) |
| Clinica | 71 | [Abrir](clinica/INVENTARIO.md) |
| Cuidados | 68 | [Abrir](cuidados/INVENTARIO.md) |
| Medicacion | 15 | [Abrir](medicacion/INVENTARIO.md) |
| Valoraciones | 19 | [Abrir](valoraciones/INVENTARIO.md) |
| Social | 7 | [Abrir](social/INVENTARIO.md) |
| Alertas | 23 | [Abrir](alertas/INVENTARIO.md) |
| Reportes | 33 | [Abrir](reportes/INVENTARIO.md) |
| ApoyoDecision | 37 | [Abrir](apoyo-decision/INVENTARIO.md) |
| Compartidas y framework | 195 | [Abrir](compartidas/INVENTARIO.md) |

## Cómo documentar cada pantalla

1. Identificar ruta/nombre de ruta, método HTTP, componente/controller y roles autorizados.
2. Separar página, parcial y componente compartido; evitar una ficha de pantalla duplicada por rol.
3. Usar [la plantilla de vista](../plantillas/VISTA.md) y detallar los elementos con [la plantilla de componente/acción](../plantillas/COMPONENTE.md).
4. Vincular variantes de rol, datos permitidos, permisos y contexto.
5. Registrar referencia visual, tokens, estados y pruebas; la lectura de Blade no acredita una revisión en navegador.

Las columnas de controles son declaraciones estáticas, incluidas ramas condicionales. No demuestran que el control esté conectado ni que el usuario lo vea. La ruta y el comportamiento deben revisarse individualmente.
