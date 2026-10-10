---
title: "Matriz de roles, permisos y entradas"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-07
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [permission_mapping]
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
runtime_evidence_status: PARTIAL
runtime_evidence_scope: FASE_3_TARGETED_TESTS_ISOLATED_POSTGRESQL
supersedes: []
related_docs: ["../TRAZABILIDAD.md", "../README.md"]
related_modules: []
---

# Matriz de roles, permisos y entradas

Fase 2 fue revisión estática sobre el commit base y cambios previos. Fase 3 ejecutó pruebas/BDD acotadas según TRAZABILIDAD. Metadatos identifican esa base y no certifican runtime global; CURRENT no aprueba reglas nuevas.

## Interpretación

**APPROVED CONTRACT:** [AGENTS](../../AGENTS.md) y [baseline de roles](../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Matriz describe la entrada concreta y separa sus grants observados; no crea autorizaciones nuevas. CONDITIONAL exige cuenta ACTIVO, permiso efectivo, regla y contexto; no equivale a acceso ya probado. READ_ONLY requiere alcance de contenido permitido. DENY es ausencia de competencia ordinaria/denegación explícita de esa entrada; grants extra no bastan. NOT_VERIFIED no concede acceso. OPEN_DECISION necesita decisión institucional. ALLOW se reserva para autorización sin condiciones adicionales: ninguna operación sensible aquí lo usa.

SUPERADMIN = SUPERADMINISTRADOR; ADMIN = ADMINISTRADOR; MÉDICO = MEDICO GENERAL/GERIATRA; demás columnas representan roles exactos del seed (ENFERMEROS, PSICOLOGO/A, NUTRICIONISTA, FISIOTERAPEUTA, PEDAGOGO, FAMILIAR). Incluye los 10 roles, también GERENTE.

## Acciones y fronteras verificables

| Recurso / acción | SUPERADMIN | GERENTE | ADMIN | MÉDICO | ENFERMERÍA | PSICOLOGÍA | NUTRICIÓN | FISIOTERAPIA | PEDAGOGÍA | FAMILIAR | PERMISSION | POLICY | SCOPE / competencia | TEST | SOURCE |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Residente / ver ficha permitida | READ_ONLY | READ_ONLY | READ_ONLY | READ_ONLY | READ_ONLY | READ_ONLY | READ_ONLY | READ_ONLY | READ_ONLY | READ_ONLY | residentes.ver | ResidentePolicy::view | Familiar vínculo ACTIVO + autoriza_informacion; Enfermería contexto según pantalla | TEST-FAM-001 | [fuente](../../app/Policies/ResidentePolicy.php) |
| Residente / crear CRUD | DENY | DENY | DENY | DENY | DENY | DENY | DENY | DENY | DENY | DENY | No capacidad autónoma | ResidentePolicy::create false | Solo formalización institucional | TEST-ADM-001 | [fuente](../../app/Models/Residente.php) |
| Preadmisión / revisar HTTP | CONDITIONAL | NOT_VERIFIED | CONDITIONAL | NOT_VERIFIED | DENY | DENY | DENY | DENY | DENY | DENY | preadmisiones.revisar | NONE OBSERVED | PENDIENTE; panel exige cuenta activa + rol existente + permiso específico | TEST-PRE-001 parcial (crear, no revisar) | [fuente](../../app/Http/Controllers/Admisiones/PreadmisionController.php) |
| Admisión / formalizar HTTP | CONDITIONAL | NOT_VERIFIED | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | DENY | DENY | admisiones.formalizar | NONE OBSERVED | Cuenta activa + SA/Admin + admisiones.formalizar en Action; APROBADA + cama apta + transaction | TEST-ADM-001 | [fuente](../../app/Http/Controllers/Admisiones/AdmisionController.php) |
| Personal / gestionar | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | DENY | DENY | DENY | personal.gestionar / personal_institucional.ver | NONE OBSERVED | Gerente/Superadmin gestión; no Admin RRHH | RolesBaselineCongeladoTest | [fuente](../../database/seeders/RolesAndPermissionsSeeder.php) |
| Turnos / planificar maestro | CONDITIONAL | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | DENY | DENY | turnos.crear/editar/cambiar_estado | NONE OBSERVED | Planificación maestra distinta de asignar plaza | RolesBaselineCongeladoTest | [fuente](../../database/seeders/RolesAndPermissionsSeeder.php) |
| Jornada / asignar residente HTTP | CONDITIONAL | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | DENY | DENY | asignaciones_residente_jornada.gestionar | NONE OBSERVED | HTTP solo SA/Admin por rol+permiso; seed Enfermería posee gestionar pero esta entrada lo deniega; no extrapolar competencia | TEST-JOR-001 parcial | [fuente](../../app/Http/Controllers/Cuidados/CuidadoController.php) |
| Prescripción / crear o suspender | DENY | DENY | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | DENY | prescripciones.crear / suspender | PrescripcionPolicy | Médico activo, personal/área propios, atención/orden del residente | TEST-AUTH-001 | [fuente](../../app/Http/Controllers/Medicacion/MedicacionController.php) |
| Medicación / administrar HTTP | DENY | DENY | DENY | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | administraciones_medicacion.crear | Policy existente no llamada por esta entrada | Enfermería asignada + jornada + prescripción/horario propios; PRN motivo | TEST-MED-001/002 | [fuente](../../app/Backend/Modulos/Medicacion/Servicios/RegistrarAdministracionMedicacionService.php) |
| Signos / registrar Action primaria | DENY | DENY | DENY | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | signos_vitales.crear | NONE OBSERVED | Auth==autor, personal/jornada/residente/turno coherentes; médico tiene grants pero Action primaria es Enfermería | TEST-SV-001, TEST-CLI-001 | [fuente](../../app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php) |
| Objetivos signos / gestionar | DENY | DENY | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | DENY | objetivos_signos_vitales.gestionar | NONE OBSERVED | Médico activo, autor propio; excepción SA no aplica por decisión específica V2.2 | TEST-SV-003 | [fuente](../../app/Backend/Modulos/Clinica/Acciones/DefinirObjetivoSignoVitalAction.php) |
| Alertas / crear manual HTTP | DENY | DENY | DENY | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | alertas.gestionar | NONE OBSERVED | TurnoEnfermeriaService contextual; Superadmin no competencia ordinaria | TEST-ALT-001 parcial | [fuente](../../app/Http/Controllers/Alertas/AlertaController.php) |
| Alertas / reconocer-asignar-seguir-cerrar HTTP | NOT_VERIFIED | DENY | CONDITIONAL | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | alertas.reconocer/asignar/seguimiento/cerrar; Enfermería gestionar | NONE OBSERVED | Admin por permiso; resto requiere contexto Enfermería. Superadmin comportamiento por entrada, no permiso clínico universal | TEST-ALT-001; negativos por entrada NOT_FOUND | [fuente](../../app/Http/Controllers/Alertas/AlertaController.php) |
| Pase / entregar o recibir panel | DENY | DENY | DENY | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | DENY | pases_turno.crear/editar (contrato); ver en mount observado | NONE OBSERVED | Emisor/receptor legítimos; cuenta/permiso/ARJ exacta revalidados Fase 3; recepción PLANIFICADA legítima | TEST-PAS-001/002 | [fuente](../../app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php) |
| Plan / crear HTTP | DENY | DENY | DENY | CONDITIONAL | CONDITIONAL | DENY | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY | planes_cuidado.crear | NONE OBSERVED | AutorizacionClinicaService admite seis profesiones pero Psicología seed solo ver; área/permiso reales | NOT_FOUND específico | [fuente](../../app/Http/Controllers/Cuidados/CuidadoController.php) |
| Instrumento / aplicar HTTP | DENY | DENY | DENY | CONDITIONAL | DENY | CONDITIONAL | DENY | DENY | DENY | DENY | aplicaciones_instrumento.crear | NONE OBSERVED | Lista seis profesiones en Controller; seed efectivo concede crear a Médico/Psicología; método/licencia no inferidos | TEST-INS-001 parcial | [fuente](../../app/Http/Controllers/Instrumentos/InstrumentoController.php) |
| Valoración / crear propia disciplina | DENY | DENY | DENY | DENY | DENY | CONDITIONAL | CONDITIONAL | CONDITIONAL | CONDITIONAL | DENY | valoraciones_psicologicas/nutricionales/funcionales.crear; seguimientos_pedagogicos.crear | NONE OBSERVED | Cada disciplina solo su endpoint; no intercambiar profesiones | NOT_FOUND específico | [fuente](../../app/Http/Controllers/Valoraciones/ValoracionProfesionalController.php) |
| Contenido clínico familiar / publicar adicional | NOT_VERIFIED | NOT_VERIFIED | NOT_VERIFIED | NOT_VERIFIED | NOT_VERIFIED | NOT_VERIFIED | NOT_VERIFIED | NOT_VERIFIED | NOT_VERIFIED | OPEN_DECISION | No permiso universal aprobado | ResidentePolicy no resuelve contenido | DEC-OPEN-006; expediente completo no autorizado por vínculo | TEST-FAM-001 no prueba contenido | [fuente](../../docs/DECISIONES_PENDIENTES.md) |

## Grants observados, no permisos instalados certificados

[Seeder](../../database/seeders/RolesAndPermissionsSeeder.php) define 10 roles y permisos por recursos; el mapeo Fase 2 fue estático; Fase 3 ejecutó seed en PostgreSQL desechable, no en despliegue. Superadmin recibe todos los permisos web, pero [User::checkPermissionTo](../../app/Models/User.php) veta escritura clínica de rol único salvo sustitución temporal, y [Gate](../../app/Providers/AppServiceProvider.php) bypass solo lectura. Excepción [local/testing](../../docs/arquitectura/PERMISOS_TEMPORALES_SUPERADMIN.md) no permanente; TECH-004. La lista de recursos clínicos de ese veto no incluye alertas: revisar competencia de cada entrada, no asumir protección global del override.

Médico tiene administraciones_medicacion.crear y AdministracionMedicacionPolicy contempla Médico; HTTP administrar exige Enfermería. Tiene signos_vitales.crear, pero Action primaria de signos exige contexto Enfermería. Esto es **OBSERVED IMPLEMENTATION** por entrada, no una decisión que redefine profesión.

Panel preadmisión admite roles médicos al revisar; seed médico no concede preadmisiones.revisar ni admisiones.ver_dashboard. El acceso efectivo depende de grants adicionales/entrada; no declarar que el rol funciona end-to-end.

En permitir(), prefijos como `registros_` o `valoraciones_` no son wildcard: compara igualdad o prefijo seguido de punto. No afirmar que otorgan todo recurso con guion bajo. Enfermería añade explícitamente ingesta/hidratación/eliminación/movilidad.crear; otras escrituras requieren comprobar grant efectivo.

**Decisión explícita de la propietaria, 2026-10-07:** añadir a ENFERMEROS únicamente registros_conductuales.ver, registros_sueno.ver, registros_ingesta.ver, registros_hidratacion.ver, registros_eliminacion.ver y registros_movilidad.ver. El seeder los enumera individualmente. Las pantallas de Enfermería conservan cuenta activa y alcance contextual del residente; estos grants no autorizan prescripción ni nuevas escrituras. Sueño individual continúa en consulta si falta registros_sueno.crear. No ejecutar el seeder completo sobre una instalación con permisos personalizados para aplicar solamente estas seis lecturas: su syncPermissions afecta toda la matriz. Seeder probado en PostgreSQL desechable; aplicación selectiva autorizada en remembermind_dev local: ENFERMEROS 66 → 72 grants, sin retiradas ni cambios de otros roles, con auditoría Spatie. Detalle y límites en TRAZABILIDAD.

## Cobertura

**TESTED EXPECTATION:** [RolesBaselineCongeladoTest](../../tests/Feature/RolesBaselineCongeladoTest.php), IDs en [TRAZABILIDAD](../../docs/TRAZABILIDAD.md). Los tests de Superadmin pueden depender del flag testing; no ratifican escritura clínica permanente. El mapeo original Fase 2 fue **RUNTIME_NOT_EXECUTED**; Fase 3 ejecuta los casos acotados de la sección siguiente. Negativos universales por todas las rutas/Livewire y grants revocados siguen sin certificación; no convertir esta tabla en PASS global.

## Evidencia Fase 3 de autorización

Fase3SeguridadNucleoTest acredita Médico prescripción con autor autenticado; Enfermería con grants no crea/edita orden; Admin con grant no diagnostica; Superadmin sin excepción no prescribe y production veta flag temporal. Familiar propio solo identidad/visitas permitidas, ajeno/contacto inactivo denegados; clínica/PDF/documentos del residente/listas globales no publicados denegados. PaseTurnoReconstruidoTest acredita permisos escribir, cuenta activa, turno/residente/ARJ propios y recepción/anulación legítimas. Fase3AlertasTest acredita cuenta/scope de lecturas/detector y preserva permisos diferentes de coordinación HTTP/Panel. No aprueba publicación clínica Familiar ni concede profesión a Administración. Resultados exactos y límites en [TRAZABILIDAD](../TRAZABILIDAD.md).
