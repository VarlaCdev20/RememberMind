# RememberMind — Árboles de decisión del área de Medicina

**Estado:** diseño funcional y técnico para revisión profesional
**Área propietaria:** Médico General / Geriatra
**Alcance:** todos los formularios, registros, alertas y transiciones médicas identificadas en el repositorio
**Restricción:** sistema de apoyo a la decisión; no diagnostica, prescribe, admite, deriva ni da de alta de forma autónoma

## 1. Objetivo

Definir un flujo médico único, trazable y explicable que conecte:

1. cola de pacientes;
2. valoración médica de admisión;
3. atención médica;
4. nota clínica y evolución;
5. antecedentes;
6. diagnósticos;
7. alergias;
8. signos vitales;
9. dolor;
10. antropometría;
11. indicaciones clínicas;
12. prescripciones y suspensiones;
13. estudios, resultados e informes;
14. documentos clínicos;
15. derivaciones e interconsultas;
16. valoración geriátrica y cognitiva;
17. seguimiento longitudinal;
18. alertas médicas;
19. egreso o cierre de seguimiento.

Los árboles definen cuándo el sistema puede guardar, cuándo debe advertir, cuándo debe bloquear por inconsistencia y cuándo debe solicitar revisión humana.

## 2. Inventario real de formularios y persistencia

| Formulario o acción | Implementación actual | Tablas principales | Árbol |
|---|---|---|---|
| Dashboard y cola médica | `DashboardMedico`, `PacientesSeguimientoPanel` | residentes, alertas, atenciones, signos_vitales, prescripciones | `MED-COLA-001` |
| Valoración médica de admisión | `ValoracionMedicaModal`, `ValoracionMedicaPanel` | atenciones, notas_clinicas, signos_vitales, historial_estados_residente | `MED-ADM-001` |
| Apertura de atención | `ExpedienteClinicoController::crearAtencion` | atenciones | `MED-ATE-001` |
| Nota/evolución médica | `NotaEvolucionMedicaModal`, registro `nota` | notas_clinicas, atenciones | `MED-NOT-001` |
| Antecedente clínico | registro `antecedente` | antecedentes_clinicos | `MED-ANT-001` |
| Diagnóstico | registro `diagnostico` | diagnosticos, atenciones | `MED-DIA-001` |
| Alergia | registro `alergia` | alergias | `MED-ALE-001` |
| Signos vitales | modal de evolución, expediente y panel de signos | signos_vitales | `MED-SV-001` |
| Dolor | registro `dolor` | valoraciones_dolor | `MED-DOL-001` |
| Antropometría | registro `antropometria` | mediciones_antropometricas | `MED-ANTRO-001` |
| Indicación clínica | registro `indicacion` | indicaciones_clinicas | `MED-IND-001` |
| Prescripción | `MedicacionAdultoModal`, `MedicacionController` | medicamentos, prescripciones, horarios_prescripcion | `MED-PRE-001` |
| Suspensión de prescripción | `MedicacionController::suspender` | prescripciones | `MED-SUS-001` |
| Solicitud de estudio | `EstudioClinicoController::solicitar` | estudios_clinicos | `MED-EST-001` |
| Resultados de estudio | `EstudioClinicoController::resultados` | resultados_estudio | `MED-RES-001` |
| Informe del estudio | `EstudioClinicoController::informar` | informes_estudio | `MED-INF-001` |
| Documento clínico | `EstudioClinicoController::documento` y expediente | documentos_clinicos | `MED-DOC-001` |
| Derivación | `EstudioClinicoController::derivar` | derivaciones | `MED-DER-001` |
| Interconsulta | nota `INTERCONSULTA` y panel de seguimiento | atenciones, notas_clinicas, derivaciones | `MED-INT-001` |
| Cognición y riesgo | módulo compartido de evaluaciones | instrumentos, aplicaciones_instrumento, respuestas_instrumento | `MED-GER-001` |
| Alerta manual/automática | alertas y eventos | alertas, eventos_alerta | `MED-ALT-001` |
| Egreso o cierre | nota `EGRESO`, estado del residente y atención | notas_clinicas, atenciones, historial_estados_residente | `MED-EGR-001` |

## 3. Reglas generales para todos los formularios

Antes de evaluar el contenido clínico, todos los árboles deben comprobar:

