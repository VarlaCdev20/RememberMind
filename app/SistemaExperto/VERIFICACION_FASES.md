# Verificación de las fases — 17/10/2026

La evidencia inicial de este documento se conserva como antecedente. La
incorporación aprobada posterior se describe en el apartado de conservación V2.

## Alcance implementado

- Seis servicios pendientes: evaluación de reglas, integración de dominios,
  motor, requisitos DCL, clasificador Petersen y explicación.
- CU-03, CU-04 y CU-06; generación automática de informe mediante Domain.
- Resultados de inferencia conservan relaciones y exclusiones originales.
- Fuente IADL con tabla/Model aprobados, adaptador descriptivo y conexión al
  extractor, mapper y repositorio existentes. No hay nuevas rutas o permisos.
- Eliminados los tres archivos iniciales sin clase, sustituidos por los
  servicios y casos de uso del reglamento en sus subcarpetas correspondientes.

## Evidencia ejecutada

| Verificación | Resultado | Límite |
|---|---|---|
| Unitarias + Feature Infrastructure SQLite | 682 pruebas / 5055 aserciones, PASS | Datos sintéticos; no aprobación clínica |
| Infrastructure PostgreSQL | 106 pruebas / 443 aserciones, PASS | Clúster temporal con runner propio |
| Nuevo flujo completo PostgreSQL | 1 prueba / 32 aserciones, PASS | Repositorios, mappers, extracción, Domain y cinco pasos reales; sin HTTP |
| BDD operativa + catálogo de códigos PostgreSQL | 24 pruebas / 528 aserciones, PASS | 73 operativas + 23 expertas; no activación institucional |
| BDD operativa SQLite | 19 pruebas / 227 aserciones, PASS | Esquema y relaciones de ese motor |
| Catálogo correlativo SQLite | 5 pruebas / 301 aserciones, PASS | 96 entidades con prefijos únicos |
| Arquitectura | 23 pruebas, PASS | Límites y contratos estáticos/rutas existentes |
| Sintaxis PHP | PASS en archivos PHP nuevos/modificados | No sustituye pruebas funcionales |
| Pint | PASS en archivos PHP nuevos/modificados | Formato/documentación |

Los 106 casos PostgreSQL preceden al nuevo test de flujo, que se ejecutó aparte;
en total son 107 casos de Infrastructure verificados allí. No se suman como si
fueran casos distintos de los ejecutados también en SQLite.

El clasificador coincide literalmente con los 30 casos y 32 combinaciones de
la matriz oficial 03A, descargados en CSV locales con procedencia registrada.
CAS-11 verifica adjudicación C02 antes del veto general de requisito N.

La revisión independiente de experto encontró dos defectos corregidos con
regresión: contradicción funcional que debía conservar X y rechazo de
identidades reales en el generador público. La revisión independiente de
persistencia IADL pasó estáticamente sin hallazgos P1/P2. Ninguna revisión
autoriza método, instrumentos ni activación clínica.

La suite general se inició antes de corregir el catálogo y los fixtures IADL.
Terminó con **1923 pruebas, 39641 aserciones, 2 fallos y 22 omisiones**.
Los dos fallos son las clases que aquel proceso ya había cargado antes de las
correcciones: esperaba 95 entidades en el catálogo y ausencia de la tabla IADL.
Ambas expectativas se actualizaron conforme a la extensión expresamente
aprobada. Sus reejecuciones posteriores pasan, igual que las suites completas
de módulo, BDD y catálogo indicadas arriba. No se repitió otra vez toda la
suite general ni se presenta aquella ejecución inicial como PASS.

## Estado previo a la aprobación de conservación V2 (histórico)

La conservación completa todavía no está implementada: requiere aprobar un
diccionario de ampliación de D-137. La propietaria aprobó la extensión IADL y
el alcance de diseño con fuentes institucionales; pidió una recomendación
antes de aprobar las 16 tablas de snapshot. No existe autorización para esas
migraciones. [Revisión de reutilización](Infrastructure/REVISION_REUTILIZACION_D137.md).

`guardar` y `guardarClasificacion` siguen rechazando explícitamente una
conservación incompleta. No se usa caché, archivos o JSON para simularla.
La migración IADL solo se ejecutó en bases de prueba desechables; no se cambió
`.env` ni se migró una base institucional.

Build/QA visual: no aplican; no se modificó frontend. El motor anterior y
ApoyoDecision permanecen conectados como antes. No se crearon commits ni se
hizo push de estas fases.


## Conservación V2 aprobada e implementada

La propietaria autorizó incorporar las nueve tablas propuestas y posponer su
reducción a una transición posterior. Se implementaron las nueve creaciones,
Models, catálogos, relaciones y ampliaciones de criterios/resultados existentes:
**32 expertas + 73 operativas = 105 tablas de negocio**, con técnicas excluidas.
[Diccionario y decisión](../../docs/base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md).

El repositorio conserva memoria, vector cognitivo y clasificación DCL original.
`VinculadorRepositorios` lo integra explícitamente, sin activar un provider.
La recuperación V2 no requiere un Closure de memoria ni consulta de nuevo los
valores operativos. Reutiliza criterios/resultados para dominios, subcomponentes
y C01–C05, conserva NULL/false/0, tipos escalares, fechas exactas y soportes
compartidos. La comparación de contenido y las transacciones rechazan
sobrescritura, procedencia ajena y conservación parcial.

