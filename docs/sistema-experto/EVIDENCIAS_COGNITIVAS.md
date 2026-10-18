---
title: "Evidencias cognitivas: contrato de lectura de Fase 3"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-17
source_of_truth: false
clinical_inference_activated: false
---

# Evidencias cognitivas: contrato de lectura

Este documento consolida las fuentes disponibles sin crear reglas expertas.
La instrucción de Fase 3 es autoridad para los estados descriptivos de interfaz.
El [diccionario, sección 39](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md)
gobierna los campos; las extensiones posteriores del baseline no modifican el
contrato de `controles_cognitivos` en esta tarea.

## Fuentes y clasificación

| Fuente | Clasificación y uso |
|---|---|
| [ControlCognitivo](../../app/Models/ControlCognitivo.php) y [ObservacionCognitiva](../../app/Modulos/Cuidados/Dominio/ObservacionCognitiva.php) | Operativa vigente: fecha, residente, personal, quince indicadores y observación. No son resultados objetivos de memoria. |
| [Captura cognitiva](../frontend/OBSERVACION_COGNITIVA_IMPLEMENTACION.md) | Contrato de captura y ausencia/false; sin MMSE, MoCA, Pfeiffer ni gravedad nueva. |
| [AdaptadorFuentesCOGMEM](../../app/Modulos/ApoyoDecision/Infraestructura/Adaptadores/AdaptadorFuentesCOGMEM.php) | Evidencia de implementación: residente, procedencia, estado, componente y contexto. No se invoca desde el resumen. |
| [AdaptadorHechosCOGMEM](../../motor-experto/src/motor_experto/adaptadores/entrada/AdaptadorHechosCOGMEM.php) | Repetición requiere contexto mnésico; olvido requiere comprensión/recepción y atribución verificadas para el motor. FALSE no demuestra preservación. |
| [LecturaResultadosExperto](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/LecturaResultadosExperto.php) | Resultados conservados: exige contrato de publicación e integridad. |
| Copia `Documents/Codex/2026-10-10/files-pasted-by-the-user-prompt/work/RememberMind/docs/sistema-experto/IMPLEMENTACION_V1.md`, sección «Paquete investigado COG-MEM» | Antecedente documental del 08/10: mapeos observacionales propuestos e inactivos, no aprobación clínica. |
| [Interfaz actualizada](INTERFAZ_MEDICA_RESULTADOS.md) | Contrato de consulta y publicación, actualizado a la arquitectura actual. |

La documentación de árboles es propuesta histórica, no fuente de reglas para
esta implementación. Las rutas antiguas Backend/Frontend quedan sustituidas por
las de `app/Modulos`. No se modifica el motor ni se pretende sincronizar el
Documento Maestro externo de O.R.I.O.N. con esta entrega.

## Observación, instrumento y resultado

`memoria_reciente`, `memoria_remota`, repetición, olvido y cambio cognitivo son
descripciones operativas. «Alterada» o «Alteración leve» son etiquetas del dato
registrado, no una nueva interpretación de gravedad ni alteración objetiva Petersen.
La nota libre se muestra literalmente; no se deduce repetición/olvido de sus palabras.

Una aplicación de instrumento, su total o una etiqueta guardada no acreditan
automáticamente un componente válido de memoria. El adaptador exige componente,
preguntas/respuestas coherentes, versión, procedencia y condiciones verificadas.
El resumen no promedia instrumentos ni atribuye un total de Barthel a COG-MEM.
La ausencia de resultado experto publicable no significa que no exista ninguna
aplicación instrumental en el expediente.

Un resultado experto requiere publicación autorizada y la validación del lector
existente. Conocimiento documental cargado, estado ACTIVO y evaluación conservada
por sí solos no bastan. Las fixtures sintéticas de las pruebas nunca habilitan
contratos en la aplicación.

## Estados y frases con fuente

| Estado | Condición y mensaje |
|---|---|
| Sin observaciones registradas | Cero controles VIGENTE dentro del período, excluyendo futuros y otros residentes. «No existen observaciones cognitivas disponibles para el período consultado». |
| Observación documentada | Indicador explícito o descripción guardada, sin señal de memoria de las enumeradas abajo. «Hay observaciones cognitivas documentadas en el período». FALSE/CONSERVADA no generan normalidad. |
| Señal preliminar | TRUE en `repite_preguntas` o `olvida_indicaciones`, o etiqueta descriptiva ALTERACION_LEVE/ALTERADA en los campos de memoria. «Se documentó repetición de preguntas…», «Se documentó olvido de indicaciones…», ambas solo si están en el mismo control, o «Se documentaron dificultades en los campos descriptivos de memoria». No es EV-CM ni inferencia. |
| Hallazgo sustentado | Exclusivamente en el bloque experto separado: resultado permitido, autorizado y sin fallos de integridad. Título/alcance de `LenguajeResultadosExperto`. |
| Información insuficiente | Controles vacíos/NO_VALORABLE o evaluación experta autorizada sin conclusión verificable. Se conserva la procedencia y se explica el límite. |
| Contradicción | Discordancia literal: valores explícitos diferentes del **mismo indicador y segundo de registro**. Se muestran ambos originales y los indicadores discordantes; no se determina cuál es correcto. No se extrapola contradicción entre fechas o entre indicadores distintos. |

«Las observaciones pueden justificar una evaluación específica de memoria, pero
no acreditan por sí solas una alteración cognitiva objetiva» procede del encargo
de la propietaria. «No determinan por sí solos su causa» conserva los límites de
contexto de los adaptadores. «Varios registros pueden corresponder al mismo
episodio» evita afirmar independencia por PK, fecha o cantidad. No se cuentan
episodios: la fuente no ofrece una identidad suficiente para hacerlo.

Los límites temporales incluyen inicio y final. La observación principal y el
resultado experto conservan fechas separadas. Diferencias entre fechas no se
presentan como pérdida de memoria, progresión ni mejoría.
