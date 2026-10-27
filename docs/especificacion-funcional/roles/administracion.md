---
title: "Funciones y alcance — Administración"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Administración

**Identificador del catálogo:** `ADMINISTRADOR`. **Grupo:** Institucional y operación diaria. **Corte documental:** 25/10/2026.

## 1. Finalidad

Coordinar el ingreso y la operación cotidiana de la residencia, sin asumir actos clínicos ni dirección técnica de acceso.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Preadmisión | Registrar y tramitar solicitudes, revisión y documentación por la entrada autorizada. |
| Admisión | Formalizar el ingreso aprobado con cama apta, vínculos y documentos; aprobar una solicitud no crea residente. |
| Alojamiento | Gestionar disponibilidad y ocupación conforme a las reglas de cama y residente. |
| Jornada y asignaciones | Abrir jornadas y realizar asignaciones operativas dentro de la planificación y permisos. |
| Documentos y contactos | Gestionar documentación, consentimientos, seguros y vínculos administrativos autorizados. |
| Visitas y actividades | Coordinar programación y registros operativos existentes. |
| Alertas y reportes | Reconocer, coordinar/asignar, seguir o cerrar según permiso y regla de la alerta; consultar/exportar reportes operativos. |

## 3. Módulos relacionados

- [Admisiones](../../../app/Modulos/Admisiones/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Institucion](../../../app/Modulos/Institucion/README.md)
- [Social](../../../app/Modulos/Social/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)
- [Reportes](../../../app/Modulos/Reportes/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Expediente administrativo, disponibilidad, jornadas, documentos y datos necesarios para coordinación. La lectura clínica no se deduce de gestionar al residente.

## 5. Información que puede registrar o gestionar

Actos administrativos y operativos: ingreso formal, datos administrativos, alojamiento, jornada, vínculos, documentos, visitas y acciones autorizadas de alerta.

## 6. Límites

- No crea residentes fuera de la formalización de admisión.
- No diagnostica, prescribe ni registra valoraciones clínicas por el rol administrativo.
- No obtiene dirección de RR. HH. o gestión técnica de cuentas por ser operador diario.

## 7. Dashboard y recorrido de trabajo

Preadmisiones, admisiones, camas, jornadas, visitas, actividades, documentos y alertas operativas; acciones que continúan tareas reales.

1. Revisar ingresos y pendientes del día.
2. Formalizar ingresos completos y gestionar alojamiento.
3. Abrir jornada/asignar recursos con contexto válido.
4. Coordinar documentación, visitas, alertas y cierre operativo.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `admisiones.formalizar`, `jornadas.gestionar`, `turnos.asignar`, `documentos.validar`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Recibe planificación de Gerencia y prepara contexto para profesionales; recibe pendientes de cuidado y coordina las acciones administrativas permitidas.

## 10. Participación en el sistema experto

No registra juicios Petersen, no revisa clínicamente ni gestiona reglas por este rol. La coordinación administrativa no acredita requisitos cognitivos.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

La auditoría de menús no acredita cada mutación operativa. Revisar cada asignación/alerta sin ampliar permisos como atajo.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)
- [tests/Feature/Fase3SeguridadNucleoTest.php](../../../tests/Feature/Fase3SeguridadNucleoTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
