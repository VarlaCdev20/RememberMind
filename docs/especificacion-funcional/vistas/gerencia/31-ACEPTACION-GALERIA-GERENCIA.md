---
title: "Gerencia: aceptación de Galería"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SPECIFIED_ACCEPTANCE_CASES_NOT_EXECUTED
runtime_verified: false
---

# Aceptación de Galería de Gerencia

**Evidencia posterior:** el [informe39](39-ACEPTACION-REGRESIONES-Y-CASOS.md) añade regresión de edición obsoleta GL-T16 usando el original protegido del formulario, sin nueva estructura. La carrera de subida duplicada se ejecutó en PostgreSQL desechable. Los estados de «localizado/no ejecutado» de esta ficha describen su corte documental; no sustituyen los resultados posteriores ni acreditan los24 casos completos.

Contratos: [Galería](29-GALERIA-GERENCIA.md), [carga/privacidad](30-CARGA-PRIVACIDAD-GALERIA.md), [decisión de 4FN y operación](../../../base-de-datos/DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md). **24 casos especificados, no ejecutados en esta tarea.** Las pruebas del módulo ya existen y sus resultados históricos se conservan por su propio corte; no se presentan como ejecución nueva.

| Caso | Condición/acción | Resultado exigido |
|---|---|---|
| GL-T01 | Entrar desde menú de Gerencia | Entrada directa Galería, ruta autorizada y mismo módulo institucional |
| GL-T02 | Cuenta inactiva, sin permiso, Familiar o profesional ajeno | Backend deniega consulta/carga/archivo privado sin exponer payload sensible |
| GL-T03 | Gerencia con lectura y sin gestión | Consulta permitida, sin mutaciones incluso manipulando Livewire |
| GL-T04 | Superadministración o rol en preview | Lectura global según contrato; ninguna gestión automática por ese contexto |
| GL-T05 | Buscar título/descripción, cambiar estado y página | Resultados, total y filtros coherentes; búsqueda/caso/acentos verificados por motor |
| GL-T06 | Abrir foto activa con nombre largo o sin alt propio | Proporción correcta, texto alternativo útil y contexto legible sin códigos |
| GL-T07 | Cargar JPG/PNG/WebP válido y recargar página | Registro, principal/miniatura privados y metadatos recuperables, autor real |
| GL-T08 | Cargar falso archivo o extensión no admitida | Validación backend sin persistencia ni bytes nuevos huérfanos |
| GL-T09 | Exceder 2 MB, 12 MP, diez fotos o 6 MB por lote | Mensaje por límite y validación servidor; no guardado parcial antes de validar límite global |
| GL-T10 | Guardar título/fecha/alt/descripcion inválidos o desconocidos | Límites correctos; fecha desconocida permanece ausente, no se inventa fecha de toma |
| GL-T11 | Lote parcialmente fallido | Resultado por archivo y resumen exacto, fallidos disponibles para reintento; sin éxito global falso |
| GL-T12 | Repetir imagen cuyo normalizado ya existe | Una entidad, bytes/autor/metadatos previos intactos, sin archivos extra |
| GL-T13 | Dos subidas concurrentes del mismo normalizado | PostgreSQL desechable: un registro y sus dos archivos, compensación solo de nuevos |
| GL-T14 | Fallo de segunda escritura o de SQL/auditoría | Rollback/compensación adecuados, error reportado y sin confirmación falsa |
| GL-T15 | Editar datos de imagen guardada | Actualiza solo metadatos permitidos; no cambia autor, hash, rutas, bytes ni fecha de registro |
| GL-T16 | Dos formularios de edición con datos antiguos | Detectar/gestionar conflicto conforme a solución implementada; no prometer protección por solo usar lock SQL |
| GL-T17 | Archivar foto usada en bienvenida | Estado archivado, historia/bytes conservados; URL bloqueada y retirada de selección |
| GL-T18 | Consultar archivadas y reactivar autorizadamente | Placeholder/metadatos sin imagen privada archivada; reactivación sin duplicado, efecto de bienvenida explicado |
| GL-T19 | Abrir URL sin permiso, variante inválida o ruta manipulada | Denegación segura; rutas privadas no expuestas y no lectura arbitraria de disco |
| GL-T20 | JPEG orientado/EXIF y archivo faltante | Orientación/recodificación correctas, EXIF no conservado; faltante con error seguro y sin imagen de reemplazo engañosa |
| GL-T21 | Cancelar/navegar con cambios sin guardar | Confirmación de descarte, posibilidad de seguir editando y retorno de foco; guardados intactos |
| GL-T22 | Activar bienvenida y después quitar/archivar todas | Selección solo de activas elegibles, destinos autorizados y fallback sin inventar foto guardada |
| GL-T23 | Teclado, foco, validaciones y carga/error/vacío | Labels/ayudas/estado anunciados; controles con tamaños apropiados y sin depender solo de color |
| GL-T24 | 1440/1280/1024/768/390 px, tema y muchas imágenes | Rejilla/imagen completa/editor legibles, carga diferida, paginación y movimiento reducido sin desbordamiento |

