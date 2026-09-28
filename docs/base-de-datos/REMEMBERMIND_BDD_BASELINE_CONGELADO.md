# RememberMind — Baseline Rector de la BDD Operativa

**Estado:** CONGELADO
**Versión:** BDD Operativa V2.1
**Ámbito:** RememberMind — sistema residencial/geriátrico con seguimiento clínico y cognitivo
**Tablas operativas:** 70
**Clasificación:** 11 maestras + 46 transaccionales + 7 intermedias + 6 auxiliares
**Entidad central:** `residentes`
**Fuente detallada del esquema:** [`REMEMBERMIND_BDD_70_TABLAS.md`](REMEMBERMIND_BDD_70_TABLAS.md)
**Decisión arquitectónica:** [`DECISION_ARQUITECTURA_BDD_V2_1.md`](DECISION_ARQUITECTURA_BDD_V2_1.md)
**Versión anterior:** BDD Operativa V2.0 — 69 tablas — HISTÓRICA
**Documento técnico complementario:** [`docs/arquitectura/REMEMBERMIND_BASELINE_TECNICO.md`](../arquitectura/REMEMBERMIND_BASELINE_TECNICO.md)

---

> [!IMPORTANT]
> **NATURALEZA DE ESTE DOCUMENTO:**
> Este baseline rector constituye el **contrato arquitectónico, funcional y de negocio** de la base de datos de RememberMind. Define los **invariantes, reglas estructurales, identidades, autorías, flujos institucionales y gobernanza**.
> El detalle pormenorizado de atributos, claves primarias, foráneas, tipos de datos físicos, nulabilidad, restricciones `CHECK` e índices reside en el **Diccionario Físico Canónico** ([`REMEMBERMIND_BDD_70_TABLAS.md`](REMEMBERMIND_BDD_70_TABLAS.md)). Ambos documentos son complementarios y no se duplican.

---

## 1. Propósito y alcance

El presente documento establece los fundamentos arquitectónicos obligatorios para el desarrollo, evolución y auditoría de RememberMind.

### Qué define este baseline:
1. Invariantes arquitectónicos de integridad y relaciones de datos.
2. Reglas de negocio estructurales que condicionan la persistencia y ciclo de vida de las entidades.
3. Conceptos y distinciones terminológicas canónicas del dominio residencial y clínico.
4. Modelo transversal de autoría clínica, actor técnico y trazabilidad.
5. Principio estricto de **Zero Fabrication** en la lógica del sistema.
6. Matriz conceptual de autorización y competencias profesionales por rol.
7. Gobernanza y control de cambios para garantizar la congelación del esquema.
8. Criterios de verificación frente a migraciones, modelos, políticas, servicios y pruebas automatizadas.

### Qué NO es este baseline:
- No es un manual de usuario final ni guía de procedimiento operativo.
- No es un diccionario físico duplicado de columnas y tipos (función asumida por `REMEMBERMIND_BDD_70_TABLAS.md`).
- No es una guía de instalación ni manual de despliegue de infraestructura.
- No es la documentación del motor de inferencia del sistema experto.
- No es una especificación visual ni de diseño de interfaz de usuario.

---

## 2. Jerarquía documental (Fuentes de verdad)

Ante cualquier duda de interpretación o discrepancia entre documentos, código y pruebas, rige la siguiente jerarquía de prelación normativa:

- **NIVEL 1 — DOCUMENTO RECTOR:**
  [`REMEMBERMIND_BDD_BASELINE_CONGELADO.md`](REMEMBERMIND_BDD_BASELINE_CONGELADO.md). Define invariantes, reglas de negocio, autoría, gobernanza y contratos del dominio.
- **NIVEL 2 — DICCIONARIO FÍSICO CANÓNICO:**
  [`REMEMBERMIND_BDD_70_TABLAS.md`](REMEMBERMIND_BDD_70_TABLAS.md). Especifica formalmente cada una de las 70 tablas operativas, atributos, PK, FK, tipos, nulabilidad, checks e índices.
- **NIVEL 3 — DECISIONES ARQUITECTÓNICAS (ADR):**
  [`DECISION_ARQUITECTURA_BDD_V2_1.md`](DECISION_ARQUITECTURA_BDD_V2_1.md) y futuros registros de decisiones arquitectónicas formalmente aprobados.
- **NIVEL 4 — IMPLEMENTACIÓN DE CÓDIGO:**
  Migraciones versionadas de Laravel, modelos Eloquent, Policies, Actions y Services modulares.
- **NIVEL 5 — VERIFICACIÓN AUTOMATIZADA:**
  Suite de tests automatizados (`tests/Feature/`, `tests/Unit/`).

> [!WARNING]
> **DOCUMENTACIÓN HISTÓRICA:**
> Los documentos correspondientes a la versión V2.0 (`REMEMBERMIND_BDD_69_TABLAS.md`, `RESULTADO_MIGRACION_APLICACION_V2.md`) y carpetas de auditorías pasadas se preservan únicamente como **evidencia de trazabilidad histórica** y **NO constituyen fuente de verdad vigente**.

---

## 3. Gobernanza y control de cambios

`RM-GOV-001` — **Congelamiento estricto del esquema de base de datos**
Queda terminantemente prohibido agregar, eliminar, fusionar, dividir, renombrar o modificar cualquier tabla, atributo, clave primaria, clave foránea, cardinalidad, nulabilidad estructural, restricción `CHECK` o introducir estructuras JSON/EAV como sustituto relacional sin cumplir estrictamente el procedimiento formal de 10 pasos:

