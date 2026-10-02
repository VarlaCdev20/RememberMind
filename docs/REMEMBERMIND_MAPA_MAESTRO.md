# RememberMind — Mapa maestro del sistema

## 1. Propósito de este documento

Este documento explica **qué es RememberMind, qué debe resolver, cómo se conectan sus módulos y cómo debe funcionar el cuidado institucional, clínico y diario del residente**.

Su objetivo principal es dar a desarrolladores y Codex una visión global del producto antes de trabajar una vista, formulario o módulo aislado.

Este documento se enfoca en:

- administración institucional;
- ingreso y permanencia del residente;
- cuidado diario;
- seguimiento continuo de salud;
- atención interdisciplinaria;
- evolución clínica y funcional;
- medicación;
- planes de cuidado;
- alertas;
- coordinación entre profesionales;
- comunicación operativa;
- trazabilidad;
- experiencia de usuario para registrar y comprender cambios del residente.

Este documento **no define el sistema experto ni sus algoritmos, inferencias, predicciones o representación del conocimiento**. Esa funcionalidad debe documentarse en un archivo independiente.

No reemplaza:

- `AGENTS.md` y los `AGENTS.md` específicos: indican cómo debe trabajar Codex;
- `REMEMBERMIND_BDD_BASELINE_CONGELADO.md`: gobierna la estructura congelada de la BDD V2;
- `REMEMBERMIND_BDD_69_TABLAS.md`: define entidades, atributos, PK, FK y relaciones;
- documentación funcional específica de cada módulo.

---

# 2. Qué es RememberMind

RememberMind es un sistema web institucional para una residencia geriátrica cuya función principal es **organizar el cuidado integral de las personas residentes y permitir que el personal comprenda su estado, su evolución y lo que necesita atención en cada momento**.

No debe entenderse como una colección de CRUD ni como una simple ficha médica digital.

Debe integrar, alrededor del residente:

- gestión institucional;
- personal, áreas, turnos y jornadas;
- preadmisión y admisión;
- residentes;
- habitaciones, camas y ocupaciones;
- contactos y familiares;
- documentación y consentimientos;
- antecedentes, diagnósticos, alergias y restricciones;
- expediente clínico interdisciplinario;
- signos vitales;
- dolor;
- antropometría;
- cognición;
- conducta;
- sueño;
- ingesta;
- hidratación;
- eliminación;
- movilidad;
- heridas y curaciones;
- medicación;
- estudios clínicos;
- valoraciones profesionales;
- planes e intervenciones de cuidado;
- actividades;
- visitas;
- incidentes;
- pases de turno;
- alertas;
- auditoría y trazabilidad.

La entidad central del sistema operativo es `residentes`. Toda información de salud, cuidado, seguimiento, funcionalidad, medicación, actividades, visitas y alertas debe poder relacionarse directa o indirectamente con `cod_residente`.

---

# 3. Idea central del producto

La idea global de RememberMind es:

```text
INSTITUCIÓN CONFIGURADA
        ↓
PERSONAL + ÁREAS + TURNOS + JORNADAS
        ↓
POSTULANTE
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
VALORACIÓN Y CONTEXTO INICIAL
        ↓
PLAN DE CUIDADO Y SEGUIMIENTO
        ↓
TRABAJO DIARIO POR TURNOS
        ↓
REGISTROS DE SALUD + CUIDADO + MEDICACIÓN + EVOLUCIÓN
        ↓
COMPARACIÓN CON HISTORIAL Y LÍNEA BASAL
        ↓
DETECCIÓN DE CAMBIOS
        ↓
ALERTAS + RESPONSABLE + RESPUESTA
        ↓
REEVALUACIÓN / INTERVENCIÓN
        ↓
NUEVOS REGISTROS
        ↓
HISTORIA LONGITUDINAL DEL RESIDENTE
```

RememberMind debe permitir responder en cualquier momento:

- ¿cómo está este residente hoy?;
- ¿qué cambió respecto a ayer, la semana pasada o su estado habitual?;
- ¿qué cuidados tiene programados?;
- ¿qué ya se realizó?;
- ¿qué falta?;
- ¿qué medicamentos corresponden?;
- ¿qué alertas están activas?;
- ¿qué ocurrió antes de una alerta?;
- ¿quién registró cada dato?;
- ¿qué profesional intervino?;
- ¿qué resultado tuvo la intervención?;
- ¿existe una tendencia que requiere revisión?;
- ¿hay información que debería escalarse al médico u otro profesional?;
- ¿qué debe entregarse al siguiente turno?

El sistema debe trabajar con **historia y evolución**, no únicamente con el último valor disponible.

---

# 4. Conceptos fundamentales

## Postulante / adulto mayor

Persona que se encuentra en proceso de preadmisión y todavía **no es residente**.

## Residente

Persona formalmente admitida a la residencia.

## Usuario

Cuenta de acceso al sistema.

## Personal

Trabajador o profesional de la residencia.

## Contacto

Familiar, responsable o persona relacionada con un residente.

No deben utilizarse estos conceptos como sinónimos en código, permisos, formularios o documentación.

---

# 5. Roles institucionales

RememberMind debe trabajar con **10 roles funcionales**:

1. `SUPERADMINISTRADOR`
2. `GERENTE`
3. `ADMINISTRADOR`
4. `ENFERMEROS`
5. `MEDICO GENERAL/GERIATRA`
6. `PSICOLOGO/A`
7. `PEDAGOGO`
8. `NUTRICIONISTA`
9. `FISIOTERAPEUTA`
10. `FAMILIAR`

