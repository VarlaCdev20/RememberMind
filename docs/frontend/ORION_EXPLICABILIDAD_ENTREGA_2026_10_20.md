# O.R.I.O.N. — entrega de interfaz, explicabilidad y trazabilidad

Fecha: 20/10/2026. Estado: implementación local del alcance ejecutable; sin publicación clínica nueva, commit ni despliegue.

## A. Diagnóstico inicial y fuentes

La pantalla ya contaba con lectura D-137, informe narrativo, selección de evaluación/criterio, fuentes autorizadas, captura instrumental, revisión profesional e historial. Existían cambios locales anteriores en continuidad de cuidados y otras áreas. Se conservaron sus funciones y se reutilizaron los servicios existentes.

Problemas abordados: resultado inicial disperso entre bloques, ausencia de un explorador visual de los cinco dominios, falta de grafos navegables, explicación técnica concentrada en auditoría, historial sin versión visible y cuidados de Enfermería separados de la consulta contextual. La simulación local necesitaba un aviso inequívoco en la nueva composición.

### Resolución de fuentes y conflictos

- Se consultaron AGENTS y reglas de `app`, `resources`, `tests` y `database`, ADR híbrido, baseline/diccionario BDD, contrato visual, stack UX/UI, contratos D-123/D-130/D-131/D-133/D-137/D-143/D-144 y documentación cognitiva vigente.
- El inventario de 71 operativas citado en el brief está superado por los documentos vigentes: **75 operativas + 29 expertas = 104 tablas de negocio**. Véase `docs/base-de-datos/DECISION_REDUCCION_EXPERTA_2026_10_20.md`. Esta tarea no modifica ninguna estructura.
- `IMPLEMENTACION_V1.md` y `ARQUITECTURA_COMPLETA_SISTEMA_EXPERTO.md` no se encontraron en el repositorio. No se reconstruyeron desde memoria. Se utilizaron `INTERFAZ_MEDICA_RESULTADOS.md`, `CONTRATO_INFORME_COGNITIVO.md`, decisiones y contratos actuales, además del código y pruebas.
- Se distinguen tres contratos: evaluación histórica D-137; perfil institucional actual calculado al corte; simulación local conservada. No se sustituyen entre sí.
- La Policy vigente reserva `consultarTrazabilidad` al acceso técnico de Superadministración. La petición de trazabilidad médica completa no se interpretó como autorización para ampliar silenciosamente esa frontera.

## B. Implementación realizada

1. Base compartida para Medicina y Enfermería, con títulos, acciones y profundidad según permisos reales.
2. Contexto del residente con fecha y versión; acciones superiores compactas; conclusión respaldada, alcance y límites antes del detalle técnico.
3. Perfil categórico de cinco áreas y acordeones por dominio. Los componentes instrumentales actuales se identifican expresamente como consulta al corte, separados del histórico.
4. Historial contraído antes del razonamiento; selección histórica conserva su evaluación y versión.
5. Modal didáctico y drawer con los componentes oficiales. Modal, drawer y detalle técnico no permanecen abiertos simultáneamente. El cambio de evaluación cierra el detalle anterior.
6. Grafo semántico con nodos y aristas reales de la versión exacta; recorrido por vínculos entrantes y salientes, conservando dirección y tipo originales.
7. Grafo histórico de siete niveles: fuente, mapeo, evidencia, condición, regla, consecuencia y resultado. No ejecuta reglas ni completa originales ausentes con registros actuales.
8. Selección, conexiones resaltadas, zoom, desplazamiento, restablecimiento, lista textual y detalle de nodos. Distribución vertical cuando el contenedor es estrecho; selección por teclado revela el nodo fuera del encuadre. `ResizeObserver` se desconecta al desmontar.
9. Tabla de construcción de reglas y condiciones, incluyendo `NO_CUMPLE`; una consecuencia no aportada no se conecta al resultado.
10. Distribución de evidencias mediante RMCharts, con conteos y alternativa textual. No representa deterioro ni normalidad.
11. Cuidados vigentes, estados operativos y enlace a agenda real con residente filtrado, reutilizando la continuidad existente. La pantalla no ejecuta cuidados.

## C. Archivos modificados en esta tarea

Raíz de todas las rutas: `C:/Users/CARLAENCINAS/Desktop/RememberMind`.

