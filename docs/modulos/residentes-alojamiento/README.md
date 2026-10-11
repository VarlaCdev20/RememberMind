---
title: "Contrato de módulo — Residentes y alojamiento"
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
related_modules: [residentes-alojamiento]
---

# Residentes y alojamiento

Compilación vigente, no aprobación de reglas nuevas. **APPROVED CONTRACT**: [AGENTS](../../../AGENTS.md), [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Descripciones de entradas, estados, clases y efectos son **OBSERVED IMPLEMENTATION**, sin certificar cumplimiento en todas las variantes. **CONFLICT** y **TECHNICAL DEBT** señalan discrepancias; **OPEN DECISION** no autoriza cambiar contrato.

Fase 2 fue una revisión estática. Fase 3 ejecutó verificación acotada en PostgreSQL aislado; resultados y límites en [trazabilidad](../../TRAZABILIDAD.md). runtime_verified: false no certifica globalmente el módulo; verified_against_commit identifica la base y no un commit de estos cambios.

## 1. Propósito

Mantener identidad residencial y alojamiento disponible respetando ingreso formal.

## 2. Alcance

Residente, habitación/cama, ocupación activa e historial; consultas de ficha.

## 3. Fuera de alcance

Fase 3 endurece contratos existentes con cambios acotados y pruebas. No cambia BDD, reglas institucionales, competencias ni catálogos; tampoco entrega motor experto o certificación global.

## 4. Actores

Administración en operación, Gerente/Superadmin lectura/planificación según permiso; profesionales/familiar leen por ámbito.

## 5. Competencias

Gestionar alojamiento no autoriza crear clínica ni publicar expediente familiar.

## 6. Precondiciones

Residente creado por admisión; cama/ habitación vigentes y sin ocupación incompatible.

## 7. Entidades y tablas

residentes, habitaciones, camas, ocupaciones_cama, admisiones, historial_estados_residente y residentes_contactos.

## 8. Relaciones

Habitación tiene camas; ocupación enlaza cama/residente/admisión y usuario registrador. Residente es CENTRAL DOMAIN ENTITY, no aggregate DDD de todo el sistema.

## 9. Estados

ACTIVA/ACTIVO para ocupación; disponibilidad Model DISPONIBLE, Action admite ACTIVA; panel DISPONIBLE/OCUPADA/MANTENIMIENTO/BLOQUEADA. CONFLICT de valores/entrada, DEC-OPEN-001.

## 10. Caso de uso principal

Consultar residente y alojamiento; ocupación inicial solo mediante FormalizarAdmision; guardias Model rechazan doble ocupación.

## 11. Flujos alternativos

Panel rechaza liberar etiqueta de cama ocupada o bloquear habitación con residentes. Traslado/liberación end-to-end: NOT VERIFIED en este contrato; no inventar Action.

## 12. Validaciones técnicas

Códigos/referencias existentes, estado correcto, coherencia de ocupación, no editar OCUPADA sin ocupación ni liberar con etiqueta.

## 13. Reglas de negocio

Una ocupación activa por cama y por residente; no creación directa de residente; no interpretar cama física como permiso de ingreso.

## 14. Reglas clínicas aprobadas

No se deriva estabilidad de tener cama o de carecer de alertas.

## 15. Autorización

ResidentePolicy::view/viewAny rechaza cuenta inactiva; create false. Familiar requiere permiso, contacto/vínculo ACTIVO y autoriza_informacion. Detalle familiar muestra solo identidad permitida; relaciones muestran únicamente visitas propias si tiene permiso. Se bloquean clínica/PDF/documentos del residente y listas globales no publicadas. DEC-OPEN-006 permanece abierta.

## 16. Transaction boundary

Admisión transaccional con locks y carrera PostgreSQL real acreditada para la misma cama. Panel guardarCama mantiene su boundary propio. La prueba de admisión no certifica todas las relocalizaciones ni cambia estados de disponibilidad DEC-OPEN-001.

## 17. Persistencia

Identidad permanece aunque cambie alojamiento; ocupación contiene fechas/usuario/admisión. Estado de cama no reemplaza ocupación.

## 18. Longitudinalidad

Historia de estado/ocupaciones y expediente conservados; no borrar clínica al trasladar/egresar.

## 19. Alertas

No se observa alerta clínica automática al editar cama en el segmento revisado.

## 20. Auditoría

Historial de residente en admisión; auditoría específica de toda relocalización NOT VERIFIED.

## 21. Continuidad asistencial

Ubicación alimenta directorio/ficha; lectura debe respetar ámbito y contenido.

## 22. Entrada UI

[resources/views/livewire/admisiones/habitaciones-panel.blade.php](../../../resources/views/livewire/admisiones/habitaciones-panel.blade.php)

## 23. Livewire / Controllers

[app/Frontend/Livewire/Admisiones/HabitacionesPanel.php](../../../app/Frontend/Livewire/Admisiones/HabitacionesPanel.php), [app/Frontend/Livewire/Compartido/Residentes/AdultosMayoresPanel.php](../../../app/Frontend/Livewire/Compartido/Residentes/AdultosMayoresPanel.php), [app/Http/Controllers/Residentes/ResidenteController.php](../../../app/Http/Controllers/Residentes/ResidenteController.php)

## 24. Actions

[app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php](../../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php)

## 25. Services

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 26. Policies

[app/Policies/ResidentePolicy.php](../../../app/Policies/ResidentePolicy.php)

## 27. Models

[Residente](../../../app/Models/Residente.php), [Habitacion](../../../app/Models/Habitacion.php), [Cama](../../../app/Models/Cama.php), [OcupacionCama](../../../app/Models/OcupacionCama.php), [HistorialEstadoResidente](../../../app/Models/HistorialEstadoResidente.php)

## 28. Eventos / Listeners / Jobs

NONE OBSERVED Event/Listener Laravel o Job propio necesario para este recorrido. EventoAlerta, cuando aparece, es registro Eloquent; dispatch Livewire comunica UI y no prueba persistencia.

## 29. Errores

Validación esperada debe dar mensaje accionable; error inesperado debe reportarse con respuesta segura (APPROVED CONTRACT AGENTS). Negativos/rollback ejecutados se acreditan solo por método/gate Fase 3 en TRAZABILIDAD; distinguir validación/403/409 de fallo técnico.

## 30. Efectos secundarios permitidos

Actualizar información administrativa autorizada; conservar ocupaciones e historial.

## 31. Efectos secundarios prohibidos

CRUD directo de residente, doble ocupación, liberar cama por cambiar etiqueta, publicar clínica familiar por vínculo.

## 32. Tests existentes

**TESTED EXPECTATION:** TEST-ALO-001, TEST-ALO-002, TEST-FAM-001. Métodos exactos y alcance en [cobertura estática](../../../docs/TRAZABILIDAD.md); DEFINED / STATICALLY_MAPPED en Fase 2; resultados ejecutados Fase 3 por método/gate en TRAZABILIDAD.

## 33. Tests faltantes

Fase 2 identificó faltantes de carreras/traslado/contenido. Fase 3 acredita carrera de admisión para una cama y lectura familiar permitida en JSON/HTML, con denegación de PDF/documentos no publicados. No acredita carrera de dos camas para un residente, traslado/liberación completo con rollback ni todos los exports.

## 34. Deuda técnica

TECH-001 contenido familiar; TECH-005 garantías físicas; TECH-006 diferencias documentales de estado. [Registro TECH](../../../docs/DEUDA_TECNICA.md). Las correcciones Fase 3 y su evidencia acotada se detallan en TRAZABILIDAD; no se certifica todo el producto.

## 35. Decisiones abiertas

DEC-OPEN-001 valores institucionales; DEC-OPEN-006 publicación clínica familiar adicional. [Registro DEC](../../../docs/DECISIONES_PENDIENTES.md). No se aprueba opción ni se duplica identificador.

## 36. Trazabilidad

REQ-ALO-001; RULE-ALO-001; AUTH-ALO-001; FLOW-ADM-001; INV-ALO-001, INV-ALO-002, INV-FAM-001; TEST-ALO-001, TEST-ALO-002, TEST-FAM-001. [Mapeo por clase/test](../../../docs/TRAZABILIDAD.md) y [invariantes](../../../docs/sistema/INVARIANTES_NEGOCIO.md).

## Análisis de gaps por módulo

| Categoría | Severidad / evidencia | Acción futura |
|---|---|---|
| CONTRACT GAP | HIGH — valores físicos/transiciones DEC-OPEN-001 | Resolver solo decisión aplicable antes de cambiar significado |
| IMPLEMENTATION GAP | MEDIUM — valores y entrada heredada según §9/11 | Contrastar variantes contra contrato; no normalizar por estética |
| AUTHORIZATION GAP | HIGH — TECH-001 contenido familiar | Negativos por cada entrada y scope, conforme §15 |
| TEST GAP | HIGH — §33; definición de tests no acredita cobertura completa ni runtime | Verificar casos faltantes en entorno aislado de Fase 3 |
| DOCUMENTATION GAP | LOW — contrato creado y trazabilidad localizada; detalles no investigados se marcan NOT VERIFIED | Mantener símbolos/fuentes y resultados ejecutados separados |
| CONTINUITY GAP | MEDIUM — integración posterior no ejecutada; no se inventa bandeja | Comparar fuentes y proyecciones autorizadas |
