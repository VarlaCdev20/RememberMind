# Datos utilizados en la evaluación experta

La propietaria autorizó cambiar el nombre físico de `hechos_instantanea_experta` a `hechos_evaluacion_experta`. Cada fila representa un hecho que el motor utilizó en una evaluación concreta. Conserva su valor original, procedencia y orden para poder consultar después la evidencia del resultado.

La migración `2026_11_20_000009_rename_expert_evaluation_facts` renombra la relación y la entrada de su contador de códigos en una transacción. En PostgreSQL actualiza también las funciones de los triggers que consultan el nombre anterior. La reversión recupera ambos nombres y las referencias de las funciones. La clave sigue siendo `cod_hecho_instantanea` y los códigos mantienen el prefijo `HIX`.

El cambio de nombre conserva las dependencias y el número de tablas. La verificación anterior sobre la dependencia funcional de las actividades instrumentales se documenta en [Contexto funcional IADL](VERIFICACION_5FN_IADL_2026_11_20.md).
