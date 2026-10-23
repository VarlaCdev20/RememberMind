# Evaluación cognitiva y cuidados — entrega integrada de Enfermería

Fecha: 20/10/2026. Estado: implementación local y pruebas automatizadas; **aceptación visual final pendiente por control del navegador desactivado**.

Este informe corresponde al brief de implementación integral recibido el 20/10. Complementa la entrega anterior de explicabilidad; sus capturas anteriores no acreditan el diseño de esta versión.

## A. Estado anterior y resolución de fuentes

La ruta de Enfermería compartía el informe técnico, los controles de captura y la estructura de consulta de Medicina. Existían lectura histórica D-137, fuentes con permisos, informe narrativo y cambios locales anteriores de continuidad de cuidados. Se conservaron esos contratos y el trabajo ajeno a esta entrega.

La consulta de perfil instrumental llamaba al conocimiento declarativo actual en cada render. También se consultaban observaciones mediante el motor. Esto no correspondía a una experiencia de consulta histórica sin ejecución al cambiar de área. Enfermería ahora utiliza una proyección de mediciones autorizadas y omite ambas consultas de inferencia; Medicina mantiene su flujo existente.

Fuentes aplicadas: instrucciones raíz y scoped, ADR híbrido, contrato visual, stacks institucional/UX, documentación D-137, lectores y Policies vigentes, baseline y extensiones aprobadas. El contrato actual de BDD documenta **75 operativas + 29 expertas = 104 tablas de negocio** en `docs/base-de-datos/DECISION_REDUCCION_EXPERTA_2026_10_20.md`. Los conteos 69/70/71 son históricos. Esta entrega no incorpora migraciones ni modifica esa estructura. `IMPLEMENTACION_V1.md` no se encontró; no se reconstruyó desde memoria.

La instrucción actual de colores se aplica mediante los tokens canónicos compartidos. Sustituye los valores anteriores para lienzo, superficies y cabecera; no crea una paleta de rol ni cambia el logo. Las versiones históricas se conservan literalmente, aunque su identificador incluya el nombre técnico del motor. La prosa específica de Enfermería utiliza «Evaluación cognitiva y cuidados».

## B. Implementación realizada e integración

- La ruta nombrada existente de Enfermería permanece; entrada desde Mis residentes y resumen, retorno al directorio y agenda real con residente filtrado.
- Cabecera con breadcrumb, identidad, edad, ubicación, fecha real de evaluación, versión, estado y modo de solo lectura. Contexto compacto sticky y nombre del residente dentro de las ventanas.
- Resultado principal con narrativa respaldada, datos utilizados, cobertura y límites. Ausencia, insuficiencia, integridad y demostración local se distinguen; no se inventan diagnósticos ni se presenta una simulación como asistencia.
- Cinco áreas seleccionables, con nombres de `LenguajeResultadosExperto`, selección visible y panel de interpretación. No se altera la evaluación seleccionada al cambiar de área.
- Modal de significado (620 px), detalle del área (860 px) y evolución (900 px), reutilizando `x-ui.modal-livewire`.
- Panel de evidencias de 640 px mediante `x-ui.drawer-livewire`, con overlay y pestañas Interpretación/Registros/Cuidados. La variante acoplada de la entrega médica anterior no desplaza la pantalla de Enfermería.
- Subcomponentes documentales, mediciones actuales, referencia, método y límites. «Ver registro asociado» vuelve a consultar el componente autorizado del área y muestra instrumento, versión, fecha, autor, resultado bruto y normalizado; no abre una evidencia histórica distinta.
- Evidencias con admisibilidad, participación y consulta de fuente. Se diferencia el dato actual de la ausencia del valor original en las trazas D-137 consultadas. Diagnóstico médico documentado separado y bajo su permiso específico.
- Sin conclusión experta se pueden leer los controles cognitivos actuales autorizados, con fechas, autores y datos descriptivos; no se convierten en una conclusión integrada.
- Resultado principal y área explorada tienen contextos separados: el resultado publicable D-137 conserva el alcance de memoria al seleccionar otra área. Sus botones vuelven a las evidencias correctas. La demostración local preserva su perfil y referencias propios, su fecha de corte, requisitos y aviso; no se fuerza al contrato D-137 ni se presenta como asistencia.
- Cuidados agrupados por ocurrencia: pendientes, realizados, no realizados, cancelados y otros vigentes. Detalle de intervención, plan, objetivo, periodicidad y ejecución/omisión correspondiente. No hay casillas ni operaciones para ejecutar un cuidado en esta vista.

