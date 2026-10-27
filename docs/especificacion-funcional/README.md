---
title: "Especificación funcional detallada de RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: DOCUMENTATION_STRUCTURE_AND_STATIC_INVENTORY
runtime_verified: false
---

# Especificación funcional detallada

**Carpeta creada el 25/10/2026.** Espacio de trabajo solicitado por la propietaria para documentar cada módulo, cada pantalla y la experiencia completa de cada rol. La estructura y los inventarios están preparados; las fichas detalladas están **en elaboración**. Crear esta carpeta no declara los módulos terminados.

## Entradas

| Área | Contenido |
|---|---|
| [Módulos](modulos/README.md) | Doce propietarios; propósito, procesos, estados, persistencia, integraciones y aceptación |
| [Vistas y componentes](vistas/README.md) | Inventario de Blade por propietario, pantallas y piezas compartidas; fichas detalladas por completar |
| [Roles](roles/README.md) | Diez perfiles; tareas, lectura/escritura, permisos, contexto, navegación y límites |
| [Plantillas](plantillas/README.md) | Módulo, vista, rol y componente/acción |
| [Contratos transversales](transversales/README.md) | Seguridad, diseño, historia, errores, BDD y continuidad existentes |
| [Trazabilidad](trazabilidad/README.md) | Requisito → rol → módulo → vista/acción → backend → prueba → evidencia |
| [Validación](validacion/README.md) | Revisión documental y comprobación técnica/visual separadas |
| [Orden de trabajo](PLAN_DOCUMENTACION.md) | Cómo desarrollar y revisar la documentación sin duplicar reglas |

## Autoridad y separación de estados

Las decisiones y contratos existentes mantienen su prioridad. [Responsabilidades actuales de módulos](../modulos/README.md), [gobernanza](../GOBERNANZA_DOCUMENTAL.md) y [AGENTS](../../AGENTS.md) son las entradas de referencia. Esta carpeta desarrolla detalle y trazabilidad; no crea otra aprobación estructural o clínica.

Para cada elemento registrar por separado:

- **Requerido:** comportamiento respaldado por una fuente aprobada.
- **Observado:** implementación localizada en el árbol actual.
- **Verificado:** prueba ejecutada, entorno, fecha y resultado.
- **Pendiente:** brecha concreta y criterio de cierre.
- **Propuesto:** decisión nueva aún no adoptada.

Los errores de producto de la [auditoría actual](../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) siguen abiertos hasta su corrección. Las plantillas no son pruebas aprobadas ni autorizaciones nuevas.
