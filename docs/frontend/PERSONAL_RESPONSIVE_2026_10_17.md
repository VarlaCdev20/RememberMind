# Personal institucional — adaptación de la vista de Gerencia

Fecha: 2026-10-17. Ruta: `/admin/personal-institucional`.

## Alcance y fuentes

Se aplicaron `remembermind-ui-review`, `remembermind-responsive-accessibility`, el contrato visual compartido, las reglas de `app/` y `resources/`, y los componentes existentes collection-header, metric-card, person-entity-card, filter-bar, status-badge y collection-view-switch. Se conservan colores, tipografía, estados y fuentes de datos; no se modifica el flujo institucional, BDD, permisos, roles ni datos operativos.

Flujo conservado: Resumen → filtrar personal → tarjetas/lista → directorio de salud o administrativo → consulta de disponibilidad/reportes → ficha de horarios. El ajuste abarca la presentación de las cinco secciones y la ficha accesible desde Gerencia. No acredita el alta de cuentas ni todo el módulo de personal/documentación laboral.

## Antes y correcciones

La inspección inicial en el navegador real a 390 CSS px mostró pestañas de 658 px dentro de 358 px disponibles, con opciones fuera de la pantalla. Las tablas fijaban mínimos de 900/920/760 px. Había filtros sin label, nombres truncados y controles pequeños. El navegador integrado se deshabilitó durante el trabajo y no pudo utilizarse para la revisión final.

- Se mantienen las cinco secciones en un nav con botones que se distribuyen en filas, estado `aria-pressed` y targets de al menos 44 px. No se esconden opciones detrás de un scroll lateral.
- Los indicadores, filtros y paneles se ajustan al ancho del contenedor, incluyendo el espacio que consume el menú lateral. Móvil muestra dos indicadores por fila; con menos de 320 px disponibles se apilan.
- Filtros con label/id, botones de icono con nombre accesible y foco visible. Las identidades, correos, áreas y roles largos pueden ocupar varias líneas.
- Las tablas de Resumen, directorios, interpretación de reportes y asignaciones conservan sus celdas y acciones. Bajo 960 px disponibles, las filas pasan a fichas con nombre de campo; bajo 480 px, cada ficha usa una columna. Se eliminan las alturas fijas de fila que superponían datos al apilarse y se reserva espacio real para las acciones en escritorio.
- La ficha de personal limita su altura a la ventana; su navegación se compacta en móvil y su contenido puede desplazarse verticalmente. Conserva foco dentro del diálogo mediante el mismo `x-trap.noscroll` usado por los overlays compartidos. No se añadió un cierre por Escape que pudiera descartar captura sin la lógica de confirmación correspondiente.
- El formulario de horarios adapta campos y acciones al espacio interior del diálogo; días laborales y acciones siguen accesibles con teclado. Se corrige la errata «Das» en su presentación.
- Se corrigió un error de sintaxis Alpine encontrado al renderizar la ficha: el manejador de apertura del PDF contenía un `try` usado directamente como expresión. Ahora se ejecuta dentro de una función; no se cambia la generación ni autorización del documento.

Las reglas están acotadas en `patterns/staff-workspace.css`, importadas por el Design System. No hay otra paleta, librería, dependencia ni geometría global de tablas impuesta a otros módulos.

## Evidencia y pruebas

Pruebas PHP con SQLite en memoria:

```sh
php artisan test --filter='PersonalInstitucionalResponsiveTest|GerenciaDashboardTest|PersonalInstitucionalHorariosTest|ContratoPersonalInstitucionalTest'
```

El grupo pasó **19 pruebas / 128 aserciones**. Se amplió después la misma prueba de presentación con una asignación sintética no persistida: ejecución focalizada **1 PASS / 24 aserciones**, dos aserciones adicionales. Se conserva prueba de ausencia de escrituras y de que Gerencia no obtiene `usuarios.crear`.

La prueba PHP exporta HTML opcionalmente solo cuando se define `RM_STAFF_QA_DIR`, siempre con datos sintéticos. La prueba frontend sirve esas fixtures en un servidor aislado y sin acceso al servidor de la aplicación, su sesión o BDD. Utiliza CSS compilado, fuentes locales, Alpine de Livewire y Chart.js/presets reales. Bloquea peticiones a destinos externos; las capturas omiten fotografía y fuentes de iconos del CDN.

