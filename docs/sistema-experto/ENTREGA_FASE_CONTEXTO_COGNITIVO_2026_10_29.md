---
title: "Entrega única: contexto y procedencia de la captura cognitiva"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-29
owner: Valoraciones_y_ApoyoDecision
source_of_truth: false
verification_scope: CONTEXT_CAPTURE_AUTHORIZATION_HISTORY_SQLITE_POSTGRES_AND_RESPONSIVE_UI
runtime_verified: true
---

# Resultado y alcance

Se entrega la fase de **contexto y procedencia de la captura cognitiva**: entrevista psicológica, escolaridad y lectoescritura referidas, idioma principal, informante principal y referencia a la atención desde la aplicación instrumental. Incluye captura, validación backend, conservación, consulta autorizada, protección del cierre e inventario técnico. No requiere que existan residentes para comprobar el contrato estructural.

La propietaria solicitó completar esta fase bajo criterio técnico e investigación médica y autorizó expresamente la FK opcional del informante. La [decisión estructural acotada](../base-de-datos/PROPUESTA_CONTEXTO_IDIOMA_INFORMANTE_2026_10_29.md) complementa la [extensión de escolaridad](../base-de-datos/DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md). No se crean tablas, permisos, baterías obligatorias o reglas diagnósticas.

## Conceptos y diccionario profesional

| Concepto | Significado y registro |
|---|---|
| Atención | Episodio de un residente con profesional, área, fecha/hora y motivo. El contexto pertenece a ese episodio. |
| Escolaridad formal informada | Años cursados referidos en la entrevista. Cero es un valor conocido; vacío significa desconocido. El nivel educativo de admisión no se convierte a años. |
| Lectura y escritura referidas | Capacidades informadas, conservadas por separado como Sí / No / No se conoce. No constituyen un examen de lectoescritura. |
| Idioma principal utilizado | Una lengua efectivamente utilizada en la atención. No equivale a nacionalidad, idioma de preferencia de toda la vida o idioma de la edición del instrumento. |
| Contacto | Persona identificada vinculada al residente en el registro institucional. Un vínculo activo no demuestra participación en una entrevista. |
| Informante principal | Contacto que efectivamente aportó el relato principal de esa atención, elegido explícitamente. No se selecciona al responsable por defecto. |
| Nivel previo | Cómo se desenvolvía la persona antes de los cambios descritos. Que el informante refiera conocerlo no prueba por sí solo la fiabilidad o exactitud del relato. |
| Autor profesional | Personal derivado de la cuenta autenticada y autorizado para registrar la sesión. No es el informante ni puede elegirse libremente desde el formulario. |
| Procedencia | Atención, profesional, fecha y vínculo explícito con el informante. La aplicación instrumental conserva su propia fecha y autoría. |
| Desconocido | Ausencia de información documentada. No equivale a No, a normalidad ni a ausencia de deterioro. |
| Prueba y contexto | Las respuestas originales pertenecen a la aplicación. Vincular una atención no convierte su contexto en respuestas ni acredita condiciones de aplicación en otra fecha. |

## Fundamento médico y decisiones de diseño

La evaluación debe integrar la historia del residente y la información de quien lo conoce, las dificultades cotidianas, los hallazgos de la evaluación y sus circunstancias. Las perspectivas pueden discrepar; el registro debe conservar esa diferencia. La puntuación de una prueba breve no establece por sí sola DCL. Fundamento: [DETeCD-ADRD, instrumentos de evaluación clínica](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/).

La educación, el idioma y otras circunstancias individuales pueden afectar la interpretación y justificar evaluación más especializada según juicio clínico. Esto fundamenta registrar contexto y limitaciones; no una corrección numérica universal. Véase [DETeCD-ADRD, recomendación 14 y sección 2.6](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772716/).

[NICE NG97, sección 1.2](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations) también orienta la obtención de historia de la persona y de alguien que la conoce. El enlace oficial devolvió HTTP403 en el cotejo directo del 29/10; se conserva como referencia previamente consultada y no se le atribuye la elección técnica de cardinalidad.

**Decisión técnica de esta fase:** un idioma principal y un informante principal estructurados, ambos opcionales. No se limita la entrevista a una sola persona o lengua. Otros idiomas, participantes, apoyo de intérprete, discrepancias, barreras sensoriales/motoras y limitaciones se describen en las notas clínicas existentes, para lectura profesional. Esta entrega no ofrece un censo estructurado de todos los participantes o lenguas ni extrae hechos automáticamente desde narrativa.

## Recorrido operativo implementado

1. Psicología abre la ficha del residente, pestaña Seguimiento, y registra una nueva sesión con su competencia y permisos actuales.
2. Describe motivo; registra escolaridad y capacidades referidas solo si se conocen.
3. Indica idioma principal si está disponible. Con permisos de consulta de contactos y vínculos, puede seleccionar al informante principal que participó; sin esos permisos no se consulta ni expone su identidad.
4. Tras elegirlo, registra si refiere conocer el nivel previo. Cambiar el informante limpia esa respuesta para evitar atribuirla a otra persona.
5. Distingue en notas el relato del residente, el relato de otras personas, lo observado, la intervención y los acuerdos. La guía compartida ofrece recordatorios sin convertirlos en escala clínica.
6. Guarda la sesión: el backend valida y conserva episodio y auditoría en una transacción. Los errores conservan la captura. Cerrar con datos, incluso solo un cambio de tipo o un valor cero, ofrece continuar o descartar.
7. El historial muestra el contexto conservado, con nombres legibles y desconocidos explícitos. Un vínculo posteriormente inactivo no borra la participación histórica.
8. Una aplicación instrumental puede referir esa atención mediante el selector existente. Captura y revisión muestran el mismo componente de contexto, respetando sus permisos, sin copiar valores a respuestas ni ajustar puntuaciones.