## Pruebas existentes y evidencia necesaria

| Fuente | Cobertura localizada | Límite de esta entrega |
|---|---|---|
| [GaleriaInstitucionalTest](../../../../tests/Feature/GaleriaInstitucionalTest.php) | Guardado privado, duplicado, archivo/URL, permisos, auditoría/compensación, límites, parcial, preview, EXIF y 17 atributos | No reejecutado ahora; no acredita nuevas confirmaciones, conflicto de edición obsoleta o QA de cinco anchos |
| [GaleriaInstitucionalConcurrenciaPostgresTest](../../../../tests/Feature/GaleriaInstitucionalConcurrenciaPostgresTest.php) | Dos cargas solapadas, un registro y sus archivos | Necesita ejecución aislada para volver a acreditar PostgreSQL; SQLite no basta |
| [DashboardPhotoRotationTest](../../../../tests/Feature/DashboardPhotoRotationTest.php) | Rotación de fotografías de dashboard según casos existentes | No certifica todos los destinos/roles del nuevo diseño sin comprobar consumidores |
| [Informe de entrega del 17/10](../../../frontend/GALERIA_INSTITUCIONAL_2026_10_17.md) | Evidencia histórica de implementación/aplicación y límites de su corte | No es una nueva aceptación ni autorización para migrar otra vez |

Al implementar ajustes: matriz caso → archivo → prueba → resultado; revisión de payload y URLs; fallos de archivos/SQL con datos sintéticos; build si cambia frontend; QA visual/teclado/contraste y pruebas de concurrencia solo en base desechable. No cargar las imágenes generadas de las propuestas como fotografías reales del centro.

Galería agrega 24 casos. El rol acumula **212 escenarios especificados**, no ejecutados durante estas tareas de documentación. [Matriz de cobertura y pendientes](33-MATRIZ-COBERTURA-GERENCIA.md).

## Verificación documental del 26/10/2026

- `node output/auditoria-integral-20261025/verificar-galeria-gerencia.mjs`: PASS; cinco documentos incluyendo matriz, doce datos, doce controles, 24 casos nuevos, 92 enlaces locales y dos PNG de 1536 × 1024 con huellas verificadas. Ocho imágenes de propuestas en el conjunto del rol.
- `node output/auditoria-integral-20261025/auditar-enlaces-documentales.mjs galeria-gerencia`: 549 documentos, 4321 enlaces locales, cero destinos inexistentes y cero duplicados exactos en ese corte compartido.
- Comprobadores documentales `verificar-reportes-gerencia.mjs`, `verificar-alertas-gerencia.mjs` y `verificar-residentes-ocupacion.mjs`: PASS. El primero valida su subtotal de casos/enlace sin congelar el acumulado de 188 anterior a esta entrega.
- `git diff --check -- docs/especificacion-funcional docs/CHANGELOG.md`: sin errores. Sin pruebas backend/frontend, build o base de datos: no se cambió producto. Los cambios concurrentes se conservan; sin commit, push, carga institucional o despliegue.
