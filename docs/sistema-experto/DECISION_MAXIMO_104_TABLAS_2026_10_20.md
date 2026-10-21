# O.R.I.O.N. — máximo vigente de 104 tablas de negocio

Fecha: 20/10/2026. Decisión expresa de la propietaria: **«Mantener máximo de 104»**. Sustituye el límite anterior de 90–100 para el trabajo futuro; no autoriza por sí misma una modificación particular del esquema congelado. El inventario instalado sigue siendo **75 operativas + 29 expertas = 104**. Las 16 tablas técnicas no son tablas de negocio. La decisión no borra ni fusiona historia.

## Consecuencia para la evaluación institucional compuesta

El DTO `EVALUACION-INSTITUCIONAL-1` une las ramas instrumental y Petersen, sus fuentes, contratos adquiridos, ausencias, orden, conocimiento, reglas y ejecutor. `PrepararEvaluacionInstitucional` lo calcula en memoria; hoy no existe un escritor relacional que permita recuperar y reproducir exactamente una emisión histórica institucional. Los contratos observacional, D-137 y del laboratorio no representan todos esos granos.

Toda propuesta con `n` tablas expertas nuevas exige retirar **al menos `n` tablas de negocio existentes** mediante consolidaciones que conserven datos, PK/FK, consumidores, 4FN, concurrencia y reversión. La cuenta `104 − n + n = 104` es necesaria, pero no demuestra que las relaciones sean compatibles. No se aceptan JSON/EAV, campos clínicos ajenos, anexos de historia en archivos ni truncar el DTO para cuadrar el inventario.

## Auditoría reproducible del candidato de cuatro relaciones

Los archivos `prototipos/orion-conservacion-100/mapa-granos-candidato.csv` y `mapa-campos-candidato.csv` son un ensayo **no instalado**. Su lectura de solo archivos arroja 51 filas de grano: 20 `CANDIDATO`, 24 `CANDIDATO_ALIASES` y **7 con brecha**. El mapa de campos tiene 783 rutas: 454 candidatas, 290 por alias y **39 con brecha**. Las brechas se agrupan así:

| Brecha | Rutas de grano | Rutas/campos | Información no cerrada |
| --- | ---: | ---: | --- |
| B01 | 4 | 16 | Contratos de relación y lista independiente de modalidades en ambas ramas del conocimiento. |
| B02 | 2 | 22 | Preguntas declarativas originales, incluso las seleccionadas sin respuesta, en captura instrumental y soportes Petersen. |
| B03 | 1 | 1 | Membresía de soportes Petersen, independiente del juicio y de otras participaciones. |

La cifra de 39 incluye nodos de colección y sus campos, no 39 columnas necesariamente nuevas. El mapa todavía no prueba equivalencia de grano, FK o replay. Las cuatro tablas nuevas candidatas más cuatro consolidaciones dejarían 104, pero **no cubren esas tres brechas**; no se convierten en migraciones.

### Primer ensayo físico de liberación de espacio

[`ensayar-criterio-sqlite.php`](../../prototipos/orion-consolidacion-104/ensayar-criterio-sqlite.php) crea exclusivamente SQLite `:memory:` y ensaya la unión 0..1 de `resultados_criterio` y `trazas_inferencia` en `evaluacion_criterios`. Conserva sus códigos como alias únicos, fechas separadas, justificación/fin nullable y la FK de `evaluaciones_reglas` al alias de traza. Con cuatro criterios sintéticos (ninguna hija, solo resultado, solo traza, ambas) reconstruyó exactamente las cuatro relaciones originales, comprobó FK y checks, y revirtió una falla inyectada antes de finalizar la transición. Comando y resultado de esta revisión:

```text
php prototipos/orion-consolidacion-104/ensayar-criterio-sqlite.php
PASS SQLite desechable: rollback, 4 criterios, 2 resultados, 2 trazas, 2 reglas, reconstrucción exacta y FK/checks.
```