`VOLUNTARIO` permanece fuera del alcance actual.

## 5.1. Superadministrador

Tiene supervisión global y lectura del sistema, administración de seguridad, usuarios, permisos y configuración técnica/institucional autorizada.

La lectura global no significa competencia clínica automática para modificar registros profesionales.

## 5.2. Gerente

El Gerente representa la gestión del personal y la administración laboral de alto nivel de la residencia.

Su ámbito funcional debe incluir principalmente:

- gestión del personal;
- altas y bajas administrativas del personal;
- información laboral disponible dentro de la estructura aprobada;
- organización general de recursos humanos;
- profesiones y especialidades registradas;
- seguimiento de disponibilidad del personal;
- planificación general de horarios y cobertura;
- revisión de distribución de personal;
- supervisión de áreas y necesidades de dotación;
- proceso institucional de contratación en aquello que pueda representarse con la estructura vigente.

Si en el futuro se requieren contratos laborales, expedientes de RR. HH., salarios u otra estructura que no exista en la BDD congelada, deberá diseñarse y aprobarse antes de modificar la BDD.

El Gerente no obtiene competencias clínicas por su cargo.

## 5.3. Administrador

El Administrador se enfoca en la **operación diaria de la residencia**.

Debe trabajar principalmente con:

- preadmisiones;
- admisiones;
- residentes;
- contactos;
- documentación administrativa;
- consentimientos;
- habitaciones;
- camas;
- ocupaciones;
- jornadas;
- asignaciones operativas;
- horarios operativos del personal dentro del modelo autorizado;
- actividades;
- visitas;
- coordinación administrativa de incidentes y alertas;
- seguros;
- seguimiento operativo de la residencia.

No debe tener escritura clínica únicamente por ser Administrador.

## 5.4. Médico general / geriatra

Debe disponer del expediente clínico interdisciplinario necesario y es el rol ordinario responsable de:

- evaluación médica;
- diagnósticos;
- indicaciones;
- prescripciones;
- interpretación clínica;
- revisión de cambios relevantes;
- establecimiento o ajuste de parámetros clínicos personalizados cuando corresponda;
- respuesta clínica a alertas;
- seguimiento médico.

## 5.5. Enfermería

Enfermería representa una de las funciones centrales del seguimiento diario.

Debe poder consultar lo necesario para el cuidado continuo y registrar, según competencia:

- signos vitales;
- dolor;
- administración de medicación;
- conducta;
- sueño;
- ingesta;
- hidratación;
- eliminación;
- movilidad;
- heridas;
- curaciones;
- incidentes;
- pases de turno;
- ejecuciones de cuidado;
- otros controles autorizados.

Enfermería **no prescribe ni modifica la orden médica**.

## 5.6. Psicología

Trabaja con contexto pertinente y registra valoraciones, notas, intervenciones, seguimiento e instrumentos autorizados dentro de su ámbito.

## 5.7. Nutrición

Trabaja con antropometría, ingesta, hidratación, información clínica pertinente, valoración nutricional e intervenciones nutricionales.

## 5.8. Fisioterapia

Trabaja con movilidad, dolor relacionado con su intervención, funcionalidad, dispositivos, restricciones, valoraciones funcionales y planes de su ámbito.

## 5.9. Pedagogía

Trabaja con actividades, participación, seguimiento pedagógico, conducta y aspectos cognitivos permitidos dentro de su competencia. No diagnostica.

## 5.10. Familiar

Accede únicamente a residentes vinculados y a información expresamente autorizada.

El portal familiar no debe ser una copia reducida del expediente clínico interno.

---

# 6. Flujo institucional V2 de ingreso

El flujo obligatorio es:

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

Reglas:

1. Aprobar una preadmisión **no crea un residente**.
2. El residente se crea únicamente al formalizar la admisión.
3. La admisión formal debe ser transaccional.
4. La operación crea o relaciona, según corresponda:
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

# 7. Ingreso y valoración inicial del residente

La admisión no debe terminar simplemente con “residente creado”.

Después del ingreso, el sistema debe facilitar la construcción del **contexto inicial del residente**, utilizando las entidades existentes y las competencias profesionales correspondientes.

El contexto inicial puede comprender:

- antecedentes;
- diagnósticos;
- alergias;
- medicamentos prescritos;
- dispositivos clínicos;
- documentación;
- seguro;
- signos vitales iniciales;
- dolor;
- antropometría;
- movilidad;
- estado nutricional;
- estado cognitivo registrado mediante mecanismos autorizados;
- conducta;
- sueño;
- restricciones;
- necesidades de cuidado;
- contactos;
- consentimientos.

El objetivo es obtener una **línea basal** que después permita comparar la evolución real del residente.

No todos los datos se registran por la misma persona: cada profesional debe registrar únicamente lo que corresponde a su competencia.

---

# 8. El expediente del residente

La pantalla del residente debe funcionar como un **centro de contexto, seguimiento y navegación**, no como un formulario gigante.

Conceptualmente:

```text
                         ┌─ ubicación / cama
                         ├─ contactos / familia
                         ├─ documentos / consentimientos
                         ├─ antecedentes / diagnósticos / alergias
                         ├─ signos vitales / dolor / antropometría
                         ├─ estudios clínicos
                         ├─ indicaciones
RESIDENTE ───────────────┼─ prescripciones / administraciones
                         ├─ cognición / conducta / sueño
                         ├─ ingesta / hidratación / eliminación
                         ├─ movilidad / heridas / curaciones
                         ├─ valoraciones profesionales
                         ├─ planes de cuidado
                         ├─ actividades / visitas
                         ├─ incidentes
                         └─ alertas / historial
```

