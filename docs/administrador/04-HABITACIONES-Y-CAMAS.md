# Habitaciones y camas: exploración visual

Entrega del 2026-10-06, rama `REINICIO`, después del cierre de Residentes (`ca498e6`). La pantalla utiliza información física y ocupaciones vigentes, sin modificar la estructura congelada ni los registros operativos durante la verificación.

## Abrir la pantalla

URL: `http://127.0.0.1:8010/admin/administracion/habitaciones`.

En PowerShell, desde `C:\laragon\www\RememberMind`, reutilizar los procesos existentes de Laravel y Vite. Si no están activos y los puertos están libres, iniciar en terminales separadas:

```powershell
php artisan serve --host=127.0.0.1 --port=8010
npm.cmd run dev -- --host=127.0.0.1 --port=5174
```

Cerrar únicamente esos procesos con `Ctrl+C` en sus terminales. Usar una cuenta activa existente. No crear usuarios, seeders ni ejecutar migraciones como paso de arranque.

## Interacción y vistas

**Mapa** es la vista inicial. Agrupa las habitaciones paginadas por su piso registrado. Pisos y habitaciones se despliegan de forma independiente; **Contraer todo / Desplegar todo** controla ambos niveles. Las camas se dibujan con un SVG del propio proyecto y usan relieve, sombras y movimiento corto al acercarse. La interacción sigue disponible mediante teclado y toque; el movimiento reducido desactiva las animaciones.

Al acercarse o enfocar una cama aparece una vista previa de disponibilidad y ocupante autorizado. Puede cerrarse con Escape, al abandonar el contenido, al desplazar o al redimensionar. Una demora breve permite mover el puntero entre la cama y la vista previa sin que desaparezca inmediatamente. Al seleccionar se abre la ficha completa.

**Tarjetas**, **Lista** y **Tabla** muestran las mismas camas con diferente densidad. La tabla permite comparar ubicación, ocupante y fecha de asignación; su desplazamiento horizontal queda contenido en móvil. La paginación compartida admite 10/20/50: en Mapa son habitaciones; en las otras vistas son camas.

## Estados y prevención

| Disponibilidad física | Fuente | Representación |
| --- | --- | --- |
| Ocupada | Existe ocupación `ACTIVA` o `ACTIVO` | Icono de persona, texto y acento informativo |
| Disponible | Cama y habitación habilitadas, sin ocupación activa | Icono de cama, texto y acento de éxito |
| No habilitada | Sin ocupación activa, pero cama o habitación no admiten asignación | Candado, texto y superficie neutral |

El estado guardado de la cama se muestra por separado. La disponibilidad reutiliza `FormalizarAdmision::filtrarCamasDisponibles`, evitando reglas paralelas. Una cama con estado físico habilitado y una ocupación vigente siempre aparece ocupada. No se infiere un piso del número de habitación: los nulos/vacíos aparecen como **Piso sin registrar**.

Seleccionar una cama no realiza una asignación. Una cama libre enlaza a **Preparar ingreso** cuando la ruta está autorizada. La primera cama sigue perteneciendo a la admisión formal. Una ocupada enlaza al detalle de Residentes, donde se conserva el flujo de cambio de alojamiento ya implementado. **Gestionar espacios** enlaza al mantenimiento existente y exige el permiso vigente; esta entrega no agrega reglas nuevas de mantenimiento ni facultades clínicas.

## Filtros y gráficos

La búsqueda ignora mayúsculas/minúsculas y cubre códigos de cama, habitación, nombre de habitación, tipo y ocupante cuando está autorizado. Los filtros combinan piso, habitación, disponibilidad física y estado guardado. Las etiquetas activas se retiran individualmente y **Limpiar** conserva la vista. Cambiar vista/filtros reinicia la página; abrir y cerrar una ficha conserva página, tamaño y filtros.