```mermaid
flowchart TD
    A[Solicitud médica] --> B{Sesión y cuenta activas}
    B -->|No| X[DENEGAR]
    B -->|Sí| C{Rol médico y permiso específico}
    C -->|No| X
    C -->|Sí| D{Personal médico activo y propio}
    D -->|No| Y[BLOQUEAR: identidad profesional inválida]
    D -->|Sí| E{Residente existe y está dentro del alcance}
    E -->|No| Z[BLOQUEAR: contexto inválido]
    E -->|Sí| F{Atención pertenece al mismo residente}
    F -->|No| Z
    F -->|Sí| G{Fecha, estado y referencias son consistentes}
    G -->|No| W[DATOS INCONSISTENTES]
    G -->|Sí| H[Continuar al árbol específico]
```

### Invariantes

- Nunca utilizar el primer personal disponible como responsable.
- Nunca utilizar códigos fijos como `ARE_0001` sin validar la asignación real.
- El backend obtiene el profesional autenticado; el formulario no permite suplantarlo.
- Un registro médico siempre pertenece al residente y, cuando corresponda, a una atención.
- Una corrección crea trazabilidad; no elimina la versión anterior.
- Un borrador no genera alertas clínicas definitivas.
- Datos referidos por terceros se identifican como `REFERIDOS`, no como diagnósticos confirmados.
- Todo resultado puede ser `NO_EVALUABLE` si faltan entradas esenciales.

## 4. Árbol maestro del área médica

```mermaid
flowchart TD
    A[Paciente en cola médica] --> B{Motivo de entrada}
    B -->|Admisión| C[MED-ADM-001]
    B -->|Consulta o evolución| D[MED-ATE-001]
    B -->|Alerta| E[MED-ALT-001]
    B -->|Interconsulta| F[MED-INT-001]
    B -->|Resultado de estudio| G[MED-RES-001]
    C --> H{Requiere atención inmediata}
    D --> I[Registrar historia, examen y evaluación]
    E --> I
    F --> I
    G --> I
    H -->|Sí| J[Escalar según protocolo]
    H -->|No| I
    I --> K{Se necesita acción clínica}
    K -->|Indicación| L[MED-IND-001]
    K -->|Prescripción| M[MED-PRE-001]
    K -->|Estudio| N[MED-EST-001]
    K -->|Derivación| O[MED-DER-001]
    K -->|Seguimiento| P[MED-NOT-001]
    L --> Q[Guardar evidencia y plan]
    M --> Q
    N --> Q
    O --> Q
    P --> Q
    Q --> R{Continúa seguimiento}
    R -->|Sí| S[Programar revisión]
    R -->|No| T[MED-EGR-001]
```

---

## 5. Cola médica y priorización

### `MED-COLA-001` — Orden de revisión médica

**Objetivo:** ordenar el trabajo médico sin diagnosticar ni ocultar pacientes.

**Entradas:** estado del residente, alertas abiertas, incidentes, signos vigentes, notas recientes, derivaciones, estudios e indicaciones pendientes.

```mermaid
flowchart TD
    A[Construir cola] --> B{Alerta crítica abierta}
    B -->|Sí| H[Prioridad institucional máxima]
    B -->|No| C{Incidente requiere médico o derivación}
    C -->|Sí| I[Prioridad alta]
    C -->|No| D{Resultado clasificado para revisión urgente}
    D -->|Sí| I
    D -->|No| E{Valoración de admisión pendiente}
    E -->|Sí| J[Cola de admisión]
    E -->|No| F{Interconsulta o seguimiento vencido}
    F -->|Sí| K[Cola de seguimiento]
    F -->|No| G[Cola ordinaria]
```

**Salida:** prioridad, causa visible y enlaces al registro que la justifica. La fecha de llegada nunca debe ocultarse; se utiliza como desempate dentro de la misma prioridad.

---

## 6. Valoración médica de admisión

### `MED-ADM-001` — Recomendación médica para admisión

**Objetivo:** producir una recomendación médica estructurada. La formalización administrativa continúa siendo una decisión separada.

**Formularios actuales:** `ValoracionMedicaModal` y `ValoracionMedicaPanel`. Deben converger en un único formulario y servicio.

