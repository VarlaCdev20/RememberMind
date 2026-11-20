# Normalización de negocio a 5FN · autorización del 20/11/2026

La propietaria autoriza corregir las relaciones de negocio para llevarlas a
5FN, separando tablas cuando sea necesario y sin romper el sistema. Aclara:
«prioriza 5fn y pueden llegar a 100 las tablas del sistema excluyendo las que
son tecnicas». El máximo vigente para este alcance pasa a **100 tablas de
negocio**, aparte de las **16 técnicas**. El punto de partida son 84 de negocio
y 100 físicas. La autorización no obliga a consumir el margen disponible.

Se mantienen los datos originales, códigos, autorías, fechas, historia,
permisos, competencias y reglas de inferencia. Se permiten las modificaciones
de columnas, relaciones, claves y restricciones necesarias para estas
descomposiciones. No se aprueba borrar historia, alterar reglas clínicas,
fabricar contextos ni sustituir relaciones por JSON/EAV.

La [auditoría de partida](AUDITORIA_5FN_NEGOCIO_2026_11_20.md) identifica
dependencias comprobadas y asuntos condicionales; no certifica el resto por
ausencia de errores. Cada cambio necesita proyección de lectura, escritura,
garantías de integridad y reversión verificadas. PostgreSQL y SQLite se prueban
de forma separada. La aplicación local se preserva mientras se desarrolla y
prueba en un checkout aislado.

## Instalación local del 20/11/2026

El lote 50 instaló las siete migraciones del 20/11 en PostgreSQL
`remembermind_dev`, con respaldo previo y una transacción para todo el lote.
El inventario pasó de 84 a **98 tablas de negocio**. Con las 16 técnicas
hay **114 tablas físicas**.

Antes de confirmar la transacción se reconstruyeron las 84 relaciones
originales y se compararon sus 15 297 filas en ambos sentidos con
`EXCEPT ALL`. El resultado fue cero filas perdidas y cero adicionales.
La reversión y la reaplicación se ensayaron sobre una copia restaurada.
Después de la instalación, la aplicación consultó 30 residentes, 51 cuentas,
30 documentos administrativos, 87 variables expertas y 44 criterios expertos.

La verificación corresponde a las siete migraciones instaladas. Sigue
pendiente cerrar el análisis de dependencias de respuestas de instrumento,
administraciones de medicación y ocupaciones de cama para una conclusión
global sobre la 5FN.
