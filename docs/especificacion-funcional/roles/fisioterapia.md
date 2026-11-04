---
title: "Funciones y alcance — Fisioterapia"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Fisioterapia

**Identificador del catálogo:** `FISIOTERAPEUTA`. **Grupo:** Profesional interdisciplinario. **Corte documental:** 25/10/2026.

## 1. Finalidad

Registrar valoración funcional/motora y seguimiento de intervenciones de su competencia.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Consulta funcional | Revisar diagnósticos, dispositivos, signos y otras fuentes pertinentes con permisos propios. |
| Valoración funcional | Registrar y corregir valoraciones funcionales conforme al mecanismo y autoría autorizados. |
| Movilidad y dolor | Documentar movilidad/dolor por las entradas permitidas. |
| Planes y seguimiento | Registrar planes de su ámbito y seguimiento de la atención. |
| Coordinación | Aportar observaciones funcionales para el cuidado, sin atribuir toda dependencia a una causa cognitiva. |
| Agenda y alertas | Consultar atenciones y alertas pertinentes, sin asumir gestión por mera lectura. |

## 3. Módulos relacionados

- [Valoraciones](../../../app/Modulos/Valoraciones/README.md)
- [Clinica](../../../app/Modulos/Clinica/README.md)
- [Cuidados](../../../app/Modulos/Cuidados/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Fuentes funcionales y clínicas pertinentes dentro del alcance autorizado; no sustituyen una evaluación cognitiva.

## 5. Información que puede registrar o gestionar

Valoraciones funcionales, movilidad/dolor, atenciones/notas y planes propios habilitados por operación.

## 6. Límites

- No prescribe medicación ni registra interpretación psicológica por este rol.
- No firma revisiones médicas/psicológicas ni administra reglas cognitivas.
- No se atribuye captura IADL o acreditación C03 automáticamente: requiere el permiso/contrato específico.

## 7. Dashboard y recorrido de trabajo

Atenciones, valoraciones, planes y fuentes funcionales propias; sin severidad o sesiones futuras inventadas.

1. Revisar contexto autorizado.
2. Registrar valoración/seguimiento funcional.
3. Documentar plan e intervención de su competencia.
4. Comunicar evolución y apoyos necesarios al equipo autorizado.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `valoraciones_funcionales.crear`, `valoraciones_funcionales.editar`, `registros_movilidad.crear`, `alertas.ver`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Aporta datos funcionales originales para continuidad y valoración interdisciplinaria; su registro no decide automáticamente Petersen.

## 10. Participación en el sistema experto

Las fuentes funcionales pueden aportar contexto cuando el adaptador las admite; este rol no recibe la gestión/revisión cognitiva de Medicina/Psicología.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

Falta aceptación completa del recorrido propio; dos enlaces de menú probados no acreditan valoración, edición y seguimiento end-to-end.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)
- [tests/Feature/AccesoDashboardPorRolTest.php](../../../tests/Feature/AccesoDashboardPorRolTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
