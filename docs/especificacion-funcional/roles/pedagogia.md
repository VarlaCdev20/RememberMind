---
title: "Funciones y alcance — Pedagogía"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Pedagogía

**Identificador del catálogo:** `PEDAGOGO`. **Grupo:** Profesional interdisciplinario. **Corte documental:** 25/10/2026.

## 1. Finalidad

Organizar y documentar actividades, participación y seguimiento pedagógico dentro del ámbito autorizado.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Actividades | Gestionar las actividades habilitadas y su contexto/participantes. |
| Seguimiento pedagógico | Registrar seguimiento propio y observaciones del proceso. |
| Atenciones y notas | Documentar atenciones/notas de su competencia con fecha y autoría. |
| Fuentes de apoyo | Consultar indicaciones, controles cognitivos y conducta permitidos para organizar el trabajo. |
| Planes | Registrar/seguir planes de su ámbito conforme a permiso y estado. |
| Alertas | Consultar alertas pertinentes sin obtener gestión ni cierre por tener lectura. |

## 3. Módulos relacionados

- [Social](../../../app/Modulos/Social/README.md)
- [Valoraciones](../../../app/Modulos/Valoraciones/README.md)
- [Clinica](../../../app/Modulos/Clinica/README.md)
- [Cuidados](../../../app/Modulos/Cuidados/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Participación, seguimiento y fuentes pertinentes del residente con permiso y contexto; observación de actividad no equivale a medición cognitiva objetiva.

## 5. Información que puede registrar o gestionar

Actividades, seguimientos, atenciones/notas y planes propios en las entradas habilitadas.

## 6. Límites

- No diagnostica DCL ni interpreta una tarea como prueba estandarizada por el nombre de una actividad.
- No prescribe ni firma revisiones médicas/psicológicas.
- No gestiona alertas o reglas cognitivas por su acceso de consulta.

## 7. Dashboard y recorrido de trabajo

Actividades propias, participantes, seguimientos y planes registrados; próximos eventos solo si están programados.

1. Consultar actividad, residentes y fuentes permitidas.
2. Organizar o ejecutar la actividad autorizada.
3. Registrar participación/seguimiento propio.
4. Comunicar observaciones y continuidad al equipo autorizado.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `seguimientos_pedagogicos.crear`, `actividades.gestionar`, `planes_cuidado.crear`, `alertas.ver`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Aporta seguimiento de actividades al expediente; recibe indicaciones y comparte observaciones con los profesionales autorizados.

## 10. Participación en el sistema experto

Sus observaciones sirven como fuente contextual cuando proceda; no se convierten automáticamente en dominios A/P ni en clasificación cognitiva.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

La auditoría no acredita cada operación de participación y seguimiento; revisar densidad/labels de Social y conexión real de cada acción.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)
- [tests/Feature/AccesoDashboardPorRolTest.php](../../../tests/Feature/AccesoDashboardPorRolTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
