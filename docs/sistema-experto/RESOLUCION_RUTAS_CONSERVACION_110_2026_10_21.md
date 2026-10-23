# O.R.I.O.N. — resolución verificable de rutas de conservación

**Corte:** 21/10/2026. **Estado:** ningún candidato de máximo 110 tablas ha superado todavía el gate de ida y vuelta integral. No es un diccionario DDL aprobable. No se modificó la BDD institucional.

## Resultado ruta por ruta

La [matriz de 51 rutas](../../prototipos/orion-conservacion-110/matriz-resolucion-51-rutas.csv) se genera con [`verificar-rutas.php`](../../prototipos/orion-conservacion-110/verificar-rutas.php). Para cada ruta indica modelo, rama, grano, propietario lógico y del oráculo, cardinalidad, identidad del oráculo, número de campos del contrato, destino candidato, **todas las columnas y FK físicas** del destino instalado, estado, ensayo parcial pertinente, incompatibilidad individual y prueba de cierre pendiente. Se coteja con los inventarios de 120 tablas físicas migradas en SQLite `:memory:`, 39 modelos, 51 colecciones y 783 rutas de campo. El [resumen generado](../../prototipos/orion-conservacion-110/resumen-resolucion-rutas.json) informa **51 rutas con bloqueo individual, 9 con ensayo parcial y 9 cuyo destino candidato no está instalado**. Ninguna figura como resuelta en un candidato de 110 porque aún no existe un escritor/lector integral de ese candidato. Una prueba parcial no se cuenta como conservación institucional.

La primera [matriz de auditoría](../../prototipos/orion-conservacion-110/matriz-auditoria-rutas.csv) marcaba 46 rutas con brechas iniciales. Este segundo gate conserva esas 46 y especifica también por qué las cinco restantes (`m001`, `m003`, `m013`, `m021`, `m023`) no acreditan conservación exacta: cabecera incompleta, motivo con propietario de clasificación distinto y vínculo de dominios que exige dos evidencias extraídas. Esta reclasificación **no demuestra que cada ruta requiera una tabla nueva**; impide contar una coincidencia nominal como ida y vuelta probada.

### Bloqueos físicos por grano

| Modelos/rutas | Prueba física o de contrato | Estado |
| --- | --- | --- |
| `m008`, `m009`, `m015`, `m016` | `relaciones_semanticas` almacena definiciones de la red; contrato/modalidad adquiridos tienen evaluación, rama, propietario, clave y orden. B01 ensayado parcialmente en SQLite. | Sin destino institucional compatible probado. |
| `m005`, `m025` | Pregunta declarada puede carecer de respuesta y ser compartida por dos ramas. La tabla candidata `entradas_perfil_instrumental` aún no está instalada. B02 ensayado en SQLite y PostgreSQL. | Sin instalación ni composición integral. |
| `m018` | Membresía requiere juicio y soporte original de la **misma evaluación**, orden y multiplicidad. `evidencias_criterio_evaluacion` no tiene esas FK compuestas instaladas. B03 ensayado parcialmente en SQLite. | Sin integración con D-137 y ORION-SNAP-2. |
| `m002`, `m014`, `m017`, `m019`, `m020`, `m022` | `evidencias_evaluacion` exige `cod_mapeo_variable_fuente` y `cod_registro_fuente` no propios de captura, constancia, grupo de membresías, procedencia, juicio o soporte original. | Reutilización incompatible. |
| `m006`, `m026` | `hechos_instantanea_experta` conserva hechos ORION-SNAP-2, no respuesta bruta vinculada a aplicación/pregunta/opción. Ensayo de 96 respuestas tipadas pasó en SQLite aislado. | Falta destino relacional institucional. |
| `m003`, `m013`, `m023` | `motivos_dcl_instantanea` exige FK `cod_clasificacion_dcl_instantanea`; el motivo de captura/adquisición pertenece a otro propietario. | No se puede usar la tabla sin cambiar grano/FK. |
| `m021` | `relaciones_evidencias_evaluacion` exige dos FK a evidencias extraídas; un dominio revisado pertenece a un juicio Petersen original. | Destino semánticamente incompatible. |
| 24 rutas `k_*` | La definición vigente no fija qué filas, valores y estados observó **cada rama** al corte; `k_criterios_dominios_resultado` y `k_variables_expertas` además usan alias de `nodos_semanticos`. | Faltan membresía, sello, protección histórica y replay desde lo retenido. |
| Rutas a `perfiles_instrumentales_versionados`, `predicados_perfil_instrumental`, `vinculos_declarativos_instantanea` | Tablas candidatas no instaladas; selector, medida, predicado y vínculo poseen propietarios y checks diferentes. | Solo prototipos parciales. |
| `m001` | `evaluaciones_expertas` tiene 16 columnas físicas y una FK a versión; la raíz compuesta del oráculo tiene 112 rutas de campo y debe fijar ambas ramas, release, huellas, corte y cierre. | No existe escritura/lectura completa. |

