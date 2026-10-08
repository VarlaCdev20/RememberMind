# Calendarios y fichas en la misma ventana

Correcciones del 2026-10-07 en `REINICIO`, sin commit, push, seeders ni modificaciones a registros operativos. La instrucción del propietario reemplaza los atajos entre módulos y la fila de ayuda de las entregas anteriores.

## Distribución y navegación

La ayuda se consulta mediante el icono de pregunta de la cabecera: abre un modal corto con campos y siguiente paso. El resumen utiliza una cifra compacta y filtros por estado, sin repetir un cuadro de objetivo. Los filtros principales ofrecen búsqueda, bandeja/estado cuando aplica y selección de área en Asignaciones/Actividades. Las áreas se obtienen de los registros disponibles; el selector compartido busca únicamente con más de diez opciones.

Las fichas se solicitan con un GET al mismo módulo, mediante `X-RM-Ficha: 1`. El backend valida la entrada, cuenta activa, permiso y Policy del residente antes de devolver un fragmento privado con `Cache-Control: private, no-store`. No se inserta una página de login, error o respuesta sin ese encabezado. El HTML procede del Blade autorizado y mantiene escape de campos. No se precargan expedientes completos ni se envían rutas físicas de archivos.

Abrir, cerrar y pulsar Escape conservan la URL, mes, filtros, página y desplazamiento. El cierre desactiva primero la trampa de foco y después desmonta el contenido, evitando dejar el fondo inerte. El foco vuelve al registro. Una petición cancelada no puede incorporar una ficha tardía. La ayuda y los indicadores de Reportes también se abren localmente. Los botones de contexto hacia otros módulos y enlaces de residente dentro de la ficha se retiraron; la navegación deliberada del menú lateral sigue disponible.

## Calendarios reales

Jornadas, Actividades y Visitas incorporan Calendario como vista inicial, además de sus vistas previas. El mes usa siete columnas y lunes primero. Mes anterior/siguiente conservan el módulo y los filtros; el día seleccionado destaca y muestra una vista previa. El calendario no depende de `page` ni de 10/20/50 registros: una consulta agregada cuenta todo el mes autorizado y una función de ventana SQL limita la vista previa a tres registros por día. Ver todos los registros del día aplica ese día en la misma pantalla y habilita su paginación.

| Ventana | Fecha y lectura |
| --- | --- |
| Jornadas | Día de la jornada, horario de turno y personas distintas con asignación activa |
| Actividades | Fecha/hora de la actividad, lugar, área, responsable, participantes y cupo registrados |
| Visitas | Selector explícito Programación o Entradas registradas; no mezcla ambas fechas |
| Asignaciones | El periodo usa día de la jornada; la ficha conserva fecha de registro de asignación |

Los conteos y gráficos de una vista mensual respetan el mes mostrado. Las filas sin la fecha elegida se señalan aparte; en Visitas se distingue ausencia de programación y ausencia de entrada. No se colocan artificialmente hoy ni se crean eventos para llenar días. El calendario respeta búsqueda, bandeja, estado, área, fechas y autorizaciones. Un día fuera del mes se rechaza. Cambiar de vista conserva un mes explícito cuando existe.

## Gráficos útiles para la tarea

- Jornadas combina horarios y personal con asignación activa por jornada. Cero no significa cobertura insuficiente ni ausencia laboral.
- Asignaciones compara áreas con un mosaico y funciones registradas con proporciones. Seleccionar una función filtra valores existentes; no asigna profesionales ni cambia funciones.
- Actividades compara participantes registrados y cupo por actividad; la ausencia de cupo aparece como Sin registrar, sin porcentajes de ocupación inventados. La distribución por área complementa esa lectura. Los participantes no acreditan asistencia.
- Reportes abre una explicación local del indicador, fecha de conteo y periodo. Ocupación actual permanece separada y no depende del periodo. Los conteos de procesos distintos no se suman como personas únicas.

Solo son consultas; no se incorporan altas, asignaciones, transiciones clínicas ni cambios a la base congelada. Los tonos, superficies y controles pertenecen al sistema de diseño existente. El movimiento reducido desactiva transiciones; en móvil el calendario desplaza sus siete columnas dentro de una región, sin desbordar la página.

## Integración y verificación