```mermaid
flowchart TD
    A[Abrir valoración] --> B{Existe preadmisión y valoración de enfermería vinculada}
    B -->|No| X[NO EVALUABLE: completar antecedentes de ingreso]
    B -->|Sí| C{Datos referidos identificados como provisionales}
    C -->|No| Y[Solicitar clasificación de la fuente]
    C -->|Sí| D{Evaluación médica mínima completa}
    D -->|No| X
    D -->|Sí| E{Hallazgo que exige atención o derivación antes de admisión}
    E -->|Sí| F[Resultado DERIVADO/OBSERVADO con motivo]
    E -->|No| G{Necesidades de cuidado pueden cubrirse institucionalmente}
    G -->|No| H[NO ADMITIDO o DERIVADO: decisión humana motivada]
    G -->|Sí| I{Médico confirma recomendación}
    I -->|No| J[Guardar BORRADOR]
    I -->|Sí| K[Guardar COMPLETADA y enviar a decisión administrativa]
```

### Datos mínimos

- condición médica general;
- fuente y vigencia de diagnósticos, antecedentes, alergias y medicación referida;
- examen físico general;
- estado neurológico y cognitivo aparente;
- dependencia y riesgo funcional;
- necesidades de monitoreo;
- recomendación y motivo;
- profesional, fecha y atención.

### Alertas asociadas

- dato referido grave sin verificación;
- posible alergia relevante;
- signos que cumplen criterio de escalamiento aprobado;
- necesidad institucional no cubierta;
- recomendación completada sin motivo;
- discrepancia entre decisión médica y estado del residente.

---

## 7. Atención médica

### `MED-ATE-001` — Apertura, desarrollo y cierre de atención

```mermaid
flowchart TD
    A[Crear atención] --> B{Área médica y profesional activos}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Motivo y tipo de atención definidos}
    C -->|No| Y[Solicitar datos]
    C -->|Sí| D[Abrir atención]
    D --> E{Se registró evaluación o nota}
    E -->|No| F[Mantener ABIERTA]
    E -->|Sí| G{Plan o conducta documentados}
    G -->|No| H[ADVERTIR: atención incompleta]
    G -->|Sí| I{Hay pendientes asociados}
    I -->|Sí| J[FINALIZAR con seguimiento pendiente]
    I -->|No| K[FINALIZAR]
```

Una atención no debe finalizarse si no existe al menos una nota o resultado clínico y una conducta documentada.

---

## 8. Nota clínica y evolución

### `MED-NOT-001` — Nota médica estructurada

**Tipos existentes:** `EVOLUCION`, `INGRESO`, `EGRESO`, `INTERCONSULTA`, `URGENCIA`, `PROCEDIMIENTO`.

```mermaid
flowchart TD
    A[Seleccionar tipo de nota] --> B{Atención válida y del residente}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Fecha no futura y profesional válido}
    C -->|No| X
    C -->|Sí| D{Valoración clínica documentada}
    D -->|No| Y[Solicitar valoración]
    D -->|Sí| E{Plan o conducta documentados}
    E -->|No| Y
    E -->|Sí| F{Incluye signos vitales}
    F -->|Sí| G[Ejecutar MED-SV-001]
    F -->|No| H{La nota contradice datos estructurados}
    G --> H
    H -->|Sí| I[ADVERTIR y solicitar confirmación]
    H -->|No| J[Guardar nota activa]
    I --> J
```

### Reglas por tipo

| Tipo | Requisitos adicionales |
|---|---|
| INGRESO | resumen de problemas, datos referidos, examen y plan inicial |
| EVOLUCIÓN | cambio desde la nota previa, respuesta y plan actualizado |
| URGENCIA | hora, motivo, evaluación, acción inmediata y destino |
| PROCEDIMIENTO | indicación, consentimiento cuando corresponda, ejecución y resultado |
| INTERCONSULTA | pregunta clínica, área destinataria y urgencia |
| EGRESO | condición de salida, conciliación, recomendaciones y seguimiento |

Las secciones SOAP pueden conservarse, pero diagnósticos, alergias, indicaciones y prescripciones confirmadas deben almacenarse también en sus tablas estructuradas.

---

## 9. Antecedentes clínicos

### `MED-ANT-001` — Registro de antecedente

