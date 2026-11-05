# Personal: límite de 60 años — 05/11/2026

La propietaria sustituye el máximo anterior de 50 por **60 años inclusive** en el formulario de personal. El mínimo se mantiene en 18. La regla compartida, el calendario HTML, la ayuda y el error por campo aceptan los 60 años completos y rechazan desde el cumpleaños 61. El cambio se aplica al alta y edición, sin modificar fechas guardadas, estructura o permisos.

Para el 05/11/2026 se admiten nacimientos del 06/11/1965 al 05/11/2008, inclusive. Las pruebas verifican ambos extremos, el día fuera de cada límite, el calendario y el guardado real en base de pruebas. También se conservan las comprobaciones de fechas imposibles y nacimiento bisiesto.

La opción de género conserva el texto ortográficamente correcto «Femenino», sin tilde; el código persistido sigue siendo F. La etiqueta «Género» mantiene su tilde. No hay cambio de catálogo ni significado.

Esta decisión prevalece sobre el límite histórico de 50 de los informes anteriores de esta fecha. Evidencia técnica: `output/personal-edad-60-*`.

Verificado: 5 pruebas y 29 aserciones correctas tanto en SQLite como en PostgreSQL desechable; build correcto en 5.47 s. No se repite la matriz visual completa: solo cambian los límites y el texto de ayuda, con los atributos HTML comprobados en la prueba del formulario.
