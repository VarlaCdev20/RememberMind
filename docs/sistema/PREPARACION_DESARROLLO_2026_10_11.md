---
title: "Preparación para continuar desarrollo"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-11
owner: RememberMind
source_of_truth: false
verified_against_commit: 28d4e51fd67a45c7a2bfc2a6f8d8b36ff92d29d7
verification_scope: FULL_SQLITE_AND_SCOPED_POSTGRES_FRONTEND_BUILD
runtime_verified: false
supersedes: []
related_docs: ["DESARROLLO_SEGURO.md", "DEPENDENCIAS_DESARROLLO.md", "../ESTADO_ACTUAL.md"]
related_modules: []
---

# Preparación para continuar desarrollo — 11/10/2026

RememberMind queda preparado para continuar desarrollo sobre la arquitectura híbrida aprobada. Copia local: `C:\Users\CARLAENCINAS\Desktop\RememberMind`, rama `PODEMOS-SEGUIR`. Los informes de Codex y sus temporales están fuera de esta raíz. No se trasladó el sistema a una nueva carpeta.

## Etapas terminadas

| Etapa | Resultado | Commit local |
|---|---|---|
| 1. Acceso y aislamiento | Retirado acceso automático de enfermería; pruebas aisladas antes de cargar Laravel | `730ce21` |
| 2. Contratos V2 | Personal, cuentas y documentación con autorización backend, validación canónica y compensación de archivos nuevos | `c15e836` |
| 3. Dependencias | Actualizaciones compatibles y excepción explícita para el advisory pendiente | `98e4099` |
| 4. Arquitectura | Consultas fuera de Blade, puerto de conocimiento y demostración experta en adaptador de entrada; reglas comprobadas | `27a7940` |
| 5. PostgreSQL y CI | Cluster desechable, runner restringido, login/destino de diez roles, concurrencia real y workflow | `a00ab98` |
| 6. Limpieza y cierre | Dos marcadores redundantes retirados; setup seguro; suite global y documentación vigente | `28d4e51` más commit documental de cierre |

## Verificación ejecutada

| Comprobación | Resultado |
|---|---|
| SQLite completa en `28d4e51` | 1.079 pruebas, 31.452 aserciones, cero fallos/errores, 15 omisiones; 16m27.333s |
| PostgreSQL 18, cohorte usada por CI | 143 pruebas, 2.489 aserciones, sin fallos/errores/omisiones; cluster nuevo |
| Frontend | 151 pruebas aprobadas |
| Vite | Build aprobado |
| Motor sin Laravel | Cinco casos de independencia aprobados |
| Composer | Configuración válida; auditoría sin avisos |
| npm runtime | Sin avisos |
| npm completo | Un advisory alto, propagado a cinco paquetes de desarrollo; pendiente explícito |
| Workflow | actionlint 1.7.12 aprobado; ShellCheck no disponible |
| Preservación | 50 archivos protegidos sin cambios de hash/tamaño |

Las 15 omisiones SQLite son dos casos de concurrencia PostgreSQL y trece funciones Jetstream/Fortify deshabilitadas (registro público, API tokens, eliminación de cuenta, verificación de correo y segundo factor). Los dos casos de concurrencia pasaron con conexiones/procesos reales en PostgreSQL. Las cohortes dirigidas se solapan; no sumarlas como pruebas distintas.

JUnit, logs y seis informes de etapa se entregan en la carpeta `outputs` del chat. Este documento conserva el resultado durable en el repositorio. No se alteraron tablas, catálogos ni reglas clínicas congeladas; no se usaron cuentas reales para probar login ni se escribió en la base instalada.

## Continuar desarrollando

1. Trabajar desde la raíz canónica y revisar `git status` antes de cada lote. Mantener el archivo local de editor `storage/framework/lsp-d02a7460f22f26a7.php` fuera de los commits.
2. Usar un propietario de `app/Modulos` y sus capas; consultar el [ADR](../arquitectura/ADR_ARQUITECTURA_HIBRIDA.md) y los [límites verificados](../arquitectura/LIMITES_VERIFICADOS_2026_10_11.md). Eloquent en Aplicación y `App/Models` son excepciones vigentes, no motivos para una reescritura general.
3. Agregar reglas críticas y autorización en backend; probar el contrato cambiado, incluido rechazo por permisos/estado/ámbito cuando aplique.
4. Ejecutar `composer test -- --filter=NombreTest` para el lote y `npm test` / `npm run build` cuando cambie frontend. Antes de integrar un lote transversal, ejecutar `composer test`.
5. Para restricciones físicas/concurrencia, usar `pwsh -File scripts/probar-postgres.ps1`; crea su propio cluster y restaura el entorno. Consultar [requisitos y aislamiento](DESARROLLO_SEGURO.md). `composer test:postgres` requiere provisionar explícitamente el perfil seguro; no crea la base por sí mismo.
6. Revisar el diff, registrar evidencia y realizar commits coherentes. Publicar cuando corresponda para obtener ejecución remota de CI.

`composer setup` instala dependencias y compila. Configurar `.env`, generar clave de una instalación nueva y migrar requieren revisar separadamente el destino; no sobrescribir el entorno existente ni regenerar claves con datos cifrados.

## Pendientes que deben guiar el siguiente trabajo

- **Dependencias:** `braces` 3.0.3, GHSA-vfj7-8cjw-p6xm, sigue afectado en la cadena de desarrollo Tailwind 3. La excepción valida advisory, versiones, cadena y alcance dev; expira el **11/11/2026**. Priorizar actualización compatible o migración Tailwind como lote propio, con revisión visual. [Detalle](DEPENDENCIAS_DESARROLLO.md).
- **Privacidad documental:** documentos de usuario conservan el disco público existente. Planificar almacenamiento privado, descargas autorizadas y migración controlada de archivos; no trasladar ni eliminar archivos reales durante limpieza general.
- **Arquitectura:** reducir gradualmente contexto HTTP en Aplicación y acoplamientos heredados al tocar cada flujo. La regla estática de vistas no garantiza ausencia de todo acceso indirecto por relaciones o llamadas dinámicas.
- **Validación:** no se ejecutó GitHub Actions remoto, QA visual integral ni despliegue de producción. Las pruebas automatizadas no sustituyen aceptación institucional.
- **Experto:** conservar motor independiente y adaptadores explícitos. Las pruebas usan conocimiento técnico/sintético; no acreditan validación ni activación clínica.

La limpieza conserva dependencias instaladas, build, almacenamiento persistente, documentación histórica útil y marcadores de capas todavía vacías. No se borran carpetas solo por su nombre. Un cluster manual de auditoría quedó detenido y aislado fuera del repositorio porque la revisión automática bloqueó su eliminación recursiva (`blocked by policy`); no participa del arranque ni de las pruebas.

Los commits de este lote son locales; no se realizó push. El sistema tiene una base comprobada para seguir desarrollando, con los pendientes anteriores visibles.