| Ruta completa | Cambio |
|---|---|
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarExploracionOrion.php` | Nueva consulta autorizada de grafos, con lectura fresca y versión exacta. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Aplicacion/DTOs/GrafoInferenciaHistorica.php` | Nueva proyección plana de la traza histórica validada. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Aplicacion/Consultas/LecturaResultadosExperto.php` | Metadatos legibles de condiciones/reglas, relaciones de evidencias y validación de cobertura e integridad. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Aplicacion/DTOs/PresentarInformeCognitivo.php` | Campos de presentación coherentes para la simulación, sin transformarla en resultado institucional. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php` | Estado bloqueado, reautorización, navegación y ventanas excluyentes. Archivo con trabajo previo conservado. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php` | Composición por rol, contexto y conexión de parciales. Archivo con trabajo previo conservado. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/explorador.blade.php` | Resultado, perfil, dominios, historial, pestañas y cobertura. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/grafo.blade.php` | SVG interactivo, navegación accesible y alternativa textual. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/reglas-tabla.blade.php` | Construcción completa, condiciones, soportes y consecuencias. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/consulta.blade.php` | Modal didáctico y drawer compartido por rol. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/historial.blade.php` | Historial contraído y versión por evaluación. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/frontend/styles/design-system/patterns/expert-results.css` | Patrón compartido, composición, grafos, foco y reflow. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/frontend/scripts/modules/orion-explorador.js` | Interacción visual y ciclo RMCharts; no inferencia. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/frontend/scripts/app.js` | Importación del módulo visual. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/ExploradorOrionTest.php` | Siete pruebas nuevas sobre grafos, versión, confidencialidad, IDOR, reautorización y conservación. Exportación opcional de HTML sintético para QA aislado. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Frontend/orion-explorador.test.js` | Distribución estable, orientación móvil, selección, limpieza y ciclo de gráfica. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/InformeCognitivoTest.php` | Actualización de expectativas de títulos por el nuevo contrato visual. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Feature/EstabilizacionOrionContinuidadTest.php` | Una expectativa abre ahora el drawer de Registro antes de comprobar el diagnóstico. Archivo preexistente sin seguimiento; se conserva el resto. |
| `C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/ORION_EXPLICABILIDAD_ENTREGA_2026_10_20.md` | Este informe. |

## D. Backend, integración y seguridad

Se reutilizan `LecturaResultadosExperto`, `LecturaFuenteEvidencia`, `PresentarInformeCognitivo`, consultas instrumentales/observacionales y `ConsultarContinuidadAsistencialExperta`. Los dos nuevos objetos de consulta/presentación permanecen en `ApoyoDecision/Aplicacion`.

Cada interacción nueva vuelve a resolver usuario y autorización del residente. Los identificadores de ventanas/nodos son estado bloqueado; el nodo solicitado debe existir en el grafo recién autorizado de esa evaluación. La consulta técnica no confía en un resultado enviado por el navegador. Se conservan permisos instrumentales, asignación y turno de Enfermería, cuenta activa y prohibición de previsualización de rol.

La lectura valida versión de reglas/condiciones, conjunto de condiciones esperado, pertenencia de evidencias y soportes, y extremos de relaciones de evidencia. Una condición sin permiso instrumental no serializa sus valores ni soportes, incluso en `NO_CUMPLE` sin soporte. La comprobación de integridad impide acreditar una conclusión válida desde un recorrido defectuoso.

No se modificaron Policies ni permisos; no se añadieron escrituras clínicas, diagnósticos, alertas, reglas, pesos, normalizaciones, catálogos, tablas o columnas. Tampoco se activó un contrato de publicación ni una acción artificial de análisis. No se ejecutó una migración o siembra contra la base operativa.

## E. Diseño y datos

