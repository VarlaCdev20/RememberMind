# Conexión del correo saliente

## Última comprobación del aviso en login — 26/10/2026

Ante una nueva captura de «El correo del sistema aún no está disponible», se
revisó la copia de `Desktop/RememberMind` que atiende el puerto 8000. El estado
observado en este corte difiere de las comprobaciones SMTP históricas siguientes:
`MAIL_PASSWORD` contiene el literal `null` y la cuenta SMTP configurada no
correspondía al remitente institucional protegido.

Se alinearon únicamente `MAIL_USERNAME` y `MAIL_FROM_ADDRESS` en el `.env` local
con `remembermind.health@gmail.com`, preservando la contraseña y las demás
variables. No se copian credenciales de cuentas personales ni se deshabilita el
guard. Se limpió la caché de configuración con `php artisan config:clear`.

`php work/verificar-smtp-local.php` devuelve **CREDENCIALES_INCOMPLETAS**; no
abre una conexión SMTP ni envía mensajes mientras falta la clave. Hace falta
configurar localmente la contraseña de aplicación de la cuenta institucional
en `MAIL_PASSWORD`. Las confirmaciones anteriores acreditan sus propios cortes,
no la disponibilidad del servicio en este estado posterior del archivo local.

Regresión sin red: `php artisan test --filter='RemitenteInstitucionalProtegidoTest|CorreoNoConfiguradoRespuestaTest' --compact`
→ **11 pruebas, 36 aserciones**, satisfactorias. Sin modificaciones de esquema,
cuentas de usuarios, permisos o destinatarios; sin commit ni push. No se afirma
conexión restablecida hasta disponer de la credencial y autenticar con TLS.

**Verificación posterior tras «listo» de la propietaria, 26/10/2026:** después
de guardar localmente la contraseña y limpiar la caché de configuración, el
comprobador terminó con código 0: `SMTP_AUTENTICADO_TLS_OK` y
`REMITENTE_COINCIDE_CON_CUENTA`. Gmail aceptó la autenticación TLS de la cuenta
institucional; la conexión se cerró sin enviar mensajes. La credencial ya no
está pendiente en este corte. Esta comprobación confirma conexión y autoría
del remitente, sin afirmar entrega de un correo de recuperación al destinatario.

## Remitente definitivo protegido — 26 de octubre de 2026

La propietaria fijó expresamente `remembermind.health@gmail.com` como única
dirección de origen y prohibió sustituirla sin nueva autorización. Esta decisión
sustituye las cuentas personales usadas en configuraciones anteriores.

`ExigirTransporteCorreoReal::REMITENTE_INSTITUCIONAL` define la dirección única;
`config/mail.php` la consume directamente y ya no permite reemplazarla con
`MAIL_FROM_ADDRESS`. Antes del envío se rechazan cambios de configuración,
cabeceras From/Sender/Return-Path diferentes, otra cuenta SMTP de Gmail y URL SMTP
alternativa que pueda sustituir la autenticación. Las pruebas usan transportes
sin red y destinatarios sintéticos; no desactivan la protección del remitente.
La contraseña de aplicación sigue en `.env`, permitiendo su rotación sin cambiar
la dirección institucional. La instrucción también consta en `AGENTS.md`.

La nueva lectura local confirmó cuenta y remitente correctos, clave presente y
caché de configuración inactiva. `php work/verificar-smtp-local.php` obtuvo
`SMTP_AUTENTICADO_TLS_OK` y `REMITENTE_COINCIDE_CON_CUENTA`. Esta verificación
autentica y cierra la conexión sin enviar mensajes. No acredita recepción.

Regresión: 52 pruebas correctas, 268 aserciones, incluyendo la protección contra
cambios, recuperación de contraseña y seguimiento documental. Sin cambios de
BDD, permisos, destinatarios, dependencias ni exposición de claves.

## Corrección del aviso de configuración — 26 de octubre de 2026

La comprobación local confirmó usuario SMTP presente y contraseña de aplicación
ausente, sin caché de configuración activa. No se leyó ni imprimió ninguna clave.
`CorreoNoConfigurado` conserva el fallo para registro técnico y devuelve un aviso
seguro: JSON 503 o retorno al formulario, sin mostrar el stack incluso con debug.
La recuperación valida la configuración antes de consultar el broker, de modo
que una cuenta existente y una inexistente reciben la misma respuesta y no se
generan tokens ante esta limitación. El formulario de login conserva el panel
de recuperación y muestra el motivo real en vez de atribuirlo al formato del correo.

