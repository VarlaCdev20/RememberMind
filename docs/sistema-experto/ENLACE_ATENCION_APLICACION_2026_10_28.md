---
title: "Enlace de la aplicación instrumental con su atención"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-28
owner: Valoraciones
source_of_truth: false
verification_scope: SCOPED_CAPTURE_SQLITE_POSTGRES_AND_RESPONSIVE_UI
runtime_verified: true
---

# Contexto del episodio y aplicación instrumental

## Fuente y alcance

La propietaria indicó continuar después de la [ampliación escalar por atención, conservando 4FN](../base-de-datos/DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md). El [diccionario vigente](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md#60-aplicaciones_instrumento--transaccional) ya establece `aplicaciones_instrumento.cod_atencion → atenciones.cod_atencion NULL`. Esta entrega usa esa relación. No requiere nuevas columnas, FK, tablas, permisos o reglas clínicas.

Se detectó que la interacción solo enviaba `cod_atencion` en transcripción y pedía teclear el código de la atención. El backend aceptaba el vínculo del mismo residente sin exigir expresamente lectura de atenciones. La relación existente no autoriza a publicar metadatos clínicos sin su permiso ni a convertir el contexto de otro episodio en las condiciones de una prueba.

## Recorrido implementado

Aplicación propia o documento original → residente e instrumento → vínculo opcional con una atención existente → respuestas originales → revisión → confirmación. El selector muestra fecha/hora, profesional y tipo legibles; no presenta la PK como etiqueta ni elige la atención más reciente por defecto. Lista hasta100 atenciones del residente y permite filtrar por fecha para localizar las anteriores. Una atención seleccionada se conserva aunque no coincida con el filtro actual. El filtro no modifica la fecha clínica de la aplicación.

El bloque plegable de referencia aparece en captura y revisión: fecha, profesional, tipo/estado y escolaridad/lectura/escritura recabadas en esa atención. Distingue0, «No» y desconocido. No consulta observaciones de la atención ni muestra contactos, documentos de identidad, teléfonos o correos. Vincular no copia los tres valores a respuestas, no ajusta puntuaciones ni acredita por sí solo la aplicabilidad de un instrumento. La referencia conserva su fecha y procedencia visibles.

Seleccionar otra atención invalida la confirmación anterior; una selección pendiente activa el descarte seguro al cerrar. Puede elegirse la atención antes de seleccionar por primera vez el instrumento. La relación ahora se envía también al guardar una aplicación propia, sin cambiar la semántica de las respuestas o la conciliación documental. La consulta instrumental existente ya recupera la identidad de la atención original; no se amplió su extractor para inferir contexto o diagnósticos.

## Seguridad y persistencia

- Captura: autenticación y cuenta ACTIVO, rol aplicador autorizado, permisos instrumentales, contexto profesional/área y Policy del residente ya existentes.
- Lectura de atenciones: exige además el permiso existente `atenciones.ver`; la consulta acota por residente y proyecta solo los metadatos necesarios, con carga anticipada del profesional.
- Sin ese permiso no se muestran opciones ni contexto. Una aplicación sin vínculo continúa disponible según sus permisos; no se concede lectura clínica por el hecho de poder aplicar un instrumento.
- Backend `AplicarInstrumento`: revalida el permiso y la Policy al vincular, y comprueba la atención del mismo residente dentro de su transacción/bloqueo existentes. Un ID ajeno o inexistente produce el mismo error de campo y no escribe respuestas ni aplicaciones. El permiso revocado entre revisión y confirmación impide guardar el vínculo.
- El vínculo continúa opcional; una cadena vacía se normaliza a NULL. Ningún autor se toma de la atención seleccionada para reemplazar al profesional aplicador original.
- Sin DDL, migraciones, cambios de catálogo, responsables, permisos concedidos o actualizaciones de atenciones. La normalización se conserva usando la FK existente y manteniendo el contexto en su episodio, sin duplicar grupos o listas.

Código: [preparación de fuentes](../../app/Modulos/Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental.php), [interacción compartida](../../app/Modulos/Valoraciones/Presentacion/Livewire/Funcionalidades/CapturaInstrumentalModal.php), [validación y escritura](../../app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php), [formulario compartido](../../app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/evaluacion-geriatrica-modal.blade.php). Consumidores: entradas por residente y por área. No se añadieron nombres de dominio V1.

## Verificación

- SQLite en memoria: **53 pruebas/391 aserciones**, repetición final completa de `php artisan test --compact tests/Feature/CapturaInstrumentalFormularioTest.php`. La primera ejecución comparaba atributos de un Model recién creado con los hidratados de SQLite y falló por tipo/orden; se corrigió la comparación con originales rehidratados, sin cambiar el dato ni debilitar la igualdad. Tres comprobaciones dirigidas pasaron antes de repetir la batería completa.
- PostgreSQL18 aislado, runner propio sin privilegios globales: **102 pruebas/1278 aserciones**, `CapturaInstrumentalFormularioTest|CapturaInstrumentalInstitucionalTest`. El arnés creó un cluster/base desechables y confirmó su parada/limpieza. No utilizó datos operativos ni se ejecutó una nueva migración sobre la base local configurada.
- Incluye ambas entradas, vínculo opcional persistido, contextos que no se copian a respuestas, lectura por residente/fecha, notas excluidas, selección fuera del filtro, fuente ajena, permiso ausente/revocado, entrada directa del backend, filtro inválido con datos conservados y confirmación invalidada al cambiar de atención. La regresión institucional cubre originales y procedencia documental en su alcance previo.
- Navegador: **1 E2E correcto**, vínculo real de la aplicación a la atención sintética, revisión/edición/confirmación y entrevista con0/No/NULL. Captura, selector y referencia inspeccionados en1440/1280/1024/768/390px, tema claro, teclado y movimiento reducido. Sin errores JavaScript ni desbordamiento horizontal. El primer examen visual detectó que los enums en mayúsculas se separaban letra por letra; se corrigió su formato y se repitió el recorrido.
- **QA visual/funcional: PASS acotado al selector, referencia y recorridos examinados**, sin afirmar aceptación global de navegación, temas o del sistema experto. El panel permanece plegable para no ocupar toda la pantalla móvil.
- Build Vite correcto, con el aviso previo de chunks superiores a500kB. Evidencia local excluida del commit: `output/enlace-atencion-instrumental-*` y capturas de `output/captura-clinica-20261028/`.

Revisión de seguridad/BDD realizada como fase de lectura separada: **PASS en el alcance anterior**, sin afirmar auditoría externa ni inmutabilidad física del contexto por SQL directo. No se enviaron correos ni se modificaron historias institucionales para verificar esta entrega.

## Límites

D02 continúa parcial: la referencia del episodio no sustituye las entradas y procedencia que exige cada método. No se activaron MoCA, RUDAS, FAQ, correcciones educativas, baremos o perfiles nuevos. Idiomas e informantes (D03/D06) conservan sus brechas normalizadas; no se los convierte en listas de cabecera para eludirlas. La revisión clínica de qué atención corresponde al acto original y las condiciones de aplicabilidad siguen siendo responsabilidad del profesional autorizado.