1. **Detección y justificación:** Identificar formalmente la necesidad técnica o asistencial.
2. **Documentación del hallazgo:** Describir con exactitud la limitación encontrada.
3. **Evaluación de impacto:** Analizar efectos en modelos, consultas, vistas, políticas y pruebas.
4. **Propuesta de alternativas:** Plantear opciones respetando el modelo relacional existente.
5. **Evaluación de normalización:** Verificar que la propuesta cumpla tercera forma normal (3FN).
6. **Aprobación explícita:** Obtener la validación formal del responsable del proyecto.
7. **Registro de decisión:** Redactar y versionar un Architecture Decision Record (ADR).
8. **Migración incremental:** Crear una nueva migración versionada hacia adelante. **Prohibido editar migraciones históricas ya aplicadas.**
9. **Actualización de pruebas:** Incorporar las aserciones correspondientes en la suite de pruebas.
10. **Versionado documental:** Actualizar el baseline y el diccionario físico de tablas.

---

## 4. Terminología obligatoria

`RM-TERM-001` — **Distinción canónica de identidades del dominio**
El sistema reconoce y separa de manera taxativa cinco conceptos de identidad que bajo ninguna circunstancia deben utilizarse como sinónimos en la base de datos, código o documentación:

1. **POSTULANTE / ADULTO MAYOR EN PREADMISIÓN:** Persona mayor evaluada o solicitante de ingreso previa a la decisión de ingreso formal. Carece de condición de residente y de registro en la tabla `residentes`.
2. **RESIDENTE:** Persona mayor formalmente admitida mediante el acto transaccional de admisión institucional con asignación de cama. Representada exclusivamente en la tabla `residentes`. Adquiere la condición de negocio de **residente admitido**, reflejada en el estado operativo de la entidad.
3. **USUARIO:** Cuenta de autenticación, credenciales de acceso y autorización informática en el sistema (`usuarios`).
4. **PERSONAL:** Trabajador o profesional institucional (`personal`). Cuando posee acceso al sistema puede estar vinculado a una única cuenta `usuarios`, y una cuenta `usuarios` puede tener como máximo un registro asociado en `personal`, conforme a la restricción `UNIQUE` vigente en `personal.cod_usuario` (`usuarios 1 → 0..1 personal`).
5. **CONTACTO:** Familiar, tutor legal, apoderado o persona externa vinculada afectiva o legalmente a un residente (`contactos`).

> [!CAUTION]
> Queda expresamente prohibido que código nuevo o refactorizado utilice la denominación `AdultoMayor` como concepto equivalente a `Residente`, salvo la capa transitoria de adaptadores legacy debidamente aislada y documentada.

---

## 5. Entidad central del modelo

`RM-DATA-001` — **Centralidad de la entidad `residentes`**
La tabla `residentes` (`cod_residente string(20)`) constituye la entidad maestra central de RememberMind. Toda la información residencial, clínica, funcional, cognitiva, de cuidados, prescripción, administración farmacológica, instrumentos de valoración, actividades asistenciales, visitas y alertas debe vincularse directa o indirectamente con `cod_residente`.

**Excepción ontológica obligatoria:**
Los datos del proceso de preadmisión (`preadmisiones` y `valoraciones_enfermeria_preadmision`) ocurren en un momento cronológico anterior al nacimiento del residente. Por definición del dominio, **no debe forzarse `cod_residente` en las tablas de preadmisión**, ya que el postulante aún no ha sido admitido y su solicitud puede resultar rechazada.

---

## 6. Flujo de preadmisión y admisión institucional

`RM-ADM-001` — **Flujo canónico de ingreso**
El ciclo institucional de admisión opera bajo la siguiente secuencia de estados y validaciones:
`Preadmisión PENDIENTE` → `Revisión técnica/asistencial` → `Resolución: APROBADA o RECHAZADA` → `Si APROBADA: Admisión formal con asignación de cama` → `Residente ADMITIDO` (persistiéndose el valor de estado operativo correspondiente en `residentes.estado`, como `ACTIVO`).

`RM-ADM-002` — **Aprobación no crea residente**
La resolución de aprobación de una preadmisión (`estado = 'APROBADA'`) representa un dictamen técnico-administrativo favorable, pero **NO crea el registro del residente**.

`RM-ADM-003` — **Nacimiento del residente en la admisión formal**
El registro en `residentes` nace única y exclusivamente durante la ejecución de la acción de admisión formal (`FormalizarAdmision`).

`RM-ADM-004` — **Transaccionalidad atómica de la admisión**
El proceso de admisión formal se ejecuta bajo una transacción atómica de base de datos (`DB::transaction`) que coordina simultáneamente:
- Creación de la tupla en `residentes` (`cod_residente`, con estado persistido `ACTIVO`).
- Registro del evento en `admisiones` (`cod_admision`, vinculada a `cod_preadmision` y `cod_residente`).
- Vinculación del familiar responsable en `residentes_contactos`.
- Asignación obligatoria de cama mediante inserción en `ocupaciones_cama` (`estado = 'ACTIVA'`).
- Inicialización en `historial_estados_residente`.
- Registro de consentimientos institucionales iniciales y documentación de soporte aplicable.

