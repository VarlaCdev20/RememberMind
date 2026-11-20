# Normalización de negocio: contrato y secuencia de implementación

Este plan desarrolla la [autorización del 20/11](DECISION_NORMALIZACION_5FN_2026_11_20.md).
No constituye evidencia de instalación ni una certificación global. Se trabaja en
`feature/bdd-normalizacion-5fn`, en un checkout independiente de la aplicación local.

## Criterio de aceptación

La 5FN se evalúa respecto de las dependencias del negocio, además de las claves.
Una tabla estrecha, una PK artificial o una muestra sin duplicados no bastan.
Para cada descomposición deben conservarse identidad, contenido, autoría, fechas,
estados y relaciones; la proyección anterior debe reconstruirse sin pérdidas ni
filas adicionales. Las escrituras y la concurrencia también deben mantener las
garantías, incluidas las operaciones SQL directas de las que dependa el sistema.

No se descompone una asignación en sus tres pares: la auditoría obtuvo 91
combinaciones espurias. Tampoco se deduce la autoría histórica desde la cuenta
actual de un trabajador, ni se recalculan mediciones originales o resultados
conservados del experto.

## Bloques y estado

| Bloque | Cambio | Estado |
| --- | --- | --- |
| Admisión y cuidado | Derivar residente de la admisión en consentimientos y del plan/intervención en ejecuciones | Implementado en checkout; conservación y reversión verificadas en SQLite y PostgreSQL; concurrencia en verificación |
| Selección semanal | Sustituir listas de días por patrón y miembros ISO relacionados | Implementado en checkout; 6 pruebas / 43 comprobaciones aprobadas en PostgreSQL; regresión de agenda en curso |
| Contexto clínico opcional | Separar la asociación directa al residente de los registros cuyo sujeto se deriva de la atención | Once familias implementadas y verificadas por bloque: [cuidados](VERIFICACION_5FN_CUIDADOS_2026_11_20.md), [contexto cognitivo](VERIFICACION_5FN_CONTEXTO_COGNITIVO_2026_11_20.md) y [resto clínico](VERIFICACION_5FN_CONTEXTO_CLINICO_2026_11_20.md). Copia poblada conserva 15.297 filas; 97 tablas de negocio y 16 técnicas. Regresión ampliada en curso |
| Declaraciones semánticas | Separar la definición de variable/criterio del nodo versionado para retirar dependencias dominio/versión y propietario/versión | Diseño en revisión; no implementado |
| Conservación IADL | Derivar funcionalidad desde la evaluación únicamente cuando exista caracterización IADL | Implementado y verificado en checkout; [resultados y límites](VERIFICACION_5FN_IADL_2026_11_20.md) |
| Respuestas y administración | Conservar preguntas libres y dosis sin horario; derivar pregunta desde opción y prescripción desde horario en los otros casos | Pendiente de implementación |
| Ocupación | Derivar residente desde admisión manteniendo la unicidad de ocupación activa por residente y cama | Pendiente de implementación y prueba concurrente |
| Revisión integral | Contratos FD/MVD/JD, consumidores, autorizaciones, migración poblada, reversión e inventario | Pendiente; no se declara 5FN global |

## Presupuesto de relaciones

Partida: 84 de negocio y 16 técnicas. Los dos primeros bloques añaden dos
relaciones de negocio: `patrones_semanales` y `dias_patron_semanal`.

El diseño por cerrar reserva hasta once asociaciones directas de residente,
una relación de declaraciones semánticas y dos asociaciones específicas para
respuestas libres y administraciones sin horario. Ese reparto alcanzaría
**100 tablas de negocio más 16 técnicas**. Es un presupuesto de diseño, no un
conteo instalado. No se unirán responsabilidades ajenas para conseguir ese
número. Si el análisis demuestra que una relación propuesta no conserva el
grano o las dependencias, se revisará el reparto antes de implementarla.

## Contrato de los días de semana

El patrón se identifica mediante una clave opaca; los consumidores recuperan
los días desde `dias_patron_semanal`, sin descodificar la clave. Hay siete días
ISO posibles y sus 128 subconjuntos, incluido el conjunto vacío; se conserva
además el significado histórico de la ausencia de selección: todos los días.
El catálogo matemático no contiene pacientes ni nuevas indicaciones clínicas.

La reversión recupera una representación canónica de los mismos miembros,
no la ortografía original de sus alias. Una selección histórica desconocida
detiene la migración antes de alterar el esquema; no se adivinan sus días.
Se impide modificar los miembros de un patrón ya referenciado para que una
edición del catálogo no cambie silenciosamente las órdenes existentes.

Los adaptadores conservan las entradas vigentes `dias_semana` y las lecturas
de agenda; el almacenamiento de la selección es relacional. Debe completarse
la regresión de medicación, cuidados y pase de turno antes de instalar.

## Instalación

La base operativa sigue sin estas migraciones. Antes de aplicar: terminar los
bloques pendientes, revisión independiente, pruebas PostgreSQL con conexiones
separadas, respaldo y ensayo sobre copia poblada. No ejecutar un reinicio de
la base operativa. La reversión debe comprobarse sobre la copia, conservando
los datos y detectando cualquier estado que no pueda representarse fielmente.
