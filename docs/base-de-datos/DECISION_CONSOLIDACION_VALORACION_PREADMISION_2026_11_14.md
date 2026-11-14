# Consolidación de la valoración inicial de Enfermería

El 14/11/2026 la propietaria autorizó retirar la tabla física `valoraciones_enfermeria_preadmision` e integrar su bloque opcional en `preadmisiones`. La autorización se limita a esta relación.

## Contrato

Cada fila de `preadmisiones` sigue representando una solicitud y admite como máximo una valoración. La restricción `UNIQUE(cod_preadmision)` del esquema anterior garantizaba ese máximo. El bloque conserva un código alternativo único `cod_valoracion_enfermeria` y columnas escalares `valoracion_*`. Una solicitud sin valoración tiene todas las columnas del bloque en NULL. Una valoración presente conserva las claves foráneas a personal y usuario, los campos obligatorios originales y las restricciones clínicas existentes.

La edición conserva el código, la autoría y la fecha del registro inicial. La bitácora identifica al actor que hizo la edición. Las columnas clínicas quedan excluidas de la serialización y de la asignación masiva de la preadmisión. El formulario sigue aplicando los permisos existentes. La transición conserva una fila por solicitud, sin convertir registros independientes en columnas repetidas ni usar JSON.

## Transición y comprobación

La migración `2026_11_14_000001_consolidate_valoraciones_enfermeria_preadmision` compara las filas proyectadas en ambas direcciones antes de retirar la tabla. La reversión reconstruye la relación y vuelve a comprobar la igualdad. El correlativo VEN conserva su secuencia.

Instalada en `remembermind_dev` el 14/11/2026 con respaldo privado comprobado mediante el catálogo de PostgreSQL. Se preservaron íntegramente las 2 valoraciones y las 38 solicitudes existentes, junto con las asignaciones de permisos.

Inventario instalado: **80 operativas + 25 expertas = 105 de negocio**, más **16 técnicas**, para **121 tablas físicas**. El esquema de prueba sin las seis extensiones de Gerencia contiene 99 tablas de negocio.
