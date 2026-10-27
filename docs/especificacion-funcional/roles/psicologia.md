---
title: "Funciones y alcance — Psicología"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Psicología

**Identificador del catálogo:** `PSICOLOGO/A`. **Grupo:** Profesional clínico. **Corte documental:** 25/10/2026.

## 1. Finalidad

Registrar e interpretar sus valoraciones psicológicas/cognitivas autorizadas y dar seguimiento, conservando mediciones y condiciones originales.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Atención y seguimiento | Registrar atenciones y notas propias, evolución y seguimientos dentro del permiso y competencia. |
| Valoración psicológica | Documentar fuentes psicológicas y observaciones conductuales pertinentes con autoría y fecha. |
| Instrumentos | Aplicar o transcribir instrumentos autorizados con versión, respuestas/tiempos y condiciones originales; interpretar solo con método aplicable. |
| Fuentes compartidas | Consultar controles, conducta, sueño, planes y alertas permitidos para la valoración. |
| Petersen | Registrar/rectificar su propia cadena de dominios y requisitos originales; C02 se deriva. |
| Resultado y revisión | Consultar inferencia y registrar revisión psicológica posterior sobre evaluación conservada. |
| Gobierno de reglas | Gestionar, sustituir, archivar y aprobar/rechazar conocimiento dentro del gobierno institucional vigente. |

## 3. Módulos relacionados

- [Valoraciones](../../../app/Modulos/Valoraciones/README.md)
- [Clinica](../../../app/Modulos/Clinica/README.md)
- [ApoyoDecision](../../../app/Modulos/ApoyoDecision/README.md)
- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Alertas](../../../app/Modulos/Alertas/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Expediente y fuentes pertinentes autorizadas para Psicología, con lectura IADL específica y sin convertir acceso parcial en evaluación completa.

## 5. Información que puede registrar o gestionar

Valoraciones, instrumentos y juicios propios autorizados, revisión psicológica y gobierno de reglas en su alcance aprobado.

## 6. Límites

- No prescribe ni cambia registros médicos por su rol psicológico.
- No fabrica respuestas, baremos, autores ni fechas para conseguir clasificación.
- No registra C02 como original, firma por Medicina ni modifica el resultado automático al discrepar.

## 7. Dashboard y recorrido de trabajo

Atenciones/agenda propias, aplicaciones realizadas, fuentes y alertas autorizadas; cada consulta debe respetar permisos revocados.

1. Abrir residente y antecedentes permitidos.
2. Capturar valoración/aplicación original con condiciones.
3. Consultar análisis por dominio y Petersen o sus abstenciones.
4. Registrar después revisión independiente cuando exista conservación real.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `valoraciones_psicologicas.crear`, `aplicaciones_instrumento.transcribir`, `controles_funcionales_iadl.ver`, `reglas_expertas.revisar`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Aporta evidencia e interpretación psicológica; contrasta con funcionalidad y contexto médico sin sustituir a sus autores; comunica seguimiento al equipo autorizado.

## 10. Participación en el sistema experto

Comparte con Medicina gestión de reglas, captura propia y revisión posterior independiente. Las observaciones no se convierten en evidencia objetiva por una etiqueta de interfaz.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

H01 y H14–H22: lectura revocada, series/etiquetas incorrectas, Petersen paralelo, puntaje/firma y ficha desconectada. H11: lectura y firma acopladas en la Policy actual.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/PermisosPetersenOriginalTest.php](../../../tests/Feature/PermisosPetersenOriginalTest.php)
- [tests/Feature/PermisosLecturaIadlPetersenTest.php](../../../tests/Feature/PermisosLecturaIadlPetersenTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