Regresión: `CorreoNoConfiguradoRespuestaTest`, `TransporteCorreoRealTest`,
`RecuperacionContrasenaFlujoTest` y `PasswordResetTest`. La entrega real sigue
pendiente de la contraseña de aplicación local y una comprobación autorizada;
esta corrección no activa envíos ni sustituye credenciales.

Verificación de esta corrección: esas cuatro clases y
`SeguimientoCorreoDocumentalTest` completaron 43 pruebas y 244 aserciones.
Build Vite correcto, con la advertencia existente de tamaño del bundle. Sin
prueba SMTP real ni commit de este ajuste sobre el árbol concurrente.

## Comprobación del 25 de octubre de 2026

La propietaria confirmó el alcance: envío desde RememberMind para recuperación
de contraseña y notificaciones. SMTP Gmail está configurado con puerto 587,
usuario y remitente presentes; `MAIL_PASSWORD` permanece vacío. La conexión TCP
al servidor respondió, lo cual no demuestra autenticación ni entrega.

Se ejecutaron `php artisan test --filter=TransporteCorreoRealTest` (10 pruebas,
16 aserciones) y `php artisan test --filter=PasswordResetTest` (4 pruebas,
6 aserciones), ambas satisfactorias. La recuperación usa `usuarios.correo`.
No se enviaron mensajes ni se modificaron cuentas para comprobar el transporte.
Falta introducir localmente la contraseña de aplicación, verificar autenticación
y realizar un envío técnico al destinatario que autorice la propietaria.

### Comprobación posterior a introducir la clave

Se normalizó únicamente `MAIL_PASSWORD` en el archivo local: se quitaron los
espacios de agrupación y se encerró el valor entre comillas para corregir el
error de lectura dotenv. No se guarda la clave en esta documentación ni en Git.
Tras limpiar la configuración, Gmail rechazó la autenticación con código 535.
El envío real sigue pendiente: comprobar que `MAIL_USERNAME` sea la misma cuenta
que generó la contraseña de aplicación y, si es necesario, generar una nueva.
No se enviaron mensajes. El comprobador local `work/verificar-smtp-local.php`
exige TLS y comunica únicamente estados, sin imprimir credenciales.

### Autenticación confirmada

En la comprobación posterior, después de guardar la configuración y ejecutar
`php artisan config:clear`, `php work/verificar-smtp-local.php` finalizó con
`SMTP_AUTENTICADO_TLS_OK` y `REMITENTE_COINCIDE_CON_CUENTA`. Gmail aceptó la
autenticación mediante TLS y el remitente coincide con la cuenta autenticada.
Esta comprobación no envía mensajes; la recepción de un correo real permanece
pendiente de un envío de prueba a un destinatario autorizado.

### Envío técnico autorizado

La propietaria indicó el destinatario de prueba en la conversación. Se ejecutó
un único envío síncrono mediante Laravel Mail con asunto «RememberMind — Prueba
de conexión del correo», sin datos clínicos, enlaces ni credenciales. Gmail
aceptó el mensaje por SMTP usando TLS (`MENSAJE_ACEPTADO_POR_SMTP`). No se
ejecutaron reenvíos ni se procesaron trabajos pendientes de la cola. Falta que
la destinataria confirme su recepción; aceptación SMTP no demuestra llegada a
la bandeja de entrada. El destinatario no se incorpora a esta documentación.

### Recepción confirmada por la propietaria

La propietaria confirmó la recepción del mensaje de prueba en esta conversación
(«genial ya esta»). Quedan comprobados la autenticación SMTP con TLS, la
aceptación del mensaje por Gmail y la recepción del envío técnico autorizado.
Esta prueba valida el transporte saliente; no acredita individualmente todas
las notificaciones ni el procesamiento de trabajos en segundo plano.

## Diagnóstico del 23 de octubre de 2026

El entorno local utilizaba `MAIL_MAILER=log`, sin credenciales SMTP ni remitente
real. Ese transporte guarda el contenido en el registro local y no entrega
mensajes. No había trabajos pendientes ni fallidos en la cola durante la revisión.

## Configuración local de Gmail

