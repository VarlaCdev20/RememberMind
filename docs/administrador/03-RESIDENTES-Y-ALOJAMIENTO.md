# Residentes y alojamiento

Entrega del 2026-10-06 en la rama `REINICIO`. Alcance: directorio administrativo, distribución de residentes, mapa físico y cambio de alojamiento dentro de una admisión vigente. No sustituye el expediente clínico ni acredita la finalización de todo el rol administrador.

## Abrir y trabajar

En `C:\laragon\www\RememberMind`, terminal PowerShell, reutilizar los procesos de Laravel y Vite que estén activos. La sesión administrativa existente abre:

`http://127.0.0.1:8010/admin/administracion/residentes`

Si los servicios no están activos, iniciar en terminales separadas:

```powershell
php artisan serve --host=127.0.0.1 --port=8010
npm.cmd run dev -- --host=127.0.0.1 --port=5174
```

Comprobar primero que esos puertos estén libres. Detener únicamente los procesos iniciados para este proyecto con `Ctrl+C` en sus terminales. No ejecutar migraciones, seeders ni crear cuentas como paso de arranque.

## Vistas y filtros

- **Tarjetas:** identidad compacta, cama/habitación, responsable, estado y acciones.
- **Lista:** comparación horizontal de personas con las mismas acciones.
- **Tabla:** comparación administrativa con desplazamiento horizontal dentro de la tabla en pantallas pequeñas.
- **Camas:** habitaciones paginadas, agrupadas por su piso real. Cada cama abre un detalle con disponibilidad, estado registrado, tipo y ocupante actual.

La búsqueda ignora mayúsculas/minúsculas y cubre nombres, apellidos, documento, códigos, habitación y cama. Los filtros combinan estado real de residentes, piso, habitación y presencia de cama vigente. Se aplican con **Buscar**; cada etiqueta activa puede retirarse por separado. **Limpiar** conserva la vista elegida. Los cambios de vista/filtro reinician la página; abrir y cerrar un detalle conserva la página del directorio.

La paginación compartida ofrece 10, 20 o 50 residentes; en el mapa esos tamaños corresponden a habitaciones. El mapa carga las camas de las habitaciones de la página mediante consultas agrupadas, sin consultas por tarjeta.

El buscador de selectores aparece únicamente con más de 10 opciones; en ese caso el menú limita su altura a tres filas y permite desplazamiento. Se reutilizan los componentes institucionales de selector, calendario, modal y paginación.

## Gráficos y significado

Las métricas y los gráficos de residentes siguen los filtros: total, con cama y sin cama; distribución de alojamiento y residentes por piso. Los elementos llevan a los filtros correspondientes. **Ocultar gráficos** permite trabajar con menos altura y **Actualizar residentes** vuelve a consultar la vista vigente.

Los conteos de camas describen infraestructura física según piso/habitación. No representan los mismos datos que el total de personas filtradas. Camas ocupadas se determinan por `ocupaciones_cama.estado IN ('ACTIVA', 'ACTIVO')`. La disponibilidad reutiliza el criterio de `FormalizarAdmision`: cama y habitación habilitadas, sin ocupación activa. Cualquier otra cama sin ocupante se muestra **No habilitada**, acompañada de su estado registrado.

Cuando se filtran personas en el mapa, solo se muestran sus camas y las habitaciones que las contienen. Personas sin ocupación activa no se colocan artificialmente en el mapa. Pisos nulos o vacíos aparecen como **Piso sin registrar**. No se deduce el piso del código de habitación ni se modifica la BDD para completar datos.

## Flujo institucional y prevención

1. La primera cama pertenece a la **admisión formal** de una preadmisión aprobada; ese proceso crea al residente. El directorio enlaza a **Ingresos por formalizar**.
2. Un residente con estancia activa puede abrir **Gestionar alojamiento** desde su tarjeta, lista, tabla o panel.
3. El formulario muestra ubicación actual y camas de destino disponibles. Permite fecha, hora y motivo; presenta el destino y la consecuencia antes de confirmar.
4. Al confirmar, el backend exige cuenta activa, permiso `ocupaciones_cama.gestionar`, Policy global y contextual, residente activo/admitido y una única admisión activa con ocupación inicial histórica.
5. Dentro de una transacción se bloquean residente, admisión, ocupación, camas y habitaciones; se vuelve a comprobar disponibilidad y se contrasta la ocupación con la que abrió el formulario.
6. La ocupación anterior queda **FINALIZADA** con fecha y motivo de liberación; la nueva queda **ACTIVA**, vinculada a la misma admisión y al usuario autenticado. La auditoría usa Activitylog. Los índices existentes protegen la exclusividad de cama y residente.

