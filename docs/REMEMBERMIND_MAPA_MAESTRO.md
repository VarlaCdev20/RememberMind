# RememberMind — Mapa maestro del sistema

## 1. Propósito de este documento

Este documento explica **qué es RememberMind, qué debe resolver, cómo se conectan sus módulos, cuál es el ciclo institucional y clínico del residente y hacia qué producto debe evolucionar**.

No reemplaza:

- `AGENTS.md` y los `AGENTS.md` específicos: indican cómo debe trabajar Codex;
- `REMEMBERMIND_BDD_BASELINE_CONGELADO.md`: gobierna la estructura congelada de la BDD V2;
- `REMEMBERMIND_BDD_69_TABLAS.md`: define entidades, atributos, PK, FK y relaciones;
- documentación funcional específica de cada módulo.

Este archivo sirve como **mapa mental y funcional global** para que desarrolladores y Codex comprendan el sistema antes de trabajar en una vista, módulo o flujo aislado.

---

# 2. Qué es RememberMind

RememberMind es un sistema web institucional para una residencia geriátrica que integra:

- gestión institucional;
- preadmisión y admisión;
- residentes;
- habitaciones, camas y ocupaciones;
- contactos y familiares;
- documentación y consentimientos;
- expediente clínico interdisciplinario;
- seguimiento longitudinal;
- medicación;
- planes de cuidado;
- estudios clínicos;
- instrumentos de evaluación;
- actividades y visitas;
- alertas e incidentes;
- asignaciones de personal y jornadas;
- auditoría y trazabilidad;
- una futura capa inteligente de apoyo para detección temprana de deterioro cognitivo.

La entidad central del sistema operativo es `residentes`. Toda información asistencial, clínica, cognitiva, funcional, de cuidado, medicación, actividades, visitas y alertas debe poder relacionarse directa o indirectamente con `cod_residente`.

RememberMind no debe entenderse como una colección de CRUD. Debe representar **procesos reales conectados**.

---

# 3. Idea central del producto

La idea funcional global es:

```text
PERSONA INTERESADA / POSTULANTE
        ↓
PREADMISIÓN
        ↓
REVISIÓN INSTITUCIONAL
        ↓
APROBADA / RECHAZADA
        ↓
ADMISIÓN FORMAL + CAMA + CONTACTOS + CONSENTIMIENTOS
        ↓
RESIDENTE ADMITIDO
        ↓
EXPEDIENTE + CONTEXTO INSTITUCIONAL
        ↓
ATENCIÓN INTERDISCIPLINARIA
        ↓
SEGUIMIENTO DIARIO Y LONGITUDINAL
        ↓
MEDICACIÓN + PLANES DE CUIDADO + ESTUDIOS + INSTRUMENTOS
        ↓
ACTIVIDADES + VISITAS + RELACIÓN FAMILIAR
        ↓
ALERTAS + TRAZABILIDAD + REPORTES
        ↓
FUTURA CAPA EXPERTA DE APOYO COGNITIVO
```

La información debe acumular historia. RememberMind no debe comportarse como una ficha que únicamente muestra el “estado actual”; debe permitir comprender **qué pasó, cuándo, quién lo registró y cómo evolucionó el residente**.

---

# 4. Diferencia de conceptos fundamentales

## Postulante / adulto mayor

Persona que todavía está en proceso de preadmisión y **aún no es residente**.

## Residente

Persona formalmente admitida a la residencia.

## Usuario

Cuenta de acceso al sistema.

## Personal

Trabajador o profesional de la residencia, vinculado cuando corresponde a una cuenta de usuario.

## Contacto

Familiar, responsable o persona vinculada al residente.

Estos conceptos no deben mezclarse en código, vistas, permisos o documentación.

---

# 5. Flujo institucional V2 congelado

El flujo obligatorio de ingreso es:

```text
Preadmisión PENDIENTE
        ↓
      revisión
        ↓
┌──────────────┬──────────────┐
│   APROBADA   │   RECHAZADA  │
└──────┬───────┴──────────────┘
       │
       ↓
ADMISIÓN FORMAL
       ↓
ASIGNACIÓN DE CAMA
       ↓
RESIDENTE ADMITIDO
```

