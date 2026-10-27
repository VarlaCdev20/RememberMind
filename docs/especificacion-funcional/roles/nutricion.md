---
title: "Funciones y alcance — Nutrición"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Nutrición

**Identificador del catálogo:** `NUTRICIONISTA`. **Grupo:** Profesional interdisciplinario. **Corte documental:** 25/10/2026.

## 1. Finalidad

Registrar valoración, mediciones y seguimiento nutricional propios y sus planes autorizados.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Consulta pertinente | Revisar diagnóstico, alergias, indicaciones y fuentes autorizadas necesarias para su valoración. |
| Mediciones | Registrar mediciones antropométricas dentro de la entrada y unidades aprobadas. |
| Valoración nutricional | Registrar valoración propia y su seguimiento. |
| Continuidad alimentaria | Consultar ingesta, hidratación y eliminación cuando la fuente esté habilitada. |
| Planes | Registrar/seguir planes de cuidado de su ámbito según permiso y estado. |
| Agenda y alertas | Consultar atenciones programadas y alertas pertinentes; la lectura no concede gestión de la alerta. |

## 3. Módulos relacionados

- [Valoraciones](../../../app/Modulos/Valoraciones/README.md)
- [Clinica](../../../app/Modulos/Clinica/README.md)
- [Cuidados](../../../app/Modulos/Cuidados/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Información clínica/nutricional pertinente y controles autorizados del residente; acceso por fuente y contexto, no historia irrestricta.

## 5. Información que puede registrar o gestionar

Valoraciones nutricionales, mediciones, atenciones/notas y planes de su competencia según operación.

## 6. Límites

- No obtiene prescripción médica ni edición de diagnósticos por este rol.
- No interpreta perfiles cognitivos ni firma revisión médica/psicológica.
- No gestiona alertas solo por tener alertas.ver.

## 7. Dashboard y recorrido de trabajo

Atenciones, valoraciones, mediciones y planes propios, con alertas pertinentes; no estado nutricional calculado sin método aprobado.

1. Consultar fuentes autorizadas y contexto.
2. Registrar medición/valoración propia.
3. Documentar plan/seguimiento permitido.
4. Comunicar indicaciones de su ámbito y revisar evolución registrada.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `valoraciones_nutricionales.crear`, `mediciones_antropometricas.crear`, `planes_cuidado.crear`, `alertas.ver`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Recibe observaciones de ingesta y contexto médico autorizado; aporta seguimiento nutricional para la continuidad de cuidados.

## 10. Participación en el sistema experto

Sus fuentes pertinentes pueden aportar contexto a adaptadores autorizados, sin convertirlas por sí solas en diagnóstico cognitivo o facultad de revisión Petersen.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

H23: una entrada de Nutrición termina en placeholder pese a existir otro circuito. Menú accesible no acredita todas las operaciones de valoración.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)
- [tests/Feature/AccesoDashboardPorRolTest.php](../../../tests/Feature/AccesoDashboardPorRolTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
