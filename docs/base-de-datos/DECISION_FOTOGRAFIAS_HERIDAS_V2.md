---
title: "Extensión aprobada de documentos_clinicos para evidencia de heridas"
status: APPROVED
version: "1.0"
last_reviewed: 2026-10-11
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [documentos_clinicos_heridas_extension]
verification_scope: OWNER_APPROVED_CONTRACT_AND_MIGRATION_TESTS
runtime_verified: true
---

# Fotografías de heridas y curaciones V2

La instrucción explícita de la propietaria «REMEMBERMIND — HERIDAS / CURACIONES V2 — EVIDENCIA FOTOGRÁFICA CLÍNICA», recibida el 11/10/2026, autoriza exclusivamente dos relaciones opcionales en `documentos_clinicos`:

| Columna | Tipo | Referencia | Borrado |
| --- | --- | --- | --- |
| cod_herida | string(20), nullable, indexada | heridas.cod_herida | RESTRICT |
| cod_curacion | string(20), nullable, indexada | curaciones_herida.cod_curacion | RESTRICT |

No se crean tablas. El inventario operativo permanece en 71; las tablas expertas y técnicas se cuentan aparte. No se alteran las columnas de `heridas` ni `curaciones_herida`. No se incorporan BLOB, JSON, columnas de rutas en heridas/curaciones, pivotes o relaciones polimórficas.

Migración forward: `2026_10_11_230000_add_evidencia_heridas_to_documentos_clinicos.php`. Las migraciones históricas permanecen intactas. SQLite y PostgreSQL verifican `up`, `down`, reaplicación, conteo de tablas, dos columnas exactas, índices, nulabilidad y FK restrictivas. Se aplicó únicamente esta migración a la instalación local configurada tras comprobar su conexión PostgreSQL.

## Grano, integridad y autoría

Una fotografía corresponde a una nueva fila `DocumentoClinico`, con `tipo_documento=FOTO_HERIDA`, `estado=VIGENTE` y `origen=HERIDA` o `CURACION_HERIDA`. Una fotografía directa indica solo `cod_herida`; una fotografía de curación indica solo `cod_curacion`. Los documentos generales anteriores mantienen ambas columnas nulas.

El modelo y la acción rechazan dos referencias simultáneas, referencias inexistentes y diferencias de residente. El residente procede de la herida o de la herida de la curación; el profesional procede de la cuenta autenticada y su personal activo. La exclusión y la coherencia de residente son garantías de aplicación, no un CHECK SQL nuevo. Las FK físicas garantizan existencia y restricción de borrado.

Cada nuevo registro conserva las fotografías anteriores. El modelo impide reemplazar contenido, relaciones, autoría y metadatos originales de una fotografía existente. No se incorpora edición o borrado ordinario de evidencia; cualquier futura corrección o anulación debe seguir la gobernanza clínica aplicable.

## Archivos y transacción

Se reutiliza el disco `local`, con raíz privada `storage/app/private`. Las cargas temporales Livewire también usan `local`. El nombre físico es UUID y la extensión procede del MIME real. Se admiten JPEG, PNG y WEBP hasta 8192 KB; máximo cinco archivos por captura, sin límite estructural de fotografías históricas. Las descripciones opcionales admiten 500 caracteres. Los límites se centralizan en `config/fotografias_clinicas.php`.

La validación inspecciona MIME con `finfo` y verifica cabecera de imagen. Hash SHA-256, tamaño, formato, fecha y autor se obtienen en servidor. Los previews temporales usan objetos locales del navegador; no generan URLs públicas de archivos clínicos.

La frontera `PersistirFotosHerida` incluye creación de herida/curación, documentos y auditoría en una transacción. Registra las rutas previstas antes de escribir y compensa todos los archivos ante fallos de almacenamiento, metadata o DB. Un error de compensación se reporta y nunca produce éxito. Una caída abrupta del proceso o una indisponibilidad permanente del disco no puede hacerse atómica con una transacción SQL; no se afirma esa garantía. Las operaciones con fotos no se anidan dentro de una transacción exterior en ejecución operativa.

## Autorización y entrega privada

Se reutilizan los permisos actuales `heridas.crear`, `curaciones_herida.crear`, `heridas.ver` y `curaciones_herida.ver`, con Policy contextual, cuenta activa, competencia, personal activo y alcance del residente. No se conceden permisos nuevos a roles. Enfermería requiere residente asignado y jornada efectiva para registrar. Superadministración puede consultar, pero su rol aislado no habilita adjuntar evidencia. Familia y administración no reciben acceso fotográfico por un permiso genérico de residentes.

La entrega utiliza el endpoint autenticado `admin.cuidados.fotografias.ver`, que revalida Policy, residente, documento vigente y contexto de herida. La descarga clínica anterior también delega a esa protección cuando recibe `FOTO_HERIDA`. Headers: `Cache-Control: private, no-store, max-age=0`, MIME explícito y `X-Content-Type-Options: nosniff`. El frontend recibe únicamente URL del endpoint y metadatos permitidos; nunca la ruta física.

## Reversión

`down()` retira exactamente las dos FK, sus índices y columnas; mantiene filas, archivos y atributos previos. Se prueba exclusivamente sobre bases desechables. Revertir una instalación con fotografías reales elimina sus vínculos relacionales: requiere preservar esos vínculos y los archivos antes de cualquier reversión operativa. No se ejecutó rollback sobre la instalación real.

## Operación y aceptación

En el lote del 11/10/2026 PHP local declaraba `upload_max_filesize=2M` y `post_max_size=8M`. La aplicación permite 8 MB, pero el servidor debe configurarse con `upload_max_filesize=8M` y un `post_max_size` que admita cinco archivos y campos, por ejemplo `45M`; cualquier proxy debe permitir ese volumen. En ese lote no se modificó la configuración global de PHP ni se reinició el servidor operativo.

Actualización técnica del 13/10/2026: el arranque local `composer dev` utiliza `scripts/servir-local.php` con límites por proceso 8M/45M. Se reinició únicamente el servidor PHP local verificado del proyecto y se comprobaron sus flags y `/up` (200); el `php.ini` global conserva sus valores anteriores. Cinco imágenes sintéticas de 8 MB exactos aprobaron transporte HTTP real y persistencia de prueba; CSRF/firma ausentes y exceso de tamaño fueron rechazados. Detalle y alcance en el [cierre backend](../frontend/FORMULARIO_HERIDAS_V2_RESULTADO.md#cierre-backend-de-heridas--curaciones--13102026). No modifica la extensión ni sus reglas aprobadas.

La implementación no incorpora diagnóstico, recomendación, riesgo o calificación automática de evolución. La comparación y el área calculada presentan hechos. La aceptación visual y la carga HTTP de archivos cercanos al límite requieren evidencia separada; consultar el [informe de resultado](../frontend/FORMULARIO_HERIDAS_V2_RESULTADO.md).