### Evolución y comparabilidad

- Selectores de evaluaciones del residente, distintas y en orden temporal, área y series disponibles. La comparación categórica lee estados ya conservados, sin ejecutar el motor.
- Las series numéricas se agrupan por instrumento, versión, perfil, método, referencia, componente y medida. Solo entran valores normalizados finitos de capturas admisibles. Un cero real se conserva; un ausente no se sustituye por cero.
- La comparación numérica muestra las mediciones actuales en el intervalo seleccionado, con precisión de minuto de las fechas del contrato de lectura. Advierte que no reconstruye la instantánea de inferencia histórica.
- Sin serie instrumental válida, se ofrece actividad diaria de controles durante 90 días. Está rotulada como cantidad de registros, separada de las evaluaciones elegidas y sin interpretar enfermedad ni estabilidad.
- RMCharts dibuja intervalos temporales reales. Un solo registro se representa como punto sin línea. No se rellenan días ausentes ni se calculan porcentajes de deterioro.
- Información de los puntos disponible al pasar el cursor o tocar y mediante botones por fecha operables con teclado; panel persistente para permitir «Ver registro». Lista de procedencia y tabla accesible equivalentes.
- No se dibuja radar: el backend vigente no ofrece un modelo común verificable para los cinco dominios. Se implementó el estado «Comparación gráfica no disponible» y la tabla categórica prevista en el brief.

## C. Archivos y responsabilidades

Rutas relativas a `C:/Users/CARLAENCINAS/Desktop/RememberMind`:

| Archivo / familia | Cambio |
|---|---|
| `app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php` | Estado de consulta bloqueado, exclusión de ventanas, comparación, fuentes y cuidados reautorizados; rama de Enfermería sin inferencia |
| `app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarPerfilInstrumental.php` | `consultarMediciones`: fuentes autorizadas y organización por componente sin ejecutar conocimiento declarativo |
| `app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarActividadCognitiva.php` | Conteos diarios autorizados, vigentes y no futuros |
| `app/Modulos/ApoyoDecision/Aplicacion/DTOs/ConsultaCognitivaEnfermeria.php` | Proyección plana, procedencia y agrupación de series compatibles |
| `app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarContinuidadAsistencialExperta.php` | Identidad de ocurrencia, programación y ejecución existentes para el detalle |
| `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php` | Composición de Enfermería en ruta existente, sin duplicar página |
| `.../resultados-experto/enfermeria.blade.php` | Contexto, resultado, perfil y accesos |
| `.../resultados-experto/enfermeria-ventanas.blade.php` | Significado, área, evidencias y cuidado |
| `.../resultados-experto/enfermeria-evolucion.blade.php` | Comparación, gráficos, puntos y tabla |
| `.../resultados-experto/evidencias.blade.php` | Admisibilidad y distinción fuente actual/original histórico |
| `resources/views/components/ui/{modal-livewire,drawer-livewire}.blade.php` | Reutilización de variantes de lectura; foco atrapado, inercia y bloqueo de scroll |
| `resources/frontend/styles/design-system/patterns/expert-results.css` | Layout, densidad, contexto, selección, gráfico y detalle del punto |
| `resources/frontend/styles/design-system/tokens/{colors,chart-colors}.css` | Paleta compartida solicitada y variante cognitiva contrastada |
| `resources/frontend/styles/design-system/patterns/palette-consistency.css` | Cabecera diferenciada y tinta de hover/active legible |
| `resources/frontend/styles/design-system/charts/chart-theme.js` | Variante `cognitive/strong` del contrato RMCharts |
| `resources/frontend/scripts/modules/orion-explorador.js` | Ciclo de evolución RMCharts, tema, destrucción y selección de punto |
| `tests/Feature/{EstabilizacionOrionContinuidadTest,ExploradorOrionTest}.php` | Ventanas, procedencia, permisos, comparación y errores |
| `tests/Unit/ConsultaCognitivaEnfermeriaTest.php` | Comparabilidad, ausencia, cero real y procedencia |
| `tests/Feature/{CapturaInstrumentalInstitucionalTest,SimulacionLocalExpertoTest}.php` | Lectura instrumental sin inferencia y regresión de la demostración en las pestañas nuevas |
| `tests/Frontend/{orion-explorador,chart-design-system,shell-visual-consistency,sidebar-responsive}.test.js` | Ciclo del chart, tokens, paleta, composición y contraste compartido |

