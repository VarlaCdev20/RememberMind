# Observación cognitiva — Enfermería

Fecha: 13/10/2026. Alcance: captura individual desde Mis residentes, consulta y continuidad. Sin cambios de BDD, motor experto, prescripciones ni permisos. La propietaria indicó continuar **sin imagen**, tomando Signos vitales como referencia.

## Fuentes y contrato

- CURRENT: instrucciones de la propietaria; baseline operativo V2.1 y extensión V2.2; diccionario de `controles_cognitivos` (sección 39); arquitectura híbrida; contrato visual y limpieza/descarte.
- Implementación de vocabulario: selectores de `seguimiento-diario-panel.blade.php`. El nuevo formulario conserva todos sus valores, sin reducir matices a un código genérico de normalidad/dificultad.
- Consumidores examinados: `SeguimientoDiarioService`, `GestionarCuidadosHttp`, `ControlCognitivo`, selector, navegación, autorización clínica, TurnoEnfermeriaService y mapeos del motor experto. No se modifica el motor ni el seguimiento diario existente.
- Resuelto: las etiquetas «Sin dificultad aparente / Con dificultad» del brief eran propuestas de presentación, no códigos autorizados. Se conserva el vocabulario actual.
- Resuelto: el HTTP omitía `repite_preguntas`, `olvida_indicaciones`, `reconoce_personas` y `reconoce_entorno`; ahora delega en una acción que valida y guarda los quince indicadores. Se retira su definición de validación anterior para evitar dos contratos divergentes.
- No se incorpora MMSE, MoCA, Pfeiffer, puntuación, clasificación de gravedad ni diagnóstico.

## Flujo UX y arquitectura

Mis residentes → Ver resumen → Registrar → Observación cognitiva → contexto real → captura A/B/C/D → validación → guardar → resultado confirmado → historial / nueva observación / volver al residente.

- Entrar conserva al residente, inicia vacío y no escribe.
- Jornada, personal y fecha proceden del servidor. La ubicación utiliza ocupación activa → cama → habitación.
- Captura y orientación ocupan aproximadamente 70/30 en escritorio; tablet reorganiza el panel y móvil utiliza una columna. Contexto profesional completo disponible mediante disclosure en móvil.
- Superficies, botones y tipografía reutilizan el Design System vigente y los tonos compartidos de la captura. No se crea otra paleta para Cognición.
- El progreso cuenta respuestas, incluidos `false`. No observado/NULL no cuenta; nunca representa desempeño.
- Las señales usan selección azul neutra, texto y borde; no se colorea «Sí» como normalidad. Confusión/cambio generan recordatorios contextuales autorizados, sin alerta persistida.
- La gráfica es una secuencia categórica de respuestas reales, con fechas, detalle accesible por foco/clic/toque y preview separado. No conecta categorías con una línea numérica.
- 0 registros: vacío; 1: un punto sin tendencia; varios: secuencia cronológica real. Máximo 200 puntos explícitamente comunicado; historial paginado de 12 en 12, incluidos estados originales.
- Los resúmenes por dominio proceden de respuestas reales de los registros mostrados, no de una fecha global atribuida a todos los dominios.

## A. Archivos y finalidad

Rutas relativas a la raíz del proyecto; todos los archivos de producto permanecen en el propietario Cuidados o la infraestructura visual compartida existente.

