---
title: "Recuperación de contraseña: mensajes y recorrido completo"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: RECOVERY_FLOW_DISPOSABLE_SQLITE_AND_BROWSER
runtime_verified: true
---

# Recuperación de contraseña — 26/10/2026

La presentación posterior se documenta en [diseño de acceso y recuperación](../frontend/ACCESO_Y_RECUPERACION_2026_10_26.md).
Ese rediseño conserva el contrato funcional descrito aquí y sustituye la composición visual de sus primeras capturas.

La propietaria eligió completar la recuperación de contraseña: solicitud,
enlace vencido, validación y confirmación del cambio. La implementación
reutiliza Fortify **1.40.0** instalado, su broker, tabla técnica de tokens,
notificación y política institucional existentes. No cambia el esquema,
los permisos, la vigencia ni la composición exigida de las contraseñas.

## Recorrido implementado

1. **Login → ¿Olvidaste tu contraseña?** conserva el panel existente.
   También se puede entrar directamente en `/forgot-password`.
2. **Solicitar instrucciones** envía el correo indicado al broker. La
   respuesta pública sigue siendo la misma para una cuenta existente,
   inexistente o una petición repetida dentro de la espera del broker.
   Se muestra **Solicitud recibida**, con ayuda para revisar Spam, esperar
   antes de repetir y utilizar el enlace más reciente. No se afirma recepción.
3. Abrir el enlace del correo consulta su validez sin consumirlo ni cambiar
   la contraseña. Un enlace vencido, utilizado, inválido, incompleto o de una
   cuenta inactiva muestra **Necesitas un enlace nuevo**, con la acción
   **Solicitar un enlace nuevo**. No se muestra un formulario inutilizable.
4. Un enlace utilizable muestra la cuenta asociada, la contraseña nueva y su
   confirmación. Los requisitos proceden de `passwordPolicy()` del backend;
   no hay una política alternativa escrita en Blade. Se conservan botones
   para mostrar/ocultar, ayuda de coincidencia y validación del servidor.
5. Si falla la validación, la contraseña anterior continúa vigente y las
   claves no reaparecen rellenadas. El enlace puede seguir utilizándose
   hasta que venza o se complete correctamente el cambio.
6. **Guardar nueva contraseña** delega en las acciones Fortify existentes.
   Al confirmar, el token se consume y se redirige a login, sin iniciar una
   sesión automáticamente. Una tarjeta persistente en esa respuesta indica
   **Contraseña actualizada**, con la instrucción de iniciar sesión con la
   clave nueva. Una marca antigua de recuperación en sessionStorage no
   oculta la confirmación detrás del panel de recuperación.

La confirmación es flash de sesión: permanece visible durante esa respuesta,
sin depender del temporizador de un toast. No se presenta como un aviso
permanente del expediente ni se conserva un estado de éxito entre sesiones.

## Correcciones e integración

- La respuesta fallida estándar de Fortify asociaba el error a `email` y
  conservaba ese campo. RememberMind utiliza `correo`. La respuesta modular
  nueva usa `correo` tanto en JSON como en la redirección del navegador.
- Se eliminan de la vista de restablecimiento referencias ejecutables a
  `App\Actions\Identidad\Fortify`, que ya no corresponde al propietario
  modular, y su política de fallback. Las acciones actuales están bajo
  `app/Modulos/Identidad/Aplicacion/Acciones/Fortify/`.
- El texto `validation.confirmed` aparecía literalmente cuando las claves
  no coincidían. Se completa esa traducción y los nombres de campos en español.
- La vista utiliza los controles, tarjetas, espaciado y contexto de tokens
  institucionales. La adaptación verificada mantiene una columna legible
  en los cinco anchos, sin crear una paleta o librería de formularios.
- El controlador modular de restablecimiento extiende el de Fortify para
  validar la forma de los parámetros antes de entrar al broker. No replica
  la generación, comparación, expiración o consumo de tokens.
- El GET del enlace se sirve con `Cache-Control: no-store, private` y
  `Referrer-Policy: no-referrer`.
- Se preserva y verifica la protección concurrentemente integrada de correo
  no configurado: comprobación anterior al broker, respuesta pública segura
  y ausencia de tokens/correos si esa comprobación falla. No se sobrescriben
  ese controlador ni su excepción de infraestructura.

## Seguridad y persistencia

- Las rutas, autenticación y CSRF siguen siendo los de Fortify; no se crean
  rutas públicas alternativas para cambiar claves.
- El POST vuelve a validar el token y los requisitos; el GET no autoriza por
  sí solo el cambio. Si el enlace vence con el formulario abierto, el POST
  devuelve el mensaje y la vía para solicitar otro.
- Los estados inválidos no diferencian públicamente cuenta ausente, token
  incorrecto o token vencido. Las solicitudes repetidas no distinguen si la
  cuenta existe. No hay un mensaje «usuario no encontrado» en este recorrido.
- El GET y la acción de cambio exigen cuenta activa. Desactivar la cuenta
  después de solicitar el enlace impide modificarla, sin reactivarla.
- La validación rechaza arrays en correo, token o contraseña, en lugar de
  pasarlos a la verificación como si fueran cadenas.
