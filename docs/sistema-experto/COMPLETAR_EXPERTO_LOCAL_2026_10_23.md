# Preparación local y cohorte experta — 23/10/2026

## Solicitud y alcance

La propietaria autorizó completar seeders y requisitos existentes, recuperar lo aprovechable de las ramas y mejorar la consulta de Superadministración, Medicina, Psicología y Enfermería. No se utilizaron subagentes. La búsqueda anterior está en [Localización de la cohorte](LOCALIZACION_COHORTE_GIT_2026_10_22.md); la interfaz está documentada en [Exploración clínica por rol](../frontend/EXPERTO_CLINICO_ROLES_2026_10_22.md).

No apareció la base `remembermind_dev` ni su respaldo. Se reconstruyó el lote sintético existente `COHORTE-INFERENCIA-30-V1` sobre PostgreSQL local `RememberMind`, conservando los 73 residentes anteriores. Esto no recupera los otros expedientes ni la historia de aquella instalación.

## Fuentes y decisiones aplicadas

- Definiciones y manifiesto existentes de `CohorteCognitivaInvestigacionSeeder` y `ManifiestoCognitivo30`.
- [Reducción experta aprobada](../base-de-datos/DECISION_REDUCCION_EXPERTA_2026_10_20.md).
- [Consolidación de bloques aprobada](../base-de-datos/DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md).
- Instaladores existentes `PermisosPetersenOriginalSeeder`, `PermisosLecturaIadlPetersenSeeder` y `PermisosConsultaFuentesExpertasSeeder`. Restauran el contrato ya aprobado; no se ejecutó `RolesAndPermissionsSeeder` en la base local.
- Acciones existentes `RegistrarConocimientoPetersen` y `RegistrarConocimientoInstitucional`. Instalan los contratos versionados existentes; no se cambiaron método, reglas, umbrales ni aprobaciones profesionales.

## Implementación y secuencia

`scripts/completar-experto-local.php` es un coordinador CLI optativo. Sin `--aplicar` imprime únicamente el plan. Exige entorno local, PostgreSQL de loopback, nombre exacto `RememberMind` o `remembermind_dev`, configuración sin caché y cohorte ausente o completa. Rechaza una cohorte parcial.

Al aplicar, adquiere un bloqueo asesor, mantiene el sitio temporalmente en mantenimiento y crea un respaldo completo privado. Verifica tamaño y catálogo con `pg_restore --list` antes de modificar. Ejecuta únicamente las dos migraciones aprobadas pendientes y los tres instaladores acotados de permisos. Después invoca el cargador existente con `--instalar-conocimiento` o, si el lote ya está presente, `--verificar`.

El cargador vuelve a respaldar antes de insertar. La transacción compara las huellas de registros anteriores, respuestas instrumentales, conocimiento anterior, roles y permisos. La preparación explícita permite añadir los dos paquetes faltantes; no activa ni sobrescribe un paquete existente incompatible. El seeder conserva las operaciones de preadmisión, admisión, cama y registro original, y crea únicamente los actores sintéticos nominales de su definición con claves aleatorias, sin contraseña conocida ni cuenta de respaldo.

Cada caso se recalcula mediante el motor a partir de SQL, con los bindings recuperados en una petición nueva. Solo después de verificar 30 casos y cero discrepancias se guardan las dos claves locales `SISTEMA_EXPERTO_SIMULACION_LOCAL` y `SISTEMA_EXPERTO_SIMULACION_BASE`. No se sustituye `.env` ni se cambia la conexión. Se restituye el estado previo de mantenimiento en `finally`.

Los dos scripts tienen un manejador CLI que registra privadamente el error y termina con código 1. No se acepta una salida de excepción como confirmación JSON ni se publica SQL en consola. No existe restauración destructiva automática: ante fallo hay que revisar el diagnóstico y el inventario antes de reintentar.

```powershell
# Consulta sin modificaciones
php scripts/completar-experto-local.php

# Instalación explícita: solo para la base local exacta autorizada
php scripts/completar-experto-local.php --aplicar --base=RememberMind

# Comprobación posterior, sin reinsertar el lote
php scripts/cargar-cohorte-local.php RememberMind --verificar
php scripts/verificar-consolidacion-experta.php
```

No usar `migrate:fresh`, `DatabaseSeeder`, el dump V1 ni el runner de La Paz para completar esta cohorte.

## Resultado confirmado