| Archivo | Finalidad |
|---|---|
| `app/Modulos/Cuidados/Dominio/ObservacionCognitiva.php` | Quince campos, etiquetas y vocabulario existente por indicador. |
| `app/Modulos/Cuidados/Aplicacion/Acciones/RegistrarObservacionCognitiva.php` | Autorización, normalización, validación, transacción, autoría y reintento idempotente por captura. |
| `app/Modulos/Cuidados/Aplicacion/Consultas/ConsultarObservacionCognitiva.php` | Consulta autorizada, filtros, paginación, DTO plano, última observación y resúmenes por dominio. |
| `app/Modulos/Cuidados/Presentacion/Livewire/Paginas/ObservacionCognitiva.php` | Captura/contexto bloqueado, errores, confirmación, limpieza y reintentos de consulta. |
| `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/observacion-cognitiva.blade.php` | Modal A–D, identidad, resumen, orientación, acciones y ventanas. |
| `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/partials/cognicion-evolucion.blade.php` | Comparación, preview y secuencia temporal categórica. |
| `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/partials/cognicion-historial.blade.php` | Filtros, estados, lectura original y paginación. |
| `app/Modulos/Cuidados/Presentacion/Livewire/Paginas/MisPacientes.php` | Apertura contextual, retorno y actualización de timeline autorizado. Se preservan otros formularios. |
| `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/mis-residentes-directorio.blade.php` | Montaje del componente en el selector actual. |
| `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/partials/quick-register-selector.blade.php` | Acceso de captura con el permiso existente y etiqueta Observación cognitiva. |
| `app/Modulos/Cuidados/Presentacion/Navegacion/NavegacionCuidadosService.php` | Resolución del formulario sin eliminar las rutas anteriores con otros consumidores. |
| `app/Modulos/Cuidados/Aplicacion/Acciones/GestionarCuidadosHttp.php` | Delegación del tipo `control-cognitivo` en la misma operación. |
| `app/Providers/ArquitecturaModularServiceProvider.php` | Alias explícito Livewire. |
| `config/vistas_modulares.php` | Registro de las tres vistas del propietario. |
| `resources/frontend/scripts/modules/observacion-cognitiva.js` | Interacción, progreso factual, comparison literal, foco y recuperación de transporte. |
| `resources/frontend/scripts/app.js` | Carga del módulo. |
| `resources/frontend/styles/design-system/patterns/observacion-cognitiva.css` | Composición, responsive y estados dentro del sistema vigente. |
| `resources/frontend/styles/design-system/patterns/care-surfaces.css` | Añade únicamente al nuevo consumidor a las superficies compartidas. |
| `resources/frontend/styles/design-system/index.css` | Import del patrón. |
| `tests/Feature/ObservacionCognitivaTest.php` | Persistencia, autorización, lectura, contexto, idempotencia, errores y flujo Livewire. |
| `tests/Frontend/observacion-cognitiva.test.js` | Progreso, ausencia/false, comparación y fallo de transporte. |
| `tests/Support/cognicion-visual-qa.mjs` | QA aislado de Blade/Alpine con datos sintéticos, sin sesión ni BDD operativa. |

## B. Componentes reutilizados

- `ui.quick-register-modal` / `ui.modal-livewire`: header, drag, scroll, footer, cierre y foco.
- `ui.clinical-trend-window`: ventana independiente no modal, bordes redimensionables y controles de teclado.
- Geometría y gestos de `rmSignosRegistro`: mover, ajustar, acotar, restablecer y retorno de foco. No se heredan cálculos clínicos de Signos.
- Interacción de captura existente de Conducta: descarte, protección de navegación y ventanas; extensión localizada sin cambiar ese formulario.
- `rmClinicalCapture`: dirty state y advertencia de recarga; `rmClinicalFormFeedback`: estado de captura/resultado.
- `ui.field`, `ui.callout`, `ui.resultado-operacion-clinica` y botones/tokens canónicos.
- Limpieza utiliza el diálogo vigente de dos decisiones y el reinicio del componente servidor. No usa restauración DOM de `ui.clinical-clear-action`, porque esta captura tiene su propio estado Livewire y código de intento.

## C. Funcionalidades

- A/B: selects de orientación y funciones, contadores y disclosure sin perder datos.
- C: siete grupos Sí/No/No observado, accesibles por teclado y con selección explícita.
- D: descripción clínica; sin máximo inventado para una columna `text`.
- Resumen y respuestas consideradas: lectura en vivo de la captura, sin inferencia ni gravedad.
- Ver gráfica: mantiene formulario; consulta periodos 7/30/90/personalizado; permite dominio/indicador, comparación, movimiento, resize y teclado.
- Ver historial: overlay de lectura, periodo/dominio/orden/paginación y detalle completo original, sin copiar respuestas a la nueva captura.
- Guardar: validación y resultado exclusivamente tras persistencia. Se desmonta la captura tras guardar para que no haya advertencia de datos pendientes sobre un registro confirmado.
- Limpiar: confirmación, mantiene identidad/jornada, borra solo respuestas pendientes.
- Cancelar/Volver/X/Escape: protege captura pendiente con Seguir editando / Salir sin guardar, sin justificativo.
- Error técnico/consulta/transporte: texto seguro, conserva datos, permite reintentar y no muestra éxito falso.
- Volver al residente refresca el resumen autorizado; la observación aparece en seguimiento reciente.

## D. Persistencia e integridad

Ocho strings anulables (máximo 30, opciones vigentes) y siete booleanos anulables. Vacío se normaliza a NULL; `false` permanece `false`. Se requiere al menos un indicador o descripción significativa; no se exige completar los quince.

Autor, residente, jornada, fecha y estado no provienen de atributos manipulables de entrada. Atención opcional debe pertenecer al residente. Se inserta historia nueva y Activitylog en la misma transacción. La PK de captura generada y bloqueada por el servidor protege reenvíos de esa captura; la PK única de SQL y recuperación del intento existente cubren colisiones de reintento. La prueba de idempotencia es secuencial: no se declara ejecutada una carrera de dos procesos.

No se cambió ningún esquema ni se escribieron datos sintéticos en la aplicación operativa.

## E. Seguridad

