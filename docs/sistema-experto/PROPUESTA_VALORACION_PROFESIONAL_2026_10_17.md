# Valoración del sistema experto y revisión profesional

Estado: **PROPUESTA PARA DECISIÓN**, 17/10/2026.
No activa conocimiento clínico, permisos o migraciones.

Actualización posterior: la propietaria aprobó **Psicología y Medicina con
revisiones separadas** y la **opción B: una tabla vinculada a la evaluación**.
Estructura y responsabilidades de firma quedan resueltas en
[la decisión aprobada](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md).
La interpretación operativa permanece pendiente de revisión profesional;
se preparó [la matriz concreta](MATRIZ_INTERPRETACION_OPERATIVA_PARA_REVISION.md).
Las opciones siguientes conservan el contexto de la decisión inicial.

## Objetivo solicitado

La propietaria solicita un proceso operativo basado en el expediente, con una
valoración del sistema experto que el profesional pueda confirmar o rechazar.
La presentación principal debe ser clara, sin terminología de fixtures. La
confirmación humana y el resultado automático deben permanecer separados.

La solicitud autoriza diseñar esta transición; no especifica instrumentos,
normas, reglas clínicas, competencias de firma ni una ampliación estructural
concreta. Esas decisiones no se deducen de una aprobación de interfaz.

## Lo que existe y se reutiliza

- Extracción de fuentes operativas, fechas, procedencia y ausencia explícita.
- Casos de uso y motor puro, reglas versionadas y explicación reproducible.
- Conservación de memoria, cinco dominios, funcionalidad y requisitos DCL.
- Pantalla de resultados, navegación, historial y autorización de lectura.
- Fuentes IADL descriptivas, sin sustituirlas por BADL ni por caídas.

La entrega local anterior prueba ese recorrido con referencias 03A. Sus
estados A/P y C01–C05 proceden de casos predeterminados, no de interpretaciones
clínicas de los antecedentes. Las ejecuciones y antecedentes ficticios
anteriores conservan esa procedencia; no se renombran como evaluaciones reales.

## Contrato operativo que falta

| Entrada o decisión | Requisito para sustituir la referencia artificial |
|---|---|
| Interpretación por dominio | Instrumento, edición, componente, método y población de referencia autorizados; alcance explícito y revisión cuando corresponda |
| Procedencia y contexto | Fuente original, autor, fecha, condiciones y factores que afectan la interpretación registrados; nunca se acreditan por defecto |
| C01 | Historia y cambio cognitivo corroborados mediante soportes definidos |
| C02 | Evidencia objetiva interpretada conforme al contrato aprobado |
| C03 | Revisión de IADL, oportunidad, nivel previo y causas de ayuda; independencia no inferida de datos ausentes |
| C04 | Revisión diferencial documentada por el profesional competente |
| C05 | Calidad e interpretabilidad explícitas según el contrato de ingeniería |
| Revisión final | Profesional autorizado, decisión, fecha y justificación, vinculados al resultado automático exacto |

Esta tabla identifica entradas del contrato existente; no incorpora criterios,
baremos, derechos de uso o umbrales nuevos. La decisión DEC-OPEN-005 continúa
pendiente respecto al conocimiento y su aceptación profesional. Una aprobación
al final no sustituye las interpretaciones y soportes necesarios durante la
inferencia. Si faltan, la salida debe indicar información insuficiente y
explicar los datos que faltan, sin fabricar un PET.

## Flujo propuesto

1. Obtener el expediente hasta una fecha de corte y validar sus fuentes.
2. Construir la memoria y aplicar únicamente conocimiento aprobado para esas
   fuentes y su versión. Los fixtures quedan fuera de la ejecución operativa.
3. Conservar la valoración automática original, sus evidencias, faltantes y
   explicación. Nunca recalcularla al abrir el historial.
4. Mostrar **Valoración del sistema experto** y **Pendiente de revisión
   profesional**. Los resultados automáticos no se presentan como firmados.
5. Permitir al profesional autorizado **Confirmar valoración** o **Rechazar
   valoración**, registrando su justificación y autoría. Si falta información,
   mantiene pendiente el caso y puede completarse una nueva evaluación.
6. Una corrección crea otra revisión vinculada a la anterior; una reejecución
   conserva otra evaluación. Ninguna sobrescribe la salida automática original.

Confirmar registra conformidad profesional con esa valoración. No genera por
sí solo diagnósticos, prescripciones, alertas, publicaciones a familiares ni
una validación general del conocimiento. Esas operaciones conservan sus flujos.

## Presentación propuesta

La pantalla existente mantiene su jerarquía:

- Hallazgos y alcance por dominio.
- Evidencias con fuente, autor, fecha y valores utilizados.
- Razones, datos faltantes y fundamento técnico desplegable.
- Al final: **Valoración del sistema experto**, estado de revisión y acciones
  autorizadas de confirmación/rechazo.
- Tras una revisión: decisión, profesional, fecha y justificación, junto al
  resultado automático original.

Un resultado operativo sustentado no necesita un aviso de simulación. Las
ejecuciones históricas ficticias conservan su identificación; la limpieza
visual no cambia la naturaleza ni el origen de los datos. En desarrollo puede
probarse el mismo flujo operativo con datos de prueba, conservando su origen.

## Persistencia: dos opciones concretas, ninguna aplicada

