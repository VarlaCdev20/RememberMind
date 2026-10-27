---
title: "Diseño de acceso y recuperación de contraseña"
status: CURRENT
version: "6.3"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: AUTH_UI_SYNTHETIC_BROWSER_AND_REGRESSION
runtime_verified: true
---

# Acceso y recuperación — 26/10/2026

El corte vigente es la **revisión 6.3**, al final de este documento. Los cortes
anteriores conservan decisiones y pruebas de sus respectivas versiones.

Actualización funcional posterior del 26/10/2026: [Recordarme ya fue aprobado,
instalado y activado por 30 días](../sistema/RECORDARME_2026_10_26.md). Su
casilla depende de la activación configurada y usa el guard nativo de Laravel.

La propietaria pidió mejorar el diseño después de verificar la conexión SMTP.
Esta entrega cambia la presentación de `/login`, `/forgot-password` y
`/reset-password/{token}`. Conserva el [recorrido funcional de recuperación](../sistema/RECUPERACION_CONTRASENA_2026_10_26.md)
y sus restricciones. No cambia autenticación, permisos, credenciales,
expiración, política de contraseña, remitente ni base de datos.

## Antes y composición aplicada

El login mezclaba lógica Alpine extensa, cajas de ayuda prominentes, mensajes
de error duplicados en toast y panel, estilos inline y animaciones parallax.
La recuperación directa y la de login tenían geometrías distintas.

Flujo UX: **acceso → correo de la cuenta → instrucciones/enlace → contraseña
nueva → confirmación en login**. Se conserva una sola acción principal por
panel. Los errores quedan junto al formulario; la ayuda secundaria puede
desplegarse sin abandonar la tarea.

- Escritorio/tablet ancha: tarjeta central de hasta 1000 px, identidad y
  contexto institucional a la izquierda; formulario a la derecha. Se conserva
  el logo público del centro y el nombre que ya figuraba en el acceso.
- Menos de 768 px: una columna de hasta 480 px, identidad breve sobre el
  formulario, sin duplicar la presentación institucional.
- Recuperación directa: indicador accesible **Correo → Enlace → Nueva contraseña**.
  Expresa el paso de interacción, no confirma que el correo haya llegado.
- **¿No recibes el correo?** despliega las instrucciones sobre Spam, espera,
  vigencia y ayuda de Administración. Los tiempos proceden de configuración.
- El login conserva campos, mostrar/ocultar contraseña, Recordarme, retorno
  de recuperación y estados de procesamiento. Un segundo envío mientras se
  procesa queda bloqueado en la interacción; el backend conserva sus controles.
- Error: un solo panel con el mensaje del servidor, sin toast superpuesto.
  El texto se inserta mediante `x-text`, sin interpretar HTML de errores.
- Confirmación: se conserva el panel de contraseña actualizada tras guardar.
- Los formularios de solicitud y nueva contraseña usan el mismo patrón visual.
  La lista de requisitos ocupa dos columnas cuando hay espacio y una en móvil.

## Revisión 2: tipografía y distribución aprobadas

La propietaria eligió la opción **2: Plus Jakarta Sans + Inter** de la
comparación local. Plus Jakarta Sans se aplica a títulos, etiquetas y acciones;
Inter a los campos, textos de ayuda y contenido. Ambas se cargan desde los
archivos locales ya incluidos en el proyecto, sin nuevos paquetes ni servicios.
El alcance se limita a `.rm-auth-page`; las demás pantallas conservan su tipografía.

La tarjeta usa una proporción de columnas 0,9/1,1 y limita el contenido del
formulario a 420 px. El encabezado breve precede a los campos; Recordarme y
recuperación comparten fila cuando hay espacio. La ayuda de Administración
queda al pie, tras un separador. En móvil se oculta el panel lateral y se
mantiene una identidad breve sobre el formulario.

Los campos tienen 52 px de alto e iconos decorativos a la izquierda. Mostrar
contraseña ocupa 44 × 44 px dentro del campo, sin desplazarlo ni tapar el texto;
conserva nombre accesible, foco visible y `aria-pressed`. La acción principal
tiene al menos 48 px. No se eliminan etiquetas, autocompletado, validaciones,
estados de procesamiento ni las confirmaciones del backend.

