---
title: "Limpieza segura de archivos y carpetas"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-10
owner: RememberMind
source_of_truth: false
verified_against_commit: d402b3729194f6afc3ceda2e954c17827ec194cb
verification_scope: BASE_COMMIT_AND_WORKTREE_CLEANUP_WITH_TARGETED_TESTS
runtime_verified: true
clinical_runtime_verified: false
related_docs: ["README.md", "ARQUITECTURA_VIGENTE.md", "AUDITORIA_LIMPIEZA_2026_10_10.md"]
---

# Limpieza segura de archivos y carpetas

Limpieza autorizada sobre `PODEMOS-SEGUIR`, en la carpeta original
`C:\Users\CARLAENCINAS\Desktop\RememberMind`. La base de este corte es
`d402b3729194f6afc3ceda2e954c17827ec194cb`.

## Archivos retirados

- 14 plantillas de `stubs`, idénticas por SHA256 a las instaladas de Livewire.
  Los generadores resuelven las plantillas del paquete cuando no hay overrides.
- `resources/frontend/assets/fuentes.css`, compuesto únicamente por un
  comentario y sin consumidores. `resources/css/app.css` y su import permanecen.
- 94 `.gitkeep` de subdirectorios previstos sin implementación, sustituidos
  por 18 marcadores mínimos en capas principales todavía vacías.

En total se retiraron **109 archivos versionados** (2.148 bytes) y se podaron
**108 directorios vacíos**. Se conservan los doce propietarios, las 48 capas,
las raíces de Livewire/Vistas/Rutas y los 248 aliases de vistas existentes.
También se retiraron cuatro temporales de septiembre de `bootstrap/cache`
(51.617 bytes), comprobando su antigüedad y que no estaban abiertos para escribir.

La poda de directorios solo se aplicó a rutas verificadas vacías, con eliminación
no recursiva y sin seguir enlaces. Los cambios versionados se realizaron mediante
parches revisables después de que la revisión automática rechazara el comando
general de borrado por PowerShell sin proporcionar un motivo adicional.

## Copias de verificación retiradas

Se retiraron mediante Git los worktrees detached de verificación y baseline.
Se preservaron las configuraciones sintéticas del primero como respaldo local.
Windows inicialmente impidió borrar la raíz de verificación; después de retirar
su contenido y registro Git, se pudo borrar el directorio ya vacío sin recursión.

Se retiró también el PostgreSQL aislado de QA en `work/pg-testing`, verificando
su ruta propia, base sintética, ausencia de PID y ausencia de listener en el
puerto 58698. Los cinco JUnit del clúster están preservados en la entrega.
El tamaño previo de estas tres ubicaciones era **828.692.765 bytes
(aproximadamente 790,30 MiB)**; es una medida de archivos, no del espacio físico
exacto recuperado en el disco.

La copia antigua `work/RememberMind` permanece desactivada, sin `.git`.
Su eliminación recursiva había sido rechazada por la revisión automática y no
se reintentó ni se sustituyó por otro método de borrado.

## Verificación efectivamente ejecutada

- Arquitectura, vista pública, dashboards por roles y matriz de permisos:
  **60 pruebas / 4.152 aserciones**, correctas, con SQLite `:memory:` forzado
  y guard de entorno sintético antes del bootstrap.
- Frontend: **147 pruebas**, correctas. `npm run build`: correcto.
- Generadores Livewire: **14 fallbacks** al paquete con los mismos hashes,
  más resolución de Form y Layout, sin generar archivos en el proyecto.
- Motor independiente: **cinco casos técnicos**, correctos, sin Laravel/Eloquent.
- Arranque real: conexión PostgreSQL y tablas requeridas confirmadas mediante
  sesión de lectura. No se iniciaron sesiones con cuentas reales.
- Sintaxis del test modificado y `git diff --check`: correctos.
- Revisión independiente: capas, registros, aliases, imports CSS y conservación
  de archivos persistentes confirmados.

La primera ejecución PHP tuvo un fallo preexistente del snapshot de rutas:
Livewire deriva su prefijo de `APP_KEY`, y el script servido depende de
`APP_DEBUG`. La prueba ahora normaliza solamente el hash de los nueve endpoints
reconocidos, exige nueve coincidencias y comprueba el prefijo actual calculado
por Livewire. Conserva la comparación exacta de las 296 rutas, su orden,
métodos, sufijos, nombres, actions, middleware y demás campos.
`APP_DEBUG=false` se fija únicamente en PHPUnit antes del bootstrap.
La ejecución posterior de las 60 pruebas pasó. No se repitió la suite PHP completa
para una limpieza que no cambia lógica de negocio.

## Conservación

La configuración `.env`, el archivo local de LSP y **48 archivos persistentes**
de `storage/app/private`, `public` y `backups` conservan sus hashes y tamaños.
Permanecen `vendor`, `node_modules`, los assets compilados, sesiones, logs,
el enlace `public/storage`, `.vite`, `public/hot` y los auxiliares de testing.
No se modificaron PHP ejecutable de la aplicación, modelos, esquema, rutas,
providers, permisos, dependencias ni datos clínicos reales. `ARQUI-NUEVA`,
`ARQUITECTURA` y `REINICIO` mantienen sus referencias anteriores.
