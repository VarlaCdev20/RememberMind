# O.R.I.O.N.: límite de 100 tablas de negocio

Fecha: 20/10/2026. Estado: análisis de factibilidad; **no autoriza DDL**.

La propietaria fijó un máximo de 90–100 tablas de negocio y rechazó el
candidato de 15 relaciones que elevaba el inventario a 119. La lectura del
inventario local con `php artisan app:auditar-base-datos-remember-mind` confirmó
29 expertas presentes y 91 tablas físicas adicionales: 75 operativas y 16
técnicas, conforme al baseline vigente. Son **104 tablas de negocio**. El límite
de 100 ya exige retirar cuatro tablas con conservación comprobada, incluso si
no se añade ninguna.

## Consolidaciones que merecen ensayo, no retiradas aprobadas

| Origen | Destino posible | Indicio físico | Verificación pendiente |
|---|---|---|---|
| `resultados_criterio` | `evaluacion_criterios` | `UNIQUE(cod_evaluacion_criterio)` | identidad alternativa, FK al valor, fechas, filas sin resultado y reconstrucción exacta |
| `trazas_inferencia` | `evaluacion_criterios` | `UNIQUE(cod_evaluacion_criterio)` | estados y tiempos, FK entrante de evaluaciones de reglas, historia incompleta y concurrencia |
| `criterios_dominios_resultado` | `nodos_semanticos` | `UNIQUE(cod_nodo_criterio)` | nodos sin criterio, gobierno por versión, FK entrante de consecuencias y 4FN |
| `clasificaciones_dcl_instantanea` | `evaluaciones_expertas` | `UNIQUE(cod_evaluacion_experta)` | contrato experimental segregado, identidad alternativa y FK de motivos/reglas sin perder historia |
| `variables_expertas` | `nodos_semanticos` | `UNIQUE(cod_nodo_semantico)` | nodos sin variable, identidad alternativa, FK entrantes de condiciones/mapeos, versión y 4FN |

Los UNIQUE solo identifican una posibilidad 0..1; no demuestran que el
traslado sea correcto. Un ensayo debe cotejar todas las filas y valores NULL,
mantener los códigos históricos como alias únicos, probar FK entrantes y
reversión poblada en SQLite y PostgreSQL desechables. El [precedente aprobado
de cuatro consolidaciones](../base-de-datos/DECISION_REDUCCION_EXPERTA_2026_10_20.md)
no autoriza estas cuatro nuevas.

El conteo agregado local de esta lectura, sin consultar contenido clínico,
fue: 148 filas en `evaluacion_criterios`, 147 en `resultados_criterio` y
18 en `trazas_inferencia`; 205 nodos y 44 criterios de resultado; seis
evaluaciones y cinco clasificaciones. Esas diferencias confirman que los
bloques destino deben admitir ausencia, sin inventar una fila faltante. Los
conteos pueden cambiar y no sustituyen la comparación de identidades y datos.

Estas cuatro candidatas llevarían 104 a 100 **si** todas fueran viables y
aprobadas. Una quinta posibilidad 0..1 es `variables_expertas`, con 87 filas
frente a 205 nodos; aun si también resultara viable, el total bajaría a 99
y solo dejaría una plaza para relaciones nuevas. Ninguna de las cinco está
aprobada o probada. Las primeras cuatro no reservarían ninguna relación nueva
para las colecciones independientes de la evaluación histórica: membresía de
aplicaciones,
perfiles/entradas/predicados, dos ramas de conocimiento y reglas observadas,
soportes Petersen, exclusiones y constancias. Convertirlas en columnas
repetidas, texto/JSON o un depósito genérico de atributos rompería los
contratos de orden, NULL, procedencia o 4FN. Reutilizar una relación existente
solo es válido después de probar igualdad de grano y reconstrucción exacta.
En general, por cada `n` nuevas relaciones imprescindibles habría que
consolidar al menos `4 + n` de las 29 expertas actuales. El candidato
rechazado de quince habría exigido **19** consolidaciones, no cuatro.

Una revisión transitoria del [ensayo PostgreSQL desechable](ENSAYO_CONSERVACION_REDUCIDA_FASE_6_2026_10_20.md)
demostró representación tipada y replay de un caso compuesto mediante un
esquema aislado. El prototipo cambió después y requiere repetir la prueba sobre
una revisión estable. Es evidencia preliminar de contenido, **no** una propuesta
instalable ni una prueba de que el límite de 100 sea alcanzable. El candidato
reducido de 15 fue rechazado antes de migrar. El camino técnico siguiente es
encontrar y probar consolidaciones adicionales junto con una proyección
relacional de los granos históricos, sin alterar la BDD ni rebajar el requisito
de recálculo íntegro. Hasta entonces O.R.I.O.N. puede preparar y explicar
resultados, pero no se declara cerrado guardar→recuperar→reproducir el
compuesto instrumental/Petersen.
