# Incidentes: consulta y seguimiento

## Alcance y flujo

Enfermería entra desde su navegación, revisa incidentes de sus residentes asignados, busca o filtra, abre el detalle y continúa con las acciones existentes cuando su contexto permite escribir. Fuera de turno conserva consulta propia; las mutaciones siguen protegidas por el servicio contextual. No se modifican permisos, responsabilidades, estados, reglas clínicas ni estructura de la BDD.

Antes: filtros estrechos, tabla con desplazamiento horizontal, apellidos V1 vacíos, menú recortable y dos vistas consultando los mismos estados. Los chips llamaban a un método inexistente; el detalle aceptaba códigos de incidentes sin aplicar el alcance del listado.

Ahora:

- Activos: `ABIERTO`, `EN_SEGUIMIENTO`. Historial: `CERRADO`, `ANULADO`, cronológico descendente. Los contadores representan todo el alcance autorizado; resultados representa la consulta filtrada.
- Búsqueda por palabras combinadas en nombre, apellidos, tipo, descripción y lugar. Tipos frecuentes más tipos ya registrados dentro del mismo alcance; no se altera el catálogo de captura ni «Otro».
- Estado contextual a la vista; tipo, gravedad y fechas en filtros adicionales. Quitar un filtro conserva los demás. Cambio de vista limpia estado; cambios de filtro reinician página. Intervalos invertidos o fechas inválidas tienen error visible y no se ejecutan como una consulta válida.
- Registros con residente/ubicación/fecha, evento/medida y gravedad/estado/detalle. Tres columnas amplias; dos en tablet; una en móvil. Identidad completa; narrativa resumida con acceso al texto completo en detalle.
- Acciones mediante `details/summary`, sin dropdown recortado. Crítica activa conserva aviso persistente según la gravedad registrada; no hay inferencia en frontend.
- Detalle con el modal compartido, contexto, narrativa, requisitos, observaciones y contactos. Nombres usan `apellido_paterno`, `apellido_materno` y `nombre_completo`.
- Lectura, conteos, tipos y detalle usan el mismo alcance. Apertura de estado y derivación exige autorización contextual antes de cargar el formulario. Los errores inesperados no exponen mensajes de excepciones al usuario.

## Componentes e integración

Se reutilizan `collection-header`, `filter-bar`, `action-button`, `status-badge`, `empty-state` y `modal-livewire`. Se compone un patrón `incidents-workspace.css`, importado después de los overrides existentes; usa tokens de superficie, fuente, profundidad, acción y estado. Las dos parciales pertenecen a Alertas y se registran en el mapa modular. No se introduce otra paleta, biblioteca o sistema de controles.

## Verificación

- `php artisan test --filter=IncidentesEnfermeria`: **23 PASS, 105 aserciones**. Registro, validación, autoría, alerta/derivación, estado trazable, atomicidad, filtros combinados, rango inválido, paginación, separación de vistas, cuenta inactiva, lectura fuera de turno y códigos ajenos rechazados.
- `node --test tests/Frontend/shell-visual-consistency.test.js`: **4 PASS**.
- `npm run build`: **PASS**; advertencia preexistente de chunk JS superior a 500 kB.
- Exportación de Blade real con datos sintéticos sobre SQLite `:memory:`: PASS. Listado y detalle revisados en 1440, 1280, 1024, 768 y 390 CSS px. Sin overflow horizontal; controles del listado y cierre de detalle con área mínima de 44 px.
- Historial, sin coincidencias, fechas inválidas y filtros desplegados revisados. Acciones abren con Enter y foco visible. En la pantalla conectada: cambio de vista, apertura/cierre del detalle, foco dentro del diálogo, sin escritura de registros.
- Contraste muestreado sobre fondos reales de claro: texto de estado mínimo **5.45:1**, aviso crítico **6.14:1**, cuerpo **7.79:1**. No es certificación global de accesibilidad.
- Se preservan los tokens de movimiento y la rama de movimiento reducido. La comprobación visual de esta tarea se realizó en claro; no se afirma comprobación del tema oscuro ni simulación de movimiento reducido en el navegador conectado.

Evidencia sintética: `storage/app/qa/incidentes/normal-{ancho}.jpg`, `detail-{ancho}.jpg`, `filters-390.jpg`, `history-390.jpg`, `empty-390.jpg` y `error-390.jpg`. El fixture es una exportación estática; la lógica Livewire se verifica en pruebas y el modal se ejercitó en la pantalla conectada. No sustituye validación clínica ni integración PostgreSQL.

## Límites

Esta entrega mejora consulta, filtros y detalle; no es una auditoría integral de todos los servicios de registro ni un rediseño de los formularios de registro, cambio de estado o derivación. Conserva sus contratos y acciones existentes. No se agregan dependencias V1. Commit local limitado a esta tarea; sin push y sin incluir los cambios previos de otros módulos.
