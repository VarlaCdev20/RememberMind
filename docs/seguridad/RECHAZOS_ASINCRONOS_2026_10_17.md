# Rechazos asíncronos de acceso — 17/10/2026

## Evidencia y alcance

La propietaria informa un 403 espontáneo mientras usa una sola cuenta. Esto amplía el caso anterior de redirección tras login: no se atribuye la recurrencia al cambio de cuenta entre pestañas.

Fuentes vigentes: [modelo de autorización](MODELO_AUTORIZACION.md), [excepción temporal de Superadmin](../arquitectura/PERMISOS_TEMPORALES_SUPERADMIN.md), `PreventWritesDuringRolePreview` y el runtime instalado de Livewire 4. El modo de previsualización sigue sin permitir POST de escritura. No se cambian permisos ni reglas de turno.

Se reprodujo una incompatibilidad concreta: dashboard, alertas y, en la rama, campana emitían sondeos automáticos mediante POST en previsualización. El middleware rechazaba esas peticiones con 403. La campana de la copia operativa ya tenía una corrección de otro trabajo y se conserva. Los tres consumidores quedan protegidos en `UNIFICACION`.

El runtime de Livewire abre por defecto una ventana con el HTML recibido ante un rechazo HTTP. Esto explica cómo una petición automática puede presentar el 403 completo sin navegación iniciada por la persona.

**Límite:** la incompatibilidad está reproducida, pero no demuestra por sí sola la causa de la sesión real reportada. No se ha capturado todavía un rechazo local de esa sesión con el nuevo diagnóstico. El navegador activo no pudo inspeccionarse porque su acceso está deshabilitado en Work.

## Cambio

- Los sondeos se omiten en previsualización. La actualización operativa de Enfermería continúa habilitada.
- Un interceptor usa `Livewire.interceptRequest` y `onError`, verificados en el runtime instalado. Sólo trata 403 si existe el componente de recuperación; otros fallos conservan su manejo original.
- Muestra un aviso persistente y un diálogo canónico. El HTML del error no se inserta en la pantalla ni se reemplaza el formulario. No reintenta, no informa éxito y no navega automáticamente.
- Los sondeos repetidos no apilan diálogos. Las nuevas acciones rechazadas mantienen feedback. Sin SweetAlert se usa aviso nativo y se conserva el aviso persistente.
- Volver al inicio requiere una elección explícita y advierte sobre datos sin guardar.
- La respuesta conserva el estado 403. Laravel registra sólo nombre de ruta, categoría del rechazo y booleano de cuenta activa. No se guardan payloads, valores clínicos, nombres, correo, tokens, identificadores de sesión ni mensajes internos de excepción. No hay tabla nueva ni servicio externo.

## Verificación

- Suite relacionada: 44 pruebas PHP, 214 aserciones; previsualización, login autorizado y turno fuera de jornada.
- Prueba adicional del bloqueo POST en previsualización: incluida en la ejecución focalizada final de 4 pruebas, 15 aserciones. Comprueba que no se ejecuta la acción ni se alteran roles.
- 6 pruebas JavaScript: conservación de pantalla, aviso persistente, ausencia de reintentos/navegación automática, diálogos sin apilar, salida explícita, fallback sin CDN y carga antes/después de Livewire.
- Copia operativa: mismas 4 pruebas PHP y 6 JavaScript correctas. Build correcto en ambas copias.
- No se certifica QA visual del navegador activo ni la desaparición del caso real sin nueva observación. PostgreSQL no se ejecutó; no hay cambios de persistencia clínica.

## Diagnóstico de una recurrencia

Buscar `Solicitud Livewire rechazada` en el log de Laravel del entorno local. Las categorías distinguen previsualización, cuenta inactiva, permiso/rol y restricción contextual. Conservar la categoría y hora de la recurrencia para localizar el control responsable; no copiar datos clínicos ni ampliar permisos para ocultar el rechazo.

## Continuación: endpoint HTTP completo

Se obtiene el snapshot firmado del dashboard mediante GET autenticado y se envía al endpoint instalado de Livewire por POST, usando el snapshot devuelto en cada refresco. Tres refrescos consecutivos de una cuenta de Enfermería activa y autorizada responden 200 sin crear signos ni administraciones. Una cuenta desactivada responde 403.

La prueba de retirada de `enfermeria.ver_dashboard` reprodujo una brecha adicional: el refresco devolvía 200 aunque el permiso ya no existía. El middleware de permisos de la ruta inicial no cubría ese recorrido de actualización. `DashboardTurno` ahora comprueba cuenta activa y el permiso vigente antes de cargar el turno y antes de renderizar. Reutiliza Gate y el permiso de la ruta; no cambia roles, grants, ámbitos de residentes ni reglas de jornada.

Esta brecha corregida no se presenta como causa del rechazo espontáneo comunicado: permitía una lectura indebida. El origen exacto del caso real continúa pendiente de evidencia local; a la fecha de esta comprobación no hay entradas locales nuevas de rechazo con el diagnóstico añadido.

Verificación adicional en la copia operativa: 7 pruebas PHP, 39 aserciones, correctas, incluidas las tres pruebas del endpoint HTTP. Las 6 pruebas JavaScript del aviso siguen correctas. No hay cambios de frontend en esta continuación; se conserva el build verificado de la entrega anterior. No se inspeccionó el navegador activo ni se ejecutó PostgreSQL.

Suite relacionada final en `UNIFICACION`: **48 pruebas PHP, 242 aserciones**, correctas (`RechazoLivewireTest`, `RolePreviewTest`, `RedireccionLoginAutorizadaTest` y `MiTurnoFueraDeJornadaTest`).