```mermaid
flowchart TD
    A[Nuevo antecedente] --> B{Tipo y descripción presentes}
    B -->|No| X[NO GUARDAR]
    B -->|Sí| C{Fuente de información identificada}
    C -->|No| D[Guardar solo como REFERIDO con advertencia]
    C -->|Sí| E{Existe antecedente activo equivalente}
    E -->|Sí| F[Mostrar duplicado y permitir actualización trazable]
    E -->|No| G{Fecha de referencia consistente}
    G -->|No| H[DATOS INCONSISTENTES]
    G -->|Sí| I[Guardar ACTIVO]
    D --> I
    F --> I
```

**Alertas:** duplicidad contradictoria, antecedente relevante no considerado en plan y fuente desconocida presentada como confirmada.

---

## 10. Diagnósticos

### `MED-DIA-001` — Registro y seguimiento diagnóstico

```mermaid
flowchart TD
    A[Proponer diagnóstico] --> B{Atención médica válida}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Nombre y certeza documentados}
    C -->|No| Y[Solicitar clasificación]
    C -->|Sí| D{Código clínico informado}
    D -->|No| E[Permitir con advertencia de codificación pendiente]
    D -->|Sí| F{Existe diagnóstico activo equivalente}
    E --> F
    F -->|Sí| G[Actualizar evolución; no duplicar]
    F -->|No| H[Crear diagnóstico]
    G --> I{Requiere plan, estudio o seguimiento}
    H --> I
    I -->|Sí| J[Exigir vínculo con conducta clínica]
    I -->|No| K[Guardar con justificación]
```

**Límite:** el sistema experto puede detectar incompatibilidades y pendientes, pero no generar diagnósticos a partir de signos o texto libre.

---

## 11. Alergias

### `MED-ALE-001` — Registro y reconciliación de alergia

```mermaid
flowchart TD
    A[Registrar alergia] --> B{Sustancia identificada}
    B -->|No| X[NO GUARDAR]
    B -->|Sí| C{Fuente y reacción conocidas}
    C -->|No| D[Guardar como REFERIDA/PENDIENTE DE CONFIRMACIÓN]
    C -->|Sí| E{Existe alergia activa equivalente}
    E -->|Sí| F[Evitar duplicado y actualizar trazabilidad]
    E -->|No| G[Guardar alergia activa]
    D --> H{Coincide con medicación activa o nueva prescripción}
    F --> H
    G --> H
    H -->|Sí| I[ALERTA para revisión inmediata]
    H -->|No| J[Sin alerta]
```

La comparación medicamento–alergia necesita un catálogo normalizado. Una coincidencia de texto libre no debe suspender medicamentos automáticamente.

---

## 12. Signos vitales

### `MED-SV-001` — Validación técnica, tendencia y alerta

```mermaid
flowchart TD
    A[Registrar signos] --> B{Al menos una medición presente}
    B -->|No| X[NO GUARDAR]
    B -->|Sí| C{Valores dentro de límites técnicos posibles}
    C -->|No| Y[BLOQUEAR o exigir rectificación]
    C -->|Sí| D{Contexto registrado cuando aplica}
    D -->|No| E[Solicitar posición, oxígeno u observación]
    D -->|Sí| F{Cumple criterio clínico urgente aprobado}
    E --> F
    F -->|Sí| G[ALERTA CRÍTICA/ALTA con evidencia]
    F -->|No| H{Cambio significativo frente a línea basal}
    H -->|Sí| I[RECOMENDACIÓN o alerta preventiva]
    H -->|No| J[Guardar VIGENTE]
    G --> J
    I --> J
```

### Reglas

- Los límites de validación del formulario son límites técnicos, no umbrales de alarma.
- La regla debe considerar contexto, tendencia, oxígeno suplementario y condiciones conocidas.
- Una rectificación conserva el registro original como anulado o sustituido con motivo.
- Todas las reglas de signos deben estar fuera de componentes Livewire y dentro del dominio clínico.

---

## 13. Dolor

### `MED-DOL-001` — Dolor, intervención y respuesta

```mermaid
flowchart TD
    A[Valoración de dolor] --> B{Escala e intensidad válidas}
    B -->|No| X[NO EVALUABLE]
    B -->|Sí| C{Ubicación, tipo y duración suficientes}
    C -->|No| D[Solicitar completar contexto]
    C -->|Sí| E{Cumple criterio de prioridad aprobado}
    E -->|Sí| F[ALERTA o revisión médica]
    E -->|No| G{Existe intervención documentada}
    G -->|No| H[RECOMENDACIÓN: definir conducta]
    G -->|Sí| I{Existe reevaluación de respuesta}
    I -->|No| J[RECOMENDACIÓN: reevaluar]
    I -->|Sí| K{Dolor mejora según objetivo}
    K -->|No| L[Escalar revisión]
    K -->|Sí| M[Continuar seguimiento]
```