Reglas obligatorias:

1. Aprobar una preadmisión **no crea un residente**.
2. El residente se crea únicamente al formalizar la admisión.
3. La admisión formal debe ser transaccional.
4. La formalización crea o relaciona, según corresponda:
   - `residentes`;
   - `admisiones`;
   - `residentes_contactos`;
   - `ocupaciones_cama`;
   - `historial_estados_residente`;
   - `consentimientos`;
   - documentación y datos iniciales pertinentes.
5. Una cama ocupada no puede asignarse a otro residente.
6. Un residente no puede tener dos ocupaciones activas.
7. No debe existir un CRUD alternativo para crear residentes saltándose admisión.

---

# 6. Antes del ingreso: preadmisión

La preadmisión representa la evaluación institucional previa.

El sistema debe permitir como mínimo que personal autorizado pueda:

- registrar al postulante;
- registrar la información requerida para evaluación;
- adjuntar/relacionar documentación administrativa pertinente;
- revisar la solicitud;
- identificar información pendiente;
- aprobar o rechazar conforme al proceso institucional definido;
- conservar la trazabilidad de la decisión.

Una preadmisión aprobada significa:

> “La persona puede continuar al proceso de admisión”.

No significa:

> “La persona ya vive en la residencia”.

---

# 7. Admisión formal

La admisión es la frontera entre postulante y residente.

Debe reunir en una sola operación institucional coherente:

```text
Preadmisión aprobada
        +
Datos de ingreso
        +
Contacto(s)
        +
Consentimiento(s)
        +
Cama disponible
        +
Documentación inicial
        ↓
RESIDENTE ADMITIDO
```

La operación debe fallar de forma segura si una condición crítica no puede completarse.

Ejemplo:

```text
se crea residente
pero la cama ya fue ocupada por otro proceso
        ↓
ROLLBACK
        ↓
no queda un residente parcial/huérfano
```

---

# 8. Habitaciones, camas y ocupación

La ubicación física se modela mediante:

```text
habitaciones
    ↓
camas
    ↓
ocupaciones_cama
    ↓
residentes
```

La cama actual de un residente se determina por su ocupación activa; no debe duplicarse como una segunda fuente de verdad arbitraria en `residentes`.

El sistema debe permitir comprender rápidamente:

- qué habitaciones existen;
- qué camas existen;
- qué camas están libres;
- qué camas están ocupadas;
- qué residente ocupa cada cama;
- desde cuándo;
- historial de ocupaciones cuando corresponda.

Las vistas de admisión deben impedir seleccionar una cama no disponible, pero la validación definitiva debe realizarse también en backend.

---

# 9. El residente como centro del sistema

Una vez admitido, el residente se convierte en el punto de unión de los demás procesos.

Conceptualmente su contexto puede verse así:

```text
                         ┌─ contactos / familia
                         ├─ documentos / consentimientos
                         ├─ cama / ubicación
                         ├─ antecedentes / diagnósticos / alergias
                         ├─ atenciones / notas
                         ├─ signos / dolor / antropometría
RESIDENTE ───────────────┼─ estudios clínicos
                         ├─ indicaciones
                         ├─ prescripciones / administraciones
                         ├─ cognición / conducta / sueño
                         ├─ ingesta / hidratación / eliminación
                         ├─ movilidad / heridas / curaciones
                         ├─ valoraciones profesionales
                         ├─ planes de cuidado
                         ├─ instrumentos
                         ├─ actividades / visitas
                         └─ alertas / incidentes
```

La pantalla del residente no debería ser un formulario gigante. Debe funcionar como **expediente y punto de navegación contextual**.

---

# 10. Jornadas, turnos y organización del trabajo

RememberMind también representa la operación diaria de la residencia.

Conceptualmente:

```text
usuarios
   ↓
personal
   ↓
turnos / áreas / jornadas
   ↓
asignaciones_personal
   ↓
asignaciones_residente_jornada
   ↓
trabajo efectivo sobre residentes
```