Los cambios anteriores de grafos, lectores y continuidad figuran también en el árbol local. La sección Git muestra todo el WIP; no atribuye todos sus archivos a este incremento.

## D. Microinteracciones

- Hover, selección y presión de áreas con desplazamiento máximo de 1 px; sin elevar tarjetas informativas.
- Botones nativos y `aria-pressed` en selección; carga localizada y desactivación de acciones en proceso.
- Acordeones con estado expandido y controles relacionados. Pestañas cambian contenido del mismo drawer.
- Las aperturas cierran las otras ventanas; el detalle del cuidado sustituye el panel de consulta.
- Modales y drawers usan los mecanismos compartidos de foco, Escape, cierre X, scroll interno y restauración del foco de Alpine.
- Gráfica con selección persistente, tabla y aviso «Comparación actualizada». RMCharts conserva el contrato de tema y movimiento reducido; libera la instancia al destruir el componente.
- Reintentos reales tras fallos. No se muestra éxito de guardado porque esta pantalla no guarda.

## E. Accesibilidad y verificación visual

**Implementado:** nombres y contexto legibles, targets de 44 px, agenda de 48 px, foco de 3 px, identificación textual de estados, equivalentes HTML del canvas, lectura opaca bajo overlays, layout apilable y movimiento reducido.

**Verificado automáticamente en componentes compartidos:** suite frontend y prueba de shell en 390/768/1024/1280/1440 px, claro y oscuro; targets, foco, controles dentro de pantalla y contraste de texto renderizado/composición alfa en tarjeta, botón principal y campo enfocado. Estas aserciones exigen 4.5:1 para texto normal. También se eliminó una expectativa de color de input tomada durante su transición: ahora se espera a que termine y se comprueba la superficie final.

Contrastes de pares opacos de tokens, calculados como apoyo: texto principal/superficie 8.47:1; secundario/superficie suave 5.11:1; texto de acción/salvia 4.63:1; borde fuerte/superficie 3.45:1; tinta de icono/fondo de selección 5.99:1. Hover salvia oscuro utiliza tinta clara contrastada (4.57:1), en vez de conservar la tinta oscura (3.75:1).

**Pendiente y no certificado:** recorrido manual de teclado/touch, restauración de foco entre estas ventanas concretas, capturas de la nueva composición y aceptación visual a los cinco tamaños en la aplicación completa. La herramienta siguió respondiendo que el control local está desactivado después del aviso «LISTO». No se reutilizaron capturas antiguas ni se eludió ese bloqueo. La suite de componentes no equivale a certificar toda la pantalla, todos sus overlays ni WCAG completa.

## F. Backend, seguridad e integridad