`RM-ADM-005` — **Prohibición de atajos de creación**
Queda terminantemente prohibido implementar formularios, endpoints o métodos CRUD que permitan insertar registros en `residentes` omitiendo el flujo formal de admisión transaccional.

> [!NOTE]
> La preadmisión solo maneja los estados canónicos `PENDIENTE`, `APROBADA` y `RECHAZADA`. Quedó formalmente descartado el estado intermedio `PENDIENTE_VALORACION_MEDICA`.

---

## 7. Valoración de enfermería de preadmisión

`RM-ADM-006` — **Estructuración relacional de la valoración de preadmisión**
La tabla `valoraciones_enfermeria_preadmision` es una entidad transaccional clínica normalizada (tabla 70 de la BDD V2.1) que sustituye definitivamente la columna JSON de versiones preliminares.

- **Cardinalidad:** 1 a 0..1 estricta respecto a `preadmisiones`, asegurada por clave foránea única `cod_preadmision UNIQUE NOT NULL` con restricción `ON DELETE RESTRICT`.
- **Condición de registro:** Solo puede ser registrada o actualizada mientras la preadmisión permanezca en estado `PENDIENTE`.
- **Restricciones técnicas de rango e integridad clínica (CHECK en motor PostgreSQL):**
  * `ck_val_enf_dolor`: Escala 0 a 10; si `hay_dolor` es verdadero, la intensidad debe situarse entre 1 y 10.
  * `ck_val_enf_pa`: Presión arterial sistólica y diastólica entre 1 y 400 mmHg, exigiendo estrictamente `pa_sistolica > pa_diastolica`.
  * `ck_val_enf_fc`: Frecuencia cardíaca entre 1 y 300 lpm.
  * `ck_val_enf_fr`: Frecuencia respiratoria entre 1 y 100 rpm.
  * `ck_val_enf_temperatura`: Temperatura entre 25.00 y 45.00 °C.
  * `ck_val_enf_saturacion`: Saturación de oxígeno entre 0% y 100%.
  * `ck_val_enf_peso`: Peso entre 20.00 y 300.00 kg.
  * `ck_val_enf_talla`: Restricción de rango canónico en centímetros: `(talla IS NULL OR (talla >= 50.0 AND talla <= 240.0))`. *(Unidad canónica formalizada: CENTÍMETROS [cm], rango operativo canónico de adulto mayor: 50.0 a 240.0 cm; cálculo de IMC convierte internamente cm a metros estrictamente para aplicar la fórmula kg/m²; interfaces y validaciones unificadas en cm conforme a RM-GOV-001).*

---

## 8. Autor clínico y actor técnico

`RM-TRACE-001` — **Separación conceptual entre autor clínico y actor técnico**
El sistema reconoce la distinción transversal entre dos responsabilidades diferentes:
1. **AUTOR CLÍNICO (`cod_personal`, `cod_personal_valorador`):** Personal institucional autorizado para realizar el acto asistencial y responsable profesional del registro. Su clave foránea apunta a `personal.cod_personal`. En el caso específico de la tabla `valoraciones_enfermeria_preadmision`, representa al personal institucional autorizado para realizar la valoración de enfermería y responsable del acto asistencial (`cod_personal_valorador`).
2. **ACTOR TÉCNICO (`cod_usuario_registro`, `cod_usuario_revision`, `cod_usuario_validacion`):** Cuenta autenticada en el sistema informático que operó técnicamente la transacción en la interfaz o API. Su clave foránea apunta a `usuarios.cod_usuario`.

Cuando el esquema de una entidad distingue autor profesional y actor técnico, ambos deben conservarse como identidades diferentes.

`RM-TRACE-002` — **No sustitución de autoría por bitácoras técnicas**
La auditoría informática de accesos y eventos (implementada mediante `spatie/laravel-activitylog`) registra la actividad técnica del sistema, pero **bajo ningún concepto sustituye las claves foráneas de autoría clínica y responsabilidad profesional** requeridas en las tablas de negocio.

`RM-TRACE-003` — **Prohibición de atribución arbitraria de autoría**
Queda prohibido atribuir registros clínicos a usuarios comodín, administradores del sistema, cuentas genéricas o mediante consultas fallback (`Personal::first()`, `User::first()`). Si la transacción no puede acreditar fehacientemente al personal institucional autorizado responsable, **la operación debe ser rechazada inmediatamente mediante validación**.

---

## 9. Principio de Zero Fabrication

`RM-DATA-002` — **Prohibición absoluta de fabricación arbitraria de contexto**
Queda terminantemente prohibido en el código productivo crear o seleccionar contexto asistencial faltante mediante mecanismos arbitrarios para forzar la persistencia de operaciones inválidas o incompletas.

Patrones expresamente prohibidos con fines de fallback clínico:
- `Personal::first()`, `User::first()`, `Residente::first()`, `Jornada::first()`, `Prescripcion::first()`
- `firstOrCreate()`, `firstOrNew()`, `factory()` utilizados en flujos de producción para suplir datos no proporcionados por el operador.

> [!IMPORTANT]
> Esta regla no prohíbe el uso idiomático de métodos de consulta de Laravel cuando el contexto está debidamente determinado por filtros de negocio válidos (por ejemplo, buscar el turno correspondiente a una hora específica). Prohíbe **inventar o asumir identidades, residentes, jornadas, prescripciones, autores o camas** para evitar errores de validación.