- Paleta, texto, superficies, bordes, sombras y movimiento consumen tokens oficiales. Los grafos usan las familias `neutral`, `clinical`, `cognitive` y estados compartidos. No se reemplazó `--rm-primary` ni se creó una paleta por rol.
- Títulos, prosa y metadatos heredan las familias tipográficas del Design System. La composición usa dos columnas cuando existe espacio y una en móvil.
- Perfil categórico: no se implementó radar porque el contrato disponible no proporciona una escala común clínicamente normalizada para los cinco dominios.
- El gráfico de cobertura cuenta evidencias conservadas en la selección; incluye fecha/fuente, unidades y cantidades textuales. La simulación no se contabiliza como cobertura clínica.
- No se afirma progresión temporal con mediciones no comparables: se conserva historial textual y acceso a las fuentes/mediciones existentes.
- Los grafos muestran estados y tipos en texto además del color. Las aristas representan relaciones conservadas; el catálogo documental no se presenta como inferencia de un residente.
- Modal y drawer reutilizan foco, Escape, cierre, scroll y adaptación de los componentes compartidos. Los cuidados permanecen separados de evidencia y diagnóstico.
- Modo claro/oscuro: mismas variables compartidas; RMCharts vuelve a dibujar mediante su registro de renderers y destruye la instancia al desmontar.

## F. Verificación ejecutada

### Pruebas y build

| Comando | Resultado observado |
|---|---|
| `php artisan test --filter='ExploradorOrionTest\|InformeCognitivoTest\|EstabilizacionOrionContinuidadTest' --compact` | **52 correctas, 295 aserciones**, SQLite aislado. |
| `scripts/probar-postgres.ps1 -Filtro 'ExploradorOrionTest\|InformeCognitivoTest'` | **37 correctas, 242 aserciones**, cluster PostgreSQL desechable. Ejecución anterior al séptimo caso y al último enriquecimiento de relaciones. |
| `scripts/probar-postgres.ps1 -Filtro 'ExploradorOrionTest'` | Revisión final del explorador: **7 correctas, 51 aserciones**, PostgreSQL aislado; cluster detenido y retirado por el script. |
| `npm test` | **220 correctas, 2 omitidas, 0 fallos**, 222 totales. Las dos omisiones ya pertenecen a la suite. |
| `node --test tests/Frontend/orion-explorador.test.js` | **2 correctas**, incluidos orientación estrecha, revelado de nodo y desconexión del observador. |
| `npm run build` | **Correcto** tras el ajuste final de interacción/CSS. |
| `php -l` sobre los cinco PHP de aplicación modificados/creados | Sin errores de sintaxis. |
| Pint sobre los PHP nuevos y nueva prueba feature | Correcto. |
| `git diff --check` del alcance O.R.I.O.N. | Sin incidencias. El chequeo global detecta espacios finales preexistentes en Agenda, fuera de esta tarea. |

Se corrigieron fallos reales durante el trabajo: títulos antiguos en expectativas, recorrido semántico que omitía relaciones entrantes, forma del argumento de colores de RMCharts, campos de presentación de simulación, templates SVG incompatibles con el ámbito Alpine y escala inicial ilegible del grafo móvil. La última exportación HTML real se volvió a renderizar mediante el caso semántico después de los ajustes de composición.

No se ejecutó toda la suite PHP del sistema. Las pruebas técnicas no equivalen a validación clínica.

### Navegador real y límites de la evidencia

Se comprobó la ruta real de Enfermería mediante la sesión disponible: carga, avisos de simulación, modal didáctico, foco después de la transición, Escape y retorno de foco, drawer de cuidados, su contenido autorizado y enlace a agenda. No se registraron datos clínicos. En esta sesión no había un histórico D-137 clínicamente publicado que permitiera verificar una traza clínica real como Medicina.

Para verificar el grafo se exportó el GET Laravel completo producido por una prueba con datos sintéticos, sin sembrar la base operativa. Se sirvió localmente mediante un servidor temporal **solo GET**, con los mismos assets compilados y Alpine/Livewire; las acciones Livewire con POST no se consideraron verificadas por esa exportación. Se probaron en tests y, para modal/cuidados, en la aplicación real. El servidor temporal se cerró al terminar.

| CSS viewport medido en navegador | Resultado de reflow del HTML renderizado |
|---|---|
| 1440 px | Sin scroll horizontal de página ni literales inválidos. |
| 1280 px | Sin scroll horizontal de página ni literales inválidos. |
| 1024 px | Sin scroll horizontal de página ni literales inválidos. |
| 768 px | Sin scroll horizontal de página ni literales inválidos. |
| 390 px | Una columna, grafo vertical legible, selección accesible y sin desbordamiento de página. |

