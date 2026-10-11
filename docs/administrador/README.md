# Administrador: trabajo gradual y documentación

Punto de entrada para mejorar el rol **ADMINISTRADOR** por procesos. Leer únicamente la guía del módulo activo y sus fuentes; evitar auditorías globales repetidas.

## Etapa actual

**Bandejas de coordinación, expediente y seguimiento**, revisadas el 2026-10-06 en `REINICIO`. Once ventanas incorporan vistas según su función, gráficos con datos reales, filtros compartidos y fichas de consulta. Habitaciones mantiene el inventario; Ocupación concentra el mapa y traslado autorizado. Estas entregas no acreditan que todos los procesos del rol administrador estén terminados. Por instrucción del propietario, esta entrega permanece **sin commit y sin push**.

El 2026-10-07 se diferenciaron gráficos y composición por proceso: horarios, anillos, franjas, ranking, columnas, puntos y situaciones. Alertas añade Tablero y Asignaciones añade Por área. La [guía 09](09-GRAFICOS-Y-VISTAS-POR-PROCESO.md) documenta la revisión vigente y su verificación.

- [Admisiones: funcionamiento, mejoras y verificación](01-ADMISIONES.md).
- [Preadmisiones: controles, vistas y verificación](../frontend/PREADMISIONES_INTERACTIVAS.md).
- [Componentes compartidos: paginación, selectores y menú](02-COMPONENTES-COMPARTIDOS.md).
- [Residentes y alojamiento: mapa, traslado, permisos y verificación](03-RESIDENTES-Y-ALOJAMIENTO.md).
- [Habitaciones y camas: inventario y mantenimiento en modales](04-HABITACIONES-Y-CAMAS.md).
- [Ocupación: mapa, selección de residente e historial](05-OCUPACION.md).
- [Coordinación diaria: jornadas, asignaciones, actividades y visitas](06-COORDINACION-DIARIA.md).
- [Expediente administrativo: contactos, documentos, consentimientos y seguros](07-EXPEDIENTE-ADMINISTRATIVO.md).
- [Seguimiento: alertas, incidentes y reportes](08-SEGUIMIENTO-Y-REPORTES.md).
- [Gráficos y vistas diferenciados por proceso](09-GRAFICOS-Y-VISTAS-POR-PROCESO.md).
- [Calendarios completos, ayuda por icono y fichas sin cambiar de ventana](10-CALENDARIOS-Y-FICHAS-LOCALES.md).
- [Roles y responsabilidades vigentes](../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md).
- [Baseline de datos](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y [extensión V2.2 aprobada](../base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md): inventario actual de 71 tablas, estructura congelada.

## Orden de trabajo

| Etapa | Proceso | Estado |
| --- | --- | --- |
| 1 | Preadmisión, revisión e ingreso formal | Bandeja y refuerzos de ingreso verificados; concurrencia PostgreSQL pendiente |
| 2 | Habitaciones y ocupación | Explorador físico, ficha y traslado interno integrados; revisión completa del mantenimiento y concurrencia PostgreSQL pendientes |
| 3 | Residentes y expediente administrativo | Directorio y panel integrados; revisión completa del expediente y cierre de estancia pendientes |
| 4 | Contactos, documentación, consentimientos y seguros | Consulta, gráficos, vínculos autorizados y descarga privada integrados; gestión y validación documental completas pendientes |
| 5 | Jornadas, asignaciones, actividades y visitas | Agendas, cobertura, filtros y fichas integrados; creación y transiciones institucionales pendientes de revisión |
| 6 | Alertas e incidentes operativos | Consulta, prioridades registradas y trayectoria de alertas integradas; acciones sensibles pendientes de revisión |
| 7 | Reportes, búsqueda y dashboard | Comparación administrativa por periodo integrada; dashboard previo conservado, exportaciones y auditoría completa pendientes |

La presencia de una pantalla o ruta no equivale a un proceso completo. Cada etapa debe cubrir funcionamiento, integración, autorización, errores y UX proporcionalmente a su alcance.

## Cómo cerrar cada entrega

1. Definir la acción del administrador y reconstruir el flujo con las fuentes vigentes.
2. Revisar la pantalla y sus componentes; implementar un cambio acotado y comprobable.
3. Verificar permisos, validación, estados, persistencia y auditoría aplicables.
4. Ejecutar pruebas relevantes y build cuando cambie el frontend; comprobar navegador, ambos temas y tamaños pertinentes.
5. Actualizar la guía: funcionamiento antes/después, archivos, validaciones ejecutadas y pendientes reales. Respetar la instrucción vigente del propietario: **no crear commits**. Publicar solo con autorización expresa.

## Uso de subagentes

Asignar tareas pequeñas con archivos y objetivo definidos. Por defecto, uno revisa flujo/seguridad y otro diseño/documentación. Evitar que editen los mismos archivos o relean todo el proyecto; pedir resultados breves, referencias y evidencia. La coordinación integra y verifica el resultado. Delegar no garantiza menor consumo de tokens: usar agentes solo cuando su trabajo separado aporte valor.

La primera inspección utilizó dos subagentes de solo lectura: mapa del administrador y flujo de Admisiones. Sus hallazgos están sintetizados en esta carpeta; no es necesario repetir esa exploración para empezar la siguiente mejora.

## Límite del rol

Administración coordina operación diaria. Gestión de cuentas, roles, personal y planificación maestra pertenecen a sus actores definidos. Las acciones clínicas requieren competencia profesional y permisos efectivos; el rediseño no amplía facultades.