La revisión 2 colocaba título y explicación antes del indicador de tres pasos;
la ayuda «¿No recibes el correo?» sigue desplegable. Solicitud directa y nueva
contraseña reutilizan las mismas reglas de composición, campos y tipografía.
Se mantiene la paleta institucional y el comportamiento de claro/oscuro.

## Revisión 3: cambio dentro de la tarjeta, sin barra de pestañas

La última indicación de la propietaria sustituye el selector visible de
pestañas: **no se muestra «Iniciar sesión / Recuperar acceso»**. Se utiliza
únicamente «¿Olvidaste tu contraseña?» y, en recuperación, «Volver al inicio
de sesión». Ambos cambian el contenido de la misma tarjeta mediante Alpine;
la dirección sigue siendo `/login`. Cada panel conserva su formulario propio,
sin formularios anidados ni nuevas rutas.

Enviar instrucciones también permanece en esa vista: `fetch` solicita JSON al
endpoint existente, con CSRF y cookies del mismo origen. La carga contiene solo
el correo y el token CSRF del formulario de recuperación, sin la contraseña
de acceso. La confirmación depende de la respuesta del servidor y conserva
el mensaje uniforme que no revela si existe una cuenta. No equivale a
confirmar entrega del mensaje. Se representan errores de validación, correo
no disponible, sesión vencida, límite de solicitudes y falta de conexión;
no se muestran respuestas HTML ni detalles internos de errores 500.
El bloqueo de doble envío y cambio de panel dura hasta finalizar la solicitud.

El indicador de tres pasos se conserva en las pantallas directas de solicitud
y nueva contraseña. Se omite en el panel compacto del login. Cambiar entre
acceso y recuperación conserva los valores escritos en memoria de la página;
no guarda contraseñas en storage. Se mueve el foco al correo del panel activo.

Movimiento solicitado expresamente por la propietaria, acotado a este acceso:

| Interacción | Efecto | Tiempo y alternativa |
|---|---|---|
| Abrir/cerrar recuperación | Opacidad y desplazamiento horizontal de 12 px | Tokens `--rm-duration-normal` / `--rm-duration-fast`; con movimiento reducido se elimina desplazamiento |
| Mover el puntero de precisión | Dos contornos laterales con profundidad opuesta de hasta 8 px; texto lateral hasta 2 px | Tokens de transición existentes; campos y botones permanecen estables |
| Salir del área | Profundidad vuelve a cero | Sin animación continua ni temporizadores |
| Pantalla táctil o movimiento reducido | Parallax deshabilitado | Contenido y operaciones disponibles sin depender de la animación |

Se reutilizan superficies, bordes y colores existentes; no se añade biblioteca
de animación. La implementación antigua de parallax retirada en la revisión 1
no se recupera: el efecto actual queda limitado al patrón y módulo de acceso.

## Revisión 4: composición limpia, fotografías y tipografía

La propietaria pidió profundidad, microinteracciones y fotografía variable,
seguido de una composición menos saturada. La versión resultante conserva
una foto, el nombre del centro, un mensaje breve y el formulario. Se retiran
los dos bloques informativos inferiores del lateral, el símbolo decorativo
adicional y los círculos de fondo. No se incorpora la barra de pestañas.

Se reutiliza `DashboardPhotoRotation::heroImage()` con contexto `login`:
tres imágenes que ya figuran en la bienvenida pública, sin consultar ni
publicar archivos de la galería privada. La selección avanza por sesión en
cada GET; después de tres vuelve a comenzar. No es un carrusel automático
ni cambia al abrir recuperación o al escribir. La foto se mantiene durante
la interacción. La prueba del login comprueba cuatro cargas y el ciclo exacto.

En escritorio la foto ocupa un recuadro lateral de 236 px de alto; en móvil,
una franja de 112 px sobre el formulario con recorte focal ajustado. El texto
se presenta sobre superficies sólidas, sin superponerse a la foto. El relieve
usa `--rm-shadow-floating` en la tarjeta y los tokens de input en campos.
Hover, foco, press, indicador de procesamiento y retorno mantienen geometría
estable. El parallax mueve solo la foto hasta 8 px con un recorte ampliado;
no desplaza el formulario. Se desactiva en táctil y movimiento reducido.