También se comprobaron tema claro/oscuro, selección SVG con Enter, revelado del resultado fuera del encuadre, descripción textual y gráfica de cobertura visible. El tamaño CSS efectivo se midió porque el navegador aplicaba zoom propio; el override temporal se restableció al terminar. No se certifica una matriz completa de ambos roles, todos los estados y ambos temas en cada viewport. Movimiento reducido está implementado por CSS compartido, pero no se emuló su preferencia en el navegador. El zoom de página al 200% y los lectores de pantalla requieren comprobación adicional.

Capturas con datos exclusivamente sintéticos:

- `C:/Users/CARLAENCINAS/.codex/visualizations/orion-2026-10-20/resultado-escritorio-claro.png`
- `C:/Users/CARLAENCINAS/.codex/visualizations/orion-2026-10-20/grafo-movil-claro.png`
- `C:/Users/CARLAENCINAS/.codex/visualizations/orion-2026-10-20/grafo-movil-oscuro.png`

**Dictamen de QA:** verificación técnica y visual acotada satisfactoria; **FAIL para aceptación integral** por evidencia completa aún no disponible en la matriz indicada y por los límites de publicación/permisos descritos a continuación. No se atribuye aprobación clínica.

## G. Capacidades disponibles y límites reservados

### UX GAP — trazabilidad técnica para Medicina

- Necesidad: permitir a un médico autorizado explorar la traza técnica completa.
- Contrato actual: `EvaluacionExpertaPolicy::consultarTrazabilidad` exige acceso técnico de Superadministración. Consultar resultados no concede ese permiso.
- Implementado: explorador funcional dentro de la frontera actual; explicación y evidencias según permisos del rol. No se muestran fuentes instrumentales restringidas.
- Alternativas: conservar acceso técnico actual o aprobar una Policy contextual de lectura clínica de trazabilidad.
- Impacto: ampliación sensible de acceso a evidencia e instrumentación.
- Cambio estructural BDD: **NO**. Cambio de autorización institucional: **SÍ**, requiere decisión explícita.
- Recomendación: aprobar por separado qué roles, fuentes y detalle histórico pueden consultar. No convertir el permiso de lectura de resultados en acceso técnico general.

### BACKEND GAP — conservación histórica institucional completa

- Necesidad: reproducir una evaluación institucional/Petersen completa con originales, contexto y software exactos.
- Contrato actual: el perfil institucional al corte declara `persistido=false`; D-137 conserva su propia traza; la simulación tiene otro contrato. Véase `docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md`.
- Datos ausentes en determinadas evaluaciones: valor original, fecha/profesional originales, transformación exacta y/o contexto y conservación compuesta del release.
- Implementado: se leen referencias y semántica realmente conservadas; cada original ausente dice «No conservado en esta evaluación». El dato fuente actual se consulta por separado.
- Alternativas: mantener consulta actual explícita, o aprobar la extensión de conservación compuesta documentada. Un sello futuro no reconstruye originales perdidos.
- Impacto: sin esa conservación no puede certificarse reproducción histórica integral del contrato institucional nuevo.
- Cambio estructural requerido para esa conservación completa: **SÍ, pendiente de autorización específica**. El grafo de lectura entregado no necesita cambios estructurales.

### Datos/decisiones clínicas

No se activó publicación D-137 indiscriminada, no se construyó un resolvedor multidominio nuevo y no se inventaron diagnósticos, umbrales ni escalas. El radar y las tendencias entre instrumentos distintos permanecen ausentes por falta de un contrato legítimo de comparabilidad. La simulación se muestra como demostración sin uso asistencial; las fixtures `VER_TEST` permanecen en pruebas aisladas.

## H. Estado de Git y alcance local

Solo se consultaron `git status` y `git diff`. No se ejecutaron operaciones Git de mutación, publicación o despliegue en esta tarea.

Los cambios de esta entrega afectan ocho rutas previamente seguidas y once rutas sin seguimiento, incluida esta documentación y una prueba de continuidad que ya existía sin seguimiento. Los diffs contra HEAD incluyen trabajo previo en algunos archivos; no deben atribuirse íntegramente a esta entrega. Permanecen modificaciones ajenas en Cuidados, Institución, Valoraciones, motor, pruebas y scripts. No se revirtieron ni incorporaron a un commit.

El chequeo global de whitespace señala incidencias en la vista preexistente de Agenda. No se modificó esa vista para limpiar trabajo ajeno. No se añadió una dependencia, capa V1, tabla de auditoría o migración.

**Implementación realizada en el árbol de trabajo local. No se ejecutó git add, git commit ni git push. No se desplegaron cambios.**