- Cuenta y residente frescos, Policy existente, personal activo y alcance/asignación vigentes antes de lectura e interacciones. No se ampliaron permisos ni responsabilidades profesionales.
- Evaluación comparada y evidencia deben pertenecer al residente autorizado. Se rechazan identidad ajena, selección duplicada, fechas fuera de orden y series/puntos no ofrecidos por el servidor.
- El cuidado se busca de nuevo en las ocurrencias autorizadas. Una revocación posterior no conserva el detalle abierto.
- Capturas exigen los permisos específicos del contrato instrumental y los controles existentes de procedencia/documento. Las respuestas originales no se serializan en el estado público de Livewire.
- Identidades de consulta marcadas `#[Locked]`; la entrada no permite atribuir registros a otro profesional ni ejecutar cuidados.
- Fallos SQL/integridad reportados con mensajes seguros. Error instrumental no equivale a ausencia y bloquea una comparación no verificable.
- Sin migraciones, reglas clínicas, umbrales, diagnósticos automáticos, nuevos historiales ni dependencias. El motor técnico permanece en `motor-experto`; consultas y UI en `ApoyoDecision`.
- Revisión independiente de solo lectura: sin P1 detectados. Se corrigieron dos P2 (error confundido con ausencia y registro asociado incorrecto); una segunda revisión acotada confirmó esos cierres. No equivale a validación clínica.

## G. Pruebas y build

Ver resultados finales y comandos en el cierre agregado a continuación. Todas las filas de las pruebas son sintéticas. PostgreSQL se ejecutó con `scripts/probar-postgres.ps1` en cluster y base efímeros, usuario sin privilegios globales; no se migró ni escribió la base operativa.

Fallos encontrados durante el trabajo y corregidos: sección de diagnóstico perdido en la nueva rama (restaurada con su permiso); un mock de clase final en una prueba nueva (sustituido por contratos); inyección de fallo instrumental sin permiso para alcanzar esa consulta (fixture corregida); expectativas de colores antiguos tras la nueva instrucción y lectura de input durante transición (actualizadas contra su estado final). No se relajaron Policies, reglas clínicas ni invariantes para aprobar pruebas.

La regresión de demostración detectó además que su narrativa no tiene el campo `fuentes` de D-137. Se consume su propio contrato `funciones/fuentes_legibles`. Se corrigió una directiva Blade pegada al texto y se actualizaron las expectativas del título y del mensaje de error al copy solicitado. La prueba que prohíbe códigos del razonamiento excluye únicamente la ubicación sintética y la versión histórica exacta que la nueva cabecera exige mostrar; sigue prohibiendo identificadores de reglas, fuentes y representaciones internas en la explicación.

## H. Limitaciones y pendientes

1. Radar condicionado a un futuro contrato aprobado de escala común; la alternativa categórica está operativa.
2. Mediciones normalizadas disponibles solo cuando instrumento, versión, permisos, perfil y referencia estén vinculados. La configuración operativa sin esos contratos produce vacíos honestos.
3. Las fuentes actuales no completan valores originales ausentes de D-137. No se reconstruye ni publica una inferencia nueva al consultar.
4. La selección histórica utiliza el paginador existente de ocho evaluaciones, con navegación hacia páginas anteriores.
5. Aceptación visual final de esta versión pendiente por la herramienta de navegador bloqueada. No se declara entrega visual plenamente verificada.
6. No se introduce dependencia V1 nueva ni se renombran las identidades técnicas existentes por cambios de copy. El alias Livewire histórico registrado por el proyecto queda fuera de esta reorganización visual.

## I. Git y entrega local

Sin staging, commits, ramas, push/pull/merge/stash/reset ni despliegues. Se preserva el WIP preexistente. `git diff --check` acotado a los archivos de esta entrega no informa errores; la revisión global detecta espacios finales preexistentes en la vista de agenda, que no se modificaron para esta consulta.