---

## 10. Principios de autorización y seguridad

`RM-AUTH-001` — **Requisitos concurrentes de autorización**
Toda operación sensible sobre la base de datos exige la comprobación simultánea de:
`Sesión autenticada` + `Cuenta activa` + `Permiso Spatie explícito` + `Policy/Service contextual` + `Regla de negocio válida` + `Competencia profesional según rol`.

> [!WARNING]
> La ocultación visual de elementos en la interfaz (Blade/Livewire) no constituye mecanismo de seguridad. La autorización **debe ser validada de forma estricta e ineludible en el backend** mediante Policies y Services antes de alterar el estado de la base de datos.

`RM-AUTH-002` — **Restricción de escritura para SUPERADMINISTRADOR**
El rol `SUPERADMINISTRADOR` posee privilegios de lectura global sobre las 70 tablas operativas y la bitácora técnica con fines de auditoría y soporte. **No recibe privilegios automáticos de escritura clínica ni puede alterar actos asistenciales** por el mero hecho de su condición administrativa. Para intervenir clínicamente, requeriría contar con la condición de personal activo y los permisos profesionales específicos.

`RM-AUTH-003` — **Restricción de escritura clínica para ADMINISTRADOR**
El rol `ADMINISTRADOR` gestiona la operativa institucional, usuarios, personal, áreas, turnos, contratos y preadmisiones. **Carece de facultades para registrar, modificar o anular diagnósticos, prescripciones, valoraciones clínicas, notas asistenciales o administraciones farmacológicas**.

---

## 11. Matriz conceptual de profesiones y roles activos

Los roles institucionales formalmente activos son 9:

| Rol Canónico | Competencia Principal | Escritura Clínica Autorizada | Prescribe |
| :--- | :--- | :--- | :---: |
| `SUPERADMINISTRADOR` | Auditoría, supervisión técnica, soporte global | Ninguna por rol administrativo | NO |
| `ADMINISTRADOR` | Gestión institucional, RRHH, infraestructura, flujo admisiones | Administrativa exclusivamente | NO |
| `MEDICO GENERAL/GERIATRA` | Evaluación integral, diagnóstico, plan médico, prescripción | Integral médica, indicaciones, recetas, estudios | **SÍ** |
| `ENFERMEROS` | Cuidados continuos, signos vitales, administración de fármacos | Valoración preadmisión, signos, cuidados, administración, notas | NO |
| `PSICOLOGO/A` | Evaluación cognitiva, conductual y salud mental | Atenciones, valoraciones psicológicas, instrumentos cognitivos | NO |
| `NUTRICIONISTA` | Evaluación nutricional, antropometría y pautas dietéticas | Atenciones, valoraciones nutricionales, antropometría | NO |
| `FISIOTERAPEUTA` | Rehabilitación, movilidad y funcionalidad física | Atenciones, valoraciones funcionales, registros de movilidad | NO |
| `PEDAGOGO` | Intervención cognitiva, estimulación y actividades lúdicas | Atenciones, seguimiento pedagógico, actividades | NO |
| `FAMILIAR` | Acompañamiento e información del residente vinculado | Ninguna (consulta estrictamente acotada) | NO |

> [!NOTE]
> El rol `VOLUNTARIO` se encuentra formalmente **fuera del alcance** operativo actual.
> Los profesionales clínicos de cada área disponen de la capacidad conceptual y el permiso `atenciones.crear` para registrar sus correspondientes consultas y encuentros dentro del marco de su competencia específica.

---

## 12. Contexto asistencial requerido por operación

Las operaciones sobre el sistema exigen diferentes niveles de contexto según su naturaleza:

1. **OPERACIONES DE PREADMISIÓN:**
   - **Operación administrativa de preadmisión:** Puede ser realizada por los roles administrativos autorizados según permisos y Policies correspondientes (registro de solicitud, revisión documental, resolución institucional). No constituye por sí misma un acto clínico ni exige contexto de enfermería.
   - **Valoración de enfermería de preadmisión:** Constituye un acto asistencial y exige estrictamente preadmisión en estado `PENDIENTE`, usuario autenticado activo, rol `ENFERMEROS`, permiso correspondiente (`valoracion_enfermeria.registrar` o `editar`), registro de `personal` asociado activo y autoría clínica válida. No requiere cama asignada ni jornada residencial.
2. **OPERACIONES RESIDENCIALES CONTINUAS (Cuidados y Medicación):**
   Requieren residente formalmente admitido (`residentes.estado = 'ACTIVO'`), cama física asignada en `ocupaciones_cama`, personal asistencial en servicio y, según la política del módulo, jornada activa o asignación del residente.
3. **ACTOS CLÍNICOS PROGRAMADOS O INTERCONSULTAS:**
   Requieren residente admitido y personal autorizado, pudiendo operar fuera de la estructura de turnos de cuidados continuos según las reglas específicas de la Policy del servicio médico o especialista.

---

## 13. Habitaciones, camas y ocupaciones

`RM-BED-001` — **Exclusividad de la cama activa**
Una cama física no puede tener más de una ocupación activa simultáneamente. Implementado en base de datos mediante índice único parcial:
`CREATE UNIQUE INDEX uq_ocupacion_cama_activa ON ocupaciones_cama (cod_cama) WHERE estado IN ('ACTIVA', 'ACTIVO')`.

