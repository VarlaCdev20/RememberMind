# Consolidación de estudios clínicos y vínculos de evidencia

La propietaria autorizó dos fusiones el 14/11/2026. El inventario resultante es de 72 tablas operativas, 23 expertas y 16 técnicas. Son 95 tablas de negocio y 111 físicas.

- `registros_estudio` conserva una fila por resultado de componente o por informe profesional. Mantiene la clave foránea al estudio, la referencia al componente en los resultados y el autor y fecha en los informes. La restricción de unicidad permite un resultado por componente de cada estudio y varios informes por estudio.
- `vinculos_evidencia_experta` conserva una fila por participación de evidencia en un criterio o por soporte de una condición. Cada fila exige una sola clase de destino. Las ramas de evidencia y de hecho conservado del criterio mantienen sus restricciones y las guardas de pertenencia y concurrencia. Los soportes de condición mantienen sus claves foráneas y su unicidad original.

Las relaciones independientes conservan una fila por hecho o vínculo, con sus códigos originales y atributos tipados. Las migraciones `000009` y `000010` comparan las filas en ambos sentidos antes de retirar las tablas y reconstruyen las relaciones originales al revertir. Los permisos y el significado de las evaluaciones se mantienen.

Instalado en PostgreSQL local el 14/11/2026 con respaldo previo. Se conservaron 12 vínculos de criterio y 12 soportes de condición. Se leyeron seis versiones de conocimiento y dos resultados institucionales conservados. Los estudios estaban vacíos en la base local. Las pruebas ejercitaron el registro de un resultado y dos informes para un mismo estudio, incluidos el rechazo de duplicados y la reversión con datos.
