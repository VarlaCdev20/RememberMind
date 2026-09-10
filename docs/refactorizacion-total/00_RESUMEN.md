# Diseño técnico de refactorización total — RememberMind

Fecha: 10/09/2026. Rama verificada: **MEJORA-SYS**. Entrega exclusivamente documental; no implementación, commit, push, paquetes ni cambios de BD. Se mantiene **Laravel + Blade + Livewire**. Este diseño reemplaza para esta tarea cualquier propuesta previa de migración tecnológica del frontend.

## Resultado

- **61 tablas AS-IS**, verificadas en esquema public mediante metadatos en transacción de solo lectura. 60 archivos de migración no equivalen a 60 tablas.
- **83 tablas TO-BE**: 68 de aplicación (incluyen users y procedencia de migración) + 15 técnicas Laravel/Spatie. No se busca un número artificial de tablas.
- **14 módulos**: Identidad y Seguridad; Institución y Personal; Residentes; Admisiones y Ocupación; Clínica; Medicación; Valoración Geriátrica Integral; Cuidados; Social y Familia; Actividades y Voluntariado; Seguridad Asistencial; Documentos; Sistema Experto; Reportes.
- Inventario físico de las **854 columnas** actuales, PK/FK/restricciones y mapa completo por tabla/columna. ERD, diccionario objetivo, modelos, relaciones, operaciones y pantallas por entidad, permisos y plan integral.

## Decisiones centrales

Persona única separada de cuenta y profesional; profesión/especialidad/acreditación independientes de rol/cargo/área. PostgreSQL con claves técnicas y códigos visibles conservados. Estancias y ocupación temporal, prescripción separada de dosis administrada, valoraciones e instrumentos versionados, planes separados de ejecuciones, evidencia documental privada y trazabilidad clínica sin eliminación física.

Todos los módulos deben terminar funcionales. El inventario distingue código operativo, vías incompletas y rutas que solo muestran un dashboard. Visitas, ficha social, portales y sistema experto requieren completar casos de uso; fisioterapia/nutrición usan atención, VGI y cuidados comunes. Estado de cuenta no tiene backend actual y queda fuera hasta aprobar alcance financiero (D12).

## Principales fusiones y retiros de estructuras

| Actual | Resultado |
|---|---|
| horarios_personal_admin + horarios_personal_salud | staff_schedules y staff_assignments |
| turnos_institucionales + turnos_enfermeria | shifts, sin confundir turno catálogo con jornada publicada |
| documentos_usuarios + documentos_adulto_mayor + documentos_preadmision | documents + document_versions + document_events; requisitos en document_types |
| familiares y datos personales de users/voluntarios/adulto_mayor/preadmisiones | persons + person_identifiers; vínculos y perfiles separados |
| valoracion_funcional_adulto + valoracion_enfermeria_admision + evaluaciones_geriatricas | assessments con instrument_versions, manteniendo diferencias de formulario |
| obs_adulto + seguimientos_diarios | daily_observations con esquema y tipo; incidentes verdaderos separados |
| estado_adulto | Enums por dimensión y hechos/estancias; retirar catálogo mixto después de conciliar |

No hay recomendación de borrar datos útiles directamente. “Retirar” tabla antigua exige migrar, conciliar y cumplir conservación aprobada. activity_log se conserva y además se extraen hechos clínicos que una vía actual guarda solo en properties.

## Principales tablas nuevas

persons, person_identifiers, professionals, professional_qualifications, professions, specialties, positions, institutions, staff_assignments, roster_assignments; admissions, admission_decisions, bed_assignments; encounters, clinical_notes, diagnoses, allergies; prescriptions, prescription_items; instrument_versions; care_plan_versions, care_task_executions; consents; activity_participations y asistencia voluntaria explícita; visits, social_followups, incidents; document_versions/document_events; expert_rule_sets/versions/runs/reviews; report_exports y migration_links.

## Aprobaciones necesarias

D01–D15 de [10_DECISIONES_PENDIENTES.md](10_DECISIONES_PENDIENTES.md): aceptar ERD/alcance, conciliación de identidades/estados/camas, matriz de acceso y competencias, firma/retención clínica, instrumentos y pautas, consentimiento y documentos, reglas de turnos, alcance de páginas pendientes, reglas del sistema experto y estrategia de corte/recuperación. No se solicita aprobar una nueva tecnología frontend.

## Guía de lectura

| Documento | Contenido |
|---|---|
| [01_INVENTARIO_FUNCIONAL.md](01_INVENTARIO_FUNCIONAL.md) | Capacidades reales, incompletas, métodos y plantillas inspeccionadas |
| [02_BD_AS_IS.md](02_BD_AS_IS.md) | 61 tablas, modelos, claves, relaciones, problemas e historial |
| [03_BD_TO_BE.md](03_BD_TO_BE.md) | 83 tablas, ERD completo, campos, integridad y temporalidad |
| [04_MAPA_MIGRACION_BD.md](04_MAPA_MIGRACION_BD.md) | Todas las columnas origen, destino y transformaciones/retiradas |
| [05_MODULOS_TO_BE.md](05_MODULOS_TO_BE.md) | 14 módulos, casos de uso, modelos, pantallas y dependencias |
| [06_CRUDS_Y_VISTAS.md](06_CRUDS_Y_VISTAS.md) | Siete operaciones por entidad, estados y componentes Blade/Livewire |
| [07_ROLES_PERMISOS.md](07_ROLES_PERMISOS.md) | Roles combinables, permisos y Policies contextuales |
| [08_ESTRUCTURA_CODIGO.md](08_ESTRUCTURA_CODIGO.md) | Monolito modular Laravel pragmático y reutilización |
| [09_PLAN_REFACTORIZACION.md](09_PLAN_REFACTORIZACION.md) | Secuencia completa, pruebas, conciliación, corte y recuperación |
| [10_DECISIONES_PENDIENTES.md](10_DECISIONES_PENDIENTES.md) | Decisiones de negocio/datos/operación y responsables |

## Alcance de verificación y límites

Inspección estática de rutas, métodos, modelos, migraciones, seeders, servicios, requests, vistas y tests, contrastada con metadatos físicos de PostgreSQL. No se ejecutó todo el producto en navegador ni se certifican resultados clínicos o validez normativa. Las reglas clínicas y datos ambiguos requieren aprobación; no se afirma que los historiales ausentes puedan reconstruirse.

La rama ya contenía modificaciones en routes/web.php y tests/Feature/UsuariosRoutesPermissionsTest.php del trabajo anterior. Se preservan. La verificación de esta entrega compara contenido de app/database/routes/resources/tests y comprueba que solo se agregaron los once documentos solicitados. No se ejecutan tests contra BD real, seeders ni migraciones.
