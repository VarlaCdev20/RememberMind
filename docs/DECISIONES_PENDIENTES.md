---
title: "Decisiones pendientes de RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verified_against_commit: null
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# Decisiones pendientes

Registro de ambigüedades materiales, no preguntas técnicas triviales. OPEN no modifica el contrato vigente. Opciones no son aprobación. Solo se bloquea el cambio dependiente; el trabajo seguro/documental continúa.

El inventario BDD actual está conciliado por decisiones explícitas: no se abre una decisión nueva de 69/70/71. Fuente: [índice BDD](base-de-datos/README.md). Referencias de línea siguientes corresponden al snapshot inspeccionado antes de añadir metadatos; localizar también sección/símbolo.

## DEC-OPEN-001

**Título:** Valores persistidos y ciclo de admisión

**Área:** Institucional / BDD

**Problema:** Baseline declara residente ACTIVO y preadmisión PENDIENTE/APROBADA/RECHAZADA; Action/tests usan ADMITIDO y la Action ADMITIDA. Diccionario de preadmisión dice estados mínimos. La instrucción actual establece condición institucional residente ADMITIDO.

**Por qué requiere decisión:** Conciliar condición de negocio con valores físicos y transición de solicitud; no deducir catálogo completo por una palabra ni por código.

**Opciones conocidas:**

A. Ratificar valores físicos existentes y documentar su relación con condición institucional.
B. Definir adaptación aprobada de implementación al catálogo rector.
C. Separar decisión por campo/transición si el contrato exige distinción.

**Impacto:** Flujo crítico, búsquedas, permisos por estado y cualquier migración de datos.

**Bloquea:** Cambios dependientes del catálogo físico; no lectura de fuentes ni mantenimiento documental.

**Evidencia:** Baseline §§6 y 29; diccionario preadmisiones; FormalizarAdmision:61,106,139; BddOperativaV2Test:122,126; AGENTS flujo institucional.

**Estado:** OPEN

## DEC-OPEN-002

**Título:** Formalización de catálogos clínicos/operativos pendientes

**Área:** BDD / dominio

**Problema:** Baseline declara pendientes de formalización cerrada planes_cuidado, alertas, consentimientos y heridas.

**Por qué requiere decisión:** Nuevos valores/CHECK/catálogos alteran modelo congelado y significado institucional.

**Opciones conocidas:**

A. Validar catálogo exacto y fuente por entidad.
B. Mantener provisionalmente validación vigente sin añadir estructura.
C. Proponer extensión formal específica si el contrato lo requiere.

**Impacto:** Transiciones, validación, restricciones físicas y portabilidad.

**Bloquea:** Cambiar valores/catálogos o agregar restricciones no aprobadas.

**Evidencia:** Baseline §29:480–481 (snapshot previo a cabecera Fase 1); database/AGENTS frozen schema.

**Estado:** OPEN

## DEC-OPEN-003

**Título:** Etiquetas clínicas y clasificación asistencial global

**Área:** Clínica / UX

**Problema:** No hay criterio aprobado para etiquetas Estable/Vigilancia/Riesgo o distribución clínica equivalente.

**Por qué requiere decisión:** Ausencia de alertas o prioridad no demuestra estabilidad; es decisión clínica, no una convención de color.

**Opciones conocidas:**

A. Mantener ausencia de etiqueta hasta decisión.
B. Definir criterio, profesional, fuente, vigencia y tratamiento de datos ausentes.
C. Mostrar solo datos existentes descriptivos sin inferir clasificación.

**Impacto:** Interpretación clínica y UX; posible estructura solo si se aprueba.

**Bloquea:** Introducir etiquetas/gráficas de clasificación no respaldadas; no UI general.

**Evidencia:** frontend/PENDIENTE_BADGES_ESTADO_RESIDENTE; FASE_9_DISTRIBUCION_Y_AGENDA; resources/AGENTS.

**Estado:** OPEN

## DEC-OPEN-004

**Título:** Contrato de reintentos/duplicados para PRN

**Área:** Medicación / persistencia

**Problema:** Baseline mantiene abierto riesgo PRN/reintentos; servicio PRN crea registros sin contrato de token/reintento localizado.