El [stack tipográfico](STACK_TIPOGRAFIA.md) documenta los títulos redondeados,
la lectura, cursiva auténtica, acento pincel y mayúsculas pequeñas. La frase
«y dignidad.» utiliza Caveat Brush; la descripción usa Inter italic local;
labels y acciones mantienen Plus Jakarta Sans. El cambio queda acotado al
acceso y recuperación, sin sustituir la tipografía de módulos clínicos.

Archivos adicionales del corte:

| Archivo | Propósito |
|---|---|
| `app/Modulos/Reportes/Presentacion/DashboardPhotoRotation.php` | Contexto público de rotación para login |
| `resources/css/app.css` | Import local de Inter 400 italic ya instalado |
| `tests/Feature/DashboardPhotoRotationTest.php` | Añade login al contrato de rotación existente |
| `tests/Feature/AuthenticationTest.php` | Ciclo real de cuatro solicitudes GET del acceso |
| `tests/Support/ServidorRecuperacionSintetico.php` | Resuelve nombres de assets públicos con espacios, conservando comprobación de ruta dentro de public |
| `docs/frontend/STACK_TIPOGRAFIA.md` | Funciones y límites de composición tipográfica |
| `docs/frontend/STACK_SKILLS_UX_UI.md` | Enlace al stack tipográfico actualizado |

### Mantenimiento compartido

Tokens existentes de superficies, estados, radios, espaciado, tipografía,
botones, campos y foco. No hay paleta ni dependencia nueva. El patrón de acceso
usa botones y campos `.rm-btn`/`.rm-input` existentes. La superficie es sólida
y el relieve procede de la sombra compartida. La revisión 3 incorpora el
movimiento acotado solicitado, descrito arriba.

Se añadió `components/auth/recovery-steps.blade.php` porque el mismo indicador
lo reutilizan las pantallas directas de solicitud y nueva contraseña. No
consulta datos ni decide reglas. El JS de
interacción se extrajo a `auth-access.js` para probarlo y evitar lógica extensa
duplicada en Blade. Se retiraron `auth-login-parallax.js` y `auth-login.css`,
sin consumidores restantes, y se actualizaron sus imports. El cambio no añade
capas de compatibilidad ni código de otras versiones.

La revisión detectó que el fondo translúcido del botón reducía contraste.
El patrón acota `--rm-capture-action-bg` al token primario sólido existente,
con tinta petróleo existente, sin modificar botones de otros módulos.
En el servidor E2E se habilitó únicamente el logo público exacto, porque el
symlink de storage salía del prefijo public; no se abrió acceso general a archivos.

## Archivos

| Archivo | Propósito |
|---|---|
| `resources/views/auth/login.blade.php` | Composición y estados de acceso/recuperación |
| `resources/views/auth/forgot-password.blade.php` | Solicitud directa coherente |
| `resources/views/auth/reset-password.blade.php` | Nueva contraseña con el mismo patrón |
| `resources/views/components/auth/recovery-steps.blade.php` | Indicador reutilizado, con `aria-current` |
| `resources/frontend/styles/design-system/patterns/auth-access.css` | Composición y adaptación con tokens compartidos |
| `resources/frontend/styles/design-system/index.css` | Import del patrón canónico |
| `resources/frontend/scripts/modules/auth-access.js` | Estado Alpine, foco, validación y doble envío |
| `resources/frontend/scripts/app.js` | Import de interacción, conservando otros cambios locales |
| `resources/frontend/scripts/modules/auth-login-parallax.js` | Retirado: interacción decorativa sin consumidores |
| `resources/frontend/styles/modules/auth-login.css` | Retirado: estilos inline históricos del login |
| `tests/Frontend/auth-access.test.js` | Validación, correo, doble envío, estado y falta de storage |
| `tests/Frontend/recuperacion-contrasena-e2e.test.js` | Matriz visual y recorrido real |
| `tests/Support/ServidorRecuperacionSintetico.php` | Logo público exacto en el ensayo aislado |
| `tests/Feature/AuthenticationTest.php` | Contrato actualizado de inicialización modular; preserva las pruebas de autenticación |

## Verificación

```bash
php artisan test --filter='AuthenticationTest|PasswordResetTest|RecuperacionContrasenaFlujoTest|CorreoNoConfiguradoRespuestaTest' --compact
node --test tests/Frontend/auth-access.test.js tests/Frontend/recuperacion-contrasena-e2e.test.js
npm run build
```