La matriz expone la evidencia individual, incluidos nombres de FK y columnas, para que estos grupos no oculten las rutas de cada rama. El oráculo de 42 tablas posee `cod_evaluacion_experta`, `cod_fila`, propietario, orden y clave tipada por colección; esos identificadores **no son columnas aprobadas** del candidato institucional.

## Alternativas y presupuesto

| Alternativa | Tablas de negocio | Evidencia | Razón por la que no se declara viable |
| --- | ---: | --- | --- |
| A. Reutilización con máximo seis relaciones nuevas | 104–110 | Nueve ensayos parciales; inventario de destinos actuales. | Cinco relaciones candidatas dejan solo una para release. El mapa dirige 177 rutas escalares distintas a `evidencias_evaluacion`, 62 a `hechos_instantanea_experta` y 56 a `nodos_semanticos`, aunque esos destinos tienen granos incompatibles. No hay diccionario nominal, checks por subtipo ni SQL integral. Esos conteos miden presión del **mapa actual**, no una cota mínima universal de columnas. |
| B. Retirar cinco y añadir once | 110 | Cuatro retiros se ensayaron separadamente con datos sintéticos. | `trazas_inferencia` tiene 1 FK entrante, `clasificaciones_dcl_instantanea` 3 y `variables_expertas` 3; hay consumidores de código. Falta transición **conjunta poblada**, equivalencia de grano de once relaciones, reconexión de FK, lectura, replay y reversión. No se ejecutó ningún retiro. |
| C. Referencias amplias | 119 con quince nuevas; 129 con veinticinco nuevas | El oráculo F02 de 42 tablas de ensayo hace ida y vuelta y replay en PostgreSQL desechable. | Superan el máximo por 9 y 19 respectivamente; F02 demuestra representabilidad, no diseño mantenible bajo 110. No se afirma que 119 sea un mínimo matemático. |

El crecimiento de columnas, índices y FK institucionales para A/B **no puede darse como diccionario final** sin elegir variantes cerradas y probarlas. La proyección literal de campos escalares a columnas técnicas del oráculo generaría tablas anchas y no prueba 4FN por subtipo. Elegir una cifra de tablas sin demostrar la dependencia funcional, la unicidad y los propietarios reproduciría el bloqueo.

## Ensayos ejecutados en bases desechables

```powershell
php prototipos/orion-conservacion-110/verificar-rutas.php
php prototipos/orion-consolidacion-104/ensayar-dto-compuesto-sqlite.php
php prototipos/orion-consolidacion-104/ensayar-identidad-preguntas-compuestas-sqlite.php
php prototipos/orion-consolidacion-104/ensayar-respuestas-dos-ramas-sqlite.php
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'ReferenciaF02PostgresDesechableTest|ConservacionB02PostgresTest|ConservacionCierrePostgresTest|ConservacionCriterioPostgresTest'
```

En este corte: matriz **51/51 rutas clasificadas**, SQLite B01/B03 compuesto **PASS** (tres contratos instrumentales, uno Petersen, nueve juicios con soportes), B02 compartido **PASS** (cinco aplicaciones/preguntas sin respuesta), respuestas **PASS** (96 filas tipadas). PostgreSQL desechable: **4 pruebas, 58 aserciones, PASS**, incluido F02 completo; su cluster fue detenido. El oráculo F02 compara DTO y replay en el esquema amplio; **ninguno de esos PASS acredita el límite de 110**. No se ha ejecutado cierre concurrente, migración poblada, reversión ni replay de un candidato A/B completo porque todavía no existe tal candidato SQL. `persistido=false` permanece.

