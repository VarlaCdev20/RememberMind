# Ensayo nominal de entradas institucionales

## Membresías originales V2

`MembresiasSQL.php` implementa un submodelo independiente de dos relaciones con FK a originales inmutables y lectura exacta de tres colecciones. Sólo admite bases desechables; no es una migración ni conserva el DTO compuesto completo. El inventario hipotético de **102** corresponde exclusivamente a este submodelo sobre las 100 existentes; no acredita el presupuesto final de 110.

[Informe, límites, normalización y pruebas](../../docs/sistema-experto/MEMBRESIAS_HISTORICAS_SQL_2026_10_22.md). [DDL nominal, diccionario y mapeo](membresias-v2/). Regenerar con `php prototipos/orion-conservacion-nominal/generar-membresias.php`; comprobar con `--check`.

Los mapas de referencia nominal y completo descritos más abajo corresponden al corpus histórico Petersen V1. Rechazan campos V2 no declarados; no deben truncarse para aparentar compatibilidad.

**Estado al 22/10/2026: ejecutable en laboratorio, no instalable.** No es una migración ni un repositorio registrado en Laravel. El candidato de entradas añade 34 relaciones: sobre la base actual de 100 supondría **134 tablas de negocio**. Los 138 del informe y resumen anteriores son históricos sobre la base 104 del ensayo original. Ninguno satisface el máximo 110.

## Modo completo añadido el 22/10

`RepositorioNominal` admite ahora el mapa cerrado `mapa-completo.php`: guarda las fuentes y salidas del DTO y ofrece `recuperarCompleta()` sin ejecutar inferencia. El replay es otra operación. `ContenidoCompleto` reconstruye únicamente copias exactas desde propietarios conservados; trece casos sintéticos prueban contenido íntegro y huellas. El worker PostgreSQL verifica la lectura con el motor inaccesible.

Este modo es una **referencia SQL**, no el diseño institucional: 127 relaciones de contenido + 2 metadatos = 129 adicionales, **229 de negocio** si se instalara junto a las 100 actuales. Su máxima anchura es 412 columnas y no cierra las FK semánticas/terminales. `instalable=false` permanece explícito. No debe aprobarse ni integrarse para aparentar el cierre bajo 110.

[Informe completo, alcance y pruebas](../../docs/sistema-experto/RECUPERACION_SQL_COMPLETA_REFERENCIA_2026_10_22.md). [Diccionario, DDL, ER y resumen generado](referencia-completa/). Para comprobarlos:

```powershell
php prototipos/orion-conservacion-nominal/compilar-contenido-completo.php --check
php prototipos/orion-conservacion-nominal/generar-referencia-completa.php --check
php artisan test --filter='ConservacionCompletaSQLTest|ContratoNominalTest' --compact
```

Las secciones siguientes describen el modo histórico de **entradas**, que continúa sin lectura independiente de salidas. Los dos modos no deben confundirse.

El ensayo conserva las **entradas** de ambas ramas de `EVALUACION-INSTITUCIONAL-1`. El resultado completo se obtiene después mediante el ejecutor retenido F02 y se compara estrictamente con el emitido. Esto prueba replay, pero no almacenamiento y lectura independientes de las salidas y trazas completas.

## Código

| Archivo | Responsabilidad |
| --- | --- |
| `ContratoNominal.php` | Mapa cerrado, tipos y nombres de columna. Extrae la captura de soporte como registro propio; comparte sólo ocho parejas con estructura y propietario compatibles. |
| `EsquemaNominal.php` | DDL SQLite/PostgreSQL, propietarios, cardinalidad, orden, cierre e inmutabilidad; reversión exclusiva del laboratorio. |
| `ProyeccionNominal.php` | Escritor y lector de campos nominales; presencia, NULL, listas, mapas y tipos. Rechaza campos desconocidos y filas no consumidas. |
| `RepositorioNominal.php` | Destino desechable, transacción propia, identidad, idempotencia y recuperación consistente de entradas. |
| `worker-concurrencia.php` | Segundo y tercer proceso PostgreSQL para inserción tardía y solicitud repetida mientras la transacción principal cierra. Sólo admite el residente sintético. |
| `generar-diccionario.php` | Generación determinista y comprobación de los 14 artefactos de diseño. Sólo compila DDL en SQLite en memoria. |

