# Ocupación: mapa y alojamiento

Revisión del 2026-10-06 en `REINICIO`. URL: `http://127.0.0.1:8010/admin/administracion/ocupacion`. El arranque y apagado de Laravel/Vite se describen en [Habitaciones](04-HABITACIONES-Y-CAMAS.md); se reutilizan los mismos procesos y la cuenta existente.

## Responsabilidad de cada ventana

Habitaciones y camas mantiene infraestructura física. Ocupación permite conocer quién ocupa una cama, desde cuándo, su disponibilidad y los movimientos registrados. No crea residentes ni sustituye la admisión formal.

## Interacción

El mapa agrupa por piso real y habitación, con niveles desplegables, tarjetas compactas y camas dibujadas con SVG. Los estados combinan texto, icono y color: disponible, ocupada y no habilitada. La vista previa se activa por puntero o foco y se descarta con Escape, desplazamiento o al abandonarla. La selección abre la ficha: ocupante autorizado, fecha, duración, características e historial acotado. Los gráficos comparan disponibilidad y pisos usando datos actuales; no inventan tendencias.

Las vistas Mapa/Tarjetas/Lista/Tabla conservan los filtros de búsqueda, piso, habitación, disponibilidad y estado físico. La paginación compartida admite 10/20/50. En Mapa pagina habitaciones; en las otras vistas pagina camas. Los filtros de disponibilidad afectan los resultados; los gráficos conservan el contexto físico previo a ese filtro.

## Seleccionar un residente desde una cama libre

1. Acercarse o enfocar una cama disponible: aparece el botón **+**. En pantalla táctil permanece visible. También existe **Elegir residente** en la ficha de la cama.
2. Elegir un residente ya admitido mediante el selector compartido. El buscador aparece solo con más de diez opciones.
3. Pulsar **Revisar traslado**: abre el formulario existente con esa cama preseleccionada, la ubicación actual, fecha/hora y motivo.
4. Revisar y confirmar. `CambiarAlojamientoResidente` vuelve a validar disponibilidad, admisión, Policy, ocupación anterior y fecha; preserva la admisión y finaliza la ocupación anterior con trazabilidad.

La selección y revisión no escriben ocupaciones. Cancelar conserva la ubicación actual. Si la cama deja de estar disponible entre la selección y la revisión, se muestra un error y no se continúa. Si cambia antes de confirmar, el Action existente vuelve a rechazar la operación. La cama de origen del mapa está bloqueada en el estado Livewire de selección; no se confía en un identificador de destino manipulado para esa etapa.

Los postulantes siguen por **Preparar ingreso → Admisiones**. La primera cama se registra al formalizar la admisión; el selector del mapa solo ofrece residentes con admisión activa y ocupación inicial registrada. La admisión inconsistente o la Policy contextual pueden impedir continuar aunque el residente aparezca en la lista; el formulario explica el impedimento antes de guardar.

## Historial y permisos

**Historial** abre los movimientos liberados de la bandeja existente. **Mapa de alojamiento** regresa al mapa. El historial utiliza la paginación compartida y requiere la Policy global de Residentes porque contiene identidades. No se amplía el alcance a familiares.

La ruta exige sesión, cuenta activa, `admisiones.ver_dashboard` y `ocupaciones_cama.ver`. El mapa físico exige además `habitaciones.ver`. Sin este último permiso se mantiene la bandeja de ocupaciones autorizada; no se concede acceso al mapa automáticamente. La identidad exige `residentes.ver` y Policy; la ficha revalida la Policy del ocupante actual. Sin acceso a personas se muestran solo datos físicos, sin nombres ni el botón de traslado.

El **+** requiere además `ocupaciones_cama.gestionar`. La selección revalida estos límites y filtra los residentes mediante Policy contextual. La persistencia sigue en el Action existente, con transacción, auditoría y controles de ocupación única. [Residentes y alojamiento](03-RESIDENTES-Y-ALOJAMIENTO.md) describe ese flujo.

## Verificación y límites

Pruebas aisladas cubren selección sin persistencia, cama que deja de estar disponible, ausencia de permiso de Residentes, botón exclusivamente en cama disponible, separación respecto al inventario y protección del historial. La revisión visual cubre las ventanas reales, ficha/edición, foco y formularios responsive, sin guardar registros operativos.

La base operativa conserva sus datos; no se ejecutan seeders ni migraciones. El inventario local actual no tiene camas libres: la selección desde una cama libre se prueba con datos sintéticos en SQLite. Su prueba visual completa en una base descartable y las carreras PostgreSQL de traslado/mantenimiento siguen pendientes. No se afirma que todo el rol administrador esté terminado.

Resultado integrado: **50 pruebas backend / 374 aserciones**, **43 pruebas frontend**, build aprobado (90 módulos). La prueba visual sintética adicional verifica ancho aprovechado, densidad de cama, acceso por teclado al + y visibilidad táctil en 1200/820/390 px.