```powershell
$env:RM_STAFF_QA_DIR = Join-Path $env:TEMP 'remembermind-staff-qa'
php artisan test --filter=PersonalInstitucionalResponsiveTest
node --test tests/Frontend/staff-workspace.test.js tests/Frontend/shell-visual-consistency.test.js tests/Frontend/management-topbar.test.js
```

Cobertura: 1440, 1280, 1024, 768 y 390 px; claro y oscuro; Resumen, Lista, Salud, Administrativo, Disponibilidad, Reportes, vacío, ficha de horarios, formulario de horario y tabla de asignaciones poblada. Se revisan scroll horizontal, campos identificados, controles de 44 px, filas sin superposición, nombres extensos, filtros desplegables, foco visible/confinado, gráficas montadas y ausencia de literales técnicos inválidos. Movimiento reducido emulado. Se añade pantalla móvil baja de 390 × 600 con acceso por teclado a Guardar, sin ejecutarlo.

La matriz inicial de nueve estados pasó **90 combinaciones**; el grupo con las pruebas compartidas pasó **7 pruebas frontend / 0 omitidas**. Tras añadir la tabla de asignaciones poblada, la ejecución final de `staff-workspace.test.js` pasó **100 combinaciones / 0 fallos / 0 omitidas**, además de la comprobación de pantalla baja. Capturas sintéticas guardadas fuera de Git; inspección visual de resumen en escritorio/móvil, lista, gráficas, ficha, formulario y asignaciones. Build Vite: **PASS**, sin nuevas dependencias; permanece el aviso informativo de tiempos del plugin CSS.

## Límites y pendientes

- **Implementado y verificado en fixtures; pendiente de verificación final en la sesión real.** La herramienta de navegador respondió «Local browser use is disabled in Work settings». No se cambió ese ajuste ni se accedió por otra herramienta a la sesión de la propietaria.
- Dictamen estricto de QA: **FAIL por evidencia incompleta de la sesión real**, no por un defecto visual observado pendiente. Los renders sintéticos complementan la inspección inicial; no equivalen a E2E sobre el servidor ni acreditan PostgreSQL.
- Los botones de exportación y filtros de periodo/área de Reportes conservan deuda previa: carecen de conexión funcional en esa vista. «Mapa de disponibilidad» y «Carga operativa» conservan contenido incompleto previo. Su implementación requiere una tarea funcional propia; esta entrega no promete que esas operaciones funcionen.
- No se certifican todos los pasos del formulario de alta, todos los estados de validación/guardado ni lectores de pantalla en dispositivo real. No se amplían permisos para probarlos con Gerencia.
- Sin nuevas dependencias V1 ejecutables. Sin commit ni push: se conserva el árbol amplio previo y falta la revisión final de la sesión real.

## Corrección de la cabecera tras captura de la propietaria

La captura posterior mostró una regresión que los cinco anchos iniciales no detectaron: entre móvil y tablet, el header compartido cambiaba a `flex-direction: column`, pero las bases de 360/280 px del patrón de personal seguían activas y pasaban a reservar altura. La prueba añadida reprodujo **688 px de altura a 672 px de viewport**, antes de corregir estilos.

El patrón de personal ahora controla explícitamente el eje flex; bajo 800 px de contenido apila identidad y acciones con `flex: 0 1 auto`. Se conservan superficies, colores, tipografía y acciones compartidas. No cambia datos, permisos ni lógica del módulo.

Regresión comprobada en 640, 672, 700, 735, 760, 767, 768 y 832 px, ambos temas: **16 casos adicionales PASS**, con cabecera de máximo 300 px como límite de regresión. Se inspeccionó también la captura sintética de 735 px: título y acciones juntos sin la reserva vertical vacía. La matriz de **100 combinaciones** y las pruebas compartidas se repitieron: **7 pruebas frontend PASS / 0 omitidas**. `npm run build`: **PASS**.

Se volvió a intentar leer la sesión mediante @Navegador; continúa deshabilitado en los ajustes de Work. Estas evidencias son de renders sintéticos aislados, no una aceptación visual final de la sesión real. Se mantiene ese pendiente.

## Simplificación visual solicitada después

