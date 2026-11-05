# Otro país en registro de Personal

## Alcance aprobado

La propietaria solicita Otro/escribir y luego acepta ampliar las opciones conservando los códigos de país en las columnas existentes. La propuesta de tres campos adicionales para nombres/prefijo manual no se autoriza ni se implementa. No hay DDL, nuevas tablas, permisos o backfill. Las tres columnas autorizadas anteriormente permanecen sin cambios. Esto no certifica la 4FN de toda la tabla: conserva los valores únicos del documento y teléfono ya registrados y evita duplicar nombres de países/prefijos en columnas nuevas.

## Interacción y persistencia

El botón Otro país / escribir, independiente para documento y celular, abre un selector con búsqueda por nombre. Se requieren al menos dos letras para listar resultados; no se generan ni descargan banderas de todos los países al abrir. Es obligatorio elegir un resultado reconocido. Un texto inventado o un nombre literal no se persiste como código. Volver a países frecuentes limpia la selección pendiente y conserva el número para corregirlo; la modificación definitiva solo ocurre al guardar.

Francia se conserva como FR en pais_documento/pais_telefono; su nombre y +33 se obtienen del catálogo local. El teléfono conserva su forma internacional en la columna existente y se recupera la parte nacional al editar. Los países no frecuentes recuperados abren el modo de búsqueda correspondiente sin inferir países de registros antiguos. El país emisor y el del celular siguen siendo independientes.

El catálogo local de 250 países/territorios usa nombres españoles de i18n-iso-countries7.14.0 y datos telefónicos de libphonenumber-js1.13.15;243 entradas tienen datos telefónicos compatibles. Incluye XK como código de uso extendido; no pretende establecer reconocimiento político. Nombres/códigos/patrones y licencias MIT se conservan en app/Modulos/Identidad/Dominio/Datos. No se agrega dependencia runtime ni se envía la búsqueda o la identidad a servicios externos. Los 13 perfiles previos conservan prioridad, especialmente Bolivia 5/6/7.

Los países añadidos disponen de DOCUMENTO_NACIONAL (formato general alfanumérico máximo30) o PASAPORTE. No se inventan reglas documentales nacionales que no están investigadas. El formato telefónico usa los patrones/longitudes versionados y límite E.164; no comprueba asignación, titularidad u operadora. Género y los estados rojo/verde aprobados se mantienen.

ui.selector añade searchOnly opcional y búsqueda de alias sin acentos, preservando consumidores anteriores. Banderas SVG locales originales flag-icons7.6.0, licencia MIT existente; no se cambia la fuente o la paleta. El panel se mantiene en la raíz del modal para evitar recorte y conservar teclado/foco.

## Verificación

Se prueba el alta/edición con Francia/FR/+33 y columnas idénticas antes/después; se rechaza Francia literal en el backend y países sin plan de telefonía. Se comprueban todos los códigos/banderas, compilación de patrones y longitud internacional. Las pruebas visuales añaden Otro/búsqueda/nombre no reconocido/Francia/bandera/+33/validación en1440,1280,1024,768,390 px, conservando la regresión del directorio. Resultados del cierre:

- SQLite: 54 pruebas, 2016 aserciones correctas. Filtro: IdentificacionInternacionalPersonalTest|PersonalPaisMigracionTest|GerenciaPersonalRenovacionTest|ContratoPersonalInstitucionalTest.
- PostgreSQL desechable: las mismas 54 pruebas y 2016 aserciones correctas mediante scripts/probar-postgres.ps1.
- Controles JavaScript: 11 pruebas correctas (node --test tests/Frontend/controles-institucionales.test.js).
- Recorrido E2E sintético completo correcto: 1440, 1280, 1024, 768 y 390 px, 220.95 s. Búsqueda, rechazo de nombre inventado, selección FR, bandera, prefijo +33 y validación del celular; sin desbordamiento horizontal, errores JavaScript ni respuestas HTTP fallidas. Capturas revisadas en escritorio y móvil. Comando: RM_GERENCIA_QA_PERSONAL=1 node --test tests/Frontend/gerencia-renovacion-e2e.test.js.
- npm run build correcto; aviso previo de tamaño de chunk, sin error de compilación.

La CI completa no se ejecuta en esta entrega. No hay migración ni escritura de datos operativos. Las evidencias de ejecución y capturas sintéticas permanecen en output y no se publican.
