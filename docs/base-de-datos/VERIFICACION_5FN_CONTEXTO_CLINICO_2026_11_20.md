# Contexto clínico restante — evidencia del 20/11/2026

Sexta migración de la [normalización autorizada](DECISION_NORMALIZACION_5FN_2026_11_20.md), en el checkout aislado. **No instalada en la base operativa; no certifica 5FN global.**

## Alcance y conservación

Se separa el sujeto directo de las filas cuyo residente se obtiene desde una atención en seis familias: `derivaciones`, `documentos`, `registros_clinicos`, `registros_textuales_clinicos`, `valoraciones_psicopedagogicas` y `valoraciones_petersen_originales`.

Las asociaciones directas conservan las identidades originales y usan la misma exclusión mediante clave generada y FK diferida de los bloques anteriores. Se preservan todos los CHECK de cada subtipo, alias generados y referencias. No se hacen opcionales datos clínicos obligatorios ni se crean atenciones para completar una relación.

La excepción legítima es el documento administrativo sin residente: puede pertenecer a personal, contacto o preadmisión. Se conserva NULL al crear, validar o cambiar su estado, sin insertar una asociación vacía. Un documento clínico sigue requiriendo sujeto. Los pases de turno también lo requieren: no se convierten en pases globales.

Petersen conserva su cadena de residente y elemento. La auto-FK compuesta se reemplaza por una FK al anterior y una guarda que contrasta el sujeto canónico y el elemento, bloqueando el anterior en PostgreSQL. Se mantiene la inmutabilidad original. Para reconstruir la columna al revertir se suspende exclusivamente la guarda inmutable declarada, dentro de la transacción y con las tablas bloqueadas; se restaura antes de finalizar.

## Contratos por motor y variante

El manifiesto distingue el núcleo y la extensión laboral documental ya instalada en PostgreSQL, que añade seis atributos. No se descartan esos campos ni se modifica su significado. La comparación de columnas incluye tipo, nulabilidad, valores por defecto y generación; el ordinal no determina su semántica.

PostgreSQL 18 puede devolver la misma conversión de un arreglo `varchar` a `text` en dos formas al restaurar `pg_dump`. Se conservan dos conjuntos exactos de restricciones observadas para los CHECK documental y Petersen; no se ignoran CHECK desconocidos. Los nombres internos de restricciones `NOT NULL` pueden variar al reconstruir una columna: la nulabilidad se coteja en los atributos, manteniendo estricta la comparación de FK, UNIQUE y CHECK.

Se ampliaron las huellas de guardas a once familias: 77 guardas SQLite y 33 PostgreSQL, más la variante documental instalada. Se contrasta el enlace completo del trigger, su habilitación y su función; la inspección no lee filas clínicas.

## Evidencia terminada

- Integración SQLite: **19 casos / 828 comprobaciones**, sin errores ni fallos; una comprobación exclusiva de PostgreSQL se ejecutó allí. Incluye documentos sin sujeto, cadenas, reversión poblada, inventario, consolidaciones históricas e incorporación experimental.
- Integración PostgreSQL: **12 casos / 663 comprobaciones**, sin errores ni fallos. Dos casos exclusivos de SQLite se ejecutaron en ese motor. La negativa de cadena comprueba el contrato, con un código dentro de la longitud permitida.
- Copia poblada PostgreSQL con las seis migraciones y todas las huellas: reconstrucción de **84 tablas originales / 15.297 filas**, tras subir, revertir y reaplicar, mediante `EXCEPT ALL` en ambos sentidos: **cero pérdidas y cero filas adicionales**.
- Resultado de la copia: **97 tablas de negocio + 16 técnicas = 113 físicas**. Se verificaron tanto sus datos como las protecciones SQL después de ambas instalaciones.

## Pendientes

La carga HTTP de cinco fotografías de 8 MB pasó por separado con **1 caso / 44 comprobaciones**. El plazo anterior de 30 s era inferior a los 32,901 s medidos en una reproducción mínima con el servidor PHP de Windows, sin Laravel ni BDD. Se amplió únicamente el plazo del cliente de QA a 120 s y el del proceso de QA a 150 s; se conservan tamaños, cantidad, CSRF, firma y persistencia privada. La ejecución extensa iniciada con el plazo anterior no se presenta como verde.

Se está verificando el uso de fotografías de heridas, documentos, pase de turno y captura Petersen. La ejecución anterior extensa de captura instrumental fue interrumpida al comenzar este bloque; no se presenta como completa. Las declaraciones semánticas, respuestas, administración, ocupación y revisión integral permanecen pendientes en el plan. No se cambiaron permisos ni reglas del experto y no se migró o reinició la base operativa.
