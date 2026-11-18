---
title: "Reparto autorizado de valoraciones profesionales"
status: APPROVED_INSTALLED_LOCAL
last_reviewed: 2026-11-18
source_of_truth_scope: [professional_assessment_partition]
---

# Reparto autorizado de valoraciones profesionales

La propietaria autorizó expresamente distribuir la relación de 51 atributos en dos de 30: Psicología/Pedagogía y Funcional/Nutrición. Esta aprobación amplía únicamente las tres separaciones anteriores de la [decisión clínica del mismo día](DECISION_SEPARACION_CLINICA_2026_11_18.md). No autoriza otras tablas, reglas clínicas ni permisos. El máximo adoptado es de **100 tablas físicas**, incluidas las expertas y técnicas.

| Relación física final | Tipos de registro | Atributos |
| --- | --- | ---: |
| `valoraciones_profesionales` | FUNCIONAL, NUTRICIONAL | 30 |
| `valoraciones_psicopedagogicas` | PSICOLOGICA, PEDAGOGICA | 30 |

El inventario final es **64 operativas + 20 expertas + 16 técnicas = 100 físicas**: 84 de negocio. El núcleo de instalación automática, sin las extensiones explícitas de Gerencia, tiene 95 físicas. El corte anterior de 99 se conserva como evidencia histórica.

## Persistencia e integración

La migración `2026_11_18_000003_partition_professional_assessments` reutiliza los contratos originales congelados de cada especialidad. Dentro de una transacción reconstruye los cuatro tipos y los distribuye en los dos grupos aprobados. Los pasos intermedios son transitorios; el inventario final es 100. Las protecciones de copia comparan las filas y rechazan contratos diferentes antes de retirar sus copias intermedias.

Cada fila mantiene su identidad, residente, profesional, fecha, estado y valores originales, incluidos decimales, booleanos y valores nulos. Las tres valoraciones clínicas conservan su atención obligatoria y la restricción de que pertenezca al mismo residente. Pedagogía conserva su actividad opcional, sin inventarle una atención clínica. Las restricciones por tipo impiden llenar campos de otra especialidad. No se sustituyen relaciones por texto, JSON ni pares campo/valor.

Psicología/Pedagogía conserva dos identidades generadas por tipo. La FK de `soportes_valoracion_petersen.cod_valoracion_psicologica` apunta a la identidad psicológica de la nueva relación, conservando su código y rechazando un seguimiento pedagógico. Funcional/Nutrición conserva las identidades lógicas mediante sus modelos, sin añadir dos columnas generadas innecesarias. Las claves foráneas conservan índices y se mantienen índices de consulta por tipo, residente y fecha.

Los cuatro modelos, la asignación de códigos, el registro compartido de consumos, los informes psicológicos, el inicio de Pedagogía, el inventario del experto y el seeder histórico usan las relaciones finales. Los nombres lógicos y permisos públicos se mantienen. El campo textual pedagógico `atencion` no sustituye la relación con el episodio clínico del modelo psicológico, incluso en recargas y consultas con relaciones.

## Seguridad, reversión y límites

La transición no cambia Policies, competencias, destinatarios ni reglas de inferencia. Conserva la prohibición de borrado físico ordinario de los modelos clínicos. La reversión recupera la relación de 51 atributos y redirige sus referencias. Un esquema modificado fuera de este contrato impide una reversión parcial. Código y esquema deben desplegarse o revertirse juntos.

El ensayo local PostgreSQL de ida y vuelta reconstruyó exactamente todas las filas de las 99 tablas originales. La prueba contempla los cuatro tipos con datos sintéticos en SQLite y PostgreSQL, incluidas restricciones de escritura directa, referencias, precisión y reversión poblada.

La menor anchura facilita mantener los grupos aprobados. La cantidad de columnas no demuestra ni refuta por sí sola 4FN o 5FN. Esta entrega no certifica 5FN global: las dependencias de reunión de las demás relaciones requieren su propio análisis. Siguen existiendo otras tablas anchas, entre ellas hechos expertos de 49 atributos, observaciones de cuidado de 44 y evaluaciones expertas de 43. No se modifican en esta autorización.

## Evidencia de instalación y pruebas

La instalación local PostgreSQL del 18/11/2026 verificó un respaldo previo con lectura completa del archivo. La proyección conjunta de los dos grupos reconstruyó exactamente las 51 columnas y todas las filas originales, incluida la identidad psicológica generada. Todas las demás tablas originales conservaron exactamente sus filas, salvo la entrada administrativa de migración. Permanecen **424 valoraciones psicológicas, 30 residentes y 51 cuentas**; la base activa no tenía registros funcionales, nutricionales ni pedagógicos antes del reparto. Los cuatro tipos se cubren con fixtures de pruebas, sin inventar filas en la base activa.

La lectura con relaciones recuperó los 424 contextos psicológicos; el inventario experto verificó las referencias y el cargador recuperó las seis versiones de conocimiento existentes. No quedaron FK PostgreSQL sin validar. El total instalado es 100 físicas, con 30 atributos en cada una de las dos relaciones afectadas.

Los respaldos y evidencias con datos se conservan únicamente en almacenamiento privado y no se incorporan al repositorio. No se ejecutaron seeders ni reinicios de la base activa.

- Bloque específico final: **4 pruebas y 30 comprobaciones en SQLite**, sin fallos.
- El mismo bloque: **4 pruebas y 30 comprobaciones en PostgreSQL desechable**, sin fallos. La prueba negativa inserta un soporte nuevo con identidad de otra especialidad; no se apoya en la prohibición de modificar un soporte histórico existente.
- Sintaxis PHP validada en los 26 archivos PHP del alcance; enlaces de la decisión y diferencias preparadas sin errores de formato.
- No hubo cambios de interfaz, dependencias ni recursos frontend; el build de frontend no corresponde a esta entrega.

- Selección final de integración: **39 pruebas y 853 comprobaciones en SQLite**, cero errores, fallos y casos omitidos.
- La misma selección: **39 pruebas y 816 comprobaciones en PostgreSQL desechable**, cero errores, fallos y casos omitidos; el servidor temporal se detuvo y su directorio se retiró con las guardas del ejecutor.
- La selección comprende reparto (4), adquisición/contexto del experto (5), consulta/guardado psicológico (4), seguridad transversal (4), inventario/identidades (2), códigos (5), reversiones de consolidaciones clínicas, documentos e historia laboral (13), inventario de Gerencia (1) y contrato físico de entradas del experto (1). Se usó PHPUnit por archivos concretos y un filtro de esos casos. Las selecciones más amplias se interrumpieron por duración y no se consideran aprobadas; no se afirma una suite global completa.
- La comprobación final de solo lectura confirmó 64 operativas, 20 expertas y 16 técnicas, las dos relaciones de 30 atributos, 424 valoraciones psicológicas, 30 residentes, 51 cuentas y una sola entrada de esta migración.

Los consumidores cambiados mantienen las entidades y códigos V2. Las reconstrucciones históricas utilizadas durante la migración son transitorias y no crean una capa de compatibilidad ejecutable para el producto.
