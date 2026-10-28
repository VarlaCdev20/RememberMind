---
title: "Contexto de evaluación: idioma e informantes, sin desnormalizar"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-28
owner: ApoyoDecision
source_of_truth: false
verification_scope: SCHEMA_CONTRACT_AUDIT_AND_READ_ONLY_INVENTORY
runtime_verified: true
---

# Alcance de esta continuación

La propietaria indicó continuar después de [vincular la aplicación instrumental con su atención](ENLACE_ATENCION_APLICACION_2026_10_28.md). Se revisaron los pendientes de idioma e informantes sin asumir una nueva relación, cardinalidad o catálogo. La autorización de ampliar campos preservando4FN sigue acotada por la [decisión de no crear más tablas](../base-de-datos/DECISION_SIN_NUEVAS_TABLAS_2026_10_27.md). «Dale» o «continúa» no sustituyen esa prohibición.

La [extensión escalar de escolaridad](../base-de-datos/DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md) y la FK de aplicación a atención están instaladas. No identifican por sí mismas el idioma utilizado ni las personas que participaron en una entrevista. La captura narrativa existente permanece disponible como relato humano, sin extracción automática.

## Fuentes y diferencias de grano

| Relación o dato revisado | Grano y uso vigente | Por qué no cierra el pendiente |
|---|---|---|
| `atenciones` | Un episodio con residente, profesional, área, fecha y contexto escalar. | No contiene una relación de participantes ni un conjunto estructurado de idiomas utilizados. Añadir campos numerados o listas no conserva ese grano normalizado. |
| `aplicaciones_instrumento.cod_atencion` | Una aplicación puede referirse a su atención. | Conserva la referencia, pero no crea identidad de informante ni acredita condiciones de administración de la prueba. |
| `residentes_contactos` | Un vínculo entre residente y contacto, con parentesco/roles. | Ser responsable principal, familiar o contacto de emergencia no prueba haber informado en una atención ni conocer el nivel previo. |
| Versiones de vínculo de contacto | Historia del vínculo maestro y sus cambios institucionales. | Una versión del vínculo no es una participación clínica en un episodio. |
| `notas_clinicas` | Una nota con autor, atención, fecha, contenido y sucesión para corrección. | Conserva narrativa; no es una tabla de participantes. No se reinterpretan notas ni su contenido como FK o hechos estructurados. |
| `participantes_actividad` | Participación de residentes en actividades. | No representa contactos informantes de una evaluación; reutilizarla cambiaría su dominio. |
| `soportes_valoracion_petersen` | Un soporte tipado de un juicio, con fuentes/FK admitidas y contrato cerrado. | No es un registro de participantes. No se añade un tipo «informante» ni una FK de contacto para eludir el modelo aprobado. |
| Idioma de una edición o referencia | Identidad del material o población normativa. | No demuestra el idioma hablado por la persona ni el empleado durante su evaluación. |

