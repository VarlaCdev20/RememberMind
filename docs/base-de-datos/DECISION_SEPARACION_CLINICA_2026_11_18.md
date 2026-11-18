---
title: "Separación de valoraciones y controles clínicos"
status: APPROVED_INSTALLED_LOCAL
last_reviewed: 2026-11-18
source_of_truth_scope: [clinical_assessment_separation]
---

# Separación de valoraciones y controles clínicos

La propietaria autorizó separar los controles cognitivos, los controles instrumentales IADL y la valoración inicial de Enfermería con un máximo de 100 tablas físicas, incluidas las técnicas y las expertas. El PostgreSQL local queda con **63 operativas + 20 expertas + 16 técnicas = 99 físicas**. El núcleo sin las extensiones explícitas de Gerencia queda con 94.

| Tabla | Columnas antes | Columnas después |
| --- | ---: | ---: |
| `registros_observaciones_cuidado` | 71 | 44 |
| `preadmisiones` | 66 | 24 |

El esquema recupera `controles_cognitivos`, `controles_funcionales_iadl` y `valoraciones_enfermeria_preadmision`. Las dos primeras conservan los códigos, autores, fechas y relaciones de sus observaciones. La segunda mantiene el vínculo al control rectificado y la restricción de atención del mismo residente. La valoración conserva un vínculo único y restrictivo con su solicitud, la identidad y autoría originales, y las validaciones clínicas. Admitir, revisar y aprobar una solicitud mantienen su flujo. Los modelos y el lector experto siguen usando las identidades lógicas originales.

La migración `2026_11_18_000001_separate_clinical_assessments` aplica las reconstrucciones verificadas de las migraciones anteriores dentro de una transacción, redirige las claves foráneas y conserva las protecciones de identidad e historial. En SQLite, controles de escritura conservan la garantía de atención y residente de IADL. En PostgreSQL la conserva una clave foránea compuesta. Para revertir, se debe ejecutar el esquema y el código correspondientes a la misma versión.

El ensayo local de ida y vuelta comparó las filas originales de las 96 tablas y validó su igualdad tras la reversión. Se respaldó la base en almacenamiento privado antes de aplicar la migración. La instalación conservó los **30 residentes, 20 controles cognitivos, 2 IADL y 2 valoraciones iniciales**; `registros_observaciones_cuidado` conservó sus 305 observaciones de movilidad, conducta y sueño. La verificación posterior del experto recuperó seis versiones de conocimiento y comprobó 13 claves foráneas de su contrato.

La migración `2026_11_18_000002_remove_derived_context_columns` retira `cod_residente` de `prescripciones`, `administraciones_medicacion` y `estudios_clinicos`, y `cod_version_modelo` de `relaciones_semanticas` y `mapeos_variables_fuente`. El residente procede de la atención obligatoria; la administración lo recupera por su prescripción. La versión procede del nodo o variable. Las lecturas calculan esos datos por vínculos indexados y siguen entregándolos a los formularios, reportes, agenda y DTO del experto. Los valores recibidos explícitamente se validan antes de guardar y se excluyen de la fila persistida.

Las claves foráneas simples siguen obligando a que existan los padres. Las protecciones SQL rechazan extremos expertos de versiones diferentes, cambios de versión de padres referenciados y traslados de registros clínicos a otro residente. En PostgreSQL se usan bloqueos de fila compartidos para coordinar las inserciones con los cambios de contexto. La reversión reconstruye los cinco datos desde sus relaciones, recupera sus restricciones compuestas y sus índices. El contador físico sigue siendo 99.

Antes de retirar las cinco columnas se respaldó la base de 99 tablas y se compararon las proyecciones completas con las filas originales. Las 98 tablas distintas de `migrations` reconstruyeron exactamente sus valores. El lector experto recuperó las seis versiones existentes. Los 30 residentes y las observaciones previas se conservaron.

La entrega reduce las dos tablas anchas y elimina estas cinco dependencias redundantes. El dictamen global de quinta forma normal requiere estudiar las dependencias de reunión de las demás relaciones, además de las dependencias funcionales y multivaluadas. La instalación no constituye una certificación de 5FN de todo el sistema.

## Verificación del alcance

- La selección de separación, rollback poblado, lectura de fuentes expertas, contexto derivado, red semántica, agenda de medicación y autoría completó **39 pruebas y 228 comprobaciones en SQLite**, sin fallos.
- La misma selección completó **39 pruebas y 227 comprobaciones en PostgreSQL desechable**, sin fallos. Incluye consultas por relaciones y el rechazo de versiones mezcladas, del cambio de residente y de una autoría manipulada. Se conservan las excepciones de autorización que ya estaban documentadas para los casos originales.
- Los contratos de inventario, códigos correlativos, talla canónica y adaptadores completaron **20 pruebas y 362 comprobaciones en SQLite**, sin fallos.
- La concurrencia PostgreSQL completó **una prueba y cuatro comprobaciones** con un segundo proceso. Mientras la prescripción estaba sin confirmar, el intento de trasladar su atención agotó el bloqueo de fila. Después del commit, el mismo traslado fue rechazado por la protección de contexto. El registro mantuvo su residente.
- Se verificó la sintaxis PHP del alcance y la ausencia de errores de espacios en el diff. El cambio afecta persistencia y consultas; no introduce cambios de frontend que requieran recompilar assets.

Comandos de las selecciones finales: `php vendor/phpunit/phpunit/phpunit --no-progress --filter` y `scripts/probar-postgres.ps1 -Filtro`, con las clases `ColumnasRelacionadasTest`, `AgendaMedicacionTest`, `AdministracionMedicacionIntegrityTest`, `RedSemanticaEloquentRepositoryTest`, `ValoracionEnfermeriaAutoriaTest`, `ConsolidacionControlesObservacionesTest` y `ConsolidacionValoracionInicialTest`. El ensayo concurrente usa `ContextoNormalizadoConcurrenciaPostgresTest`. Los registros y respaldos quedan en almacenamiento privado. La suite completa y la CI del repositorio quedan fuera de esta verificación focal.
