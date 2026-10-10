# EvaluacionTecnicaSolicitada v1

El evento transporta `SolicitudEvaluacionTecnicaV1` e implementa `ShouldDispatchAfterCommit`. La prueba de contrato demuestra entrega tras el commit exterior, ausencia de entrega dentro de una transaccion anidada y ausencia de entrega tras rollback.

Ningun proceso clinico publica este evento y no hay listener/job clinico activo. Para la futura cola deberan aprobarse: productor y momento de valoracion finalizada, deduplicacion durable por identidad/clave, numero y clasificacion de reintentos, recuperacion de fallos, trazabilidad del transporte y revision profesional del resultado. Las operaciones criticas de admision, cama, prescripcion y administracion conservan su ejecucion transaccional sin depender de esta cola.
