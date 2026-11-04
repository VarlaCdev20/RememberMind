---
title: "Funciones y alcance — Enfermería"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Enfermería

**Identificador del catálogo:** `ENFERMEROS`. **Grupo:** Profesional de cuidado continuo. **Corte documental:** 25/10/2026.

## 1. Finalidad

Ejecutar y documentar cuidado continuo de residentes autorizados, manteniendo observación longitudinal, medicación y pase de turno.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Contexto de guardia | Consultar jornada, residentes asignados y tareas del turno. |
| Observación y controles | Registrar signos, dolor, controles y episodios observacionales autorizados con fecha/contexto. |
| Cuidados cotidianos | Documentar sueño, conducta, ingesta, hidratación, eliminación, movilidad y otras fuentes habilitadas por cada operación. |
| Medicación | Consultar la orden y documentar administración/omisión de una ocurrencia válida; mantenerla separada de la prescripción. |
| Incidentes y heridas | Registrar incidentes, heridas/curaciones y actuaciones permitidas con sus fuentes. |
| Planes y continuidad | Consultar indicaciones y programación, documentar ejecuciones y comunicar pendientes en pase de turno. |
| Alertas y resultados | Atender acciones de alerta autorizadas y consultar resultados cognitivos/evidencias permitidas para el cuidado. |

## 3. Módulos relacionados

- [Cuidados](../../../app/Modulos/Cuidados/README.md)
- [Medicacion](../../../app/Modulos/Medicacion/README.md)
- [Clinica](../../../app/Modulos/Clinica/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [ApoyoDecision](../../../app/Modulos/ApoyoDecision/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Residentes y fuentes de su contexto autorizado, órdenes vigentes, agenda, alertas y resultados permitidos. El alcance puede depender de turno/asignación, no de conocer un código.

## 5. Información que puede registrar o gestionar

Registros de Enfermería y administración/ejecución permitidas, con personal propio, jornada y residente coherentes.

## 6. Límites

- No prescribe ni modifica medicamento, dosis o frecuencia de la orden médica.
- No firma revisiones de Medicina/Psicología ni gestiona reglas por disponer de lectura.
- Una observación de dificultad no se presenta como aplicación estandarizada o diagnóstico cognitivo.

## 7. Dashboard y recorrido de trabajo

Mis residentes, agenda y pendientes de cuidados/medicación, alertas y registros del turno; acceso a resumen y continuidad.

1. Abrir la guardia y confirmar residentes asignados.
2. Revisar órdenes, cuidados y alertas.
3. Registrar controles y actuaciones realmente realizadas.
4. Comunicar pendientes al siguiente turno/profesional autorizado.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `enfermeria.ver_pacientes_asignados`, `signos_vitales.crear`, `administraciones_medicacion.crear`, `pases_turno.crear`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Recibe órdenes y planes, aporta observaciones fechadas y entrega pendientes mediante el pase; no altera decisiones de otras disciplinas.

## 10. Participación en el sistema experto

Consulta resultados/evidencias dentro del residente asignado. No recibe el permiso de guardar ejecuciones observacionales ni firma médica/psicológica por este rol.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

H03 puede volver ambiguo el contexto de guardia; H27/H28 limitan la evidencia actual de medicación. Las gráficas deben distinguir observación de medición.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)
- [tests/Feature/Fase3SeguridadNucleoTest.php](../../../tests/Feature/Fase3SeguridadNucleoTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