La instantánea de `git status --short` y `git diff --stat` se agrega al cierre. `diff --stat` no incluye archivos no seguidos; estos aparecen en status y en la lista de responsabilidades de este informe.

**Trabajo realizado en el árbol local. No se ejecutó git add, git commit ni git push. No se realizaron despliegues.**

## Cierre de verificación

| Comando / alcance | Resultado |
|---|---|
| `php artisan test --filter='ConsultaCognitivaEnfermeriaTest\|ExploradorOrionTest\|EstabilizacionOrionContinuidadTest\|InformeCognitivoTest\|test_consulta_de_mediciones_sin_inferencia_conserva_captura_y_fuente_por_componente' --compact` | **64 passed, 368 assertions**, SQLite en memoria |
| `./scripts/probar-postgres.ps1 -Filtro 'ConsultaCognitivaEnfermeriaTest\|ExploradorOrionTest\|EstabilizacionOrionContinuidadTest\|InformeCognitivoTest'` | **62 tests, 352 assertions**, PostgreSQL efímero |
| PostgreSQL final: DTO + identidad de evidencia/área + ventanas sin inferencia + mediciones por componente | **6 tests, 51 assertions**, servidor efímero detenido y cluster limpiado por el runner |
| `php artisan test --filter='SimulacionLocalExpertoTest' --compact` | **24 pasadas y 2 expectativas pendientes** en esa ejecución; las dos se corrigieron y verificaron por separado, sin cambiar código funcional entre ambas comprobaciones |
| `php artisan test --filter='test_referencias_de_memoria_explicitas_conservan_datos_y_fechas_originales\|test_vista_previa_corrupta_muestra_error_seguro_sin_patron' --compact` | **2 passed, 28 assertions**; quedan verificados los 26 casos de esa suite, en ejecuciones separadas |
| `npm test` | **222 passed, 0 failed, 2 skipped** (224 casos); los dos omitidos son gates condicionales existentes |
| Prueba final `shell-visual-consistency.test.js` con contraste renderizado añadido | **4 passed**, claro/oscuro y tamaños incluidos en la suite |
| `npm run build` | **PASS**, 4.33 s; sin dependencias nuevas |
| `git diff --check` acotado a archivos de esta entrega | **PASS**; avisos de normalización CRLF/LF, sin alteración del índice |
| QA manual de la nueva pantalla en la aplicación | **PENDIENTE**, control del navegador bloqueado; no se certifica aprobación visual completa |

Los logs de ejecución están en la carpeta temporal del sistema con prefijo `cognitive-nursing-`; el informe conserva aquí los resultados. No se depositaron datos operativos ni capturas de residentes reales en el repositorio.

### Instantánea final de Git — árbol completo con WIP preexistente

`git status --short`:

