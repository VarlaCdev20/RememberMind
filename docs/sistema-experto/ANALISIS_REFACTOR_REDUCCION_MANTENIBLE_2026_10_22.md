# Refactor de tablas expertas: conteo y límites verificables

**Análisis previo del 22/10/2026.** Se conserva como fundamento de la propuesta. Posteriormente la propietaria autorizó las cuatro consolidaciones y quedaron implementadas, probadas e instaladas: **100 tablas de negocio**, con igualdad exacta de las 257 filas trasladadas. Véanse la [decisión y diccionario físico](../base-de-datos/DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md) y el [cierre de refactorización](CIERRE_REFACTOR_BLOQUES_2026_10_22.md). Los estados «candidato/no instalado» de las secciones siguientes describen el momento anterior a esa instalación. La conservación del DTO compuesto sigue pendiente y la quinta unión no se adoptó.

## Respuesta cuantificada

El inventario instalado sigue siendo **75 operativas + 29 expertas = 104 tablas de negocio**. Las 138 del [ensayo nominal](ENSAYO_NOMINAL_CONSERVACION_2026_10_22.md) eran un candidato no instalado, no el estado actual.

| Escenario | Operativas | Expertas | Total | Alcance real |
| --- | ---: | ---: | ---: | --- |
| Actual | 75 | 29 | **104** | Instalado; conservación compuesta todavía pendiente. |
| Cuatro consolidaciones con dependencia 0..1 respaldada por UNIQUE | 75 | 25 | **100** | Base refactorizada candidata; transición conjunta no probada ni instalada. |
| Las anteriores más una unión tipada de vínculos | 75 | 24 | **99** | Alternativa con mayor riesgo semántico, no una quinta relación 1:1. |

**100 y 99 no son conteos finales demostrados del sistema completo.** Después de consolidar deberán incorporarse sólo las relaciones que requiera la conservación completa y que no tengan un destino legítimo existente.

La cuenta final sería **100 + N**, o **99 + N** si la quinta unión supera las pruebas. Manteniendo el máximo de 110, quedarían diez u once cupos respectivamente. El valor de N todavía no está cerrado: dar ahora un número exacto de tablas finales equivaldría a presentar una estimación como diseño probado.

Las cuatro consolidaciones históricas de 108 a 104 ya están instaladas; **no se descuentan otra vez**. Véase la [propuesta anterior aprobada](PROPUESTA_REDUCCION_CUATRO_TABLAS_2026_10_20.md).

## Cuatro candidatas de menor riesgo

| Relación actual | Destino candidato | Evidencia física de singularidad | Disminución |
| --- | --- | --- | ---: |
| `resultados_criterio` | Bloque de resultado en `evaluacion_criterios` | UNIQUE `se19_u1` sobre `cod_evaluacion_criterio` | 1 |
| `trazas_inferencia` | Bloque de traza en `evaluacion_criterios` | UNIQUE `se20_u1` sobre `cod_evaluacion_criterio` | 1 |
| `variables_expertas` | Bloque de variable en `nodos_semanticos` | UNIQUE `se06_u1` sobre `cod_nodo_semantico` | 1 |
| `clasificaciones_dcl_instantanea` | Bloque de clasificación en `evaluaciones_expertas` | UNIQUE sobre `cod_evaluacion_experta` | 1 |

Las migraciones que respaldan estas afirmaciones son `2026_10_08_120019_create_resultados_criterio_table.php`, `2026_10_08_120020_create_trazas_inferencia_table.php`, `2026_10_08_120006_create_variables_expertas_table.php` y `2026_10_17_200000_incorporate_conservacion_experta_v2.php`. Se contrastó también el inventario migrado de `prototipos/orion-conservacion-110/inventario-120-tablas.csv`.

Una fila de propietario puede no tener resultado, traza, variable o clasificación. La consolidación debe conservar cada bloque de manera independiente, incluyendo su ausencia; no exigir simultaneidad ni generar campos predeterminados. Cada identidad antigua debe seguir disponible como clave alternativa UNIQUE. Las FK entrantes deben apuntar a esas identidades preservadas, con el mismo alcance de versión y evaluación.

