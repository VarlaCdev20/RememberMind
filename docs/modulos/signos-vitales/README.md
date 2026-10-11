---
title: "Contrato de módulo — Signos vitales"
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
related_modules: [signos-vitales]
---

# Signos vitales

Decisión UX de la propietaria (2026-10-09): limpieza y salida de captura pendiente requieren confirmación, **sin justificativo**, incluso ante lectura crítica no persistida. Véase [contrato de limpieza y descarte](../../frontend/LIMPIEZA_Y_DESCARTE_FORMULARIOS_ENFERMERIA.md). No cambia la confirmación clínica al registrar ni el ciclo de alertas ya guardadas.

Compilación vigente, no aprobación de reglas nuevas. **APPROVED CONTRACT**: [AGENTS](../../../AGENTS.md), [baseline](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [decisión V2.2](../../../docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md). Descripciones de entradas, estados, clases y efectos son **OBSERVED IMPLEMENTATION**, sin certificar cumplimiento en todas las variantes. **CONFLICT** y **TECHNICAL DEBT** señalan discrepancias; **OPEN DECISION** no autoriza cambiar contrato.

Snapshot documental de Fase 2 sobre commit base y cambios previos. Fase 3 ejecutó pruebas acotadas en PostgreSQL aislado; resultados en [trazabilidad](../../../docs/TRAZABILIDAD.md). `runtime_verified: false` no certifica globalmente el módulo. El commit identifica la base, no un commit de estas correcciones.

## 1. Propósito

Registrar mediciones longitudinales y evaluación explicable sin persistencia durante preview.

## 2. Alcance

Siete variables de signos, evaluación, objetivo individual V2.2, confirmación crítica, historial y rectificación observada.

## 3. Fuera de alcance

Fase 3 endurece contratos existentes con cambios acotados y pruebas. No cambia BDD, reglas institucionales, competencias ni catálogos; tampoco entrega motor experto o certificación global.

## 4. Actores

Enfermería contexto asignado registra; Médico define objetivo individual; lectura por rol/ámbito.

## 5. Competencias

Médico con permiso gestiona objetivo; Enfermería no cambia objetivos; SA lectura global no autor objetivo por decisión específica.

## 6. Precondiciones

Auth==usuario, personal propio activo, residente admitido/asignado y jornada válida para Action primaria.

## 7. Entidades y tablas

signos_vitales, objetivos_signos_vitales, residentes, personal, jornadas, asignaciones_residente_jornada, alertas, eventos_alerta.

## 8. Relaciones

Medición pertenece residente/autor/contexto; objetivo por residente/parámetro/versionado; alerta automática enlaza cod_registro del signo.

## 9. Estados

Medición ACTIVO/VIGENTE; rectificación original RECTIFICADO+nuevo; método anular ANULADO no acreditado como ruta activa. Objetivos VIGENTE/REEMPLAZADO/ANULADO.

## 10. Caso de uso principal

MisPacientes preevalúa sin escribir; confirma críticos según UI; Service/Action revalida y guarda nuevo signo+alerta/evento automático si corresponde.

## 11. Flujos alternativos

Action, Request y validarYNormalizar exigen al menos una de las siete mediciones persistibles; PA necesita par completo. Peso pertenece a antropometría y dolor a su valoración: ninguno satisface el mínimo de signos. Service::registrar utiliza fecha/hora recibidas tras normalización; Request conserva fecha/hora recibidas y usa hoy/ahora cuando faltan; Request y Service rechazan fecha/hora futura. Ese create no guarda jornada; rectificar exige signos_vitales.editar. TECH-011 corregido sin alterar nullable ni estructura.

## 12. Validaciones técnicas

Validación de numéricos/rangos técnicos y mínimo una de las siete mediciones, PA par; null no cero ni NORMAL; formulario/payload residente coherentes. Fase3SignosVitalesTest reproduce seis negativos de peso/dolor aislados en Service y HTTP y acredita el rechazo tras corregir TECH-011. No convierte todas las variables en obligatorias ni guarda automáticamente antropometría/dolor en otra tabla.

## 13. Reglas de negocio

Preview cero efectos; un campo inválido no oculta la gravedad de otros campos válidos. Nuevo registro por evento; objetivo seleccionado por intervalo clínico de vigencia, sin rebajar crítico general. No persistir evaluación como diagnóstico global. Una alerta crítica SIGNOS pendiente bloquea controles posteriores del residente hasta atención iniciada o término válido; atención, reevaluación y medicación indicada permanecen disponibles. El estado de atención no modifica el color histórico.

## 14. Reglas clínicas aprobadas

APPROVED CONTRACT decisión V2.2: objetivos de siete parámetros, prioridad crítico general → objetivo → reglas generales; SpO2 sin umbral universal automático y glucemia aislada >250 sugiere revisión. No añadir umbrales nuevos.

## 15. Autorización

Action comprueba autor/jornada/residente; TurnoEnfermeriaService contextual. Objetivos Action médico/permiso propios. NONE OBSERVED Policy específica de signos; no inventarla.

## 16. Transaction boundary

RegistrarSignosVitalesAction transaction de signo+alerta+evento; rectificar transaction original/nuevo. Preview no transaction de escritura.

## 17. Persistencia

Unidades/valores nulos según contrato; fecha clínica validada en Action primaria, con momento técnico en Activitylog; no talla/peso en tabla signos. Por instrucción de la propietaria del 2026-10-07, el formulario de Enfermería asigna la fecha/hora del servidor al abrir el registro y bloquea su modificación en UI y Livewire. Otros flujos conservan la fecha clínica recibida conforme a sus contratos.

## 18. Longitudinalidad

Basal lee recientes ACTIVO/VIGENTE; rectificación conserva anterior sin FK nueva, motivo en observación, sin reevaluar alerta en ese método.

## 19. Alertas

ServicioDecisionAlertaClinica solo críticos con AUTOMATICA_AL_CONFIRMAR; una alerta por signo y evento CREACION dentro del boundary; sugerir no es crear.

## 20. Auditoría

Autoría clínica real y evento de alerta; método anular agrega activity pero no contiene contexto clínico visible.

## 21. Continuidad asistencial

Historial/basal/último signo alimentan cuidado/pase; ausencia no estabilidad. Filtro de últimos datos del pase no uniforme.

## 22. Entrada UI

[resources/views/livewire/cuidados/mis-residentes-directorio.blade.php](../../../resources/views/livewire/cuidados/mis-residentes-directorio.blade.php)

## 23. Livewire / Controllers

[app/Frontend/Livewire/Enfermeria/Cuidados/MisPacientes.php](../../../app/Frontend/Livewire/Enfermeria/Cuidados/MisPacientes.php), [app/Frontend/Livewire/Medico/Clinica/ObjetivosSignosVitalesPanel.php](../../../app/Frontend/Livewire/Medico/Clinica/ObjetivosSignosVitalesPanel.php), [app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php](../../../app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php)

## 24. Actions

[app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php](../../../app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php), [app/Backend/Modulos/Clinica/Acciones/DefinirObjetivoSignoVitalAction.php](../../../app/Backend/Modulos/Clinica/Acciones/DefinirObjetivoSignoVitalAction.php)

## 25. Services

[app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php](../../../app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php), [app/Backend/Modulos/Clinica/SignosVitales/EvaluadorSignosVitales.php](../../../app/Backend/Modulos/Clinica/SignosVitales/EvaluadorSignosVitales.php), [app/Backend/Modulos/Clinica/SignosVitales/ServicioDecisionAlertaClinica.php](../../../app/Backend/Modulos/Clinica/SignosVitales/ServicioDecisionAlertaClinica.php), [app/Backend/Modulos/Clinica/SignosVitales/ServicioBasalSignosVitales.php](../../../app/Backend/Modulos/Clinica/SignosVitales/ServicioBasalSignosVitales.php)

## 26. Policies

NONE OBSERVED en el recorrido investigado; no se exige una clase vacía por simetría.

## 27. Models

[SignoVital](../../../app/Models/SignoVital.php), [ObjetivoSignoVital](../../../app/Models/ObjetivoSignoVital.php), [Alerta](../../../app/Models/Alerta.php), [EventoAlerta](../../../app/Models/EventoAlerta.php)

## 28. Eventos / Listeners / Jobs

NONE OBSERVED Event/Listener Laravel o Job propio necesario para este recorrido. EventoAlerta, cuando aparece, es registro Eloquent; dispatch Livewire comunica UI y no prueba persistencia.

## 29. Errores

Validación esperada debe dar mensaje accionable; error inesperado debe reportarse con respuesta segura (APPROVED CONTRACT AGENTS). CONFLICT: captura con getMessage en entrada heredada, TECH-010.

## 30. Efectos secundarios permitidos

Confirmación guarda medición y alerta/evento si regla aprobada exige; preview solo evaluación.

## 31. Efectos secundarios prohibidos

Preview persiste; objetivo oculta crítico; ausencia se rotula NORMAL/Estable; anticipar alerta antes de guardar; antropometría en signos.

## 32. Tests existentes

**TESTED EXPECTATION:** TEST-SV-001, TEST-SV-002, TEST-SV-003, TEST-SV-004, TEST-SV-005. Métodos exactos y alcance en [cobertura estática](../../../docs/TRAZABILIDAD.md); DEFINED / STATICALLY_MAPPED en Fase 2; resultados ejecutados Fase 3 por método/gate en TRAZABILIDAD.

## 33. Tests faltantes

Fase 2 identificó faltantes entre expediente/Action/rectificación. Fase 3 acredita peso/dolor aislados sin signos vacíos, temperatura/glucemia/PA/varias mediciones, PA parcial, error técnico y rollback crítico con historia preservada. No certifica equivalencia universal de fechas/contexto ni todos los caminos alternos de rectificación.

## 34. Deuda técnica

TECH-008 lectores de continuidad; TECH-009 fronteras alternas sin contexto; TECH-010 excepciones crudas al usuario; TECH-011 mínimo de medición distinto de persistencia. [Registro TECH](../../../docs/DEUDA_TECNICA.md). Las correcciones Fase 3 y su evidencia acotada se detallan en TRAZABILIDAD; no se certifica todo el producto.

## 35. Decisiones abiertas

DEC-OPEN-003 etiquetas clínicas globales; DEC-OPEN-002 catálogo aplicable. V2.2 ya aprobada, no requiere nueva decisión. [Registro DEC](../../../docs/DECISIONES_PENDIENTES.md). No se aprueba opción ni se duplica identificador.

## 36. Trazabilidad

REQ-SV-001; RULE-SV-001; AUTH-SV-001; Secuencia signos en FLUJOS_GERIATRICOS; INV-SV-001, INV-SV-002, INV-CLI-001, INV-CLI-002; TEST-SV-001, TEST-SV-002, TEST-SV-003, TEST-SV-004, TEST-SV-005. [Mapeo por clase/test](../../../docs/TRAZABILIDAD.md) y [invariantes](../../../docs/sistema/INVARIANTES_NEGOCIO.md).

## Análisis de gaps por módulo

| Categoría | Severidad / evidencia | Acción futura |
|---|---|---|
| CONTRACT GAP | MEDIUM — límites de estados/contexto según decisiones ya enlazadas; no regla nueva | Resolver solo decisión aplicable antes de cambiar significado |
| IMPLEMENTATION GAP | MEDIUM — entradas distintas descritas en §11/15; conformidad integral NOT VERIFIED | Contrastar variantes contra contrato; no normalizar por estética |
| AUTHORIZATION GAP | HIGH — TECH-009 método alterno; ruta activa no acreditada | Negativos por cada entrada y scope, conforme §15 |
| TEST GAP | HIGH — §33; definición de tests no acredita cobertura completa ni runtime | Verificar casos faltantes en entorno aislado de Fase 3 |
| DOCUMENTATION GAP | LOW — contrato creado y trazabilidad localizada; detalles no investigados se marcan NOT VERIFIED | Mantener símbolos/fuentes y resultados ejecutados separados |
| CONTINUITY GAP | MEDIUM — filtros/corrección no uniformes | Comparar fuentes y proyecciones autorizadas |
