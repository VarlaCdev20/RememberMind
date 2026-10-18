# Revisión de reutilización de D-137 antes de ampliar conservación

Fecha: 17/10/2026. Estado: ANTECEDENTE DE DISEÑO; ALTERNATIVA V2 POSTERIORMENTE APROBADA.

La propietaria aprobó después la alternativa de nueve tablas con la instrucción
«REALIZA ESTA INCORPORACION Y LUEGO IREMOS REDUCIENDO LAS TABLAS». Se aplica
[la decisión V2](../../../docs/base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md).
El resto de esta revisión conserva el análisis que motivó las alternativas.

La propietaria pidió una recomendación antes de autorizar las 16 tablas. Se
recomienda conservar esa propuesta como alternativa y resolver primero la
evolución del contrato D-137. Reducir el número de tablas sin preservar las
responsabilidades y sus FK no es un criterio suficiente.

## Qué ya representa el esquema

| Responsabilidad | Representación existente | Brecha precisa |
|---|---|---|
| Evaluación, residente y versión | evaluaciones_expertas | Falta corte original con zona/microsegundos y versión del contrato de conservación |
| Evidencia y fuente mapeada | evidencias_evaluacion -> mapeos_variables_fuente -> fuentes_datos_expertas | Falta valor bruto tipado, fecha clínica, episodio y metadatos originales de Domain |
| Criterio evaluado y evaluabilidad | evaluacion_criterios -> nodos_semanticos | No distingue el resultado de dominio del subcomponente ni conserva su jerarquía |
| Resultado categórico | resultados_criterio -> valores_semanticos | Faltan justificación exacta y catálogo experimental representable, separado de salidas G09 |
| Soportes por criterio | evidencias_criterio_evaluacion | No representa soportes sintéticos independientes que no tienen un mapeo operativo |
| Traza de reglas/condiciones/soportes | trazas_inferencia, evaluaciones_reglas, evaluaciones_condiciones_regla, evidencias_soporte_condicion | D-130 guarda condiciones planas de igualdad; no el AST técnico ampliado Y/O/NO/DISTINTO_DE |
| Relaciones entre evidencias | relaciones_evidencias_evaluacion | No equivale a perfil funcional, requisito DCL o su acreditación |

Fuentes verificadas: migraciones 120014 a 120023 del 08/10/2026, Models
EvaluacionExperta/EvidenciaEvaluacion/EvaluacionCriterio/ResultadoCriterio,
`PersistirEvaluacionTecnica`, `LecturaResultadosExperto` y los nuevos puertos.

## Reutilización compatible y límites

La cabecera y las FK de procedencia ya son reutilizables. Crear otro registro
de residente o evaluación para ocultar una incompatibilidad está prohibido.

También son reutilizables las relaciones de criterios/resultados y soportes
cuando sus nodos, valores, roles y versiones representen realmente la entrada.
No se debe insertar A/P como si fueran los valores descriptivos D-131/G09.
Modificar sus catálogos y consumidores requiere un contrato versionado explícito;
que una columna sea string no autoriza cualquier literal nuevo.

El cambio aparentemente pequeño de volver nullable
`evidencias_evaluacion.cod_mapeo_variable_fuente` para admitir referencias SIM
modificaría una invariante física congelada. Los consumidores actuales esperan
un mapeo real; no es una reducción segura automática de tablas.

Reutilizar `evaluacion_criterios` para subcomponentes exigiría definir su
jerarquía, nodos y resultados correspondientes, y actualizar lectores que hoy
esperan exactamente cinco dominios o criterios documentados. No basta con
guardar filas adicionales y descartarlas silenciosamente al recuperar.

## Recomendación

1. Mantener las 23 tablas y sus consumidores con su semántica vigente mientras
   se verifica el flujo experimental nuevo. Ese trabajo está implementado.
2. Diseñar una extensión versionada de conservación reutilizando cabecera,
   evidencias operativas, criterios, resultados y soportes cuando sean compatibles.
3. Añadir únicamente memoria original y las relaciones funcionales/DCL que
   no tienen representación; fijar expresamente cómo se conservan referencias
   SIM sin debilitar la FK de mapeo para todas las evidencias existentes.
4. Conservar el AST de reglas técnicas solo si se exige guardar reglas fuera
   del subconjunto D-130; no añadirlo por anticipación para la clasificación
   actual, que no atribuye reglas de conocimiento ficticias.
5. Presentar el diccionario físico definitivo y su prueba de reconstrucción
   para aprobación. Hasta entonces no cambiar catálogos ni habilitar guardar().

La propuesta de 16 tablas evita modificar D-137 y puede preservar cualquier
objeto admitido por su contrato de conservación. Su coste es duplicar parte de
criterios/resultados/soportes. Una extensión evolutiva podría reutilizarlos,
pero implica más cambios en contratos y consumidores existentes. La elección
debe comparar ambas consecuencias; no se afirma un número menor de tablas sin
haber cerrado esas decisiones.

## Contrato de aceptación independiente de la alternativa

- Reconstrucción exacta de memoria, vector y DCL tras una nueva instancia/proceso.
- null/false/0, tipos escalares, fechas, zonas y orden conservados sin coerción.
- Un solo objeto original por hecho compartido por memoria y soportes.
- Misma evaluación/residente/versión/corte en todas las relaciones.
- Transacciones, rollback e idempotencia; no escrituras parciales ni éxito falso.
- Sin JSON/EAV clínico, FK textuales sustitutas o inferencias A/P desde G09.
- Casos sintéticos y soporte técnico para fuentes institucionales; ejecución
  asistencial continúa cerrada hasta otro contrato y sus aprobaciones.