---

## 14. Antropometría

### `MED-ANTRO-001` — Coherencia y tendencia antropométrica

```mermaid
flowchart TD
    A[Nueva medición] --> B{Peso o talla presentes}
    B -->|No| X[NO GUARDAR]
    B -->|Sí| C{Valores técnicamente posibles}
    C -->|No| Y[DATOS INCONSISTENTES]
    C -->|Sí| D{IMC calculado coincide cuando aplica}
    D -->|No| E[Recalcular y solicitar confirmación]
    D -->|Sí| F{Existe medición comparable previa}
    F -->|No| G[Establecer línea basal]
    F -->|Sí| H{Cambio supera criterio aprobado}
    H -->|Sí| I[RECOMENDACIÓN a nutrición/medicina]
    H -->|No| J[Guardar seguimiento]
```

---

## 15. Indicaciones clínicas

### `MED-IND-001` — Creación y seguimiento de indicación

```mermaid
flowchart TD
    A[Nueva indicación] --> B{Atención médica válida}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Tipo, descripción y prioridad presentes}
    C -->|No| Y[Solicitar completar]
    C -->|Sí| D{Contradice alergia, diagnóstico o restricción vigente}
    D -->|Sí| E[ADVERTIR y exigir justificación]
    D -->|No| F{Requiere ejecución por otra área}
    E --> F
    F -->|Sí| G[Crear seguimiento y destinatario]
    F -->|No| H[Guardar indicación]
    G --> H
```

Una indicación debe tener estado, responsable destinatario, vigencia y criterio de finalización cuando produzca tareas asistenciales.

---

## 16. Prescripción y suspensión

### `MED-PRE-001` — Prescripción segura

```mermaid
flowchart TD
    A[Nueva prescripción] --> B{Médico autorizado y atención válida}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Medicamento de catálogo seleccionado}
    C -->|No| Y[Solicitar normalización del medicamento]
    C -->|Sí| D{Dosis, unidad, vía y frecuencia completas}
    D -->|No| Z[NO GUARDAR]
    D -->|Sí| E{Es PRN}
    E -->|Sí| F{Condición e intervalo PRN definidos}
    F -->|No| Z
    E -->|No| G{Horarios completos y coherentes}
    G -->|No| Z
    F --> H{Alergia o contraindicación estructurada detectada}
    G --> H
    H -->|Sí| I[ALERTA y confirmación médica motivada]
    H -->|No| J{Duplicidad terapéutica potencial}
    J -->|Sí| K[ADVERTIR y solicitar justificación]
    J -->|No| L[Guardar ACTIVA]
    I --> L
    K --> L
```

### `MED-SUS-001` — Suspensión

```mermaid
flowchart TD
    A[Solicitar suspensión] --> B{Prescripción activa}
    B -->|No| X[CONFLICTO: ya no está activa]
    B -->|Sí| C{Médico autorizado}
    C -->|No| Y[DENEGAR]
    C -->|Sí| D{Motivo documentado}
    D -->|No| Z[NO GUARDAR]
    D -->|Sí| E{Hay dosis próximas o pendientes}
    E -->|Sí| F[Notificar a enfermería y actualizar agenda]
    E -->|No| G[Suspender]
    F --> G
    G --> H[Registrar profesional y fecha]
```

La propuesta se alinea con la separación existente entre prescripción, horario y administración. El sistema no debe crear automáticamente medicamentos de catálogo a partir de texto sin una revisión del catálogo institucional.

---

## 17. Estudios clínicos, resultados e informes

### `MED-EST-001` — Solicitud de estudio

```mermaid
flowchart TD
    A[Solicitar estudio] --> B{Atención y tipo de estudio válidos}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Motivo clínico documentado}
    C -->|No| Y[Solicitar motivo]
    C -->|Sí| D{Existe solicitud equivalente pendiente}
    D -->|Sí| E[ADVERTIR duplicidad]
    D -->|No| F{Prioridad y destino definidos}
    E --> F
    F -->|No| G[Solicitar completar]
    F -->|Sí| H[Crear SOLICITADO]
```