`RM-BED-002` — **Exclusividad de ocupación por residente**
Un residente no puede mantener más de una ocupación de cama activa simultáneamente. Implementado en base de datos mediante índice único parcial:
`CREATE UNIQUE INDEX uq_ocupacion_residente_activa ON ocupaciones_cama (cod_residente) WHERE estado IN ('ACTIVA', 'ACTIVO')`.

`RM-BED-003` — **Fuente de verdad de la cama asignada**
La cama actualmente ocupada por un residente se resuelve consultando la tabla intermedia `ocupaciones_cama` filtrando por ocupación activa. Queda prohibido duplicar o desnormalizar columnas `cod_cama` dentro de la tabla `residentes`.

`RM-BED-004` — **Protección de concurrencia en la asignación de cama**
La formalización de la admisión y las acciones de traslado deben verificar la disponibilidad de la cama bajo revalidación transaccional para prevenir condiciones de carrera. *(Estado: Implementado mediante índices únicos en base de datos y validación de servicio; recomendación pendiente de formalización de bloqueo pesimista `lockForUpdate` sistemático).*

---

## 14. Normalización relacional y modelado de datos

`RM-DATA-003` — **Adherencia a la normalización relacional (3FN)**
El proyecto RememberMind adopta el modelo relacional normalizado como estándar para la información estructurada:
- Queda prohibido el uso de estructuras JSON, esquemas EAV (Entity-Attribute-Value), campos de texto libre no estructurado o columnas booleanas acumulativas por patología como sustituto del diseño relacional normalizado para datos clínicos estructurados.
- Cada entidad transaccional responde a un grano de información bien definido con claves foráneas normalizadas y tipos estrictos.

---

## 15. Medicación y administración farmacológica

`RM-MED-001` — **Cadena canónica de medicación**
El circuito farmacológico opera mediante la secuencia estricta de 4 entidades:
`medicamentos` (catálogo farmacológico) → `prescripciones` (orden médica emitida) → `horarios_prescripcion` (pauta horaria programada) → `administraciones_medicacion` (evento real ejecutado u omitido).

`RM-MED-002` — **Privilegio exclusivo de prescripción**
El personal del rol `ENFERMEROS` no prescribe ni altera órdenes médicas; su ámbito funcional se limita a la preparación, administración y registro del fármaco o justificación de su omisión.

`RM-MED-003` — **Integridad de sujeto en la administración**
Toda administración registrada debe pertenecer de forma inequívoca al mismo residente titular de la prescripción. Restricción garantizada en motor mediante clave foránea compuesta e índice de grano:
`FOREIGN KEY (cod_prescripcion, cod_residente) REFERENCES prescripciones (cod_prescripcion, cod_residente)`.

`RM-MED-004` — **Integridad del horario programado**
Cuando se registra una administración vinculada a una pauta programada (`cod_horario_prescripcion IS NOT NULL`), dicho horario debe pertenecer estrictamente a la prescripción en curso.

`RM-MED-005` — **Medicaciones condicionales (PRN / Según necesidad)**
Las prescripciones indicadas bajo pauta condicional (`segun_necesidad = true`) pueden generar administraciones sin vinculación a un horario predeterminado fijo.

`RM-MED-006` — **Inmutabilidad del evento de omisión**
Cuando una dosis prescrita no se administra por negativa, ausencia o criterio clínico, el evento debe registrarse con su correspondiente estado y justificación técnica en `administraciones_medicacion`. Queda prohibido eliminar la pauta para ocultar la no administración.

`RM-MED-007` — **No sobreescritura de la orden médica**
La dosis efectivamente administrada (`dosis_administrada`) no sobrescribe la dosis pautada por el facultativo (`prescripciones.dosis`).

---

## 16. Cuidados y planes de atención

`RM-CARE-001` — **Estructura del proceso de cuidados**
El módulo de atención geriátrica estructura los cuidados en cuatro conceptos normalizados:
`planes_cuidado` (objetivos y valoración global) → `intervenciones_cuidado` (catálogo de acciones específicas) → `programaciones_cuidado` (frecuencia y horarios previstos) / `ejecuciones_cuidado` (registro fáctico del cuidado aplicado).

- No existe la tabla `tareas_cuidado`.
- Toda ejecución registrada debe pertenecer al residente titular del plan de cuidados correspondiente (garantizado mediante trigger de integridad en motor `validar_ejecucion_residente_plan`).

---

## 17. Seguimiento longitudinal e inmutabilidad histórica

`RM-CLIN-001` — **Carácter inmutable de los registros longitudinales**
Los registros de seguimiento clínico y funcional representan eventos sucedidos en un punto específico de la línea temporal y **no constituyen un estado actual sobrescribible**:
- `signos_vitales`, `valoraciones_dolor`, `controles_cognitivos`, `registros_conductuales`, `registros_sueno`, `registros_ingesta`, `registros_hidratacion`, `registros_eliminacion`, `registros_movilidad`, `mediciones_antropometricas`, `aplicaciones_instrumento`, `valoraciones_profesionales`.

Queda estrictamente prohibido actualizar los valores de un registro clínico pasado para reflejar la condición actual del paciente; cada medición genera una nueva tupla histórica.

---

## 18. Trazabilidad de corrección y no borrado físico

