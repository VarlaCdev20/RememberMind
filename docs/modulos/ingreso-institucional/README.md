---
title: "Contrato de módulo — Ingreso institucional"
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
related_modules: [ingreso-institucional]
---

# Ingreso institucional

Compilación vigente, no aprobación de reglas nuevas. **APPROVED CONTRACT**: [AGENTS](../../../AGENTS.md), [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Descripciones de entradas, estados, clases y efectos son **OBSERVED IMPLEMENTATION**, sin certificar cumplimiento en todas las variantes. **CONFLICT** y **TECHNICAL DEBT** señalan discrepancias; **OPEN DECISION** no autoriza cambiar contrato.

Fase 2 fue una revisión estática. Fase 3 ejecutó verificación acotada en PostgreSQL aislado; resultados y límites en [trazabilidad](../../TRAZABILIDAD.md). runtime_verified: false no certifica globalmente el módulo; verified_against_commit identifica la base y no un commit de estos cambios.

## 1. Propósito

Revisar postulantes y formalizar admisión con cama sin convertir aprobación en creación de residente.

## 2. Alcance

Preadmisión, revisión, formalización, vínculos/consentimiento y ocupación inicial.

## 3. Fuera de alcance

Fase 3 endurece contratos existentes con cambios acotados y pruebas. No cambia BDD, reglas institucionales, competencias ni catálogos; tampoco entrega motor experto o certificación global.

## 4. Actores

Administración autorizada; revisión médica según entrada observada. Médico sin grants adicionales no tiene acceso equivalente al panel/HTTP.

## 5. Competencias

Decisión institucional de revisión separada del acto formal de ingreso; no competencia clínica automática de roles administrativos.

## 6. Precondiciones

Preadmisión APROBADA, sin admisión previa; cama apta sin ocupación activa; contacto/consentimiento válidos.

## 7. Entidades y tablas

preadmisiones, admisiones, residentes, contactos, residentes_contactos, camas, ocupaciones_cama, historial_estados_residente, consentimientos, documentos y seguros_residente opcional.

## 8. Relaciones

Admisión enlaza preadmisión/residente; ocupación enlaza cama/admisión; consentimiento firma residente XOR contacto vinculado.

## 9. Estados

HTTP PENDIENTE → APROBADA/RECHAZADA; Action escribe preadmisión ADMITIDA y residente ADMITIDO. CONFLICT con valores físicos ACTIVO/catálogo rector, DEC-OPEN-001.

## 10. Caso de uso principal

Revisar sin crear residente. FormalizarAdmision::ejecutar bloquea solicitud/cama y crea toda la operación.

## 11. Flujos alternativos

Rechazo con motivo; solicitud ya formalizada/otra cama ocupada se rechaza. DecisionAdmisionModal sobre residente existente es entrada heredada conflictiva, no vía canónica.

## 12. Validaciones técnicas

exists, nombres/contacto requeridos según camino, cama válida y fecha de nacimiento/formato en panel; Action revalida aprobación/ocupación.

## 13. Reglas de negocio

Aprobar no crea residente; no CRUD directo de creación; admisión con cama y operación atómica.

## 14. Reglas clínicas aprobadas

No reglas de severidad ni diagnóstico nuevas; valoración de preadmisión sigue su contrato vigente.

## 15. Autorización

Middleware y panel requieren preadmisiones.revisar/admisiones.formalizar y roles correspondientes, con cuenta activa. FormalizarAdmision revalida cuenta, rol administrativo autorizado y permiso formalizar antes de consultar/mutar. No se inventa Policy vacía. Evidencia A/E en trazabilidad.

## 16. Transaction boundary

FormalizarAdmision transaction(...,3) con locks preadmisión/cama; auditoría dentro. Fase3AdmisionAtomicidadTest acredita rollback en nueve puntos, incluidas creación de contacto, seguro y auditoría. Fase3ConcurrenciaCamaPostgresTest acredita dos procesos independientes realmente bloqueados sobre la misma cama: una admisión y una denegación, sin huérfanos. Es evidencia de esta Action/entorno, no de toda operación institucional.

## 17. Persistencia

Nuevo residente, admisión, relación, ocupación, historial y consentimiento; crea contacto solo cuando datos válidos lo requieren; no fallback arbitrario.

## 18. Longitudinalidad

Historial de estado y solicitud se conservan; no borrar residente o clínica al rechazar.

## 19. Alertas

NONE OBSERVED en FormalizarAdmision; no se añade alerta de ingreso ficticia.

## 20. Auditoría

activity('Admisiones') con autor y residente; historial institucional no se sustituye por log.

## 21. Continuidad asistencial

Residente y ocupación habilitan contexto posterior; no se crean jornadas/asignaciones/cuidados automáticamente por aprobación.

## 22. Entrada UI

[resources/views/livewire/admisiones/preadmisiones-panel.blade.php](../../../resources/views/livewire/admisiones/preadmisiones-panel.blade.php)

## 23. Livewire / Controllers

[app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php](../../../app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php), [app/Http/Controllers/Admisiones/PreadmisionController.php](../../../app/Http/Controllers/Admisiones/PreadmisionController.php), [app/Http/Controllers/Admisiones/AdmisionController.php](../../../app/Http/Controllers/Admisiones/AdmisionController.php), [app/Frontend/Livewire/Admisiones/DecisionAdmisionModal.php](../../../app/Frontend/Livewire/Admisiones/DecisionAdmisionModal.php)

## 24. Actions

[app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php](../../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php)

## 25. Services

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 26. Policies

[app/Policies/ResidentePolicy.php](../../../app/Policies/ResidentePolicy.php) ResidentePolicy protege lectura/niega CRUD; no es Policy de formalización.

## 27. Models

[Preadmision](../../../app/Models/Preadmision.php), [Admision](../../../app/Models/Admision.php), [Residente](../../../app/Models/Residente.php), [Contacto](../../../app/Models/Contacto.php), [ResidenteContacto](../../../app/Models/ResidenteContacto.php), [OcupacionCama](../../../app/Models/OcupacionCama.php), [HistorialEstadoResidente](../../../app/Models/HistorialEstadoResidente.php), [Consentimiento](../../../app/Models/Consentimiento.php)

## 28. Eventos / Listeners / Jobs

NONE OBSERVED Event/Listener Laravel o Job propio necesario para este recorrido. EventoAlerta, cuando aparece, es registro Eloquent; dispatch Livewire comunica UI y no prueba persistencia.

## 29. Errores

Validación esperada debe dar mensaje accionable; error inesperado debe reportarse con respuesta segura (APPROVED CONTRACT AGENTS). CONFLICT: captura con getMessage en entrada heredada, TECH-010.

## 30. Efectos secundarios permitidos

Crear exactamente relaciones institucionales previstas y seguro opcional; actividad aprobada.

## 31. Efectos secundarios prohibidos

Crear residente al aprobar, asignar cama ocupada, reutilizar residente/cama activos, saltar ingreso mediante CRUD o definir nuevos estados.

## 32. Tests existentes

**TESTED EXPECTATION:** TEST-ADM-001, TEST-PRE-001. Métodos exactos y alcance en [cobertura estática](../../../docs/TRAZABILIDAD.md); DEFINED / STATICALLY_MAPPED en Fase 2; resultados ejecutados Fase 3 por método/gate en TRAZABILIDAD.

## 33. Tests faltantes

Fase 2 identificó faltantes de rollback/carrera/permisos/contacto. Fase 3 acredita nueve puntos de rollback, carrera real de la misma cama y consentimiento de contacto ajeno rechazado por Model. Sigue pendiente certificar equivalencia completa de todas las entradas panel/HTTP y SQL directo de contacto.

## 34. Deuda técnica

TECH-005 (garantías físicas), TECH-006 (documentación/entrada heredada), TECH-009 (autorización por entrada). [Registro TECH](../../../docs/DEUDA_TECNICA.md). Las correcciones Fase 3 y su evidencia acotada se detallan en TRAZABILIDAD; no se certifica todo el producto.

## 35. Decisiones abiertas

DEC-OPEN-001 estados institucionales; DEC-OPEN-002 solo catálogos dependientes. [Registro DEC](../../../docs/DECISIONES_PENDIENTES.md). No se aprueba opción ni se duplica identificador.

## 36. Trazabilidad

REQ-ADM-001; RULE-ADM-001; AUTH-ADM-001; FLOW-ADM-001; INV-ADM-001, INV-ADM-002, INV-CON-001; TEST-ADM-001, TEST-PRE-001. [Mapeo por clase/test](../../../docs/TRAZABILIDAD.md) y [invariantes](../../../docs/sistema/INVARIANTES_NEGOCIO.md).

## Análisis de gaps por módulo

| Categoría | Severidad / evidencia | Acción futura |
|---|---|---|
| CONTRACT GAP | HIGH — valores físicos/transiciones DEC-OPEN-001 | Resolver solo decisión aplicable antes de cambiar significado |
| IMPLEMENTATION GAP | MEDIUM — valores y entrada heredada según §9/11 | Contrastar variantes contra contrato; no normalizar por estética |
| AUTHORIZATION GAP | HIGH — TECH-009 permisos/contexto por entrada | Negativos por cada entrada y scope, conforme §15 |
| TEST GAP | HIGH — §33; definición de tests no acredita cobertura completa ni runtime | Verificar casos faltantes en entorno aislado de Fase 3 |
| DOCUMENTATION GAP | LOW — contrato creado y trazabilidad localizada; detalles no investigados se marcan NOT VERIFIED | Mantener símbolos/fuentes y resultados ejecutados separados |
| CONTINUITY GAP | MEDIUM — integración posterior no ejecutada; no se inventa bandeja | Comparar fuentes y proyecciones autorizadas |