Por ejemplo, `evaluaciones_reglas` depende de la identidad de traza. `condiciones_regla_experta` y `mapeos_variables_fuente` dependen de la variable. Hechos, motivos y reglas aplicadas dependen de la clasificación. El retiro de una tabla sólo procede después de trasladar sus filas, reconectar esos vínculos y demostrar el retorno exacto.

La relación 0..1 hace razonable estudiar estas consolidaciones sin convertir colecciones en columnas. No acredita por sí sola que se hayan migrado sus consumidores, preservado 4FN o superado el cierre institucional.

## Quinta candidata, con cautela distinta

`evidencias_soporte_condicion` podría integrarse con `evidencias_criterio_evaluacion` mediante variantes cerradas de participación. Esa unión no está respaldada por una dependencia 1:1 entre ambas tablas. Sus propietarios y reglas de participación son diferentes.

Para aceptar la reducción a 99 hace falta demostrar el significado común de la relación, FK específicas por variante, checks excluyentes, orden y compatibilidad de todos los lectores. No utilizar una columna `tipo/id` sin FK ni mezclar varios vínculos en una celda. Se recomienda estudiar primero las cuatro candidatas anteriores; la quinta es opcional.

## Forma de completar la conservación con mantenimiento razonable

1. **Unificar las raíces reales existentes.** Reutilizar evaluación, criterio, hecho, regla ejecutada y revisión según su responsabilidad. Conservar resultados y trazas singulares como bloques del propietario cuando se pruebe la equivalencia.
2. **Versionar el conocimiento una sola vez.** Estudiar definición inmutable y membresía/estado observado por evaluación. No duplicar automáticamente doce tablas de conocimiento ni leer definiciones actuales para reproducir una evaluación antigua. Esta reutilización necesita una prueba histórica real.
3. **Distinguir el original de la representación adquirida.** Una observación, una respuesta, una evidencia extraída y un juicio no se vuelven intercambiables al cambiar el nombre de una tabla. Se pueden compartir registros idénticos entre ramas mediante FK, conservando las proyecciones de tipo distintas cuando el DTO las requiera.
4. **Mantener colecciones en filas hijas.** Preguntas sin respuesta, soportes, condiciones, exclusiones y modalidades independientes necesitan relaciones y orden; se reutilizan destinos sólo cuando coincidan grano y responsabilidad.
5. **Conservar salida y entradas.** El resultado histórico debe poder leerse sin recalcularse. El replay se comprueba por separado con conocimiento y ejecutor retenidos.
6. **Medir mantenimiento además del conteo.** Revisar anchura de tablas, proporción de columnas opcionales, FK por variante, claridad de nombres, consultas y alcance de las correcciones. La cabecera de 207 columnas del ensayo nominal no se adopta como solución definitiva.

## Prueba exigida antes del conteo final

El [ensayo previo de cinco consolidaciones](ENSAYO_CINCO_RELACIONES_104_2026_10_21.md) contiene pruebas aisladas, entre ellas resultado/traza en PostgreSQL. No es una migración conjunta aceptada. Esta revisión sólo hizo inspección estática y no repitió las suites ni modifica su alcance histórico.

Para poder afirmar «quedan X tablas y todo funciona» hace falta:

- Diccionario exacto de las cuatro consolidaciones y de cada relación adicional necesaria, con todas sus FK y consumidores.
- Transición de filas sintéticas pobladas, preservando identificadores, NULL, orden, historia y versión; reversión íntegra.
- Guardar y leer el DTO completo sin consultar fuentes clínicas actuales, más replay determinista separado de ambas ramas.
- Corrección de las 44 referencias semánticas pendientes y de los vínculos terminales de membresía identificados en el ensayo nominal.
- Pruebas de idempotencia, cierre concurrente, autorización, archivo de reglas, revisiones independientes e integración Laravel.
- Gate independiente de normalización y seguridad; aprobación específica del esquema antes de instalarlo, conforme a `database/AGENTS.md`.

**Recomendación:** adoptar **100 como base candidata de refactor**, conservando las 75 operativas y reduciendo de 29 a 25 las expertas existentes. Cerrar después N con el mapa completo y el ensayo, dentro del máximo 110. No comprometer 99 como cifra final ni instalar el candidato de 138.
