---
title: "Hidratación V2 — contrato y auditoría del formulario"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: false
verified_against_commit: 7a6758e8
verification_scope: WORKTREE_HIDRATACION_V2
runtime_verified: true
---

# Hidratación V2 — contrato vigente

Evidencia del árbol de trabajo de `FORMULARIOS`, base `7a6758e8`; el hash no identifica una publicación de estos cambios. Este documento describe la implementación y no autoriza estructura o reglas nuevas. Resultado y gates: [Hidratación V2](../frontend/FORMULARIO_HIDRATACION_V2_RESULTADO.md).

## Fuentes

- [Baseline congelado](REMEMBERMIND_BDD_BASELINE_CONGELADO.md).
- [Inventario operativo V2.1, tabla 43](REMEMBERMIND_BDD_70_TABLAS.md#43-registros_hidratacion--transaccional), más la extensión V2.2 aprobada para objetivos de signos vitales, ajena a este lote.
- `database/migrations/2026_09_18_001047_create_registros_hidratacion_table.php` y `app/Models/RegistroHidratacion.php`.
- `CuidadosEnfermeriaService`, `TurnoEnfermeriaService`, `MiTurnoService`, `MisPacientes` y `RegistrosEnfermeria`: contrato ejecutable inspeccionado.
- Solicitud explícita Hidratación V2 del propietario, 2026-10-09.

## Contrato estructural comprobado

| Campo | Persistencia | Captura / autoridad |
| --- | --- | --- |
| `cod_hidratacion` | string(20), PK | Generación del servidor para cada aporte. |
| `cod_residente` | string(20), FK residentes | Residente seleccionado; contexto bloqueado y autorización al guardar. |
| `cod_personal` | string(20), FK personal | Personal activo de la cuenta autenticada. |
| `cod_jornada` | string(20), FK jornadas | Jornada actual válida del profesional. |
| `fecha_hora` | datetime | `now()` del servidor; no editable. |
| `cantidad_ml` | decimal(8,2), obligatorio | Nueva captura independiente: entero de 1 a 10000 mL, conservando la validación previa del flujo de Enfermería. |
| `tipo_liquido` | string(60), nullable | Texto libre opcional, trim; vacío → NULL. |
| `tolerancia` | string(30), nullable | Valores aplicativos existentes: ADECUADA, PARCIAL, RECHAZO, NAUSEAS; vacío → NULL. No ENUM ni catálogo nuevo. |
| `observacion` | text, nullable | Trim; hasta 5000 caracteres; sin truncamiento silencioso. |
| `estado` | string(20), obligatorio | VIGENTE al crear. No se sobrescribe historia. |

`via`: **no existe** en la tabla y **no se persiste**. No se añadieron columnas, tablas, PK/FK, índices, relaciones ni catálogos.

## Camino canónico y drift corregido

`Mis residentes → selector → Hidratación → MisPacientes::guardarHidratacion → CuidadosEnfermeriaService::registrarHidratacion → RegistroHidratacion → registros_hidratacion`.

El escritor genérico `CuidadosEnfermeriaService::registrar` intentaba persistir `via = ORAL`, sin columna correspondiente: **CONTRACT_DRIFT / BUG_PRODUCT**. Su rama Hidratación ahora delega al mismo escritor específico. El consumidor existente `RegistrosEnfermeria` también delega; conserva su ruta y remapea los errores a sus nombres de campo. Se retiraron de su captura de hidratación los campos genéricos sin persistencia propia.

Hay varios consumidores existentes, pero **una sola implementación de validación y escritura de aportes independientes**. El escritor específico rechaza claves ajenas: no acepta autor, jornada, fecha o vía aportados por el cliente. La hidratación opcional de Ingesta conserva su contrato y transacción existentes; no se modificó ese producto.

## Escritura y permisos

Se reutiliza `TurnoEnfermeriaService::autorizarMutacionEnfermeria`: cuenta activa, rol/competencia contextual de Enfermería, permiso explícito `registros_hidratacion.crear`, residente admitido y alcance/asignación vigente. Se exige además personal activo y jornada actual antes de insertar.

El formulario vuelve a comprobar contexto y autorización al guardar. Superadministración no recibe escritura clínica por su rol. La lectura de histórico exige `registros_hidratacion.ver`. Un fallo SQL se reporta en Laravel y devuelve un mensaje seguro, manteniendo captura y sin resultado de éxito.

Cada confirmación crea una fila nueva. Una vez mostrado el resultado, repetir el método de guardar en esa fase se rechaza; «Registrar otro aporte» inicia otra captura vacía.

## Continuidad y longitudinalidad

- Historial: mismo residente, VIGENTE, fecha no futura; últimos diez, orden descendente por fecha y código. DTO plano para UI.
- Gráfica: últimos seis aportes y, cuando es válido, un punto sin guardar. Eje temporal real; escala vertical dinámica en mL.
- Acumulado y cantidad: SUM/COUNT completos, mismo residente y `cod_jornada` de la jornada actual; no se calculan desde la lista truncada.
- Último aporte: último registro longitudinal elegible, aunque pertenezca a otra jornada. Su fecha permanece visible.
- Diferencia: actual menos último, descriptiva; no clasifica suficiencia ni riesgo.
- El historial conserva decimales y ceros legítimos generados por Ingesta. Esto **no amplía** el rango de las nuevas capturas independientes, que siguen siendo enteros de 1–10000 mL.
- Una tolerancia histórica de otro consumidor se muestra literalmente cuando no pertenece a las cuatro opciones de captura, sin convertirla falsamente en ausencia ni incorporarla como opción nueva. Los empates temporales se ordenan por código de forma consistente con el backend.

Sin meta diaria, balance hídrico, diagnóstico de deshidratación, interpretación automática, recomendación ni alerta nueva. La tolerancia representa la observación registrada, sin inferencia adicional.

## Verificación

Tests específicos de validación, autoría, hora, jornada, alcance, permisos revocados, cuenta/personal inactivos, continuidad, otros residentes/jornadas, estados, fechas futuras, decimales históricos y fallo real de persistencia. Migración y seed PostgreSQL ejecutados únicamente en la base desechable dedicada `remembermind_hidratacion_fresh_20261009`.

No se reinició `remembermind_dev`. La QA de navegador y fallo controlado usó datos sintéticos en una base PostgreSQL aislada de testing. Detalles y pendientes en el informe de resultado.
