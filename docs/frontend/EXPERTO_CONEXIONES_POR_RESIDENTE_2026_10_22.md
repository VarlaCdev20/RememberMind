# Sistema experto: conexiones y recorridos por residente

22/10/2026. Trabajo en `feature/apoyo-decision-informe-neurocognitivo`, conservando
la rama y los archivos locales anteriores. Estado: implementado; aceptación visual
y E2E final pendientes. Sin cambios de esquema, permisos ni metodología clínica.
El propietario autorizó aplicar y publicar el trabajo en GitHub con el mensaje
exacto `UI UX SISTEMA EXPERTO`, aun con el navegador bloqueado. Esa autorización
permite entregar el commit sin presentar la revisión visual pendiente como aprobada.

## Fuentes y alcance

La instrucción del propietario pide una consulta médica por residente, conexiones
propias y grafos grandes distribuidos, visibles e interactivos. Se aplican AGENTS
raíz/app/resources/tests, el contrato visual compartido y las fuentes vigentes de
`docs/sistema-experto/README.md`, `INTERFAZ_MEDICA_RESULTADOS.md` y las decisiones
de conservación. La documentación histórica no autoriza otros contratos ni nuevas
inferencias. La ruta de consulta y sus Policies existentes siguen siendo el límite
de acceso. La indicación antigua de ubicación en una skill de arquitectura se
subordina al AGENTS actual: se usa `app/Modulos/ApoyoDecision/Presentacion`.

Skills: module-delivery, source-of-truth, ui-review, ux-flow-architect,
design-system, data-visualization-ux, motion-microinteractions,
responsive-accessibility, Laravel best practices y system-testing. QA y
release-check conservan pendientes explícitos; no hay aprobación clínica.

## Flujo e integración

1. Medicina entra por Sistema experto y selecciona un residente admitido.
2. La consulta conserva nombre, ubicación, fecha y alcance del expediente actual.
3. Puede elegir áreas, requisitos o aplicaciones existentes. Solo aparecen grupos
   suministrados por el backend; un grupo no disponible permanece deshabilitado.
4. El mapa enlaza soporte → valoración → elemento únicamente cuando la salida
   autorizada conserva esos vínculos. C02 usa solo `derivado_de` declarado por el
   motor. Las aplicaciones sin valoración se muestran como registros del
   expediente, sin atribuirles una conclusión cognitiva.
5. El inspector permite seguir cada relación. Camino explorado mantiene los pasos,
   resalta las aristas recorridas y permite regresar sin modificar las fuentes.
6. Mapa y Lista utilizan el mismo contenido. Los controles de zoom, ajuste y
   enfoque permiten examinar conexiones en redes grandes. La representación
   inicia en Lista en pantallas estrechas; conserva la opción de Mapa.

El recorrido es una interacción local sobre la captura autorizada de la consulta.
No es una transmisión en vivo de nuevas filas de BDD ni una animación que afirme
que el motor está ejecutándose. La acción existente de análisis vuelve a adquirir
fuentes; la navegación local no guarda ni ejecuta inferencia.

La red administrativa conserva paginación y filtros del backend. Se incorporan
cuadrícula, niveles y enfoque del nodo con su vecindario; se puede volver al
conjunto completo de la página. Los nodos fuera del enfoque siguen existiendo en
la fuente y en la lista accesible. El inspector conserva relaciones que atraviesan
la página; no se dibuja una arista si su extremo no está representado.

## Responsabilidades y seguridad

- `ConexionesResidenteVisible` proyecta los DTO ya autorizados y verifica que
  soportes, valoraciones y aplicaciones pertenezcan al residente solicitado.
- `ResultadosExpertoResidente` conserva la reautorización y adquisición existentes.
  Un error de adquisición no se representa como ausencia de datos.
- No hay consultas SQL en Blade, escritura nueva, cambio de permisos, usuarios
  creados como fallback ni dependencia con el catálogo artificial G09.
- El cliente recibe etiquetas y detalles seleccionados, sin volcar respuestas,
  documentos completos o `datos_originales` de los soportes.
- La consulta actual y la evaluación histórica mantienen cortes distintos. El
  mapa actual no se presenta como traza histórica conservada ni diagnóstico.
- Enfermería y Psicología conservan sus composiciones existentes.

## Diseño y adaptación

Se reutilizan los tokens, controles y visor de grafos instalados. La composición
agrupa identidad, selector, pasos, mapa/lista e inspector. Los estilos usan
contenedores para pasar de cinco columnas a tres, dos o una; el inspector baja
debajo del mapa al reducirse el espacio. Hay etiquetas accesibles, estados de
selección, navegación por teclado y alternativa textual. Las transiciones usan
los tokens existentes y respetan movimiento reducido. Estas decisiones no
equivalen a una validación visual de los cinco anchos.

