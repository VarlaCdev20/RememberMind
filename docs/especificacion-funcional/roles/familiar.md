---
title: "Funciones y alcance — Familiar"
status: CURRENT
version: "1.2"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Familiar

**Alta y vínculo — decisiones 17B/18A del 25/10/2026:** Gerencia registra/corrige el vínculo familiar con trazabilidad. La cuenta Familiar queda activa al completar sus datos y contar con vínculo autorizado. La activación no publica información clínica ni sustituye los controles de cuenta, vínculo, autorización de información y fuente. [Constancia](GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos). Implementación y pruebas de esta ampliación pendientes.

**Identificador del catálogo:** `FAMILIAR`. **Grupo:** Consulta vinculada. **Corte documental:** 25/10/2026.

## 1. Finalidad

Consultar información expresamente autorizada del residente con el que mantiene un vínculo válido.

La funcionalidad de esta ficha compila [el baseline aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y decisiones allí enlazadas. La descripción no crea permisos ni certifica su implementación completa.

## 2. Funciones principales

| Función | Qué le corresponde hacer |
|---|---|
| Identidad y vínculo | Consultar el resumen permitido del residente vinculado, no un directorio global. |
| Actividad autorizada | Consultar actividades/participación del vinculado cuando la entrada lo permite. |
| Visitas | Consultar las visitas propias dentro del contexto autorizado. |
| Privacidad | Acceder solo al contenido permitido; el vínculo no publica automáticamente documentos o expediente clínico. |

## 3. Módulos relacionados

- [Residentes](../../../app/Modulos/Residentes/README.md)
- [Social](../../../app/Modulos/Social/README.md)

Relacionar un módulo no concede todas sus operaciones; cada fuente/acción conserva su autorización.

## 4. Información que puede consultar

Requiere contacto asociado a la cuenta y vínculo activo con autorización de información. Las fuentes clínicas/documentales no publicadas permanecen restringidas.

## 5. Información que puede registrar o gestionar

No registra ni corrige actos clínicos. Las entradas de consulta no conceden gestión de visitas, contactos, documentos o participación por implicación.

## 6. Límites

- No consulta residentes ajenos ni listados globales.
- No accede a notas, historia sensible, resultados expertos o documentos clínicos por disponer del vínculo.
- No diagnostica, prescribe, valora, revisa resultados o gestiona conocimiento.

## 7. Dashboard y recorrido de trabajo

Resumen vinculado, actividades permitidas y visitas propias. Una documentación sin mecanismo de publicación no se muestra como compartida.

1. Autenticar y comprobar vínculo/permiso.
2. Abrir únicamente el residente autorizado.
3. Consultar información disponible y visitas propias.
4. Recibir un estado claro cuando el contenido está restringido o no publicado.

Fuente de navegación observada: [SidebarService](../../../app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php). [El contrato de dashboards](../../arquitectura/DASHBOARDS_POR_ROL.md) aporta fuentes/períodos por su corte. Las vistas y cada botón se detallarán en las fichas de pantalla, sin duplicar esta responsabilidad por cada vista.

## 8. Autorización y ejemplos de permisos

Cuenta activa + permiso explícito + Policy/contexto + regla/estado válido + relación/competencia. En actos clínicos, personal activo y autoría propia cuando corresponda. Un grant del seeder no demuestra permiso efectivo en la BDD instalada ni sustituye la Policy.

Ejemplos localizados en [el seeder de roles](../../../database/seeders/RolesAndPermissionsSeeder.php): `residentes.ver`, `residentes_contactos.ver`, `actividades.ver`, `visitas.ver`. No es una lista exhaustiva ni autoriza a ejecutar el seeder; se consultó como código.

## 9. Continuidad con otros actores

Recibe la información autorizada por el proceso institucional. Ampliar publicación clínica exige decisión específica; no surge de esta ficha.

## 10. Participación en el sistema experto

No tiene acceso al resultado experto por el solo rol familiar ni participa en su captura, revisión o gobierno.

[Fuentes expertas vigentes](../../sistema-experto/README.md): distinguir resultado observacional conservado de evaluación conjunta transitoria, revisión de resultado y aprobación de una regla. Son actos diferentes.

## 11. Evidencia y limitaciones actuales

El 26/10/2026 se detallaron [registro y vistas desde Gerencia](../vistas/gerencia/09-FAMILIARES-Y-CONTACTOS.md), [formularios y separación de autorizaciones](../vistas/gerencia/10-FORMULARIOS-FAMILIARES-PRIVACIDAD.md) y [24 casos de aceptación](../vistas/gerencia/11-ACEPTACION-FAMILIARES-CONTACTOS.md). Especificación documental: no concede a Familiar gestión de vínculos ni publica clínica, y no acredita integración o ejecución de pruebas.

El recorrido auditado sin vínculo no acredita todas las publicaciones familiares. DEC-OPEN-006 mantiene abierto exclusivamente el contenido clínico adicional.

Pruebas localizadas, **no reejecutadas** al redactar esta ficha:

- [tests/Feature/Fase3SeguridadNucleoTest.php](../../../tests/Feature/Fase3SeguridadNucleoTest.php)
- [tests/Feature/RolesVistasPermisosMatrixTest.php](../../../tests/Feature/RolesVistasPermisosMatrixTest.php)

La [auditoría H01–H32](../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) conserva resultados ejecutados y límites del 25/10. Esta entrega documenta conteo y funciones; la aceptación vista por vista, permisos por cada acción y corrección de defectos continúan según su evidencia.