Archivos principales: `ExploradorAdministrativoService`, `OperacionController`, `operacion.blade.php`, parciales `calendario-operativo`, `ficha-fragmento`, `guia-operativa`, `indicador-modal`, `filtros-operativos`, `graficos-por-proceso`; scripts `operaciones-interactivas.js`; estilos `calendario-operativo.css` y patrón existente de operaciones.

Pruebas de regresión: mes completo con 26 actividades y paginación diez/página 99, día con 25 registros, intersección de filtros, febrero bisiesto, mes/día inválidos, funciones inventadas, fechas distintas de visita, fragmentos autorizados/denegados, privacidad y ausencia de mutaciones. Frontend cubre URL estable, foco, petición cancelada/tardía, respuestas de login/error, preferencia de gráficos y liberación del fondo antes de desmontar. La prueba visual sintética revisa siete columnas y ancho a 1200/760/390 px.

La comprobación real verificó los 50 registros de Jornadas en octubre, mes siguiente vacío, modal local y retorno de foco sin elementos inertes. Visitas muestra la ausencia de fechas existentes. Asignaciones permitió buscar un área registrada y aplicar su filtro; Actividades mostró calendario y gráficos distintos sin desbordar la página en escritorio.

Resultados: 73 pruebas PHP y 922 aserciones aprobadas en la regresión de explorador, admisiones, residentes y espacios; 52 pruebas frontend aprobadas sin omisiones; build de Vite aprobado. La ejecución frontend inicial tuvo fallos de inicio de Chromium en el entorno restringido; la ejecución autorizada fuera de ese entorno pasó completa. Los pendientes globales anteriores de la guía 08 siguen sin certificarse por esta revisión acotada.

## Movimiento con propósito

La pulsación confirma el día elegido sin desplazar las celdas; el panel del día aparece con una transición breve de opacidad y desplazamiento. Las áreas responden al foco de teclado igual que al puntero; el tablero de cobertura resalta el grupo que contiene la acción enfocada. Las proporciones de gráficos conservan su animación específica y no se agregan efectos repetitivos a todas las filas. Todos estos efectos respetan movimiento reducido y reutilizan tokens existentes, sin dependencias nuevas.

El ajuste final de movimiento compiló correctamente. El control automático del navegador dejó de iniciar por un fallo del entorno de ejecución de Windows, por lo que la inspección real posterior de esas transiciones y de los indicadores locales de Reportes queda pendiente. Se solicitó abrir Actividades en el panel de Codex; la herramienta confirmó la solicitud en cola, no una carga verificada. No se hicieron commits, migraciones ni seeders.


## Revisión posterior de filtros e interpretación

Dos subagentes revisaron interacción y claridad del área de salud, sin modificar registros. Se corrigieron defectos encontrados en esa revisión:

- Cambiar entre Calendario, Programación, Tarjetas y Tabla conserva la bandeja efectiva y el día. Retirar el mes retira también su día dependiente.
- Categorías, meses, comparaciones y estados del gráfico se calculan después de aplicar filtros; los chips de estado de la cabecera conservan explícitamente el contexto general de búsqueda y fechas. El total del gráfico representa su selección. Elegir un grupo conserva los otros filtros.
- Participantes/cupo usan una escala común entre actividades; el gráfico identifica código y fecha, aclara el límite de ocho actividades y no confunde participación registrada con asistencia.
- Tras un periodo inválido, el formulario recupera área y eje de fecha de Visitas. El chip distingue Programación de Entrada registrada.
- Al cerrar una ficha abierta directamente mediante URL se elimina solo `detalle`, mediante reemplazo de historial. La apertura normal sigue sin cambiar URL. La paginación y el selector de cantidad excluyen ese parámetro para evitar reabrirla.

No se amplían permisos ni se incorporan operaciones clínicas. Se mantiene la base congelada y no se crean cuentas, seeders ni commits. Laravel y Vite se reactivaron en 8010/5174; la revisión visual y el inicio de sesión automático no pudieron completarse porque el control del navegador falla antes de leer la página.

Verificación de esta revisión posterior: 77 pruebas PHP y 965 aserciones aprobadas; 53 pruebas frontend aprobadas, sin omisiones; build de Vite aprobado y diff sin errores de espacios. Las nuevas regresiones cubren día entre vistas, filtros en todos los gráficos, bandeja efectiva, recuperación del formulario, escala común y exclusión de detalle en paginación. La cuenta administrativa se comprobó mediante lectura de la BD, sin recuperar ni mostrar su contraseña. El intento de login quedó bloqueado en el arranque del control del navegador y no se declara una sesión iniciada.
