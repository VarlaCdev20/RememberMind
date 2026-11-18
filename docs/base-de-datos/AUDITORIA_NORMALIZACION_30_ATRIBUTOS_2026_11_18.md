# Auditoría de normalización y límite de 30 atributos

El PostgreSQL local de RememberMind tiene 100 tablas físicas. Son 64 operativas, 20 expertas y 16 técnicas. Siete superan los 30 atributos. Este informe documenta el estado observado y dos cambios propuestos. El esquema instalado corresponde a la [decisión vigente del reparto de valoraciones](DECISION_REPARTO_VALORACIONES_2026_11_18.md).

## Dependencias comprobadas

La 4FN exige que toda dependencia multivaluada no trivial tenga una superclave como determinante. La 5FN exige que las dependencias de reunión estén implicadas por claves candidatas. Ambas se evalúan sobre las reglas de los datos admisibles. El ancho de una tabla y la ausencia de contradicciones en los datos actuales responden preguntas distintas. [Fagin, cuarta forma normal](https://research.ibm.com/publications/multivalued-dependencies-and-a-new-normal-form-for-relational-databases) y [Fagin, quinta forma normal](https://research.ibm.com/publications/normal-forms-and-relational-database-operators).

| Relación | Dependencia o hallazgo | Dictamen y tratamiento |
| --- | --- | --- |
| `valoraciones_profesionales` | Las dos variantes exigen una atención. La FK compuesta garantiza que `cod_atencion` determina `cod_residente`. Varias valoraciones pueden pertenecer a una misma atención. | La dependencia funcional tiene un determinante que no es superclave. Incumple 4FN y por tanto 5FN. Propuesta de 30 a 29 atributos al consultar el residente por la atención. |
| `valoraciones_psicopedagogicas` | Psicología requiere atención y el residente se obtiene de ella. Los 424 registros psicológicos coinciden con su atención. Pedagogía conserva su propio residente y carece de atención clínica. | La redundancia es condicional a Psicología. Se debe resolver por subtipo, preservando el residente directo de Pedagogía. |
| `documentos`, `mediciones_clinicas`, `registros_observaciones_cuidado` | La FK de la atención verifica el residente cuando hay atención. Algunos tipos admiten registrar el residente sin ella. Los 30 documentos actuales dependen del residente directo. | Redundancia condicional. Una sustitución global por la atención perdería el vínculo de las filas sin atención. |
| `mediciones_clinicas` | El guardado habitual calcula `imc` a partir de peso y talla. La columna admite datos y ausencia de valor propios y otros recorridos aceptan IMC. La muestra local carece de antropometrías para contrastar la equivalencia histórica. | Candidato a cálculo para registros con peso y talla confirmados. Retirar la columna requiere un contrato explícito para el cálculo, el redondeo, la ausencia y la conservación de importaciones. |
| `registros_consumo` | `cantidad` conserva el texto original. En 135 filas su valor difiere de `cantidad_cualitativa` o esta última está vacía. Los lectores históricos y dos recorridos de escritura conservan esos campos. | Mantener el contenido de `cantidad` y `caracteristica` en el detalle tipado. El volumen medido y una estimación cualitativa tienen significados propios. |
| `valoraciones_enfermeria_preadmision` | `cod_usuario_registro` y `cod_personal_valorador` registran la cuenta del acto y el perfil profesional. En las dos filas coinciden con la relación actual de personal y usuario. | Conservar ambas identidades hasta demostrar que el vínculo se puede consultar para la fecha histórica del acto. |
| `hechos_instantanea_experta` | El hecho y las caracterizaciones Cognitiva e IADL se relacionan por `cod_hecho_instantanea`. El código de funcionalidad determina la evaluación en las filas que lo contienen, según FK compuesta e identidad única. | Revisar esa dependencia en el subtipo IADL. Conservar fecha original, valor, orden, identidad de la fuente y referencias históricas de instrumentos. |
| `evaluaciones_expertas` | La cabecera, el resultado institucional emitido y la clasificación conservada contienen los hechos registrados de cada ejecución. El resultado institucional tiene un solo bloque por evaluación. | Las distintas versiones, dominios, resultados y huellas tienen significados propios. La separación por clave permite reducir el ancho. Una relación 1 a 0/1, por sí sola, no demuestra una infracción de 4FN o 5FN. |
| `horarios_prescripcion`, `programaciones_cuidado` | `dias_semana` se interpreta como una colección separada por texto. La base local carece de valores explícitos para probar casos con varios días. | Si el día se modela como dato consultable, representa un conjunto codificado en una columna. Una migración relacional precisa conservar la diferencia entre todos los días, la selección explícita y los valores históricos que el lector no reconoce. |

El inventario de claves, restricciones y columnas generadas cubrió las 100 tablas. El análisis semántico se concentró en las siete más anchas y en las dependencias anteriores. Hay una infracción global demostrada en `valoraciones_profesionales` y casos condicionales en otras relaciones. La certificación tabla por tabla exige completar el inventario de dependencias de reunión del dominio. La prueba de reconstrucción descrita a continuación acredita solamente el reparto de los datos observados.

La comparación de definiciones encontró 22 índices secundarios con una copia equivalente. Sus definiciones coinciden en tabla, método y columnas. Se excluyeron índices únicos, parciales, de expresión, inválidos o usados para identidad de réplica o agrupamiento. Esto permite preparar una limpieza física de índices con inventario estable y revisión de reversiones anteriores. Su retirada no reduce columnas ni cambia la forma normal.

## Propuesta para alcanzar 30 atributos

Cada detalle propuesto usa como clave primaria la clave de la fila principal y declara una clave foránea restrictiva al registro original. El conteo del detalle incluye esa clave. Los atributos se trasladan con sus tipos y valores. Las restricciones de cada subtipo deben seguir siendo efectivas durante las escrituras directas y en las operaciones de la aplicación.

| Relación actual | Actual | Principal | Relación propuesta | Detalle | Filas comparadas |
| --- | ---: | ---: | --- | ---: | ---: |
| `hechos_instantanea_experta` | 49 | 25 | `caracterizaciones_hecho_experto` | 25 | 149 |
| `registros_observaciones_cuidado` | 44 | 28 | `detalles_movilidad_cuidado` | 17 | 305 |
| `evaluaciones_expertas` | 43 | 27 | `resultados_institucionales_expertos` | 17 | 3 |
| `valoraciones_enfermeria_preadmision` | 43 | 29 | `hallazgos_fisicos_preadmision` | 15 | 2 |
| `documentos` | 36 | 25 | `detalles_documento_clinico` | 12 | 30 |
| `mediciones_clinicas` | 33 | 23 | `detalles_valoracion_dolor` | 11 | 270 |
| `registros_consumo` | 32 | 15 | `detalles_eliminacion_cuidado` | 18 | 345 |

- `caracterizaciones_hecho_experto` recibe los dos códigos y los campos `cognitiva_*` e `iadl_*`. Conserva la relación al hecho y la garantía de que la funcionalidad pertenece a la misma evaluación. Antes de cambiar esa FK se debe preservar la prueba de pertenencia desde las nuevas posiciones.
- `detalles_movilidad_cuidado` recibe marcha, equilibrio, traslado, tipo de apoyo, dispositivo, fatiga, riesgo de caída, motivo, actividad, distancia, dolor, mareo, disnea, debilidad, cambio habitual y tolerancia. El `observacion` compartido permanece en la cabecera.
- `resultados_institucionales_expertos` recibe los 14 campos `institucional_*`, la versión Petersen y la cuenta que ejecutó. La cabecera conserva el estado, el residente, las fechas, las referencias de clasificación y las relaciones de hechos y revisiones. La integridad del resultado exige una escritura atómica y el mismo contrato de inmutabilidad.
- `hallazgos_fisicos_preadmision` recibe los bloques de dolor, piel y heridas, las seis constantes vitales y peso/talla. Los indicadores negativos siguen siendo valores registrados. Los requisitos y límites vigentes pasan con los campos.
- `detalles_documento_clinico` recibe estudio, atención, profesional, título, descripción, formato, tamaño, fecha clínica, origen, herida y curación. La cabecera mantiene ambas identidades tipadas porque otras relaciones dependen de ellas. La relación atención/residente se conserva mediante validación relacional en la transición.
- `detalles_valoracion_dolor` recibe intensidad, ubicación, tipo, duración, desencadenante, intervención, respuesta, valoración anterior, frecuencia y factores de alivio. La reevaluación conserva el mismo residente y la referencia exclusiva al tipo Dolor.
- `detalles_eliminacion_cuidado` recibe los 17 campos específicos de eliminación, incluidos los históricos. La tolerancia de ingesta e hidratación, el estado y la observación común permanecen en la cabecera.

El ensayo PostgreSQL usó una transacción de solo lectura. Para cada relación proyectó las dos partes, recuperó las columnas por la clave original con una unión externa y comparó ambos sentidos mediante `EXCEPT ALL`. Se reconstruyeron las **1.104 filas sin diferencias**, incluidos valores nulos y filas sin detalle. Documentos clínicos y caracterizaciones expertas carecían de filas que ejercitaran sus detalles en esta base. La implementación deberá probar esos casos con datos sintéticos, incluyendo claves foráneas, lecturas, escrituras, permisos, reversiones e inmutabilidad. El ensayo valida columnas y recuperación de las filas actuales. Todavía se requiere una migración probada para comprobar el comportamiento en el sistema.

Este reparto propone **107 tablas físicas**, 69 operativas, 22 expertas y 16 técnicas. Alcanzar simultáneamente 30 atributos y 100 tablas exige encontrar siete reducciones netas compatibles en otras relaciones o una distribución distinta con evidencia equivalente. El reparto de anchura y la auditoría de dependencias tienen criterios de aceptación separados.

## Corrección concreta con 100 tablas

Retirar únicamente `valoraciones_profesionales.cod_residente` deja esa relación con 29 columnas y mantiene el total de 100. Su atención es obligatoria para Funcional y Nutrición y permite recuperar el residente con la clave foránea existente. La transición debe sustituir el soporte de la FK compuesta por la relación obligatoria y proteger el contexto histórico frente al traslado de la atención. El código conserva el valor derivado para filtros, reportes, exportaciones, autorización y adaptadores del experto. La reversión debe reconstruir el campo y restablecer sus garantías. La base local carece de filas funcionales o nutricionales. Los ejemplos poblados, el traslado concurrente de una atención y una valoración con contexto ajeno requieren pruebas antes de instalar.

La corrección puntual fue autorizada e instalada después de esta auditoría. El reparto de las otras siete tablas sigue como propuesta. [Decisión y verificación del residente derivado](DECISION_RESIDENTE_VALORACIONES_PROFESIONALES_2026_11_18.md).