Los conteos y gráficos describen la ubicación/búsqueda elegidas **antes** del filtro de disponibilidad y estado. Esto permite comparar las tres categorías y cambiar entre ellas sin perder el contexto físico. El resultado y su paginación sí aplican todos los filtros. La gráfica de ocupación expresa camas ocupadas dividido entre todas las camas físicas registradas; el gráfico por piso compara ocupadas y disponibles y mantiene visibles las camas no habilitadas como capacidad restante. No representa una tasa clínica ni una tendencia histórica.

**Ocultar gráficos** reduce altura; **Actualizar habitaciones** vuelve a consultar la misma vista. Los datos no se actualizan mediante sondeo periódico: reflejan la última navegación/refresco. El formulario de asignación revalida disponibilidad antes de persistir.

## Ficha y trazabilidad

La ficha muestra tipo, estado de cama y habitación, capacidad, observaciones físicas, ocupante actual autorizado, fecha/hora y días transcurridos. El origen proviene de la admisión o del último cambio previo de alojamiento de esa misma admisión; el motivo aparece solo cuando está registrado. No se utiliza el motivo clínico de ingreso para explicar una cama.

Las últimas 12 ocupaciones de la cama se consultan únicamente al abrir su ficha, con fechas, estado, admisión y motivo de liberación. No se cargan expedientes clínicos ni identidad histórica de otros residentes. El nombre y código del ocupante actual solo se incorporan con `residentes.ver` y Policy global; el detalle actual vuelve a verificar Policy contextual.

## Autorización y datos

La ruta mantiene sesión autenticada, cuenta activa, `admisiones.ver_dashboard` y `habitaciones.ver`. Los familiares no acceden al mapa global. Tener permiso de infraestructura sin permiso/Policy de residentes permite ver disponibilidad, pero no identidad, búsqueda por nombre, motivo de traslado ni historial de ocupaciones.

La fuente física utiliza `habitaciones`, `camas`, `ocupaciones_cama` y la admisión existente; no modifica tablas, columnas, catálogos, usuarios o seeders. La selección es de consulta. Las consultas del mapa se agrupan por página y no crecen con cada cama o habitación; la ficha carga un historial acotado.

## Recuperación

- Sin resultados: quitar una etiqueta, cambiar piso o limpiar filtros.
- Piso no registrado: consultar o completar el dato mediante el mantenimiento autorizado; no se inventa su ubicación.
- Cama ocupada/no habilitada: consultar características; no existe acción de asignación directa.
- Cama inexistente: respuesta 404; nunca se sustituye por la primera cama disponible.
- Cuenta/permiso insuficiente: acceso denegado; no se conceden permisos automáticamente.

## Verificación

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --filter 'MapaHabitacionesTest|ResidentesEspaciosTest|AlojamientoResidenteTest|AdmisionesOperativasTest'
node --test --test-concurrency=1 "tests/Frontend/**/*.test.js"
npm.cmd run build
```

Las pruebas usan datos sintéticos en SQLite en memoria. Cubren cama ocupada/habitación bloqueada, filtros y búsqueda, permisos físicos sin identidad, familiar/cuenta inactiva, motivo de traslado real, cuatro vistas, paginación y consultas constantes. La revisión del navegador cubre desplegables, vista previa mediante foco, selección, las cuatro vistas, móvil/tablet, estados vacíos y ambos temas. No se registraron asignaciones ni traslados sobre residentes operativos.

Resultado: **39 pruebas backend / 298 aserciones**, **42 pruebas frontend aprobadas** y build Vite aprobado. El mapa conserva habitaciones sin camas y permite encontrarlas por su código/nombre; la capacidad no crea camas ficticias. El historial diferencia ocupaciones finalizadas sin fecha de liberación de una ocupación vigente. No aparecieron errores nuevos del navegador durante la revisión final del módulo.

La verificación de carreras concurrentes PostgreSQL pertenece al flujo de asignación y sigue pendiente según la guía de Residentes. Esta entrega de consulta no afirma haberla resuelto.
