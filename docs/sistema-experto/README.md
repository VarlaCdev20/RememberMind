---
title: "Sistema experto: fuentes, funcionamiento y límites"
status: CURRENT
version: "3.20"
last_reviewed: 2026-10-31
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [expert_contract_routing]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENT_ROUTING_STATIC_CODE_MAP_AND_SCOPED_CATALOG_METADATA
runtime_verified: false
---

# Consulta cognitiva y sistema experto

**Orden vigente del 30/10/2026:** [estado funcional y desarrollo técnico primero](ESTADO_FUNCIONAL_Y_ORDEN_DESARROLLO_2026_10_30.md). Durante todo el desarrollo no se solicitan estudios con residentes, firmas o avales externos para continuar. Primero se completa y prueba el funcionamiento integral; la evaluación posterior en uso real se realiza después. Los controles de datos, permisos y coherencia de inferencia se conservan.

## Entrada única: dónde encontrar cada parte

Objetivo de trabajo: detección temprana de DCL en personas mayores de 60 años, con evidencias explicables y revisión profesional. La propietaria aprobó el 28/10/2026 una selección escalonada: entrevista e informante + MoCA o RUDAS + FAQ como prioridad inicial; apoyos y evaluación ampliada según el caso. Las versiones, condiciones y derechos de incorporación permanecen por cerrar en su alcance.

