# Ensayo de consolidación de resultado y traza por criterio

Fecha: 20/10/2026. Estado: **propuesta técnica parcial; no autoriza DDL**.
El límite vigente es 100 tablas de negocio; la instalación actual contiene
104. Este ensayo examina dos de las consolidaciones posibles, sin convertir
ese ahorro en permiso para añadir otras relaciones.

## Grano y datos que deben conservarse

`evaluacion_criterios` identifica un criterio evaluado. Por sus restricciones
UNIQUE, `resultados_criterio` y `trazas_inferencia` tienen cada una **cero o una**
fila por criterio. Una ausencia de resultado o de traza debe seguir siendo
ausencia, nunca una fila inventada con valores NULL. Las fechas de resultado,
traza y criterio son distintas y deben permanecer distintas.

El ensayo de solo lectura
[`ensayar-criterios.php`](../../prototipos/orion-reduccion-100/ensayar-criterios.php)
proyecta ambas hijas a columnas nominales en memoria y reconstruye las tres
relaciones, con comparación estricta de todas las columnas actuales. La base
local de desarrollo produjo **PASS: 148 criterios, 147 resultados y 18 trazas**.
Por tanto, el caso de un criterio sin resultado y los 130 sin traza no se
rellenaron. El script solo imprime conteos, no contenido clínico; rechaza una
base que no sea la de desarrollo o una prueba desechable y no ejecuta SQL de
escritura. El control negativo con `APP_ENV=production` terminó con código 1
antes de leer filas. Es evidencia de representación de filas existentes, **no** prueba
una migración ni futuros estados.

## Diccionario mínimo candidato

Se conservaría `evaluacion_criterios` y se retirarían **dos** relaciones
después de probar su migración poblada. En `evaluacion_criterios` se añadirían
estas ocho columnas nullable, sin reutilizar columnas clínicas actuales:

| Origen | Columna candidata en el padre | Tipo | Condición |
|---|---|---|---|
| `resultados_criterio.cod_resultado_criterio` | `cod_resultado_criterio` | string(20), UNIQUE | NULL si no hubo resultado; alias histórico íntegro |
| `resultados_criterio.cod_valor_semantico` | `cod_valor_resultado` | string(20), FK a `valores_semanticos` | no NULL cuando hay resultado |
| `resultados_criterio.fecha_hora_determinacion` | `fecha_hora_resultado` | datetime | no NULL cuando hay resultado |
| `resultados_criterio.justificacion_original` | `justificacion_resultado_original` | text nullable | preserva NULL y texto exacto |
| `trazas_inferencia.cod_traza_inferencia` | `cod_traza_inferencia` | string(20), UNIQUE | NULL si no hubo traza; alias histórico íntegro |
| `trazas_inferencia.estado_inferencia` | `estado_traza` | string(30) | no NULL cuando hay traza |
| `trazas_inferencia.fecha_hora_inicio` | `fecha_hora_inicio_traza` | datetime | no NULL cuando hay traza |
| `trazas_inferencia.fecha_hora_fin` | `fecha_hora_fin_traza` | datetime nullable | preserva traza abierta |

Dos checks de grupo impedirían columnas de resultado sin
`cod_resultado_criterio` y columnas de traza sin `cod_traza_inferencia`, o
alias presentes sin sus valores obligatorios. La FK actual de
`evaluaciones_reglas.cod_traza_inferencia` se redirigiría al alias UNIQUE del
padre, manteniendo su valor, tipo, longitud, índice, restricción y política
`RESTRICT`. Las demás FK del criterio, incluida su autorreferencia, permanecen.
El grano sigue siendo un criterio: no se introduce lista, JSON/EAV ni columnas
repetidas por regla. El bloque opcional 0..1 no crea dependencia multivaluada
independiente; esta afirmación aún requiere el gate de 4FN independiente.

## Transición y reversión que faltan probar

1. En SQLite y PostgreSQL **desechables**, cargar historia sintética con
   criterio sin resultado ni traza, con resultado solo, con traza y varias
   evaluaciones de regla, NULL en justificación/fin y referencias cruzadas.
2. Añadir columnas y constraints nuevos; copiar las filas sin modificar sus
   códigos ni fechas; cambiar la FK entrante dentro de una transacción o una
   ventana de mantenimiento compatible; cotejar las tres relaciones por todas
   sus columnas y pertenencia de reglas antes de retirar las hijas.
3. Probar inserción directa inválida, IDs duplicados, FK a valor inexistente,
   traza ajena, fallo intermedio/rollback y dos escritores concurrentes. El
   escritor deberá crear un bloque opcional solo cuando el alias siga NULL;
   no podrá sobrescribir historia cerrada.
4. En reversión, reconstruir ambas hijas con sus códigos originales y
   reinstalar la FK de evaluaciones de regla antes de retirar las columnas.
   Si una fila nueva no es representable, fallar sin borrar datos. La reversión
   en una base con historia no debe ejecutar `DROP` destructivo a ciegas.

**Impacto aritmético:** 104 − 2 = 102 tablas de negocio. Aun si este candidato
supera las pruebas y recibe aprobación expresa, no alcanza el máximo de 100
ni resuelve la conservación compuesta. No se ha creado migración ni cambiado
el esquema operativo.
