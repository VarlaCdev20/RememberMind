# Selector de dirección de personal — OpenStreetMap, 05/11/2026

La propietaria autorizó usar OpenStreetMap tras descartar Google Maps con clave y facturación. Alcance: formulario de alta/edición de personal de Gerencia. Se conserva el diseño, los permisos y la columna `personal.direccion`; no se añaden coordenadas al expediente ni se altera el esquema. La caché técnica temporal conserva respuestas del servicio.

## Flujo integrado

1. Elegir país, departamento/región y opcionalmente municipio.
2. Pulsar **ELEGIR EN EL MAPA**. Una búsqueda de esa región centra el mapa Leaflet.
3. Pulsar el mapa, o moverlo con flechas y **MARCAR EL CENTRO**.
4. Pulsar **CONSULTAR DIRECCIÓN DEL PUNTO**. La sugerencia queda separada de la dirección del formulario.
5. Revisar/corregir el texto y pulsar **USAR ESTA UBICACIÓN**. Se actualiza la dirección y, cuando existe, se refleja barrio/calle/número en los campos. Los valores ajenos al catálogo usan la opción Otra existente. No se inventan componentes ausentes.
6. La dirección solo persiste al confirmar el alta o edición normal; no al consultar el mapa. Cambiar la región invalida la sugerencia previa.

La sugerencia de OpenStreetMap puede ser aproximada o incompleta; no certifica residencia ni existencia de número domiciliario. Se mantiene la entrada manual y el enlace opcional a Google Maps. No se utiliza geolocalización del dispositivo.

## Integración y límites

Livewire reautoriza cada operación con `AutorizarGestionPersonal`; exige cuenta activa, permiso, alcance de destino y ausencia de simulación. Se validan coordenadas, contexto y longitud del texto. La propuesta remota se conserva bloqueada en el estado de interacción y se rechaza si cambia el contexto. No se añade una ruta pública, permisos ni una tabla.

Leaflet **1.9.4** (BSD-2-Clause), fijado en package-lock, se distribuye mediante Vite; su JavaScript se carga al abrir el mapa. Nominatim se consulta desde el servidor con identificación RememberMind, caché de 24 horas, bloqueo global y separación mínima de dos segundos entre consultas. No hay autocompletado, sondeos periódicos, consultas al arrastrar ni descargas de mapas anticipadas. Las imágenes usan la caché HTTP del navegador y muestran atribución visible.

Fuentes y condiciones del servicio público:
- [Política de Nominatim](https://operations.osmfoundation.org/policies/nominatim/): uso moderado, máximo agregado de una consulta por segundo, identificación, atribución y ausencia de autocompletado. La aplicación limita más estrictamente y conserva caché.
- [Política de imágenes del mapa](https://operations.osmfoundation.org/policies/tiles/): URL oficial HTTPS, Referer del navegador, caché y atribución. No ofrece garantía de disponibilidad.
- [Leaflet estable](https://leafletjs.com/reference-1.9.4.html).

`config/services.php` permite cambiar el proveedor con `PERSONAL_GEOCODING_URL` y `PERSONAL_MAP_TILES_URL`, sin modificar código. Mantener un proveedor compatible y su atribución aplicable. No se guardan claves ni se modifica `.env`. Se transmite solamente el contexto geográfico o punto consultado a Nominatim; el navegador solicita imágenes de la región. No se envían identidad, documento, correo ni teléfono.

## Evidencia de cierre

- SQLite y PostgreSQL desechable: suite relacionada de **45 pruebas, 426 aserciones**, aprobada en ambos. Tras ampliar el negativo de cuenta suspendida, suite focal de mapa **7 pruebas, 48 aserciones** aprobada en ambos motores.
- JavaScript: cinco pruebas aprobadas entre normalización, vista previa, confirmación explícita, errores y descarte de respuestas tardías.
- E2E real de Blade/Livewire/Leaflet con respuestas externas sintéticas: un recorrido aprobado en 1440, 1280, 1024, 768 y 390 px. Consulta separada de confirmación, dirección revisada, atribución, reflujo, foco y selección por teclado. Sin errores JavaScript ni respuestas HTTP fallidas. Imágenes de prueba explícitamente rotuladas; no se confunden con cartografía real.
- Inspección visual de las capturas desktop/móvil: mantiene paleta y tipografía, campo de revisión amplio y alternativa manual. No se acredita una auditoría global de accesibilidad.
- Vite build aprobado. `npm audit` informa seis vulnerabilidades altas de dependencias previas (Vite/Puppeteer y transitivas); Leaflet no aparece entre los paquetes señalados. No se ejecuta una actualización transversal en esta tarea.
- Prueba de conexión real desde Laravel a Nominatim con búsqueda pública de La Paz: centro válido. Imagen pública de mapa con identificación y Referer: HTTP200 sin bloqueo. Un intento inicial desde PowerShell agotó el tiempo; la prueba de Laravel posterior respondió.
- No se consultaron domicilios personales reales, no se enviaron correos ni se modificaron registros institucionales. La prueba visual completa usa datos sintéticos, no acredita todas las regiones del mundo ni disponibilidad continua. CI completa pendiente.
- Revisión de seguridad de fase separada: sin nuevo bypass de permisos ni exposición de datos de identidad; las consultas denegadas no envían HTTP. Sin cambios de reglas clínicas, BDD o legacy ejecutable.

Evidencias locales: `output/personal-mapa-*20261105*` y `output/personal-mapa-20261105/`. Se excluyen del commit los artefactos de prueba y los cambios concurrentes ajenos.
