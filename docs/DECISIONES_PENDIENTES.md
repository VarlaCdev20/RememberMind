---
title: "Decisiones pendientes de RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-08
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

**Título:** Método, derechos y validación del experto cognitivo

**Área:** Investigación / experto

**Problema:** O.R.I.O.N. define la arquitectura cognitiva, el esquema D-123/D-137 y el contrato piloto D-131/D-143/D-144. El [núcleo técnico V1](sistema-experto/IMPLEMENTACION_V1.md) usa datos artificiales. Permanecen pendientes los contratos específicos de contenido, instrumentos/derechos, responsables y validación profesional para activación clínica. No se requieren ni se autorizan pesos, scores o agregación global por esta referencia.

**Por qué requiere decisión:** Crear skill Buchanan/multicriterio no aprueba método clínico, licencias, algoritmo, datos o persistencia.

**Opciones conocidas:**

A. Aprobar protocolo y responsabilidades con fuentes/versiones/casos de referencia.
B. Mantener diseño y fixtures sintéticas sin activación clínica.
C. Acotar una etapa/piloto con decisiones específicas previas.

**Impacto:** Resultado cognitivo, explicaciones, recomendaciones, validación, posibles alertas y modelo futuro.

**Bloquea:** Activación clínica, copia de reactivos y aceptación clínica del motor; no diseño documental independiente.

**Evidencia:** [Índice experto](sistema-experto/README.md), [implementación técnica y fuentes O.R.I.O.N.](sistema-experto/IMPLEMENTACION_V1.md). Los tres diseños de árboles siguen PROPOSED y no sustituyen la red semántica aprobada. La autorización técnica de migraciones y pruebas no es aprobación clínica.

**Estado:** OPEN

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