**27 pruebas PHP, 181 aserciones; 5 pruebas frontend; build correcto.** Se
conserva la advertencia de tamaño de chunk mayor de 500 kB. Salida PHP:
`output/pruebas-diseno-acceso-20261026.txt`.

El E2E ejecuta Laravel/Alpine/Fortify reales con una cuenta sintética en SQLite
temporal, notificaciones falsas y peticiones externas bloqueadas. Cubre el
error de acceso y un solo aviso visible, solicitud desde login y pantalla
directa, mostrar/ocultar contraseña, error de confirmación, guardar, confirmación,
rechazo del enlace usado y retorno. No envía correo real ni cambia cuentas reales.

Matriz de login y recuperación: 1440, 1280, 1024, 768 y 390 px; claro y oscuro.
Se verifican ausencia de desbordamiento, etiquetas, controles de al menos 44 px
de altura, foco al abrir/volver, logo cargado, errores JavaScript y literales
inválidos. Se miden colores compuestos de títulos, etiquetas, nota y botones:
4,5:1 en texto normal, 3:1 en títulos grandes e iconos. Es una comprobación
acotada, no una certificación completa WCAG ni de todos los estados de hover.
La matriz principal respeta movimiento reducido; la revisión 3 añade una
comprobación con movimiento normal y otra al volver a movimiento reducido.

En esta revisión se repitieron los comandos anteriores: **27 pruebas PHP,
181 aserciones; 5 pruebas frontend, sin omisiones; build correcto**. El E2E
comprueba además la familia tipográfica calculada, la carga efectiva local de
Plus Jakarta Sans y que el botón de mostrar contraseña esté dentro del campo.
Las capturas de esta carpeta se actualizaron con la revisión 2. La inspección
visual cubrió login en escritorio y móvil, recuperación móvil y nueva
contraseña en oscuro. `git diff --check` del alcance no informó errores.

Resultado de la revisión 3: **27 pruebas PHP, 182 aserciones; 6 pruebas frontend,
0 fallos y 0 omitidas; build correcto**, con la advertencia existente de tamaño
de chunk. Evidencia PHP: `output/pruebas-diseno-acceso-20261026-actual.txt`;
build: `output/build-acceso-20261026-actual.txt`.
El E2E demuestra que abrir y enviar recuperación no navega ni recarga el
documento, que no hay barra de pestañas, que la contraseña escrita sigue al
volver y que el parallax no desplaza los campos. La comprobación visual
incluye `recuperacion-parallax-1440.png` y las capturas actualizadas por ancho.
Las pruebas unitarias cubren fallos HTTP 419/422/429/503/500, HTML inesperado
y red, además del bloqueo de solicitudes simultáneas y ausencia de falso éxito.

Resultado final de la revisión 4: **30 pruebas PHP y 264 aserciones; 6 pruebas
frontend, sin fallos ni omisiones; build correcto (116 módulos, 3,55 s)**.
El comando PHP de este corte incorpora `DashboardPhotoRotationTest` al filtro
anterior. La última ejecución frontend duró 45,45 s. Se conserva la advertencia
de Vite por chunk mayor de 500 kB. No se ejecutó la suite global ajena al alcance.

La inspección visual del corte abarca capturas de login en 1440, 1280, 1024,
768 y 390 px, con revisión adicional de recuperación móvil y modo oscuro.
Se comprueban carga local de Caveat Brush, cursiva real de Inter, mayúsculas
pequeñas, apertura con teclado y ausencia de la barra de pestañas. La matriz
automatizada verifica ambos temas en los cinco anchos y el recorrido completo.
Dictamen acotado a estos criterios: **PASS**. No equivale a una certificación
WCAG completa ni a una revisión de todas las pantallas del producto.

Capturas del recorrido con cuenta sintética y fotografías institucionales
públicas en `output/recuperacion-contrasena-20261026/`:

- `acceso-light-1440.png`, `acceso-light-390.png` y equivalentes por ancho/tema.
- `recuperacion-light-390.png` y equivalentes por ancho/tema.
- `acceso-error-390.png`, `solicitud-desde-login-390.png`.
- `confirmacion-login-390.png`, `enlace-utilizado-390.png`.

Dictamen acotado de la superficie y recorrido enumerados: **PASS**. La sesión
real de la propietaria y el envío real de un mensaje no forman parte de este
ensayo. No se afirma aceptación de las demás pantallas del sistema ni de los
hallazgos abiertos de la auditoría general.