La vista principal debe permitir identificar rápidamente:

- residente;
- habitación/cama;
- estado;
- alergias/restricciones críticas autorizadas;
- alertas activas;
- últimos registros importantes;
- tendencias recientes;
- plan de cuidado activo;
- medicación pendiente;
- tareas/intervenciones programadas;
- profesionales involucrados;
- accesos a cada dimensión del expediente.

---

# 9. Flujo diario de cuidado

El seguimiento diario debe ser uno de los núcleos funcionales de RememberMind.

## 9.1. Inicio de turno

Al iniciar una jornada o turno, el personal autorizado debe poder conocer:

- residentes asignados;
- ubicación de cada residente;
- alertas abiertas;
- incidencias del turno anterior;
- pases de turno;
- medicación pendiente/próxima;
- intervenciones de cuidado programadas;
- controles requeridos;
- cambios recientes;
- pendientes críticos.

## 9.2. Durante el turno

El sistema debe facilitar registros rápidos pero completos de:

- signos vitales;
- dolor;
- administración de medicación;
- ingesta;
- hidratación;
- eliminación;
- sueño;
- conducta;
- movilidad;
- heridas/curaciones;
- ejecuciones de cuidado;
- incidentes;
- observaciones pertinentes.

Cada registro debe actualizar inmediatamente el contexto histórico del residente y evaluar las reglas de validación y alerta aplicables.

## 9.3. Fin de turno

Antes de cerrar/entregar el turno debe facilitarse:

- revisión de pendientes;
- alertas no resueltas;
- medicación pendiente u omitida según el modelo;
- cambios observados durante el turno;
- incidentes;
- cuidados realizados;
- cuidados no realizados y motivo cuando corresponda;
- pase de turno estructurado;
- información que requiere seguimiento posterior.

El siguiente equipo debe recibir continuidad, no empezar “desde cero”.

---

# 10. Seguimiento longitudinal y comparación temporal

RememberMind debe preservar la evolución del residente.

Las entidades longitudinales incluyen, entre otras:

- `signos_vitales`;
- `valoraciones_dolor`;
- `mediciones_antropometricas`;
- `controles_cognitivos`;
- `registros_conductuales`;
- `registros_sueno`;
- `registros_ingesta`;
- `registros_hidratacion`;
- `registros_eliminacion`;
- `registros_movilidad`;
- `heridas` y `curaciones_herida`;
- `aplicaciones_instrumento`;
- valoraciones profesionales.

Nunca deben reducirse a un único valor sobrescribible.

La experiencia debe permitir comparar:

```text
ANTES
↓
INTERVENCIÓN / CAMBIO / PERIODO
↓
DESPUÉS
```

Ejemplos:

- presión arterial antes y después de un periodo de seguimiento;
- dolor antes y después de una intervención;
- movilidad antes y después de fisioterapia;
- peso/antropometría antes y después de un plan nutricional;
- evolución de una herida entre curaciones;
- cambios de conducta en diferentes periodos;
- calidad/cantidad de sueño por días;
- ingesta e hidratación por turno/día;
- evolución de resultados de instrumentos cuando sea metodológicamente válida su comparación.

---

# 11. Formularios clínicos y de cuidado

Los formularios de salud y seguimiento **no deben ser formularios mínimos de dos campos cuando el proceso requiere contexto clínico**.

Cada formulario debe utilizar de manera completa los campos aprobados que realmente aportan al proceso.

Debe presentar:

- labels visibles;
- unidades de medida;
- formato esperado;
- campos obligatorios claramente identificados;
- ayuda contextual;
- validaciones inline;
- fecha/hora de negocio;
- responsable derivado del usuario/profesional cuando corresponda;
- contexto del residente;
- últimos valores relevantes;
- comparación con valores anteriores;
- advertencias de cambio;
- observación cuando el modelo la permita;
- resultado de la validación.

Si una variable clínicamente necesaria **no existe estructuralmente en la BDD congelada**, no debe agregarse silenciosamente ni ocultarse en JSON/EAV. Debe documentarse como necesidad y solicitar aprobación de cambio estructural.

---

# 12. Formularios con contexto visual y gráficas

Las pantallas que registran datos longitudinales deben combinar:

```text
FORMULARIO ACTUAL
        +
ÚLTIMO REGISTRO
        +
GRÁFICA DE EVOLUCIÓN
        +
COMPARACIÓN
        +
RANGO / OBJETIVO AUTORIZADO
        +
ALERTAS RELACIONADAS
```

No es necesario colocar una gráfica decorativa en formularios administrativos estáticos.

Las gráficas son especialmente importantes para:

- presión arterial;
- frecuencia cardiaca;
- frecuencia respiratoria;
- temperatura;
- saturación de oxígeno;
- glucemia cuando sea registrada;
- dolor;
- peso y otras medidas antropométricas;
- movilidad;
- ingesta/hidratación cuando puedan representarse cuantitativamente;
- sueño cuando los datos registrados permitan comparación;
- instrumentos y valoraciones repetidas cuando sea metodológicamente válido.

Las visualizaciones deben poder mostrar, según el caso:

- últimas 24 horas;
- 7 días;
- 30 días;
- periodo personalizado;
- línea basal;
- objetivo/rango personalizado;
- máximo/mínimo;
- cambio absoluto;
- cambio porcentual cuando tenga sentido clínico;
- marcadores de alertas;
- marcadores de intervenciones o cambios relevantes.

