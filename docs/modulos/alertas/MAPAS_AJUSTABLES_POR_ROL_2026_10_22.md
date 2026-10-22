# Vistas de relaciones ajustables por rol

## Alcance y flujo

Petición del 22/10/2026: ajustar la visualización, sustituir el término técnico «grafo» en la interfaz y dar a cada área composición, iconos y microinteracciones coherentes con su trabajo.

Flujo: seleccionar una alerta autorizada → explorar el registro de origen o el contexto profesional → seleccionar un elemento → consultar sus campos y vínculos → continuar al detalle autorizado. Se mantienen los filtros de especialidad y sus estados vacíos. No se añaden nodos para llenar una pantalla.

La referencia previa orienta identidad y lectura de las relaciones; esta instrucción posterior solicita composiciones diferentes y ajustables. Se conserva el Design System: no hay paletas nuevas por rol, librerías nuevas, cambios de permisos, reglas clínicas o persistencia.

## Composiciones implementadas

| Rol | Nombre visible | Organización y utilidad | Icono |
|---|---|---|---|
| Enfermería | Ruta del cuidado | Recorrido entre caso, hecho de origen y actuaciones documentadas. | Pulso |
| Medicina/Geriatría | Revisión clínica | Caso central con fuente y contexto de revisión a los lados. | Estetoscopio |
| Psicología | Red de bienestar | Vínculos alrededor del caso; contexto y observaciones consultables por separado. | Cerebro |
| Nutrición | Alimentación e hidratación | Contexto central; bloques diferenciados de alimentación, apoyo hídrico y continuidad, si constan. | Alimentación; gota para el bloque hídrico |
| Fisioterapia | Movilidad y autonomía | Capacidades y continuidad distribuidas por niveles junto al origen y el caso. | Persona caminando; apoyo y objetivo para sus campos |
| Pedagogía | Recorrido de participación | Observaciones de actividad distribuidas alrededor de su contexto. La geometría no crea un ciclo clínico nuevo. | Libro y evolución de la actividad |
| Administración | Mesa de coordinación | Columnas para responsabilidad, fuente y seguimiento. | Portapapeles |
| Gerencia | Equipo y responsabilidades | Responsable destacado y caso como punto de conexión. | Equipo |
| Superadministración | Origen e historial | Procedencia por etapas, separada de caso, contexto y eventos. | Huella |

Cada elemento tiene icono según su función: residente, responsable, medición, administración, plan, intervención, área o evento. Se reutiliza Phosphor, ya cargado por los layouts. La dependencia previa del CDN de iconos no se reemplaza en esta tarea; sus iconos necesitan estar disponibles en navegador. El nombre y la información siguen siendo textuales.

Las formas distinguen pasos, fichas, relaciones y capacidades sin asignar gravedad clínica. Los colores conservan la semántica institucional. El DTO incorpora únicamente `foco`, `origenModulo` e índice de sección para presentación; no altera campos clínicos ni atribuye una causa a la última valoración del residente.

## Ajustes locales

- Altura seleccionable de 280 a 680 px, limitada al espacio de pantalla disponible.
- Acercamiento de 60 % a 400 %, ajuste de todos los elementos visibles y enfoque de la selección.
- Desplazamiento del lienzo con ratón o un dedo; las acciones equivalentes de escala/enfoque están disponibles como botones.
- Organización voluntaria: arrastrar elementos o usar Alt + flechas con el foco sobre ellos. Las flechas sin Alt conservan la navegación por elementos visibles.
- Restablecer recupera la composición propia del área. Cambiar de caso recrea el estado local; no se guardan coordenadas, no hay almacenamiento de datos clínicos en el navegador ni escrituras por reorganización.
- Pantalla completa utiliza la API nativa. Un rechazo muestra cómo continuar con altura/escala. Al abrir el detalle se sale primero de pantalla completa, evitando ocultar el modal fuera de ella.
- Fichas de consulta con texto completo y acceso al inspector como alternativa a la representación visual.

