# Catálogo local de países y telefonía

Datos de i18n-iso-countries7.14.0 (nombres en español, MIT) y libphonenumber-js1.13.15 (metadata.max.json, MIT, derivada de Google libphonenumber). Catálogo de250 países/territorios; incluye XK como código de uso extendido, sin afirmar reconocimiento político. Prefijos y patrones nacionales/móviles disponibles en243 territorios. Instantánea descargada el05/11/2026; no consulta servicios externos durante la captura.

Fuentes: https://github.com/michaelwittig/node-i18n-iso-countries y https://github.com/catamphetamine/libphonenumber-js/blob/master/METADATA.md. Versiones fijadas en las URL de unpkg usadas para generar el archivo; licencias adjuntas. Los13 perfiles previos de RememberMind prevalecen (incluido el inicio5/6/7 de Bolivia aprobado por la propietaria).

Los países adicionales usan formato documental general DOCUMENTO_NACIONAL/PASAPORTE; no se inventa formato nacional específico. Se comprueba formato telefónico, no asignación/titularidad/operadora. No se agrega dependencia al runtime.

Instantáneas exactas: https://unpkg.com/i18n-iso-countries@7.14.0/langs/es.json y https://unpkg.com/libphonenumber-js@1.13.15/metadata.max.json. Se toma el prefijo internacional, el patrón móvil cuando existe (en su defecto el patrón nacional), sus longitudes posibles y el máximo compatible con 15 dígitos internacionales. Los nombres alternativos se conservan como alias de búsqueda.
