---
title: "Catálogo y funcionalidad de los diez roles"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [role_functional_compilation]
verification_scope: APPROVED_ROLE_BASELINE_AND_STATIC_CODE_COMPARISON
runtime_verified: false
---

# Roles y funcionalidad de RememberMind

**Ampliación funcional de Gerencia — 25/10/2026:** se adoptaron todas las [respuestas 15–29](GERENCIA_CUESTIONARIO_PENDIENTE.md#registro-y-próximos-pasos), incluyendo recuperación por enlace de cuentas de su alcance, registro/corrección de vínculos familiares y exportación PDF/CSV agregada. Se conservan las exclusiones de cuentas Gerencia/Superadministración y la separación clínica. Son decisiones documentadas, con implementación y pruebas pendientes; no permisos ya instalados.

## Cuántos roles tenemos

**Diez roles vigentes:** tres institucionales, seis profesionales y uno familiar. Coinciden el [baseline funcional aprobado](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y el catálogo `$rolesActivos` de [RolesAndPermissionsSeeder](../../../database/seeders/RolesAndPermissionsSeeder.php), contrastados el **25/10/2026**. No se ejecutó el seeder ni se cambió el catálogo instalado.

| Grupo | Cantidad | Integrantes |
|---|---:|---|
| Institucional | 3 | Superadministración, Gerencia, Administración |
| Profesional | 6 | Medicina, Enfermería, Psicología, Nutrición, Fisioterapia, Pedagogía |
| Familiar | 1 | Familiar |
| **Total** | **10** | Identificadores únicos del catálogo vigente |

Medicina general/Geriatría comparte un único rol `MEDICO GENERAL/GERIATRA`; no se cuenta como dos. Un usuario puede reunir roles, sin crear un rol nuevo por esa combinación. Rol, cuenta de usuario y profesional/personal son identidades diferentes. VOLUNTARIO está fuera del alcance vigente.

## Qué hace cada rol

| Rol | Funcionalidad central | Ficha detallada |
|---|---|---|
| Superadministración | Administrar el acceso y supervisar técnicamente el sistema y su operación global. | [Funciones, límites y fuentes](superadministracion.md) |
| Gerencia | Dirigir la residencia mediante gestión de personal, organización, planificación y supervisión de cobertura. | [Funciones, límites y fuentes](gerencia.md) |
| Administración | Coordinar el ingreso y la operación cotidiana de la residencia, sin asumir actos clínicos ni dirección técnica de acceso. | [Funciones, límites y fuentes](administracion.md) |
| Medicina | Registrar atención médica, decisiones y seguimiento clínico propios, y participar en la evaluación cognitiva según contratos aprobados. | [Funciones, límites y fuentes](medicina.md) |
| Enfermería | Ejecutar y documentar cuidado continuo de residentes autorizados, manteniendo observación longitudinal, medicación y pase de turno. | [Funciones, límites y fuentes](enfermeria.md) |
| Psicología | Registrar e interpretar sus valoraciones psicológicas/cognitivas autorizadas y dar seguimiento, conservando mediciones y condiciones originales. | [Funciones, límites y fuentes](psicologia.md) |
| Nutrición | Registrar valoración, mediciones y seguimiento nutricional propios y sus planes autorizados. | [Funciones, límites y fuentes](nutricion.md) |
| Fisioterapia | Registrar valoración funcional/motora y seguimiento de intervenciones de su competencia. | [Funciones, límites y fuentes](fisioterapia.md) |
| Pedagogía | Organizar y documentar actividades, participación y seguimiento pedagógico dentro del ámbito autorizado. | [Funciones, límites y fuentes](pedagogia.md) |
| Familiar | Consultar información expresamente autorizada del residente con el que mantiene un vínculo válido. | [Funciones, límites y fuentes](familiar.md) |

## Separación de responsabilidades

- **Superadministración** administra el sistema y supervisa globalmente; el rol técnico no firma ni prescribe clínicamente.
- **Gerencia** organiza personal y planificación institucional.
- **Cuentas — decisiones del 25/10:** Gerencia crea cuentas de Administración, los seis roles profesionales y Familiar, y puede editar sus datos y activar/suspender su acceso. No gestiona Gerencia/Superadministración dentro de esta extensión. Superadministración crea todos. Cambiar roles, editar permisos y eliminar no están incluidos; [alta](../vistas/gerencia/02-PERSONAL.md#7-decisión-de-la-propietaria-alta-de-cuentas-por-rol) y [mantenimiento](../vistas/gerencia/02-PERSONAL.md#9-decisión-registrada-mantenimiento-de-datos-y-acceso). Implementación pendiente.
- **Administración** coordina ingreso, alojamiento y operación diaria.
- **Profesionales** registran y actúan dentro de su disciplina, permisos y contexto. Medicina prescribe; Enfermería administra/documenta el cuidado.
- **Familiar** consulta solo al residente vinculado y el contenido autorizado.

## Participación en la evaluación cognitiva

| Actor | Función aprobada y límite |
|---|---|
| Medicina/Psicología | Fuentes y juicios originales autorizados, lectura IADL específica, gestión/aprobación de reglas y revisión posterior independiente sobre resultado conservado |
| Enfermería | Observaciones y cuidados propios; consulta de resultados/evidencias permitidos. No firma revisión médica/psicológica ni guarda inferencia por disponer de lectura |
| Superadministración | Consulta técnica y supervisión; no gestión/aprobación clínica de reglas por el solo rol |
| Nutrición/Fisioterapia/Pedagogía | Fuentes y seguimiento de su competencia; no adquieren captura/revisión cognitiva ni gestión de reglas por analogía |
| Gerencia/Administración/Familiar | No reciben competencia clínica experta por esos roles |

La revisión de un resultado y la aprobación de una regla son actos distintos. No se exige firma previa para cada ejecución técnica válida. La conservación conjunta instrumental/Petersen sigue pendiente; no se documenta como una revisión histórica ya operativa.

## Cómo se determina el acceso real

No basta el nombre del rol ni la visibilidad del menú: sesión y cuenta activa + permiso efectivo + Policy/contexto + regla/estado + relación y competencia. Personal activo y autoría propia cuando el acto los exige. [Modelo compuesto de autorización](../../seguridad/MODELO_AUTORIZACION.md).

Una capacidad funcional aprobada, un permiso declarado en el seeder y una acción efectivamente protegida son evidencias diferentes. Los defectos H01/H02 no amplían facultades; deben corregirse. La previsualización «Ver como rol» es de solo lectura y no modifica identidad o atribución de actos.

## Alcance de esta documentación

Cada ficha desarrolla finalidad, funciones, módulos, lectura/escritura, límites, dashboard, flujo, permisos de ejemplo, continuidad, experto y evidencias. [La documentación de cada vista](../vistas/README.md) completará campos, botones, rutas y variantes de rol.

**Verificado aquí:** diez nombres únicos y su correspondencia con las diez fichas, fuentes/enlaces locales y ejemplos de permisos presentes en el código. **No ejecutado aquí:** seeder, cambios de permisos, consulta del catálogo de producción, nuevas suites de autorización o QA visual. [Estado del producto](../../ESTADO_ACTUAL.md) conserva los resultados técnicos previos, sin convertir esta redacción en aceptación de cada operación.