Esto permite que la información clínica y de cuidado tenga contexto institucional: quién estaba asignado, en qué jornada y qué profesional registró una acción.

No toda información necesita una jornada obligatoriamente; se aplica según la estructura congelada de cada tabla.

---

# 11. Atención clínica interdisciplinaria

`atenciones` funciona como uno de los puntos principales de contexto clínico.

Desde una atención pueden originarse, según corresponda:

- notas clínicas;
- diagnósticos;
- estudios clínicos;
- indicaciones clínicas;
- prescripciones;
- aplicaciones de instrumentos;
- valoraciones psicológicas;
- valoraciones nutricionales;
- valoraciones funcionales.

Conceptualmente:

```text
RESIDENTE
   ↓
ATENCIÓN
   ├─ nota clínica
   ├─ diagnóstico
   ├─ indicación
   ├─ estudio
   ├─ prescripción
   ├─ instrumento
   └─ valoración profesional
```

El expediente es interdisciplinario, pero cada profesión trabaja dentro de su competencia y permisos.

---

# 12. Seguimiento longitudinal

RememberMind debe registrar evolución temporal, no sobrescribir observaciones anteriores.

El seguimiento incluye estructuras como:

- signos vitales;
- dolor;
- antropometría;
- controles cognitivos;
- conducta;
- sueño;
- ingesta;
- hidratación;
- eliminación;
- movilidad;
- heridas y curaciones;
- instrumentos;
- valoraciones profesionales.

La experiencia ideal debe permitir responder preguntas como:

- ¿cómo evolucionó este residente durante la última semana?
- ¿hubo cambios de conducta?
- ¿cómo cambió la movilidad?
- ¿existe una tendencia en peso o antropometría?
- ¿qué ocurrió antes de una alerta?
- ¿qué profesional registró cada observación?

---

# 13. Flujo de medicación

La medicación tiene cuatro conceptos diferentes:

```text
MEDICAMENTO
    ↓
PRESCRIPCIÓN
    ↓
HORARIOS
    ↓
ADMINISTRACIÓN REAL
```

## Medicamento

Catálogo de qué medicamento existe.

## Prescripción

Orden indicada por el médico.

## Horario de prescripción

Cuándo corresponde administrar.

## Administración

Qué ocurrió realmente: administrado, omitido o el estado que corresponda al modelo aprobado.

Regla profesional fundamental:

```text
MÉDICO
→ prescribe

ENFERMERÍA
→ consulta la orden
→ administra/documenta
→ NO modifica la prescripción
```

La interfaz de Enfermería debe estar orientada al trabajo del turno: qué medicamento corresponde, a quién, cuándo y qué queda pendiente, manteniendo siempre el contexto del residente.

---

# 14. Planes de cuidado

La estructura es:

```text
PLAN DE CUIDADO
      ↓
INTERVENCIONES
    ↙       ↘
PROGRAMACIÓN  EJECUCIÓN
```

- plan: define el objetivo;
- intervención: define qué debe hacerse;
- programación: define cuándo debe hacerse;
- ejecución: registra qué ocurrió realmente.

No existe una entidad V2 `tareas_cuidado`.

El sistema debe separar claramente “lo programado” de “lo realizado”.

---

# 15. Estudios clínicos

Los estudios se modelan de forma genérica y normalizada:

```text
tipo_estudio_clinico
       ↓
componentes_estudio

RESIDENTE
   ↓
estudio_clinico
   ├─ resultados
   ├─ informe
   └─ documentos clínicos
```

Ejemplos:

```text
Hemograma
→ componente: hemoglobina
→ resultado: valor medido
→ documento: PDF del laboratorio
```

```text
Tomografía
→ informe: hallazgos/conclusión
→ documento: imagen/PDF/DICOM
```

No se debe crear una tabla nueva por cada estudio sin una futura decisión estructural aprobada.

---

# 16. Instrumentos y evaluaciones

La infraestructura V2 permite:

```text
instrumento
  ↓
preguntas
  ↓
opciones

residente
  ↓
aplicación del instrumento
  ↓
respuestas
```