Si existe un único registro, la interfaz debe indicarlo claramente en vez de inventar una tendencia.

---

# 13. Modelo de validación clínica de datos

Las validaciones clínicas no deben reducirse a:

```text
required | numeric
```

RememberMind debe diferenciar varios niveles.

## 13.1. Validación de formato

Ejemplos:

- campo requerido;
- numérico;
- fecha válida;
- unidad correcta;
- longitud;
- escala permitida.

## 13.2. Validación de plausibilidad

Debe detectar valores imposibles o probablemente producto de error de digitación/medición.

Un valor físicamente imposible puede bloquear el registro.

Un valor clínicamente inusual pero posible **no debería bloquearse automáticamente**: debe advertirse, solicitar confirmación o generar alerta según severidad.

## 13.3. Validación basada en evidencia

Cuando un dato tenga interpretación clínica, sus referencias generales deben provenir de fuentes clínicas verificables y vigentes.

No se debe inventar un rango por intuición del desarrollador.

## 13.4. Rango personalizado del residente

La edad por sí sola no basta para determinar qué es “normal”.

Cuando corresponda, el sistema debe permitir que el médico establezca o confirme objetivos/rangos individualizados considerando factores como:

- edad;
- fragilidad;
- multimorbilidad;
- diagnóstico;
- tratamiento;
- antecedentes;
- tolerancia;
- riesgo de caída;
- situación basal;
- otras condiciones clínicamente relevantes.

## 13.5. Línea basal personal

Además del rango clínico general, RememberMind debe comparar el dato con el comportamiento habitual del propio residente.

Ejemplo:

```text
valor actual
vs
últimos registros
vs
promedio/tendencia reciente
vs
línea basal
vs
objetivo personalizado
```

## 13.6. Cambio brusco

Aunque un valor todavía se encuentre en un rango aceptable, un cambio rápido respecto al estado habitual puede ser relevante.

La lógica de alerta debe poder considerar **delta y tendencia**, no solo límites absolutos.

## 13.7. Validación contextual

Cuando sea clínicamente pertinente, la interpretación puede necesitar contexto como:

- síntomas;
- postura;
- momento del día;
- intervención reciente;
- medicación;
- enfermedad actual;
- hidratación;
- contexto de la medición.

Si ese contexto requiere nuevos campos estructurados no presentes en la BDD, deberá evaluarse como cambio de modelo antes de implementarlo.

---

# 14. Ejemplo: presión arterial

La presión arterial ilustra por qué RememberMind no debe usar una única regla como:

```text
si edad = 82 → normal = X
```

Las guías clínicas muestran que los objetivos en adultos mayores requieren individualización, especialmente ante fragilidad y multimorbilidad.

Como referencia de diseño clínico:

- NICE NG136 mantiene para adultos con hipertensión de 80 años o más un objetivo de presión clínica inferior a 150/90 mmHg, usando juicio clínico ante fragilidad o multimorbilidad;
- las guías ESC 2024 disponen recomendaciones específicas para pacientes muy mayores o frágiles y enfatizan un enfoque individualizado;
- la hipotensión ortostática se define habitualmente por una caída sostenida de al menos 20 mmHg de presión sistólica o 10 mmHg de presión diastólica dentro de los 3 minutos de ponerse de pie, según la declaración científica de la American Heart Association de 2024.

Por tanto, el sistema debe poder representar conceptualmente:

```text
PRESIÓN REGISTRADA
      ↓
VALIDACIÓN DE DIGITACIÓN / PLAUSIBILIDAD
      ↓
COMPARACIÓN CON OBJETIVO PERSONALIZADO
      ↓
COMPARACIÓN CON LÍNEA BASAL
      ↓
ANÁLISIS DE CAMBIO / TENDENCIA
      ↓
¿HAY DESVIACIÓN RELEVANTE?
      ↓
NO → guardar y graficar
SÍ → guardar + advertir/generar alerta según regla autorizada
```

Una alerta automática no debe afirmar un diagnóstico.

Debe utilizar lenguaje de apoyo, por ejemplo:

> “Se detectó una variación de presión arterial fuera del rango configurado para este residente. Requiere revisión clínica.”

O:

> “Se detectó un descenso relevante respecto a los registros recientes. Verificar medición, síntomas y condición del residente.”

La decisión clínica continúa correspondiendo al personal competente.

---

# 15. Registro de reglas clínicas y fuentes

Toda regla automática que interprete un dato de salud debe tener trazabilidad documental.

Antes de activarse debería conocerse:

- variable;
- población aplicable;
- fuente clínica;
- organización/autores;
- versión/año;
- rango o criterio;
- excepciones;
- severidad asociada;
- acción esperada;
- responsable clínico que aprobó su uso institucional;
- fecha de revisión de la regla.

Ejemplo conceptual:

```text
Regla: variación de presión arterial
Fuente: guía clínica identificada
Versión: YYYY
Población: adultos mayores / condición específica
Criterio general: ...
Personalizable por médico: SÍ
Genera alerta: según severidad
Aprobada institucionalmente por: ...
Revisar: ...
```

Las fuentes clínicas deben revisarse periódicamente porque las guías cambian.

---

# 16. Clasificación de validaciones y respuesta

Una validación puede producir:

## Normal

El dato se registra y actualiza la gráfica/historial.

## Observación

Existe un cambio menor que conviene mostrar, pero no necesariamente requiere una alerta.

## Advertencia

El valor o tendencia requiere verificación del profesional.

La interfaz utiliza semántica warning.

## Alerta