- Se usa la tabla técnica existente `password_reset_tokens`; los tokens
  quedan **hasheados**, no en texto ni mediante un cifrado reversible.
- Contraseñas y tokens no se agregan a Activitylog. Se conserva el evento
  existente «Contraseña restablecida.» al guardar correctamente.
- El cambio no concede roles, competencias, acceso clínico ni cambia el
  estado de la cuenta. Tampoco introduce políticas nuevas de contraseña.

## Archivos de esta tarea

| Archivo | Responsabilidad |
|---|---|
| `app/Modulos/Identidad/Aplicacion/Consultas/ConsultarEnlaceRecuperacion.php` | Lectura de validez con el broker existente |
| `app/Modulos/Identidad/Presentacion/Http/Controllers/RestablecerContrasenaController.php` | Forma de entrada y delegación a Fortify |
| `app/Modulos/Identidad/Presentacion/Http/Responses/RestablecimientoContrasenaResponse.php` | Error en `correo`, recuperación y confirmación web/JSON |
| `app/Providers/FortifyServiceProvider.php` | Bindings y datos de la vista de recuperación |
| `app/Modulos/Identidad/Aplicacion/Acciones/Fortify/ResetUserPassword.php` | Rechazo de cambio para cuenta inactiva |
| `app/Http/Responses/PasswordResetLinkResponse.php` | Marca de sesión para conservar el panel correcto |
| `resources/views/auth/forgot-password.blade.php` | Solicitud directa y explicación de pasos |
| `resources/views/auth/reset-password.blade.php` | Formulario, errores y enlace no utilizable |
| `resources/views/auth/login.blade.php` | Panel existente y confirmación visible del cambio |
| `lang/es/passwords.php` y `lang/es/validation.php` | Mensajes comprensibles y confirmación traducida |
| `tests/Feature/RecuperacionContrasenaFlujoTest.php` | Casos positivos y negativos HTTP/SQL |
| `tests/Support/ServidorRecuperacionSintetico.php` | Ensayo privado con base temporal y notificaciones falsas |
| `tests/Frontend/recuperacion-contrasena-e2e.test.js` | Recorrido real Laravel/Alpine/Fortify en navegador |

## Evidencia ejecutada

```bash
php artisan test --filter='RecuperacionContrasenaFlujoTest|PasswordResetTest|AuthenticationTest|UpdatePasswordTest|PasswordConfirmationTest|RedireccionLoginAutorizadaTest|CorreoNoConfiguradoRespuestaTest' --compact
node --test tests/Frontend/recuperacion-contrasena-e2e.test.js
npm run build
```

Resultado: **44 pruebas PHP, 223 aserciones**, una prueba E2E satisfactoria y
build Vite correcto. Dentro de las 44 hay catorce casos nuevos del flujo:
uniformidad de solicitud, ayuda web, correo inválido, enlace de notificación,
lectura sin efectos, vencimiento, parámetros inválidos/ajenos, uso único,
validación, cuenta inactiva, entrada manipulada, respuesta JSON sin secretos,
renovación del enlace y login con la nueva clave. La regresión también incluye
autenticación, contraseña del perfil, confirmación, destinos de login y error
seguro por configuración de correo ausente.

SQLite `:memory:` en PHP. El E2E prepara exclusivamente un archivo SQLite nuevo
y vacío, con nombre restringido en el directorio temporal, token de acceso del
ensayo y una cuenta sintética. La notificación se captura con `Notification::fake()`;
se utiliza su URL real para recorrer el restablecimiento. No se llama a SMTP.
La URL del token sintético se conserva temporalmente fuera del repositorio y
se elimina al terminar. Las peticiones del navegador a otros servidores están
bloqueadas. No se alteran cuentas ni contraseñas institucionales reales.

El E2E recorre solicitud desde login y desde la pantalla directa, lectura
válida, mostrar/ocultar contraseña, error de confirmación, cambio, confirmación
en login, rechazo del enlace ya utilizado y vuelta a solicitar. Verifica
1440, 1280, 1024, 768 y 390 px, claro/oscuro, etiquetas, ausencia de
desbordamiento horizontal y ausencia de errores JavaScript. Utiliza la
preferencia de movimiento reducido y la API de tema existente. Las capturas
son sintéticas, sin contraseñas rellenadas en el formulario.

Evidencias locales:

- `output/pruebas-recuperacion-20261026.txt`.
- `output/recuperacion-contrasena-20261026/`.

## Límites y pendientes separados

- No se ha enviado un correo de recuperación real en esta tarea ni cambiado
  la contraseña de la propietaria. La comprobación previa de conexión SMTP
  sigue documentada en [correo institucional](CORREO_SMTP_CONFIGURACION.md).
- No se reejecuta la suite completa ni se afirma concurrencia PostgreSQL.
  El build mantiene la advertencia existente de chunk superior a 500 kB.
- No se certifica accesibilidad integral ni se rediseña el login completo.
- No se integra recuperación en el historial de fichas/paquetes; son alcances
  diferentes. La cola permanente y monitorización del servicio siguen siendo
  otra tarea.
- No se renombra la notificación existente ni se limpia otro legacy ajeno.
- Árbol concurrente preservado. Sin migraciones, nuevas dependencias, commit,
  push ni despliegue.