## Estado de entrega

### Corrección 6.3: descripción de acceso en una línea

Por indicación de la propietaria se abrevia a «Ingresa tus datos para continuar.»
y se retira el ancho de 34ch y el balance de líneas del corte anterior.
Permanece centrada y con tamaño de cuerpo. No se fuerza `white-space: nowrap`,
para conservar reflow al ampliar el texto. Verificación en Chrome local:
una línea, centrado y sin desbordamiento a 1440, 1280, 1024, 768 y 390 px.
Capturas `acceso-linea-{ancho}.png`. Build correcto (3,68 s), con advertencia
existente de tamaño de chunk. Sin cambios de backend ni repetición de suites
funcionales ajenas a este ajuste de texto.

### Corrección 6.2: jerarquía de bienvenida y explicación

La descripción «Ingresa tus datos para continuar con tu jornada.» queda
centrada bajo la bienvenida, con ancho máximo de 34 caracteres aproximados
(`ch`). Se agrupan título y descripción con el espaciado compartido de 8 px
y se conserva una separación mayor antes del formulario. El título mantiene
su peso y tamaño destacado; la explicación mantiene tamaño de cuerpo y tinta
secundaria. Los labels siguen alineados con los campos. Alcance limitado a
`#panel-login`, sin cambiar recuperación ni nueva contraseña.

El E2E comprueba centrado de la descripción y tamaño superior del título,
además de la matriz responsive de cinco anchos en claro y oscuro.
Resultado: 6 pruebas frontend correctas (54,12 s). Se añade ajuste equilibrado
de líneas (`text-wrap: balance`), comprobado posteriormente en Chrome local
a 1440 y 390 px, sin desbordamiento. Capturas `acceso-jerarquia-{ancho}.png`.
Build final correcto (3,24 s), con la advertencia existente de tamaño de chunk.

### Corrección 6.1: bienvenida centrada en mayúsculas

Por indicación de la propietaria, únicamente `#acceso-titulo` se centra y
se presenta en mayúsculas mediante CSS. No cambia el contenido ni el estilo
de los títulos de recuperación/nueva contraseña. La prueba de navegador
comprueba las dos propiedades calculadas en cinco anchos y ambos temas.
Resultado: 6 pruebas frontend correctas (55,01 s) y build correcto (3,44 s),
con la advertencia existente de tamaño de chunk. Inspección móvil sin
desbordamientos; sin cambios de backend.

### Revisión 6: reducir saturación de información

La propietaria pidió menos información visible en los tres formularios.
Se retiran encabezados redundantes «RememberMind · Recuperación», la barra
de pasos y notas repetidas. El título identifica la tarea y una frase breve
explica la acción. En acceso, el pie pasa a una sola frase de ayuda.

La solicitud directa conserva el correo, su validación, la acción principal
y la confirmación uniforme del backend. «¿No recibes el correo?» despliega
Spam, espera entre solicitudes, vigencia y ayuda de Administración. La
confirmación no afirma entrega ni revela la existencia de la cuenta.

En nueva contraseña se conserva la cuenta de solo lectura, ambos campos de
contraseña, mostrar/ocultar, coincidencia, errores y bloqueo de doble envío.
La política se presenta como ayuda breve junto al campo, con mínimo/máximo
y cada condición habilitada provenientes de `$politica`. No cambia el backend
ni se relajan requisitos. Se retira la caja de cinco renglones y la nota final
redundante. El enlace inválido conserva su error y acción de solicitar otro.

Se elimina `components/auth/recovery-steps.blade.php` y sus estilos sin
consumidores, así como los estilos de la caja de requisitos retirada. La
tarjeta compartida, la imagen fija con rotación al recargar y el lema centrado
en cursiva se mantienen.

Verificación del corte 6: **31 pruebas PHP, 276 aserciones; 6 pruebas frontend
sin fallos ni omisiones (55,41 s); build correcto (3,42 s)**. Se inspeccionan
las capturas actualizadas de nueva contraseña en escritorio/móvil y solicitud
directa, sin defectos observados en este alcance. Se mantiene la advertencia
de tamaño de chunk de Vite. Sin cambios de base de datos, credenciales,
permisos, envío real de correo, commit, push ni despliegue.

