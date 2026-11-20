# Contexto opcional de cuidados — evidencia del 20/11/2026

Alcance: cuarta migración de la [normalización autorizada](DECISION_NORMALIZACION_5FN_2026_11_20.md), en el checkout `RememberMind-5fn`. No está instalada en la aplicación operativa y no certifica la 5FN global.

## Dependencia y conservación

En `mediciones_clinicas` y `registros_observaciones_cuidado`, la atención determina el residente cuando existe. Se retira su almacenamiento repetido; los registros sin atención conservan su sujeto en `residentes_mediciones_clinicas` y `residentes_observaciones_cuidado`. No se inventan atenciones para representar registros directos.

Cada asociación tiene por clave la identidad original del registro. Una FK diferida apunta a una clave técnica generada, disponible únicamente cuando el registro no tiene atención. Esto impide dos fuentes de sujeto incluso cuando distintas transacciones no ven todavía sus inserciones. El alias técnico se excluye de la serialización del modelo.

Se conservan valores originales, CHECK completos, alias generados de tipo, referencias al dolor original, fechas, responsables y estados. Las escrituras bloquean la atención antes de contrastar su sujeto. Cambiar el episodio o el sujeto histórico se rechaza; una rectificación sigue el flujo existente. Las proyecciones del modelo y los adaptadores recuperan el residente por la relación canónica.

## Comprobaciones terminadas

- PostgreSQL desechable: **5 pruebas, 78 comprobaciones**, sin fallos ni omisiones. Incluyen seis tipos de registro, proyecciones parciales y reutilización de consultas, ausencia de efectos parciales, reversión poblada y autorreferencias.
- Conexiones independientes PostgreSQL: la asociación no confirmada y el registro con atención del mismo ID no pueden coexistir. Se verificó tanto `READ COMMITTED` como `REPEATABLE READ`.
- Un CHECK ajeno detiene la migración sin eliminar restricciones ni dejar una separación parcial. Se contrasta el contrato instalado antes del DDL.
- SQLite: las tres pruebas aplicables de contexto aprobaron **66 comprobaciones**. Los dos casos exclusivos de PostgreSQL se ejecutaron en ese motor; no se presenta SQLite como prueba de concurrencia PostgreSQL.
- Copia poblada PostgreSQL: reconstrucción de **84 tablas y 15.297 filas originales**, con `EXCEPT ALL` en ambos sentidos, tras subir, revertir y reaplicar las cuatro migraciones. **Cero pérdidas y cero filas adicionales**.
- Inventario de esa copia: **88 tablas de negocio y 16 técnicas**, 104 físicas.
- Integridad SQL y migraciones históricas: **11 pruebas, 109 comprobaciones**, aprobadas en SQLite. Se conserva la prueba de reversión de las consolidaciones anteriores, retirando primero la separación posterior.
- Inventario actualizado: **1 prueba, 67 comprobaciones**, aprobadas. La comprobación de administración desde la ficha también aprobó tras consultar el residente derivado (**1 prueba, 4 comprobaciones**).

La reversión recupera `cod_residente` antes de retirar las asociaciones. PostgreSQL puede cambiar el ordinal físico de la columna reconstruida; se contrastan nombre, tipo, nulabilidad, valores por defecto y generación, sin confundir posición con semántica.

## Integración y límites pendientes

La regresión ampliada de cuidados, reportes, pantallas y migraciones históricas está en ejecución. Se actualizan fixtures que todavía escribían columnas derivadas retiradas y aserciones físicas para consultar el contexto canónico, conservando sus filtros y comprobaciones negativas.

Quedan los demás contextos clínicos opcionales, las declaraciones semánticas, las respuestas, la administración, la ocupación y la revisión integral del plan. El respaldo y los ensayos son privados; no se incluyen datos de la copia en Git. No se han cambiado reglas clínicas ni permisos, ni se ha reiniciado o migrado la base operativa.
