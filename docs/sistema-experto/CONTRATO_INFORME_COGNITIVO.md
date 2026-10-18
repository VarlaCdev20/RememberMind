---
title: "Contrato de salida del informe cognitivo"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-17
source_of_truth: false
clinical_inference_activated: false
---

# Contrato de salida — Fase 4

> La solicitud posterior de la propietaria sustituye la jerarquía visual y el acceso técnico de esta versión por el [informe neurocognitivo](REDISENO_INFORME_NEUROCOGNITIVO_2026_10_17.md). El lector conserva las invariantes de publicación, pero añade narrativa determinista, lectura IADL autorizada y auditoría restringida. Las entradas técnicas automáticas descritas históricamente aquí ya no se incorporan a la pantalla normal.

## Fuentes y autoridad

Vigentes: instrucción de la propietaria de Fase 4; AGENTS raíz, app, resources y
tests; [ADR híbrido](../arquitectura/ADR_ARQUITECTURA_HIBRIDA.md);
[interfaz médica](INTERFAZ_MEDICA_RESULTADOS.md);
[evidencias cognitivas](EVIDENCIAS_COGNITIVAS.md); baseline físico D-137 y
[conservación V2 aprobada](../base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md).
La ampliación de conservación no autoriza publicación clínica de sus instantáneas.

Implementación consultada: ResultadosExpertoResidente, lector, lenguaje,
EvaluacionExpertaPolicy, modelos de evaluación/criterio/resultado/evidencias/traza,
adaptadores COG-MEM, AlcanceLecturaExperta y configuración de publicación.

G09 está fuera de este checkout: se inspeccionaron, sin modificar ni importar,
`C:/Users/CARLAENCINAS/.codex/worktrees/pr15-ci-utf8/RememberMind/`:

- `docs/sistema-experto/G09_EXPERIMENTAL_GUIA_EJECUCION.md`.
- `motor-experto/src/motor_experto/investigacion/g09/EvaluadorG09Experimental.php`.
- `ConstructorInformeG09Experimental.php` y `CatalogoReglasG09Experimental.php`
  en la misma carpeta.

Estado: experimental, versión `G09-EXP-20261017`, sin integración asistencial en
este árbol. Se conserva su catálogo de **presentación**, sin copiar el motor ni
crear mapeos de inferencia, reglas o umbrales. Los manifiestos locales
`PaquetesCandidatosCognitivos` son documentales, no evaluaciones del residente.

## Responsabilidades

`LecturaResultadosExperto` sigue consultando registros conservados. Reautoriza,
valida versión, evaluabilidad, dominios, condiciones, soportes y traza. No ejecuta
inferencias ni reconstruye la instantánea desde datos actuales. Añade dos campos
de procedencia para la presentación: `criterio_registrado` y
`evaluabilidad_codigo`, sin modificar su significado ni resultado.

`PresentarInformeCognitivo::presentar(lectura, criterio)` recibe exclusivamente
esa lectura. Devuelve arrays planos de presentación; no cambia la entrada y no
consulta ni persiste datos. Sus textos clínicos proceden de los contratos
inspeccionados y de `LenguajeResultadosExperto`. Blade se limita a mostrarlos.

Livewire selecciona área/evaluación/fuente, reautoriza y maneja el estado del
drawer. Relee al usuario antes de cada interacción para respetar revocaciones.
La Policy no cambia. La consulta de fuente conserva su permiso adicional y se
identifica como dato **actual**, no como evidencia histórica reconstruida.

## DTO de presentación

| Campo | Significado |
|---|---|
| `titulo` | Análisis cognitivo. |
| `areas` | Memoria, Atención, Funciones ejecutivas, Lenguaje y Habilidades visuoespaciales, con su código interno como clave. |
| `actual` | Área seleccionada, sin mezclar resultados de las otras áreas. |
| `historial` | Copia de las entradas devueltas por el lector: identidad, fecha, versión, criterio, estado y evaluabilidad originales. El paginador del lector conserva su función. |
| `limite_global` | Alcance por componentes examinados; no diagnóstico ni cognición global. |
| `sin_evaluacion` | Ausencia de evaluación conservada; las observaciones no equivalen a área evaluada. |
| `complemento_textos` | Límites de documentación, controles, aplicaciones y pruebas técnicas. |

Cada área incluye `nombre`, `icono`, `estado`, `conclusion`, `significado`,
`limites`, `respaldo`, `tono`, `evaluada` y `por_que`.