- Consulta: cuenta ACTIVO + `controles_cognitivos.ver` + alcance vigente de `TurnoEnfermeriaService`; familiar excluido por defecto.
- Enfermería fuera de turno: consulta de residentes propios autorizados; no escritura.
- Creación: `AutorizacionClinicaService` conserva profesión, permiso, personal/área, admisión, asignación y turno. Se revalida en cada envío y se conserva el bloqueo de continuidad de Signos existente.
- Superadmin: lectura global; no creación clínica por el mero rol (pruebas con excepción temporal desactivada).
- El alias y el contexto residente/código están bloqueados en Livewire; se validan también los accesos directos a acciones/consultas.
- Revisión de seguridad y BDD del ámbito: sin hallazgos abiertos en autoría, IDOR, nulabilidad, rollback, estado ni mapping. No acredita certificación clínica, WCAG o producción.

## F. Pruebas ejecutadas

| Comando / ámbito | Resultado |
|---|---|
| `php artisan test --compact --filter=ObservacionCognitivaTest` | 24 PASS / 120 assertions, SQLite. |
| `php vendor/phpunit/phpunit/phpunit -c phpunit.postgres.xml --filter=ObservacionCognitivaTest` | 24 PASS / 120 assertions, base aislada y runner limitado. |
| PHP Cognición + Conducta + Seguimiento diario + Sidebar + Signos y fase 3 Signos | 78 PASS / 402 assertions, antes de incorporar tres casos adicionales de Cognición. |
| PHP Cognición + MisResidentesNavegacion | 35 PASS / 168 assertions, antes del último caso de recuperación de consulta. |
| JS Cognición + Conducta + Signos | 43 PASS. |
| `npm test` | 182 PASS / 1 FAIL, previo al último test añadido. Fallo global preexistente descrito abajo. |
| `npm run build` | PASS; advertencia de tamaño de bundle existente. |
| `node tests/Support/cognicion-visual-qa.mjs` | 5 viewports exactos, reduced motion runtime, cero errores JS, movimiento/resize/foco/captura/contraste y estados 0/1/error. |
| Guardado confirmado tras desmontar captura | 1 PASS / 9 assertions (regresión localizada posterior). |
| `php artisan test --compact --filter=MisPacientesRedisenadaTest` | 165 PASS / 1726 assertions. |
| `php artisan test --compact --filter='ApplicationBoundaryTest\|VistasModularesTest'` | 3 PASS / 1 FAIL preexistente, 29 assertions. |

Falló `tests/Frontend/shell-visual-consistency.test.js:131`: exige iguales superficies de dashboard entre Enfermería y los demás roles. Los ajustes previos de `care-surfaces.css` cambian la superficie del dashboard de Enfermería. Se reprodujo **excluyendo el CSS nuevo de Cognición** con las mismas expectativas en un test temporal; sigue fallando. No se debilita el test ni se rediseñan dashboards ajenos a este brief.

El gate arquitectónico también identifica el uso preexistente de `request()` en `app/Modulos/Cuidados/Aplicacion/Consultas/ConsultaCentralCuidados.php:134`. Ese archivo era trabajo ajeno sin commit y no se modificó en este lote. Las clases nuevas de Cognición no usan HTTP ni sesión desde Aplicación.

QA usa el Blade real emitido por Feature tests, scripts reales y CSS compilado con transporte sintético: acredita composición/Alpine, no una sesión clínica operativa completa. Las acciones y renderizaciones Livewire se prueban por separado con BDD real de testing en SQLite/PostgreSQL. No se ejecutó toda la suite PHP en este lote.

## G. Pendientes y Git

- Resolver por separado el contrato de superficies entre roles y su test global JS.
- Corregir por separado la dependencia HTTP de ConsultaCentralCuidados; el gate de arquitectura global no está verde.
- No hay decisión clínica ni cambio estructural bloqueando este formulario. El vocabulario se mantiene existente, sin reinterpretación experta.
- No se realizó comprobación con la sesión operativa del navegador. No se afirma certificación WCAG, aprobación clínica nueva ni producción.
- Sin commit ni push. Árbol ya contenía trabajo sin commit de otras tareas; se preservó. La integración por rama/PR y separación de ese estado quedan pendientes.

## H. Evidencia visual

Capturas y `result.json` en `%TEMP%/remembermind-cognicion-qa/evidence/`, todas con datos sintéticos:

- `cognicion-empty-form.png`: escritorio vacío.
- `cognicion-selected-1440.png`: respuestas y contexto actual.
- `cognicion-popup-1280.png`: ventana categórica separada con un histórico.
- `cognicion-history-1440.png`: historial de lectura.
- `cognicion-error.png`: error real provocado en backend de testing y captura conservada.
- `cognicion-1440.png`: recordatorio contextual por confusión, no alerta generada.
- `cognicion-768.png` / `cognicion-390.png`: tablet y móvil.
- `cognicion-empty.png`: gráfica sin histórico; cero puntos ficticios.

Las verificaciones y capturas se ejecutan contra artefactos propios aislados, no datos reales de residentes.