El esquema experto conserva resultados, pero no dispone de un dictamen humano
vinculado a la evaluación. `cod_personal_solicitante` representa al solicitante;
`estado_ejecucion` representa al motor. Ninguno reemplaza autoría y decisión
de revisión. Activitylog acompaña la auditoría, no sustituye el dictamen.

### A. Reutilizar notas clínicas, sin tabla nueva

Ampliar `notas_clinicas` con:

| Columna propuesta | Tipo | Uso |
|---|---|---|
| `cod_evaluacion_experta` | string(20), nullable, FK restrictiva e indexada | Evaluación original objeto de la revisión |
| `decision_revision_experta` | string(20), nullable | CONFIRMADA o RECHAZADA, exclusivamente cuando existe la FK anterior |

Autor, fecha clínica, contenido y corrección reutilizan los campos existentes
y `cod_nota_anterior`. Ambas columnas se informan juntas; las notas ordinarias
las mantienen NULL. Requiere aprobar esa invariante, el tipo de nota y su
flujo de escritura. La atención, profesional y evaluación deben corresponder
al mismo residente. No obliga a fabricar una atención para satisfacer una FK.

Ventaja: cero tablas adicionales. Limitación: requiere una atención clínica
válida y amplía el contrato de todas las notas clínicas.

### B. Una tabla específica de revisiones

Recomendación técnica para separar ejecución y dictamen longitudinal:

| Columna propuesta | Tipo | Uso |
|---|---|---|
| `cod_revision_experta` | string(20), PK | Identidad correlativa |
| `cod_evaluacion_experta` | string(20), FK requerida | Evaluación exacta revisada |
| `cod_personal` | string(20), FK requerida | Profesional que firma, derivado de su cuenta |
| `cod_usuario` | string(20), FK requerida | Cuenta autenticada que registra la decisión |
| `fecha_hora_revision` | datetime requerida | Fecha de la revisión |
| `decision` | string(20) requerida | CONFIRMADA o RECHAZADA |
| `justificacion` | text requerido | Fundamento profesional de la decisión |
| `cod_revision_anterior` | string(20), FK propia nullable | Revisión que rectifica |

Tabla propuesta: `revisiones_evaluacion_experta`. Sin JSON, timestamps
automáticos, borrados en cascada ni copia del residente/version: se obtienen
de la evaluación original. Todas las FK se indexan y son restrictivas.
Pendiente se deriva de la ausencia de revisión vigente. La relación es una
evaluación con una cadena de revisiones, con una sola cabeza vigente.

La primera revisión tiene predecesora NULL; las posteriores apuntan a la
cabeza de esa misma evaluación. La operación bloquea la evaluación, comprueba
la cabeza, evita bifurcaciones y rechaza reintentos divergentes. No permite
actualizar o eliminar revisiones previas. Las garantías físicas de unicidad
y la carrera real de dos revisores deben probarse en PostgreSQL.

Ventaja: no altera la captura clínica ordinaria y permite corregir decisiones
sin sobrescritura. Limitación: incorpora una tabla de negocio. Su diccionario,
catálogos y restricciones requieren aprobación antes de crear la migración.

## Autorización por decidir

Debe definirse qué profesional interpreta cada entrada y quién confirma la
valoración final: Medicina/Geriatría, Psicología o responsabilidades separadas.
La Policy actual solo autoriza lectura; un permiso existente de instrumentos
no equivale a autorización para revisar cualquier resultado experto.

La nueva operación debe exigir cuenta activa, permiso explícito, Policy
contextual, personal activo, competencia y acceso al residente. El cliente no
elige otro profesional como autor. Superadministración conserva su capacidad
de consulta/auditoría, sin recibir competencia clínica por su rol.

## Orden de implementación y aceptación

1. Resolver profesional responsable y aprobar instrumentos, interpretaciones
   y reglas con sus fuentes/versiones; definir soportes operativos C01–C05.
2. Aprobar una de las dos opciones de persistencia y la competencia de revisión.
3. Implementar puertos operativos de interpretación y revisión, sustituyendo
   las restricciones SIM exclusivamente en el recorrido autorizado.
4. Implementar conservación del dictamen y las acciones/Policies; conectar la
   pantalla existente con los estados y operaciones realmente guardados.
5. Probar faltantes, contradicción, abstención, fuentes, revisión positiva y
   negativa, IDOR, autoría, cuenta inactiva, reintento, corrección, rollback y
   concurrencia PostgreSQL. La prueba técnica no se llama validación clínica.

No se retiraron avisos de ejecuciones ficticias, activaron mapeos pendientes,
modificaron datos clínicos ni aplicaron cambios de esquema mediante esta propuesta.

## Fuentes revisadas

- Instrucción actual de la propietaria y reglamentos maestros de esta tarea.
- `docs/DECISIONES_PENDIENTES.md`, DEC-OPEN-005.
- `docs/sistema-experto/CONTRATO_INFORME_COGNITIVO.md`.
- `docs/sistema-experto/EVIDENCIAS_COGNITIVAS.md`.
- `docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md` y diccionario vigente.
- Decisiones aprobadas IADL y conservación experta V2.
- `FuenteCasoSimulado`, contratos de Domain y `EvaluacionExpertaPolicy`.
- Modelos `EvaluacionExperta`, `NotaClinica` y `ModeloExperto`.