`RM-TRACE-004` — **Prohibición de borrado físico ordinario en datos clínicos**
Queda prohibido el borrado físico ordinario de datos clínicos. Cualquier rectificación debe gestionarse mediante los mecanismos de corrección formalmente soportados por la entidad en el dominio:
- Anulación lógica de registro con motivo documentado.
- Suspensión de la vigencia (aplicable a prescripciones).
- Cierre formal del plan (aplicable a planes de cuidado).
- Emisión de un nuevo registro compensatorio o adenda.

> [!NOTE]
> No todas las entidades admiten anulación directa si su máquina de estados no la contempla en la implementación vigente. Cuando una entidad clínica carezca de estado de anulación, las rectificaciones se registran mediante notas clínicas o valoraciones complementarias.

---

## 19. Gestión documental: Administrativa vs. Clínica

`RM-DOC-001` — **Separación de expedientes documentales**
El almacenamiento de archivos se distribuye en dos entidades según su naturaleza jurídica y confidencialidad:
1. `documentos`: Gestión documental administrativa institucional (cédulas de identidad, certificados de nacimiento, poderes legales, autorizaciones de ingreso, contratos).
2. `documentos_clinicos`: Expediente médico asistencial (informes de alta hospitalaria, imágenes diagnósticas, estudios externos, analíticas especializadas).

`RM-DOC-002` — **Metadatos en base de datos y almacenamiento de archivos**
Los archivos de gran tamaño no deben almacenarse como binarios BLOB en la base de datos por defecto. Las entidades documentales conservan la ruta o identificador seguro del archivo y los metadatos definidos por su esquema físico correspondiente. El detalle exacto de atributos de `documentos` y `documentos_clinicos` se mantiene en `REMEMBERMIND_BDD_70_TABLAS.md`.

---

## 20. Consentimientos informados

`RM-CONS-001` — **Validez de consentimientos y legitimación de contactos**
- La tabla `consentimientos` registra autorizaciones expresas para procedimientos, tratamientos o actividades institucionales.
- Cuando `firma_residente = true`, `cod_residente_contacto` debe ser `NULL`.
- Cuando `firma_residente = false`, debe existir un contacto válido vinculado al mismo residente mediante `cod_residente_contacto` (perteneciente a `residentes_contactos`).

---

## 21. Estudios clínicos estructurados

`RM-STUDY-001` — **Normalización de exámenes complementarios**
El modelo organiza los estudios paraclínicos mediante el esquema relacional:
`tipos_estudio_clinico` → `componentes_estudio` (parámetros analíticos esperados)
`residentes` → `estudios_clinicos` → `resultados_estudio` (valores cuantitativos/cualitativos) / `informes_estudio` (interpretación médica).

Queda prohibido crear tablas físicas especializadas por tipo de examen (como `hemogramas`, `tomografias`, `radiografias`).

---

## 22. Instrumentos psicométricos y de valoración geriátrica

`RM-INST-001` — **Estructura relacional de instrumentos estandarizados**
El módulo opera mediante:
`instrumentos` → `preguntas_instrumento` → `opciones_pregunta`
`aplicaciones_instrumento` (acto de evaluación) → `respuestas_instrumento` (selección realizada).

- El dominio puede soportar instrumentos psicométricos y escalas funcionales (tales como Barthel, Katz, MMSE, MoCA o Pfeiffer) cuando exista la correspondiente autorización legal y metodológica.
- Cada respuesta registrada debe corresponder rigurosamente a una pregunta perteneciente al instrumento aplicado.
- Toda incorporación de reactivos o escalas comerciales debe contar previamente con la verificación de licencias y derechos de propiedad intelectual.

---

## 23. Alertas institucionales y eventos

`RM-ALERT-001` — **Historial del ciclo de vida de alertas**
La gestión de eventos críticos opera mediante:
- `alertas`: Entidad principal que describe la condición o riesgo detectado.
- `eventos_alerta`: Conserva el historial de cambios y eventos de la alerta (`tipo_evento`, `estado_anterior`, `estado_nuevo`, `fecha_hora`, `descripcion`).
- El responsable profesional del seguimiento de la alerta debe consignarse en `cod_personal_responsable` (`personal.cod_personal`), no admitiéndose el uso de cuentas informáticas genéricas.

---

## 24. Auditoría técnica vs. Trazabilidad asistencial

`RM-TRACE-005` — **Coexistencia y límites de auditorías**
1. **AUDITORÍA TÉCNICA:** Gestionada centralizadamente mediante `spatie/laravel-activitylog` para registrar el actor autenticado, evento y propiedades configuradas. Queda prohibido duplicar bitácoras creando tablas ad-hoc como `auditoria_eventos`.
2. **TRAZABILIDAD ASISTENCIAL:** Reside de forma endógena en las columnas de negocio de las tablas clínicas (`cod_residente`, `cod_personal`, `fecha_hora`, `estado`). Queda prohibido registrar transcripciones completas de texto clínico en la bitácora técnica de Activitylog.

---

## 25. Integridad referencial y claves foráneas

`RM-DATA-004` — **Políticas de integridad en motor de base de datos**
- Compatibilidad absoluta de tipos entre claves primarias y foráneas (`string(20)` canónico).
- Todas las claves foráneas **deben disponer de índice adecuado** para optimización de uniones (`JOIN`).
- Cláusula de eliminación `ON DELETE RESTRICT` como política predeterminada en todas las tablas clínicas e históricas para impedir eliminaciones en cascada que destruyan la memoria del expediente médico.
- Restricciones de unicidad (`UNIQUE`) explícitas para impedir la duplicación de identidades activas.