`evaluada=true` solo cuando el lector publica un resultado COG-MEM reconocido,
autorizado, sin avisos de integridad y con traza/soportes, fuera de la lectura
técnica. No mide cobertura exhaustiva del dominio. Las otras áreas permanecen
sin evaluación publicable en el lector actual. El estado de publicación bloqueada,
la falta de evaluación y la insuficiencia no se convierten en normalidad.

El presentador no es una nueva autoridad de publicación: el contrato versionado
y las comprobaciones completas pertenecen al lector. `lectura_clinica` continúa
vacío en configuración. No se activa conocimiento por este rediseño.

## Memoria: traducción y alcance

| Salida conservada | Conclusión cuando está autorizada y sustentada |
|---|---|
| `DIFICULTAD_EVIDENCIADA` | Se identificaron dificultades en los componentes evaluados de memoria. |
| `SIN_DIFICULTAD_EVIDENCIADA` | No se encontraron dificultades en los componentes efectivamente evaluados. |
| `HALLAZGOS_MIXTOS` | Se encontraron resultados diferentes entre las tareas evaluadas, por lo que se requiere interpretar cada hallazgo en su contexto. |
| `EV-CM-0`, sin resultado | Información insuficiente; evidencia insuficiente para concluir sobre memoria. |
| `EV-CM-1`, sin resultado | Información preliminar que requiere ampliación. |
| Integridad incompatible | No se publica conclusión. Se muestran los avisos conservados, sin elegir un resultado alternativo. |
| Observaciones generales sin evaluación | Controles disponibles, sin evaluación integrada; no se describe el área como evaluada. |

El significado de los tres resultados conserva la redacción de
`LenguajeResultadosExperto::RESULTADOS`. EV-CM no se calcula de nuevo. Mixtos
requiere el soporte independiente que ya comprueba el lector; no equivale a
evidencia contradictoria G09 ni a diagnóstico.

## G09: seis estados segregados

`PresentarInformeCognitivo::presentarG09Prueba` es una entrada interna de pruebas,
sin ruta ni conexión al componente asistencial. Exige `APP_ENV=testing`, base
desechable identificada por el guard existente, informe
`PRUEBA_TECNICA_NO_CLINICA`, versión exacta y uno de los cuatro criterios G09.
No lee residentes, no ejecuta G09 y siempre devuelve `publicable=false`.
El catálogo de lenguaje por sí solo no valida trazas ni autoriza uso clínico.

| Estado técnico original | Etiqueta descriptiva |
|---|---|
| `SIN_EVIDENCIA_ADMISIBLE` | Sin evidencia admisible. |
| `SOLO_SENAL_CONTEXTO` | Solo señal de contexto. |
| `EVALUABLE_SIN_SOPORTE` | Evaluable sin soporte positivo; no equivale a preservación. |
| `HALLAZGO_ACOTADO` | Hallazgo experimental acotado a la prueba técnica. |
| `MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION` | Múltiples hallazgos pendientes de resolución, sin priorización automática. |
| `EVIDENCIA_CONTRADICTORIA` | Evidencia contradictoria; sin conclusión consistente. |

No se convierte ninguno en A/P, deterioro, diagnóstico, gravedad o clasificación
Petersen. Los estados/orígenes TEST_ONLY/EXPERIMENTAL y versiones G09-EXP se
excluyen del historial asistencial. La entrada técnica anterior COG-MEM sigue
limitada a testing/base desechable y se presenta explícitamente como artificial.

## Jerarquía y funciones

1. ¿Qué se encontró?: conclusión del área seleccionada y estado real.
2. ¿Qué áreas se evaluaron?: cinco áreas seleccionables, respaldo y límites.
3. ¿Qué significa?: interpretación permitida; si falta, «La información disponible
   todavía no permite establecer una interpretación cognitiva integrada».
4. ¿Por qué?: soporte legible, evidencias y fundamento técnico desplegables.
5. Historial: fecha, versión y estado de cada evaluación sin recalcular resultados.

Se mantiene la misma ruta de Medicina/Enfermería y la selección de criterio,
evaluación, paginación, fuente y drawer. El complemento conserva definiciones,
preparación, controles y artefactos de QA, distinguiéndolos de resultados.
Los identificadores técnicos se reservan al fundamento. Se retiran botones
deshabilitados de exportación/nueva evaluación/revisión, sin inventar operaciones.
No se crea otra pantalla, menú o motor.