| Comprobación | Resultado |
|---|---|
| Residentes anteriores | 73; huellas preservadas durante la carga |
| Casos añadidos | 30, identificados por documento nominal y marca de admisión |
| Total actual | 103 |
| Paquetes de conocimiento | 2, recuperados por el lector SQL |
| Comparación técnica | 30 casos; cero discrepancias |
| Patrones presentes | PET-01, PET-02, PET-03 y PET-04: tres casos de cada uno; los restantes conservan su estado esperado de ausencia/limitación |
| Segunda ejecución de verificación | Cero residentes agregados; 30 disponibles; cero discrepancias |
| Estructura instalada | 25 tablas expertas, 116 físicas y siete FK trasladadas verificadas |
| Resultados o firmas inventados | Ninguno; no se guardaron salidas esperadas ni revisiones profesionales |
| Recuperación de `remembermind_dev` | No; se reconstruyó únicamente el lote versionado |

Los respaldos, huellas y actas completas están en `storage/app/private/auditorias-locales/completar-experto/` y `cohorte30/`, excluidos de Git y del directorio público. No se incorporan datos personales o volcados al repositorio.

La primera carga falló por una lectura de fuente no instalada y se revirtió sin dejar casos ni versiones. Las migraciones aprobadas ya aplicadas se conservaron. Se añadieron al coordinador los instaladores existentes pendientes y la carga posterior terminó. No se debilitó la autorización para forzar el seeder.

## Consulta por rol

| Rol | Recorrido y límite |
|---|---|
| Superadministración | Sistema experto → Residentes y fuentes. Barras seleccionables, campos y autoría al lado; población habitual y lote sintético separados. Lectura y auditoría autorizadas, sin firma clínica. |
| Medicina | Directorio del experto → residente → Vista del experto. Fuentes, factores, requisitos, análisis explícito y resultado actual separado de la historia. |
| Psicología | Su ficha/directorio → mismo panel con el contexto de Psicología y sus acciones existentes. |
| Enfermería | Sus residentes asignados → resultados expertos. Mediciones y resultados conservados, sin nueva inferencia, prescripción ni ampliación de asignaciones. |

El panel organiza la explicación en cuatro pasos interactivos. Las áreas y requisitos mantienen el estado del backend; documentos disponibles y soportes utilizados tienen etiquetas distintas. Los casos de investigación se identifican como sintéticos. Los mapas muestran la selección y conexiones cercanas, con paginación de fuentes y escala legible.

El lote nuevo tiene fuentes y cálculos reproducibles; la carga no convierte esos cálculos en evaluaciones históricas ni revisiones firmadas. Enfermería conserva el acceso a los resultados efectivamente guardados de sus residentes asignados. No se asignaron los nuevos casos a cuentas reales para aparentar una prueba por rol.

## Evidencia y límites

- Preparación explícita en PostgreSQL desechable: 1 prueba / 17 aserciones; fallo intermedio, rollback completo, preservación, roles, separación de población y cálculo desde SQL.
- Misma preparación inicial en SQLite: 1 / 14 antes de añadir los tres controles de población. La comparación de fecha se ajustó para usar el valor leído de SQL, evitando confundir el formato de una fecha con una modificación.
- Panorama y proyección clínica: 7 / 50.
- Consulta conectada de Superadmin e informe: 28 / 355.
- Instaladores de permisos Petersen/IADL: 2 / 28.
- Enfermería, lectura sin escrituras e IDOR/asignación revocada: 2 / 21. Una ejecución coincidió con mantenimiento y recibió 503; la repetición posterior pasó.
- Interacción frontend: 15 pruebas. Blade compilado correctamente. Build PASS en 26,70 s, con la advertencia existente de chunk JS mayor de 500 kB.
- Destino CLI incorrecto rechazado con código 1 antes de cambios.
- Verificación instalada de inventario/FK/lectura de versiones y segunda comparación de los 30 casos: satisfactorias.

Verificación técnica del lote: **PASS**. Validación clínica independiente: **pendiente**, sin métricas clínicas ni firmas atribuidas. Revisión de autorización del cambio: lectura por campo, cuenta activa, Policy, alcance del residente, competencia y ausencia de escrituras por consulta; complementada por los negativos citados. La preparación no modifica las Policies.

QA visual: **FAIL por cobertura incompleta**, sin defecto visual observado. CUA devolvió `Local browser use is disabled in Work settings`; no se eludió mediante otro navegador, CDP o automatización de terminal. Queda pendiente el recorrido autenticado de los cuatro roles en 1440, 1280, 1024, 768 y 390 px, teclado y temas. Las capturas históricas no prueban el código actual.

Se dejaron servidores locales escuchando en 127.0.0.1:8010 y :8011 con sesiones por puerto. No se afirma que se haya iniciado sesión desde el navegador. No se hizo push. La interfaz queda sin commit por la aceptación visual pendiente; los cambios concurrentes de guía/avatar y Valoraciones se conservaron.
