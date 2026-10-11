---
title: "Contrato de módulo — Pase de turno y continuidad"
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
related_modules: [pase-turno]
---

# Pase de turno y continuidad

Compilación vigente, no aprobación de reglas nuevas. **APPROVED CONTRACT**: [AGENTS](../../../AGENTS.md), [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Descripciones de entradas, estados, clases y efectos son **OBSERVED IMPLEMENTATION**, sin certificar cumplimiento en todas las variantes. **CONFLICT** y **TECHNICAL DEBT** señalan discrepancias; **OPEN DECISION** no autoriza cambiar contrato.

Fase 2 fue una revisión estática. Fase 3 ejecutó verificación acotada en PostgreSQL aislado; resultados y límites en [trazabilidad](../../TRAZABILIDAD.md). runtime_verified: false no certifica globalmente el módulo; verified_against_commit identifica la base y no un commit de estos cambios.

## 1. Propósito

Transferir información relevante del residente entre jornadas y profesionales autorizados.

## 2. Alcance

Contexto clínico, borrador/entrega/recepción, resumen/pendientes/vigilancia/recomendación y anulación observada.

## 3. Fuera de alcance

Fase 3 endurece contratos existentes con cambios acotados y pruebas. No cambia BDD, reglas institucionales, competencias ni catálogos; tampoco entrega motor experto o certificación global.

## 4. Actores

Enfermería saliente/entrante por residente/jornada; lectura administrativa/global según permisos y recurso.

## 5. Competencias

Entregar/recibir requiere relación/contexto; permiso de lectura en mount no autoriza mutaciones.

## 6. Precondiciones

Jornada saliente propia, entrante existente/planificada, residente asignado y resumen al entregar. generar requiere otro enfermero activo como receptor; procesarPase permite receptor previo null. Para recibir se exige designación o asignación entrante legítima.

## 7. Entidades y tablas

pases_turno, jornadas, turnos, asignaciones_personal, asignaciones_residente_jornada; fuentes clínicas signos, dolor, medicación, planes/ejecuciones, incidentes, heridas y alertas.

## 8. Relaciones

Pase del residente con dos jornadas y personal saliente/entrante; recepción timestamp/observación; no tabla de pendientes nueva.

## 9. Estados

Panel BORRADOR → ENTREGADO → RECIBIDO, ANULADO lógico; generar escribe GENERADO; HTTP EMITIDO; Model acepta aliases para recepción. No catálogo uniforme aprobado por leer compatibilidad.

## 10. Caso de uso principal

Preparar desde hechos/pendientes/alertas; guardar borrador; entregar bloquea edición; quien recibe debe estar designado o asignado en la jornada entrante y confirma una vez. El panel puede entregar sin personal entrante previamente resuelto.

## 11. Flujos alternativos

generar conserva GENERADO y pendientes serializados en TEXT; revalida relevo y receptor activo/ARJ entrante. HTTP conserva EMITIDO y exige jornadas del relevo mediante Service. anularPase revalida emisor propio, permiso editar y asignación saliente; conserva observacion_recepcion y registra motivo solo en auditoría. Los distintos estados no se normalizan sin DEC-OPEN-002.

## 12. Validaciones técnicas

Resumen mínimo en confirmarEntrega y jornadas existentes; receptor obligatorio/validado en generar, opcional al procesarPase y autorizado por designación/asignación al recibir. Doble recepción/edición tras entrega rechazadas en Service. Métodos no equivalentes.

## 13. Reglas de negocio

Conservar fuente/fecha/autor/contexto; no fabricar jornada entrante ni perder pendientes; no redefinir resultados clínicos por lector.

## 14. Reglas clínicas aprobadas

estado_general/resumen textual no constituye clasificación Estable/Vigilancia/Riesgo aprobada; DEC-OPEN-003.

## 15. Autorización

Service revalida cuenta/personal activos, permisos crear/editar según operación, residente admitido y turno vigente para procesar. ARJ debe corresponder a jornada saliente exacta; recibir admite designación/asignación legítima entrante, incluida PLANIFICADA, sin imponer turno saliente al receptor. Lectura del panel reautoriza residente/pase. Negativos acotados ejecutados en PaseTurnoReconstruidoTest; no certificación de todas las entradas históricas.

## 16. Transaction boundary

procesarPase, confirmarRecepcion y anularPase usan transacción/lock. generar y HTTP siguen siendo creación de una fila con guardias de contexto; no se acredita mismo log o concurrencia de pases. Carrera real ejecutada en Fase 3 corresponde a admisión/cama, no a todo módulo.

## 17. Persistencia

Campos TEXT con resumen/pendientes; versión generar serializa JSON como compatibilidad existente, no diseño EAV propuesto. Entrega/recepción con fecha.

## 18. Longitudinalidad

Fila, autor, fechas y observación de recepción se conservan. El ciclo panel no admite edición tras entrega/recepción; anulación lógica exige emisor autorizado y no sobrescribe observación de recepción.

## 19. Alertas

Lectura de alertas activas; no cerrar/crear alerta automáticamente por entregar o recibir; eventos siguen en fuente.

## 20. Auditoría

activity en procesar/recibir/anular; no toda entrada HTTP/generar acredita mismo log.

## 21. Continuidad asistencial

Hecho histórico != pendiente != alerta != acción futura != resumen. Fase 3 corrige proyección de resultado/estado de medicación, cuidado EJECUTADA/REALIZADA y alertas RECONOCIDA/ASIGNADA; deriva agenda sin crear hechos. Medicamento se proyecta como nombre legible y no Model/JSON; test RED/GREEN. Conserva pendientes anteriores e historia pertinente.

## 22. Entrada UI

[resources/views/livewire/cuidados/pase-turno-panel.blade.php](../../../resources/views/livewire/cuidados/pase-turno-panel.blade.php)

## 23. Livewire / Controllers

[app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php](../../../app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php), [app/Http/Controllers/Cuidados/CuidadoController.php](../../../app/Http/Controllers/Cuidados/CuidadoController.php)

## 24. Actions

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 25. Services

[app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php](../../../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php), [app/Backend/Modulos/Enfermeria/Servicios/MiTurnoService.php](../../../app/Backend/Modulos/Enfermeria/Servicios/MiTurnoService.php), [app/Backend/Modulos/Enfermeria/Servicios/TurnoEnfermeriaService.php](../../../app/Backend/Modulos/Enfermeria/Servicios/TurnoEnfermeriaService.php)

## 26. Policies

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 27. Models

[PaseTurno](../../../app/Models/PaseTurno.php), [Jornada](../../../app/Models/Jornada.php), [AsignacionPersonal](../../../app/Models/AsignacionPersonal.php), [AsignacionResidenteJornada](../../../app/Models/AsignacionResidenteJornada.php)

## 28. Eventos / Listeners / Jobs

NONE OBSERVED Event/Listener Laravel o Job propio necesario para este recorrido. EventoAlerta, cuando aparece, es registro Eloquent; dispatch Livewire comunica UI y no prueba persistencia.

## 29. Errores

Validación esperada debe dar mensaje accionable; error inesperado debe reportarse con respuesta segura (APPROVED CONTRACT AGENTS). Negativos/rollback ejecutados se acreditan solo por método/gate Fase 3 en TRAZABILIDAD; distinguir validación/403/409 de fallo técnico.

## 30. Efectos secundarios permitidos

Guardar resumen/transición/recepción y auditoría; consultar fuentes permitidas.

## 31. Efectos secundarios prohibidos

Fabricar jornada/receptor, entregar residente fuera de scope, modificar clínica fuente, borrar historia o fingir todos los pendientes resueltos.

## 32. Tests existentes

**TESTED EXPECTATION:** TEST-PAS-001, TEST-PAS-002, TEST-PAS-003. Métodos exactos y alcance en [cobertura estática](../../../docs/TRAZABILIDAD.md); DEFINED / STATICALLY_MAPPED en Fase 2; resultados ejecutados Fase 3 por método/gate en TRAZABILIDAD.

## 33. Tests faltantes

Fase 2 identificó faltantes de permisos/cuenta/scope/anulación/proyección/carrera. Fase 3 acredita negativos de permiso, cuenta/receptor inactivos, ARJ/jornada/residente ajenos, turno inválido, anulación ajena y proyección fiel. Permanece pendiente una carrera real de duplicados de pase y cobertura de todas las revocaciones por cada mutación.

## 34. Deuda técnica

TECH-008 continuidad/proyección; TECH-009 autorización/entradas; TECH-005 garantías físicas/carreras. [Registro TECH](../../../docs/DEUDA_TECNICA.md). Las correcciones Fase 3 y su evidencia acotada se detallan en TRAZABILIDAD; no se certifica todo el producto.

## 35. Decisiones abiertas

DEC-OPEN-003 etiquetas globales, DEC-OPEN-002 catálogo cuando aplicable; no decisión nueva para corregir permiso faltante. [Registro DEC](../../../docs/DECISIONES_PENDIENTES.md). No se aprueba opción ni se duplica identificador.

## 36. Trazabilidad

REQ-PAS-001; RULE-PAS-001; AUTH-PAS-001; FLOW-PAS-001; INV-PAS-001, INV-CLI-003; TEST-PAS-001, TEST-PAS-002, TEST-PAS-003. [Mapeo por clase/test](../../../docs/TRAZABILIDAD.md) y [invariantes](../../../docs/sistema/INVARIANTES_NEGOCIO.md).

## Análisis de gaps por módulo

| Categoría | Severidad / evidencia | Acción futura |
|---|---|---|
| CONTRACT GAP | MEDIUM — límites de estados/contexto según decisiones ya enlazadas; no regla nueva | Resolver solo decisión aplicable antes de cambiar significado |
| IMPLEMENTATION GAP | HIGH — proyección/eventos no equivalentes TECH-002/008 según módulo | Contrastar variantes contra contrato; no normalizar por estética |
| AUTHORIZATION GAP | HIGH — TECH-009 permisos/contexto por entrada | Negativos por cada entrada y scope, conforme §15 |
| TEST GAP | HIGH — §33; definición de tests no acredita cobertura completa ni runtime | Verificar casos faltantes en entorno aislado de Fase 3 |
| DOCUMENTATION GAP | LOW — contrato creado y trazabilidad localizada; detalles no investigados se marcan NOT VERIFIED | Mantener símbolos/fuentes y resultados ejecutados separados |
| CONTINUITY GAP | HIGH — TECH-008 | Comparar fuentes y proyecciones autorizadas |

## Evidencia Fase 3 — lote D

TECH-008 / FLOW-PAS-001 / INV-MED-001 / INV-ALT-001: PaseTurnoService proyecta administraciones mediante resultado y estado del registro, distingue omisión documentada de ausencia, y deriva pendientes/vencidas desde AgendaMedicacionService. No crea administraciones ficticias ni una tabla de pendientes. Incluye cuidados EJECUTADA y REALIZADA, pendientes previos aún abiertos y alertas RECONOCIDA/ASIGNADA; conserva incidentes, heridas e historia reciente en sus fuentes. Resumen manual del pase conserva su semántica. Último signo del contexto excluye anulados/rectificados.

PostgreSQL aislado: 46 tests / 203 aserciones PASS, 13.310 s (PaseTurnoReconstruidoTest, AgendaMedicacionTest, MiTurnoServiceTest). Dos casos de proyección dieron RED antes de corregir; escritor real de administración registra ADMINISTRADA/OMITIDA con REGISTRADA y la lectura no cambia sus filas. Semántica global de estados DEC-OPEN-001/002 y PRN/reintentos DEC-OPEN-004 permanecen abiertas. runtime_verified: false no certifica todo el módulo; resultados acotados en [trazabilidad](../../TRAZABILIDAD.md). Sin commit.