Este ensayo aporta evidencia para **dos consolidaciones candidatas**, no para un cambio de inventario instalado. No acredita PostgreSQL, datos históricos poblados de la instalación, concurrencia, 4FN independiente, reversión operativa ni el escritor/lector compuesto. Las demás consolidaciones y el diseño completo tampoco tienen transición física probada. No se ha pedido autorización de DDL para una propuesta aún incompleta.

El segundo [ensayo físico desechable de vínculos](../../prototipos/orion-consolidacion-104/ensayar-vinculos-sqlite.php) conserva cada participación y cada soporte de condición como **fila independiente** en dos variantes cerradas de `evidencias_criterio_evaluacion`. Retiene el código histórico del soporte como alias único; asigna una PK nueva de 20 caracteres, preserva FK originales y rechaza destinos mixtos, alias duplicado o condición inexistente. Dos participaciones y dos soportes sintéticos reconstruyeron exactamente las tablas de origen, incluido un mismo origen que soporta dos condiciones. Probó rollback antes de retirar la fuente. El ensayo de solo lectura sobre la base local actual confirmó 225 participaciones y 150 soportes, sin escribirlas. Comandos:

```text
php prototipos/orion-consolidacion-104/ensayar-vinculos-sqlite.php
PASS SQLite desechable: rollback, 2 participaciones, 2 soportes independientes, reconstrucción exacta y FK/checks.
php prototipos/orion-reduccion-100/ensayar-vinculos-evidencia.php
PASS participaciones=225, soportes=150, filas_union_tipadas=375
```

También esta **tercera consolidación candidata** requiere prueba física PostgreSQL, transición poblada, concurrencia, reversión y 4FN independiente; no está instalada ni autorizada. Tres posibilidades demostradas con sintéticos no equivalen a las cuatro consolidaciones mínimas de un candidato de cuatro tablas, cuyo propio mapa conserva B01–B03 sin resolver.

Un [tercer script SQLite desechable](../../prototipos/orion-consolidacion-104/ensayar-clasificacion-sqlite.php) ensaya la **cuarta consolidación candidata**: una clasificación DCL 0..1 se representa como bloque opcional de `evaluaciones_expertas`, con código histórico único y la FK de motivos y reglas aplicada al alias. Reconstruyó tres evaluaciones, dos clasificaciones —una con patrón NULL— y sus dos colecciones hijas, además de probar rollback, checks y FK negativas. Resultado: `PASS SQLite desechable: rollback, 3 evaluaciones, 2 clasificaciones, motivos/reglas, reconstrucción exacta y FK/checks.` Quedan sin probar todas las demás FK entrantes, historia poblada, PostgreSQL, concurrencia, reversión operativa y 4FN independiente.

Con **cuatro consolidaciones candidatas** y cuatro relaciones nuevas, la aritmética sería `104 − 4 + 4 = 104`. Es solo una cuenta: el mapa de cuatro relaciones aún pierde B01–B03, por lo que **no existe todavía un diccionario instalable de conservación completa**. Ninguna de estas consolidaciones se ejecutó en el esquema institucional.

## Próxima prueba exigida antes de pedir DDL

1. Resolver B01–B03 con relaciones tipadas y nominales, reutilizando una tabla solo si conserva exactamente su grano. Publicar diccionario de columnas, PK/FK, índices, checks, cierre de colecciones y la lista de tablas que se consolidarían para no superar 104.
2. Ejecutar `guardar → recuperar → replay` contra el DTO canónico en PostgreSQL y SQLite desechables, con NULL, ausencia, listas vacías, orden, pregunta sin respuesta, soportes compartidos, cambio posterior del original y conflicto de identidad. Probar transición poblada y reversión con datos sintéticos.
3. Revisar independientemente 4FN, permisos, inmutabilidad, concurrencia y recuperación del ejecutor retenido. Solo entonces solicitar la aprobación **específica** de ese diccionario conforme a `database/AGENTS.md`.

Mientras tanto se mantienen las funciones de consulta e inferencia con abstención explícita. No se etiqueta una consulta calculada en memoria como evaluación histórica íntegramente conservada ni se habilita una revisión de ese compuesto inexistente.