```text
 M app/Modulos/ApoyoDecision/Aplicacion/Consultas/CapturasInstrumentalesResidente.php
 M app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarPerfilInstrumental.php
 M app/Modulos/ApoyoDecision/Aplicacion/Consultas/LecturaResultadosExperto.php
 M app/Modulos/ApoyoDecision/Aplicacion/Consultas/PrepararEvaluacionInstitucional.php
 M app/Modulos/ApoyoDecision/Aplicacion/DTOs/PresentarInformeCognitivo.php
 M app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php
 M app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php
 M app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/evidencias.blade.php
 M app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/historial.blade.php
 M app/Modulos/Cuidados/Aplicacion/Acciones/GestionarEjecucionesAgenda.php
 M app/Modulos/Cuidados/Aplicacion/Acciones/PaseTurnoService.php
 M app/Modulos/Cuidados/Aplicacion/Consultas/MiTurnoService.php
 M app/Modulos/Cuidados/Presentacion/Livewire/Paginas/AgendaEnfermeria.php
 M app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/agenda-enfermeria.blade.php
 M app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/partials/pase-turno-contexto.blade.php
 M app/Modulos/Institucion/Presentacion/Livewire/Funcionalidades/PersonalInstitucionalHorarios.php
 M app/Modulos/Valoraciones/Presentacion/Livewire/Paginas/DashboardPsicologo.php
 M app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/dashboard-psicologo.blade.php
 M app/Providers/ArquitecturaModularServiceProvider.php
 M motor-experto/src/motor_experto/adaptadores/entrada/ConstructorMemoriaInstrumental.php
 M motor-experto/src/motor_experto/soporte/conservacion/InstantaneaInstrumental.php
 M resources/frontend/scripts/app.js
 M resources/frontend/styles/design-system/charts/chart-theme.js
 M resources/frontend/styles/design-system/components/drawers.css
 M resources/frontend/styles/design-system/components/modals.css
 M resources/frontend/styles/design-system/patterns/expert-results.css
 M resources/frontend/styles/design-system/patterns/palette-consistency.css
 M resources/frontend/styles/design-system/tokens/chart-colors.css
 M resources/frontend/styles/design-system/tokens/colors.css
 M resources/views/components/ui/drawer-livewire.blade.php
 M resources/views/components/ui/modal-livewire.blade.php
 M scripts/probar-postgres.ps1
 M tests/Feature/CapturaInstrumentalInstitucionalTest.php
 M tests/Feature/CapturaPetersenOriginalTest.php
 M tests/Feature/ConocimientoCognitivoInstitucionalTest.php
 M tests/Feature/InformeCognitivoTest.php
 M tests/Feature/MiTurnoAuditoriaCierreTest.php
 M tests/Feature/PersonalInstitucionalHorariosTest.php
 M tests/Feature/ResumenCognitivoResidenteTest.php
 M tests/Feature/SimulacionLocalExpertoTest.php
 M tests/Frontend/chart-design-system.test.js
 M tests/Frontend/shell-visual-consistency.test.js
 M tests/Frontend/sidebar-responsive.test.js
 M tests/TestCase.php
 M tests/Unit/InstantaneaInstrumentalCompletaTest.php
?? app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarActividadCognitiva.php
?? app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarContinuidadAsistencialExperta.php
?? app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarExploracionOrion.php
?? app/Modulos/ApoyoDecision/Aplicacion/DTOs/ConsultaCognitivaEnfermeria.php
?? app/Modulos/ApoyoDecision/Aplicacion/DTOs/GrafoInferenciaHistorica.php
?? app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/consulta.blade.php
?? app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/enfermeria-evolucion.blade.php
?? app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/enfermeria-ventanas.blade.php
?? app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/enfermeria.blade.php
?? app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/explorador.blade.php
?? app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/grafo.blade.php
?? app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/reglas-tabla.blade.php
?? app/Modulos/Cuidados/Aplicacion/Consultas/OcurrenciasCuidado.php
?? docs/frontend/EVALUACION_COGNITIVA_ENFERMERIA_ENTREGA_2026_10_20.md
?? docs/frontend/ORION_EXPLICABILIDAD_ENTREGA_2026_10_20.md
?? docs/sistema-experto/ENTREGA_INTEGRAL_ORION_2026_10_20.md
?? docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md
?? docs/sistema-experto/FASE_0_1_ESTABILIZACION_2026_10_20.md
?? motor-experto/src/motor_experto/dominio/instrumentos/CorteDocumentoInstrumental.php
?? prototipos/orion-conservacion-0_2/
?? prototipos/orion-conservacion-100/
?? resources/frontend/scripts/modules/orion-explorador.js
?? tests/Feature/EstabilizacionOrionContinuidadTest.php
?? tests/Feature/ExploradorOrionTest.php
?? tests/Feature/GuardasPreparacionBaseTest.php
?? tests/Feature/PreparacionCompuestaCoherenteTest.php
?? tests/Feature/PreparacionPostgresRepetidaTest.php
?? tests/Feature/RedisenoEnfermeriaIntegralTest.php
?? tests/Frontend/orion-explorador.test.js
?? tests/Support/EsquemaSinTransaccion.php
?? tests/Support/RestablecerEsquemaPostgres.php
?? tests/Unit/ConsultaCognitivaEnfermeriaTest.php
?? tests/Unit/CorteDocumentoInstrumentalTest.php
?? tests/Unit/OcurrenciasCuidadoTest.php
?? work/

```