Puede soportar instrumentos cognitivos o funcionales cuando su uso sea metodológica y legalmente autorizado.

No deben incorporarse preguntas, baremos, algoritmos o contenido protegido sin comprobar metodología y derechos.

---

# 17. Actividades

RememberMind contempla actividades y participación de residentes.

Conceptualmente:

```text
ÁREA / PERSONAL
      ↓
ACTIVIDAD
      ↓
PARTICIPANTES
      ↓
RESIDENTES
```

La actividad no es solamente una agenda. Puede formar parte del seguimiento institucional y, cuando sea pertinente, del contexto funcional/cognitivo del residente.

No debe duplicarse al mismo residente dentro de una actividad cuando la regla de integridad lo impide.

---

# 18. Familia, contactos y visitas

Los residentes pueden tener múltiples contactos y un contacto puede relacionarse según el modelo aprobado.

El vínculo se establece mediante `residentes_contactos`.

El familiar con acceso al sistema no es un profesional interno y no recibe acceso al expediente completo.

Puede acceder únicamente a información autorizada, por ejemplo:

- perfil básico autorizado;
- su relación de contacto;
- documentos/consentimientos autorizados;
- actividades autorizadas;
- visitas.

Por defecto no tiene acceso directo a información clínica sensible como:

- notas clínicas;
- valoraciones psicológicas;
- controles cognitivos;
- pases de turno;
- prescripciones detalladas;
- administraciones;
- resultados médicos;
- documentos clínicos sensibles.

El portal familiar debe sentirse como un espacio propio y seguro, no como una versión recortada del panel clínico interno.

---

# 19. Alertas e incidentes

Una alerta no debe ser solo un badge rojo.

El dominio distingue:

```text
alerta
   ↓
eventos_alerta
```

El ciclo puede registrar:

```text
creada
→ reconocida
→ asignada
→ atendida
→ cerrada/anulada
```

El historial no debe sobrescribirse.

La experiencia de usuario debe permitir responder:

- qué ocurrió;
- a qué residente afecta;
- qué gravedad/semántica aprobada tiene;
- quién debe atenderla;
- si alguien ya la reconoció;
- qué acciones se realizaron;
- cuándo fue cerrada.

Las alertas reales de riesgo/error deben utilizar semántica visual `danger`/rojo, pero la severidad debe venir del dominio y no inventarse en Blade/JavaScript.

---

# 20. Roles y experiencia por usuario

Roles vigentes:

1. SUPERADMINISTRADOR
2. ADMINISTRADOR
3. ENFERMEROS
4. MEDICO GENERAL/GERIATRA
5. PSICOLOGO/A
6. PEDAGOGO
7. NUTRICIONISTA
8. FISIOTERAPEUTA
9. FAMILIAR

`VOLUNTARIO` está fuera del alcance actual.

La experiencia no debe ser la misma para todos.

## Superadministrador

Debe poder supervisar globalmente el sistema, seguridad, usuarios, personal y configuración institucional. Tiene lectura total, pero no adquiere automáticamente competencia clínica para escribir.

## Administrador

Debe operar principalmente:

- personal/usuarios;
- áreas/turnos/jornadas;
- preadmisión/admisión;
- residentes;
- contactos;
- habitaciones/camas;
- documentos;
- consentimientos;
- operación institucional;
- actividades/visitas;
- alertas necesarias para operar.

No obtiene escritura clínica por ser administrador.

## Médico

Debe trabajar sobre expediente interdisciplinario, diagnósticos, indicaciones, estudios, prescripciones y seguimiento clínico pertinente.

Es el rol ordinario autorizado a prescribir.

## Enfermería

Debe tener una vista muy operativa del turno:

- residentes y ubicación;
- prescripciones/horarios;
- medicación pendiente;
- signos y dolor;
- planes;
- alertas;
- seguimiento diario;
- heridas/curaciones;
- pases de turno;
- ejecuciones de cuidado.

No prescribe.

## Psicología

Trabaja con contexto pertinente, cognición, conducta, sueño, instrumentos autorizados, valoraciones psicológicas e intervenciones de su ámbito.

