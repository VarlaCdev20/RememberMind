# Preadmisiones: interfaz y controles institucionales

## Uso

En `/admin/admisiones/preadmisiones`, el administrador puede alternar entre lista, tarjetas y tabla. Las tres vistas comparten búsqueda, filtros, expediente y paginación de 10, 20 o 50 solicitudes.

El panorama muestra cantidades reales por estado y solicitudes registradas durante seis meses. La leyenda filtra por estado; una barra filtra por mes. Los indicadores son generales y el contador de resultados corresponde a los filtros activos. Las admisiones formalizadas se muestran separadas de las aprobaciones pendientes de ingreso. No se agrega un estado institucional de revisión.

Los iconos de expediente, documentos e historial abren las pestañas existentes. El registro guía cinco pasos, valida antes de avanzar y permite volver a pasos anteriores. El último paso presenta un resumen editable. Registrar una preadmisión no crea un residente.

## Componentes compartidos

- `x-ui.selector`: opciones nativas como fuente de datos, búsqueda sin acentos solo al superar diez opciones, teclado, selección simple o múltiple y un máximo de tres opciones visibles cuando existen más de diez. El resto permanece disponible mediante desplazamiento.
- `x-ui.calendario`: fechas ISO sin conversión UTC, semana desde lunes, cambio de mes/año y límites inclusivos definidos por el formulario. La fecha de nacimiento conserva el límite de edad que ya valida el backend.
- Ambos requieren contexto Livewire y `wire:model`. En un modal con foco restringido, indicar `teleport` con el selector de una capa interior; el formulario usa `#rm-wizard-control-layer`.

Las superficies, estados, sombras y colores consumen el Design System existente. Las transiciones respetan movimiento reducido. El gestor de tema consulta el documento actual, reaplica la preferencia al navegar y sincroniza cambios entre ventanas. Los iconos claro/oscuro tienen reglas que prevalecen sobre el estilo general de iconos del encabezado.

## Seguridad y errores

Se mantienen autorización, revisión y admisión formal existentes. No se modifican tablas, permisos, catálogos ni cuentas. Los fallos inesperados se reportan mediante Laravel y conservan los datos del formulario. Si falla la generación documental o el aviso de correo, la respuesta informa el resultado parcial en lugar de afirmar un éxito completo.

El listado carga conteos documentales; el contenido se obtiene al abrir el expediente. El selector de camas consume la relación V2 `residente`.

## Verificación local

Desde `C:\laragon\www\RememberMind`, en PowerShell:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --filter 'CasosPreadmisionTest|Admision'
node --test tests/Frontend/controles-institucionales.test.js tests/Frontend/shell-visual-consistency.test.js
npm.cmd run build
```

Las pruebas PHP usan la configuración SQLite de pruebas, no la base operativa. Comprobar también en navegador: filtro vacío y limpieza, tres vistas, páginas de resultados, búsqueda/teclado en selectores, calendario, navegación con ambos temas y distribución móvil.

La suite frontend general tuvo un timeout en `sidebar-responsive.test.js:72`, al esperar `data-rm-sidebar-ready` después de navegar en su fixture. Esa prueba global del menú queda pendiente de diagnóstico; las pruebas de controles y consistencia de tema pasan. No se realizaron escrituras de registros operativos durante la revisión visual.