Git diff --stat (solo archivos seguidos):

```text
 .../Consultas/CapturasInstrumentalesResidente.php  |   4 +-
 .../Consultas/ConsultarPerfilInstrumental.php      |  12 +
 .../Consultas/LecturaResultadosExperto.php         |  28 +-
 .../Consultas/PrepararEvaluacionInstitucional.php  |  48 +-
 .../Aplicacion/DTOs/PresentarInformeCognitivo.php  |   7 +-
 .../Paginas/ResultadosExpertoResidente.php         | 349 ++++++++-
 .../medico/resultados-experto-residente.blade.php  | 112 +--
 .../medico/resultados-experto/evidencias.blade.php |   7 +-
 .../medico/resultados-experto/historial.blade.php  |   5 +-
 .../Acciones/GestionarEjecucionesAgenda.php        |  84 ++-
 .../Aplicacion/Acciones/PaseTurnoService.php       |  29 +-
 .../Aplicacion/Consultas/MiTurnoService.php        |  58 +-
 .../Livewire/Paginas/AgendaEnfermeria.php          | 338 +++++++--
 .../livewire/cuidados/agenda-enfermeria.blade.php  | 820 +++++++++++++--------
 .../partials/pase-turno-contexto.blade.php         |   3 +-
 .../PersonalInstitucionalHorarios.php              |   2 +-
 .../Livewire/Paginas/DashboardPsicologo.php        |   6 +-
 .../valoraciones/dashboard-psicologo.blade.php     |   4 +-
 .../ArquitecturaModularServiceProvider.php         |  63 +-
 .../entrada/ConstructorMemoriaInstrumental.php     |   5 +
 .../conservacion/InstantaneaInstrumental.php       |   5 +
 resources/frontend/scripts/app.js                  |   1 +
 .../styles/design-system/charts/chart-theme.js     |   3 +
 .../styles/design-system/components/drawers.css    |   8 +
 .../styles/design-system/components/modals.css     |   9 +
 .../design-system/patterns/expert-results.css      | 162 ++++
 .../design-system/patterns/palette-consistency.css |  10 +-
 .../styles/design-system/tokens/chart-colors.css   |   1 +
 .../styles/design-system/tokens/colors.css         |  29 +-
 .../views/components/ui/drawer-livewire.blade.php  |  26 +-
 .../views/components/ui/modal-livewire.blade.php   |   5 +-
 scripts/probar-postgres.ps1                        |   8 +-
 .../CapturaInstrumentalInstitucionalTest.php       |  15 +
 tests/Feature/CapturaPetersenOriginalTest.php      |   4 +-
 .../ConocimientoCognitivoInstitucionalTest.php     |  22 +-
 tests/Feature/InformeCognitivoTest.php             |  12 +-
 tests/Feature/MiTurnoAuditoriaCierreTest.php       |   1 +
 .../Feature/PersonalInstitucionalHorariosTest.php  |  27 +
 tests/Feature/ResumenCognitivoResidenteTest.php    |   3 +-
 tests/Feature/SimulacionLocalExpertoTest.php       |  12 +-
 tests/Frontend/chart-design-system.test.js         |   5 +
 tests/Frontend/shell-visual-consistency.test.js    |  35 +-
 tests/Frontend/sidebar-responsive.test.js          |   2 +-
 tests/TestCase.php                                 |  14 +-
 tests/Unit/InstantaneaInstrumentalCompletaTest.php |  11 +
 45 files changed, 1848 insertions(+), 566 deletions(-)

```