### Verificación posterior

| Comando / alcance | Resultado | Evidencia y límite |
|---|---|---|
| Unitarias y Feature del módulo, BDD, códigos e inventario, SQLite | **740 pruebas / 5799 aserciones, PASS** | Código final; 31 casos de conservación incluidos, con motor real, 19 subcomponentes, historial D-137 y reconstrucción con nueva instancia |
| Architecture en la ejecución conjunta posterior | 2 fallos ajenos a esta incorporación | `ResumenSeguimientoResidente` usa auth() en Application y la vista `tab-resumen.blade.php` contiene un cierre de enlace; archivos de Clínica modificados concurrentemente |
| Infrastructure + BDD + códigos + inventario, PostgreSQL | **162 pruebas / 1206 aserciones, PASS** | `remembermind-pg-3864dc02684f4fe082b37b218af23e64.junit.xml`, clúster temporal propio |
| Salida del motor real conservada, PostgreSQL | **1 prueba / 4 aserciones, PASS** | `remembermind-pg-89c4d62cb09949d587de0067ac8d298a.junit.xml`, caso añadido después de la ejecución anterior |
| Historial D-137 visible después de anexar snapshot, PostgreSQL | **1 prueba / 4 aserciones, PASS** | `remembermind-pg-be330bb9bc2c45abad1966e0838baa85.junit.xml`; las evaluaciones exclusivamente V2 no se publican por G09 |
| Orden corrupto rechazado sin coerción, PostgreSQL | **1 prueba / 1 aserción, PASS** | `remembermind-pg-19274e587a6447c1884e45eeded0cf39.junit.xml`; regresión también cubierta en SQLite |
| Conservación e inventario PostgreSQL, ejecución estrecha anterior | 36 pruebas / 496 aserciones, PASS | `remembermind-pg-4c076537f8934a9a92f60c2c1451ca74.junit.xml`; solapada con el grupo de 162, no se suma |
| Gate independiente de estructura / contenido de persistencia | PASS estático, sin P1/P2 pendientes | Revisores de lectura; no equivale a validación clínica ni a prueba de carreras |
| Sintaxis PHP / Pint / diff | PASS | PHP-l en 56 archivos propios nuevos/modificados; Pint acotado y git diff --check |

El total PostgreSQL vigente es **165 casos / 1215 aserciones** de los cuatro grupos
no solapados. La ejecución SQLite final está en
`remembermind-conservacion-v2-final.junit.xml`. Una ejecución anterior de
761 pruebas incluyendo Architecture pasó antes de los cambios concurrentes
de Clínica; la posterior de 762 registró los dos fallos de arquitectura
indicados y ninguno en el módulo experto. No se presenta como PASS global. Los informes y logs están en el directorio temporal del sistema.
Las pruebas demuestran down/up con D-137 preexistente, bloqueo de reversión con
historia, reintentos sin escrituras, fallo intermedio con rollback de memoria o
DCL, integridad de referencias, tipos, zonas y conservación del límite AST32.
La concurrencia con dos procesos PostgreSQL no se ha probado y no se afirma.

La primera ejecución PostgreSQL falló al declarar una self-FK antes de la PK.
Se corrigió la migración y pasaron las ejecuciones posteriores indicadas.
Su parada inicial excedió el timeout del runner; posteriormente no existe PID
activo. La política automática rechazó la limpieza recursiva de su carpeta
`remembermind-pg-test-f13d1adbdf0e40d6bca0eb8614c370e5`, que permanece en TEMP.
Los clústeres de las ejecuciones exitosas se detuvieron y retiraron por el runner.

La suite general iniciada durante la implementación terminó con **1959 pruebas,
40479 aserciones, 18 errores, 2 fallos y 22 omisiones**. No fue una ejecución del
código final: había cargado tres casos propios antes de sus correcciones
(PHPDoc del constructor, expectativa anterior de escritura bloqueada y retirada
de un índice al revertir SQLite). Los tres pasan en la ejecución final de
740 pruebas indicada arriba; la conservación también pasó en PostgreSQL.

Los otros 17 casos afectados corresponden a Clínica y sus pantallas, modificadas
concurrentemente: nueve errores de fixtures por una relación `personal` nula,
seis por la propiedad Livewire `evolucionComparada` ausente, uno por la clave
`ancla` ausente en `estado-evolucion.blade.php` y un fallo por tres respuestas
HTTP 500 al renderizar fichas. Se registran como incidencias de aquella ejecución,
sin modificar ese trabajo concurrente ni afirmar su estado actual. Evidencia:
`remembermind-conservacion-v2-full.junit.xml` y
`remembermind-conservacion-v2-full.log`, en TEMP. No se repitió toda la suite
general ni se declara PASS global del repositorio.

Las migraciones solo se ejecutaron en SQLite en memoria y PostgreSQL temporal.
No se modificó `.env` ni una base institucional. No se activaron rutas,
providers, permisos, resultados asistenciales o un informe clínico. No hay
frontend nuevo de esta tarea, por lo que build/QA visual no aplican al alcance.
No se realizaron operaciones de escritura Git ni push de esta incorporación.
