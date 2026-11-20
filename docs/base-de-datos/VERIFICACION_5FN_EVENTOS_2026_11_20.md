# Ocupación, respuestas y medicación después del lote 51

El 20/11/2026 se instaló la migración `2026_11_20_000008_normalize_event_contexts` en PostgreSQL `remembermind_dev`. Las tres tablas físicas de eventos sustituyen a las tres anteriores. Las vistas con los nombres que usa la aplicación permiten leer y registrar los mismos actos.

| Relación física | Hecho que identifica una fila | Origen del dato según su contexto |
| --- | --- | --- |
| `estancias_cama` | Una estancia de una admisión en una cama | El residente procede de la admisión. Un traslado conserva la estancia anterior. |
| `capturas_instrumento` | Una respuesta en una aplicación de un instrumento | Si se eligió una opción, esta identifica la pregunta. Una respuesta libre guarda su pregunta directamente. |
| `dosis_registradas` | Una administración u omisión de medicación | Un horario identifica la prescripción. Una dosis sin horario guarda su prescripción directamente. |

## Comprobaciones

- Se hizo un respaldo previo. En una copia restaurada se ensayaron instalación, inserción de los casos ausentes en la base activa, reversión con esos casos y reaplicación.
- PostgreSQL rechazó una segunda respuesta a la misma pregunta y aplicación, aunque se intentó registrar por la otra vía. Dos dosis del mismo horario en fechas distintas se conservaron y el intento de duplicar el día se rechazó.
- Dos conexiones independientes intentaron ocupar camas distintas para el mismo residente en admisiones diferentes. La segunda esperó el bloqueo y, después de confirmar la primera, su reintento fue rechazado. La prueba de admisión concurrente sobre una misma cama también produjo una sola ocupación.
- En la instalación activa se compararon los valores y multiplicidades de **16.165 filas en las 98 relaciones de negocio** mediante `EXCEPT ALL` en ambos sentidos, dentro de la misma transacción. Hubo **cero diferencias**. La aplicación pudo consultar 30 residentes, 30 ocupaciones activas, 498 respuestas y 51 cuentas. Los ejemplos de medicación se ejercitaron en la copia.
- La auditoría posterior de solo lectura contó **77 tablas operativas, 21 expertas y 16 técnicas**. Todas las claves foráneas de negocio figuran validadas en PostgreSQL. El inventario del sistema experto verifica el origen de la pregunta contra la vista y contra las claves físicas de la captura y la opción.
- En SQLite se comprobó el ciclo con respuestas de ambos tipos y dosis en días sucesivos. La reversión conserva la unicidad por horario y fecha. Una reconstrucción histórica de SQLite había omitido la fecha en ese índice.

## Alcance del dictamen

Quedan resueltas las tres dependencias señaladas en la revisión posterior al lote 50. La conservación de las filas y la comprobación de estos contratos no prueban por sí solas la 5FN de cada relación posible. La auditoría distingue la identidad histórica de quien registró un acto, los valores de medición originales y las dependencias que deben cumplirse para cualquier estado válido. Para una certificación global sigue siendo necesario cerrar el inventario semántico de dependencias de reunión por relación.

La evidencia completa de metadatos y agregados de esta instalación está en `storage/app/private/auditoria-5fn-poslote51.json`. Para obtener otro corte de solo lectura se usa `php scripts/auditar-normalizacion-negocio.php`.
