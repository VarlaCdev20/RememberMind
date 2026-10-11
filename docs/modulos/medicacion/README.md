---
title: "Contrato de módulo — Medicación"
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
related_modules: [medicacion]
---

# Medicación

Compilación vigente, no aprobación de reglas nuevas. **APPROVED CONTRACT**: [AGENTS](../../../AGENTS.md), [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Descripciones de entradas, estados, clases y efectos son **OBSERVED IMPLEMENTATION**, sin certificar cumplimiento en todas las variantes. **CONFLICT** y **TECHNICAL DEBT** señalan discrepancias; **OPEN DECISION** no autoriza cambiar contrato.

Fase 2 fue una revisión estática. Fase 3 ejecutó verificación acotada en PostgreSQL aislado; resultados y límites en [trazabilidad](../../TRAZABILIDAD.md). runtime_verified: false no certifica globalmente el módulo; verified_against_commit identifica la base y no un commit de estos cambios.

## 1. Propósito

Separar orden médica, horarios y administración/omisión asistencial.

## 2. Alcance

Catálogo, prescripciones, horarios, agenda derivada, administración programada/PRN y suspensión.

## 3. Fuera de alcance

Fase 3 endurece contratos existentes con cambios acotados y pruebas. No cambia BDD, reglas institucionales, competencias ni catálogos; tampoco entrega motor experto o certificación global.

## 4. Actores

Médico prescribe/suspende; Enfermería administra/documenta; otros roles lectura permitida.

## 5. Competencias

Enfermería no prescribe ni edita orden. Admin/Gerente/SA no competencia clínica por rol; excepción temporal aparte.

## 6. Precondiciones

Orden/medicamento activos, atención del mismo residente; administración requiere autor y jornada propios/residente asignado.

## 7. Entidades y tablas

medicamentos, prescripciones, horarios_prescripcion, administraciones_medicacion, atenciones, personal, jornadas, asignaciones_residente_jornada.

## 8. Relaciones

Orden del residente/médico/atención; horario de esa orden; administración mismo residente/prescripción y horario si programada.

## 9. Estados

Prescripción ACTIVA/ACTIVO → SUSPENDIDA con motivo. Service administración estado REGISTRADA, resultado ADMINISTRADA/OMITIDA; Model default FINALIZADO. Agenda PENDIENTE/PROXIMA/VENCIDA son derivados.

## 10. Caso de uso principal

Médico autoriza y crea orden+horarios. Enfermería registra programada desde agenda o PRN con motivo/valoración; no altera orden.

## 11. Flujos alternativos

PRN sin horario; omisión con motivo; suspensión exige orden activa y profesional. Reintento/identidad PRN pendiente DEC-OPEN-004.

## 12. Validaciones técnicas

Dosis numérica/forma de entrada, horarios requeridos si no PRN, atención del residente, horario de orden activa, duplicado programado por día; PRN intensidad 0..10 y motivo.

## 13. Reglas de negocio

Administrar mismo residente/orden/horario; no atribuir a otro personal; no registrar dosis ficticia para satisfacer FK.

## 14. Reglas clínicas aprobadas

No se define frecuencia/dosis clínica nueva; orden médica gobierna. Falla validación no es motivo para inventar administración.

## 15. Autorización

PrescripcionPolicy + AutorizacionClinicaService para orden; HTTP administración solo Enfermería y Service contextual. AdministracionMedicacionPolicy existe pero no es llamado por ese HTTP y contempla Médico: no mezclar entradas.

## 16. Transaction boundary

prescribir transaction orden/horarios; registrarProgramada transaction/locks; registrarPrn create sin transaction propia ni reintento seguro acreditado.

## 17. Persistencia

Cada administración es evento con resultado, fecha programada/real, autor/jornada; horario NULL solo PRN coherente.

## 18. Longitudinalidad

No sobrescribir administraciones ni borrar orden para ocultar error; suspensión conserva historia.

## 19. Alertas

Agenda recordatorios no equivale a alerta persistida. Creación automática por toda omisión/PRN NOT VERIFIED; no inventar regla.

## 20. Auditoría

Suspensión conserva autor/motivo/fecha; Activitylog universal de administración NOT VERIFIED.

## 21. Continuidad asistencial

Agenda deriva ausencia de registro; Pase filtra estado en vez de resultado, gap TECH-008.

## 22. Entrada UI

[resources/views/livewire/medicacion/salud-administracion-medicacion.blade.php](../../../resources/views/livewire/medicacion/salud-administracion-medicacion.blade.php)

## 23. Livewire / Controllers

[app/Http/Controllers/Medicacion/MedicacionController.php](../../../app/Http/Controllers/Medicacion/MedicacionController.php), [app/Frontend/Livewire/Medico/Medicacion/SaludMedicacionPanel.php](../../../app/Frontend/Livewire/Medico/Medicacion/SaludMedicacionPanel.php), [app/Frontend/Livewire/Enfermeria/Medicacion/SaludAdministracionMedicacionPanel.php](../../../app/Frontend/Livewire/Enfermeria/Medicacion/SaludAdministracionMedicacionPanel.php)

## 24. Actions

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 25. Services

[app/Backend/Modulos/Medicacion/Servicios/AgendaMedicacionService.php](../../../app/Backend/Modulos/Medicacion/Servicios/AgendaMedicacionService.php), [app/Backend/Modulos/Medicacion/Servicios/RegistrarAdministracionMedicacionService.php](../../../app/Backend/Modulos/Medicacion/Servicios/RegistrarAdministracionMedicacionService.php), [app/Backend/Modulos/Clinica/Servicios/AutorizacionClinicaService.php](../../../app/Backend/Modulos/Clinica/Servicios/AutorizacionClinicaService.php)

## 26. Policies

[app/Policies/PrescripcionPolicy.php](../../../app/Policies/PrescripcionPolicy.php), [app/Policies/AdministracionMedicacionPolicy.php](../../../app/Policies/AdministracionMedicacionPolicy.php)

## 27. Models

[Medicamento](../../../app/Models/Medicamento.php), [Prescripcion](../../../app/Models/Prescripcion.php), [HorarioPrescripcion](../../../app/Models/HorarioPrescripcion.php), [AdministracionMedicacion](../../../app/Models/AdministracionMedicacion.php)

## 28. Eventos / Listeners / Jobs

NONE OBSERVED Event/Listener Laravel o Job propio necesario para este recorrido. EventoAlerta, cuando aparece, es registro Eloquent; dispatch Livewire comunica UI y no prueba persistencia.

## 29. Errores

Validación esperada debe dar mensaje accionable; error inesperado debe reportarse con respuesta segura (APPROVED CONTRACT AGENTS). Negativos/rollback ejecutados se acreditan solo por método/gate Fase 3 en TRAZABILIDAD; distinguir validación/403/409 de fallo técnico.

## 30. Efectos secundarios permitidos

Nueva orden/horarios o evento asistencial; suspensión médica explícita; alertas solo conforme regla aprobada.

## 31. Efectos secundarios prohibidos

Enfermería prescribe, modifica orden o atribuye administración; duplicar programada; convertir pendiente derivado en fila ficticia.

## 32. Tests existentes

**TESTED EXPECTATION:** TEST-MED-001, TEST-MED-002, TEST-AUTH-001. Métodos exactos y alcance en [cobertura estática](../../../docs/TRAZABILIDAD.md); DEFINED / STATICALLY_MAPPED en Fase 2; resultados ejecutados Fase 3 por método/gate en TRAZABILIDAD.

## 33. Tests faltantes

Fase 3 acredita lector de continuidad contra escritor real, separando estado/resultado y agenda sin inserciones. Siguen pendientes reintento PRN según decisión, carrera real de administración y rollback programada por todas las entradas.

## 34. Deuda técnica

TECH-005 físicas/concurrencia; TECH-008 proyección de continuidad; no ausencia de Action como deuda artificial. [Registro TECH](../../../docs/DEUDA_TECNICA.md). Las correcciones Fase 3 y su evidencia acotada se detallan en TRAZABILIDAD; no se certifica todo el producto.

## 35. Decisiones abiertas

DEC-OPEN-004 PRN/reintentos; DEC-OPEN-002 solo catálogo aplicable. [Registro DEC](../../../docs/DECISIONES_PENDIENTES.md). No se aprueba opción ni se duplica identificador.

## 36. Trazabilidad

REQ-MED-001; RULE-MED-001; AUTH-MED-001; FLOW-MED-001; INV-MED-001, INV-MED-002, INV-CLI-001; TEST-MED-001, TEST-MED-002, TEST-AUTH-001. [Mapeo por clase/test](../../../docs/TRAZABILIDAD.md) y [invariantes](../../../docs/sistema/INVARIANTES_NEGOCIO.md).

## Análisis de gaps por módulo

| Categoría | Severidad / evidencia | Acción futura |
|---|---|---|
| CONTRACT GAP | HIGH — identidad/reintento PRN DEC-OPEN-004 | Resolver solo decisión aplicable antes de cambiar significado |
| IMPLEMENTATION GAP | HIGH — proyección/eventos no equivalentes TECH-002/008 según módulo | Contrastar variantes contra contrato; no normalizar por estética |
| AUTHORIZATION GAP | MEDIUM — controles por entrada no intercambiables; no se declara vulnerabilidad global | Negativos por cada entrada y scope, conforme §15 |
| TEST GAP | HIGH — §33; definición de tests no acredita cobertura completa ni runtime | Verificar casos faltantes en entorno aislado de Fase 3 |
| DOCUMENTATION GAP | LOW — contrato creado y trazabilidad localizada; detalles no investigados se marcan NOT VERIFIED | Mantener símbolos/fuentes y resultados ejecutados separados |
| CONTINUITY GAP | HIGH — TECH-008 | Comparar fuentes y proyecciones autorizadas |

## Evidencia Fase 3 — lote D

TECH-008 / FLOW-PAS-001 / INV-MED-001 / INV-ALT-001: PaseTurnoService proyecta administraciones mediante resultado y estado del registro, distingue omisión documentada de ausencia, y deriva pendientes/vencidas desde AgendaMedicacionService. No crea administraciones ficticias ni una tabla de pendientes. Incluye cuidados EJECUTADA y REALIZADA, pendientes previos aún abiertos y alertas RECONOCIDA/ASIGNADA; conserva incidentes, heridas e historia reciente en sus fuentes. Resumen manual del pase conserva su semántica. Último signo del contexto excluye anulados/rectificados.

PostgreSQL aislado: 46 tests / 203 aserciones PASS, 13.310 s (PaseTurnoReconstruidoTest, AgendaMedicacionTest, MiTurnoServiceTest). Dos casos de proyección dieron RED antes de corregir; escritor real de administración registra ADMINISTRADA/OMITIDA con REGISTRADA y la lectura no cambia sus filas. Semántica global de estados DEC-OPEN-001/002 y PRN/reintentos DEC-OPEN-004 permanecen abiertas. runtime_verified: false no certifica todo el módulo; resultados acotados en [trazabilidad](../../TRAZABILIDAD.md). Sin commit.