## Persistencia, 4FN y seguridad

- Tres columnas nullable nuevas en `atenciones`: `idioma_principal_evaluacion`, `cod_residente_contacto_informante`, `informante_conoce_nivel_previo`; la segunda es FK restrictiva e indexada al vínculo existente.
- El grano sigue siendo una atención. La clave de la atención determina sus atributos principales; nombre y parentesco no se duplican. No se cruzan conjuntos independientes ni se usan JSON, EAV, listas de PK o columnas numeradas. La revisión acredita este alcance, no la 4FN global.
- SQL garantiza FK, restricción del borrado, dependencia de conocimiento previo respecto del informante y longitud no vacía del idioma. La Action garantiza mismo residente y contacto/vínculo activos: una FK sola no acredita pertenencia al residente.
- Guardado con cuenta activa, permiso, Policy, rol/profesión, área y autor del servidor. No se amplían las competencias de Medicina, Enfermería, Gerencia o Superadministración por esta entrega.
- Bloqueos en orden residente → contacto → vínculo, alineados con Social. La consulta de candidatos no implica que hayan participado; el guardado revalida después del bloqueo.
- La proyección instrumental omite observaciones, correos y teléfonos. Sin permisos de contactos, omite también FK, conocimiento previo y relaciones de identidad; no basta con ocultar el nombre en Blade.
- El modelo impide sobrescribir los seis atributos de contexto después de conservarlos. Esto no es un trigger SQL: una consulta directa podría eludir esa protección; no se afirma inmutabilidad física global. La corrección clínica debe conservar la historia mediante el mecanismo de dominio aplicable.
- No se usan notas como FK ni como hechos extraídos. Los datos antiguos quedan desconocidos; no se completan con el contacto actual, la última sesión o el país de origen.

## Integración y archivos responsables

| Responsabilidad | Implementación |
|---|---|
| Guardado de entrevista | `RegistrarSesionPsicologica` en Valoraciones/Aplicacion/Acciones |
| Consulta y validación del informante | `ContextoAtencionCognitiva` en Valoraciones/Aplicacion/Consultas |
| Fuente contextual de una aplicación | `PrepararCapturaInstrumental` |
| Interacción | `FichaPsicologicaResidente` y `CapturaInstrumentalModal` |
| Lectura compartida | `x-ui.cognitive-attention-context` en historial, captura y revisión |
| Esquema | Migración `2026_10_29_010000_add_idioma_informante_atenciones.php` |
| Inventario experto | `InventarioExperto::entradas`, separado de filas clínicas y de aplicabilidad instrumental |

## Verificación de la entrega

- SQLite: ejecución inicial de 105 pruebas/991 aserciones; regresión posterior de 13 pruebas/96 aserciones que incluye los ajustes finales de cierre, permisos y bloqueos.
- PostgreSQL 18 aislado, usuario sin privilegios globales: 112 pruebas ejecutadas, 111 correctas/1054 aserciones y 1 omitida por ser específica de `PRAGMA query_only` de SQLite. Regresión final después de los ajustes:13 pruebas/96 aserciones correctas, en `output/contexto-fase-cierre-postgres-regresion*`. Ambos clusters propios detenidos y eliminados al finalizar.
- Escenarios: NULL/cero/false, longitud y forma de entrada, informante ajeno/inactivo/inexistente, falta de permisos, fuente sin datos sensibles, cambio de informante, cierre sucio, historia, FK y borrado restringido, rollback vacío y rechazo del rollback con contexto, error de auditoría/autoría y regresiones del formulario existente.
- Revisión independiente de código BDD/seguridad: PASS acotado después de corregir el orden de bloqueos y el cierre con cambio exclusivo de tipo;3 pruebas propias SQLite/28 aserciones. No equivale a prueba de todas las carreras concurrentes posibles.
- Interfaz: recorrido real Livewire sobre SQLite desechable en 1440/1280/1024/768/390 px; selección explícita, revisión instrumental, guardado, teclado y movimiento reducido. Ejecución final: 1 prueba E2E correcta, sin errores JavaScript ni overflow horizontal y etiquetas visibles debajo de la cabecera al desplazar el foco. Evidencia `output/captura-contexto-20261029/`; sin filas sintéticas en la aplicación operativa.
- Build Vite correcto. Conserva el aviso previo de bundle mayor de 500 kB, que esta fase no pretende resolver.
- Instalación local autorizada: respaldo privado verificado con `pg_restore --list`, comparación de las28.107 atenciones por sus columnas originales, idénticas antes/después; tres columnas nuevas NULL, FK verificada y122 tablas físicas conservadas. El inventario posterior verifica la estructura sin consultar filas clínicas. No se publican el respaldo ni datos reales.

La evidencia local de ejecución permanece fuera del commit. Los tests, contrato y código reproducibles sí se versionan. No se enviaron correos ni se realizó push.

## Límite de cierre y siguiente fase

**Esta fase queda cerrada en su alcance de captura contextual y procedencia.** D02/D03/D06 siguen distinguiendo captura de aplicabilidad y calidad profesional. El siguiente trabajo del experto es cerrar derechos/versiones y perfiles de extracción de los instrumentos seleccionados, y después su integración con hechos, criterios y conservación conjunta conforme a los contratos aprobados. La configuración de un perfil no puede darse por aprobada porque existan estas columnas.

No se afirma que el sistema experto completo esté terminado, validado clínicamente o autorizado para diagnóstico autónomo. No se añadieron simulaciones operativas, tablas, reactivos protegidos, umbrales, puntuaciones ni dependencias V1.
