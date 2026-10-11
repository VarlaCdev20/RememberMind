# Coordinación diaria del administrador

Entrega del 2026-10-06 en `REINICIO`, sin commit ni push. Reutiliza Laravel/Vite y la cuenta existente; el arranque se describe en [Habitaciones](04-HABITACIONES-Y-CAMAS.md). No crea usuarios, seeders ni registros operativos.

## Función y distribución

Las antiguas tablas genéricas pasan a bandejas con objetivo visible, contexto de búsqueda, estados reales, dos gráficos y vistas propias del proceso. La cabecera ofrece actualización, ocultar gráficos y navegación contextual. Las superficies usan los tokens institucionales, sombras moderadas, relieve al acercarse, foco visible y movimiento reducido.

| Ventana | Vistas | Información y gráficos |
| --- | --- | --- |
| Jornadas | Agenda, tarjetas, tabla | Fecha, horario, cantidad de personal asignado y estado; distribución por horario y meses |
| Asignaciones | Lista, tarjetas, tabla | Personal, jornada, área, función y fecha; distribución por área y meses |
| Actividades | Agenda, tarjetas, tabla | Lugar, responsable, área, fecha, cupo y participantes; distribución por área y meses |
| Visitas | Agenda, lista, tabla | Contacto, residente, programación, entrada, salida y motivo; estados y meses |

Las agendas agrupan por día dentro de la página actual y ordenan fechas de forma ascendente. Las otras vistas con fecha empiezan en orden descendente. Se puede cambiar el orden. Jornadas distingue Hoy/Próximas/Finalizadas/Todas; Visitas distingue Hoy/Programadas/Dentro/Finalizadas/Todas conforme a fechas registradas. Una bandeja vacía ofrece abrir todos los registros cuando existen datos en el módulo.

## Filtros e interacción compartida

1. Buscar por palabras: cada palabra debe aparecer en alguno de los campos disponibles; permite buscar nombres distribuidos entre nombres y apellidos.
2. Elegir bandeja, estado registrado, fechas y orden. Los selectores institucionales muestran buscador solamente con más de diez opciones; el calendario institucional envía ISO `Y-m-d` sin depender de Livewire para un formulario GET.
3. Aplicar: conserva la vista y el tamaño de página, reinicia la página y presenta filtros activos removibles. Limpiar recupera la bandeja inicial.
4. Elegir un estado o grupo del gráfico: abre la bandeja general y filtra ese grupo. Elegir un mes aplica su intervalo. Se conserva búsqueda y contexto; al cambiar grupo se elimina el estado previo para evitar resultados contradictorios.
5. Elegir un registro: abre una ficha modal con los campos completos y observaciones disponibles. Escape, fondo y Cerrar vuelven a la misma bandeja. La ficha recibe foco y bloquea el desplazamiento del fondo.

El resumen y los gráficos se calculan antes de aplicar la bandeja, estado o categoría seleccionada. Sí respetan búsqueda, rango de fechas y, en Visitas, fecha exacta. Muestran hasta ocho grupos y ocho meses con registros; no generan meses vacíos ni tendencias inventadas. La paginación compartida ofrece 10/20/50 y conserva filtros. En móvil los gráficos empiezan plegados al cargar una página nueva; las listas se apilan y las tablas desplazan horizontalmente dentro de su contenedor.

## Validación y límites

Un rango invertido, fecha inválida, estado inventado, vista incompatible o tamaño de página no admitido se rechaza en el servidor. El formulario conserva valores corregibles, muestra mensajes accesibles y mantiene la última consulta válida. Los estados y categorías proceden de los registros reales; no se agregan catálogos ni reglas a la base congelada.

Esta entrega habilita consulta y navegación. No incorpora botones de creación, cierre de jornada, asignación profesional, inscripción de participantes ni registro de entrada/salida mientras sus operaciones existentes no tengan una revisión completa. Los endpoints previos no quedan certificados por este rediseño: siguen pendientes, entre otros, turno activo/duplicidad de jornada, jornada abierta/personal activo/área coherente, autorización contextual de participantes y transiciones de visita. No cambia responsabilidades profesionales.

## Implementación y autorización

`ExploradorAdministrativoService` coordina consultas, metadatos de vistas, gráficos, filtros, paginación y detalle; `ConsultaOperativaService` conserva las fuentes V2. `OperacionController` valida entradas y exige cuenta activa, permiso explícito y ausencia de rol familiar. Las visitas requieren además autorización general de lectura de residentes y Policy del residente al abrir su ficha. Jornadas/Asignaciones/Actividades consultan su información operativa con su permiso específico.

Presentación: `operacion.blade.php`, parciales de tarjetas/campos/detalle, `operaciones-interactivas.js` y `operaciones-interactivas.css`. Reutiliza selector, calendario, cabecera, paginación y gestor de tema existentes. No agrega dependencias, tablas, columnas ni escrituras; los conteos de asignados/participantes sin relaciones muestran cero.

## Verificación

Las pruebas `ExploradorAdministrativoTest` cubren las tres vistas de cada módulo, detalles, búsqueda por palabras, conteos, categorías numéricas, entradas inválidas, recuperación con la misma sesión, permisos/cuenta/familiar, tamaño constante de consultas y ausencia de mutaciones. Las suites de Admisiones, Residentes y alojamiento se ejecutan como regresión. Las pruebas frontend comprueban inicialización móvil y actualización conservando URL; la revisión real de navegador verifica calendario, selector corto sin búsqueda, tabla, modal, rango inválido y persistencia del tema entre las once ventanas.

El resultado final de pruebas/build y sus límites se registra en [Seguimiento y reportes](08-SEGUIMIENTO-Y-REPORTES.md). La concurrencia PostgreSQL de alojamiento continúa como pendiente de la entrega anterior; estas bandejas son de lectura.
