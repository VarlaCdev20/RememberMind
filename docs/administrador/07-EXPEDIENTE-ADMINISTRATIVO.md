# Expediente administrativo: consulta y vínculos

Entrega del 2026-10-06 en `REINICIO`, sin commit ni push. Comparte filtros, calendarios, gráficos, paginación, modales y comportamiento responsive descritos en [Coordinación diaria](06-COORDINACION-DIARIA.md).

## Ventanas y unidad de conteo

| Ventana | Vistas | Consulta disponible |
| --- | --- | --- |
| Contactos y responsables | Tarjetas, lista, tabla | Contacto único, vínculos activos, presencia de responsabilidad principal/emergencia en algún vínculo y teléfono |
| Documentación | Lista, tarjetas, tabla | Tipo, titular, vencimiento, validación y estado; gráfico de tipos y vencimientos por mes |
| Consentimientos | Cronología, tarjetas, tabla | Tipo, residente, firmante, fecha y estado; distribución por tipo y meses |
| Seguros | Tarjetas, lista, tabla | Entidad, residente, plan, afiliación, titular, cobertura y teléfono de la entidad |

Contactos cuenta personas de contacto, no filas de vínculos. La agregación evita duplicar un contacto cuando está relacionado con varios residentes. Responsable/emergencia en la bandeja indican que esa condición existe en algún vínculo activo; la ficha muestra el parentesco y condición de cada vínculo autorizado. Los gráficos comparan cantidad de vínculos activos y estados.

Los seguros se comparan por entidad y estado. No presentan una tendencia por fechas que el registro no proporciona. Los consentimientos muestran cronología de registros, sin convertir su estado en una autorización clínica nueva. Documentación ofrece Todos/Pendientes/Por vencer/Vencidos/Validados según estado, fecha de vencimiento y fecha de validación existentes; Por vencer usa la ventana operativa previa de treinta días.

## Ficha y acceso privado

La ficha contiene los campos de la bandeja y datos adicionales existentes. Contactos presenta hasta veinte vínculos activos, cargando los residentes en un lote y comprobando Policy por cada uno. El acceso a Residentes se ofrece solo cuando la navegación y Policy lo permiten. Los detalles con residente vuelven a autorizarlo aunque el identificador se escriba directamente en la URL.

Documentación ofrece Descargar archivo únicamente cuando el archivo existe en almacenamiento privado. Reutiliza `admin.documentos.descargar`, que vuelve a autorizar la descarga y mantiene la auditoría existente. Si falta el archivo, muestra un aviso; no ofrece una descarga ficticia ni publica ruta física o hash. Abrir esta ficha no valida el documento ni modifica el consentimiento.

## Seguridad e integración

Exige sesión del mismo usuario activo, permiso específico del módulo, exclusión de FAMILIAR y `ResidentePolicy::viewAny`; el detalle con residente usa también `view`. Las consultas no crean residentes, contactos, consentimiento ni seguro. Se conserva el flujo Preadmisión → revisión → admisión formal con cama → residente; ninguna ficha ofrece creación directa de residente.

La cuenta existente y los datos operativos permanecen intactos. Los ejemplos de pruebas son sintéticos y se crean exclusivamente en SQLite `:memory:`. No se modifica la estructura congelada de 71 tablas.

## Pendientes de gestión

Esta entrega acredita consulta, conteos y presentación. Quedan pendientes la revisión completa de altas/ediciones, validación documental, revocación y otros procesos sensibles. En particular, el endpoint previo de consentimiento necesita comprobar pertenencia del documento al mismo residente; la respuesta previa de relaciones de contacto debe separar permisos de visitas, consentimientos y ocupación. No se conectan estas rutas como nuevas acciones seguras en la interfaz.

Las pruebas cubren contacto sin duplicación, múltiples vínculos, Policy denegada, ausencia de permiso general de residente, archivo inexistente, privacidad de rutas/hashes y categorías numéricas. Los resultados y límites finales están en [Seguimiento y reportes](08-SEGUIMIENTO-Y-REPORTES.md).
