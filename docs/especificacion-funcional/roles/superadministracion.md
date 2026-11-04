---
title: "Funciones y alcance — Superadministración"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Superadministración

**Identificador del catálogo:** `SUPERADMINISTRADOR`. **Grupo:** Institucional y técnico. **Corte documental:** 25/10/2026.

## 1. Finalidad

Administrar el acceso y supervisar técnicamente el sistema y su operación global.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Cuentas y acceso | Gestionar usuarios, estados y asignación de roles/permisos conforme al gobierno vigente. |
| Supervisión global | Consultar los dominios institucionales, operativos y clínicos en su alcance de lectura. |
| Auditoría | Inspeccionar trazabilidad técnica, acciones e información de seguridad sin editar la historia para ocultar hechos. |
| Conocimiento experto | Consultar versiones, red semántica, reglas y estado técnico; la gestión/aprobación de reglas corresponde a Medicina/Psicología. |
| Previsualización por rol | Usar «Ver como rol» como presentación temporal de solo lectura, conservando identidad y sin impersonar a un profesional. |
| Reportes | Consultar reportes globales autorizados y supervisar incidencias operativas. |

## 3. Módulos relacionados

- [Identidad](../../../app/Modulos/Identidad/README.md)
- [Institucion](../../../app/Modulos/Institucion/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Reportes](../../../app/Modulos/Reportes/README.md)
- [ApoyoDecision](../../../app/Modulos/ApoyoDecision/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Lectura global contemplada por el baseline, condicionada por cuenta activa y controles de la entrada. La administración técnica no sustituye las reglas de estado, integridad o admisión.

## 5. Información que puede registrar o gestionar

Cuentas, roles/permisos y gestión técnica/institucional autorizada. La capacidad de una mutación se comprueba por operación, no por un bypass genérico.

## 6. Límites

- No diagnostica, prescribe, administra medicación o firma valoraciones por disponer únicamente de este rol.
- No recibe gestión/aprobación de reglas clínicas ni revisión médica/psicológica por ser autoridad técnica.
- No modifica ni elimina auditoría o historia clínica para ocultar actuaciones.

## 7. Dashboard y recorrido de trabajo

Supervisión de acceso, operación institucional, disponibilidad/cobertura y alertas autorizadas; recuentos con período y fuente. «Ver como rol» debe conservar el banner y el bloqueo de escritura.

1. Comprobar incidencias y accesos.
2. Entrar a cuentas, supervisión o auditoría según la tarea.
3. Ejecutar solo la operación administrativa/técnica autorizada.
4. Consultar resultado y trazabilidad.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `usuarios.gestionar`, `roles.editar_permisos`, `auditoria.ver`, `reglas_expertas.ver`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Mantiene condiciones técnicas para el trabajo de todos los roles; deriva decisiones clínicas al profesional competente y operaciones diarias a Administración.

## 10. Participación en el sistema experto

Consulta técnica de conocimiento y resultados; no firma ni gestiona reglas clínicas por el solo rol. Un rol profesional adicional se evalúa por su propia competencia y permisos.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

H07/H08: la lectura técnica no resuelve la conservación conjunta. Las excepciones locales de pruebas no constituyen capacidades clínicas permanentes.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/RolePreviewTest.php](../../../tests/Feature/RolePreviewTest.php)
- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