Existe una situación definida institucionalmente que requiere atención o seguimiento activo.

## Crítica

Existe un criterio autorizado que requiere atención prioritaria/escalamiento inmediato según protocolo institucional.

Los niveles clínicos exactos no deben ser inventados por desarrollo: deben definirse con fuentes y aprobación profesional.

---

# 17. Motor operativo de alertas

Las alertas deben ser una capacidad transversal del sistema.

No deben limitarse a “poner una tarjeta roja”.

Flujo conceptual:

```text
DATO / EVENTO / TENDENCIA
          ↓
REGLAS AUTORIZADAS
          ↓
DETECCIÓN
          ↓
CREAR ALERTA
          ↓
CLASIFICAR SEVERIDAD
          ↓
ASIGNAR / DEFINIR RESPONSABLE
          ↓
NOTIFICAR
          ↓
RECONOCER
          ↓
ATENDER
          ↓
REGISTRAR ACCIÓN / RESULTADO
          ↓
CERRAR O ANULAR
```

`alertas` representa la alerta y `eventos_alerta` preserva su ciclo de vida.

El historial nunca debe sobrescribirse.

---

# 18. Qué puede originar una alerta

La generación automática debe implementarse solo después de definir/validar la regla correspondiente.

Posibles familias de alertas:

## 18.1. Umbral clínico

Un valor sale del rango autorizado para el residente.

## 18.2. Cambio brusco

Un valor cambia significativamente respecto a su registro anterior o línea basal.

## 18.3. Tendencia sostenida

Varios registros muestran deterioro progresivo aunque ninguno de manera aislada parezca extremo.

## 18.4. Combinación de señales

Varias dimensiones cambian de manera coincidente y la combinación ha sido definida como clínicamente relevante.

No debe inventarse una combinación sin respaldo y aprobación.

## 18.5. Medicación

Situaciones operativas como administraciones pendientes, omitidas o incidencias deben generar los avisos permitidos por el flujo de medicación.

## 18.6. Incidente

Caídas, lesiones u otros incidentes registrados pueden originar alertas según severidad/protocolo.

## 18.7. Heridas

Cambios registrados en una herida pueden requerir seguimiento según criterios profesionales autorizados.

## 18.8. Nutrición / hidratación

Patrones sostenidos de cambios en ingesta, hidratación o antropometría pueden generar advertencias cuando existan reglas clínicas aprobadas.

## 18.9. Conducta, sueño, movilidad o cognición

Cambios persistentes pueden generar solicitudes de revisión profesional cuando las reglas estén formalmente definidas.

---

# 19. Calidad de alertas

El sistema debe evitar dos extremos:

```text
demasiadas alertas → fatiga de alertas
muy pocas alertas → cambios importantes pasan desapercibidos
```

Por ello debe contemplarse:

- deduplicación;
- agrupación cuando varias mediciones representan el mismo problema;
- evitar crear una alerta nueva en cada refresh;
- reglas de repetición/cooldown cuando corresponda;
- severidad;
- responsable;
- reconocimiento;
- escalamiento;
- cierre con motivo/resultado;
- trazabilidad completa.

Una alerta activa debe ser visible hasta resolverse; no puede depender únicamente de un toast temporal.

---

# 20. Escalamiento de alertas

Las alertas deben poder seguir una matriz institucional.

Ejemplo conceptual:

```text
ALERTA
 ↓
PROFESIONAL / EQUIPO RESPONSABLE
 ↓
NO RECONOCIDA EN TIEMPO DEFINIDO
 ↓
ESCALAR A RESPONSABLE SUPERIOR / MÉDICO / ADMINISTRACIÓN
 ↓
REGISTRAR CADA ESCALAMIENTO EN eventos_alerta
```

Los tiempos y destinatarios concretos deben ser definidos institucionalmente según tipo y severidad.

---

# 21. Notificaciones de alertas

RememberMind debe poder notificar alertas mediante varios canales.

## 21.1. Dentro del sistema

Es el canal principal y debe mostrar:

- residente;
- tipo;
- severidad;
- hora;
- responsable;
- estado;
- contexto necesario;
- acción para abrir el detalle.

## 21.2. Correo electrónico

Puede utilizarse para alertas configuradas que requieren notificación fuera de la aplicación.

No debe enviar información clínica excesiva en el correo.

Preferir mensajes como:

> “RememberMind registró una alerta que requiere revisión. Ingrese al sistema para consultar el detalle.”

con contexto mínimo y enlace seguro autenticado.

## 21.3. WhatsApp

Puede integrarse mediante WhatsApp Business Platform para notificaciones autorizadas.

Debe contemplar:

- consentimiento/expectativa adecuada del destinatario;
- configuración del número institucional;
- plantillas aprobadas cuando correspondan;
- destinatarios autorizados;
- mínimos datos sensibles;
- registro de envío;
- estado de entrega cuando la integración lo permita;
- enlace hacia RememberMind para consultar detalles bajo autenticación.

Nunca se debe convertir WhatsApp en un expediente clínico paralelo.

## 21.4. Falla de canal externo

Si falla correo o WhatsApp:

- la alerta interna sigue existiendo;
- no se pierde el evento;
- se registra el fallo técnico;
- puede reintentarse de manera segura;
- no se duplica la alerta de negocio.

---

# 22. Gráficas, tendencias y panel clínico

Las gráficas deben ayudar a interpretar el seguimiento, no decorar.

Cada módulo longitudinal debe evaluar cuál es la visualización más útil.

Ejemplos:

## Signos vitales

Series temporales por variable con rango/objetivo autorizado y marcadores de alertas.

