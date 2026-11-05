# Registro de personal: cuatro pasos unificados — 05/11/2026

## Alcance

La propietaria solicita aplicar a todo el formulario el diseño y la validación por campo aprobados para identidad y dirección. Se reutilizan `ui.field`, `ui.selector` y `ui.form-section`, Nunito, las superficies café grisáceas, el acento verde y los estados de validación existentes. No hay DDL, nuevos permisos, requisitos profesionales o reglas clínicas.

## Presentación

- Identidad conserva país/documento/teléfono, edad de 18 a 50 y una sola captura de dirección. El verde de los padres geográficos se conserva al modificar un descendiente; sus opciones, errores y estados dependientes se limpian antes de validar el nuevo valor.
- Información laboral usa el mismo selector que identidad; la función de una cuenta existente continúa deshabilitada y protegida por autorización en backend. Fecha de ingreso, matrícula y observaciones conservan su carácter opcional. No se inventa prohibición de fechas futuras ni obligatoriedad profesional adicional.
- Cuenta valida correo y contraseña temporal con los límites existentes. El control de mostrar/ocultar no altera la contraseña y tiene nombre accesible y estado presionado. El correo conserva minúsculas y la contraseña conserva sus caracteres; los textos laborales se normalizan como antes.
- Documentación presenta el resumen de identidad, dirección, función y acceso sin mostrar la contraseña. Los archivos usan controles nativos con el mismo estilo, errores y confirmación verde por campo, progreso individual y «Quitar selección». Cargar o validar formato no equivale a aprobación documental.

## Validación y persistencia

Una validación exitosa marca únicamente campos con valor; los opcionales vacíos no aparecen como correctos. La corrección por campo conserva errores ajenos. El guardado revisa los cuatro pasos antes de la operación y vuelve al primero inválido, enfocando su primer control con error. La operación institucional final mantiene sus validaciones, autorización, transacción y auditoría; no se sustituye por la validación del navegador.

Los archivos temporales deben pertenecer al catálogo de la función, ser archivos admitidos y respetar los 5 MB existentes. Al quitar una selección se limpian sus errores/estado de validación. La fotografía sigue admitiendo JPG/PNG/WebP de hasta 2 MB; una ruta escrita no se acepta como imagen nueva. La referencia original bloqueada permite conservar la foto guardada al editar; el backend vuelve a contrastarla con la cuenta real. Una foto vacía continúa siendo opcional.

La selección de rol no amplía responsabilidades o permisos. En edición se conserva la posibilidad previa de mantener roles existentes sin obligar a reducirlos a uno. La fecha de ingreso usa el formato ISO que produce el calendario; los límites institucionales existentes se mantienen.

## Verificación

Pruebas nuevas: validación laboral aislada y estados opcionales, regreso al primer paso inválido sin escritura, archivos inválidos/correctos/eliminados y claves ajenas al catálogo, conservación de los padres geográficos válidos, fotografía nueva/manipulada/vacía y conservación de la registrada.

Evidencias sintéticas en `output/personal-formulario-integral-*`. La CI completa no forma parte de esta entrega y no se utilizan cuentas o documentos reales para las pruebas.

Integración del recorrido: la verificación visual descubre una llamada obsoleta a un método inexistente al entrar al cuarto paso. Se retira esa llamada; una prueba del recorrido completo confirma identidad → laboral → cuenta → revisión sin persistencia anticipada. No se introduce una sesión paralela ni un método vacío como sustituto.

Accesibilidad: al enfocar un campo se desplaza su grupo completo, incluidos ayuda/error y acciones, respetando el pie fijo. Se incluye el control de contraseña y la retirada de archivos para evitar acciones tapadas al navegar con teclado.

Cierre: 66 pruebas y 2155 aserciones correctas en SQLite y las mismas 66/2155 en PostgreSQL desechable; 11 pruebas JavaScript de controles correctas. Build de producción correcto. El E2E completo pasa en 128.74 s en 1440/1280/1024/768/390 px: cuatro pasos, campos faltantes, correcciones aisladas, selector de función, contraseña mostrable sin mutación, resumen sin contraseña, archivos incorrectos/correctos/retirados y regresión de identidad/dirección/directorio. Sin errores JavaScript ni respuestas HTTP fallidas. Capturas revisadas en escritorio y móvil para laboral, cuenta y documentos. Dictamen visual y funcional del alcance: PASS. Se conservan cambios preexistentes y la CI completa queda pendiente fuera de este alcance.
