# O.R.I.O.N. — integración F02 sobre las migraciones institucionales

**Estado:** ensayo ejecutable en SQLite `:memory:`; **no es un diseño físico aprobable ni una migración institucional**. Corte 21/10/2026.

## Cambio comprobado

El [ensayo F02](../../prototipos/orion-conservacion-0_2/ensayar-sqlite-completo.php) acepta `--base-institucional`. Fuerza SQLite en memoria antes de iniciar Laravel, rechaza una configuración en caché o una conexión distinta, instala las migraciones reales y confirma sus **120 tablas físicas**: 104 de negocio y 16 técnicas. Reutiliza la cabecera existente `evaluaciones_expertas`, con sus FK reales a `residentes`, `versiones_modelo_experto` y `personal`. Solo en esa base desechable agrega `huella_compuesta` y `estado_conservacion`, y crea las 37 relaciones F02 restantes. Los identificadores de residente, versión, usuario y evaluación de la cabecera coinciden con los dos contextos del DTO sintético. Ningún registro institucional se lee ni se modifica.

La [matriz generada de integración](../../prototipos/orion-conservacion-0_2/matriz-integracion-38-relaciones.csv) identifica **los 39 modelos y sus 51 rutas**, las 37 tablas F02 que se crean, las dos fusiones B01 ya ensayadas y los destinos institucionales evaluados. Se regenera con `php prototipos/orion-conservacion-0_2/generar-matriz-integracion.php`. Cada fila indica expresamente que la prueba completa acredita la **referencia F02**, no una reutilización semántica de las tablas candidatas. `m001` conserva el contenido compuesto en `entradas_compuestas` y usa `evaluaciones_expertas` como cabecera; por ello reutilizar la cabecera no equivale a haber resuelto las otras 50 rutas.

## Resultado reproducible

```powershell
php prototipos/orion-conservacion-0_2/ensayar-sqlite-completo.php --base-institucional
php artisan test tests/Unit/ReferenciaF02SqliteDesechableTest.php --compact
```

Seis casos sintéticos pasan guardar → leer entradas → comparar canónicamente → reconstruir ambas ramas → comparar DTO completo y huella. Se comprueban FK de propietario, prohibición de insertar después del cierre, inmutabilidad de la cabecera incluso durante el `UPDATE` que la cierra, unicidad de identidad y rollback de escritura parcial. El modo integrado termina con **157 tablas físicas = 141 de negocio + 16 técnicas**. El modo F02 puro sigue con 38 tablas físicas y las mismas seis comparaciones. Este resultado prueba representabilidad en una copia de las migraciones reales; no demuestra concurrencia PostgreSQL ni migración poblada/reversión. **El replay de este ensayo usa un release temporal y el conocimiento leído del árbol actual; todavía no prueba replay histórico autónomo tras terminar el proceso.**

## Límite de 110 e integridad semántica

Este candidato de referencia excede el máximo por **31 tablas de negocio**. No es una cota mínima matemática: cada modelo F02 tiene su propia tabla por prudencia de grano; una reutilización o consolidación podría reducir el número solo después de probar identidad, propietario, FK, cardinalidad, ausencia/NULL y reversión para cada relación. Hoy la única reutilización física completa probada en la copia real es la cabecera. B01 funde dos pares de modelos dentro de F02; todavía no prueba que `relaciones_semanticas` pueda conservar hechos adquiridos.

Las 37 tablas F02 usan el códec de escalares tipados del oráculo (`*_tipo`, `*_texto`, `*_entero`, etc.). Ese códec permite comparar exhaustivamente los **783 campos**, pero **no constituye un diccionario clínico nominal mantenible** ni sustituye un modelo normalizado. Por eso no se presentan esas tablas como DDL institucional para aprobación. Persisten las brechas B01–B03 y las demás rutas detalladas en la [matriz de 51 rutas](../../prototipos/orion-conservacion-110/matriz-resolucion-51-rutas.csv). La necesidad de relaciones nuevas bajo 110 debe demostrarse con columnas nominales, PK/FK, checks por grano, escritor/lector integral y transición poblada.

