# Reducción aprobada de cuatro tablas expertas — 20/10/2026

Estado: **APROBADA, IMPLEMENTADA E INSTALADA EN `remembermind_dev`**.

La propietaria rechazó la propuesta de 25 tablas adicionales y pidió reducir
la estructura existente conservando el recálculo íntegro de las evaluaciones
históricas. Tras presentar el diccionario concreto, respondió «listo que
fslta». Se registra como aprobación de las cuatro consolidaciones y sus 29
columnas, nulabilidad, PK/FK, constraints y transición/reversión descritas en
el [diccionario aprobado](../sistema-experto/PROPUESTA_REDUCCION_CUATRO_TABLAS_2026_10_20.md).
No aprueba otras modificaciones estructurales.

## Alcance físico

| Origen que se retira después de comprobar sus datos | Destino existente |
|---|---|
| `evidencias_cognitivas_instantanea` | Bloque cognitivo fijo en `hechos_instantanea_experta` |
| `evidencias_iadl_instantanea` | Bloque IADL fijo e independiente en `hechos_instantanea_experta` |
| `funcionalidad_instantanea_experta` | Bloque funcional en `clasificaciones_dcl_instantanea` |
| `soportes_sinteticos_criterio` | Alternativa tipada por hecho en `evidencias_criterio_evaluacion` |

El objetivo autorizado es **75 operativas + 29 expertas = 104 tablas de
negocio**, desde las 108 anteriores, sin crear tablas en este incremento.
Las tablas técnicas siguen fuera del inventario de negocio. Hasta que la
instalación se compruebe, el objetivo no acredita el conteo físico local.

Se conservan las identidades originales como alias únicos, los valores y
NULL originales, tipos, fechas, orden, soportes y vocabularios existentes.
Ambos bloques del hecho pueden coexistir. No se introducen JSON/EAV, listas
serializadas, vistas que simulen una retirada ni una tabla de auditoría.
Las relaciones de reglas y condiciones que conservan OR/NOT permanecen.

## Transición y aceptación

1. Migración nueva; no reescribir migraciones aplicadas. Bloquear escritores,
   comprobar coherencia y referencias entrantes antes de modificar el esquema.
2. Ampliar destinos, trasladar, reconstruir y comparar exactamente el origen;
   retirar las cuatro relaciones únicamente después de la comprobación.
3. Adaptar lectores, escritores, Models, códigos e inventario sin una capa
   permanente de compatibilidad. Conservar los contratos de observación y
   simulación y las restricciones de ejecución existentes.
4. Probar recuperación, SQL negativo, anexión IADL única, concurrencia cuando
   corresponda y reversión con datos en SQLite y PostgreSQL desechables.
5. Auditar independientemente persistencia y seguridad. Antes de instalar,
   verificar el destino `remembermind_dev`, conservar y verificar un respaldo
   privado, y aplicar únicamente la migración aprobada, sin resetear datos.
6. Verificar el inventario y las huellas tras instalar. La reversión debe
   recuperar las relaciones originales o fallar explícitamente ante datos
   no representables; nunca descartar historia para aparentar éxito.

## Límites

Esta reducción no activa ejecución clínica, cambia permisos ni aprueba reglas
o responsabilidades profesionales. El guardado que permita el recálculo
institucional íntegro sigue pendiente de cerrar su diccionario reducido y
retener/verificar todos los brutos, perfiles, contexto y ejecutor original.
Las instantáneas preparadas en memoria no equivalen a persistencia completa.

## Evidencia de instalación

Instalada el 20/10/2026, después de las pruebas y el gate independiente.
Se comprobó el destino PostgreSQL local exacto y se aplicó **únicamente**
`2026_10_20_180000_consolidate_cuatro_tablas_expertas`, sin seed, reset ni otras
migraciones pendientes. El mantenimiento temporal terminó satisfactoriamente.

- Inventario físico `public`: **124 → 120**, correspondientes a **108 → 104
  tablas de negocio** y las mismas 16 técnicas. Auditor: **29 / 29 expertas**;
  ninguna de las cuatro relaciones retiradas permanece como tabla o vista.
- Comparación inversa de **840 filas** de origen: 545 hechos, 55 cognitivas,
  5 IADL, 5 funcionalidades, 5 clasificaciones, 75 soportes sintéticos y 150
  vínculos originales. Igualdad canónica estricta antes/después, con huella
  `968cde8488b92f94e72e1a26b1fb4fc469bd18dbe3f83200aea49ce005b63cd1`.
  En el destino hay 225 vínculos: 150 anteriores y 75 con nuevo código EVC,
  conservando sus alias SSX originales.
