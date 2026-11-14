# Consolidación de ingesta e hidratación

La autorización del 14/11/2026 cubre la unión de `registros_ingesta` y `registros_hidratacion` en `registros_consumo`. Cada fila representa una comida registrada o una toma de líquido, con el código original, residente, profesional, jornada, fecha y estado. Registrar ambos eventos desde un mismo formulario conserva dos filas.

El tipo `INGESTA` requiere el tipo de comida y excluye los atributos exclusivos de hidratación. El tipo `HIDRATACION` requiere la cantidad en mililitros y excluye los atributos exclusivos de ingesta. Los valores específicos siguen en columnas escalares. En este alcance, los atributos dependen de la clave del evento y cada conjunto independiente conserva su propia fila. Los permisos y las consultas de Enfermería siguen distinguiendo los dos tipos.

La migración `2026_11_14_000002_consolidate_registros_consumo` compara todas las columnas en ambos sentidos antes de retirar las tablas originales. Aborta ante códigos compartidos o referencias entrantes no contempladas. Su reversión reconstruye las dos relaciones y sus claves foráneas, y comprueba la equivalencia antes de retirar la tabla consolidada. Se conservan los correlativos de ingesta e hidratación.

Se instaló en `remembermind_dev` con respaldo privado comprobado mediante el catálogo de PostgreSQL. La comparación confirmó las **75 ingestas**, **135 hidrataciones** y las asignaciones de permisos previas. El inventario instalado quedó en **79 operativas + 25 expertas = 104 de negocio**, más **16 técnicas**, para **120 físicas**.