## Nutrición

Trabaja con antropometría, ingesta, hidratación, eliminación, información clínica pertinente y planes/intervenciones nutricionales.

## Fisioterapia

Trabaja con función, movilidad, dolor pertinente, dispositivos, valoraciones funcionales y planes/intervenciones.

## Pedagogía

Trabaja con seguimiento pedagógico, cognición permitida, conducta, actividades y planes de su ámbito. No diagnostica.

## Familiar

Solo accede al residente vinculado y a la información expresamente autorizada.

---

# 21. Autorización global

Toda operación sensible debe evaluarse como:

```text
sesión autenticada
+
cuenta activa
+
permiso explícito
+
Policy contextual
+
regla de negocio válida
+
alcance / relación / competencia profesional
```

Ejemplos:

- conocer un `cod_residente` no autoriza a verlo;
- un familiar no puede abrir otro residente cambiando la URL;
- una enfermera no modifica una prescripción;
- un administrador no crea diagnósticos por su rol;
- un profesional no atribuye registros a otro profesional;
- Superadmin no adquiere competencia clínica automática.

---

# 22. Documentación

RememberMind distingue:

## Documentación administrativa

Ejemplos:

- identificación;
- documentación de ingreso;
- autorizaciones;
- documentos del contacto;
- otros documentos institucionales.

## Documentación clínica

Ejemplos:

- informes médicos;
- laboratorios;
- radiografías;
- tomografías;
- resonancias;
- DICOM;
- documentos clínicos externos.

Los archivos clínicos grandes deben estar en almacenamiento privado con metadatos/ruta en la BDD, no como BLOB por defecto.

---

# 23. Auditoría y trazabilidad

RememberMind debe poder contestar:

```text
¿quién hizo qué?
¿cuándo?
¿sobre qué residente/recurso?
¿qué estado tenía antes?
¿qué estado quedó después?
```

La auditoría técnica utiliza Spatie Activitylog según las reglas del proyecto.

La trazabilidad clínica también vive en las propias tablas del dominio mediante residente, profesional, atención/jornada cuando corresponda, fecha/hora y estado.

No se debe duplicar el texto clínico completo en una bitácora técnica.

---

# 24. Correcciones y conservación de historia

La historia clínica no debe “editarse hasta que parezca correcta”.

Los hechos históricos deben preservarse.

Según el dominio se utilizan:

- estado;
- anulación;
- suspensión;
- cierre;
- corrección enlazada;
- registro compensatorio.

El borrado físico ordinario de información clínica está prohibido.

---

# 25. Dashboards: qué deben responder

Un dashboard no debe responder “¿cuántas filas existen en mis tablas?”.

Debe responder al usuario:

```text
¿Qué tengo que saber ahora?
¿Qué necesita atención?
¿Qué debo hacer después?
¿Qué está pendiente?
¿Qué cambió?
```

Ejemplos según rol:

### Administración

- preadmisiones pendientes;
- admisiones por completar;
- disponibilidad de camas;
- documentos/consentimientos pendientes;
- ocupación;
- alertas operativas.

### Enfermería

- residentes asignados;
- medicación próxima/pendiente;
- controles pendientes;
- alertas activas;
- planes/intervenciones programadas;
- información del turno.

### Médico

- residentes que requieren revisión;
- estudios/resultados pertinentes;
- alertas clínicas;
- prescripciones/indicaciones relevantes;
- evolución clínica.

### Profesional interdisciplinario

- residentes asignados o relevantes;
- atenciones pendientes;
- valoraciones;
- intervenciones/planes;
- alertas pertinentes.

### Familiar

- información autorizada del residente;
- actividades/visitas;
- documentos o acciones autorizadas;
- comunicaciones que el proyecto defina formalmente.

No deben inventarse KPI sin dato real o regla funcional aprobada.

---

# 26. UX global esperada

RememberMind debe ser usable por personal que trabaja diariamente en una residencia geriátrica.

La experiencia debe priorizar:

1. claridad;
2. seguridad;
3. rapidez operativa;
4. prevención de errores;
5. accesibilidad;
6. consistencia;
7. estética.