La propietaria indicó que Personal estaba saturado. Se revisó la jerarquía con `remembermind-ui-review` y `remembermind-ux-flow-architect`; se conservan la dirección cálida, los componentes compartidos y las capacidades existentes. La captura anterior documenta un problema, no una nueva composición de referencia aprobada. No corresponde definir otra paleta, captura clínica, reglas de visualización ni movimiento nuevo.

### UX FLOW

- Objetivo: localizar personal y continuar a su ficha/horario con menos bloques compitiendo por atención.
- Usuario y entrada: Gerencia en `/admin/personal-institucional`, con sus permisos actuales.
- Recorrido: orientación y acción de horario → elegir una de las cinco secciones → consultar los cuatro indicadores → buscar en el directorio → abrir filtros secundarios solo cuando hagan falta → consultar persona y continuar al horario autorizado.
- Excepciones: se conservan vacío, búsqueda sin coincidencias, limpieza de filtros y validaciones/cierre del diálogo. Un filtro aplicado sigue representado en los chips; al entrar con filtros secundarios aplicados, el desplegable inicia abierto.
- Salida: listado correspondiente a los filtros actuales; la consulta no persiste cambios. Las asignaciones siguen su flujo existente de confirmación.

| Información | Fuente y prioridad | Presentación conservada en móvil |
|---|---|---|
| Identidad de la vista y horario | Header y acción Livewire existentes, principal | Cabecera breve y acciones visibles |
| Total, activos, en turno, fuera de turno | `estadisticas` existente, contexto | Franja única en dos columnas, sin cuatro sombras/iconos decorativos |
| Personas y cantidad mostrada | `personalResumen` y totales existentes, principal | Un panel de directorio con selector Tarjetas/Lista |
| Búsqueda | `busquedaResumen`, frecuente | Siempre visible junto al acceso Filtros |
| Tipo, estado y turno | Propiedades de filtro existentes, secundario | Desplegable con teclado, labels y estado accesible |

Implementación: se elimina el bloque independiente «Personal mostrado», se integran título/contador/selector en el panel del listado y se quita la segunda fila de badges redundantes. Se conserva la fila de chips descriptivos y su limpieza. Las pestañas usan títulos breves; los cuatro indicadores reutilizan `x-ui.metric-card` en una franja sin decoración repetida, con sus descripciones aún accesibles a lectores de pantalla. Las tarjetas de personas usan un `dl` para área, rol y turno, sin tres cajas interiores ni esquina decorativa; los nombres dejan de forzarse a mayúsculas. La cabecera omite la etiqueta redundante «Gestión del sistema» y reduce su subtítulo. Se retira el botón «Exportar PDF» de esa cabecera porque carecía de manejador; no se retira una exportación operativa ni se implementa una simulación. La deuda de Reportes se conserva documentada.

Pruebas PHP del grupo relacionado: **19 PASS / 136 aserciones**; incluyen conservación de filtros aplicados y limpieza, permisos y ausencia de escritura al consultar. Tras ajustar la inicialización Alpine y simplificar las fichas, ejecución focalizada **1 PASS / 32 aserciones**. Build Vite: **PASS**. La prueba frontend amplía el recorrido para comprobar el desplegable con Espacio, su `aria-expanded`, los tres filtros completos sin desbordamiento, los cuatro indicadores y el inicio del directorio en la primera pantalla sintética. Se comprueba además la entrada con filtros ya aplicados; sus valores se verifican con Livewire/PHP y sus chips en el render aislado, que no hidrata `wire:model`.

Matriz de 100 combinaciones y 16 anchos/temas intermedios, más filtros aplicados y pantalla baja: **PASS**. El grupo con shell/topbar pasa **7 pruebas frontend / 0 omitidas**. Se inspeccionan las capturas sintéticas de Resumen a 390 y 1440 px: cabecera compacta, franja de indicadores, panel de directorio y datos alineados. No se cambian consultas, permisos, BDD ni persistencia. Sin dependencias ni legacy nuevo. No se crea commit mientras falta aceptación esencial en la sesión real; no se hace push.

Dictamen de QA global: **FAIL por evidencia real incompleta**. @Navegador sigue deshabilitado; no se puede verificar en la sesión real ni acreditar el resultado con sus iconos cargados desde CDN. La aceptación del diseño por la propietaria continúa pendiente. Los resultados sintéticos anteriores son verificaciones complementarias, no E2E de la aplicación.

## Segunda revisión: directorio como tarea principal