### Revisión 5: diseño compartido en los tres formularios autorizados

La propietaria delimitó expresamente el alcance a **acceso, recuperación y
cambio de contraseña**. Se aplica la misma tarjeta en `/login`,
`/forgot-password` y `/reset-password/{token}` mediante el componente Blade
`resources/views/components/auth/access-card.blade.php`. Este componente
reutiliza el rotador público existente y contiene foto, identidad, lema
centrado en cursiva y contenedor del formulario. La lógica de envío y los
campos permanecen en sus vistas; el componente no decide autenticación.

Se elimina la variante de recuperación de una sola columna estrecha en
escritorio. Las tres pantallas comparten las dos columnas, el ancho de captura
máximo de 420 px, relieve, foco y tipografía. El lateral agrupa su contenido
centrado verticalmente para evitar grandes separaciones en el formulario
de nueva contraseña. En móvil se conserva la franja fotográfica compacta y
una columna. La foto permanece fija al mover el cursor y rota al recargar.

La recuperación abierta desde login sigue dentro de la misma tarjeta, sin
navegación ni barra de pestañas. La solicitud directa y el enlace del correo
mantienen sus rutas y pasos propios. No se cambia confirmación de contraseña,
verificación de correo ni doble factor, fuera del alcance confirmado.

Prueba PHP añadida: las rutas directa y de nueva contraseña conservan correo,
lema, foto y contenedor compartido. La prueba E2E amplía su matriz de cinco
anchos por dos temas a la solicitud directa y aplica comprobaciones de
layout, controles, etiquetas y contraste también al formulario de nueva clave.
Las notificaciones del ensayo son falsas y la cuenta es sintética en SQLite
temporal; no se envían correos ni se cambian cuentas institucionales.

Resultado del corte 5: **31 pruebas PHP, 276 aserciones; 6 pruebas frontend,
0 fallos y 0 omisiones (54,98 s); build correcto (3,71 s)**. Se conserva
la advertencia de Vite por tamaño de chunk. Evidencias actualizadas en
`output/pruebas-diseno-acceso-20261026-actual.txt`,
`output/build-acceso-20261026-actual.txt` y las capturas
`solicitud-directa-{tema}-{ancho}.png` / `nueva-contrasena-{tema}-{ancho}.png`.
Inspección visual adicional de solicitud y nueva contraseña en escritorio,
y nueva contraseña móvil: sin defectos observados en el alcance revisado.

### Corrección 4.1 solicitada en anotaciones de la propietaria

Esta decisión sustituye el acento de pincel y el parallax descritos en los
cortes anteriores. «Cuidado, memoria y dignidad.» usa íntegramente Plus Jakarta
Sans 700 con el mismo tamaño, color y estilo. La foto permanece fija al mover
el cursor, sin transformación ni zoom. Se eliminan los handlers de puntero,
los métodos JS y las reglas CSS del parallax. Se conserva la rotación por
recarga, el foco y las transiciones entre acceso y recuperación.

La prueba de navegador compara posición y dimensiones de la fotografía antes
y después de mover el cursor con movimiento normal; exige `transform: none`
y también comprueba movimiento reducido. El lema carece de fragmentos con
otras familias. Captura del corte: `recuperacion-foto-fija-1440.png`.
Verificación del corte 4.1: **6 pruebas frontend correctas, sin omisiones
(45,08 s), build correcto (4,05 s)**. Se mantiene la advertencia de tamaño de
chunk. No se modificó backend ni se repitió la suite PHP de la revisión 4.

Implementado y probado en el entorno descrito. Preservados cambios simultáneos.

Corrección 4.2: por indicación posterior de la propietaria, el lema completo
queda centrado y en cursiva. Usa Plus Jakarta Sans 700 italic cargada del
paquete local existente; no se simula ni cambia la fuente entre palabras.
El resto del formulario y la fotografía fija conservan su comportamiento.
El E2E exige `text-align: center` y `font-style: italic` en los anchos donde
el lateral está visible, además de las verificaciones responsive en cinco
anchos. Resultado: 6 pruebas frontend correctas (45 s) y build correcto
(3,34 s), con la advertencia existente de tamaño de chunk.
Sin cambios de backend de autenticación en este rediseño, migraciones, nuevos
servicios externos, credenciales, permisos, commit, push ni despliegue.
