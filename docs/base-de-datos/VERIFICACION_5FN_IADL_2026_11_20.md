# Contexto funcional IADL · verificación del 20/11/2026

Alcance autorizado: normalización de negocio. La migración
`2026_11_20_000003_derive_iadl_functional_context` retira
`hechos_instantanea_experta.iadl_cod_funcionalidad_instantanea`. La evaluación
ya identifica su funcionalidad mediante una referencia única. Los hechos sin
caracterización IADL siguen proyectando NULL, aunque su evaluación tenga una
funcionalidad: no se fabrica evidencia.

## Integración e integridad

`ContextoIadl` reconstruye la misma forma lógica para el almacén y los Models.
El motor independiente, las reglas y la interpretación no cambian. El almacén
deja de escribir la referencia redundante y recupera las evidencias desde la
evaluación. Se conservan códigos, orden, vocabulario, tipos, autorías y fechas.

La unicidad del orden IADL se expresa por evaluación, equivalente a la
anterior por funcionalidad dada su relación única. Las guardas exigen que
exista funcionalidad al anexar IADL y protegen el contexto conservado. Los
bloqueos siguen el orden evidencia → evaluación; una prueba asíncrona con tres
conexiones PostgreSQL verifica ese orden y la confirmación posterior.

Se separó la instalación de guardas de hechos del resto, conservando el SQL
histórico predeterminado. La reinstalación para pruebas de corrupción reconoce
el esquema actual. Las pruebas históricas revierten primero las transiciones
posteriores, sin retirar FK ni debilitar reglas para que aprueben.

## Evidencia

| Comprobación | Resultado |
| --- | --- |
| Nuevos casos IADL y recuperación existente: tres perfiles, otros patrones, tipos, fechas y huellas | SQLite: 11 pruebas, 156 aserciones; PostgreSQL: 11 pruebas, 156 aserciones; sin errores ni fallos |
| Reinstalación, orden concurrente y transición histórica poblada | PostgreSQL: 3 pruebas, 94 aserciones; sin errores ni fallos |
| Suites completas de conservación y reducción histórica | SQLite: 52 pruebas, 304 aserciones; sin errores ni fallos |
| Reconstrucción sobre copia poblada, con las tres migraciones | 84 tablas y 15.297 filas; EXCEPT ALL sin pérdidas ni adicionales; reversión y reaplicación aprobadas |
| Revisión independiente después de corregir hallazgos | PASS del alcance corregido; no certifica 5FN global |

La primera reversión SQLite detectó el contador de FK diferidas que deja una
reconstrucción de tabla. Se corrigió cerrando ese estado únicamente después
de comprobar todas las referencias con `foreign_key_check`; la reversión
poblada se repitió satisfactoriamente. No se desactivó una FK para aceptar
datos inválidos.

Los hallazgos de revisión sobre reinstalación, preparación del esquema
histórico y orden de bloqueos se corrigieron y se probaron de forma
específica. Los lotes no se suman como casos únicos. Las evidencias privadas
`5fn-iadl-*`, `5fn-historia-*`, `5fn-conservacion-integral-sqlite.*` y
`5fn-copia-tres-postgres.log` permanecen fuera de Git.

## Instalación y pendientes

Implementado en `feature/bdd-normalizacion-5fn`; **sin instalar en la base de
uso**. El checkout mantiene **66 operativas + 20 expertas + 16 técnicas = 102
físicas**. El límite autorizado sigue siendo 100 de negocio, aparte de las
técnicas.

Quedan los demás bloques del [plan](PLAN_NORMALIZACION_5FN_2026_11_20.md), la
integración final y su verificación global. Esta entrega no afirma que todas
las tablas ya estén en 5FN ni que haya aprobado una CI completa.
