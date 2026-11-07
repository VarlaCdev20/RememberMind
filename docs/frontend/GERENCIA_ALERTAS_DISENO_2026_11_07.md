# Alertas de Gerencia — diseño de consulta

Fecha: 07/11/2026. Alcance: presentación de `admin.alertas-clinicas` para el perfil de supervisión. La propietaria solicita diseñar la vista de alertas durante el recorrido de Gerencia.

## Fuentes y recorrido

Fuentes: instrucciones institucionales y de recursos; contrato visual y stack UX/UI; responsabilidad del módulo Alertas; `AlertasPanel`, `BandejaAlertas`, `LenguajeAlerta` y parcial `gerencia`. La implementación existente conserva el ciclo, permisos y reglas aprobadas; este trabajo no los redefine.

Objetivo de Gerencia: localizar pendientes dentro de su ámbito, consultar residente/responsable y abrir la trayectoria de la alerta. Entrada desde Consulta institucional → Alertas. Recorrido: resumen de alertas activas → filtros → resultados → Ver detalle → volver a la consulta. El registro de atención sigue perteneciendo al rol competente. Los estados vacíos ofrecen cambiar filtros; no se fabrican alertas o responsables.

## Composición implementada

- Encabezado compartido, superficies café grisáceo/salvia, tipografía y controles del Design System.
- Tres accesos de resumen compactos con icono, cifra y flecha: prioridades altas/críticas, sin responsable y límite registrado superado. Se mantienen los conteos reales y la aclaración de que pueden solaparse.
- Búsqueda, estado y prioridad en una fila adaptable. Origen, especialidad, responsable y fechas permanecen en «Más filtros», respetando la apertura automática de filtros adicionales existentes.
- Tabla de cinco columnas: residente/alerta, seguimiento, responsable, fecha y detalle. Prioridad y estado mantienen significados separados, agrupados visualmente. La fecha muestra día y hora en dos líneas.
- Se reutiliza `status-badge` para prioridad registrada: alta/crítica en variante de peligro, otras prioridades neutras. No se infiere gravedad clínica desde el frontend.
- En anchos pequeños, las filas se presentan como fichas etiquetadas. La única acción principal sigue siendo «Ver detalle».

El patrón CSS está limitado a `rm-manager-alerts`; los estilos de tabla se limitan al listado y no se propagan a tablas de historia dentro del detalle. No cambian backend, persistencia, estados, permisos, orden ni paginación. No se incorporan dependencias o una paleta paralela. Los cambios previos de Enfermería en `alertas-panel.blade.php` quedan fuera de este trabajo.

## Verificación

Pruebas funcionales de entrada por diez roles y filtros de Gerencia: 2 aprobadas / 125 aserciones. Pruebas negativas de gráficos clínicos restringidos y formularios de atención para roles de coordinación/lectura: 2 aprobadas / 29 aserciones. Total localizado: **4 pruebas / 154 aserciones**, SQLite de pruebas; no equivale a integración PostgreSQL.

El ensayo E2E usa el servidor sintético protegido existente, token local y base desechable; bloquea solicitudes externas. Verifica cinco anchos, resumen/filtros, limpieza, búsqueda vacía, apertura/cierre del detalle, ausencia de errores JavaScript y desbordamiento/superposición. La expectativa de limpieza se ajustó al contrato existente: «Limpiar» muestra todos los estados, sin cambiar el comportamiento del producto.

Se comprobó en el navegador de la propietaria que la ruta de Gerencia carga la nueva composición; no se realizaron mutaciones ni comunicaciones. Las capturas de entrega utilizan datos sintéticos.

Build de Vite y prueba E2E aprobados en 1440, 1280, 1024, 768 y 390 px. Revisión visual de la bandeja en escritorio y móvil y del detalle en móvil con datos sintéticos. Evidencia en `output/gerencia-alertas-diseno-20261107/`.
