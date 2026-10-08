# Admisiones: funcionamiento, mejoras y verificación

Entrega del 2026-10-06 en `REINICIO`: bandeja rediseñada, controles compartidos y refuerzos del ingreso formal. Estructura de datos sin cambios.

## Qué hace el administrador

Ruta `/admin/administracion/admisiones`. Consultar solicitudes listas para ingreso, admisiones activas e historial. Los indicadores son generales; los resultados responden a filtros. Los valores provienen de registros existentes.

| Etapa | Criterio |
| --- | --- |
| Por formalizar | Preadmisión `APROBADA` sin admisión |
| Admitidos | Admisión `ACTIVA` |
| Historial | Admisión con estado diferente de `ACTIVA` |

Seleccionar un indicador cambia la etapa y conserva búsqueda, orden, tamaño y vista. El enlace de camas lleva al alojamiento habilitado. Tabla, lista y tarjetas consultan los mismos registros y mantienen su contexto.

La búsqueda por nombre, documento, código o habitación se aplica después de 500 ms sin escribir. También se puede pulsar Buscar. Orden reciente/antiguo y páginas de 10, 20 o 50 registros se validan en backend. Cambiar filtros reinicia la página. No hay polling ni consultas por cada fila; el resumen rápido usa los datos que ya se presentaron.

Nombre o icono de identificación abre un resumen breve con fecha, etapa, ubicación y estado. El modal conserva foco, permite Escape y vuelve al control que lo abrió. El acceso principal abre el residente o **Preparar ingreso**, que lleva directamente al expediente aprobado mediante `solicitud`; no necesita volver a buscarlo.

## Flujo institucional y seguridad

Preadmisión `PENDIENTE`, revisión, `APROBADA`/`RECHAZADA`, admisión formal con cama y residente `ADMITIDO`. Aprobar no crea residente. No existe alta directa alternativa.

La cuenta debe estar activa y tener `admisiones.formalizar`; la Acción y la interacción Livewire verifican la autorización. Si el modelo tiene una Policy registrada, se evalúa su operación contextual `formalizar`, incluido el registro recargado bajo bloqueo. No se otorgaron permisos ni roles y no se agregaron catálogos.

`FormalizarAdmision` coordina residente, admisión, ocupación, contacto responsable, historial y consentimiento en una transacción. La solicitud debe seguir aprobada y no tener admisión. Se bloquean solicitud, cama y habitación; una ocupación activa impide reutilizar la cama. La habilitación y disponibilidad se vuelven a consultar al confirmar, aunque el formulario estuviera abierto antes.

La misma consulta de disponibilidad sirve al contador y al selector. Se consumen los valores operativos existentes `ACTIVA`, `ACTIVO`, `DISPONIBLE` para habilitación y `ACTIVA`, `ACTIVO` para ocupación; no se modifican estados persistidos por el rediseño. Una habitación inhabilitada no ofrece camas. Historial conserva la última asignación de cama de cada admisión y evita duplicar filas por traslados.

Los errores generales del formulario se anuncian con `role=alert` y reciben foco. La validación local conserva sus mensajes; ninguna visualización inventa gravedad clínica.

## Diseño y componentes

- Cabecera contextual, indicadores con etapa activa y acceso a alojamiento.
- Filtros en una fila adaptable, carga visible y tres vistas con los mismos datos.
- Sombras y elevación leve al acercarse; transiciones breves y movimiento reducido respetado.
- [Paginación institucional](02-COMPONENTES-COMPARTIDOS.md): altura normal de 68–70 px en escritorio y controles de 44 px. Se reutiliza en preadmisiones y los paginadores Laravel/Livewire generales.
- Menú lateral con columnas independientes para icono, etiqueta, contador y caret. En compacto, contador debajo del icono, cifras grandes 99+ y total accesible completo.

Se conserva el Design System y la preferencia de tema. Tablas anchas se desplazan dentro de su contenedor; las tarjetas se distribuyen en una columna en móvil.

## Archivos y lectura selectiva

- [Controlador](../../app/Http/Controllers/Administracion/OperacionController.php): filtros, consulta y conexión de la nueva vista.
- [Servicio de consultas](../../app/Backend/Modulos/Administracion/Servicios/ConsultaOperativaService.php): etapas, agregados y última ocupación.
- [Bandeja](../../resources/views/pages/admin/administracion/admisiones.blade.php) y [registro](../../resources/views/pages/admin/administracion/partials/admision-registro.blade.php).
- [Interacción](../../resources/frontend/scripts/modules/admisiones-interactivas.js) y [estilo](../../resources/frontend/styles/design-system/patterns/admisiones-interactivas.css).
- [Formalización](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php) y [Livewire](../../app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php).
- [Baseline de datos](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [extensión V2.2 aprobada](../base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md), [responsabilidades](../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md).

Inventario vigente de 71 tablas; sin migraciones, seeders ni registros operativos creados en esta entrega. Se sustituyó el uso relacionado de entidad antigua por `residente`; la limpieza global del legado pertenece a otra etapa.

## Validaciones ejecutadas

En PowerShell, desde `C:\laragon\www\RememberMind`:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit --filter 'CasosPreadmisionTest|AdmisionesOperativasTest|BddOperativaV2Test|OcupacionCamaIntegrityTest|DashboardAdministracionIntegracionVisualTest|PaginacionInstitucionalTest'
node --test --test-concurrency=1 "tests/Frontend/**/*.test.js"
npm.cmd run build
```

Resultado: **54 pruebas PHP, 590 aserciones; 38 pruebas frontend; build correcto**. Las pruebas PHP usan SQLite de pruebas. Se cubrieron autorización negativa, cuenta inactiva, Policy, persistencia válida, invariantes de residente/ocupación, infraestructura inhabilitada, filtros, orden estable, historial y paginación compartida.

El menú se comprobó en 28 combinaciones de viewport, tema y variante. En navegador real se verificaron tabla/lista/tarjetas, resumen y foco, búsqueda y limpieza, orden, tamaño de página, página siguiente, ambos temas, menú expandido/compacto y pantallas estrechas. No se confirmó una admisión operativa durante la revisión visual.

## Límites y siguiente etapa

Falta probar concurrencia/bloqueos en PostgreSQL de integración. SQLite comprueba reglas y rollback, pero no acredita el comportamiento concurrente de PostgreSQL. La revisión visual del formulario completo con datos nuevos debe usar un entorno aislado y datos sintéticos; no se fabricaron solicitudes en la base operativa para habilitarlo.

Continuar gradualmente con el formulario de ingreso y sus dependencias documentales, y después alojamiento. Esta entrega no declara terminado todo el rol administrador.