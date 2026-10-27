---
title: "Gerencia: aceptación de Residentes y ocupación"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SPECIFIED_ACCEPTANCE_CASES_NOT_EXECUTED
runtime_verified: false
---

# Aceptación de Residentes y ocupación

Contratos: [Residentes](18-RESIDENTES-GERENCIA.md), [Ocupación](19-OCUPACION-HABITACIONES-CAMAS.md), decisiones 29B/24A y B. Los 24 casos siguientes están **especificados, no ejecutados**. No sustituyen pruebas de autorización, base PostgreSQL o navegador. Usar datos sintéticos.

| Caso | Condición/acción | Resultado exigido |
|---|---|---|
| RO01 | Entrar en Residentes sin preferencia previa | Tarjetas iniciales, nombres y acciones legibles, sin PK/códigos visibles |
| RO02 | Alternar tarjetas y tabla tras filtrar | Mismos residentes, total y filtros; tabla semántica accesible |
| RO03 | Buscar nombre/combinar estado, piso y habitación | Solo coincidencias autorizadas, paginación reiniciada y alcance visible |
| RO04 | Dos contactos principales para un residente | Una fila/residente; conflicto explícito sin convertir menor código en prioridad institucional |
| RO05 | Edad, piso o cama no registrados | Ausencias legibles; no cero, piso ficticio ni ubicación histórica presentada como actual |
| RO06 | Abrir ficha y volver | Contexto/filtros restaurados sin datos de otro residente |
| RO07 | Usuario inactivo o sin residentes.ver | Denegación en backend, sin nombres en payload parcial |
| RO08 | Manipular residente o revocar permiso con detalle abierto | Revalidación contextual; operación denegada y sin exposición residual |
| RO09 | Gerencia abre ficha administrativa | Solo secciones autorizadas; no añade edición clínica, diagnóstico ni revisión experta |
| RO10 | Consultar directorio o cambiar vista | No crea residentes ni omite preadmisión y admisión formal con cama |
| RO11 | Entrar a Ocupación desde Inicio y menú | Destino correcto para Gerencia, sin redirección al 403 H04 ni grant global de Administración |
| RO12 | Alternar mapa/tabla con filtros | Misma disponibilidad, ocupante autorizado, totales y corte de consulta |
| RO13 | Cama vacía con habitación bloqueada | No habilitada, nunca disponible por estar vacía |
| RO14 | Cama con ocupación vigente | Ocupada; no puede liberarse con cambio de etiqueta física |
| RO15 | Habitación sin camas o capacidad distinta de inventario | Diferencia visible; no inventa camas ni denominadores |
| RO16 | Duplicación activa o relación inconsistente | Incidencia y conteos no presentados como íntegros; ninguna reparación silenciosa |
| RO17 | Solo habitaciones.ver, sin lectura de residentes | Datos físicos permitidos; identidad/historial nominal ausentes del payload, no solo ocultos |
| RO18 | Historia contiene admisión y traslados, más de doce eventos | Origen/fechas correctos; paginación o límite visible sin aparentar historia íntegra |
| RO19 | Consultante sin permiso de traslado | Sin mutación disponible y backend deniega petición manipulada |
| RO20 | Actor autorizado intenta primera asignación fuera de admisión o fecha inválida | Rechazo preservando historia; reutiliza Action y flujo institucional |
| RO21 | Dos actores autorizados eligen misma cama | Prueba PostgreSQL: una ocupación activa, conflicto claro, rollback del intento fallido |
| RO22 | Teclado, Escape, filtros, drawer y lector de pantalla | Orden lógico, foco restaurado, estados anunciados y tabla alternativa al mapa |
| RO23 | 1440, 1280, 1024, 768 y 390 px; movimiento reducido | Sin recortes globales, acciones utilizables, labels y contraste comprobados con tokens |
| RO24 | Carga/error/sin datos/sin coincidencias y crecimiento del listado | Estados distintos, reintento sin falsa confirmación, consultas sin N+1 por cama/residente |

## Pruebas existentes localizadas

| Fuente | Cobertura que declara el código actual | Qué no demuestra esta documentación |
|---|---|---|
| [ResidentesEspaciosTest](../../../../tests/Feature/ResidentesEspaciosTest.php) | Directorio, filtros, contactos sin duplicar, métricas, paginación, permisos y consultas | Recorrido final de Gerencia ni QA visual ejecutados ahora |
| [MapaHabitacionesTest](../../../../tests/Feature/MapaHabitacionesTest.php) | Ocupación/disponibilidad, identidad condicionada, detalle, habitaciones sin camas, autorización y consultas | Solución de H04, historia ilimitada o autorización por cada identidad histórica |
| [AlojamientoResidenteTest](../../../../tests/Feature/AlojamientoResidenteTest.php) | Action, historia, admisión, cama ocupada, reintento, Policy y Livewire | Asignación del permiso de mutación a Gerencia |
| [OcupacionCamaIntegrityTest](../../../../tests/Feature/OcupacionCamaIntegrityTest.php) | Integridad de ocupación en sus casos existentes | Corrección de todos los datos institucionales |
| [AlojamientoBloqueoPostgresTest](../../../../tests/Feature/AlojamientoBloqueoPostgresTest.php) | Ensayo específico del motor PostgreSQL según su implementación | Resultado de concurrencia en esta tarea; no se ejecutó |

## Evidencia necesaria para cerrar implementación

Relacionar cada RO con archivo implementado, prueba/comando, entorno y resultado real. Probar autorización del payload, no solo ausencia de botón. Conservar captura visual con datos sintéticos y ancho de viewport; medir contraste y foco. Ejecutar build únicamente cuando se cambie frontend. Los traslados se prueban en base desechable, sin mover personas de la institución.

Esta entrega comprueba enlaces y consistencia documental. **Implementación, suite funcional, integración PostgreSQL y QA visual permanecen pendientes.** No se ejecutaron migraciones, suites de aplicación, commit o push.

## Verificación documental del 26/10/2026

- `node output/auditoria-integral-20261025/verificar-residentes-ocupacion.mjs`: PASS; tres documentos, veinte datos nominales, 24 casos únicos y 37 enlaces locales válidos.
- `node output/auditoria-integral-20261025/auditar-enlaces-documentales.mjs residentes-ocupacion`: 535 documentos, 4081 enlaces locales, cero destinos inexistentes y cero duplicados exactos.
- `node output/auditoria-integral-20261025/verificar-planificacion-cobertura.mjs`: PASS de consistencia documental y de las dos imágenes existentes; no prueba interfaz ejecutable.
- `git diff --check -- docs/especificacion-funcional docs/CHANGELOG.md`: sin errores. Se preserva el árbol con cambios simultáneos; las cifras son evidencia de este corte documental.