## Decisión y continuidad

No se han creado migraciones institucionales, ni cambiado `persistido=false`, ni vinculado revisiones a evaluaciones transitorias. La autorización del máximo de 110 **no aprueba** las dos columnas del ensayo ni nuevas relaciones. Tampoco corresponde pedir aprobación de elevar el límite a 141: la cifra describe este oráculo amplio, no un candidato físico final. El siguiente cierre técnico exige un diseño nominal único, ensayado en SQLite y PostgreSQL desechables con concurrencia, idempotencia, migración poblada/reversión y consumidores existentes; entonces se presentará el DDL exacto y el total que realmente necesite.

## Permisos y pruebas relacionadas

`CapturasInstrumentalesResidente` exige la Policy `consultarResultados` y cinco permisos de lectura antes de consultar aplicaciones filtradas por `cod_residente`, estado y corte. Las preguntas/opciones se obtienen de las aplicaciones de ese residente y de selectores declarados; el permiso de catálogo por sí solo no autoriza una consulta clínica. Los cuatro permisos ya existentes añadidos al rol médico (`respuestas_instrumento.ver`, `instrumentos.ver`, `preguntas_instrumento.ver`, `opciones_pregunta.ver`) no agregan escritura. La prueba de la ruta médica verifica acceso con el conjunto completo y HTTP 403 tras revocar `preguntas_instrumento.ver`. La Policy actual permite a Medicina consultar los residentes para los que `ResidentePolicy::view` da acceso cuando tiene los permisos generales; la delimitación adicional por asignación médica **no está implementada** y requeriría una decisión institucional de alcance si se pretende restringirla.

Resultados de este corte: `ReferenciaF02SqliteDesechableTest` **2 pruebas, 6 aserciones**; `RevisionValoracionExpertaTest` **17 pruebas, 58 aserciones**; cluster PostgreSQL desechable con `ReferenciaF02PostgresDesechableTest`, `ConservacionB02PostgresTest`, `ConservacionCierrePostgresTest` y `ConservacionCriterioPostgresTest` **4 pruebas, 58 aserciones**. Estas cuatro pruebas PostgreSQL pertenecen a la **referencia**, no al modo integrado con las 104 tablas. `npm test`: **226 aprobadas, 2 omitidas**; `npm run build`: correcto. La batería Laravel completa se ejecuta por separado y su resultado debe registrarse al terminar.

| Criterio de aceptación | Estado de este corte |
| --- | --- |
| 104 tablas originales migradas y cabecera real reutilizada | Implementado y probado solo en SQLite desechable |
| Dos ramas, 39 modelos, 51 rutas, seis casos y comparación estricta | Probado en la referencia integrada SQLite; replay depende aún del release temporal |
| Esquema nominal normalizado con máximo 110 | No implementado; la referencia integrada usa 141 tablas de negocio y códec de prueba |
| Integración PostgreSQL y cierre concurrente de ese esquema | Pendiente; las cuatro pruebas PostgreSQL validan prototipos de referencia separados |
| Migración poblada, reversión sin pérdida y consumidores existentes | Pendiente |
| Persistencia institucional activada y revisión sobre resultado conservado | Bloqueada por diseño y aprobación estructural específica |

La corrida global `php artisan test --compact` se intentó, pero se interrumpió tras un tramo prolongado sin nueva salida; antes de ello había marcado fallos cuyo resumen y nombres no llegó a imprimir. Una repetición con `--debug --stop-on-failure` confirmó **1.637 pruebas terminadas** sin llegar aún al primer fallo y también se interrumpió para evitar otra corrida prolongada. Por tanto, **la batería Laravel completa no está aprobada y sus fallos no están identificados**. El registro técnico parcial de depuración quedó fuera del repositorio en `%TEMP%/orion-full-debug-20261021.log`; no contiene historias clínicas reales. No se modificaron pruebas ajenas para ocultar esos fallos.