Principios:

- cada input tiene label visible;
- validaciones inline claras;
- feedback de loading;
- prevención de doble submit;
- success/info/warning/danger consistentes;
- rojo para error/riesgo/alerta real;
- toasts para resultados transitorios;
- alertas críticas persistentes en la pantalla;
- modales para decisiones/focos breves, no para procesos gigantes;
- responsive desktop/tablet/mobile;
- glassmorphism cálido y moderado como acento visual;
- contexto del residente visible cuando reduce el riesgo de registrar información en la persona equivocada.

---

# 27. Qué NO es RememberMind

RememberMind no debe convertirse en:

- un ERP financiero;
- un sistema de facturación;
- un sistema de pagos;
- una plataforma de voluntariado;
- un multitenant de instituciones/sedes sin decisión futura;
- una tabla universal de observaciones clínicas;
- un sistema que permita al sistema experto sustituir decisiones profesionales.

El baseline actual excluye expresamente economía, facturación, pagos, cuentas/movimientos financieros, voluntariado, instituciones, sedes y tablas universales genéricas.

---

# 28. Hacia dónde debe llegar el sistema

La meta funcional de la V2 operativa es disponer de una plataforma coherente donde:

1. la institución gestione el ingreso correctamente;
2. cada residente tenga un expediente longitudinal completo;
3. cada profesional vea y registre solo lo que corresponde a su competencia;
4. Enfermería pueda ejecutar el cuidado diario de forma eficiente;
5. la medicación sea trazable desde prescripción hasta administración;
6. planes e intervenciones puedan programarse y registrar su ejecución;
7. cambios clínicos/cognitivos/funcionales puedan observarse en el tiempo;
8. estudios y documentos queden integrados al expediente;
9. familia tenga acceso seguro y limitado;
10. alertas tengan ciclo de vida y responsables;
11. toda operación crítica sea auditable;
12. la información operativa sirva como base confiable para la futura capa inteligente.

---

# 29. Futuro: sistema experto para deterioro cognitivo

## Estado

La BDD operativa congelada declara que las tablas futuras del sistema experto están **fuera del baseline actual de 69 tablas**.

La documentación académica del proyecto plantea como dirección un:

> sistema experto para la detección temprana del deterioro cognitivo en adultos mayores, basado en modelado multicriterio.

La documentación también contempla conceptos como:

- inteligencia artificial simbólica;
- ingeniería del conocimiento;
- base de conocimiento;
- hechos, criterios y reglas;
- motor de inferencia;
- modelado multicriterio;
- ponderaciones/umbrales;
- clasificación de riesgo cognitivo;
- alertas preventivas;
- explicabilidad;
- trazabilidad del razonamiento;
- validación con experto humano.

## Principio funcional

La futura capa inteligente debería **consumir información confiable del sistema operativo** y generar apoyo a la decisión, no reemplazar al profesional.

Conceptualmente:

```text
HISTORIAL DEL RESIDENTE
   ├─ controles cognitivos
   ├─ instrumentos autorizados
   ├─ conducta
   ├─ movilidad
   ├─ valoraciones profesionales
   ├─ otros criterios validados
   ↓
BASE DE CONOCIMIENTO
   ↓
MOTOR DE INFERENCIA / MODELO MULTICRITERIO
   ↓
CLASIFICACIÓN / SEÑAL PREVENTIVA
   ↓
EXPLICACIÓN DEL PORQUÉ
   ↓
REVISIÓN POR PROFESIONAL
   ↓
ACCIÓN HUMANA / SEGUIMIENTO
```

## Lo que todavía NO debe inventarse

Hasta que exista una definición metodológica validada, Codex no debe inventar:

- pesos;
- reglas clínicas;
- umbrales;
- scores;
- sensibilidad/especificidad objetivo;
- clasificación exacta de riesgo;
- preguntas o baremos de instrumentos protegidos;
- nuevas tablas del sistema experto.

Esos elementos requieren documentación científica/metodológica, validación y decisiones explícitas del proyecto.

---

# 30. Flujo global resumido por etapas

