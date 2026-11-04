---
title: "Cierre del contexto de evaluación: idioma e informante principal"
status: ADOPTED_INSTALLED_LOCAL
last_reviewed: 2026-10-29
owner: Valoraciones
source_of_truth: true
source_of_truth_scope: [attention_primary_language_and_primary_informant_extension]
---

# Alcance adoptado

La propietaria solicita terminar la fase de contexto en un entregable, usando criterio técnico e investigación médica. Se mantiene la autorización de ampliar campos conservando 4FN y la prohibición de tablas nuevas. El 29/10/2026 respondió **«Sí, autorizar esa FK»** a la propuesta concreta de un idioma principal e informante principal por atención, con complemento narrativo y FK opcional hacia el vínculo existente. Se adopta únicamente esta ampliación, instalada localmente tras pruebas SQLite/PostgreSQL.

## Tres columnas por atención

| Columna | Tipo y significado |
|---|---|
| `idioma_principal_evaluacion` | Texto nullable, hasta 80 caracteres: un idioma principal efectivamente utilizado en esa atención. No es nacionalidad, lengua de una edición ni lista de idiomas. |
| `cod_residente_contacto_informante` | String(20) nullable, FK con borrado restringido a `residentes_contactos.cod_residente_contacto`: contacto vinculado que efectivamente aportó el relato principal en ese episodio. No se selecciona por defecto. |
| `informante_conoce_nivel_previo` | Boolean nullable: conocimiento previo referido por ese informante. NULL es desconocido; no acredita confiabilidad clínica ni es un criterio diagnóstico. Exige un informante seleccionado cuando tiene valor. |

El autor, residente, fecha y área siguen siendo los de la atención. No se duplican identidad, parentesco ni datos de contacto. La autorización expresa resuelve el requisito de AGENTS.md para esta FK, con su índice de soporte y borrado restringido; no concede relaciones, catálogos o permisos adicionales.

## Cardinalidad y límites

Se conserva como dato estructurado **un idioma principal y un informante principal por episodio**, ambos opcionales. Esto no afirma que solo pueda haber una lengua o una persona: otros participantes, idiomas utilizados, intérprete, discrepancias y limitaciones pueden describirse en la nota clínica existente. Esa narrativa es para lectura profesional, no una lista codificada ni una fuente extraída automáticamente por el motor. La fase no entrega un registro estructurado de todos los participantes o idiomas.

En `atenciones`, la PK determina estos atributos escalares; el nombre y parentesco del informante siguen dependiendo del vínculo existente y no se copian a la atención. No se cruzan conjuntos independientes de idiomas y personas, ni se usan columnas numeradas, JSON, EAV o IDs concatenados. Este razonamiento es local al cambio, no una certificación global de 4FN.

## Seguridad e integridad previstas

- Conservar permisos y competencias actuales: esta entrega no habilita otros roles a crear sesiones psicológicas.
- Para elegir o identificar un contacto, exigir `contactos.ver` y `residentes_contactos.ver`, además de acceso a la atención y al residente.
- Al guardar, verificar dentro de la transacción que vínculo y contacto están activos y corresponden al mismo residente; no aceptar una PK ajena ni atribuir el episodio a otro profesional.
- No convertir ausencia de informante en ausencia de deterioro. No inferir capacidad de lectura/escritura del idioma o de los años de estudio.
- Conservar contexto histórico: impedir sobrescritura por la vía del modelo y no reconstruir participación histórica desde un contacto actual.
- La migración deja las atenciones anteriores con NULL y no rellena datos. El rollback rechaza pérdida de contexto conservado.
- No cambiar reglas, perfiles instrumentales, puntuaciones, prescripciones ni permisos. El contexto no activa un método sin su contrato y versión aprobados.

## Fundamento médico y adaptación técnica

[NICE NG97, recomendaciones 1.2.1–1.2.6](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations) recomienda historia de la persona y, cuando sea posible, de alguien que la conoce, incluyendo repercusión en la vida cotidiana. [DETeCD-ADRD: instrumentos de evaluación clínica](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/) describe el valor del relato del informante, las perspectivas discrepantes y la integración de antecedentes y pruebas. [DETeCD-ADRD: atención especializada](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772716/) considera educación, cultura y circunstancias individuales al interpretar los hallazgos.

Las fuentes orientan la captura descriptiva; **no exigen un único informante ni un único idioma**. La referencia principal con complemento narrativo es una decisión técnica de alcance compatible con la restricción de no crear tablas. No sustituye una entrevista profesional ni permite diagnosticar a partir de los tres campos.

## Aceptación y despliegue

Instalación local del 29/10/2026 en `remembermind_dev`: respaldo privado verificado, FK comprobada,28.107 atenciones con columnas previas idénticas y contexto nuevo NULL. Se mantienen 122 tablas físicas,106 de negocio + 16 técnicas. No hubo relleno retrospectivo ni cambios de filas clínicas. La evidencia agregada y el informe completo de pruebas se encuentran en la [entrega de cierre](../sistema-experto/ENTREGA_FASE_CONTEXTO_COGNITIVO_2026_10_29.md). La ruta y huella del respaldo permanecen en evidencia local privada, fuera de Git.