## Dolor

Evolución de intensidad + intervenciones registradas + respuesta.

## Antropometría

Peso y otras medidas en periodos útiles, comparando evolución.

## Movilidad

Evolución de registros/valoraciones que permitan comparación válida.

## Heridas

Timeline de curaciones y cambios registrados.

## Sueño / conducta / ingesta / hidratación / eliminación

Tendencias de registros según el tipo de dato almacenado y su semántica real.

Las visualizaciones nunca deben afirmar causalidad únicamente porque dos eventos aparezcan próximos en el tiempo.

---

# 23. Vista “antes y después”

Cuando exista una intervención relevante, la interfaz debe facilitar una comparación contextual.

Ejemplo:

```text
ANTES
Presión: ...
Dolor: ...
Movilidad: ...
Fecha/hora: ...

INTERVENCIÓN / PERIODO
...

DESPUÉS
Presión: ...
Dolor: ...
Movilidad: ...
Fecha/hora: ...

CAMBIO
...
```

La comparación solo debe incluir variables realmente disponibles y clínicamente comparables.

---

# 24. Atención clínica interdisciplinaria

`atenciones` funciona como uno de los contextos principales de atención profesional.

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

El expediente puede ser interdisciplinario, pero cada profesional registra solo dentro de su competencia y permisos.

La información importante debe poder ser consultada transversalmente por quienes realmente la necesiten para cuidar al residente.

---

# 25. Medicación

La cadena es:

```text
MEDICAMENTO
    ↓
PRESCRIPCIÓN
    ↓
HORARIOS
    ↓
ADMINISTRACIÓN REAL
```

## Médico

Prescribe y modifica/suspende la prescripción según el flujo autorizado.

## Enfermería

Consulta la prescripción y horarios, administra/documenta y registra lo ocurrido.

Enfermería no altera la orden médica.

La pantalla de trabajo diario debe permitir conocer rápidamente:

- qué residente;
- qué medicamento;
- dosis/orden vigente según estructura aprobada;
- horario;
- estado;
- qué está pendiente;
- qué ya fue administrado;
- qué fue omitido/no administrado y el contexto permitido;
- alertas relacionadas.

---

# 26. Planes de cuidado

La estructura es:

```text
PLAN DE CUIDADO
      ↓
INTERVENCIONES
    ↙       ↘
PROGRAMACIÓN  EJECUCIÓN
```

- plan: objetivo;
- intervención: qué debe hacerse;
- programación: cuándo;
- ejecución: qué ocurrió realmente.

No existe `tareas_cuidado` en V2.

La interfaz debe diferenciar claramente:

```text
PROGRAMADO
vs
REALIZADO
vs
NO REALIZADO / PENDIENTE
```

según los estados realmente definidos.

---

# 27. Dolor

La valoración de dolor no debe reducirse a un número cuando la estructura permite mayor detalle.

Puede registrar, según la BDD aprobada:

- intensidad;
- ubicación;
- tipo;
- duración;
- desencadenante;
- intervención;
- respuesta;
- fecha/hora;
- estado.

La interfaz debe permitir comparar el dolor antes/después y mostrar evolución.

La escala usada y sus validaciones deben estar metodológicamente definidas.

---

# 28. Nutrición, antropometría, ingesta e hidratación

El seguimiento nutricional debe conectar:

```text
antropometría
+
ingesta
+
hidratación
+
eliminación
+
valoración nutricional
+
intervenciones
+
información clínica pertinente
```

La interfaz debe facilitar tendencias y cambios, evitando analizar cada registro aisladamente.

Las reglas automáticas deben ser validadas por Nutrición/Médico según corresponda antes de generar alertas clínicas.

---

# 29. Movilidad y fisioterapia

Debe facilitarse el seguimiento de:

- movilidad;
- dolor relacionado con la intervención;
- dispositivos;
- restricciones pertinentes;
- valoración funcional;
- planes/intervenciones;
- evolución en el tiempo.

Las comparaciones deben permitir comprender si existe mejora, estabilidad o deterioro según los datos registrados, sin convertir una variación aislada en diagnóstico automático.

---

# 30. Conducta, sueño y seguimiento psicológico

Los registros de conducta y sueño deben formar parte de la historia longitudinal.

Psicología debe poder contextualizar cambios con la información permitida y registrar valoraciones/intervenciones dentro de su ámbito.

El sistema debe facilitar la identificación visual de patrones, pero las conclusiones clínicas deben pertenecer al profesional competente.

---

# 31. Heridas y curaciones

El seguimiento de una herida debe conservar historia.

```text
HERIDA
 ↓
CURACIÓN 1
 ↓
CURACIÓN 2
 ↓
CURACIÓN 3
 ↓
EVOLUCIÓN
```

No debe sobrescribirse la valoración anterior.

La interfaz debe facilitar comparar registros sucesivos y destacar cambios relevantes cuando existan criterios clínicos aprobados.

---

# 32. Incidentes

Los incidentes deben registrarse con trazabilidad y conectarse al residente y al personal/contexto correspondiente según la BDD.

Según severidad y reglas institucionales pueden:

- crear una alerta;
- requerir atención profesional;
- requerir seguimiento;
- aparecer en el pase de turno;
- exigir cierre/documentación posterior.

---

# 33. Pases de turno

El pase de turno debe resumir información útil para continuidad del cuidado.

No debe convertirse en una copia automática de todo el expediente.

Debe ayudar a comunicar:

