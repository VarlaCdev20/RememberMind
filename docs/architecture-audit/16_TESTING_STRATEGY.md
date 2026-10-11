# Estrategia de pruebas

## Baseline real

Se inventariaron 18 archivos en tests. Predominan pruebas Jetstream/Fortify; hay tests de horarios y CasosPreadmisionTest no versionado. phpunit.xml usa SQLite :memory:, correo array, cola sync y sesión array. No se ejecutó la suite en esta fase documental; no hay porcentaje de cobertura ni declaración de tests verdes. Los tests que actúan como SUPERADMINISTRADOR no prueban el permiso mínimo por el bypass Gate.

## Pirámide pragmática

| Capa | Qué probar | Ejemplos/criterio |
|---|---|---|
| Unit | Reglas sin BD | Dosis/unidades, transición permitida, scoring con respuestas incompletas, turno cruzando medianoche |
| Feature | HTTP/Livewire/Actions con BD aislada | 403 de lectura vs crear, FormRequest, errores, redirects y efectos persistidos |
| Integration PostgreSQL | Constraints, locks, tipos, JSON, SQL y migraciones | Dos asignaciones concurrentes; FK; reintento de dosis; cierre de estancia |
| Integration externa | Storage, Mail y experto | Fake primero; adaptador con timeout, schema inválido, reintento y fallo permanente |
| Frontend | Componentes con conducta | Modal/foco, filtros URL, formulario con errores, gráfico vacío y destrucción |
| E2E | Pocos flujos críticos | Admisión → cama; prescripción → administración; evaluación → experto → revisión |

SQLite sirve para pruebas rápidas compatibles, pero no certifica PostgreSQL: índices parciales, EXCLUDE, aislamiento, ILIKE y casting deben probarse en PostgreSQL desechable. No apuntar tests al .env real. Verificar APP_ENV=testing, conexión/base permitida, ninguna config cache productiva y datos sintéticos. Las pruebas de migración ejecutan esquema completo en esa base, nunca migrate:fresh sobre datos reales.

## Casos prioritarios de regresión

1. Usuario con usuarios.ver intenta store/update y conceder SUPERADMINISTRADOR: 403, sin alta ni cambio de roles. Actor con grant limitado tampoco puede autoconceder permisos.
2. Familiar sin vínculo/vínculo revocado intenta show, búsqueda, PDF y props diferidas: denegado; familiar válido recibe solo campos compartidos.
3. Cuenta desactivada con sesión ya iniciada: siguiente solicitud denegada y sesión invalidada según política; probar acceso_sistema deshabilitado.
4. Dosis de otro residente: rechazo aunque existan ambas FK. Dos requests iguales crean una ejecución. Suspensión concurrente impide administración fuera de regla.
5. Dos ingresos a una cama: un éxito y un conflicto manejado; ninguna estancia parcial. Cambio de cama conserva intervalo anterior.
6. Fallo PDF/correo después de admisión: admisión coherente, documento pendiente recuperable; retry no duplica residentes ni mensajes.
7. Evaluación finalizada corregida: original conservado, autor/motivo nuevo, comparación usa versión. Datos faltantes no se convierten en bajo riesgo.
8. Archivo ajeno/ruta directa/MIME inválido/sobretamaño: denegado; filename original no determina ruta. Exportación no incluye secretos o fórmulas activas no deseadas.
9. Inferencia tardía ante datos cambiados: resultado marcado obsoleto; sin efecto clínico automático. Revisión requiere permiso y razón.
10. Usuario con dos perfiles: menú y acceso efectivos correctos, caché no conserva permisos revocados.

## Fixtures y validación de migración

Factories con estados explícitos: persona sin documento, residente reingresado, familiar sin cuenta, profesional fuera de turno, prescripción suspendida, evaluación anulada y duplicación legacy a conciliar. No usar nombres/datos reales en snapshots de tests.

Comparar origen/destino: filas por entidad, correspondencias únicas, conteos por estado, FK huérfanas cero, hashes de archivos, suma de administraciones y series por residente, excepciones firmadas. Verificar PK/morph de Spatie y sesiones. Antes de retirar columnas, buscar lectores y probar reportes además de formularios.

## Frontend y rendimiento

Vitest + Vue Test Utils para comportamiento importante, Playwright para E2E si se aprueba la infraestructura. No snapshots de todo el HTML. Teclado, zoom, foco al cerrar modal, lector de pantalla en flujos clave y contraste claro/oscuro; axe automatizado como apoyo, no certificación completa.

Datos sintéticos con varios años de signos/evaluaciones; medir tiempos/consultas y EXPLAIN de timeline/colas. Paginar, comprobar memoria de exports, evitar N+1. Gates de release: P0 sin fallos, flujo crítico estable, migración restaurable y revisión institucional registrada. No exigir 100% de cobertura como sustituto de esos casos.
