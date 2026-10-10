---
title: "Contrato de módulo — Jornadas y asignaciones"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [module_contract_compilation]
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
runtime_evidence_status: PARTIAL
runtime_evidence_scope: FASE_3_TARGETED_TESTS_ISOLATED_POSTGRESQL
supersedes: []
related_docs: ["../../TRAZABILIDAD.md", "../../sistema/INVARIANTES_NEGOCIO.md", "../../seguridad/MODELO_AUTORIZACION.md"]
related_modules: [jornadas-asignaciones]
---

# Jornadas y asignaciones

Compilación vigente, no aprobación de reglas nuevas. **APPROVED CONTRACT**: [AGENTS](../../../AGENTS.md), [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Descripciones de entradas, estados, clases y efectos son **OBSERVED IMPLEMENTATION**, sin certificar cumplimiento en todas las variantes. **CONFLICT** y **TECHNICAL DEBT** señalan discrepancias; **OPEN DECISION** no autoriza cambiar contrato.

Fase 2 fue una revisión estática. Fase 3 ejecutó verificación acotada en PostgreSQL aislado; resultados y límites en [trazabilidad](../../TRAZABILIDAD.md). runtime_verified: false no certifica globalmente el módulo; verified_against_commit identifica la base y no un commit de estos cambios.

## 1. Propósito

Separar catálogo de turno, jornada real y asignaciones laborales/asistenciales.

## 2. Alcance

turnos, jornadas, asignaciones_personal y asignaciones_residente_jornada; contexto aplicable de Enfermería.

## 3. Fuera de alcance

Fase 3 endurece contratos existentes con cambios acotados y pruebas. No cambia BDD, reglas institucionales, competencias ni catálogos; tampoco entrega motor experto o certificación global.

## 4. Actores

Gerente planificación maestra; Administrador operación/asignación; Enfermería actúa en contexto asignado.

## 5. Competencias

Asignación de personal a área/plaza no equivale a asignación de residente ni concede todas las competencias clínicas.

## 6. Precondiciones

Personal activo real, turno/jornada válidos; para escritura de cuidado, cuenta/permiso/residente/jornada aplicables.

## 7. Entidades y tablas

turnos, jornadas, personal, areas, asignaciones_personal, asignaciones_residente_jornada y residentes.

## 8. Relaciones

Jornada enlaza turno/fecha; asignación laboral personal-área-jornada; asistencial residente-personal-jornada.

## 9. Estados

ABIERTA creada HTTP, ACTIVA panel; lectores admiten PLANIFICADA/EN_CURSO según operación. Asignaciones ACTIVA/ACTIVO; laboral previa ANULADA al sustituir plaza.

## 10. Caso de uso principal

Planificar jornada/plaza; asignar residente explícitamente; resolver contexto propio antes del acto asistencial.

## 11. Flujos alternativos

Sin personal/jornada no se fabrica contexto. Noche puede cruzar fecha; testing tiene fallback de hora que no certifica ventana real.

## 12. Validaciones técnicas

exists no basta para contexto asistencial. Panel/ARJ conservan sus guardias. InstitucionalController::abrirJornada exige cuenta activa, permiso jornadas.gestionar y turno ACTIVO; asignarPersonal exige permiso, personal ACTIVO y área ACTIVA, bloquea jornada y rechaza estado que no admite asignación. No se inventa un catálogo nuevo; mantiene PLANIFICADA/ABIERTA/ACTIVA/EN_CURSO existentes.

## 13. Reglas de negocio

Usuario != personal; turno != jornada; asignación laboral != asistencial; no fallback de primera fila.

## 14. Reglas clínicas aprobadas

No se impone turno de Enfermería universal a todos los actos médicos; depende del caso de uso aprobado.

## 15. Autorización

Panel autorizarAsignacion comprueba ACTIVO+turnos.asignar; asignarJornada HTTP roles SA/Admin+gestionar. Service de turno añade scope/competencia para cuidado.

## 16. Transaction boundary

Panel reasignación de plaza conserva transacción; HTTP residente-jornada mantiene transacción/lock. asignarPersonal HTTP revalida jornada bloqueada dentro de transacción. Abrir jornada crea una fila. No se acredita carrera de asignaciones por la prueba de camas.

## 17. Persistencia

Jornada real y asignaciones explícitas; generar pase no fabrica jornada entrante.

## 18. Longitudinalidad

Desvincular anula asignación laboral; conserva relación previa. Cierre completo de jornada NOT VERIFIED en segmentos revisados.

## 19. Alertas

Lectura por residentes/jornada contextual; asignar personal no genera clínica ficticia.

## 20. Auditoría

Auditoría por todas las operaciones de planificación NOT VERIFIED; no sustituir autoría asistencial por registrador laboral.

## 21. Continuidad asistencial

Contexto conecta cuidados y emisor/receptor de pase; responsable de área se muestra desde asignaciones, sin columna/edición nueva.

## 22. Entrada UI

[resources/views/livewire/identidad/turnos-asignaciones-panel.blade.php](../../../resources/views/livewire/identidad/turnos-asignaciones-panel.blade.php)

## 23. Livewire / Controllers

[app/Frontend/Livewire/Administracion/Identidad/TurnosAsignacionesPanel.php](../../../app/Frontend/Livewire/Administracion/Identidad/TurnosAsignacionesPanel.php), [app/Http/Controllers/Identidad/InstitucionalController.php](../../../app/Http/Controllers/Identidad/InstitucionalController.php), [app/Http/Controllers/Cuidados/CuidadoController.php](../../../app/Http/Controllers/Cuidados/CuidadoController.php)

## 24. Actions

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 25. Services

[app/Backend/Modulos/Enfermeria/Servicios/TurnoEnfermeriaService.php](../../../app/Backend/Modulos/Enfermeria/Servicios/TurnoEnfermeriaService.php), [app/Backend/Modulos/Enfermeria/Servicios/MiTurnoService.php](../../../app/Backend/Modulos/Enfermeria/Servicios/MiTurnoService.php)

## 26. Policies

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 27. Models

[Turno](../../../app/Models/Turno.php), [Jornada](../../../app/Models/Jornada.php), [Personal](../../../app/Models/Personal.php), [Area](../../../app/Models/Area.php), [AsignacionPersonal](../../../app/Models/AsignacionPersonal.php), [AsignacionResidenteJornada](../../../app/Models/AsignacionResidenteJornada.php)

## 28. Eventos / Listeners / Jobs

NONE OBSERVED Event/Listener Laravel o Job propio necesario para este recorrido. EventoAlerta, cuando aparece, es registro Eloquent; dispatch Livewire comunica UI y no prueba persistencia.

## 29. Errores

Validación esperada debe dar mensaje accionable; error inesperado debe reportarse con respuesta segura (APPROVED CONTRACT AGENTS). Negativos/rollback ejecutados se acreditan solo por método/gate Fase 3 en TRAZABILIDAD; distinguir validación/403/409 de fallo técnico.

## 30. Efectos secundarios permitidos

Crear contexto laboral/asistencial autorizado y anular relación anterior cuando corresponde.

## 31. Efectos secundarios prohibidos

Fabricar personal/jornada, usar primera fila FK, derivar permiso clínico de plaza o autoasignar residentes.

## 32. Tests existentes

**TESTED EXPECTATION:** TEST-JOR-001, TEST-IDE-001. Métodos exactos y alcance en [cobertura estática](../../../docs/TRAZABILIDAD.md); DEFINED / STATICALLY_MAPPED en Fase 2; resultados ejecutados Fase 3 por método/gate en TRAZABILIDAD.

## 33. Tests faltantes

Fase 3 acredita negativos de turno fuera de horario, residente inactivo y ARJ de jornada ajena en las entradas ejercitadas. Siguen pendientes ventana nocturna y permisos universales por todas las entradas, cierre integral y efecto en próximos actos, y carreras ARJ.

## 34. Deuda técnica

TECH-005 capa física/carreras; TECH-009 controles distintos en consumidores de contexto. [Registro TECH](../../../docs/DEUDA_TECNICA.md). Las correcciones Fase 3 y su evidencia acotada se detallan en TRAZABILIDAD; no se certifica todo el producto.

## 35. Decisiones abiertas

DEC-OPEN-001 solo estados institucionales dependientes; no se abre decisión nueva para elección técnica. [Registro DEC](../../../docs/DECISIONES_PENDIENTES.md). No se aprueba opción ni se duplica identificador.

## 36. Trazabilidad

REQ-JOR-001; RULE-JOR-001; AUTH-JOR-001; FLOW-JOR-001; INV-IDE-001, INV-JOR-001; TEST-JOR-001, TEST-IDE-001. [Mapeo por clase/test](../../../docs/TRAZABILIDAD.md) y [invariantes](../../../docs/sistema/INVARIANTES_NEGOCIO.md).

## Análisis de gaps por módulo

| Categoría | Severidad / evidencia | Acción futura |
|---|---|---|
| CONTRACT GAP | MEDIUM — límites de estados/contexto según decisiones ya enlazadas; no regla nueva | Resolver solo decisión aplicable antes de cambiar significado |
| IMPLEMENTATION GAP | MEDIUM — entradas distintas descritas en §11/15; conformidad integral NOT VERIFIED | Contrastar variantes contra contrato; no normalizar por estética |
| AUTHORIZATION GAP | MEDIUM — controles por entrada no intercambiables; no se declara vulnerabilidad global | Negativos por cada entrada y scope, conforme §15 |
| TEST GAP | HIGH — §33; definición de tests no acredita cobertura completa ni runtime | Verificar casos faltantes en entorno aislado de Fase 3 |
| DOCUMENTATION GAP | LOW — contrato creado y trazabilidad localizada; detalles no investigados se marcan NOT VERIFIED | Mantener símbolos/fuentes y resultados ejecutados separados |
| CONTINUITY GAP | MEDIUM — integración posterior no ejecutada; no se inventa bandeja | Comparar fuentes y proyecciones autorizadas |