- Se recuperaron cinco vectores y clasificaciones de la cohorte simulada
  con el lector anterior y con nuevas instancias del lector reducido. Huella
  conjunta idéntica `e85a5073f90d576623c3f0a498bd3bce59ba7ca9f12537ee5e213f34c2526ea9`.
  Esta prueba no se atribuye a evaluaciones institucionales completas todavía
  no conservadas.
- Respaldo privado previo `remembermind_dev_antes_reduccion_20261020_080940.dump`,
  1907380 bytes, SHA-256
  `3b24a75599a305dbd9d75be45f1414294486b82030f358562e518026cb1b632e`.
  Se comprobó su formato custom y se listó su inventario mediante `pg_restore
  --list`; no se ensayó restaurar ese respaldo. El archivo permanece en storage
  privado ignorado por Git, sin credenciales ni payloads clínicos en este documento.
- `app:auditar-base-datos-remember-mind --evaluar-reduccion` devolvió
  `PASS_RECONSTRUCCION_MODELO_REDUCIDO` después de instalar. Es lectura y no
  sustituye la evidencia separada de DDL y recuperación anterior.

La reversión poblada se probó en ambos motores desechables. No se ejecutó una
reversión en la base local después de instalar ni se hizo push.

## Implementación y pruebas

Migración nueva `2026_10_20_180000_consolidate_cuatro_tablas_expertas`, con
transición transaccional, comparación inversa antes del DROP y reversión
poblada. El almacén y sus Models usan los tres destinos; se retiran los cuatro
Models antiguos. Las secuencias mantienen sus identidades como alias y el
catálogo de entidades físicas baja a 104 sin reiniciar correlativos.

Las guardas físicas preservan el hecho y permiten una sola anexión IADL,
restringen los bloques completos y ambas ramas del vínculo, y protegen el
contexto desde los padres referenciados. PostgreSQL serializa los escritores
por padres/cabecera y exige READ COMMITTED para estas mutaciones; otro
aislamiento de escritura se rechaza expresamente. La lectura de auditoría
mantiene su transacción REPEATABLE READ + READ ONLY. SQLite no acredita
bloqueos PostgreSQL.

Resultados ejecutados por la raíz, con filas completamente sintéticas:

| Selección y corte | SQLite | PostgreSQL desechable |
|---|---|---|
| Regresión amplia: baseline, códigos, inventario, conservación, observaciones, simulación y roles | 208 casos: 203 aprobados, 5 exclusivos PostgreSQL omitidos; 2032 aserciones | 208 aprobados; 2052 aserciones |
| Después de reforzar el contexto de padres: conservación, migración, reversión, proyección y preflight | 69 casos: 67 aprobados, 2 exclusivos PostgreSQL omitidos; 351 aserciones | 73 aprobados; 378 aserciones, incluidas las cuatro carreras iniciales |
| Carrera final: clasificación idempotente, anexión IADL única, orden, mismo hecho por ambas ramas y padre frente a vínculo | No aplicable a bloqueos PostgreSQL | 5 aprobadas; 41 aserciones |
| Regresión observacional y simulación tras el último refuerzo de padres | 73 aprobadas; 360 aserciones | 73 aprobadas; 360 aserciones |

Las selecciones se solapan; no se suman sus totales. La regresión amplia
precede al último refuerzo de padres; las selecciones dirigidas posteriores
comprueban ese cambio. Las pruebas que inyectan corrupción retiran y reponen
guardas únicamente en las bases desechables del runner para ejercitar también
el rechazo del lector. Los negativos SQL prueban las guardas instaladas.

Pint y `git diff --check`: PASS. Los dos scripts de independencia del motor:
PASS. Build y QA visual no aplican porque no se modifica frontend. Se retiraron
los consumidores ejecutables de las cuatro tablas; sus nombres se conservan
solo como identidades alternativas y en el mapa de migración/reversión.

Comandos principales:

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'ReduccionFisicaConservacionTest|PreflightReduccionExpertaTest|ProyeccionReduccionExpertaTest|ConservacionExpertaTest' --log-junit storage/app/private/reduccion-guardas-final-sqlite.xml
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'ReduccionFisicaConservacionTest|PreflightReduccionExpertaTest|ProyeccionReduccionExpertaTest|ConservacionExpertaTest|ReduccionConcurrenciaPostgresTest' -Informe storage/app/private/reduccion-guardas-final-postgres.xml -Log storage/app/private/reduccion-guardas-final-postgres.log
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'ReduccionConcurrenciaPostgresTest' -Informe storage/app/private/reduccion-concurrencia-final-postgres.xml -Log storage/app/private/reduccion-concurrencia-final-postgres.log
```

La revisión independiente de BDD y seguridad emitió PASS técnico acotado del
diff final tras cerrar el hallazgo de contexto mutable. Revisó estáticamente
el cambio; la raíz ejecutó las pruebas citadas. No certifica validación
clínica, instalación local ni conservación institucional íntegra.