```text
┌─────────────────────────────────────────────┐
│ 0. CONFIGURACIÓN INSTITUCIONAL              │
│ usuarios • personal • roles • permisos      │
│ áreas • turnos • habitaciones • camas       │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 1. PREADMISIÓN                              │
│ postulante • datos • documentos • revisión  │
└──────────────────────┬──────────────────────┘
                       ↓
              APROBADA / RECHAZADA
                       ↓
┌─────────────────────────────────────────────┐
│ 2. ADMISIÓN FORMAL                          │
│ residente • contacto • consentimiento       │
│ cama • ocupación • historial • documentos   │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 3. CONTEXTO DEL RESIDENTE                   │
│ perfil • ubicación • expediente • familia   │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 4. ATENCIÓN INTERDISCIPLINARIA              │
│ médico • enfermería • psicología • nutrición│
│ fisioterapia • pedagogía                    │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 5. CUIDADO DIARIO Y SEGUIMIENTO             │
│ signos • dolor • cognición • conducta       │
│ sueño • ingesta • hidratación • movilidad   │
│ heridas • jornadas • pases                  │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 6. TRATAMIENTO / CUIDADO PLANIFICADO        │
│ prescripciones • horarios • administraciones│
│ planes • intervenciones • programaciones    │
│ ejecuciones                                 │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 7. EVALUACIÓN Y EVIDENCIA                    │
│ estudios • informes • documentos clínicos   │
│ instrumentos • valoraciones profesionales   │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 8. VIDA INSTITUCIONAL                       │
│ actividades • participación • visitas       │
│ familia                                     │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 9. ALERTAS + AUDITORÍA + TRAZABILIDAD       │
│ detectar • reconocer • asignar • atender    │
│ cerrar • conservar historia                 │
└──────────────────────┬──────────────────────┘
                       ↓
┌─────────────────────────────────────────────┐
│ 10. FUTURA CAPA EXPERTA                     │
│ apoyo cognitivo • riesgo • explicación      │
│ alerta preventiva • revisión profesional    │
└─────────────────────────────────────────────┘
```

---

# 31. Regla para desarrollar cualquier módulo

Antes de implementar una funcionalidad, Codex debería poder responder:

1. ¿En qué etapa del mapa maestro estoy?
2. ¿Quién utiliza esta función?
3. ¿Sobre qué residente/postulante/recurso trabaja?
4. ¿Qué información necesita ver?
5. ¿Qué puede registrar o modificar?
6. ¿Qué no debe poder hacer?
7. ¿Qué estado previo requiere?
8. ¿Qué estado/resultados produce?
9. ¿Qué otros módulos consume?
10. ¿Qué otros módulos dependen de este resultado?
11. ¿Qué debe quedar auditado?
12. ¿Qué historia debe conservarse?
13. ¿Qué errores deben impedirse?
14. ¿Qué pruebas demuestran que el flujo está completo?

Si Codex no puede responder estas preguntas, todavía no comprende suficientemente el módulo.

---

# 32. Aspectos no definidos por las fuentes actuales

Las fuentes consultadas no permiten cerrar todavía, sin inventar requisitos, algunos procesos globales como:

- flujo institucional completo de egreso/baja/fallecimiento/traslado del residente;
- reglas exactas de comunicación institucional con familiares;
- política definitiva de notificaciones externas;
- reglas clínicas exactas y umbrales del futuro sistema experto;
- nuevas estructuras persistentes para la capa experta.

Cuando estos procesos sean necesarios deben documentarse y aprobarse explícitamente antes de convertirlos en reglas de producto o estructura de BDD.

---

# 33. Fuentes internas principales

Este mapa deriva principalmente de:

- `REMEMBERMIND_BDD_BASELINE_CONGELADO.md`;
- `REMEMBERMIND_BDD_69_TABLAS.md`;
- documentación académica vigente del proyecto sobre sistema experto y deterioro cognitivo;
- reglas vigentes de los `AGENTS.md` del repositorio.

Cuando este documento contradiga una fuente superior, prevalece la fuente superior y este mapa debe actualizarse.
