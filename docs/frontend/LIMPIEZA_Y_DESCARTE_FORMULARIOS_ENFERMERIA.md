# Limpieza y descarte de captura — Enfermería

## Contrato vigente · 2026-10-09

Decisión expresa de la propietaria: **pedir confirmación para descartar, sin justificativo**.

- Cancelar, cerrar o volver con cambios sin registrar pide confirmación; «Seguir editando» conserva la captura.
- «Salir sin guardar» descarta únicamente la captura pendiente. No elimina ni modifica registros guardados.
- «Limpiar campos» solicita confirmación y mantiene abierto el formulario, con el mismo residente y contexto profesional. En una corrección devuelve los campos a los valores iniciales de esa captura.
- Signos Vitales permite limpiar o descartar una lectura crítica no persistida tras confirmación. Sustituye el anterior bloqueo de descarte/limpieza por criticidad. La clasificación y la confirmación necesaria para **registrar** una lectura crítica permanecen vigentes.
- Ni limpieza ni descarte generan una medición, alerta o éxito de registro.
- La navegación/recarga del navegador con captura visible modificada conserva la advertencia nativa; su texto lo controla el navegador.

## Consumidores

Selector de Mis residentes: Signos Vitales, Dolor, Ingesta, Eliminación, Movilidad, Seguimiento, Procedimiento y Administración programada. Esta última conserva la prescripción, ocurrencia y momento de administración seleccionados.

Componente compartido `ui.clinical-clear-action` y controlador `rmClinicalCapture`: cuidados y correcciones, dispositivos, incidentes y seguimiento/cierre de incidentes en Registros; hidratación; curaciones/cierre de heridas; seguimiento diario.

Los permisos de registro, Policies, contexto de jornada, validación y persistencia siguen en los servicios existentes. No hay cambios de esquema ni justificación obligatoria nueva.

## Evidencia

Pruebas de limpieza/descarte del selector verifican conservación de contexto, cancelación de confirmación, permiso revocado, llamada fuera del formulario y ausencia de signos/alertas nuevas. Pruebas JS verifican restauración de campos editables, contexto protegido y advertencia de navegación con datos pendientes.

QA en PostgreSQL de testing con residentes sintéticos: Dolor (cancelar limpieza/conservar, limpiar y descartar), Signos Vitales (pulso 135 sin guardar, seguir editando y limpiar), Hidratación (limpiar volumen y tipo, conservar residente y descartar nueva captura). Capturas privadas: `storage/app/qa/formularios/`.

Verificación del lote: `php artisan test` sobre `MisPacientesRedisenadaTest`, `SeguimientoDiarioPersistenciaTest`, `SignosVitalesPanelClasificacionTest` y `Fase3SignosVitalesTest`: **140 PASS / 1209 assertions**. `npm test`: **108 PASS**, incluyendo campos condicionales/remontados. `npm run build`: **PASS**. No se presenta este resultado como ejecución de toda la suite PHP ni como QA visual de cada formulario alternativo.
