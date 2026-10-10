---
title: "Extensión aprobada de registros_movilidad — Movilidad V2"
status: APPROVED
version: "1.0"
last_reviewed: 2026-10-10
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [registros_movilidad_v2_extension]
verification_scope: OWNER_APPROVED_CONTRACT
runtime_verified: false
---

# Movilidad V2 — extensión aditiva aprobada

Autoridad: instrucción explícita de la propietaria «REMEMBERMIND — MOVILIDAD V2» del 10/10/2026. Amplía exclusivamente `registros_movilidad` con nueve columnas nullable. No crea tablas, índices, catálogos SQL ni modifica PK/FK, relaciones o columnas anteriores. El inventario sigue en **71 tablas operativas**; las tablas expertas se cuentan por separado.

| Columna | Tipo |
| --- | --- |
| motivo_registro | string(30) |
| actividad_realizada | string(40) |
| distancia_metros | decimal(7,2) |
| dolor_movilidad | boolean |
| mareo | boolean |
| disnea | boolean |
| debilidad | boolean |
| cambio_habitual | string(20) |
| tolerancia_movilidad | string(30) |

Migración: `2026_10_10_000100_extend_registros_movilidad_v2.php`. `down()` retira exactamente estas nueve columnas y conserva las filas y atributos anteriores. El rollback pierde los valores de la extensión: se verifica solo en bases desechables y requiere preservar esos valores antes de un rollback sobre historia real.

## Grano e historia

Una fila representa un evento observado de un residente, con autor profesional, jornada y momento fijados por servidor. Cada confirmación crea una fila independiente. No se actualizan ni borran eventos anteriores; no se reconstruyen retrospectivamente los nuevos datos desde observaciones libres.

Se mantienen `marcha`, `traslado`, `tipo_apoyo`, `dispositivo`, `equilibrio`, `fatiga`, `riesgo_caida`, `observacion`, contexto, fecha y estado. Dispositivos históricos en texto libre se muestran literalmente. Los nuevos registros usan las opciones aprobadas; `NINGUNO` significa ausencia observada y NULL significa no informado.

La extensión incorpora atributos escalares del mismo evento; no contiene listas independientes, JSON/EAV, repetición de entidades ni nuevas dependencias multivaluadas independientes. No cambia el grano longitudinal ni las cardinalidades existentes.

## Captura aprobada

- Motivo: CONTROL_DIARIO, CAMBIO_FUNCIONAL, POST_CAIDA, TRAS_FISIOTERAPIA, ANTES_TRASLADO, OTRO.
- Actividad: CAMINAR_HABITACION, CAMINAR_PASILLO, LEVANTARSE_CAMA, TRANSFERENCIA_CAMA_SILLON, CAMBIO_POSTURAL, SEDESTACION, BIPEDESTACION.
- Marcha obligatoria: INDEPENDIENTE, ASISTIDA, SILLA_RUEDAS, ENCAMADO.
- Traslado: INDEPENDIENTE, SUPERVISION, AYUDA_UNA_PERSONA, AYUDA_DOS_PERSONAS, GRUA.
- Apoyo: INDEPENDIENTE, SUPERVISION, PARCIAL, COMPLETA, UNA_PERSONA, DOS_PERSONAS.
- Dispositivo: NINGUNO, BASTON, ANDADOR, SILLA_RUEDAS, BARANDILLA, OTRO.
- Equilibrio: ESTABLE, INESTABLE, NO_VALORABLE.
- Fatiga: SIN_FATIGA, LEVE, MODERADA, SEVERA.
- Tolerancia: BUENA, PARCIAL, MALA.
- Cambio habitual: SIN_CAMBIOS, MEJOR, PEOR.
- Riesgo observado: BAJO, MEDIO, ALTO.
- Distancia opcional: 0–99999.99 metros, hasta dos decimales, únicamente en las dos actividades de caminar. Una distancia incompatible se rechaza en backend; el cambio de actividad la limpia en la captura.
- Síntomas: true/false/NULL; NULL nunca se sustituye por false.
- Observación opcional: trim, hasta 5000 caracteres; vacío pasa a NULL.

Petición posterior de la propietaria (10/10/2026): al seleccionar OTRO en motivo o dispositivo, desplegar y exigir su descripción. `motivo_otro` y `dispositivo_otro` son entradas transitorias de captura de hasta 500 caracteres, no columnas ni catálogos nuevos. El escritor conserva sus textos como narrativa en la columna existente `observacion`, junto con la observación libre, sin interpretar ni reconstruir datos estructurados desde ese texto. Máximo combinado: 5000 caracteres; se rechaza el exceso sin truncar. El cambio a otra opción limpia el detalle en la captura; el backend rechaza detalles no vacíos incompatibles. Historia anterior intacta.

Estas son opciones de documentación aprobadas, no diagnósticos ni nuevas reglas clínicas. El color representa la selección o el estado observado; no se calcula gravedad, puntuación, tendencia funcional, recomendación o alerta automática. Una caída se documenta mediante el flujo real de incidentes; guardar movilidad no crea un incidente.

## Escritura y autorización

`CuidadosEnfermeriaService::registrarMovilidad` es el único escritor del flujo. Su whitelist admite solo campos de captura. Residente, profesional, jornada, fecha/hora y estado se resuelven y validan en servidor; claves de autoridad aportadas por cliente se rechazan.

Se conservan cuenta/personal activos, competencia vigente, permiso `registros_movilidad.crear`, Policy y alcance/jornada/asignación del servicio de turnos. No se otorga escritura a otros roles. La excepción temporal de escritura clínica de Superadministración, ya aprobada y limitada por la configuración CURRENT, no se altera en este lote.

La entrada genérica `registrar` y el consumidor anterior `RegistrosEnfermeria` delegan al mismo escritor. La rama de movilidad no genera una valoración de dolor por un valor residual de otro tipo de cuidado. Las opciones del consumidor anterior se alinean con el contrato canónico sin cambiar sus rutas.

## Lectura y presentación

El histórico requiere `registros_movilidad.ver`, conserva el residente contextual y excluye anulados/futuros. DTO plano, sin números derivados de categorías. Ventana común movible/redimensionable con filtros de actividad, grupos de fecha y carga progresiva de 40 eventos. La captura no aparece como evento histórico.

La continuidad muestra conteos reales de la jornada actual: registros, actividades de deambulación y eventos con un dato de fatiga documentado, incluido SIN_FATIGA. NULL no se cuenta como fatiga documentada. No son una clasificación funcional. La presentación omite atributos no informados y conserva el texto literal de historia anterior.

Implementación y evidencia: [resultado de Movilidad V2](../frontend/FORMULARIO_MOVILIDAD_V2_RESULTADO.md).
