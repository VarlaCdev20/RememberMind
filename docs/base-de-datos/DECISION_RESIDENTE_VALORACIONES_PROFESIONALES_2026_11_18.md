# Residente de las valoraciones funcionales y nutricionales

La propietaria autorizó retirar `valoraciones_profesionales.cod_residente` una vez comprobada la continuidad del sistema. La atención obligatoria identifica al residente. La relación pasa de 30 a 29 atributos y el inventario permanece en **100 tablas físicas**, 64 operativas, 20 expertas y 16 técnicas. Esta decisión aplica a los tipos Funcional y Nutricional. [Auditoría del alcance](AUDITORIA_NORMALIZACION_30_ATRIBUTOS_2026_11_18.md).

## Contrato de persistencia

La migración `2026_11_18_000004_derive_professional_assessment_resident` valida la coincidencia del residente antes de retirar la columna. Conserva el código de valoración, autor, fecha, estado, campos clínicos y referencia a la medición antropométrica cuando corresponda. La clave foránea de la atención y las restricciones por tipo siguen exigiendo una atención existente. La reversión obtiene el residente de esa atención antes de restaurar la columna y sus claves anteriores. Ambos sentidos comparan las proyecciones de las filas y se ejecutan en una transacción.

El expediente, los reportes, los adaptadores del experto y la carga histórica leen el residente desde el mismo vínculo. Si un guardado recibe explícitamente un residente, lo valida contra la atención antes de escribir. Se puede cambiar la atención de una valoración dentro del mismo residente. La base rechaza cambiarla a una atención de otro residente o reasignar una atención que ya tiene valoraciones. Los permisos y la autoría aplicables se siguen comprobando en los recorridos existentes.

PostgreSQL coordina el guardado con bloqueos compartidos sobre las filas de atención. Los cambios de residente de una atención usan la lectura comprometida para que una transacción anterior no oculte valoraciones recién confirmadas. La guarda también protege las escrituras SQL directas. SQLite aplica las mismas restricciones para escrituras ordinarias, mediante sus disparadores y su serialización de las escrituras.

## Evidencia

El ensayo sobre el PostgreSQL local reconstruyó las proyecciones de las 100 tablas y recuperó exactamente sus filas tras revertir. El contrato de entradas del experto pasó la comprobación de estructura y referencias. La base local carecía de valoraciones funcionales o nutricionales, por lo que los casos con datos de ambos tipos se verifican en bases desechables.

- PostgreSQL completó 22 pruebas y 349 comprobaciones. La prueba adicional de concurrencia comprobó el bloqueo desde una inserción SQL directa y desde el modelo en sesiones independientes. Pasó sus ocho comprobaciones.
- SQLite completó 19 pruebas de regresión, seis pruebas específicas del reparto con el caso adicional de reportes y una prueba del seeder histórico de escenarios cognitivos. Los tres bloques terminaron sin fallos.
- La instalación local se aplicó después de comprobar la lectura completa de un respaldo privado. Se verificó la igualdad de las filas originales y sus proyecciones, las 100 tablas, las 424 valoraciones psicológicas, los 30 residentes y las 51 cuentas. El contrato de entradas del experto continuó verificado. La entrega se limita a persistencia y consultas de backend.

El hallazgo de dependencia funcional señalado para esta columna queda atendido por el cambio. El límite de 30 atributos para las otras siete tablas anchas y la verificación global de 5FN corresponden a la auditoría posterior.
