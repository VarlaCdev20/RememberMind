---
title: "Auditoría: Nuevo registro de Enfermería / Medicación programada"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Evidencia localizada con partes propuestas: no autoriza nuevas columnas/reglas. Aplicar decisiones aprobadas posteriores en su alcance, incluida V2.2 para objetivos; consultar [índice](README.md).

# Auditoría: Nuevo registro de Enfermería / Medicación programada

Fuentes: BDD Operativa V2.1 (`REMEMBERMIND_BDD_70_TABLAS.md` y baseline
congelado), migrations de `prescripciones`, `horarios_prescripcion`,
`administraciones_medicacion` y endurecimiento V2, modelos respectivos,
`AgendaMedicacionService`, `RegistrarAdministracionMedicacionService`,
Livewire `MisPacientes` y pruebas de administración.

## Modelo y campos del formulario

- `medicamentos`: nombre comercial/genérico, concentración y forma farmacéutica
  para mostrar medicamento y presentación. No se edita.
- `prescripciones`: `cod_residente`, `cod_medicamento`, dosis
  `decimal(10,3)`, unidad, vía `string(60)`, frecuencia, vigencia y estado.
  Toda la orden es de solo lectura.
- `horarios_prescripcion`: horario `time`, dosis programada
  `decimal(10,3)`, días de semana y estado. Se elige una ocurrencia pendiente
  concreta; no se toma la primera como FK implícita.
- `administraciones_medicacion`: fecha/hora programada, fecha/hora real,
  resultado, dosis administrada `decimal(10,3)`, motivo de omisión,
  observación, estado, prescripción, horario, residente, jornada y personal.

La vía procede exclusivamente de `prescripciones.via_administracion`.
No existe un catálogo normalizado de vías en la BDD V2.1; este formulario no
ofrece una vía alternativa.

## Estados y reglas

- Prescripción administrable: `ACTIVA` o `ACTIVO`, emitida antes de ahora,
  pauta programada (no PRN), del residente autorizado.
- Horario: `ACTIVO` y correspondiente al día actual según `dias_semana`.
- Opciones de resultado del formulario: `ADMINISTRADA` u `OMITIDA`.
  El servicio guarda ese valor en `resultado` y `REGISTRADA` en `estado`.
  `PENDIENTE`, `PROXIMA` y `VENCIDA` son estados calculados de agenda, no
  resultados de una administración.
- Si se administra: fecha/hora real y dosis positiva requeridas; dosis de
  hasta tres decimales y capacidad `decimal(10,3)`; sin motivo de omisión.
- Si se omite: motivo de 5 a 500 caracteres, sin hora real ni dosis
  administrada. Observación opcional, trim y máximo 2000 caracteres conforme
  a la validación vigente del formulario clínico; la columna física es `text`.

## Integridad y concurrencia

El servidor revalida permiso, turno, residente, asignación de jornada,
vigencia de prescripción, horario activo, día de pauta y duplicidad.
`RegistrarAdministracionMedicacionService` usa transacción y `lockForUpdate`
sobre la prescripción y el horario, consulta duplicados y la BDD impide dos
administraciones de la misma prescripción y horario por día mediante
`uq_administracion_programada_dia`. La BDD también asegura pertenencia del
residente a la prescripción y del horario a la prescripción.

No se crearon campos ni tablas propuestos para este formulario. La
idempotencia de PRN sin horario y de reintentos de red sigue documentada como
deuda técnica general en el baseline; queda fuera de esta administración
programada.
