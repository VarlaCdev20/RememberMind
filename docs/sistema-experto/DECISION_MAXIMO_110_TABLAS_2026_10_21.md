# Decisión de capacidad para la conservación de O.R.I.O.N.

**Fecha:** 21/10/2026. **Estado:** decisión explícita de la propietaria sobre el límite máximo, no aprobación de DDL.

> Aclaración de inventario del 25/10: el presupuesto de seis relaciones de esta decisión se calculó al inicio sobre 104 tablas. La [consolidación aprobada del 22/10](../base-de-datos/DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md) dejó 100 instaladas. Se conserva el máximo de 110 y el alcance original de la decisión; no se aprueban nuevas relaciones por diferencia aritmética.

La propietaria sustituyó el [máximo anterior de 104 tablas](DECISION_MAXIMO_104_TABLAS_2026_10_20.md) por un máximo de **110 tablas de negocio**. El inventario instalado comprobado en `remembermind_dev` al iniciar este trabajo sigue siendo **75 operativas + 29 expertas = 104 tablas de negocio**, además de **16 técnicas** de Laravel y paquetes: 120 tablas físicas. Por tanto existe capacidad numérica para **hasta seis relaciones de negocio adicionales**. No hay obligación de crearlas.

Esta decisión autoriza estudiar y proponer un diseño dentro de 110. **No aprueba** nombres, tablas, columnas, FK, índices, checks, triggers, cambios de cardinalidad, consolidaciones, retiros, migraciones, permisos nuevos ni activación clínica. Cada modificación del esquema congelado requiere una propuesta nominal, un ensayo relacional en bases desechables y autorización estructural específica conforme a `database/AGENTS.md`.

Los [ensayos de conservación bajo 104](ENSAYO_CINCO_RELACIONES_104_2026_10_21.md) y el [dictamen de factibilidad](DICTAMEN_FACTIBILIDAD_CONSERVACION_104_2026_10_21.md) siguen siendo evidencia histórica de sus respectivos prototipos. El candidato de 15 relaciones llevaría el inventario actual a 119 y el de 25 lo llevaría a 129; ninguno queda aprobado por este nuevo límite. El presupuesto de seis relaciones no subsana por sí solo las brechas B01–B03 ni demuestra la ida y vuelta del DTO compuesto.

Hasta demostrar guardado, lectura SQL y replay exactos de ambas ramas, `EVALUACION-INSTITUCIONAL-1` conserva `persistido=false`. La tabla existente `revisiones_evaluacion_experta` podrá asociarse al resultado Petersen únicamente después de disponer de una evaluación íntegra y recuperable. Las 286 aplicaciones observacionales no se reclasifican como medidas instrumentales.
