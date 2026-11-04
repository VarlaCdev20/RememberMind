---
title: "Funciones y alcance — Medicina"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Medicina

**Identificador del catálogo:** `MEDICO GENERAL/GERIATRA`. **Grupo:** Profesional clínico. **Corte documental:** 25/10/2026.

## 1. Finalidad

Registrar atención médica, decisiones y seguimiento clínico propios, y participar en la evaluación cognitiva según contratos aprobados.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Consulta clínica | Revisar expediente, antecedentes, diagnósticos, alergias, evolución y fuentes pertinentes autorizadas. |
| Atención médica | Registrar atenciones, notas, antecedentes y diagnósticos propios conforme a competencia y autoría. |
| Prescripción | Crear, modificar o suspender órdenes médicas mediante el flujo de prescripción y horarios. |
| Estudios e indicaciones | Solicitar/documentar estudios, resultados, informes, derivaciones e indicaciones según permisos. |
| Continuidad interdisciplinaria | Consultar cuidados, funcionalidad y alertas pertinentes; comunicar indicaciones para su ejecución. |
| Evaluación cognitiva | Consultar/capturar fuentes autorizadas, transcribir aplicaciones y registrar/rectificar su cadena Petersen cuando corresponda. |
| Revisión y conocimiento | Revisar después resultados conservados y gestionar/aprobar reglas en el alcance institucional adoptado. |

## 3. Módulos relacionados

- [Clinica](../../../app/Modulos/Clinica/README.md)
- [Medicacion](../../../app/Modulos/Medicacion/README.md)
- [Valoraciones](../../../app/Modulos/Valoraciones/README.md)
- [ApoyoDecision](../../../app/Modulos/ApoyoDecision/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Fuentes clínicas, instrumentales y funcionales pertinentes del residente autorizado, con permisos específicos; IADL requiere su permiso propio.

## 5. Información que puede registrar o gestionar

Registros médicos propios, órdenes, estudios/indicaciones y fuentes o juicios expertos autorizados. Las rectificaciones conservan historia.

## 6. Límites

- No atribuye un acto a otro profesional ni corrige la cadena original ajena.
- La lectura de un perfil orientativo no lo convierte automáticamente en diagnóstico confirmado.
- La revisión médica no sobrescribe la psicológica ni altera la salida automática.

## 7. Dashboard y recorrido de trabajo

Atenciones registradas, agenda, estudios propios pendientes, prescripciones/notas recientes y alertas autorizadas; no contadores clínicos inventados.

1. Abrir residente y revisar fuentes pertinentes.
2. Registrar valoración/decisión médica y órdenes autorizadas.
3. Consultar o ejecutar análisis cognitivo dentro del contrato.
4. Revisar después el resultado realmente conservado y comunicar continuidad.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `diagnosticos.crear`, `prescripciones.crear`, `valoraciones_petersen.crear`, `evaluaciones_expertas.revisar_medicina`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Aporta contexto médico y revisión diferencial; Enfermería ejecuta/documenta cuidado y Psicología aporta su evaluación independiente.

## 10. Participación en el sistema experto

Medicina/Psicología gestionan y aprueban reglas; pueden registrar sus juicios, leer IADL y guardar ejecuciones observacionales. La revisión del DTO conjunto sigue pendiente de conservación institucional completa.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

H07–H12: conservación conjunta pendiente, corte retrospectivo, lectura/firma y explicación con alcance insuficiente. No se acredita cierre clínico con pruebas sintéticas.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/PermisosPetersenOriginalTest.php](../../../tests/Feature/PermisosPetersenOriginalTest.php)
- [tests/Feature/PermisosLecturaIadlPetersenTest.php](../../../tests/Feature/PermisosLecturaIadlPetersenTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