Fuentes rectoras: [diccionario](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [notas clínicas](../../database/migrations/2026_09_18_001022_create_notas_clinicas_table.php), [captura original Petersen y soportes](../../database/migrations/2026_10_19_020000_create_captura_petersen_original.php), [contrato de fuentes admitidas](../../app/Modulos/ApoyoDecision/Aplicacion/Servicios/ContratoFuentesPetersen.php). Las migraciones son evidencia de la estructura, no permiso para cambiar su propósito.

## DOMAIN GAP y decisión funcional pendiente

**Problema:** no hay un contrato general adoptado para identificar los idiomas utilizados ni los informantes efectivos de cada episodio. La existencia de contactos, notas y referencias bibliográficas no lo sustituye.

**Impacto:** no se completan D03/D06 ni se generan hechos clínicos a partir del parentesco, responsable principal, país, idioma del material o relato libre. D02 continúa parcial hasta definir la aplicabilidad de la fuente para cada método.

**Cardinalidad por resolver con la propietaria:**

1. ¿Una evaluación registra varios idiomas utilizados o únicamente un idioma principal?
2. ¿Una atención registra varios informantes identificados o únicamente uno?

Se plantearon estas dos preguntas funcionales durante el trabajo. No constituyen una aprobación para crear tablas. Hasta recibir la definición no se implementa una restricción artificial de un idioma o un informante, ni se da por aprobado un conjunto multivaluado.

**Opciones técnicas delimitadas:** un atributo principal único puede ser escalar si la institución adopta expresamente esa semántica. Un conjunto de personas o idiomas requiere filas con su grano y procedencia; si implica una relación no existente, conserva la prohibición de nuevas tablas y exige una decisión distinta. No se propone instalar una tabla por el mero hecho de continuar.

**Justificación de4FN:** los conjuntos independientes de informantes e idiomas no deben cruzarse en filas de atención, duplicando el episodio para cada combinación. Tampoco se representan mediante JSON/EAV, IDs concatenados o columnas `informante_1`, `informante_2`, etc. La forma normal se preserva manteniendo el pendiente cuando no hay una relación aprobada. Esta revisión no certifica la4FN global de toda la BDD.

## Avance implementado sin depender de esa decisión

El [inventario de entradas](../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/InventarioExperto.php) añade `contexto_evaluacion` a su proyección existente. [La herramienta ya disponible](../../scripts/inventariar-experto.php) lo expone en `contrato_entradas.contexto_evaluacion` sin modificar rutas o pantallas:

- Verifica la estructura de escolaridad por atención, sus columnas y la FK de aplicación a atención.
- Distingue estructura existente de datos registrados y de aplicabilidad instrumental.
- Verifica el vínculo maestro residente-contacto y declara explícitamente que ese vínculo no acredita participación en una entrevista ni conocimiento previo.
- Mantiene pendiente el contrato general de idioma por episodio; el idioma de un material no se usa como dato del caso.
- No produce puntuaciones, hechos del caso o diagnósticos; no selecciona responsables, consulta filas clínicas o crea estructura faltante.

Los booleanos del inventario describen capacidades e implicaciones del contrato, no respuestas clínicas de un residente. Un `false` como `vinculo_maestro_acredita_participacion` significa que esa relación maestra no prueba participación; no significa que una persona no haya participado. Las claves previas del inventario y sus consumidores conservan su forma.

## Verificación y corte local

- SQLite en memoria: **12 pruebas/435 aserciones** de contrato de entradas, mapa de fuentes y conteos del inventario.
- PostgreSQL18 desechable: **11 pruebas correctas/399 aserciones**, con **1 omitida** por comprobar exclusivamente `PRAGMA query_only` de SQLite. Runner sin privilegios globales; cluster propio detenido y eliminado al terminar.
- Escenarios: base sin residentes, esquema completo, esquema totalmente ausente, extensión de escolaridad no instalada, conexión SQLite de solo lectura y motor no admitido. Se comprueba que el inventario no crea campos ni lee/escribe filas clínicas, también de atenciones/contactos/notas.
- Ejecución local de `php scripts/inventariar-experto.php`: transacción de solo lectura confirmada. Estructura de escolaridad/FK disponible y vínculo maestro de contactos verificado; idioma episódico sigue sin contrato general y el vínculo maestro no acredita informantes del caso. Se consultaron esquema y catálogos permitidos, sin expedientes. Evidencia local no versionada: `output/inventario-contexto-local.json` y resultados `output/inventario-contexto-*`.
- Sin cambios de BDD, permisos, catálogos clínicos, inferencia, datos institucionales o envíos de correo. No se añadieron nombres de dominio V1. Build y QA visual no aplican: solo cambian inventario técnico, tests y documentación; no hay modificación de interfaz.

La captura estructurada de idioma e informantes permanece pendiente de su definición y modelo autorizados. No se presenta esta revisión como cierre de ambos formularios ni como validación clínica del sistema experto.