### `MED-RES-001` — Carga y clasificación del resultado

```mermaid
flowchart TD
    A[Cargar resultados] --> B{Componentes pertenecen al tipo de estudio}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Cada componente tiene valor y unidad cuando aplica}
    C -->|No| Y[Solicitar completar]
    C -->|Sí| D{Rango y clasificación son coherentes}
    D -->|No| E[DATOS INCONSISTENTES]
    D -->|Sí| F{Resultado requiere revisión prioritaria según protocolo}
    F -->|Sí| G[ALERTA con componente y evidencia]
    F -->|No| H[Marcar REALIZADO pendiente de informe/revisión]
```

### `MED-INF-001` — Informe y cierre del estudio

```mermaid
flowchart TD
    A[Crear informe] --> B{Estudio realizado y resultados disponibles}
    B -->|No| X[ADVERTIR insuficiencia]
    B -->|Sí| C{Origen interno o externo identificado}
    C -->|No| Y[Solicitar origen]
    C -->|Sí| D{Hallazgos, conclusión o recomendación presentes}
    D -->|No| Z[NO GUARDAR]
    D -->|Sí| E{Conclusión requiere acción clínica}
    E -->|Sí| F[Crear pendiente de seguimiento]
    E -->|No| G[Marcar revisado]
    F --> G
```

---

## 18. Documento clínico

### `MED-DOC-001` — Incorporación segura al expediente

```mermaid
flowchart TD
    A[Subir documento] --> B{Archivo permitido y tamaño válido}
    B -->|No| X[RECHAZAR]
    B -->|Sí| C{Pertenece al residente y a estudio/atención cuando aplica}
    C -->|No| Y[BLOQUEAR]
    C -->|Sí| D{Tipo, título y origen presentes}
    D -->|No| Z[Solicitar metadatos]
    D -->|Sí| E[Calcular hash y comprobar duplicado]
    E -->|Duplicado| F[Advertir y vincular versión si corresponde]
    E -->|Nuevo| G[Guardar en almacenamiento privado]
    F --> G
    G --> H{Requiere validación profesional}
    H -->|Sí| I[Estado PENDIENTE DE VALIDACIÓN]
    H -->|No| J[Estado VIGENTE]
```

El contenido del archivo no se convierte automáticamente en diagnóstico ni resultado estructurado.

---

## 19. Derivación e interconsulta

### `MED-DER-001` — Derivación

```mermaid
flowchart TD
    A[Solicitar derivación] --> B{Área solicitante y receptora distintas y válidas}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Motivo y prioridad documentados}
    C -->|No| Y[Solicitar completar]
    C -->|Sí| D{Existe derivación equivalente pendiente}
    D -->|Sí| E[Advertir duplicidad]
    D -->|No| F[Crear PENDIENTE]
    E --> F
    F --> G{Responsable receptor asignado}
    G -->|No| H[Cola del área receptora]
    G -->|Sí| I[Notificar responsable]
    H --> J{Respuesta registrada dentro del plazo aprobado}
    I --> J
    J -->|No| K[Escalar pendiente]
    J -->|Sí| L[Cerrar con respuesta]
```

### `MED-INT-001` — Interconsulta

La nota de tipo `INTERCONSULTA` debe vincularse a una derivación o solicitud estructurada, contener pregunta clínica concreta, destinatario, prioridad y respuesta. No debe depender únicamente de buscar una palabra en una nota.

---

## 20. Valoración geriátrica, cognición y riesgo

### `MED-GER-001` — Integración de instrumentos y dominios

```mermaid
flowchart TD
    A[Revisión geriátrica] --> B{Instrumentos autorizados y vigentes}
    B -->|No| X[NO EVALUABLE: programar instrumento]
    B -->|Sí| C{Aplicación completa y respuestas válidas}
    C -->|No| Y[DATOS INCONSISTENTES]
    C -->|Sí| D{Clasificación cumple criterio de seguimiento}
    D -->|Sí| E[Crear recomendación por dominio]
    D -->|No| F{Existe cambio respecto a evaluación previa}
    F -->|Sí| E
    F -->|No| G[SIN HALLAZGO]
    E --> H{Dominio afectado}
    H -->|Cognitivo/psicológico| I[Interconsulta correspondiente]
    H -->|Funcional/caídas| J[Fisioterapia/enfermería]
    H -->|Nutricional| K[Nutrición]
    H -->|Médico| L[Seguimiento médico]
```

