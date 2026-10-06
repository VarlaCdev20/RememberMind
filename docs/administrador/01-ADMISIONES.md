# Admisiones: funcionamiento y revisión inicial

Estado al 2026-10-06: **inspección inicial, rediseño pendiente**.

## Pantalla y finalidad

Ruta: `/admin/administracion/admisiones`. Es una bandeja administrativa de seguimiento. Ofrece vista, búsqueda, limpieza de filtros y paginación; relaciona el proceso con habitaciones, preadmisiones y expediente del residente.

| Vista | Criterio actual |
| --- | --- |
| Por formalizar | Preadmisión `APROBADA` sin admisión |
| Admitidos | Admisión `ACTIVA` |
| Historial | Admisión con estado diferente de `ACTIVA` |

Los indicadores muestran cantidades generales, no solo los resultados de la búsqueda. Son datos de seguimiento; no inventan severidad clínica. La existencia de datos locales no justifica crear solicitudes para completar la pantalla.

## Proceso institucional

La solicitud comienza `PENDIENTE`. Tras revisión queda `APROBADA` o `RECHAZADA`. Una aprobación no crea residente. La admisión formal requiere una solicitud aprobada, contacto responsable y cama disponible, además de los datos y validaciones vigentes del formulario.

La acción `FormalizarAdmision` coordina residente, admisión, ocupación, contacto, historial y consentimiento dentro de una transacción con bloqueos. El residente queda `ADMITIDO`; admisión y ocupación quedan `ACTIVA`. No debe existir alta directa que evite este proceso ni dos ocupaciones activas para un residente.

La bandeja enlaza «Continuar» al panel de preadmisiones, filtrando por solicitud aprobada. La formalización ocurre allí; esta bandeja no ejecuta por sí misma la admisión.

## Fuentes de implementación

- [Rutas](../../routes/web.php).
- [Controlador de operación](../../app/Http/Controllers/Administracion/OperacionController.php).
- [Consultas y definición de módulos](../../app/Backend/Modulos/Administracion/Servicios/ConsultaOperativaService.php).
- [Vista administrativa](../../resources/views/pages/admin/administracion/operacion.blade.php).
- [Interacción de preadmisiones y formulario de admisión](../../app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php).
- [Acción de admisión formal](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php).
- [Navegación por actor](../../app/Backend/Modulos/Identidad/Servicios/SidebarService.php).

Estos archivos prueban implementación actual, no sustituyen reglas institucionales y baseline de datos.

## Próximas mejoras acotadas

1. **Bandeja y continuidad:** mejorar jerarquía, distribución, filtros y acciones con componentes compartidos. Evaluar que «Continuar» abra directamente el expediente correcto mediante el parámetro `solicitud`, ya disponible. Mantener contexto y ambos temas.
2. **Autorización:** verificar que las mutaciones Livewire exijan el permiso efectivo de formalización y contexto aplicable. La inspección encontró comprobación por roles en `abrirAdmision`/`formalizarAdmision`; el endpoint HTTP sí usa `admisiones.formalizar`. Confirmar y cubrir el caso negativo antes de dar por terminado el flujo.
3. **Disponibilidad y errores:** contrastar el resumen que acepta `ACTIVA`/`ACTIVO`/`DISPONIBLE` con la acción que exige cama `ACTIVA`. Revisar habitación y ocupación según fuentes vigentes. Añadir anuncio accesible y foco a errores generales, sin perder datos del formulario.

Los puntos 2 y 3 son hallazgos de inspección estática. No se comprobaron mediante escrituras operativas ni se cambiaron permisos, catálogos o estructura.

## Evidencia y estado de validación

- Rama revisada: `REINICIO`; checkout limpio al iniciar esta etapa.
- Se abrió la ruta con la sesión administrativa existente y se comprobó «Por formalizar», incluida su respuesta vacía. No se crearon cuentas, residentes ni seeders.
- Dos subagentes revisaron navegación/alcance y flujo/código, sin modificaciones.
- Esta entrega agrega documentación y abre la pantalla. No implementa todavía el rediseño ni corrige los hallazgos anteriores.
- Los resultados previos de preadmisiones están en [su guía](../frontend/PREADMISIONES_INTERACTIVAS.md); deben repetirse cuando una nueva implementación lo requiera.

## Verificación de una futura entrega

Cubrir apertura autorizada y denegada, filtros y estados, continuidad de solicitud, rechazo de mutación sin permiso, aprobación sin residente, admisión válida y rollback, cama ocupada e infraestructura no habilitada conforme a reglas vigentes. Usar datos sintéticos en pruebas y revisar bloqueo/concurrencia con PostgreSQL de integración.

En navegador, comprobar lista vacía/con datos, foco y teclado, anuncio de errores, retención de datos, temas claro/oscuro y distribución móvil. No confirmar ingresos reales como parte de QA visual.
