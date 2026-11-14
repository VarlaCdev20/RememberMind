# Consolidación de cuidados y criterios del sistema experto

La autorización del 14/11/2026 cubre tres fusiones acotadas. El inventario resultante es de **77 tablas operativas y 24 expertas**, para **101 de negocio y 117 físicas** contando las 16 técnicas.

| Origen | Almacenamiento | Grano y restricciones |
| --- | --- | --- |
| Eliminación | `registros_consumo` | Una fila por evento de alimentación, hidratación o eliminación. El discriminador y las restricciones separan los campos de cada tipo. |
| Movilidad y conducta | `registros_observaciones_cuidado` | Una fila por observación de movilidad o conducta. La atención es opcional y, cuando existe, debe pertenecer al mismo residente. |
| Criterio de dominio | `nodos_semanticos` | Bloque escalar opcional, con máximo de un criterio por nodo y código propio único. Las consecuencias siguen apuntando a ese código mediante una clave foránea. |

Se conservan los códigos clínicos, la autoría, las fechas, los estados y las relaciones. Alimentación e hidratación siguen siendo eventos independientes aunque se registren juntos. Las ocurrencias repetidas de cualquiera de los tipos permanecen en filas independientes. El criterio tiene dos identificadores alternativos cuando existe, su código original y el nodo único que lo contiene. Los atributos siguen dependiendo de la identidad del evento o del nodo, y el diseño evita combinar conjuntos independientes de observaciones o listas de reglas en una fila.

Las tres migraciones del 14/11/2026, de `000003` a `000005`, comparan las columnas originales y sus proyecciones en ambos sentidos mediante `EXCEPT`. Rechazan cambios de estructura o referencias imprevistas, y reconstruyen las relaciones anteriores al revertir. Los modelos y permisos de cada tipo conservan sus contratos. El motor sigue recibiendo los mismos criterios y consecuencias.

Instalación verificada el 14/11/2026 en el PostgreSQL local, con respaldo privado y ensayo previo de reversión sobre los datos existentes. Se preservaron 75 registros de alimentación, 135 de hidratación, 135 de eliminación, 135 de movilidad, 135 de conducta y 44 criterios. Se comprobaron las asignaciones de permisos, las consecuencias y la lectura de las seis versiones de conocimiento.