El ajuste utiliza los límites de los elementos visibles y la proporción real del contenedor. `ResizeObserver` se desconecta al retirar la vista. Las posiciones se copian: nunca se modifica el DTO original ni la lista de relaciones.

## Mapa de microinteracciones

| Disparador | Comunicación | Propiedad y token | Foco / movimiento reducido |
|---|---|---|---|
| Selección o foco de un elemento | Qué se está consultando y qué relaciones lo conectan | Borde/relleno y trazo; `--rm-duration-fast` / `--rm-duration-hover` | Foco visible, sin desplazamiento decorativo. |
| Nueva información en el inspector | Cambio del elemento consultado | Opacidad y desplazamiento de 4 px; `--rm-duration-enter` | El inspector conserva su contenedor y foco. Sin animación con movimiento reducido. |
| Continuidad, movilidad o coordinación | Recorrido de lectura horizontal | Entrada horizontal del inspector | Sin esperar al fin de la animación. |
| Nutrición o participación | Desarrollo de los bloques del registro | Entrada vertical del inspector | Sin esperar al fin de la animación. |
| Bienestar psicológico | Lectura de contexto y vínculos | Transición de opacidad con `--rm-duration-slow` | Sin pulsaciones ni efectos repetidos. |
| Arrastre | Posición local o desplazamiento solicitado | Actualización inmediata de coordenadas | Sin interpolación que retrase el puntero; Alt + flechas como alternativa. |
| Cambio rápido de selección | Última información seleccionada | Reemplazo del contenido; transición anterior descartada | No determina estado clínico ni bloquea interacción. |

No hay animación infinita, destellos, rotación decorativa de iconos ni cálculo de prioridad en frontend. La información clínica permanece disponible mientras se consulta.

## Adaptación y verificación

En 1440/1280/1024 px se conserva el espacio por rol ya implementado y se ajusta el lienzo a su columna. En 768/390 px se conserva una columna, controles con reflow, altura acotada, desplazamiento táctil y fichas alternativas. Es el comportamiento implementado, pendiente de inspección visual real en esos cinco anchos y ambos temas.

| Verificación del corte | Resultado |
|---|---|
| SQLite: flujos y vínculos de especialidad | 11 pruebas / 282 aserciones, PASS; `mapas-roles-sqlite.xml`. |
| SQLite: cierre de las vistas por rol, títulos, secciones, bloqueos y composición | 4 pruebas / 246 aserciones, PASS; `mapas-cierre-sqlite.xml`. Este corte se solapa con el anterior. |
| JavaScript: composición de nueve roles, conservación de nodos/relaciones, consulta, arrastre, Alt + flechas, enfoque, resize/cleanup y recuperación de pantalla completa | 15 pruebas, PASS; `js-mapas-final.log`. Incluye las pruebas cercanas de interacción y mediciones. |
| Sintaxis de expresiones Alpine y contrato de movimiento reducido | Incluidos en las pruebas JavaScript; no sustituyen interacción ni medición visual. |
| Build final | PASS; 111 módulos / 9,40 s; `build-mapas-final.log`. |
| Pint, sintaxis PHP y diff | PASS en los archivos afectados. |

Evidencia en `output/alertas-20261021/`. No se repite la integración PostgreSQL porque no se modifican consultas, persistencia ni reglas; sus pruebas del corte de vínculos anterior no se presentan como aceptación del nuevo aspecto visual.

Dictamen de QA visual: **FAIL por evidencia incompleta**, sin defecto visual afirmado por suposición. Pendientes los cinco anchos, ambos temas, iconos en SVG, contraste compuesto, foco/tacto y pantalla completa en navegador real. El control local sigue desactivado según el estado confirmado anteriormente; el propietario eligió continuar sin ese control. No se sustituye por otra automatización ni se afirma aceptación visual desde tests.

Sin cambios de BDD, permisos, fuentes clínicas o dependencias; sin staging, commit ni push. No se introduce dependencia V1 en la nueva distribución. Se conserva el trabajo local anterior.
