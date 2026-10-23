# Evidencia bibliográfica y aceptación técnica

Fecha de verificación: 22/10/2026. Estado: **fuentes externas verificadas; sin dictamen profesional atribuido**.

## Alcance de esta actualización

La investigación fundamenta decisiones de diseño y comprueba referencias. La aceptación técnica se acredita ejecutando el software y sus pruebas. Ninguna de estas actividades registra una firma clínica ni demuestra por sí sola desempeño con residentes reales. No se cambian los estados de aprobación de las reglas en la base de datos.

El desarrollo, las pruebas y la ejecución autorizada del motor continúan sin exigir una firma profesional previa para cada evaluación. Las revisiones de Medicina y Psicología son posteriores, independientes y se vinculan a resultados conservados. La validación clínica se registra como una dimensión distinta de la terminación técnica del software; no se usa como requisito para programar formularios, corregir errores o comprobar la inferencia con casos sintéticos.

## Fuentes verificadas

| Fuente primaria u oficial | Información comprobada | Aplicación al sistema y límite |
| --- | --- | --- |
| [IMDRF/SaMD WG/N41FINAL:2017 — Clinical Evaluation](https://www.imdrf.org/documents/software-medical-device-samd-clinical-evaluation), publicado 21/09/2017 | Distingue asociación clínica válida, validación analítica/técnica y validación clínica. | Mantener evidencias separadas del fundamento bibliográfico, la corrección del procesamiento y el desempeño clínico. Es un marco de referencia; no se afirma una autorización regulatoria de RememberMind. |
| [National Institute on Aging — Assessing Cognitive Impairment in Older Patients](https://www.nia.nih.gov/health/health-care-professionals-information/assessing-cognitive-impairment-older-patients) | La selección de instrumentos depende del entorno, edad y características de la población, idioma y competencia del aplicador; NIA no avala una prueba específica. | Conservar identidad instrumental, población y condiciones de aplicación. No adoptar un umbral universal ni deducir respuestas individuales de una publicación. La página se contrastó mediante su contenido indexado; la apertura directa devolvió HTTP 405. |
| [MoCA Cognition — Terms of Use](https://mocacognition.com/terms-of-use) y [FAQ](https://mocacognition.com/faq) | La política oficial consultada distingue el uso del puntaje total para tamizaje de la interpretación de tareas, subpuntuaciones y dominios, que mantiene requisitos de capacitación/certificación. | No reproducir contenido protegido ni considerar el puntaje global como cinco mediciones de dominio. La incorporación concreta requiere cotejar la licencia y la versión aplicables. Se consultaron extractos oficiales indexados; la apertura directa de términos agotó el tiempo de respuesta. No se equipara la política antigua de la página de permisos con los términos más recientes. |

No se añadió un instrumento, baremo, umbral o equivalencia a los perfiles institucionales a partir de estas referencias. La bibliografía no identifica qué prueba recibió un residente ni sus resultados originales.

## Recorrido conservado comprobable sin cambiar el esquema

Se reutilizan `EjecutarObservaciones`, `ConsultarObservacionesConservadas`, `ValoracionRevisable`, `RevisarValoracionExperta` y `ConsultarRevisionesValoracionExperta`.

La prueba `CicloObservacionalConservadoTest` comprueba mediante Livewire: ejecutar y guardar una evaluación observacional, recuperar su informe, modificar después una fuente sintética y revisar la evaluación guardada por ambas disciplinas. Se verifican los dos órdenes de revisión, las autorías independientes, la conservación tipada del resultado y la ausencia de una clasificación DCL inventada.

Esta prueba utiliza datos sintéticos en una base desechable y no genera aprobaciones de personas reales. El alcance es el contrato observacional existente. La conservación íntegra del DTO compuesto instrumental/Petersen sigue siendo una implementación distinta, pendiente de un diseño institucional ejecutable y de su instalación autorizada. No se afirma que esa persistencia exista.

Comando de comprobación: `php artisan test --filter=CicloObservacionalConservadoTest --compact --stop-on-failure`. Resultado en SQLite desechable: **2 pruebas aprobadas, 40 aserciones, cero fallos**. Se comprobó el recorrido con Medicina primero y con Psicología primero. No se añadieron funcionalidades duplicadas ni se modificó el esquema para repetir un comportamiento ya existente.

El mismo recorrido pasó en PostgreSQL mediante `powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro CicloObservacionalConservadoTest`: **2 pruebas, 40 aserciones, cero fallos**. Se usó un cluster temporal propio, detenido al concluir. La base institucional no fue modificada. Pint pasó sobre la nueva prueba. No se modificaron componentes frontend en este incremento, por lo que no se repitió el build previamente aprobado.

## Estado que debe figurar en la documentación

- Fundamento externo: **referencias verificadas**, con sus límites de acceso y aplicación.
- Software: **aceptación técnica** sólo para los recorridos y pruebas ejecutados.
- Firmas clínicas: únicamente las registradas por usuarios profesionales reales sobre resultados conservados.
- Validación clínica del sistema: pendiente hasta disponer de evidencia de esa actividad; no se sustituye por una atribución a un experto inexistente.

No se modificaron registros institucionales, tablas, permisos ni aprobaciones clínicas. Esta nota aclara los estados; no elimina historia documental ni altera contratos clínicos.