| Necesito encontrar… | Abrir |
|---|---|
| Cierre técnico actual con relaciones y permisos conservados; reglas para la siguiente etapa | [Entregable único de correcciones y estado pendiente](ENTREGABLE_CORRECCIONES_TECNICAS_2026_10_31.md) |
| Entregable único actual: fundamento médico, guía integrada de captura y brechas pendientes | [Entrega del 31/10: fuentes profesionales, plan de faltantes y límites técnicos](ENTREGABLE_INTEGRACION_Y_FUENTES_DCL_2026_10_31.md) |
| Entrega única de instrumentos iniciales, versiones y perfiles de preparación | [Circuito inicial, conexión a hechos/inferencia y condiciones reales de activación](ENTREGA_INSTRUMENTOS_Y_PERFILES_INICIALES_2026_10_29.md) |
| Entrega completa de la fase de contexto: conceptos, captura, integración y pruebas | [Cierre de contexto y procedencia del 29/10](ENTREGA_FASE_CONTEXTO_COGNITIVO_2026_10_29.md) |
| Primer bloque: qué registrar y cómo validarlo | [Contrato de captura y preparación sin residentes](organizacion/REGISTROS_ORIGINALES_Y_VALIDACION.md) |
| De cada fuente a las evidencias del caso | [Mapa de fuentes, áreas y criterios](organizacion/MAPA_FUENTES_A_HECHOS.md) |
| Los componentes y el diagrama adoptados | [Estructura por ocho componentes](organizacion/ESTRUCTURA_POR_COMPONENTES.md) |
| El código de hechos, red semántica, reglas e inferencia | [Mapa único de componentes y recorridos](organizacion/MAPA_COMPONENTES.md) |
| Qué datos existen y qué falta capturar | [Ficha y matriz D01–D27](MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md) |
| Los 45 instrumentos y evaluaciones candidatos | [Catálogo de investigación por finalidad](instrumentos/CATALOGO_CANDIDATOS.md) |
| Qué instrumentos se seleccionaron y qué integramos primero | [Selección escalonada adoptada y pendientes de activación](instrumentos/SELECCION_ADOPTADA_2026_10_28.md) |
| Ediciones, condiciones, derechos y captura de los tres iniciales | [Fichas MoCA, RUDAS y FAQ; pendientes concretos de incorporación](instrumentos/CIRCUITO_INICIAL.md) |
| Formularios propios de captura y entrevista implementados | [Fuentes médicas, interfaz y verificación responsive](../frontend/FORMULARIOS_CAPTURA_COGNITIVA_2026_10_28.md) |
| Escolaridad por sesión y límites de idioma/informantes | [Tres campos escalares por atención y justificación de 4FN](../base-de-datos/DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md) |
| Cómo vincular la evaluación con el contexto de su atención | [Selector contextual, FK existente y límites de interpretación](ENLACE_ATENCION_APLICACION_2026_10_28.md) |
| Idioma e informante principal por atención, conservando 4FN | [Ampliación autorizada e instalada del 29/10](../base-de-datos/PROPUESTA_CONTEXTO_IDIOMA_INFORMANTE_2026_10_29.md); [auditoría precedente del 28/10](CONTEXTO_IDIOMA_INFORMANTES_2026_10_28.md) |
| Qué materiales originales ya se obtuvieron y qué condiciones faltan | [Cotejo de seis documentos y consultas preparadas](instrumentos/COTEJO_MATERIALES_2026_10_28.md) |
| Captura de funcionamiento cotidiano por Medicina/Psicología | [Formulario propio, autoría, rectificación e integración](FORMULARIO_FUNCIONAMIENTO_COTIDIANO_2026_10_28.md) |
| Qué está realmente catalogado en la base local | [Inventario PostgreSQL del 27/10/2026](inventarios/README.md) y [metadatos](inventarios/2026-10-27.json) |
| Reglas institucionales: fuente y gestión | [Mapa de reglas/versiones](organizacion/MAPA_COMPONENTES.md#3-reglas-en-un-solo-lugar-fuente-y-responsabilidades) y ruta existente `/admin/sistema-experto/reglas`, sujeta a autorización |
| Retirada de simulaciones y laboratorio | [Alcance, conservación y verificación](RETIRADA_LABORATORIO_2026_10_27.md) |
| Contratos aprobados y límites pendientes | Tablas y secciones siguientes; [catálogo documental completo](INDICE_DOCUMENTAL.md) para antecedentes |

El inventario comprobó **26 entradas de instrumentos** (2 Barthel, 5 capturas sintéticas y 19 registros de tarea inactivos), **6 versiones de conocimiento** y **32 reglas**. Estos conteos describen metadatos, no instrumentos aplicados, evaluación posterior en uso real ni cierre del experto. No confundir los 45 candidatos investigados con el catálogo instalado.

## Recorrido vigente

Fuentes originales autorizadas → adaptadores de ApoyoDecision → motor simbólico PHP → cinco dominios/19 componentes → perfil orientativo Petersen o abstención → explicación. La rama observacional tiene conservación y revisión posterior comprobadas. **La conservación institucional conjunta instrumental/Petersen sigue pendiente.**

La implementación usa `motor-experto/` y `app/Modulos/ApoyoDecision/`; no existe un segundo motor que deba reconstruirse. [Estado actual](../ESTADO_ACTUAL.md) y [auditoría del 25/10](../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) delimitan los defectos abiertos.

## Contratos y decisiones aplicables

Preparación actual para detección temprana en personas mayores de 60 años: [ficha y matriz de datos del 27/10/2026](MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md). Es un inventario estático para revisión: separa capacidades, fuentes parciales y conservación pendiente; no selecciona una batería ni cambia reglas o permisos.

La [decisión de selección del 28/10/2026](instrumentos/SELECCION_ADOPTADA_2026_10_28.md) complementa ese corte: cierra las familias y niveles, sin declarar perfiles instalados, derechos obtenidos o evaluación posterior en uso real.

| Alcance | Fuente |
|---|---|
| Institucional A–D / reglas G09 | [Contenido adoptado](PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md) |
| Petersen E–H | [Adopción institucional](PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md) |
| Requisitos C01–C05 y cinco dominios | [Contrato funcional exacto](CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md) |
| Captura Petersen y soportes | [Estructura y permisos aprobados](PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md) |
| Procedencia instrumental | [Extensión aprobada de fuentes](PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md) |
| Gobierno y sustitución de reglas | [Decisión aprobada](PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md) |
| Informe y evidencias | [Contrato del informe](CONTRATO_INFORME_COGNITIVO.md) y [fuentes y límites](EVIDENCIAS_COGNITIVAS.md) |
| Inventario y consolidaciones instaladas | [106 tablas de negocio + 16 técnicas; decisión vigente sin nuevas tablas](../base-de-datos/README.md) |
| Revisión posterior independiente | [Decisión de revisiones](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md) |

El nombre PROPUESTA de un documento no invalida una adopción posterior escrita en él. La adopción tiene alcance concreto; no concede una firma profesional, mediciones originales ni aprobación de DDL adicional.

## Comportamiento y límites que deben preservarse

- Cada dominio mantiene evaluabilidad independiente; A/P requieren sustento. NULL, negación, ausencia y contradicción no se convierten en normalidad.
- C1 ∧ C2 ∧ C3 de G09 pertenecen a la misma aplicación. Observación nominal no equivale a medición instrumental.
- C02 se deriva de los cinco dominios; no se captura como juicio original. C01/C03/C04/C05 siguen sus soportes y compuertas.
- Los cuatro perfiles son orientativos de posible DCL. Una abstención fundada no se sustituye por un resultado deseado.
- Medicina y Psicología revisan después y por separado. No se exige su firma antes de desarrollar o ejecutar técnicamente el contrato aprobado.
- La evaluación conjunta conserva `persistido=false` mientras no exista guardado y recuperación institucional íntegros. No vincular su revisión a un DTO transitorio.
- El laboratorio y la cohorte simulada están desconectados del producto. Las pruebas automatizadas quedan aisladas en `tests/`; los registros técnicos almacenados se excluyen del circuito y no se reinterpretan como historia real. Véase [retirada](RETIRADA_LABORATORIO_2026_10_27.md).

## Conservación: evidencia y brecha

[La referencia SQL completa](RECUPERACION_SQL_COMPLETA_REFERENCIA_2026_10_22.md) demuestra igualdad del corpus con sus límites, pero no es instalable: 229 tablas hipotéticas, problemas de anchura y FK históricas incompletas. No sustituye el diseño institucional. [Las cuatro consolidaciones ya instaladas](CIERRE_REFACTOR_BLOQUES_2026_10_22.md) sí redujeron 104 → 100 en su alcance; no cierran el DTO conjunto.

## Investigación y entregas anteriores

[Catálogo completo de contratos, decisiones, investigación y cortes](INDICE_DOCUMENTAL.md). Las fuentes bibliográficas no crean mediciones; las pruebas de prototipos no certifican el producto institucional. Los 104 escenarios citados históricamente no tienen manifiesto local identificado; no se atribuye su ejecución.

`IMPLEMENTACION_V1.md` no existe en este árbol. No se recrea su contenido ni se usa como requisito de lectura; las entradas anteriores que lo mencionaban se concilian con las fuentes presentes.
