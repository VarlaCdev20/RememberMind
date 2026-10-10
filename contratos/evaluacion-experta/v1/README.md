# Solicitud tecnica v1

La implementacion ejecutable es `SolicitudEvaluacionTecnicaV1`. `solicitud-tecnica.json` contiene un ejemplo sintetico probado. El contrato conserva los identificadores y la fecha de corte de `ContextoEjecucion`; exige exactamente siete campos, contrato entero 1, uso PRUEBA_TECNICA, identificadores textuales no vacios de hasta 20 caracteres y fecha ISO 8601 con zona horaria. Rechaza campos adicionales, versiones desconocidas y cualquier activacion clinica.

`claveIdempotencia()` identifica de forma estable el mismo mensaje. Esta clave por si sola no garantiza deduplicacion durable de una cola. La persistencia tecnica existente rechaza una evaluacion ya conservada y mantiene sus bloqueos/transaccion; una nueva identidad representa otra ejecucion tecnica.

Faltan decisiones aprobadas para publicar solicitudes desde valoraciones clinicas, obtener hechos clinicos fuera del piloto restringido, politica de reintentos y recuperacion, almacenamiento durable del estado del transporte, revisiones profesionales y activacion del conocimiento. No se crean tablas ni se conectan consumidores clinicos para anticipar esas decisiones.
