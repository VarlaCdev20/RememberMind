---
title: "Propuesta acotada: contexto de evaluación en atenciones"
status: PARTIALLY_SUPERSEDED
version: "1.2"
last_reviewed: 2026-10-28
owner: Valoraciones
source_of_truth: false
verification_scope: STATIC_SCHEMA_AND_CURRENT_CAPTURE_GAP
runtime_verified: false
---

# Contexto de evaluación: escolaridad, idioma e informante

## Decisión posterior: ampliación escalar conservando 4FN

La propietaria indicó después **«amplia los campos de ser necesario pero no rompas 4fn»**. La [decisión posterior de tres columnas por atención](DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md) desarrolla escolaridad y capacidad referida de lectura/escritura sin nuevas relaciones. Esta propuesta de seis campos **no se adopta íntegramente**: idioma e informantes siguen sin esta ampliación estructural. El rechazo que se conserva debajo describe el paso anterior; no bloquea la extensión posterior autorizada.

## Decisión anterior de la propietaria — 28/10/2026

A la pregunta de aprobación de los seis campos y la FK, la propietaria respondió **«Mantener la BDD actual»**. Se adopta la alternativa 2. La ampliación queda rechazada y no instalada: no se crearán esas columnas, FK, migraciones o permisos para este alcance. El detalle de abajo conserva la propuesta como antecedente, sin autorizar su ejecución posterior.

Se mostrará `residentes.nivel_educativo` como referencia general actual de admisión y se usará la guía compartida para orientar las observaciones ya existentes. Esa lectura no crea una fotografía del contexto del episodio. No se deducen años, alfabetización, idioma ni identidad del informante. Tampoco se serializan campos nuevos en notas ni se extraen de ellas hechos automáticos. D02, D03 y D06 permanecen parciales.

Cualquier alternativa estructural futura requerirá una nueva decisión expresa; la autorización general de extensiones del 27/10 no revoca esta elección concreta.

## Solicitud y diferencia con lo disponible

La propietaria indicó continuar con el contexto de evaluación. La tabla `residentes` ya conserva `nivel_educativo` como texto general de admisión, pero no representa los años efectivamente cursados ni acredita lectoescritura. `residentes_contactos` representa un vínculo con una persona de referencia, no que esa persona haya informado durante una evaluación concreta. No se identificó una columna operativa de idioma de evaluación en las fuentes revisadas.

La [decisión de no crear tablas](DECISION_SIN_NUEVAS_TABLAS_2026_10_27.md) permite proponer campos en relaciones existentes; la gobernanza congelada mantiene aprobación específica de cambios de relaciones/FK. **Esta propuesta no está instalada ni añade campos de formulario que aparenten guardarse.**

## Antecedente no adoptado: seis columnas nullable en `atenciones`

`atenciones` ya representa un episodio con residente, profesional, área, fecha y observación. La entrevista propia se registra por ese flujo. El contexto debe pertenecer al episodio, sin convertir el dato general actual del residente en una medición histórica. `aplicaciones_instrumento.cod_atencion` ya permite vincular una aplicación al episodio; no se crea otra relación para ello.

| Campo propuesto | Tipo | Qué conserva |
|---|---|---|
| `escolaridad_anios` | smallInteger nullable, no negativo | Años de educación formal declarados/revisados en ese episodio; cero es un valor conocido y NULL es desconocido. No se deducen años desde una categoría educativa. |
| `puede_leer` | boolean nullable | Información explícita del episodio: sí/no/no conocido. No es una prueba de comprensión lectora. |
| `puede_escribir` | boolean nullable | Información explícita del episodio: sí/no/no conocido. No se deduce de la firma o del nivel educativo. |
| `idioma_evaluacion` | string(80) nullable | Idioma con el que se realizó la entrevista/evaluación; no se deduce del nombre, nacionalidad o instrumento. No introduce un catálogo cerrado nuevo. |
| `cod_residente_contacto` | string(20) nullable, FK restrictiva a `residentes_contactos.cod_residente_contacto` | Vínculo exacto del contacto que aportó información en ese episodio. El backend comprueba mismo residente y vínculo/persona activos al registrar. No selecciona automáticamente al responsable principal. |
| `informante_conoce_nivel_previo` | boolean nullable | Si está documentado que ese informante conoce el funcionamiento previo: sí/no/no conocido. No declara fiabilidad ni corroboración independiente. |