## Decisión técnica

No se presenta DDL para aprobación: el candidato A no representa las rutas señaladas y B carece de transición conjunta demostrada. La matriz especifica la prueba que cerraría cada ruta. El próximo diseño debe producir columnas nominales y FK para esos granos, ejecutar todas esas pruebas en **un mismo** escritor/lector SQL bajo máximo 110, luego comparar las 783 rutas y las dos ramas contra el oráculo F02. Si no supera ese gate, corresponde cuantificar un nuevo límite o decidir una reducción de alcance institucional; ninguna de esas decisiones está asumida aquí.

## Incremento ejecutable posterior a este dictamen

Se añadió [`ensayar-sqlite-completo.php`](../../prototipos/orion-conservacion-0_2/ensayar-sqlite-completo.php), que reutiliza el **escritor y lector SQL completo** `ProyeccionTipada` sobre SQLite `:memory:`. Se parametrizó solo el prefijo de esquema de esa clase, sin alterar el repositorio Laravel. En este oráculo, B01 comparte una tabla física para contratos adquiridos de ambas ramas y otra para sus modalidades; cada fila conserva propietario, rol de colección y orden. Son **38 tablas de ensayo** para 39 modelos y 51 rutas, frente a 40 tablas SQLite sin esa fusión y 42 tablas de la referencia PostgreSQL original. No se trata de una migración ni de una solución bajo 110: añadir 38 relaciones al inventario instalado daría 142 tablas de negocio, sin contar aún todos los controles de release y anclaje de la referencia PostgreSQL.

El script ejecuta seis variantes sintéticas: patrón amnésico, falta de C03, ausencia de gobierno, ausencias/NULL/orden, varias fuentes documentales y vínculo interpretativo. En cada una guarda filas SQL, recupera entradas, compara forma canónica, reconstruye las dos ramas y compara DTO/huella originales con el replay. También rechaza escritura posterior al cierre, edición de la cabecera cerrada y una hija de propietario ajeno; comprueba rollback sin fila parcial. La nueva [prueba PHPUnit](../../tests/Unit/ReferenciaF02SqliteDesechableTest.php) ejecuta el script: **1 prueba, 3 aserciones, PASS**. La referencia PostgreSQL original se volvió a ejecutar sin regresión: **1 prueba, 5 aserciones, PASS** en cluster desechable.

Este avance prueba que una fusión B01 legítima funciona dentro del **oráculo amplio** y agrega SQLite a la demostración de ida y vuelta completa. No satisface todavía los checks institucionales de versión, pertenencia y 4FN de un candidato A/B de máximo 110; tampoco demuestra una transición poblada/reversión de tablas existentes ni autoriza `persistido=true`.

## Verificación del recorrido profesional relacionada con este corte

La batería Laravel focalizada avanzó **193 pruebas y 1579 aserciones** antes de detectar un fallo en `RevisionValoracionExpertaTest`: Medicina recibía HTTP 403 al montar los resultados, porque la reconstrucción instrumental comprueba cinco permisos de lectura y el rol tenía solo `aplicaciones_instrumento.ver`. Se completaron en `RolesAndPermissionsSeeder` los cuatro permisos **ya existentes** de respuestas, instrumento, preguntas y opciones para Medicina. No se creó un permiso, tabla o competencia de escritura, ni se ejecutó el seeder contra la BDD institucional. Tras el ajuste, `RevisionValoracionExpertaTest` pasó **16 pruebas y 55 aserciones**, incluido el cambio de evaluación con una revisión abierta. La prueba de revocación de `respuestas_instrumento.ver` pasó **1 prueba y 3 aserciones**: una cuenta sin ese permiso sigue sin consultar ni exponer las fuentes. El ensayo SQLite completo, Pint, `npm test` (226 aprobadas, dos omitidas) y `npm run build` pasaron. La batería Laravel focalizada completa **no se volvió a ejecutar** tras ese ajuste; no se registra como aprobada.
