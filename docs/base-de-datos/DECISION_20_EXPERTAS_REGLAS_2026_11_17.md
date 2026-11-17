---
title: "Reducción a 20 tablas expertas mediante registros de reglas tipados"
status: APPROVED_INSTALLED_LOCAL
last_reviewed: 2026-11-17
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [expert_rule_trace_and_snapshot_consolidation]
verification_scope: SQLITE_POSTGRESQL_LOCAL_INSTALLATION
runtime_verified: true
---

# Evaluaciones e instantáneas de reglas

La instrucción «baja de 22 a 20 expertas» autoriza las dos uniones revisadas. El destino tiene **60 operativas, 20 expertas y 16 técnicas, para un total de 96 tablas físicas**. El núcleo sin las extensiones de Gerencia tiene 91 tablas físicas.

| Orígenes | Destino | Identidad física |
|---|---|---|
| `evaluaciones_reglas` y `evaluaciones_condiciones_regla` | `evaluaciones_reglas` | `cod_elemento_evaluacion` |
| `reglas_aplicadas_dcl_instantanea` y `condiciones_regla_instantanea` | `reglas_aplicadas_dcl_instantanea` | `cod_elemento_regla_dcl` |

Cada fila conserva una regla o una condición identificada por `tipo_evento`. Los códigos originales y sus secuencias siguen identificando cada clase. Las claves generadas por clase permiten que las FK de propiedad, de árbol y de evidencias apunten al tipo correcto. La relación de una regla con varias condiciones se conserva en filas individuales. El grano y las dependencias de cada proyección original se mantienen. La unión disjunta evita introducir colecciones de hechos independientes en la misma fila, duplicar datos de la regla en cada condición o guardar el árbol como JSON o EAV.

Las evaluaciones mantienen la traza, la regla y la condición del conocimiento, sus estados, fechas y unicidades. La instantánea mantiene la clasificación propietaria, la referencia a la regla original cuando existe, su consecuencia tipada, el orden y los nodos del árbol AND/OR/NOT con literales tipados. Los vínculos padre/hijo se conservan por clave foránea al tipo correspondiente. La pertenencia, estructura, orden y profundidad del árbol siguen comprobándose al reconstruir la instantánea. Se mantienen las guardas de inmutabilidad de los modelos expertos.

Las consultas y escrituras utilizan las proyecciones tipadas de `BloquesExpertos`. Esto conserva la forma original de las filas y los códigos que consumen los lectores de resultados, las trazas, la explicación y el guardado institucional. Los modelos conservan sus relaciones. El cambio se limita a persistencia y lectura, con las reglas de inferencia, la autoría y los permisos vigentes.

## Migración y reversión

Las migraciones automáticas `2026_11_17_000003_consolidate_evaluaciones_reglas.php` y `2026_11_17_000004_consolidate_instantaneas_reglas.php` aplican el contrato congelado de las cuatro fuentes. La copia compara las proyecciones en ambos sentidos con `EXCEPT`, reconstruye las referencias y retira las tablas anteriores en la transacción. La validación de claves se realiza antes de los cambios de nombre, dado que el motor actualiza las FK entre las fuentes al renombrarlas.

La reversión restituye los nombres, columnas, restricciones, índices y filas de los cuatro orígenes. Las uniones de reglas deben revertirse antes que las migraciones históricas de sus dependencias. La aplicación y su esquema deben ejecutarse en la misma versión durante una reversión.

El ensayo local de ida y vuelta en PostgreSQL conservó exactamente **45 evaluaciones de regla y 161 evaluaciones de condición**. Las tablas de instantáneas de reglas estaban vacías en ese destino. La conservación de árboles poblados se ejercita en bases desechables, comparando la huella original de una clasificación con la recuperada después de revertir y reaplicar.

La instalación local se confirmó después de comprobar el catálogo del respaldo privado `union-expertas20-20261117-d2c70c1907e0a4a6.dump`. Se contrastaron las filas proyectadas, las tablas dependientes y las asignaciones de roles y permisos. El recuento posterior confirmó 96 tablas físicas, dos migraciones registradas, seis versiones de conocimiento recuperables y cero FK sin validar.

## Pruebas

- SQLite pasó los cinco casos finales de unión, reconstrucción del árbol, reversión histórica, explorador y resultado institucional. Fueron 80 comprobaciones.
- SQLite pasó los 11 casos focales de explorador y reversión histórica con 96 comprobaciones. La ejecución ampliada anterior registró 99 aprobadas, una omitida por requerir PostgreSQL y una que falló por el orden de reversión de un ensayo histórico. Ese caso se corrigió y pasó en la reejecución focal.
- PostgreSQL desechable pasó cuatro pruebas con 62 comprobaciones, incluida la conservación de un árbol compuesto tras revertir y reaplicar, el rechazo de referencias de otro tipo y la renumeración de entidades físicas.
- PostgreSQL desechable pasó los tres casos finales de unión, árbol compuesto y resultado institucional con 47 comprobaciones.
- `php scripts/verificar-consolidacion-experta.php` comprobó en solo lectura el inventario y 13 FK del contrato experto. Las proyecciones conservadas se contrastaron por igualdad de contenido con las fuentes anteriores.

Las pruebas se ejecutaron sobre el árbol compartido. Los ajustes en `ResultadoInstitucionalSQL.php`, `GerenciaCamposAvisoEstructuraTest.php` y `instalar-resultado-institucional.php` quedan en los archivos previos que aún no estaban en Git. La CI completa queda pendiente.
