# Personal: dirección asistida y edad — 05/11/2026

## Decisión y alcance

La propietaria solicita país/departamento/zona/calle dependientes, alternativa «Otra» y edad de 18 a 50 años en el formulario de personal. Se conserva la dirección textual de `personal.direccion` (255 caracteres). No se agregan columnas, tablas, permisos, relaciones, servicios externos ni información por suposición.

La captura asistida es una ayuda para redactar esa dirección, no una jerarquía geográfica persistida. Sus componentes no quedan disponibles como filtros independientes ni se reconstruyen analizando el texto de direcciones antiguas. La evaluación de la normalización de una futura dirección estructurada corresponde a otra decisión de modelo; esta entrega no sustituye relaciones por JSON/EAV ni certifica la 4FN de toda la tabla.

## Interacción

- Dirección conserva su editor de texto y abre «Completar por país, zona y calle» cuando se necesitan sugerencias. El formulario mantiene los componentes compartidos `ui.field` y `ui.selector`, colores y estados rojo/verde aprobados. El selector añade `context` opcional: una cadena dependiente se reinicializa al cambiar sus padres y evita conservar el estado deshabilitado anterior; los consumidores sin contexto conservan su clave previa.
- El país de residencia es independiente de los países del documento y teléfono. La ayuda empieza en Bolivia sin asignar ese país a ninguna columna de la persona ni inferirlo de una dirección guardada.
- País → departamento/región → municipio/ciudad → zona/barrio → calle/avenida. Cambiar un padre vacía sus descendientes y sus errores/estados válidos. «Otra» permite escribir cada nivel no disponible; el número de vivienda es opcional.
- Las sugerencias se verifican contra su padre en el servidor, recalculando las opciones sin confiar en las listas públicas del navegador.
- «Usar esta dirección» valida y compone una dirección postal legible en mayúsculas. El texto anterior se conserva hasta ese momento y también ante un error. Una dirección compuesta que excede 255 caracteres se rechaza para abreviarla; no se trunca. La aplicación de la ayuda no guarda ni crea personal: la persistencia continúa en la operación final del registro.
- Al editar se conserva íntegro el texto existente. Se retira del formulario la antigua reconstrucción mediante expresiones regulares y el valor supuesto S/N.

## Cobertura de sugerencias

Bolivia muestra sus nueve departamentos. Los municipios, zonas y calles son las sugerencias parciales que ya tenía este formulario para La Paz y El Alto. Se restringen a sus padres; los nombres no constituyen una verificación postal o geográfica del domicilio. Se retiran las calles ficticias de fallback «AV. PRINCIPAL»/«CALLE SECUNDARIA». Los países o lugares sin cobertura ofrecen escritura mediante «Otra»; no se promete un catálogo mundial de calles. No se transmiten domicilios a terceros.

## Edad aprobada

Una regla compartida en `DatosPersonalesTrabajador` se usa en validación por campo, pasos y `GuardarPersonalInstitucional`, para alta y edición. El HTML del calendario recibe los mismos límites `min`/`max` y ayuda «De 18 a 50 años, inclusive».

Se aceptan 18 cumplidos y todo el año de edad 50; desde el cumpleaños 51 se rechaza. Para 05/11/2026, el intervalo de nacimiento es 06/11/1975–05/11/2008, inclusive. También se valida el formato y la existencia de la fecha. No se modifican fechas guardadas, cuentas o situaciones laborales por esta regla de captura.

## Verificación

Las pruebas nuevas cubren cumpleaños 18/51, fecha imposible, nacimiento bisiesto, validación HTML/Livewire y rechazo del backend sin escritura. También cubren cadenas geográficas válidas, país independiente, limpieza de descendientes, rechazo de zona/calle de otro municipio y dirección extranjera escrita mediante «Otra».

Las evidencias técnicas se conservan en `output/personal-direccion-edad-*`; no se incorporan datos operativos ni capturas reales al commit.

Resultados: 60 pruebas y 2069 aserciones correctas en SQLite; las mismas 60/2069 en PostgreSQL desechable mediante `scripts/probar-postgres.ps1`. El alcance incluye identidad internacional, migración previa de países, contrato de personal, formulario y regla de nacimiento. La CI completa queda fuera de esta entrega.

Cierre de UI: después de incorporar el contexto dependiente del selector se repiten las tres pruebas focales del formulario (37 aserciones correctas) y las 11 pruebas JavaScript de controles. `npm run build` correcto (4.61 s), con el aviso previo de tamaño de chunk. El recorrido E2E completo `RM_GERENCIA_QA_PERSONAL=1 node --test tests/Frontend/gerencia-renovacion-e2e.test.js` pasa en 236.46 s: 1440/1280/1024/768/390 px, selección de departamento/municipio/zona/calle, aplicación explícita de dirección, regresión de países/teléfono y directorio, sin errores JavaScript ni respuestas fallidas. Se revisan las capturas del asistente en los cinco anchos y el color final en escritorio/móvil. Dictamen visual/funcional del alcance: PASS; no equivale a verificar domicilios reales ni a una CI completa.
