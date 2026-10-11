---
title: "Contrato de módulo — Alertas"
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
related_modules: [alertas]
---

# Alertas

Compilación vigente, no aprobación de reglas nuevas. **APPROVED CONTRACT**: [AGENTS](../../../AGENTS.md), [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Descripciones de entradas, estados, clases y efectos son **OBSERVED IMPLEMENTATION**, sin certificar cumplimiento en todas las variantes. **CONFLICT** y **TECHNICAL DEBT** señalan discrepancias; **OPEN DECISION** no autoriza cambiar contrato.

Fase 2 documentó evidencia estática. Fase 3 ejecutó pruebas acotadas en PostgreSQL aislado: [trazabilidad](../../TRAZABILIDAD.md). runtime_verified: false conserva ausencia de certificación global; verified_against_commit identifica la base, no un commit de estas correcciones.

## 1. Propósito

Conservar condición/origen y trayectoria de atención, sin confundir alerta, evento, notificación y auditoría.

## 2. Alcance

Detección/creación manual, responsable, reconocimiento/intervención/seguimiento/cierre por entradas observadas.

## 3. Fuera de alcance

Fase 3 corrige integridad y autorización con esquema y estados existentes. No entrega motor experto, catálogo aprobado nuevo ni certificación global.

## 4. Actores

Enfermería asignada gestiona cuidado; Administración dispone acciones específicas HTTP. Profesionales leen según permisos.

## 5. Competencias

Un permiso de lectura o rol administrativo no concede registrar un acto clínico; coordinación de alerta no es prescripción.

## 6. Precondiciones

Residente/contexto permitido; acción con permiso; estado admite transición; responsable activo y personal vinculado si asignación.

## 7. Entidades y tablas

alertas, eventos_alerta, residentes, personal, usuarios y signos_vitales cuando origen SIGNOS.

## 8. Relaciones

Alerta del residente con responsable personal; EventoAlerta autor usuario/fecha/acción; origen modulo+cod_registro no es FK universal polimórfica nueva.

## 9. Estados

Controller ABIERTA/RECONOCIDA/ASIGNADA/EN_ATENCION/ATENDIDA/CERRADA/ANULADA; Panel/Service mutables ABIERTA/EN_ATENCION. Catálogo completo pendiente DEC-OPEN-002.

## 10. Caso de uso principal

Crear/detectar condición; registrar origen y evento requerido; asignar/intervenir/seguir/cerrar con historia. Panel/Service y detector agregan CREACION en la transacción de creación; HTTP conserva CREADA. TECH-002 corregido con evidencia runtime acotada.

## 11. Flujos alternativos

Controller evento CREADA y CAMBIO_ESTADO; signos automático CREACION; Panel asigna responsable sin ASIGNADA, intervención EN_ATENCION. No imponer etapas no observadas.

## 12. Validaciones técnicas

Texto de motivo/acción/cierre requerido con longitud según entrada; tipo/origen/prioridad validators; estado/transición válida; no tratar prioridades como gravedad clínica universal.

## 13. Reglas de negocio

Alerta no pierde eventos; evento clínico no equivale a dispatch UI, correo ni Activitylog.

## 14. Reglas clínicas aprobadas

Solo reglas aprobadas por origen; signos crítico via evaluator. DeteccionAlertasService operativo no acredita motor experto cognitivo.

## 15. Autorización

Panel comprobarPermiso por acción o gestionar y scope según rol; HTTP Enfermería contexto o Admin permiso específico; Policy alerta NONE OBSERVED.

## 16. Transaction boundary

AlertasService concentra creación/cambio/evento en transacciones con lock para mutaciones. Panel delega coordinación conservando permisos específicos o gestionar; HTTP conserva su matriz de transición y Enfermería contextual. Detector usa actor autenticado y evento inicial dentro de su transacción. Lectura preventiva no crea alertas.

## 17. Persistencia

Alerta actual y eventos separados; responsables son personal, autores eventos usuarios; no crear otra tabla de auditoría.

## 18. Longitudinalidad

Seguimientos agregan eventos; cierre/anulación conserva alerta y trayectoria; no borrar historia.

## 19. Alertas

Este módulo es fuente persistida; repetición mismo evento no equivale automáticamente a nueva alerta. Idempotencia de todos los orígenes NOT VERIFIED.

## 20. Auditoría

EventoAlerta = acciones asistenciales; logs técnicos separados. Creación manual y cambios acreditados por registros/eventos y rollback en Fase3AlertasTest, no por mensajes UI.

## 21. Continuidad asistencial

Pase incluye RECONOCIDA/ASIGNADA además de las alertas activas ya leídas; TECH-008 corregido con pruebas de proyección. No cierra alerta para ocultar pendiente ni copia eventos a otra tabla.

## 22. Entrada UI

[resources/views/livewire/alertas/alertas-panel.blade.php](../../../resources/views/livewire/alertas/alertas-panel.blade.php)

## 23. Livewire / Controllers

[app/Frontend/Livewire/Compartido/Alertas/AlertasPanel.php](../../../app/Frontend/Livewire/Compartido/Alertas/AlertasPanel.php), [app/Http/Controllers/Alertas/AlertaController.php](../../../app/Http/Controllers/Alertas/AlertaController.php)

## 24. Actions

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 25. Services

[app/Backend/Modulos/Alertas/Servicios/AlertasService.php](../../../app/Backend/Modulos/Alertas/Servicios/AlertasService.php), [app/Backend/Modulos/Alertas/Servicios/DeteccionAlertasService.php](../../../app/Backend/Modulos/Alertas/Servicios/DeteccionAlertasService.php), [app/Backend/Modulos/Clinica/SignosVitales/ServicioDecisionAlertaClinica.php](../../../app/Backend/Modulos/Clinica/SignosVitales/ServicioDecisionAlertaClinica.php)

## 26. Policies

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 27. Models

[Alerta](../../../app/Models/Alerta.php), [EventoAlerta](../../../app/Models/EventoAlerta.php)

## 28. Eventos / Listeners / Jobs

NONE OBSERVED Event/Listener Laravel o Job propio necesario para este recorrido. EventoAlerta, cuando aparece, es registro Eloquent; dispatch Livewire comunica UI y no prueba persistencia.

## 29. Errores

Validación/403/409 son esperados y seguros; falla técnica no produce éxito. Creación/cambio de estado y evento comparten transacción y tienen fault injection en Fase3AlertasTest. No se certifican todas las fallas de infraestructura posibles.

## 30. Efectos secundarios permitidos

Estado/responsable y evento correspondiente por acción; nueva medición clínica permanece en su módulo.

## 31. Efectos secundarios prohibidos

Borrar evento/alerta, sustituir evento por log, preview genera alertas, crear catálogo/umbrales no aprobados.

## 32. Tests existentes

**TESTED EXPECTATION:** TEST-ALT-001, TEST-SV-001, TEST-SV-004. Métodos exactos y alcance en [cobertura estática](../../../docs/TRAZABILIDAD.md); DEFINED / STATICALLY_MAPPED en Fase 2; resultados ejecutados Fase 3 por método/gate en TRAZABILIDAD.

## 33. Tests faltantes

Fase 2 identificó faltantes de evento/rollback/permisos/reintentos/continuidad. Fase 3 acredita creación y cambios con evento atómico en las entradas ejercitadas, lectura contextual y continuidad de RECONOCIDA/ASIGNADA. Siguen pendientes cobertura universal de todas las entradas, carreras de todos los orígenes y reintentos dependientes de DEC-OPEN-004.

## 34. Deuda técnica

TECH-002 evento/entrada; TECH-008 proyección de estados activos; TECH-009 diferencias de autorización de consumidores. [Registro TECH](../../../docs/DEUDA_TECNICA.md). Fase 3 corrige TECH-002; el resto conserva su estado y alcance en el registro TECH.

## 35. Decisiones abiertas

DEC-OPEN-002 catálogos; DEC-OPEN-004 si reintento afecta identidad de evento; DEC-OPEN-003 no usar prioridad como estado clínico global. [Registro DEC](../../../docs/DECISIONES_PENDIENTES.md). No se aprueba opción ni se duplica identificador.

## 36. Trazabilidad

REQ-ALT-001; RULE-ALT-001; AUTH-ALT-001; FLOW-ALT-001; INV-ALT-001, INV-SV-001; TEST-ALT-001, TEST-SV-001, TEST-SV-004. [Mapeo por clase/test](../../../docs/TRAZABILIDAD.md) y [invariantes](../../../docs/sistema/INVARIANTES_NEGOCIO.md).

## Análisis de gaps por módulo

| Categoría | Severidad / evidencia | Acción futura |
|---|---|---|
| CONTRACT GAP | HIGH — catálogo DEC-OPEN-002 | Resolver solo decisión aplicable antes de cambiar significado |
| IMPLEMENTATION GAP | HIGH — proyección/eventos no equivalentes TECH-002/008 según módulo | Contrastar variantes contra contrato; no normalizar por estética |
| AUTHORIZATION GAP | MEDIUM — controles por entrada no intercambiables; no se declara vulnerabilidad global | Negativos por cada entrada y scope, conforme §15 |
| TEST GAP | HIGH — §33; definición de tests no acredita cobertura completa ni runtime | Verificar casos faltantes en entorno aislado de Fase 3 |
| DOCUMENTATION GAP | LOW — contrato creado y trazabilidad localizada; detalles no investigados se marcan NOT VERIFIED | Mantener símbolos/fuentes y resultados ejecutados separados |
| CONTINUITY GAP | HIGH — TECH-008 | Comparar fuentes y proyecciones autorizadas |

## Evidencia Fase 3 — lote C

TECH-002 / RULE-ALT-001 / INV-ALT-001 / AUTH-ALT-001: Fase3AlertasTest, AlertasFlujoTest y negativos/gestión de AlertasHistorialFeatureTest: PostgreSQL PASS, 24 tests / 109 aserciones, 38.614 s. RED válido: siete casos de evento inexistente, rollback, escritura durante render, cuenta inactiva y detección fuera de scope. No se acredita concurrencia de todos los orígenes. Permanece la expectativa visual preexistente de timeline fallida en suite global. Cierre crítico directo desde ABIERTA con resultado sigue permitido; no se inventa intervención obligatoria. Sin commit.