---

## 26. Concurrencia e idempotencia

### A. Reglas implementadas formalmente:
1. **Unicidad de ocupación de cama:** Impedida a nivel de motor de base de datos mediante índice único parcial sobre camas y residentes en estado activo (`uq_ocupacion_cama_activa`, `uq_ocupacion_residente_activa`).
2. **Unicidad de asignación funcional:** Índices únicos parciales que impiden la duplicación activa de asignaciones de personal por jornada y área (`uq_asignacion_personal_activa`, `uq_asignacion_residente_activa`).
3. **Unicidad de administración programada por día:** Índice único condicional `uq_administracion_programada_dia` en `administraciones_medicacion` sobre `(cod_prescripcion, cod_horario_prescripcion, fecha_hora_programada::date)` cuando `cod_horario_prescripcion IS NOT NULL`.

### B. Recomendaciones y necesidades de arquitectura pendientes de formalización:
- **Pendiente de análisis de idempotencia general de administración de medicación:** Evaluación de escenarios de medicación condicional (PRN / según necesidad sin horario prefijado) y reintentos en red.
- Implementación de bloqueo pesimista (`select ... for update`) en la acción de formalización de admisión para evitar carreras concurrentes en la asignación del último cupo en cama.
- Idempotencia transaccional mediante tokens únicos en la recepción de solicitudes desde la interfaz web.

---

## 27. Principios de seguridad y privacidad clínica

`RM-SEC-001` — **Directrices de confidencialidad y mínimo privilegio**
- Principio de mínimo privilegio: los usuarios solo acceden a la información indispensable para su función asistencial o administrativa.
- Separación de expediente familiar: los usuarios con rol `FAMILIAR` solo acceden a datos expresamente autorizados del residente vinculado mediante `residentes_contactos`, quedando bloqueado el acceso a la historia clínica integral, notas profesionales y pases de turno.
- No divulgación de credenciales ni secretos criptográficos en registros de bitácora o código fuente.
- Autorización mandatoria en backend, sin delegar la seguridad a filtros de componentes visuales.

---

## 28. Límite arquitectónico con el sistema experto

`RM-EXPERT-001` — **Frontera operativa del sistema experto**
- Los módulos o tablas del sistema experto de inferencia y recomendación se encuentran **formalmente FUERA de las 70 tablas operativas** del baseline V2.1.
- El sistema experto se define arquitectónicamente para operar como consumidor de lectura sobre los datos autorizados del expediente.

> [!NOTE]
> **PRINCIPIO PROPUESTO PARA APROBACIÓN FORMAL:**
> Ningún componente automatizado del sistema experto podrá admitir residentes, emitir prescripciones médicas, alterar la historia clínica consolidada, borrar registros ni sustituir la decisión autónoma y soberana del profesional de la salud humano.

---

## 29. Estados y ciclos de vida

### A. Ciclos de vida y convenciones operativas:

#### 1. Preadmisiones (`preadmisiones.estado`)
- **Clasificación:** VERIFICADO EN CÓDIGO Y MIGRACIÓN.
- **Valores:** `PENDIENTE`, `APROBADA`, `RECHAZADA`.
- **Transiciones:**
  * Inserción inicial → `PENDIENTE` (actor: usuario institucional / registro).
  * `PENDIENTE` → `APROBADA` (actor: evaluador técnico con permiso; habilita admisión formal).
  * `PENDIENTE` → `RECHAZADA` (actor: evaluador técnico; exige `motivo_rechazo`).

#### 2. Residentes (`residentes.estado`)
- **Clasificación:** CONVENCIÓN OPERATIVA ACTUAL (columna `string(20)` abierta en motor sin restricción `CHECK` cerrada).
- **Valores observados en código y tests:** `ACTIVO`, `INACTIVO` (y estados de egreso documentados en `historial_estados_residente`).
- **Transiciones observadas:**
  * Creación en admisión formal → `ACTIVO`.
  * Egreso / Traslado / Fallecimiento → `INACTIVO` (registrado en historial con motivo y fecha).

#### 3. Ocupaciones de Cama (`ocupaciones_cama.estado`)
- **Clasificación:** VERIFICADO EN ÍNDICE DE MOTOR Y CÓDIGO.
- **Valores:** `ACTIVA`, `FINALIZADA` (histórica; los índices parciales de motor contemplan `ACTIVA` / `ACTIVO`).
- **Transición:** `ACTIVA` (con `fecha_hora_liberacion = NULL`) → `FINALIZADA` (con `fecha_hora_liberacion >= fecha_hora_asignacion`).

#### 4. Prescripciones Médicas (`prescripciones.estado`)
- **Clasificación:** CONVENCIÓN OPERATIVA ACTUAL (columna `string(20)` abierta en motor).
- **Valores observados en código y tests:** `ACTIVA`, `SUSPENDIDA`, `FINALIZADA`.
- **Restricción verificada en motor:** `ck_prescripcion_fechas` exige que si existe `fecha_hora_suspension`, esta debe ser `>= fecha_hora_prescripcion`.

