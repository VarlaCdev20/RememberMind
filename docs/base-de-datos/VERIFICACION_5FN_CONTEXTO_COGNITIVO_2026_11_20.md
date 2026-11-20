# Contexto cognitivo opcional — evidencia del 20/11/2026

Quinta migración de la [normalización autorizada](DECISION_NORMALIZACION_5FN_2026_11_20.md). Implementada en `RememberMind-5fn`, sin instalar en la aplicación operativa y sin certificar 5FN global.

## Cambio y contratos preservados

`aplicaciones_instrumento`, `controles_cognitivos` y `controles_funcionales_iadl` obtienen el residente desde su atención cuando existe. Los registros sin atención conservan su sujeto en tres asociaciones directas con la identidad original como clave. No se fabrican episodios.

La migración reutiliza la transición fechada del bloque de cuidados. Sus claves generadas y FK diferidas excluyen las dos fuentes simultáneas. Se conservan puntajes, texto, estados, autoría, fechas, procedencia documental, rectificaciones y referencias existentes. Las aplicaciones mantienen su restricción de edición; IADL sigue siendo inmutable mediante el modelo. No se cambian reglas, baremos, clasificaciones ni permisos del experto.

Una capacidad compartida adapta lectura, guardado, proyecciones parciales, `fresh` y `refresh`. El residente canónico queda disponible también como atributo original hidratado para los consumidores existentes. El alias técnico se excluye de serialización y escritura, incluidas las rectificaciones. PostgreSQL bloquea el registro raíz, evitando aplicar `FOR UPDATE` al lado nullable de las relaciones.

El inventario distingue columnas físicas y campos derivados. Comprueba ambas rutas, PK, FK y alias calculado. Contrasta las 35 guardas SQLite y las 15 funciones y enlaces PostgreSQL de las cinco familias normalizadas. Una protección vacía, deshabilitada, con otra función o con eventos limitados a `UPDATE OF` se rechaza. Esta inspección no consulta filas clínicas ni certifica disponibilidad de datos.

## Evidencia terminada

- Núcleo cognitivo: **3 pruebas / 78 comprobaciones** aprobadas en SQLite y PostgreSQL: ambas rutas, aislamiento entre residentes, original hidratado, edición permitida/prohibida y reversión poblada.
- PostgreSQL integrado con el bloque de cuidados e inventario: **15 casos / 599 comprobaciones**, sin errores ni fallos. Una comprobación exclusiva de SQLite se omitió en ese motor y se ejecutó en SQLite.
- Bloqueos PostgreSQL: **1 prueba / 17 comprobaciones**. Una reproducción independiente confirma que el SQL sin `OF` falla con `0A000` y el modelo corregido funciona.
- Inspección reforzada y núcleo cognitivo en SQLite: **13 casos / 595 comprobaciones**, sin errores ni fallos; dos casos exclusivos de PostgreSQL se verificaron allí.
- Negativas de guardas PostgreSQL, incluida limitación `UPDATE OF`: **1 prueba / 6 comprobaciones**, aprobada.
- Migración histórica e incorporación experimental: **9 pruebas / 91 comprobaciones**, aprobadas en SQLite.
- Dashboard de Psicología: **3 pruebas / 28 comprobaciones**. Adaptador instrumental: **18 pruebas / 73 comprobaciones**, aprobados en SQLite.
- Conservación del experto y adaptador IADL: **42 pruebas** aprobadas en SQLite. El lote inicial de 49 detectó un fallo de rectificación por el alias calculado; corregido y repetido: **1 prueba / 11 comprobaciones**, aprobada. No se presenta el lote inicial como completamente aprobado.
- Copia poblada PostgreSQL con las cinco migraciones y las últimas huellas: reconstrucción de **84 tablas originales / 15.297 filas**, `EXCEPT ALL` en ambos sentidos, tras migrar, revertir y reaplicar: **cero pérdidas y cero filas adicionales**. Contratos SQL completos reconocidos tras ambas instalaciones.
- Inventario resultante de la copia: **91 tablas de negocio + 16 técnicas = 107 físicas**.

## Pendientes

Se ejecuta la regresión adicional de captura institucional y migraciones históricas. La suite amplia anterior de pantallas fue interrumpida para adaptar los siguientes contratos; sus casos completados no equivalen a una suite completa aprobada. Quedan seis familias clínicas opcionales, declaraciones semánticas, respuestas, administración, ocupación y la revisión integral del plan. La base operativa conserva su estructura anterior; no se migró ni reinició.