## Ejecución

Desde la raíz del repositorio, sin cambiar `.env`:

```powershell
php prototipos/orion-conservacion-nominal/generar-diccionario.php --check
php artisan test --filter='ContratoNominalTest|ConservacionNominalCompuestaTest' --compact --stop-on-failure
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'ContratoNominalTest|ConservacionNominalCompuestaTest'
```

El runner PostgreSQL crea y elimina su propio cluster, con identidad `remembermind_test_<hex>_testing`, propietario no privilegiado y puerto efímero. El repositorio rechaza otras identidades. SQLite debe estar en memoria y sin bases adjuntas con archivos. Los scripts SQL generados son artefactos de inspección; no incluyen esa guarda de conexión y **no deben ejecutarse manualmente contra una base institucional**.

El replay binario heredado de F02 funciona en Windows con ZIP. Las pruebas que lo requieren se omiten explícitamente en otros sistemas; las pruebas de representación y compilación del diccionario se mantienen independientes. No se afirma replay Linux.

## Diccionario y cobertura

- `cobertura-783.csv`: destino exacto de las 783 rutas originales.
- `cobertura-51-rutas.csv`: los 51 puntos originales de colección/modelo.
- `modelos.csv`: 40 modelos lógicos, incluidos los 39 originales y la captura 1:1 extraída.
- `matriz-campos.csv`: 651 rutas locales de esos modelos; algunas se usan en ambas ramas. No es un recorte de las 783 ubicaciones.
- `diccionario-fisico.csv`, `fk.csv`, `indices.csv`: columnas, restricciones referenciales e índices efectivamente compilados en SQLite.
- `esquema-sqlite.sql`, `esquema-pgsql.sql`: DDL exacta del candidato ensayado.
- `reversion-sqlite.sql`, `reversion-pgsql.sql`: retirada de sus relaciones en laboratorio; no retira ninguna tabla instalada.
- `modelo-er.mmd`: propietarios y cardinalidades del candidato. No representa las referencias semánticas pendientes como si ya existieran.
- `referencias-conocimiento-pendientes.csv`: 44 referencias del conocimiento original cuyo identificador está copiado, pero cuya FK histórica aún no está reconectada.
- `resumen.json`: conteos y límites comprobables, sin datos clínicos.

## Límites de aceptación

1. Excede en 28 tablas el máximo institucional. No prueba una imposibilidad universal de diseñar otra solución de seis tablas.
2. Conserva entradas y huellas; necesita replay para reconstruir las salidas. No ofrece todavía lectura independiente del DTO completo almacenado.
3. Las FK de evaluación/propietario no equivalen a las 44 referencias semánticas históricas pendientes. Tampoco cierran por sí solas la correspondencia entre cada ID de membresía y su soporte original.
4. La cabecera de entradas tiene 207 columnas y la captura 173; la mantenibilidad requiere otra revisión antes de proponer el modelo institucional.
5. La reversión probada retira y restaura **el candidato añadido sobre una base migrada desechable**. No demuestra fusionar ni retirar tablas institucionales pobladas.
6. PostgreSQL impide confirmar una cabecera abierta con un trigger diferido. SQLite verifica el cierre completo mediante el repositorio y los triggers de cierre; no tiene un trigger equivalente al final de la transacción.
7. No hay integración de este candidato con permisos, revisiones o historial de producción. `persistido=false` sigue siendo correcto en la aplicación.

El informe completo de este corte está en [ENSAYO_NOMINAL_CONSERVACION_2026_10_22.md](../../docs/sistema-experto/ENSAYO_NOMINAL_CONSERVACION_2026_10_22.md).
