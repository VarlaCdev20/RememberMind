---
title: "Consolidación de documentos, valoraciones y textos clínicos"
status: CURRENT
last_reviewed: 2026-11-16
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [approved_schema_transition]
verification_scope: SQLITE_POSTGRESQL_AND_LOCAL_INSTALLATION
runtime_verified: true
---

# Cuatro uniones autorizadas e instaladas el 16/11/2026

La instrucción de la propietaria «Realiza esa unión cuidando la 3/4FN y no romper el sistema» autoriza exclusivamente las cuatro uniones siguientes, continuando las reducciones del 14/11. No amplía competencias, permisos ni reglas clínicas.

| Relación física vigente | Orígenes | Tipos añadidos | Reducción |
|---|---|---|---:|
| valoraciones_profesionales | valoraciones_profesionales + seguimientos_pedagogicos | PEDAGOGICA | 1 |
| documentos | documentos + documentos_clinicos | ADMINISTRATIVO, CLINICO | 1 |
| mediciones_clinicas | mediciones_clinicas + valoraciones_dolor | DOLOR | 1 |
| registros_textuales_clinicos | notas_clinicas + indicaciones_clinicas | NOTA, INDICACION | 1 |

El recuento instalado pasa de **68 operativas + 22 expertas + 16 técnicas = 106 físicas** a **64 operativas + 22 expertas + 16 técnicas = 102 físicas**, de las cuales **86 son de negocio**. El núcleo de pruebas sin las seis relaciones de la extensión de Gerencia tiene 58 operativas + 22 expertas + 16 técnicas = 96 físicas. No confundir ese núcleo con la base local extendida.

## Grano, dependencias y conservación de 3FN/4FN

Cada fila representa exactamente un seguimiento profesional, un archivo, una medición o un texto clínico. Se conservan identidad, autoría, residente, episodio, fechas y todos los atributos originales. La relación con una actividad sigue siendo opcional para Pedagogía; las valoraciones que requieren atención mantienen esa obligación por tipo.

La unión es disjunta mediante tipo_evento. Cada código determina los atributos de su único registro; los controles por tipo prohíben rellenar atributos exclusivos de otro subtipo y mantienen obligatoriedad y límites originales. Los alias de identidad son columnas generadas, no copias editables. No se incorporan atributos de catálogos dependientes de otras claves ni listas de valores independientes. Los contactos, prescripciones, respuestas y demás relaciones plurales mantienen sus tablas. Por ello estas cuatro uniones no introducen dependencias transitivas ni dependencias multivaluadas independientes sobre una misma fila. Esta revisión de normalización se limita a los cuatro granos tipados; no es una certificación de todo el esquema.

Las claves de las relaciones vigentes son:

- valoraciones_profesionales.cod_valoracion_profesional;
- documentos.cod_documento;
- mediciones_clinicas.cod_registro_medicion;
- registros_textuales_clinicos.cod_registro_textual.

Los identificadores de los modelos de dominio se conservan como claves generadas: cod_seguimiento_pedagogico, cod_documento_clinico, cod_valoracion_dolor, cod_nota y cod_indicacion. cod_documento_ref identifica exclusivamente archivos administrativos. Las FK entrantes utilizan la identidad del tipo correspondiente. Una indicación no puede ser la nota anterior de una corrección; una medición de signos no puede ser el origen de una reevaluación de dolor; un archivo clínico no puede sustituir un documento administrativo en consentimientos o entregas. Se mantienen las restricciones de residente y episodio existentes.

Las restricciones SQL, tipos, precisión, índices y definiciones originales están congeladas explícitamente en las cuatro migraciones 2026_11_16_000001 a 000004 y su soporte ConsolidacionTipada20261116.php. No se utilizan JSON/EAV ni vistas SQL para simular tablas retiradas.

## Transición e integración

Cada migración bloquea las relaciones afectadas en PostgreSQL, comprueba columnas y FK, copia y compara en ambos sentidos mediante EXCEPT, reorienta las FK y retira los originales dentro de una transacción. Cualquier diferencia o fallo impide confirmar. La reversión restaura las relaciones, códigos, datos y referencias originales; se probó con datos en todos los tipos nuevos, incluyendo notas corregidas, documentos vinculados y reevaluaciones.

