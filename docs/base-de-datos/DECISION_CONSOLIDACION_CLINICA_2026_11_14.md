# Consolidación de sueño, mediciones y valoraciones profesionales

La autorización del 14/11/2026 comprende tres migraciones reversibles. El inventario local pasó de 117 a 113 tablas físicas, con 73 operativas, 24 expertas y 16 técnicas.

| Origen | Destino | Grano |
| --- | --- | --- |
| `registros_sueno` | `registros_observaciones_cuidado` | Una observación por fila. El sueño conserva su fecha de calendario y la hora queda ausente. |
| `signos_vitales` y `mediciones_antropometricas` | `mediciones_clinicas` | Una toma por fila, con sus valores, precisión, autor y contexto originales. |
| `valoraciones_psicologicas`, `valoraciones_nutricionales` y `valoraciones_funcionales` | `valoraciones_profesionales` | Una valoración por fila, con su atención, profesional, estado y campos de la especialidad. |

Cada fila representa un solo hecho. Los conjuntos independientes de observaciones, mediciones y valoraciones se conservan en filas diferentes y no se cruzan entre sí. Las claves y los correlativos se conservan. Las restricciones de tipo impiden mezclar atributos de especialidades o clases de registro. La fecha de sueño, la talla en centímetros y la pertenencia de la atención al residente se verifican en la base de datos.

Dos claves alternativas generadas permiten a una valoración nutricional apuntar a una toma antropométrica y a un soporte Petersen apuntar a una valoración psicológica. Se generan a partir del código y tipo del registro, sin capturarse como datos adicionales. Los permisos, los filtros de ámbito y el contrato de extracción del conocimiento mantienen su significado.

Las migraciones `000006` a `000008` comparan todos los campos en ambos sentidos mediante `EXCEPT`, bloquean cambios concurrentes durante la transición PostgreSQL y reconstruyen las tablas y referencias anteriores al revertir.

La instalación se verificó en PostgreSQL local con respaldo previo y ensayo de reversión. Se preservaron 270 registros de movilidad y conducta, 35 de sueño, 135 de signos vitales, 424 valoraciones psicológicas y los soportes Petersen existentes. Los tipos antropométrico, funcional y nutricional estaban vacíos en esa base, por lo que sus relaciones también se comprobaron con datos sintéticos en las pruebas.