- cambios relevantes;
- alertas activas;
- incidencias;
- cuidados pendientes;
- situación de medicación;
- evolución que requiere observación;
- instrucciones/indicaciones pertinentes y autorizadas.

---

# 34. Estudios clínicos

La estructura es genérica:

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

Ejemplo:

```text
Hemograma
→ componente: hemoglobina
→ resultado: valor
→ documento: PDF
```

No se crea una tabla nueva por estudio sin aprobación estructural.

Los resultados repetidos comparables pueden presentarse en tendencia cuando resulte clínicamente apropiado.

---

# 35. Instrumentos

La infraestructura contempla:

```text
instrumento
  ↓
preguntas
  ↓
opciones

residente
  ↓
aplicación
  ↓
respuestas
```

Los instrumentos deben utilizarse únicamente cuando su metodología y derechos de uso estén autorizados.

No se deben inventar puntuaciones, preguntas, baremos o interpretación.

---

# 36. Familia, contactos y visitas

El familiar accede exclusivamente a residentes vinculados mediante `residentes_contactos` y a información expresamente autorizada.

Puede acceder a elementos permitidos como:

- perfil básico autorizado;
- relación de contacto;
- documentos/consentimientos autorizados;
- actividades autorizadas;
- visitas.

Por defecto no tiene acceso directo a:

- notas clínicas;
- valoraciones psicológicas;
- controles cognitivos;
- pases de turno internos;
- prescripciones detalladas;
- administraciones;
- resultados médicos;
- documentos clínicos sensibles.

---

# 37. Actividades

RememberMind contempla actividades y participación.

Las actividades pueden apoyar bienestar y seguimiento institucional, pero no deben confundirse automáticamente con tratamiento clínico.

Debe conservarse participación e historial cuando corresponda.

---

# 38. Documentación administrativa y clínica

Los documentos administrativos y los documentos clínicos son dominios distintos.

Los archivos clínicos sensibles deben permanecer en almacenamiento privado con acceso autorizado.

Los correos, WhatsApp u otros canales externos no deben utilizarse como almacenamiento paralelo de documentos sensibles.

---

# 39. Auditoría y trazabilidad

Debe poder determinarse:

```text
QUIÉN
HIZO QUÉ
SOBRE QUÉ RESIDENTE/RECURSO
CUÁNDO
EN QUÉ CONTEXTO
CON QUÉ RESULTADO
```

La auditoría técnica se apoya en Spatie Activitylog según las reglas del proyecto.

La trazabilidad clínica también vive en las propias entidades mediante residente, profesional, atención, jornada, fecha/hora y estado cuando corresponda.

No se debe duplicar el expediente completo dentro del log técnico.

---

# 40. Corrección de registros

La información clínica es longitudinal.

No debe borrarse físicamente ni sobrescribirse para ocultar errores.

Deben utilizarse los mecanismos definidos por el dominio:

- estado;
- anulación;
- suspensión;
- cierre;
- corrección enlazada;
- registro compensatorio.

---

# 41. Principio de seguridad

Toda operación sensible debe considerar:

```text
sesión autenticada
+
cuenta activa
+
permiso explícito
+
Policy contextual
+
regla de negocio
+
alcance/relación/competencia
```

Que una opción esté oculta en la interfaz no significa que esté protegida.

---

# 42. Principio de experiencia de usuario

RememberMind debe reducir carga cognitiva del personal.

La interfaz debe priorizar:

- contexto del residente;
- alertas;
- cambios recientes;
- pendientes;
- acción principal;
- historial relevante;
- comparación;
- validaciones claras;
- confirmación del resultado.

Los formularios deben ser completos sin convertirse en formularios interminables.

Puede utilizarse progressive disclosure, secciones, tabs o pasos cuando realmente ayuden.

---

# 43. Dashboard por rol

No todos los roles deben ver el mismo dashboard.

## Gerente

Prioriza personal, cobertura, organización de recursos humanos, distribución y necesidades de personal.

## Administrador

Prioriza operación institucional, preadmisiones, admisiones, camas, ocupaciones, jornadas, asignaciones, documentación, visitas y pendientes administrativos.

## Enfermería

Prioriza residentes del turno, alertas, cambios recientes, medicación, controles, cuidados programados y pendientes.

## Médico

Prioriza alertas clínicas que requieren revisión, evolución, residentes que requieren atención, estudios, indicaciones y medicación.

## Otros profesionales

Priorizan residentes/intervenciones de su ámbito, cambios relevantes y pendientes.

## Familiar

Prioriza información autorizada de su residente vinculado, actividades/visitas y comunicaciones permitidas.

---

# 44. Panel de seguimiento del residente

Debe existir una experiencia capaz de resumir, en una sola lectura autorizada:

```text
HOY
- últimas mediciones
- medicación
- cuidados
- alertas
- incidentes
- ingesta/hidratación
- movilidad
- dolor

TENDENCIAS
- 24 h
- 7 días
- 30 días

PENDIENTES
- controles
- medicación
- cuidados
- revisiones

HISTORIAL
- eventos clínicos
- intervenciones
- alertas
- cambios de estado
```

El detalle visible depende del rol y permisos.

---

# 45. Mejoras recomendadas para el producto

## 45.1. Línea basal personalizada

Calcular y visualizar el comportamiento habitual del residente utilizando registros históricos válidos, sin convertirlo automáticamente en criterio médico.

## 45.2. Rango clínico personalizado

Permitir que el profesional competente configure/confirme objetivos cuando el proceso lo requiera.

## 45.3. Marcadores de eventos en gráficas

Mostrar visualmente eventos relevantes como alertas, cambios de tratamiento o intervenciones cuando puedan relacionarse correctamente.