## Verificación

Comprobado en esta reanudación:

- `node --test tests/Frontend/expert-resident-workspace.test.js tests/Frontend/orion-explorador.test.js`:
  10 pruebas correctas. Incluyen inicialización sin recursión, selección y retorno
  por vínculos existentes, dirección original, enfoque recuperable y distribución
  de 90 nodos sin superposición de posiciones.
- `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Unit/ConexionesResidenteVisibleTest.php --colors=never`:
  6 pruebas, 21 aserciones. Comprueban ausencia de vínculos inventados,
  deduplicación, dependencia C02 y rechazo de fuentes de otro residente.
- Build Vite actualizado: correcto; aviso existente de bundle mayor de 500 kB.
- Regresión de InformeCognitivo/NavegacionSistemaExperto/LaboratorioExpertoFrontend:
  61 pruebas, 574 aserciones correctas en SQLite. Evidencia local en
  `output/experto-recorridos-regresion.junit.xml`. Total PHP de esta reanudación:
  67 pruebas, 595 aserciones; frontend: 10 pruebas correctas.
- Pint en los archivos nuevos y el router sintético: correcto tras formato.
  El componente existente conserva incidencias de estilo también presentes en
  HEAD; no se reformateó globalmente un archivo ajeno al alcance del cambio.

Se amplió el E2E optativo existente para ejercitar el recorrido de un residente
de la cohorte sintética y los cinco anchos. No se ejecutó durante esta reanudación:
la herramienta de navegador responde `Local browser use is disabled in Work
settings`, incluso después de que el propietario indicó haberlo habilitado y
seleccionó el plugin. No se usa otro navegador para eludir ese bloqueo.

## Pendientes de aceptación

- Sesión médica autorizada en la instalación, recorrido de fuentes, enfoque,
  arrastre, teclado, retorno, tema y cinco anchos: sin verificar en esta versión.
- E2E con cohorte, restauración del foco y presentación SVG/Alpine: pendiente.
- PostgreSQL: no ejecutado en esta entrega; no hay cambio de persistencia.
- Entrega autorizada mediante commit `UI UX SISTEMA EXPERTO` en la rama actual;
  la revisión visual y E2E sigue pendiente y no se sustituye por el build.
- Hallazgos anteriores de profesiones de cuentas y catálogos instalados permanecen
  separados: no se modificaron responsabilidades o permisos para obtener acceso.

Dictamen visual: **FAIL por verificación incompleta**, no por un defecto visual
observado de esta versión. Los fallos detectados mediante pruebas se corrigieron
en implementación: pérdida de getters al componer el visor e inicialización
recursiva del mapa derivado; las regresiones locales cubren ambos.
<<<<<<< HEAD

## Integración local posterior del commit f9128b2

El commit publicado se incorporó al árbol local sin cambiar de rama ni descartar
modificaciones simultáneas. La prueba E2E optativa se ejecutó en una base SQLite
desechable, con recursos compilados propios y sesiones separadas por rol. Se
detectó y corrigió un fallo visible: Alpine no expandía los `template x-for`
ubicados dentro del SVG del residente. Ahora los nodos y aristas se emiten desde
Blade con datos ya autorizados, y Alpine conserva selección, enfoque, zoom y
recorrido sin modificar los registros originales.

La prueba E2E pasó para superadministración, Medicina y Enfermería, incluidos
mapa/lista, teclado, filtros y 1440, 1280, 1024, 768 y 390 px. La matriz produjo
20 verificaciones de ancho sin desbordamiento horizontal. También pasaron las
10 pruebas frontend del explorador, las 6 pruebas unitarias del proyector
(21 aserciones) y 71 pruebas de integración del informe, navegación,
laboratorio y explorador (656 aserciones): 77 pruebas PHP y 677 aserciones
en total. El build Vite terminó correctamente y Blade recompiló las vistas.
El router de ensayo concede a su médico sintético los permisos de
lectura necesarios exclusivamente en la base desechable; los permisos de la
aplicación instalada no cambiaron. La captura sintética presenta datos de
entrenamiento, no un resultado clínico.

El navegador integrado de Work sigue deshabilitado por la configuración local;
esta aceptación se basa en Chromium automatizado con base desechable. El shell
de esa prueba intenta usar SweetAlert externo, bloqueado por el router privado;
se documentaron seis avisos `Swal is not defined` ajenos al explorador. La
revisión manual en la sesión real de la propietaria y PostgreSQL siguen
pendientes. Este cierre de QA no sustituye la validación clínica ni aprueba
activación de conocimiento.
=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