## Ajuste posterior aprobado: aplicación de las cuatro referencias visuales

La propietaria aportó cuatro imágenes de estructura y aprobó su aplicación conservando la paleta existente. Se tomó la composición de Enfermería de la imagen 4, perfil y selección de la 3, lectura/modal de la 2 y grafo con detalle lateral de la 1. Sus datos, diagnósticos, puntuaciones, porcentajes y reglas ilustrados no se incorporaron al conocimiento.

### Cambios

- Resultado compacto con título de 26 px del token compartido y límite informativo integrado.
- Cinco dominios en fila con suficiente ancho; tres/dos/una columnas según ancho real del contenedor, incluso cuando se abre la consulta.
- Detalle del dominio seleccionado separado del contexto funcional; subcomponentes inicialmente contraídos para evitar repetir el informe completo.
- Consulta acoplada desde 1280 px: reserva ancho del informe y permanece sin backdrop ni trampa de foco modal en escritorio. En móvil vuelve a modal de ancho completo, con trampa de foco y scroll propio. Escape y retorno de foco se conservan. El listener de media query se elimina al desmontar.
- Modal de lectura de 620 px, superficie opaca, título visible y bloques diferenciados de resumen, respaldo y límites.
- Cuidados agrupados por los estados ya entregados por el backend; no se crearon ocurrencias ni se cambiaron reglas de ejecución.
- Grafo y detalle en proporción aproximada 2:1, con detalle desplazable. La selección de una regla muestra condiciones, evidencias y consecuencias conservadas. Se añadió centrar grafo; no se altera la inferencia.

Se extendieron dos componentes compartidos mediante opciones optativas; los consumidores existentes conservan sus valores predeterminados. Archivos adicionales a los de la sección C:

- `C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/views/components/ui/drawer-livewire.blade.php`: opciones `docked` y `surface`, foco y adaptación del panel.
- `C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/views/components/ui/modal-livewire.blade.php`: opción `surface`.
- `C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/frontend/styles/design-system/components/drawers.css`: variantes acoplada y lectura.
- `C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/frontend/styles/design-system/components/modals.css`: superficie de lectura y jerarquía del título.

### Verificación del ajuste

- `ExploradorOrionTest|EstabilizacionOrionContinuidadTest`: **21 correctas, 100 aserciones**.
- `InformeCognitivoTest`: **31 correctas, 195 aserciones**.
- Suite frontend después de regenerar assets: **220 correctas, 2 omitidas, 0 fallos**. La primera ejecución había leído un bundle anterior y falló en el color exacto del campo; la reconstrucción y repetición resolvieron el desfase sin cambiar la expectativa ni la paleta.
- Build correcto. No se repitió PostgreSQL por tratarse de presentación sin cambios de persistencia; la evidencia anterior se mantiene separada.
- Ruta real de Enfermería: 1440/1280/1024/768/390 CSS px, sin desbordamiento ni literales inválidos. Perfil de cinco columnas en 1440 y 1280; tres en 1024 y 768; una en 390.
- Escritorio: separación medida de **20 px** entre informe y panel, sin solapamiento; `aria-modal=false`. Móvil de 390 px: panel de **390 px**, `aria-modal=true` y foco dentro del panel.
- Cambio a Atención actualiza el detalle seleccionado. Modal opaco, título de 17 px, foco interior y retorno al disparador después de Escape verificados.
- Grafo: comprobado con HTML Laravel de prueba aislada, detalle de condiciones y consecuencias visible, centrar/selección por teclado, tema oscuro y reflow móvil sin desbordamiento; consola sin errores. Las interacciones POST de esa exportación no se usaron como prueba de integración.
- No se certifica lector de pantalla, zoom de página al 200% o toda la matriz de estados clínicos. Continúan las fronteras de autorización y conservación de la sección G.

Evidencia exclusivamente sintética:

- `C:/Users/CARLAENCINAS/.codex/visualizations/orion-2026-10-20/referencia-composicion-aplicada.png`
- `C:/Users/CARLAENCINAS/.codex/visualizations/orion-2026-10-20/referencia-grafo-detalle.png`
- `C:/Users/CARLAENCINAS/.codex/visualizations/orion-2026-10-20/referencia-grafo-movil-oscuro.png`

Los cambios continúan locales, sin stage, commit, push ni despliegue.
