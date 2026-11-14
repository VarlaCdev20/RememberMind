# Consolidación de registros clínicos, asignaciones y componentes de reglas

La propietaria autorizó estas tres fusiones el 14/11/2026. El inventario instalado de partida tiene 72 tablas operativas, 23 expertas y 16 técnicas. Con las tres migraciones pasa a 68 operativas, 22 expertas y 16 técnicas, para un total de 90 tablas de negocio y 106 físicas.

| Destino | Orígenes | Diferencia |
|---|---|---:|
| `registros_clinicos` | `antecedentes_clinicos`, `diagnosticos`, `alergias`, `dispositivos_clinicos` | −3 |
| `asignaciones_jornada` | `asignaciones_personal`, `asignaciones_residente_jornada` | −1 |
| `componentes_regla_experta` | `condiciones_regla_experta`, `consecuencias_regla_experta` | −1 |

Cada fila conserva el código original y representa una sola ocurrencia clínica, una sola asignación o un solo componente de regla. El discriminador y las restricciones SQL hacen mutuamente excluyentes los datos de cada tipo. Las relaciones independientes siguen en sus tablas: la pertenencia a un área y la asignación de un residente pueden registrarse sin multiplicar filas entre sí. Cada condición o consecuencia mantiene un solo valor semántico y sus restricciones de unicidad originales. Dentro de cada grupo, la fusión no introduce dependencias multivaluadas independientes sobre el mismo hecho. Esta comprobación concierne a las tres relaciones consolidadas.

Las claves generadas permiten que los soportes Petersen solo apunten a antecedentes o diagnósticos del tipo esperado, la bitácora solo a asignaciones de personal y las evaluaciones de condiciones solo a condiciones. Los diagnósticos conservan la relación entre atención y residente. Se preservan la unicidad de asignaciones activas, las claves del catálogo y los permisos.

Cada migración copia y compara ambos conjuntos de filas dentro de una transacción, reorienta las referencias y luego retira las tablas anteriores. Al revertir en orden inverso, se reconstruyen los orígenes, índices y referencias y se repite la comparación. La extensión independiente de historia de Gerencia admite su instalación antes o después de la fusión de asignaciones.


## Evidencia local del 14/11/2026

Antes de confirmar se verificó una copia de respaldo en el directorio privado de la aplicación. El ensayo de ida y vuelta en PostgreSQL conservó 1 dispositivo, 1.389 asignaciones de personal, 1.145 asignaciones de residentes, 90 condiciones y 32 consecuencias. Antecedentes, diagnósticos y alergias estaban vacíos en el corte de instalación. Las pruebas con datos sintéticos comprobaron las cuatro clases clínicas, las referencias por tipo y las asignaciones activas en SQLite y PostgreSQL.

Tras instalar se comprobó el inventario de 106 tablas físicas, la integridad de las tres tablas dependientes y la conservación de los permisos por rol. Se leyeron las seis versiones expertas existentes.