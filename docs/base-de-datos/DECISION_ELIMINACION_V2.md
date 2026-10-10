---
title: "Extensión aprobada de registros_eliminacion — Eliminación V2"
status: APPROVED
version: "1.0"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [registros_eliminacion_v2_extension]
verification_scope: OWNER_APPROVED_CONTRACT
runtime_verified: false
---

# Eliminación V2 — extensión aditiva aprobada

La propietaria autorizó explícitamente el 2026-10-09, en «REMEMBERMIND — ELIMINACIÓN V2 — EXTENSIÓN APROBADA DE BDD + FORMULARIO COMPLETO», ampliar exclusivamente `registros_eliminacion` con los siguientes trece campos nullable. No se crean tablas ni se cambia otra tabla, PK, FK, relación, catálogo SQL o índice. Inventario operativo: **71 tablas**, sin cambio; las tablas expertas se inventarían aparte.

| Columna | Tipo |
| --- | --- |
| cantidad_cualitativa | string(20) |
| volumen_ml | decimal(8,2) |
| color_orina | string(30) |
| aspecto_orina | string(30) |
| olor_orina | string(30) |
| tipo_miccion | string(30) |
| tipo_bristol | unsignedTinyInteger |
| color_heces | string(30) |
| esfuerzo_defecacion | string(30) |
| presencia_sangre | boolean |
| presencia_moco | boolean |
| molestia_eliminacion | boolean |
| descripcion_molestia | string(250) |

La migración `2026_10_09_000200_extend_registros_eliminacion_v2.php` añade únicamente estas columnas. `down()` retira exactamente las trece: conserva columnas y filas anteriores. Un rollback pierde los valores almacenados en esas trece columnas; por ello solo se prueba en bases desechables y no se recomienda sobre historia V2 real sin preservación previa.

## Contrato de registro

Una fila = un evento observado de un residente, con profesional y jornada autorizados y fecha/hora del servidor. Cada confirmación crea otra fila; no se sobrescribe historia. El formulario no permite editar identidad/autor/jornada/fecha.

- Tipo obligatorio: URINARIA o INTESTINAL.
- Cantidad cualitativa opcional: ESCASA, HABITUAL, ABUNDANTE.
- URINARIA: volumen 0–999999.99 mL, máximo dos decimales, opcional; color CLARA/AMARILLO_CLARO/AMARILLO/AMBAR/OSCURA/ROJIZA/OTRO; aspecto CLARO/TURBIO/SEDIMENTO/HEMATICO_APARENTE/OTRO; olor HABITUAL/INTENSO/INUSUAL; micción ESPONTANEA/ASISTIDA.
- INTESTINAL: Bristol entero 1–7; color MARRON/MARRON_CLARO/MARRON_OSCURO/ROJIZO/NEGRUZCO/OTRO; esfuerzo SIN_ESFUERZO/CON_ESFUERZO/DIFICULTOSO; moco triestado.
- Sangre y molestia: triestado en ambos tipos. NULL = no valorado/no informado, false = no observado/referido, true = sí. No se convierte NULL en false.
- Descripción de molestia opcional, hasta 250 caracteres; se recomienda cuando Sí. Si molestia false/NULL se normaliza a NULL para evitar texto stale.
- Continencia opcional: CONTINENTE o INCONTINENCIA_URINARIA para urinaria; CONTINENTE o INCONTINENCIA_FECAL para intestinal.
- Observación opcional, trim, hasta 5000 caracteres; vacío → NULL.
- Campo específico del otro tipo con cualquier valor no NULL, incluso false o cero: rechazo backend. Datos desconocidos y autoridad aportada por cliente también se rechazan.

Los valores son opciones de captura aprobadas, no un nuevo catálogo SQL ni una clasificación diagnóstica. No se generan diagnósticos, reglas de gravedad, metas, alertas automáticas ni tendencias numéricas.

## Historia anterior y normalización

`cantidad` y `caracteristica` se conservan para historia. No se hace backfill automático ni se deduce unidad, volumen o Bristol desde esas cadenas ambiguas. Nuevos registros V2 dejan ambos campos NULL. Si los campos nuevos están NULL y existen datos anteriores, la presentación los identifica como «Dato de registro anterior» y mantiene su texto literal.

La extensión contiene atributos de una sola observación por evento, sin listas independientes, JSON/EAV ni repetición de entidades. Campos de rama incompatible se mantienen NULL. Esta estructura no introduce dependencias multivaluadas independientes; se mantiene el grano longitudinal del registro.

## Continuidad y consumidores

Una implementación de validación y escritura en `CuidadosEnfermeriaService::registrarEliminacion`. El escritor genérico delega al mismo servicio; el antiguo formulario general dirige al formulario estructurado, preservando las rutas existentes. No conserva un escritor alternativo con columnas inexistentes.

COUNT por residente/jornada/tipo, último global del mismo tipo e historial reciente se calculan desde registros vigentes no futuros; no son columnas. El historial se representa mediante timeline filtrable, no como gráfica heterogénea.

Verificación por motores, pruebas, responsive y QA: [resultado Eliminación V2](../frontend/FORMULARIO_ELIMINACION_V2_RESULTADO.md). La aprobación estructural no certifica ejecución, accesibilidad ni producción.
