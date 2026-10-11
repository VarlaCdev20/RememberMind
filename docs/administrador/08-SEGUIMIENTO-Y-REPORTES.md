# Seguimiento administrativo y reportes

Entrega del 2026-10-06 en `REINICIO`, sin commit ni push. Cierra la mejora visual de once bandejas de consulta; no declara completos todos los procesos institucionales del administrador.

## Alertas e incidentes

Alertas ofrece Lista/Tarjetas/Tabla, bandejas por estado registrado y selector de prioridad obtenido de los datos reales. Los gráficos comparan prioridades y meses; el filtro de prioridad afecta el contexto del resumen. La ficha muestra descripción, origen de generación y los últimos doce eventos de `eventos_alerta`, con fecha, transición y descripción. No modifica ni elimina esos eventos.

Incidentes ofrece Cronología/Lista/Tabla, gravedad registrada y meses. La ficha amplía descripción, medida inmediata y observación. La gravedad y las medidas proceden del registro profesional; el diseño no calcula severidad clínica ni propone intervenciones. Las tarjetas combinan icono, texto y estado; colores semánticos y foco no dependen solo del color.

Ambas requieren permiso específico, cuenta activa, exclusión de familiares, autorización general de lectura de residentes y Policy contextual al abrir un detalle. El rediseño no concede escritura clínica. La actualización de alertas existente requiere revisión de contexto y naturaleza de la alerta antes de exponerse como nueva acción administrativa; esa tarea queda pendiente.

## Reportes administrativos

Permite elegir periodo mediante calendario compartido, actualizar y comparar conteos autorizados de Preadmisiones, Admisiones, Visitas, Actividades, Alertas e Incidentes. Cada tarjeta abre el módulo correspondiente con su bandeja inicial; ese acceso no representa necesariamente una lista idéntica al conteo del reporte. No incluye nuevas exportaciones clínicas.

| Indicador | Fecha usada para el periodo |
| --- | --- |
| Preadmisiones | Solicitud |
| Admisiones | Admisión formal |
| Visitas | Entrada registrada; no incluye visitas solo programadas |
| Actividades | Fecha/hora de actividad |
| Alertas e incidentes | Fecha/hora del registro |
| Ocupación actual | Fotografía de ocupaciones activas sin liberación, con cama y habitación habilitadas; no se filtra por periodo |

Los conteos representan procesos distintos y no se suman como personas únicas. Solo aparecen indicadores cuyo permiso está autorizado. Un periodo inválido muestra error y conserva la consulta anterior. Las barras conservan valores textuales accesibles. Las tarjetas del reporte son navegación a los módulos, no acciones que alteren datos.

Los enlaces genéricos previos a PDF con información clínica no se publicitan en esta nueva pantalla; necesitan revisión específica de permisos y contenido antes de considerarse exportaciones administrativas seguras. El dashboard previo permanece conservado.

## Verificación y entrega

Comandos de verificación local (pruebas PHP con SQLite `:memory:`, nunca la base operativa):

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --filter 'ExploradorAdministrativoTest|DashboardAdministracionIntegracionVisualTest|EspaciosResidencialesTest|MapaHabitacionesTest|AlojamientoResidenteTest|ResidentesEspaciosTest|AdmisionesOperativasTest'
npm.cmd test
npm.cmd run build
git diff --check
```

Backend relacionado: **67 pruebas y 779 verificaciones aprobadas**, incluyendo las once ventanas, autorización, datos, filtros y las regresiones de Admisiones/Residentes/alojamiento. Frontend: **45 pruebas aprobadas**. Build: **91 módulos procesados correctamente**. Pint sobre los archivos PHP modificados y `git diff --check` terminaron correctamente. La primera ejecución frontend dentro del aislamiento no pudo arrancar Chromium; la misma suite local con acceso al ejecutable terminó aprobada. La prueba de recuperación de filtros conserva explícitamente la cookie entre solicitudes para reproducir el regreso real del navegador.

Navegador real: las once ventanas cargaron con el tema oscuro persistente y sin desbordamiento de página en escritorio. Jornadas se revisó también en tamaño móvil, calendario GET, selector corto sin buscador, cambio de vista, ficha/foco/cierre y rechazo de fechas invertidas. Reportes y Contactos se verificaron en móvil; la ficha de Contactos conserva el pie visible y desplaza su contenido internamente. Los gráficos se inicializaron plegados en esa carga móvil. La revisión con datos operativos fue de consulta; la evidencia guardada corresponde a una búsqueda vacía, sin identidades o información clínica.

La suite PHP global de 671 pruebas **no quedó aprobada**: mostró fallos adicionales y se interrumpió antes de completarse. Se aisló su primer fallo con `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --stop-on-failure`: `AdministracionMedicacionEnfermeriaTest.php:52` espera el texto `Alergia relevante` en un componente clínico que no se modifica en esta entrega (29 pruebas ejecutadas hasta ese fallo). No se atribuyen los demás fallos sin examinarlos ni se presenta la regresión relacionada aprobada como verificación global.

Persisten las tareas de gestión descritas en las guías 06/07, la revisión de esos fallos globales, exportaciones sensibles y concurrencia PostgreSQL de alojamiento. El escaneo del alcance nuevo no encontró dependencias V1, creación directa de residentes, debug ni reinicios de datos. No se crean commits por instrucción explícita del propietario.