La nueva captura de la propietaria sigue mostrando el encabezado antiguo, PDF, «Personal mostrado» y cuatro tarjetas grandes, elementos retirados del código en la revisión anterior. No se deduce de esta captura que la versión actual esté cargada. Se comprobó el proceso `artisan serve` del proyecto y se limpió únicamente la caché de vistas compiladas con `php artisan view:clear`; no se alteró sesión, configuración ni datos. No se pudo recargar su pestaña: @Navegador sigue deshabilitado.

Análisis: la captura dedica prácticamente la primera pantalla a orientación, indicadores y filtros; el personal queda debajo. La tarjeta del selector duplica el título/contador del directorio y cada filtro añade otro bloque de superficie. Además, el backend limitaba el listado a 8 tarjetas/12 filas sin posibilidad de recorrer los registros restantes. Esto era una limitación funcional, aunque el buscador permitiera localizar un registro conocido.

El flujo vigente sustituye la franja de cuatro celdas de la revisión anterior: **Personal → buscar → recorrer la lista paginada → abrir Horarios de una persona**. La búsqueda permanece visible; tipo/estado/turno se despliegan a demanda y conservan chips/limpieza. Las cinco secciones permanecen accesibles. Tarjetas es ahora una vista alternativa, no la entrada predeterminada. Vacío, filtrado y retorno del diálogo conservan sus mecanismos actuales.

Implementado:

- Cabecera de texto sin panel decorativo ni icono grande. Contexto de total/activos/en turno/fuera de turno como `dl` con flex y salto de línea, sin cuatro tarjetas ni grid.
- Lista vertical con separadores dentro de una sola superficie: nombre, correo, rol, área, turno, estado y acciones permitidas. Reutiliza `person-entity-card`, `status-badge`, botones y paleta compartida; no se crean otras reglas de permisos.
- `WithPagination` y paginación compartida `x-ui.paginacion`, 12 personas por página en ambas vistas y orden estable. Cambiar búsqueda/tipo/estado/turno o limpiar filtros vuelve a la primera página; cambiar Lista/Tarjetas conserva la página y los filtros.
- «Ver disponibilidad» describe el destino real del botón superior, cuyo método abre esa sección. La asignación continúa desde Horarios de una persona. No se inventa una asignación desde un botón que solo cambiaba pestaña.
- Precarga de relaciones de turno y área para el directorio. El área conserva la selección existente de primera asignación, usando la relación cargada en lugar del accessor que abría otra consulta por persona. No se definen nuevas reglas de asignación.

Pruebas: **20 PHP PASS / 152 aserciones** en el grupo relacionado; tras el ajuste final de precarga/área, **2 focalizadas PASS / 46 aserciones**. La prueba nueva crea 16 cuentas sintéticas institucionales, alcanza la segunda página, verifica el registro final y el reinicio al filtrar; conserva pruebas de ausencia de asignaciones persistidas y de que Gerencia no obtiene `usuarios.crear`.

Frontend: **120 combinaciones PASS** (12 estados × 5 anchos × 2 temas), **16 comprobaciones intermedias**, filtros activos y pantalla baja. Incluye lista predeterminada, Tarjetas y página con 12 personas; comprueba cuatro valores de contexto sin grid, controles/foco/desplegables, etiquetas, reflow y ausencia de sombras internas. Grupo compartido: **7 PASS / 0 omitidas**. Build: **PASS**. Capturas sintéticas de 390/735/1440 px revisadas; no sustituyen la sesión real y omiten iconos CDN.

Seguridad/integración: misma consulta institucional y exclusiones de roles, mismas acciones/autorizaciones, mismas rutas y modelo congelado. Sin migraciones, nuevas dependencias ni nueva compatibilidad V1. Las exportaciones/filtros de Reportes y paneles incompletos de Disponibilidad siguen siendo deuda funcional documentada; no se presentan como operaciones verificadas. Sin commit ni push mientras falta la aceptación esencial en la sesión real. Dictamen global de QA: **FAIL por evidencia real incompleta**; verificación sintética del alcance cambiado: PASS.

## Cierre Git posterior

La propietaria solicitó commit y push y confirmó incluir todos los cambios pendientes del proyecto. Este corte se publica como avance técnico, conservando los pendientes de aceptación visual real. Resultados del conjunto y correcciones de cierre: [informe de publicación](../sistema/PUBLICACION_AVANCES_2026_10_17.md).