#### 5. Administraciones de Medicación (`administraciones_medicacion.estado`)
- **Clasificación:** CONVENCIÓN OPERATIVA ACTUAL (columna `string(20)` abierta en motor).
- **Valores observados en código y tests:** `ADMINISTRADA`, `OMITIDA`, `PENDIENTE`.

### B. Ciclos de vida pendientes de formalización:
Los ciclos de vida específicos de `planes_cuidado`, `alertas`, `consentimientos` y `heridas` cuentan con campos de estado abiertos (`string(20)`) que operan bajo validación en capa de servicio y Policies, clasificados como **Pendientes de formalización de catálogo cerrado en base de datos**.

---

## 30. Matriz de trazabilidad: Regla → Implementación → Verificación

| ID Regla | Descripción Sintética | Implementación Canónica | Prueba Automatizada | Estado |
| :--- | :--- | :--- | :--- | :---: |
| `RM-GOV-001` | Congelamiento de 70 tablas operativas | Migraciones V2.1 y modelos | `BddOperativaV2Test::test_existen_exactamente_las_70_tablas_operativas` | **VERIFICADA** |
| `RM-ID-001` | Claves primarias `cod_* string(20)` | Migraciones core V2 | `BddOperativaV2Test::test_las_claves_y_columnas_de_identidad_respetan_el_baseline` | **VERIFICADA** |
| `RM-ADM-002` | Aprobar preadmisión no crea residente | `FormalizarAdmision` | `BddOperativaV2Test::test_residente_solo_se_crea_al_formalizar_admision_y_aprobar_no_lo_crea` | **VERIFICADA** |
| `RM-ADM-005` | Bloqueo de creación directa de residente | `ResidentePolicy` | `BddOperativaV2Test::test_creacion_directa_de_residente_esta_bloqueada` | **VERIFICADA** |
| `RM-BED-001` | No reutilización de cama con ocupación activa | `2026_09_24_000300_harden_v2_data_integrity` | `BddOperativaV2Test::test_no_se_reutiliza_cama_ni_residente_con_ocupacion_activa` | **VERIFICADA** |
| `RM-MED-003` | Administración ligada a prescripción del residente | FK compuesta en migración hardening | `BddOperativaV2Test::test_administracion_debe_corresponder_a_prescripcion_del_mismo_residente` | **VERIFICADA** |
| `RM-INST-001` | Respuesta corresponde a pregunta de instrumento | Migración hardening y servicio | `BddOperativaV2Test::test_respuestas_solo_aceptan_preguntas_del_instrumento_aplicado` | **VERIFICADA** |
| `RM-AUTH-002` | Superadministrador lectura global sin escritura clínica | `RolesAndPermissionsSeeder` | `BddOperativaV2Test::test_superadministrador_puede_ver_todas_las_tablas_operativas` | **VERIFICADA** |
| `RM-TRACE-001` | Separación conceptual autor clínico / actor técnico | `ValoracionEnfermeriaPreadmision` | `ValoracionEnfermeriaAutoriaTest::test_1_10_11_enfermero_con_rol_permiso_y_personal_activo_registra_con_autoria_separada` | **VERIFICADA** |
| `RM-TRACE-003` | Prohibición de fallback de autoría clínica | `ValoracionEnfermeriaPreadmisionPolicy` | `ValoracionEnfermeriaAutoriaTest::test_12_no_existe_fallback_automatico` | **VERIFICADA** |
| `RM-TRACE-004` | Prohibición de borrado físico ordinario en clínica | `ValoracionEnfermeriaPreadmisionPolicy` | `ValoracionEnfermeriaAutoriaTest::test_9_delete_siempre_rechazado_por_politica` | **VERIFICADA** |
| `RM-BED-004` | Protección de concurrencia en cama | Validación de servicio / Índices de motor | Servicios / Transacciones | **PARCIAL** |
| `RM-EXPERT-001` | Principio de límite con sistema experto | Principio propuesto de gobernanza | N/A (Norma de diseño) | **DOCUMENTAL** |

---

## 31. Historial del baseline

- **Versión 2.0 (Histórica):**
  69 tablas operativas. Línea base inicial de la arquitectura relacional normalizada. (Referencia: `REMEMBERMIND_BDD_69_TABLAS.md`).
- **Versión 2.1 (Vigente y Congelada):**
  70 tablas operativas (11 maestras, 46 transaccionales, 7 intermedias, 6 auxiliares).
  *Cambio principal:* Normalización e incorporación formal de `valoraciones_enfermeria_preadmision` (tabla 70) en reemplazo definitivo de la columna temporal JSON, implementación del modelo de autoría clínica (`cod_personal_valorador`) y actor técnico (`cod_usuario_registro`), y endurecimiento de restricciones `CHECK` de admisibilidad técnica en motor PostgreSQL. (Referencia: `DECISION_ARQUITECTURA_BDD_V2_1.md`).

---

# AVISO FINAL DE GOBERNANZA

**LA BASE DE DATOS OPERATIVA V2.1 SE ENCUENTRA FORMALMENTE CONGELADA.**

Cualquier cambio estructural en tablas, campos, tipos, nulabilidad o relaciones requiere la apertura formal de un proceso de control de cambios, evaluación de impacto, aprobación del propietario y registro documental correspondiente. Ningún agente automatizado o desarrollador está facultado para alterar el esquema por iniciativa propia.
