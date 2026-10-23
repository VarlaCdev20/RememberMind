# Petersen y revisión posterior: estado de persistencia y decisión pendiente

Fecha: 21/10/2026. Estado: **análisis de la implementación; sin DDL autorizado ni aplicado**.

## Resultado de la auditoría

La captura de los nueve juicios originales ya usa `valoraciones_petersen_originales` y `soportes_valoracion_petersen`. El motor institucional consulta fuentes autorizadas, deriva C02 y evalúa los cuatro patrones o abstenciones. `ConsultarPetersenInstitucional` devuelve una instantánea de cálculo con `persistido=false`: no existe hoy una fila `evaluaciones_expertas` que conserve íntegramente *ese* resultado Petersen, sus cinco dominios, C01–C05, fuentes, exclusiones, condiciones y paquete de reglas al corte. Por tanto, el cálculo visible no tiene un `cod_evaluacion_experta` exacto que se pueda firmar o recuperar idénticamente.

La **tabla de revisiones ya existe y fue aprobada**: `revisiones_evaluacion_experta`. No hace falta una segunda tabla de firmas. Su FK obligatoria `cod_evaluacion_experta` impide legítimamente firmar un cálculo Petersen transitorio. `RevisarValoracionExperta` y `ConsultarRevisionesValoracionExperta` implementan actualmente firmas independientes para evaluaciones que sí cumplen su contrato de conservación y verificación; no se debe insertar una evaluación parcial sólo para conseguir una FK.

## Diccionario vigente de revisión, sin cambios

| Campo | Tipo / relación | Regla vigente |
|---|---|---|
| `cod_revision_experta` | PK `string(20)` | Identifica cada firma inmutable. |
| `cod_evaluacion_experta` | FK `string(20)` requerida → `evaluaciones_expertas` | Firma una evaluación exacta, no un residente o una consulta actual. |
| `disciplina` | `MEDICINA` o `PSICOLOGIA` | Dos cadenas separadas. |
| `cod_personal`, `cod_usuario` | FK requeridas → `personal`, `usuarios` | Se derivan de la sesión con competencia, permiso y Policy. |
| `fecha_hora_revision` | Fecha técnica de firma | No altera fecha clínica del resultado. |
| `decision` | `CONFIRMADA` o `RECHAZADA` | El resultado automático permanece igual. |
| `justificacion` | Texto no vacío | Fundamento profesional incluso en confirmación. |
| `cod_revision_anterior` | Self-FK nullable en la misma evaluación/disciplina | Rectificación por fila nueva; una raíz y una sucesora por cadena. |

Las FK son restrictivas; índice único de raíz por evaluación/disciplina; `UPDATE` y `DELETE` rechazados. Spatie Activitylog registra la operación; el dictamen clínico y su procedencia permanecen en la tabla de dominio. Esta entidad ya cuenta en las **104 tablas de negocio** (75 operativas + 29 expertas). No se añadieron tablas en esta entrega.

La opción de interfaz **«La información no me permite emitir una revisión concluyente»** no tiene valor autorizado en `decision`. Actualmente equivale a **no firmar todavía** y mantener `PENDIENTE`; no es una tercera firma conservada. Si la institución desea registrar formalmente ese acto, se necesitaría una decisión expresa para ampliar el catálogo/check de `decision` y definir su significado en la cadena histórica. Del mismo modo, `observaciones_clinicas` separadas y FKs de fuentes adicionales no existen en la tabla; hoy toda fundamentación de una firma válida pertenece a `justificacion`. No se almacenan en campos ajenos ni se simula una tercera opción.

## Brecha estructural previa a la firma de Petersen

El objeto a conservar tiene granularidades distintas: cabecera y corte de evaluación; membresía ordenada de juicios; fuentes y exclusiones; cinco estados de dominio; cinco requisitos, incluido C02 derivado; condiciones/reglas/versiones; clasificación o abstención; huella y versión del ejecutor. Reutilizar una sola columna de texto/JSON, `evidencias_evaluacion` sin su grano propio o una evaluación D-137 truncada rompería el contrato de reproducción y 4FN. El [dictamen de factibilidad bajo 104](DICTAMEN_FACTIBILIDAD_CONSERVACION_104_2026_10_21.md) muestra que el candidato reducido aún no supera la ida y vuelta relacional integral ni la transición poblada. El [ensayo de cinco relaciones](ENSAYO_CINCO_RELACIONES_104_2026_10_21.md) no equivale a aprobación estructural.

**Propuesta de extensión de persistencia, aún no instalable:** conservar la revisión en la tabla existente y vincularla sólo cuando un diseño nominal bajo el máximo de 104 demuestre `guardar → recuperar → replay` exacto de la evaluación compuesta. Antes de presentar DDL se necesitan: diccionario de cada grano/campo/PK/FK/cardinalidad y orden, restricciones de unicidad e inmutabilidad, plan de migración de datos y reversión, prueba SQLite y PostgreSQL desechables, y aprobación específica de la propietaria conforme a `database/AGENTS.md`. No se solicita aprobar un esquema incompleto.

## Consecuencia operativa actual

- Se pueden capturar/corregir/anular los nueve juicios originales con autoría y soportes; C02 permanece derivado.
- Se puede consultar el resultado Petersen **actual** y sus causas, sin presentarlo como historia conservada.
- Medicina y Psicología sí pueden revisar por separado las otras evaluaciones expertas **íntegras y revisables** mediante el flujo existente.
- **No se ofrece un botón para firmar Petersen mientras su resultado exacto sea transitorio.** La ausencia de firma no bloquea el cálculo; tampoco una firma de otra evaluación confirma el Petersen actual.
- El historial Petersen íntegro y sus revisiones vinculadas permanecen bloqueados por la conservación compuesta. No se alteró el inventario de 104 tablas ni se inventaron resultados.

Fuentes de decisión: [contrato C01–C05](CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md), [E–H adoptado](PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md), [revisiones aprobadas](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md), [máximo 104](DECISION_MAXIMO_104_TABLAS_2026_10_20.md).