Los instrumentos deben estar autorizados, versionados y cargados con sus reglas metodológicas. No se debe reproducir contenido protegido sin verificar derechos de uso.

---

## 21. Alertas médicas

### `MED-ALT-001` — Generación, atención y cierre

```mermaid
flowchart TD
    A[Hallazgo del motor o alerta manual] --> B{Evidencia válida y residente identificado}
    B -->|No| X[NO CREAR]
    B -->|Sí| C{Existe alerta activa con misma huella}
    C -->|Sí| D[Actualizar evidencia; no duplicar]
    C -->|No| E{Prioridad calculada por regla aprobada}
    E -->|No| F[Solicitar prioridad manual motivada]
    E -->|Sí| G[Crear ABIERTA]
    F --> G
    D --> H{Profesional reconoce y revisa fundamento}
    G --> H
    H -->|Rechaza| I[Registrar descarte/override y motivo]
    H -->|Acepta| J[Asignar responsable y pasar a EN ATENCIÓN]
    J --> K{Intervención y resultado documentados}
    K -->|No| L[Mantener abierta o escalar por plazo]
    K -->|Sí| M{Criterio de cierre cumplido}
    M -->|No| L
    M -->|Sí| N[Cerrar con evento auditable]
```

### Contenido obligatorio de una alerta

- regla y versión;
- fecha de evaluación;
- residente;
- prioridad;
- datos que activaron la regla;
- registros fuente y fechas;
- datos faltantes o desconocidos;
- explicación en lenguaje claro;
- acción sugerida;
- profesional destinatario;
- estado y plazo;
- validación, rechazo o cierre profesional.

### Familias iniciales de alertas médicas

| Código | Familia | Evidencia principal |
|---|---|---|
| `ALT-MED-SV` | Signos y tendencia | signos_vitales |
| `ALT-MED-DOL` | Dolor sin respuesta o seguimiento | valoraciones_dolor |
| `ALT-MED-ALE` | Alergia y medicación | alergias, prescripciones |
| `ALT-MED-PRE` | Prescripción inconsistente | prescripciones, horarios |
| `ALT-MED-REA` | Reacción adversa | administraciones_medicacion |
| `ALT-MED-EST` | Resultado que requiere revisión | resultados_estudio, informes_estudio |
| `ALT-MED-DER` | Derivación sin respuesta | derivaciones |
| `ALT-MED-GER` | Deterioro por instrumento | aplicaciones_instrumento |
| `ALT-MED-SEG` | Seguimiento vencido | atenciones, notas, indicaciones |
| `ALT-MED-DAT` | Datos clínicos contradictorios | múltiples fuentes |

Los umbrales y plazos quedan pendientes de aprobación médica e institucional.

---

## 22. Seguimiento, revisión y egreso

### `MED-SEG-001` — Seguimiento longitudinal

```mermaid
flowchart TD
    A[Cerrar atención o emitir conducta] --> B{Existen pendientes clínicos}
    B -->|No| C[Seguimiento ordinario según plan]
    B -->|Sí| D{Cada pendiente tiene responsable y fecha}
    D -->|No| E[ADVERTIR: seguimiento incompleto]
    D -->|Sí| F[Programar revisión]
    F --> G{Llegó fecha sin registro de cumplimiento}
    G -->|Sí| H[ALERTA de seguimiento vencido]
    G -->|No| I[Esperar o registrar resultado]
    I --> J{Resultado resuelve el pendiente}
    J -->|No| K[Reevaluar plan]
    J -->|Sí| L[Cerrar pendiente con evidencia]
```

### `MED-EGR-001` — Egreso o cierre médico

```mermaid
flowchart TD
    A[Proponer egreso/cierre] --> B{Atención y nota de egreso completas}
    B -->|No| X[BLOQUEAR]
    B -->|Sí| C{Alertas críticas abiertas}
    C -->|Sí| Y[No cerrar hasta resolución o justificación autorizada]
    C -->|No| D{Resultados, derivaciones y seguimientos pendientes}
    D -->|Sí| E[Asignar continuidad y responsable]
    D -->|No| F{Conciliación de medicación completa}
    E --> F
    F -->|No| G[Solicitar conciliación]
    F -->|Sí| H{Recomendaciones y signos de alarma documentados}
    H -->|No| I[Solicitar completar]
    H -->|Sí| J[Confirmación profesional]
    J -->|Sí| K[Registrar egreso/cierre e historial de estado]
```

