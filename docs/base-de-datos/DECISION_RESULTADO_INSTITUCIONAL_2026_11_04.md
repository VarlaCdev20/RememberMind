---
title: Conservación del resultado institucional y revisiones
status: APPROVED
last_reviewed: 2026-11-04
owner: ApoyoDecision
source_of_truth: true
---

# Decisión y alcance

La propietaria respondió **«Autorizar ese guardado y sus revisiones»** a la solicitud explícita de dieciséis columnas en `evaluaciones_expertas`, tres referencias de origen en `hechos_instantanea_experta`, cinco FK indexadas y sus CHECK, y el permiso `evaluaciones_expertas.ejecutar_institucional` para Medicina/Psicología. Esta decisión sustituye, exclusivamente en ese alcance, la respuesta del31/10 «Mantener relaciones y permisos actuales».

No se crean tablas: **81 operativas +25 expertas =106 de negocio**, además de16 técnicas. No se modifican las mediciones históricas, las reglas clínicas, los permisos de captura ni las competencias de revisión. No se añade `NO_CONCLUYENTE` a las revisiones: siguen admitiendo `CONFIRMADA` y `RECHAZADA`.

## Diccionario y transición

El diccionario nominal autorizado es el de [la propuesta del31/10](PROPUESTA_CONSERVACION_RESULTADO_INSTITUCIONAL_2026_10_31.md). La extensión explícita es `database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php`, fuera del descubrimiento automático de migraciones. Su instalación debe conservar el inventario, las filas anteriores y todas las FK existentes. Las diecinueve columnas nuevas permanecen NULL en la historia previa.

Las tres nuevas referencias de hechos son mutuamente excluyentes: respuesta, aplicación o juicio original. Conservan el grano del hecho tipado. No se introduce una aplicación duplicada en un hecho de respuesta. Las ramas D137, observacional e históricas anteriores siguen sometidas a sus restricciones. Las cinco FK son RESTRICT e indexadas.

## Contrato que se conserva

`RESULTADO-INSTITUCIONAL-1` retiene los estados emitidos de cinco dominios y C01–C05, el resultado principal y patrón nullable, explicación narrativa original, actor, personal, corte tipado, versiones cognitiva/Petersen, identidad del ejecutor, valores originales de respuestas/contexto/juicios y estados de reglas/condiciones realmente evaluadas. C02 se deriva; no se añade un juicio original C02.

Se reutilizan las relaciones expertas existentes para trazas y evidencia mapeada. Una captura o juicio sin mapeo no se introduce artificialmente en `evidencias_evaluacion`. Las referencias de fuente de los hechos resuelven ese caso. La explicación es prosa original; no una serialización JSON o EAV que pretenda retener colecciones arbitrarias.

**No equivale a conservar todo `EVALUACION-INSTITUCIONAL-1`**: no retiene íntegros la configuración de adquisición, todos los catálogos, membresías arbitrarias, todas las salidas internas ni el release ejecutable. El DTO completo conserva `persistido=false`; el resultado SQL tiene un contrato distinto y declara `replay_completo=false`. Recuperar el resultado original y reproducir el motor completo son capacidades diferentes.

## Operación y autorización

La ejecución recibe únicamente residente y UUID. Adquiere originales mediante las consultas autorizadas existentes, con corte consistente; reautoriza en la escritura, conserva en transacción, recupera desde SQL, comprueba la huella y confirma. La cuenta y profesional se derivan de sesión. Los reintentos del mismo actor/residente/UUID devuelven el resultado original.

Medicina y Psicología requieren permiso explícito, cuenta/personal activos, competencia, residente admitido y alcance. Superadministración no obtiene ejecución por su rol. La lectura del historial reautoriza; una falta de lectura de las fuentes no produce un resultado recortado. Las revisiones son posteriores y separadas sobre la evaluación conservada. Una discrepancia no modifica el resultado ni las reglas.

## Reversión

La reversión solo permite retirar la extensión cuando sus campos y referencias no contienen resultados. Con historia poblada se rechaza: no se borra para liberar la estructura. Antes de instalar se exige respaldo privado, prueba en SQLite/PostgreSQL desechables y comparación de las filas previas e inventario.

La evidencia de pruebas e instalación se registra en el informe de cierre del recorrido; aprobar el alcance no certifica por sí mismo que la instalación o las pruebas ya hayan ocurrido.
