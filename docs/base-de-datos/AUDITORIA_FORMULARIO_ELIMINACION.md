---
title: "Auditoría del formulario Eliminación V2"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: false
verification_scope: ELIMINACION_V2_SCHEMA_BACKEND_AND_TESTS
runtime_verified: true
related_docs: [DECISION_ELIMINACION_V2.md, ../frontend/FORMULARIO_ELIMINACION_V2_RESULTADO.md]
---

# Auditoría del formulario Eliminación V2

La aprobación expresa del 09/10/2026 sustituye el contrato anterior de captura de este documento. La versión 1.0 describía cantidad/característica en texto y campos estructurados todavía propuestos. La extensión nueva no se atribuye al baseline original.

## Estructura

La [decisión aprobada](DECISION_ELIMINACION_V2.md) añade exactamente 13 columnas nullable a la tabla operacional existente `registros_eliminacion`: `cantidad_cualitativa`, `volumen_ml`, `color_orina`, `aspecto_orina`, `olor_orina`, `tipo_miccion`, `tipo_bristol`, `color_heces`, `esfuerzo_defecacion`, `presencia_sangre`, `presencia_moco`, `molestia_eliminacion`, `descripcion_molestia`. Tipos y límites físicos: decisión aprobada y migración `2026_10_09_000200_extend_registros_eliminacion_v2.php`.

Inventario operacional: 71 tablas, sin cambio. No cambian otras tablas, PK, FK, índices ni relaciones. `down()` elimina exclusivamente las trece columnas. SQLite y PostgreSQL verifican rollback/reaplicación conservando columnas y una fila histórica. Sin backfill ni conversión automática.

## Escritor y autorización

`MisPacientes::guardarEliminacion` → `CuidadosEnfermeriaService::registrarEliminacion` → `RegistroEliminacion`. El consumidor genérico delega al escritor canónico. La pantalla antigua `RegistrosEnfermeria` dirige al formulario contextual de Mis residentes y rechaza su captura obsoleta; se retira su escritura de columnas inexistentes.

Cuenta activa, permiso `registros_eliminacion.crear`, personal activo, residente autorizado y jornada actual son exigidos en servidor. Contexto de residente bloqueado y reautorización al guardar. Autor, jornada, fecha/hora, código y estado proceden del servidor. Administrador/Superadministrador no adquieren escritura clínica por su rol.

## Validación

- Tipo obligatorio exclusivamente URINARIA/INTESTINAL; catálogos aprobados centralizados en `RegistroEliminacion::OPCIONES`.
- Volumen opcional 0–999999.99 mL, máximo dos decimales; 0.00 distinto de NULL.
- Bristol opcional entero 1–7; continencia compatible con el tipo.
- Campos no NULL de la otra rama rechazados, incluidos false/0; campos desconocidos y claves de autoría/contexto rechazados.
- Sangre, moco y molestia conservan true/false/NULL.
- Descripción de molestia opcional 250; se normaliza NULL cuando molestia es falsa/no valorada.
- Observación opcional trim/5000, sin truncar.

## Historia y continuidad

Cada confirmación inserta un evento independiente VIGENTE; no sobrescribe ni elimina historia. Nuevos registros dejan `cantidad` y `caracteristica` NULL. Si las trece columnas nuevas son NULL y existe texto anterior, el DTO muestra «Dato de registro anterior»: `cantidad = 350` no recibe una unidad inventada.

Consultas limitadas al residente contextual, estado VIGENTE y fecha no futura. Último global por tipo consultado independientemente del límite de historial. Conteos COUNT reales de toda la jornada. Seis eventos recientes y hasta cuarenta en el popup, con límite comunicado. Sin permiso de lectura no se expone historia ni continuidad. Pase de turno consume la etiqueta del tipo real en lugar del atributo inexistente `tipo`.

## Interacción

Clinical Form progresivo con contexto existente, fecha automática, selector por tipo, captura/continuidad 65/35, Bristol mediante SVG propios y estados nullable. Cambio de rama con confirmación, conserva comunes y limpia específicos incompatibles. Limpieza y descarte sin justificativo. Error seguro conserva captura; éxito solo después de persistir.

Historial emergente reutiliza la ventana clínica con etiquetas de historial, filtros Todos/Urinaria/Intestinal y grupos Hoy/Ayer/fecha. Movimiento y tamaño por teclado, Escape y retorno de foco. No hay gráfica numérica, tendencia, interpretación clínica, diagnóstico ni alerta automática derivada de Eliminación.

Resultados y límites de QA: [reporte del lote](../frontend/FORMULARIO_ELIMINACION_V2_RESULTADO.md). Datos sintéticos en bases aisladas; no certifica producción ni WCAG.