Guardar únicamente en `.env`, fuera de Git:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=remembermind.health@gmail.com
MAIL_PASSWORD="contraseña-de-aplicación"
MAIL_FROM_ADDRESS=remembermind.health@gmail.com
MAIL_FROM_NAME="RememberMind"
```

La cuenta remitente debe estar autorizada por la propietaria. Google exige
verificación en dos pasos para crear una contraseña de aplicación. Introducir
esa clave directamente en el archivo local; no compartirla en chats ni commits.
Referencias: [Google: contraseñas de aplicación](https://support.google.com/accounts/answer/185833?hl=es)
y [Gmail: servidor SMTP y TLS](https://developers.google.com/workspace/gmail/imap/imap-smtp).

Después de cambiar la configuración, limpiar la caché si se utiliza
(`php artisan config:clear`) y reiniciar el trabajador de cola para que lea las
nuevas variables. `composer dev` incluye el trabajador; si se inicia únicamente
el servidor web, se necesita también `php artisan queue:work`.

## Confirmaciones y comprobación

- Se rechazan `log` y las cadenas de respaldo que terminan en `log` antes de
  entregarles el mensaje. `array` se admite exclusivamente en pruebas.
- Gmail sin usuario o contraseña de aplicación produce un error de configuración.
- Registrar una solicitud en cola no acredita envío ni recepción. Los paquetes
  documentales muestran «Solicitud registrada» y su auditoría registra la solicitud.
- Los fallos del envío de ficha no deben exponer excepciones al navegador.
- Una conexión TCP o una prueba con `Mail::fake()` no acreditan recepción.
- La aceptación final requiere enviar un mensaje técnico sin datos personales
  al destinatario autorizado y confirmar su recepción. No reenviar masivamente
  avisos previos ni restablecer contraseñas para probar SMTP.

## Evidencia de esta corrección

Pruebas focalizadas: `TransporteCorreoRealTest` y `ContratoPersonalInstitucionalTest`.
Resultado: 20 pruebas correctas, 72 aserciones. Compilación Vite correcta;
permanece la advertencia de tamaño del bundle existente.
No hay cambios de esquema ni de permisos. La recepción real queda pendiente hasta
guardar la contraseña de aplicación y ejecutar la comprobación con la propietaria.

## Seguimiento y reintentos documentales — 25/10/2026

La mejora autorizada se integra en los botones existentes «Enviar ficha» y
«Enviar paquete documental» de Usuarios. La recuperación de contraseña conserva
su circuito y sus cuatro pruebas; esta entrega no encola tokens ni contraseñas.

### Recorrido y significado de los estados

1. El backend reautoriza la cuenta activa y sus permisos existentes. La solicitud
   recibe una referencia interna y queda pendiente en la cola `correo-documental`.
2. El trabajador recupera los modelos desde SQL, reautoriza al solicitante y
   comprueba que el correo del destinatario no haya cambiado. Prepara el PDF en
   memoria y vuelve a comprobar permisos/destinatario antes de transmitirlo.
3. El estado aparece en un modal reutilizando SweetAlert2. Consulta el backend
   sin caché cada dos segundos, durante un máximo de quince consultas; al cerrar
   detiene las consultas y permite comprobar el estado de nuevo.

| Estado visible | Qué acredita |
|---|---|
| Pendiente | Solicitud registrada en la cola, todavía sin envío |
| En proceso | Preparación del adjunto o comunicación SMTP |
| Reintento programado | Rechazo SMTP temporal explícito (4xx); otro intento en 30 y luego 120 segundos |
| Aceptado por el servidor | El transporte confirmó aceptación; no acredita recepción o lectura |
| Falló el envío | Fallo de preparación, autorización, destinatario o rechazo SMTP permanente; máximo tres intentos para rechazos temporales |
| Envío sin confirmar | Interrupción durante SMTP o ausencia de confirmación; no se reenvía automáticamente |

Las solicitudes repetidas del mismo actor, usuario y tipo recuperan la referencia
pendiente. Tras aceptación o resultado indeterminado se aplica además un minuto
de protección de doble clic. Un nuevo envío posterior sigue siendo una solicitud
deliberada del usuario; antes de repetir uno indeterminado debe revisar la bandeja.
Se limita la frecuencia de las rutas de solicitud y consulta.

No se promete entrega exactamente una vez: SMTP puede aceptar un mensaje y perder
la respuesta. El estado indeterminado conserva esa incertidumbre. Reejecutar un
trabajo que ya llegó a aceptación no vuelve a enviarlo; una fase SMTP interrumpida
se detiene para comprobación manual. El bloqueo de caché compartida serializa las
solicitudes y los trabajos con la misma referencia; su backend debe compartirse
entre procesos en una instalación con varios trabajadores.

### Persistencia, permisos y privacidad

Se reutiliza Activitylog para la trazabilidad técnica, con eventos anexados bajo
`Correo/correo_estado`: referencia, tipo, estado, intento y próximo intento. No
se añadió una tabla ni se modificaron columnas, FK, catálogos o permisos. El
inventario permanece en 100 tablas de negocio más las técnicas existentes.
Los estados técnicos no sustituyen historia clínica ni un recibo del proveedor.

La consulta exige el actor original, el usuario destinatario exacto y el permiso
vigente que exige ese tipo de envío. Una referencia ajena devuelve 404. La
previsualización de rol no puede solicitar envíos. Los documentos no se guardan
en `storage/app/public/temp` ni se serializan como PDF dentro del trabajo nuevo.
Se conserva la copia al solicitante que ya tenía el envío de ficha.

Los fallos del trabajador se registran con referencia, estado e intento; las
excepciones almacenadas en la cola tienen mensajes genéricos. No se guarda el
texto de la excepción SMTP con credenciales/direcciones ni se muestra al usuario.
Gmail requiere TLS; el tiempo máximo de espera de SMTP es de 20 segundos.

### Operación de la cola

Se requiere una cola persistente (`database`, `redis`, `sqs` o `beanstalkd`). El
backend local es `database`, con reserva de 90 segundos y job de 60 segundos.
No se admiten `sync`, `null` o conexiones sin reintentos para este circuito.

```bash
php artisan queue:listen --queue=correo-documental --tries=3 --timeout=60 --sleep=3
```

Se inició este trabajador local después de comprobar que la cola documental
estaba vacía. No se procesaron colas anteriores ni se enviaron más mensajes de
prueba reales. `composer dev` incluye `correo-documental,default` en su listener
para futuros arranques de desarrollo. Una instalación permanente debe mantener
el trabajador mediante su supervisor de procesos y reiniciarlo con cada cambio
de configuración/código. Esta sesión no instala un servicio del sistema.

### Evidencia de implementación

- Servicio: `app/Modulos/Identidad/Aplicacion/Servicios/EnviosCorreoDocumental.php`.
- Trabajo reutilizado: `app/Modulos/Identidad/Infraestructura/Jobs/EnviarFichaUsuarioJob.php`.
- Preparación de los dos adjuntos: `app/Modulos/Identidad/Infraestructura/Notificaciones/PrepararCorreoDocumental.php`.
- Presentación: `resources/frontend/scripts/modules/correo-envios.js`, conectada a los dos botones existentes.
- Se corrigió un bloqueo preexistente del PDF: `usuarios` no conserva `created_at`;
  la fecha de ingreso se obtiene de `personal.fecha_ingreso` o se muestra ausente.
  El turno del adjunto se consulta a través de jornada. El paquete deja de
  presentar el día actual como una fecha de registro que no está conservada.

```bash
php artisan test --filter='SeguimientoCorreoDocumentalTest|CorreoDocumentalColaRealTest|TransporteCorreoRealTest|PasswordResetTest|ContratoPersonalInstitucionalTest' --compact
node --test tests/Frontend/correo-envios.test.js tests/Frontend/correo-envios-modal.test.js
npm run build
composer validate --no-check-publish
```

Resultado: **38 pruebas PHP, 169 aserciones; 4 pruebas frontend; build y Composer
correctos**. SQLite es desechable y el transporte de pruebas es `array`, sin red.
La prueba de cola usa el trabajador real de Laravel, dos rechazos SMTP sintéticos
451 y un mensaje aceptado al tercer intento; demuestra la reserva SQL y las dos
esperas sin equipararlas a una prueba de concurrencia PostgreSQL.

La prueba de interfaz abre el modal real en un documento sintético a 390 px,
comprueba pendiente → aceptado, un único POST, ausencia de desbordamiento y fin
del polling al cerrar. No sustituye una revisión de la sesión real autenticada.
En ese corte quedaban pendientes el panel de correo, las métricas de recepción
del proveedor y otras familias de notificaciones. El siguiente apartado registra
la implementación posterior del historial. No se realizó commit, push ni despliegue.

## Historial de fichas y paquetes documentales — 25/10/2026

La propietaria eligió «En Administración, con acceso desde cada usuario» y
«Fichas y paquetes documentales ya implementados». El alcance implementado es
**Mis envíos de correo**, con los permisos actuales; no concede acceso a los
envíos de otras cuentas ni crea permisos nuevos.

### Recorrido y presentación

1. Entrar por **Mis envíos de correo** en Gestión del sistema o Documentación,
   cuando el menú y los permisos existentes permiten ese acceso. También existe
   un acceso en la cabecera de Usuarios.
2. Desde la ficha o detalle de un usuario, **Historial de correos** abre el mismo
   listado filtrado por esa cuenta destinataria.
3. Buscar por nombre/apellido, tipo de documento, estado y fechas de **última
   actualización**; los resultados se paginan en grupos de doce solicitudes.
4. **Ver detalle** abre el modal compartido: documento, destinatario y solicitante
   actuales, estado y explicación, recorrido cronológico e intento previsto.
   **Actualizar estado** consulta de nuevo; abrir o actualizar no envía correo.
5. Solo una solicitud fallida muestra **Volver a solicitar envío**. La confirmación
   explica que se preparará un documento nuevo con los datos y correo actuales.
   Se reutiliza el servicio de cola/idempotencia; no se borra el envío anterior.
6. Un estado sin confirmar pide comprobar la recepción; no tiene reenvío directo
   desde este historial. Un estado aceptado tampoco se presenta como leído.

Ruta GET: `/admin/usuarios/correos/historial`, nombre
`admin.usuarios.correos.index`. Parámetro opcional `usuario`: cuenta destinataria.
Es un identificador de navegación, no un código visible al usuario.

En escritorio se usa tabla; en tablet/móvil, tarjetas con el mismo contenido y
acciones. Los filtros tienen etiquetas visibles y la ventana usa el modal
institucional, con foco contenido, Escape y retorno al control de apertura.
No se introdujo una paleta, librería ni componente de modal alternativo.

### Lectura y seguridad

- Sesión autenticada, cuenta ACTIVO y permiso vigente por tipo: `usuarios.ver`
  para ficha; `usuarios.editar` o `usuarios.crear` para paquete.
- Todas las consultas se limitan por solicitante autenticado, tipo de modelo y
  tipos permitidos. El servidor aplica el mismo alcance al abrir el detalle y
  solicitar otro envío. Un UUID de otra cuenta devuelve 404 sin efectos.
- Los identificadores de selección y usuario quedan bloqueados mediante
  `Livewire\Attributes\Locked`; las acciones vuelven a consultar el alcance.
- Una cuenta inactiva, un invitado o la revocación de permisos impiden acceso.
  La previsualización de roles no puede reenviar; conserva el guard existente.
- El reenvío Livewire limita a diez solicitudes por minuto y cuenta, además
  del bloqueo e idempotencia del servicio. Superar el límite no encola correo.
- No hay SQL en Blade. Las relaciones personales y de contactos se precargan
  para evitar consultas por fila. La búsqueda usa las entidades V2, no columnas
  de nombres inexistentes en `usuarios`.
- No se guardan PDF, cuerpo de correo, contraseña ni dirección destinataria en
  este historial. El modal declara que los nombres se leen de la cuenta actual.
  **No es una instantánea histórica del contenido ni del destinatario SMTP**.

La persistencia continúa en el Activitylog existente: una fila visible por
solicitud se obtiene del último evento, antes de aplicar filtros de estado o
fecha. El detalle conserva todos los eventos disponibles. No hay migraciones,
tablas, índices ni cambios de permisos añadidos. La retención de Activitylog
determina cuánto historial permanece consultable. Los mensajes anteriores que
no registraban estos eventos no se reconstruyen ni se marcan como entregados.

### Archivos de esta ampliación

| Archivo | Propósito |
|---|---|
| `app/Modulos/Identidad/Aplicacion/Consultas/HistorialCorreoDocumental.php` | Consulta paginada, alcance, búsqueda y presentación de auditoría |
| `app/Modulos/Identidad/Presentacion/Livewire/Paginas/CorreosPanel.php` | Filtros, detalle, validación y solicitud de reenvío |
| `app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/correos-panel.blade.php` | Tabla/tarjetas y modal compartido |
| `app/Modulos/Identidad/Presentacion/Rutas/web.php` | Ruta protegida dentro del registro modular existente |
| `app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php` | Accesos que respetan permisos vigentes |
| `app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/usuarios-panel.blade.php` | Acceso general y desde detalle de usuario |
| `app/Modulos/Identidad/Presentacion/Vistas/paginas/usuarios/show.blade.php` | Acceso desde ficha de usuario |
| `tests/Feature/HistorialCorreoDocumentalTest.php` | Integración HTTP/Livewire, filtros, seguridad, reenvío y ausencia de efectos al leer |
| `tests/Support/ServidorCorreoSintetico.php` | Servidor de ensayo con SQLite temporal, token, rutas limitadas y correo array |
| `tests/Frontend/correo-historial-e2e.test.js` | Navegador real con Laravel/Livewire y datos sintéticos desechables |

### Pruebas y límites de aceptación

```bash
php artisan test --filter='HistorialCorreoDocumentalTest|SeguimientoCorreoDocumentalTest|CorreoDocumentalColaRealTest|TransporteCorreoRealTest|PasswordResetTest|ContratoPersonalInstitucionalTest' --compact
node --test tests/Frontend/correo-envios.test.js tests/Frontend/correo-envios-modal.test.js tests/Frontend/correo-historial-e2e.test.js
npm run build
```

**Resultado: 55 pruebas PHP, 252 aserciones; 5 pruebas frontend; build correcto**
con la advertencia preexistente de chunk mayor de 500 kB. Dentro de esas 55,
17 pruebas nuevas del historial cubren aislamiento entre solicitantes y
destinatarios, revocación, cuenta inactiva/invitado, filtros, paginación,
rechazo de filtros inválidos, detalle sin escritura, fallo de configuración,
previsualización, límite de solicitudes y reenvío idempotente sin eliminar historia.

La prueba E2E ejecutó Laravel y Livewire reales sobre un SQLite temporal nuevo
en el directorio temporal del sistema. El servidor exige un token aleatorio,
rechaza bases no temporales o pobladas al prepararse y no se instala en public/.
Las solicitudes del navegador a otros servidores están bloqueadas. No se
enviaron correos reales ni se alteró la base institucional en esta ampliación.

Se comprobaron 1440, 1280, 1024, 768 y 390 px, claro/oscuro: ausencia de
desbordamiento horizontal, etiquetas, apertura con teclado, foco dentro del
modal, Escape, retorno del foco, filtros y estado vacío. Capturas sintéticas en
`output/correo-historial-20261025/`; salida PHP en
`output/pruebas-historial-correo-20261025.txt`. Las imágenes pertenecen al ensayo,
no a expedientes reales. La inspección de capturas detectó y permitió corregir
texto recortado y una opción mal formada en el selector de documento.

Una ejecución ampliada de `FrontendArchitectureTest` detectó cinco fallos fuera
de este flujo: modelos en `acceso-valoraciones`, conteo fijo de componentes (77
esperados frente a 84 actuales, incluyendo este nuevo), contrato de paleta,
contratos de overlays y separadores de dashboards. Los asertos no se debilitaron
ni se reescribieron esos módulos para declarar toda la arquitectura aprobada.
No se ejecutó la suite completa, una prueba PostgreSQL ni una certificación
integral de accesibilidad/contraste. Los controles compartidos mantienen sus
contratos; la revisión en la sesión institucional autenticada queda separada
del ensayo E2E.

### Pendientes y alcance excluido

- No hay lectura global de los envíos de otros solicitantes; requiere definir
  el alcance de auditoría correspondiente antes de ampliar esa frontera.
- Recuperación de contraseña y demás notificaciones conservan su circuito; la
  recuperación tiene pruebas de regresión satisfactorias, pero no entra aún
  en este historial.
- Las notificaciones de entrega, rebote y lectura del proveedor no están
  implementadas. SMTP aceptado no equivale a recepción.
- No se definió ni cambió una política de retención de auditoría.
- Se conserva el árbol concurrente y la rama existente. Sin commit, push,
  despliegue ni nuevas dependencias. No se reintrodujo nomenclatura V1 en el
  flujo nuevo ni se habilitó envío alguno al consultar esta pantalla.

Ampliación del 26/10/2026: [recuperación de contraseña, mensajes y recorrido completo](RECUPERACION_CONTRASENA_2026_10_26.md).
Esta ampliación mejora la recuperación; no la incorpora al historial documental.
