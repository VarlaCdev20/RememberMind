---
title: "Extensión clínica V2.2 — objetivos individuales de signos vitales"
status: APPROVED
version: "V2.2"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: true
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# Extensión clínica V2.2 — objetivos individuales de signos vitales

**Aprobación de la propietaria:** 4 de octubre de 2026. La extensión se autorizó condicionada a consistencia, cuarta forma normal y reutilización. No modifica las 70 tablas del baseline V2.1; añade una tabla operativa, por lo que el inventario actual contiene **71 tablas operativas**.

## Necesidad y alcance

La V2.1 no tiene una fuente estructurada para objetivos médicos individuales. El texto libre de indicaciones no se interpreta como umbral. `objetivos_signos_vitales` guarda una versión por residente y variable medible de `signos_vitales`; no reemplaza el registro longitudinal ni la prescripción.

| Campo | Tipo / regla |
|---|---|
| `cod_objetivo_signo` | PK `string(20)` |
| `cod_residente` | FK obligatoria a `residentes.cod_residente` |
| `cod_personal` | FK obligatoria al médico autor en `personal.cod_personal` |
| `parametro` | Código cerrado de las siete mediciones persistidas en `signos_vitales` |
| `min_objetivo`, `max_objetivo` | `decimal(8,2)`; al menos uno presente |
| `min_critico`, `max_critico` | `decimal(8,2)` opcionales; definen umbral crítico individual aprobado por médico |
| `vigente_desde`, `vigente_hasta` | `datetime(6)`; intervalo de vigencia |
| `estado` | `VIGENTE`, `REEMPLAZADO` o `ANULADO` (retiro médico sin borrado) |
| `motivo` | Justificación clínica obligatoria; no se usa para extraer umbrales |

## Normalización y evolución

Cada fila expresa **un solo objetivo versionado para un residente y una variable**. No hay conjuntos repetidos, listas, JSON ni valores clínicos independientes escondidos en una celda. Los atributos dependen únicamente de la clave de la versión; no existen dependencias multivaluadas independientes, por lo que la relación cumple 4FN bajo este contrato. La variable se restringe a columnas reales de `signos_vitales`, evitando una tabla EAV universal. El mismo servicio y pantalla admiten objetivos para cualquiera de esas variables. Otras magnitudes futuras requieren una fuente de medición aprobada y una extensión explícita del catálogo de códigos, no una cadena libre.

La sustitución bloquea el residente en una transacción, cierra la vigencia anterior y añade una versión nueva. El retiro cierra la vigencia y conserva la fila con estado `ANULADO`; el motivo del retiro queda en el registro de actividad. Los límites anteriores permanecen en la tabla y no se borran. La unicidad `(cod_residente, parametro, vigente_desde)` identifica cada versión. Un índice único parcial impide dos filas `VIGENTE` para un mismo residente y parámetro en SQLite y PostgreSQL. En otros motores se conserva el bloqueo transaccional de la aplicación; antes de habilitarlos en producción se debe verificar un índice equivalente. PostgreSQL aún requiere validación de integración; SQLite valida el índice y la lógica secuencial.

## Seguridad e interpretación

Solo una cuenta médica activa con personal activo y permiso `objetivos_signos_vitales.gestionar` puede escribir. Enfermería puede leer los objetivos vigentes del residente asignado durante el registro; Superadministración conserva lectura, sin escritura clínica por rol. Un límite crítico general aprobado prevalece sobre un objetivo individual. Un objetivo individual vigente clasifica después del umbral crítico general y antes de la referencia general. Ningún objetivo se infiere de indicaciones de texto libre.

SpO₂ no recibe un límite universal automático: depende del objetivo médico individual y del contexto. Una glucemia aislada >250 mg/dL se presenta como sugerencia de revisión, sin alerta automática; la literatura para cuidados de larga duración se refiere a elevación sostenida y situación clínica. Fuentes: [ADA 2026, adultos mayores](https://diabetesjournals.org/care/article/49/Supplement_1/S277/163921/13-Older-Adults-Standards-of-Care-in-Diabetes-2026), [BTS, oxígeno](https://www.brit-thoracic.org.uk/clinical-resources/guidelines/emergency-oxygen/).