## 45.4. Detección de tendencias

Distinguir un dato aislado de un patrón persistente.

La lógica concreta debe validarse clínicamente.

## 45.5. Alertas deduplicadas

Evitar notificaciones repetidas del mismo evento sin información nueva.

## 45.6. Escalamiento

Permitir que una alerta no atendida avance al siguiente responsable conforme a protocolo.

## 45.7. Centro de notificaciones

Unificar alertas y notificaciones con filtros por estado, severidad, residente, fecha y responsable.

## 45.8. Alertas externas seguras

Correo y WhatsApp como canales complementarios, enviando información mínima y llevando al usuario autenticado a RememberMind.

## 45.9. Resumen de turno

Generar un resumen operativo basado en registros reales del periodo, sin sustituir el juicio del profesional.

## 45.10. Comparación contextual

Antes/después y tendencias alrededor de intervenciones cuando los datos permitan una comparación válida.

---

# 46. Gobernanza de reglas clínicas

Ninguna regla clínica automática debe llegar a producción únicamente porque “parece correcta”.

Proceso recomendado:

```text
NECESIDAD
↓
INVESTIGACIÓN CLÍNICA
↓
FUENTE VERIFICABLE
↓
DEFINICIÓN DE POBLACIÓN Y EXCEPCIONES
↓
REVISIÓN POR PROFESIONAL COMPETENTE
↓
APROBACIÓN INSTITUCIONAL
↓
IMPLEMENTACIÓN
↓
TESTS
↓
MONITOREO
↓
REVISIÓN PERIÓDICA
```

RememberMind puede detectar y avisar.

El sistema no debe sustituir al profesional ni presentar una alerta automática como diagnóstico definitivo.

---

# 47. Referencias clínicas iniciales para diseño de validaciones

Estas fuentes sirven como **punto de partida para diseñar reglas**, no como autorización automática para hardcodear todos sus valores.

## Presión arterial

- National Institute for Health and Care Excellence (NICE). *Hypertension in adults: diagnosis and management (NG136)*. La guía mantiene objetivos específicos para adultos de 80 años o más y exige juicio clínico ante fragilidad/multimorbilidad.
  - https://www.nice.org.uk/guidance/ng136

- European Society of Cardiology (ESC). *2024 ESC Guidelines for the Management of Elevated Blood Pressure and Hypertension*. Incluye manejo específico de personas muy mayores o frágiles y atención centrada en el paciente.
  - https://www.escardio.org/guidelines/clinical-practice-guidelines/all-esc-practice-guidelines/elevated-blood-pressure-and-hypertension/

- American Heart Association. *Orthostatic Hypotension in Adults With Hypertension: A Scientific Statement* (2024). Define hipotensión ortostática y aborda monitoreo en adultos con hipertensión.
  - https://www.ahajournals.org/doi/10.1161/HYP.0000000000000236

Cada nueva familia de reglas —saturación, temperatura, glucemia, dolor, nutrición, movilidad, sueño, etc.— debe tener su propia revisión documental antes de activar umbrales automáticos.

---

# 48. Comunicación por WhatsApp

La integración futura de WhatsApp debe utilizar la plataforma oficial de WhatsApp Business y respetar sus políticas vigentes.

Como criterio de diseño:

- el destinatario debe esperar/aceptar la comunicación según la política aplicable;
- mensajes iniciados por la institución pueden requerir plantillas aprobadas;
- las plantillas deben tener propósito claro;
- debe existir mecanismo de baja/gestión de preferencias cuando corresponda;
- no enviar historia clínica completa ni detalles innecesarios;
- el detalle sensible se consulta dentro de RememberMind bajo autenticación.

Referencia inicial:

- WhatsApp Business Messaging/Commerce Policy y material oficial de Meta sobre message templates.
  - https://business.whatsapp.com/policy

---

# 49. Fuera de alcance de este documento

Este mapa maestro **no define**:

- arquitectura ni reglas de un sistema experto;
- base de conocimiento;
- red semántica;
- motor de inferencia;
- reglas de producción;
- pesos;
- criterios multicriterio;
- predicciones;
- clasificación automatizada de riesgo cognitivo;
- explicabilidad de inferencias.

Esos elementos deben existir en un documento independiente dedicado exclusivamente a esa parte del proyecto.

---

# 50. Resultado final esperado

RememberMind debe llegar a comportarse como un sistema que acompaña el funcionamiento real de una residencia geriátrica:

```text
ADMINISTRA LA INSTITUCIÓN
        +
ORGANIZA AL PERSONAL
        +
ADMITE CORRECTAMENTE AL RESIDENTE
        +
ORGANIZA SU CUIDADO
        +
REGISTRA SU SALUD DIARIAMENTE
        +
PRESERVA SU HISTORIA
        +
COMPARA SU EVOLUCIÓN
        +
VISUALIZA TENDENCIAS
        +
DETECTA CAMBIOS SEGÚN REGLAS AUTORIZADAS
        +
GENERA ALERTAS ÚTILES
        +
NOTIFICA A QUIEN CORRESPONDA
        +
PERMITE INTERVENIR
        +
REGISTRA EL RESULTADO
        +
ENTREGA CONTINUIDAD ENTRE TURNOS Y PROFESIONALES
```

El objetivo no es acumular información.

El objetivo es que la información registrada ayude a **cuidar mejor, detectar cambios oportunamente, coordinar al equipo y comprender la evolución real del residente**, manteniendo siempre la responsabilidad clínica en los profesionales competentes.