Los modelos mantienen sus responsabilidades y filtros por tipo. Consultas de documentación administrativa, resúmenes profesionales, captura instrumental, generación de códigos, seeders y auditorías se adaptan a las relaciones vigentes. Los nombres históricos que permanecen en permisos y contratos de fuentes expertas identifican conceptos lógicos; su recuperación usa modelos sobre las tablas consolidadas. No se han cambiado reglas ni pesos del motor.

La extensión explícita de documentación de Gerencia puede aplicarse antes o después de esta transición. Se conservan sus seis campos de carga/anulación cuando ya existen. No se realiza backfill ni se inventan fechas de documentos.

## Instalación local y evidencia

Destino comprobado: PostgreSQL local remembermind_dev. Se creó y comprobó un respaldo completo previo, guardado fuera de Git en el directorio privado de la aplicación:

uniones-documentos-valoraciones-20261116-39d7baa57f32ff5c.dump

El ensayo de ida y vuelta conservó exactamente las huellas de los ocho orígenes, las tablas dependientes y las asignaciones de roles/permisos. Después se instalaron y registraron las cuatro migraciones en un mismo lote.

| Contenido previo conservado | Filas |
|---|---:|
| Valoraciones profesionales, todas PSICOLOGICA | 424 |
| Seguimientos pedagógicos | 0 |
| Documentos administrativos | 30 |
| Documentos clínicos | 0 |
| Mediciones clínicas, todas SIGNOS | 135 |
| Valoraciones de dolor | 135 |
| Notas clínicas | 135 |
| Indicaciones clínicas | 0 |
| Total | 859 |

Tras confirmar: 102 tablas físicas, cuatro migraciones registradas, cero FK pendientes de validación y seis versiones expertas existentes recuperadas por el cargador de conocimiento. Los tipos vacíos locales se comprobaron con registros sintéticos en las bases desechables; no se añadieron datos de relleno al destino.

## Verificación y límites

- SQLite: el grupo final de documentación, expediente, captura instrumental, autorización Petersen, reportes de enfermería, dolor y las cuatro uniones completó **96 pruebas y 1.346 comprobaciones**. Incluye rollback ante un cambio de esquema intermedio, separación de tipos, reevaluaciones, correcciones, importación administrativa y proyección parcial de identidad.
- PostgreSQL desechable: las mismas transiciones y los dos escenarios de renumeración, incluidos fallo intermedio y conservación de auditoría, completaron 4 pruebas y 55 comprobaciones.
- SQLite: reevaluaciones, fotografías y resumen profesional completaron 6 pruebas y 56 comprobaciones en el grupo focal corregido.
- SQLite: después de adaptar la identidad lógica del seguimiento pedagógico, los adaptadores operativos y mapeos expertos completaron **37 pruebas y 95 comprobaciones**.
- Las pruebas de migraciones anteriores de dolor y fotografías completaron 4 pruebas y 34 comprobaciones; el grupo inicial de dolor, documentación y contrato base completó 27 pruebas y 233 comprobaciones.
- PostgreSQL: los cinco casos de documentos de soporte y clasificación Petersen se repitieron tras corregir la traducción entre tabla física e identidad lógica de fuente: **5 pruebas y 99 comprobaciones**. El motor permanece sin cambios. Los dos escenarios de renumeración se repitieron después del ajuste de resolución de fuentes consolidadas: **2 pruebas y 26 comprobaciones**.
- El primer grupo ampliado PostgreSQL terminó con 85 casos, cinco errores de esa identidad de fuente y una aserción que no reconocía el código P0001 de las restricciones institucionales existentes. Ambos problemas se corrigieron y sus casos se repitieron satisfactoriamente en los grupos anteriores; no se presenta aquella ejecución inicial como verde ni como CI completa.
- La carga HTTP real de cinco fotografías de 8 MB falló por tiempo de conexión al servidor temporal, antes de persistir los documentos; la persistencia privada y sus relaciones tienen pruebas focales separadas. Ese escenario de transporte queda pendiente.
- La CI completa y la revisión visual global no forman parte de la evidencia anterior. No hubo cambios de frontend que requieran recompilación.