No se crea ninguna tabla, PK adicional, JSON/EAV ni lista de informantes en texto. Se conserva el grano de una atención. Cardinalidad nueva propuesta: una atención puede identificar cero o un contacto informante vinculado; un vínculo puede informar en varias atenciones. Si intervienen varias personas, esta extensión mínima no representa una relación de participantes múltiples y no se simulará con IDs en observaciones.

## Datos históricos, reglas y permisos

- Seis campos NULL para los registros existentes; sin rellenarlos desde la admisión ni inferir normalidad.
- Mismos catálogos y estados actuales de atención. Estos campos no activan MoCA/RUDAS/FAQ, métodos, correcciones educativas o umbrales del experto.
- Captura dentro de las entrevistas/aplicaciones autorizadas, con autor derivado del usuario y ámbito clínico vigente. No concede a otro rol acceso a contactos ni nuevas competencias.
- La identidad del contacto solo se consulta/selecciona con permisos vigentes de contactos y vínculos, scope del mismo residente y validación backend. Una autorización estructural no amplía por sí misma esos permisos. Teléfono, dirección, documento o correo del contacto no forman parte del selector.
- Debe distinguirse la escolaridad general del expediente de lo declarado para el episodio. El contexto histórico no se actualiza automáticamente al editar la admisión o el contacto.
- Sin contacto identificado, `informante_conoce_nivel_previo` permanece NULL; un relato del residente o equipo puede conservarse como tal en la narrativa nativa, sin una identidad falsa. La extensión no acredita por sí sola C01 o C05.
- Correcciones y acceso histórico deben respetar el mecanismo institucional antes de habilitar modificaciones de episodios ya finalizados. Esta propuesta no añade un botón de edición destructiva.

## Alternativas

1. **Añadir los seis campos al episodio** (propuesta): conserva contexto estructurado y relación verificable; requiere aprobar columnas/FK y pruebas de persistencia/autorización.
2. **Mantener el esquema**: mostrar la escolaridad general ya disponible y orientar la entrevista mediante la guía existente. Idioma, años e informante del episodio siguen sin contrato estructurado; no se anuncian como capturados ni se fabrican perfiles interpretativos.

No se elige como alternativa crear una tabla, copiar datos a `residentes` como estado global ni guardar un objeto serializado en observaciones.

## Plan anterior no autorizado

No ejecutar: la propietaria eligió conservar el esquema. El plan propuesto era: migración aditiva sobre tabla existente y prueba de conservación de filas; casts y relación de Atencion; validación y autorización en el backend; selector de contacto contextual si el permiso lo permite; campos opcionales y revisión antes de guardar; lectura histórica y procedencia; SQLite/PostgreSQL y móvil. No ejecutar migración sobre la BDD configurada antes de comprobar el destino y la aprobación.

## Alternativa elegida sin ampliación estructural

Exponer en la captura el nivel educativo general del expediente como referencia de lectura, sin convertirlo en años ni actualizarlo desde el formulario clínico. Mejorar las ayudas sobre idioma/fuente y desconocidos usando la guía compartida ya existente. Esto no sustituye ni cierra los seis datos propuestos.

## Fuentes del proyecto inspeccionadas

- [Residentes y nivel educativo](../../database/migrations/2026_09_18_001006_create_residentes_table.php).
- [Atenciones y grano de episodio](../../database/migrations/2026_09_18_001021_create_atenciones_table.php).
- [Vínculo residente-contacto](../../database/migrations/2026_09_18_001016_create_residentes_contactos_table.php).
- [Aplicación original](../../app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php) y preparación del formulario.
- [Matriz D02/D03/D06](../sistema-experto/MATRIZ_DATOS_DETECCION_DCL_2026_10_27.md).

No se enviaron correos, no se consultaron historias reales y no se modificó el esquema ni el catálogo por esta propuesta. El inventario de tablas permanece sin cambios.