El cambio institucional definitivo de estado debe respetar el flujo de admisión/egreso autorizado y no depender únicamente de una nota.

## 23. Flujo transaccional recomendado

Toda acción médica seguirá este patrón:

```text
Autorizar
→ validar identidad profesional y residente
→ validar datos y relaciones
→ guardar registro clínico en transacción
→ emitir evento de dominio después del commit
→ evaluar reglas relacionadas
→ persistir resultado y evidencias
→ proyectar alerta si corresponde
→ notificar al rol responsable
```

Las vistas solo consultan y presentan información. El motor no se ejecuta desde `render()`.

## 24. Cambios arquitectónicos necesarios antes de implementar

1. Unificar `ValoracionMedicaModal` y `ValoracionMedicaPanel`.
2. Crear un `ValoracionMedicaService` o Action único.
3. Separar datos referidos de antecedentes, diagnósticos y alergias confirmados.
4. Eliminar fallback a cualquier personal y códigos de área fijos.
5. Evitar que la valoración médica formalice por sí misma una admisión.
6. Centralizar notas médicas en un servicio con corrección versionada.
7. Usar un único flujo de prescripción; actualmente hay modal y controlador paralelos.
8. No crear medicamentos de catálogo automáticamente desde texto libre.
9. Vincular interconsulta con derivación estructurada.
10. Mover reglas de signos fuera de Livewire.
11. Centralizar creación y ciclo de vida de alertas.
12. Crear permisos médicos específicos por acción; no usar solo `valoracion_medica.ver` como puerta para todo el módulo.

## 25. Pruebas requeridas

Cada árbol debe cubrir:

- profesional sin permiso;
- personal inactivo o ausente;
- residente inexistente o fuera de alcance;
- atención de otro residente;
- formulario incompleto;
- borrador;
- registro confirmado;
- dato en límite técnico;
- criterio clínico activado y no activado;
- ausencia de datos que produce `NO_EVALUABLE`;
- evidencia contradictoria;
- ejecución repetida sin duplicar alertas;
- cambio de evidencia que actualiza el resultado;
- aceptación y rechazo profesional;
- cierre con evidencia;
- corrección sin destrucción del historial;
- autorización por rol;
- compatibilidad PostgreSQL y SQLite.

## 26. Orden de construcción

### Etapa 1 — Unificación del registro

1. Atención médica.
2. Nota clínica.
3. Antecedentes, diagnósticos y alergias.
4. Signos y dolor.
5. Valoración médica de admisión.

### Etapa 2 — Conducta clínica

6. Indicaciones.
7. Prescripción y suspensión.
8. Estudios y resultados.
9. Derivaciones e interconsultas.

### Etapa 3 — Inteligencia explicable

10. Cola priorizada.
11. Alertas médicas versionadas.
12. Seguimiento vencido.
13. Integración geriátrica.
14. Egreso y continuidad.

### Etapa 4 — Validación

15. Modo sombra.
16. Revisión médica de resultados.
17. Ajuste mediante nuevas versiones.
18. Piloto controlado.

## 27. Referencias marco

- WHO, *Integrated care for older people (ICOPE): guidance for person-centred assessment and pathways in primary care*, 2.ª ed.: https://www.who.int/publications/i/item/9789240103726
- WHO, *Medication Without Harm*: https://www.who.int/initiatives/medication-without-harm
- FDA, *Clinical Decision Support Software*: https://www.fda.gov/regulatory-information/search-fda-guidance-documents/clinical-decision-support-software
- WHO, *Ethics and governance of artificial intelligence for health*: https://www.who.int/publications/i/item/9789240029200
- HL7, *CDS Hooks 2.0*: https://cds-hooks.hl7.org/2.0/

Estas referencias proporcionan principios de seguridad, revisión independiente, seguimiento y gobernanza. Los umbrales clínicos y protocolos finales deben ser aprobados para el contexto institucional y regulatorio aplicable.
