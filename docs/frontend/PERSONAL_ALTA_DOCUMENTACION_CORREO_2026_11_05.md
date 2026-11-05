# Alta de personal: documentación y acceso — 05/11/2026

Decisiones de la propietaria en este chat: Gerencia elige la fecha límite al registrar. Se conserva el mínimo inmediato de identidad, fotografía y CV, más título según profesión y matrícula documental para Medicina. Los demás documentos admitidos por el catálogo pueden solicitarse con plazo; la ausencia de un opcional no constituye una obligación vencida. La fecha de ingreso es el día de confirmación del registro, asignado por servidor.

## Implementación

- Información laboral conserva rol, fecha de ingreso automática y observaciones. El número de matrícula pasa a Documentación; no se elimina su columna ni se exige por duplicado en el paso laboral.
- El backend ignora fechas de ingreso manipuladas en el alta y deriva el día del timestamp de creación de la cuenta. La edición conserva la fecha original, sin sustituir fechas históricas desconocidas por hoy.
- Las solicitudes seleccionadas se conservan mediante las relaciones documentales existentes, dentro de la misma transacción del alta. Una solicitud posterior de origen ALTA reutiliza el plazo original del expediente; no divide sus plazos ni reinicia su única prórroga de siete días. El valor inicial sugerido de siete días es editable por Gerencia. Los consumidores antiguos sin fecha explícita conservan el valor anterior.
- El mínimo inmediato se valida también en backend. No se permite aplazar archivos mínimos, desconocidos, ya adjuntados o emitir estos plazos desde una edición.
- La dirección ofrece búsqueda en Google Maps al pulsarla; no consulta ni transmite direcciones automáticamente. Referencia técnica: https://developers.google.com/maps/documentation/urls/get-started .
- Vista previa local de PDF e imágenes permitidas: URL blob temporal, MIME canónico validado en servidor, revocación al cerrar/cambiar archivo/cambiar rol/desmontar. No se crea una ruta pública para archivos privados.
- El correo se dirige a la cuenta registrada, conserva contraseña temporal e incorpora el enlace estándar para crear la contraseña personal. El token usa el broker existente, caduca y queda inutilizable tras el cambio. Se conserva el control obligatorio de contraseña inicial. El fallo de transporte se distingue del alta ya confirmada.
- Remitente protegido sin cambios: remembermind.health@gmail.com. Sin nuevas tablas, columnas ni permisos. Sin nuevos servicios o dependencias.
- Selectores en mayúsculas visuales, documentación agrupada y resumen con plazo y fecha automática; se conserva Nunito y la paleta cálida aprobada.

## Verificación

Suite relacionada de 95 pruebas: alta, rollback de SQL/archivos, autorización, mínimo documental, plazo, fechas originales, correo y cambio real de contraseña mediante HTTP. SQLite y PostgreSQL desechable: 95 aprobadas y 2381 aserciones en cada motor. Las pruebas usan datos sintéticos y Mail::fake; no acreditan entrega SMTP real.

Pruebas JavaScript de normalización y ciclo de vida de las URL locales; recorrido automatizado de los cuatro pasos en 1440, 1280, 1024, 768 y 390 px, incluido plazo, imágenes, enlace Maps y ausencia de errores JavaScript. Build de Vite ejecutado. Evidencia local en output/personal-alta-documental-*20261105* y output/auditoria-integral-20261025/gerencia-renovada/personal-unificado/.

La captura del visor PDF en Chromium automatizado permanece oscura: se comprobó su fuente blob, pero no se acredita la representación visual de su contenido. Pendiente comprobar el PDF en el navegador interactivo; el uso del navegador está desactivado en este entorno. No se realizaron envíos SMTP reales ni CI completa. El resto de cambios concurrentes del repositorio queda fuera de este commit.