Se rechazan cama ocupada/no habilitada, misma cama, fecha anterior al alojamiento/admisión, estancia inactiva, admisión cerrada/inconsistente, primera ocupación faltante y formularios desactualizados. El fallo no libera la cama anterior. Un familiar no puede usar este flujo aunque reciba accidentalmente el permiso operativo.

## Detalle del residente

El panel conserva Resumen, Datos, Salud, Documentos e Historial y la autorización contextual existente. Resumen/Datos incluyen la cama exacta y su piso. Historial añade las últimas 30 ocupaciones con fechas y motivos de liberación, además del historial de estados. El expediente completo mantiene su ruta y autorización independientes.

Los datos clínicos detallados permanecen en el expediente autorizado. Esta entrega no agrega edición clínica, permisos ni facultades por rol.

## Errores y recuperación

- Sin resultados: retirar una etiqueta, cambiar búsqueda o limpiar filtros.
- Sin cama disponible: se conserva el alojamiento; revisar la infraestructura habilitada.
- Ubicación cambió: cerrar y volver a abrir el formulario para consultar su estado actual.
- Admisión o historial inconsistente: revisar el ingreso formal; no crear una cama inicial desde este formulario.
- Fallo inesperado: Laravel registra el fallo; no se emite una confirmación falsa ni se exponen SQL o secretos.

Los datos persistidos usan las tablas existentes `residentes`, `admisiones`, `ocupaciones_cama`, `camas`, `habitaciones`, `residentes_contactos` y Activitylog. No se añadieron tablas, columnas, catálogos, usuarios ni seeders.

## Verificación y límites

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --filter 'ResidentesEspaciosTest|AlojamientoResidenteTest'
node --test --test-concurrency=1 "tests/Frontend/**/*.test.js"
npm.cmd run build
```

Las pruebas usan SQLite en memoria y datos sintéticos: filtros reales, agrupación/paginación, consultas constantes, estados de cama, autorización, conservación de historial/admisión, rechazo sin efectos parciales y reintentos. Se ejecutó además la suite relacionada de admisiones, integridad V2, navegación familiar y auditoría.

La revisión del navegador usa la sesión existente en modo de consulta: cuatro vistas, detalle de cama/residente, prevención del formulario sin disponibilidad, controles, estados vacíos, móvil y persistencia de tema. No se confirmó un traslado sobre los residentes de la BDD operativa.

Resultado de esta entrega: suite relacionada **61 pruebas / 516 aserciones**; después de añadir historial, piso sin registrar y conservación de página, suite focalizada **19 pruebas / 130 aserciones**. Frontend **40 pruebas aprobadas** y build Vite aprobado. La prueba PostgreSQL queda omitida de forma explícita. Navegador revisado en escritorio, tablet y móvil, sin desbordamiento de la página; la ficha de cama cabe en la ventana móvil.

**Pendiente de integración:** probar dos asignaciones/traslados concurrentes en un PostgreSQL desechable. SQLite no demuestra el comportamiento de los bloqueos PostgreSQL. No ejecutar esta prueba con los residentes operativos.

`AlojamientoBloqueoPostgresTest` comprueba el bloqueo de una cama entre dos conexiones PostgreSQL. Está omitido por defecto; requiere `RM_POSTGRES_CONCURRENCIA=1`, conexión `pgsql` y una base ya migrada cuyo nombre termine en `_testing`. Crea únicamente habitación/cama sintéticas y las elimina al terminar. No ejecuta migraciones ni seeders. Esta prueba del bloqueo tampoco sustituye una carrera completa entre dos traslados.

**Siguiente alcance:** mantenimiento completo de Habitaciones y camas, cierre de estancia y expediente administrativo se revisan por separado; sus pantallas existentes no equivalen a procesos ya certificados.
