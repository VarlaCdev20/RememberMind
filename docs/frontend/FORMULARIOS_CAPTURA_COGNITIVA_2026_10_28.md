---
title: "Formularios propios de captura cognitiva y entrevista"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-28
owner: Valoraciones
source_of_truth: false
verification_scope: SCOPED_UI_AND_ISOLATED_SQLITE_WORKFLOWS
runtime_verified: true
---

# Captura clínica: aplicación instrumental y entrevista

La propietaria autorizó formularios propios de captura y solicitó fuentes médicas. Esta entrega organiza dos superficies existentes de Valoraciones: el registro compartido de aplicaciones y la entrevista/atención psicológica. No crea una evaluación cognitiva propia ni activa MoCA, RUDAS o FAQ. Las condiciones de esos instrumentos siguen en el [circuito inicial](../sistema-experto/instrumentos/CIRCUITO_INICIAL.md).

## Fundamento médico y aplicación concreta

- [Alzheimer’s Association, DETeCD-ADRD, guía clínica de evaluación](https://alz-journals.onlinelibrary.wiley.com/doi/10.1002/alz.14333): historia aportada por la persona e informante, cambios cognitivos y funcionales, ánimo/conducta y contexto de evaluación.
- [NICE NG97, recomendaciones de evaluación](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations): historia, actividades cotidianas, información complementaria y evaluación clínica; una prueba breve por sí sola no determina el diagnóstico.

Estos fundamentos se traducen en una guía opcional compartida para redactar el contexto: cambio y evolución, vida cotidiana, ánimo/conducta y condiciones de evaluación. Distingue relato del residente, informante y observación directa. Lo desconocido no se representa como normal. No se incorporan reactivos protegidos, baremos, puntuaciones, nuevas obligaciones profesionales ni criterios diagnósticos desde estas fuentes.

## Recorrido implementado

**Registro de evaluación:** contexto del residente y profesional → origen propio/documental, instrumento y fecha → respuestas originales del catálogo autorizado → observaciones → revisión de solo lectura → confirmación existente. La revisión muestra respuesta, ausencia, versión y procedencia documental cuando corresponde. Volver a editar conserva la captura; revisar no persiste. Cero, «No» y ausencia permanecen distintos. Los valores y puntuaciones originales transcritos se muestran separados; no se presenta una puntuación clínica nueva.

**Entrevista y atención psicológica:** motivo/contexto → relato, observación y actuación → registro por el flujo de atenciones existente. Conserva tipos autorizados y límites de 255/1000 caracteres. La fecha de la sesión sigue siendo la del guardado y el profesional se resuelve por el backend; no se anuncia una fecha clínica editable inexistente.

Ambas superficies comparten secciones, campos, selectores, guía, tokens y acciones de la interfaz institucional. Se conservan paleta greige/salvia, profundidad sutil, foco y ayudas junto al campo. El pie del modal permanece separado del contenido desplazable. Un defecto de desmontaje de los controles compartidos al pasar a revisión queda corregido sin modificar su semántica.

## Integración y seguridad

- [CapturaInstrumentalModal](../../app/Modulos/Valoraciones/Presentacion/Livewire/Funcionalidades/CapturaInstrumentalModal.php) añade una proyección de presentación sobre los datos ya autorizados, sin consultas nuevas. Las entradas bloqueadas y el flujo de confirmación siguen vigentes.
- [Formulario compartido](../../app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/evaluacion-geriatrica-modal.blade.php): consumidores por residente y por área.
- [Ficha psicológica](../../app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/ficha-psicologica-residente.blade.php): bloque de entrevista/sesión.
- [Guía reutilizable](../../resources/views/components/ui/cognitive-context-guide.blade.php) y [estilos de Valoraciones](../../resources/frontend/styles/design-system/patterns/valoraciones-explorador.css).

Sin DDL, migraciones, permisos nuevos, cambios de catálogo, reglas de inferencia o puntuación. No cambia la autorización, autoría ni persistencia clínica existente. No introduce dependencias V1. No se consultó ni modificó historia real para estas comprobaciones.

## Evidencia técnica y visual

- Feature: **56 pruebas, 369 aserciones** de captura y ficha psicológica; después de enriquecer la revisión documental, **4 pruebas dirigidas, 51 aserciones**. El selector compartido final se comprobó además con **2 pruebas de ficha psicológica, 21 aserciones**.
- Frontend unitario: **5 pruebas**; incluye desmontaje seguro de selector/calendario.
- Navegador: **1 recorrido E2E**, edición → revisión → volver → confirmación, conservación de «No», registro de entrevista, navegación de teclado y movimiento reducido. Captura y entrevista comprobadas en **1440, 1280, 1024, 768 y 390 px**, sin desbordamientos ni errores JavaScript. Revisión visual de capturas de escritorio y móvil.
- Contraste muestreado de texto en revisión: **7,82:1**. Es una medición localizada, no una certificación integral de accesibilidad.
- Build Vite correcto; conserva el aviso de tamaño de chunks del proyecto.
- Evidencia local no versionada: `output/captura-clinica-20261028/` y su `verificacion.json`.

El E2E reutiliza el servidor privado de pruebas y SQLite desechable, con ruta opt-in y token. No agrega rutas públicas, residentes de demostración ni simulaciones al sistema operativo. La versión sintética del fixture deja de contener el marcador de catálogo retirado «TEST»; los datos siguen identificados como sintéticos y aislados. Ejecución: `RM_CAPTURA_E2E=1 node --test tests/Frontend/captura-clinica-e2e.test.js` (establecer la variable según la shell).

## Contexto sin ampliar el esquema — actualización del 28/10/2026

La propietaria respondió «Mantener la BDD actual» a la [ampliación propuesta](../base-de-datos/PROPUESTA_CONTEXTO_ATENCION_COGNITIVA_2026_10_28.md). No se implementó el DDL. La guía compartida muestra `residentes.nivel_educativo` como escolaridad general actual de admisión, con ausencia explícita y sin convertirla a años ni acreditar lectoescritura en una fecha pasada. La captura instrumental la recibe después de la autorización existente; la entrevista reutiliza el residente ya autorizado. No se consulta ni ofrece un contacto adicional.

Se separan idioma/comunicación, fuente del relato y condiciones conocidas. No se añaden controles que parezcan persistir esos datos, requisitos clínicos, parsers de narrativa o ajustes de puntuación. Las observaciones conservan su flujo y contenido original. Las dos entradas instrumentales y la entrevista comparten el mismo componente y sus estilos adaptativos existentes.

### Verificación de esta actualización

- `php artisan test --compact tests/Feature/CapturaInstrumentalFormularioTest.php tests/Feature/FichaPsicologicaResidenteTest.php`: **60 pruebas, 407 aserciones**, SQLite en memoria. Incluye escolaridad presente/ausente, ambas entradas instrumentales, lectura sin modificar el expediente y relato original conservado sin crear una aplicación ni un juicio Petersen.
- `RM_CAPTURA_E2E=1 node --test tests/Frontend/captura-clinica-e2e.test.js`: **1 recorrido correcto**, repetido tras añadir evidencia de la guía abierta. Teclado para desplegarla, edición/revisión/confirmación, guardado instrumental y de entrevista, cinco anchos, sin desbordamiento horizontal ni errores JavaScript. Se inspeccionaron las capturas actuales de la guía en ambos formularios a 1440, 1280, 1024, 768 y 390 px. **PASS acotado a la guía y estos recorridos**, tema claro; no es aceptación global del producto ni de la barra lateral.
- `npm run build`: correcto, con el aviso existente de chunks superiores a 500 kB.
- Sin cambios de estructura, relaciones, permisos, catálogos, inferencia ni datos institucionales. No se repitió PostgreSQL: la actualización añade una referencia de lectura y ayudas; no cambia las escrituras, transacciones o bloqueos. Las pruebas de backend y navegador usan bases aisladas.
- La propuesta de seis campos se conserva como antecedente **rechazado**, y la matriz mantiene D02/D03/D06 parciales. No se enviaron correos ni se añadieron dependencias o nombres de dominio V1.

## Límites pendientes

La guía narrativa no resuelve los campos estructurados de escolaridad, idioma o informante pendientes en la matriz. El formulario solo muestra preguntas del catálogo autorizado: no acredita que exista una edición completa y utilizable de cada prueba elegida. Materiales, derechos, adaptación, puntuación y conexiones clínicas deben cotejarse por instrumento. No se repitió integración PostgreSQL en este cambio de presentación ni se declara validación clínica del experto.