**Por qué requiere decisión:** Distinguir repetición válida de registro duplicado sin inventar frecuencia clínica o alterar esquema.

**Opciones conocidas:**

A. Precisar identidad del evento y comportamiento de reintento compatible con modelo.
B. Formalizar extensión estructural si falta representación necesaria.
C. Mantener regla vigente, documentando límite, hasta decisión concreta.

**Impacto:** Administración, audit, riesgo de duplicación y concurrencia.

**Bloquea:** Crear nuevas reglas de frecuencia/unicidad o claves estructurales sin aprobación; no pruebas técnicas independientes.

**Evidencia:** Baseline §26:419–421; RegistrarAdministracionMedicacionService:144–179; índice programado en hardening no resuelve PRN.

**Estado:** OPEN

## DEC-OPEN-005

**Título:** Medición original, derechos y validación clínica del experto.

**Estado al 25/10/2026: PARCIALMENTE RESUELTA por decisiones específicas; siguen abiertos los alcances enumerados.**

| Alcance | Estado y fuente |
|---|---|
| Contenido institucional A–D | Adoptado el 19/10: [contrato](sistema-experto/PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md) |
| Petersen E–H | Adoptado el 19/10 y concretado por [C01–C05](sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md) |
| Captura original y gobierno | Aprobaciones específicas registradas; [captura](sistema-experto/PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md) y [gobierno](sistema-experto/PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md) |
| Revisión posterior | Medicina y Psicología por separado; el alcance conjunto depende de conservación real |
| Conservación conjunta | Diseño institucional y guardado SQL íntegro pendientes; no se equiparan al prototipo |
| Aplicaciones originales, método y licencias | Requeridos por evidencia/instrumento concreto; no completados por una aprobación documental |
| Validación clínica de desempeño | Pendiente de casos y revisión profesional independiente |

No reabrir A–D/E–H como si faltara su adopción. No exigir firma previa para desarrollar, probar o ejecutar técnicamente los contratos aprobados; conservar estado, límites y abstenciones. Tampoco convertir una investigación o consentimiento de desarrollo en medición, certificación o validación profesional inexistente.

**Bloquea exclusivamente:** contenido clínico nuevo no aprobado, uso no autorizado de instrumentos, instalación de DDL no autorizada y declaración de validación clínica sin evidencia. No bloquea correcciones técnicas compatibles, pruebas desechables ni implementación del alcance ya aprobado.

**Evidencia actual:** [índice experto](sistema-experto/README.md) y [auditoría del 25/10](auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md), H07–H13. Las opciones generales anteriores se sustituyen por esta separación de alcances; no se crean nuevas reglas.

## DEC-OPEN-006

**Título:** Publicación clínica a familiares

**Área:** Seguridad / clínica / documentación

**Problema:** Contrato limita a información autorizada por vínculo; dashboard no publica documentos, pero entradas genéricas cargan datos clínicos. La reparación de filtros es TECH-001, no permiso nuevo.

**Por qué requiere decisión:** El contenido adicional compartible, su responsable/consentimiento y frontera requieren decisión institucional; el permiso de ver residente no la concede.

**Opciones conocidas:**

A. Mantener contenido actualmente autorizado y denegar lo no publicado.
B. Definir publicación explícita por tipo/contenido/proceso.
C. Diseñar alcance acotado si existe necesidad aprobada.

**Impacto:** Privacidad, reportes/documentos, responsabilidad profesional y vínculo.

**Bloquea:** Agregar publicación clínica nueva; no corregir técnica compatible con frontera existente en tarea posterior.

**Evidencia:** Baseline roles Familiar; DASHBOARDS_POR_ROL; ResidentePolicy:21–33; ResidenteController:29–32; ReporteV2Controller:15–18.

**Estado:** OPEN

## Fuentes y cierre

[Baseline](base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [experto](sistema-experto/README.md), [deuda](DEUDA_TECNICA.md).

Al decidir: enlazar aprobación/fecha/alcance, marcar estado y actualizar documentación dependiente. No cerrar porque exista implementación ni presentar estas opciones como cambios aprobados.
