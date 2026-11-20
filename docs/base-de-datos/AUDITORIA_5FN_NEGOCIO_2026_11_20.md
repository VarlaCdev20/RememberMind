# Auditoría de quinta forma normal · tablas de negocio

**Corte:** 20/11/2026. **Resultado global: NO CERTIFICADO EN 5FN.**
Se revisaron las 84 tablas físicas de negocio instaladas: 64 operativas y
20 expertas. Las 16 técnicas se excluyen del análisis semántico. No se modificó
la BDD, sus datos, permisos ni reglas. Este informe no autoriza una migración.

## Fuentes y alcance real

Se aplican el [baseline](REMEMBERMIND_BDD_BASELINE_CONGELADO.md), el
[diccionario del núcleo](REMEMBERMIND_BDD_70_TABLAS.md), las consolidaciones
aprobadas y las dos decisiones posteriores del 18/11:
[reparto](DECISION_REPARTO_VALORACIONES_2026_11_18.md) y
[residente derivado](DECISION_RESIDENTE_VALORACIONES_PROFESIONALES_2026_11_18.md).
La [auditoría anterior](AUDITORIA_NORMALIZACION_30_ATRIBUTOS_2026_11_18.md)
estudió principalmente tablas anchas; no había certificado las 84 relaciones.

La comprobación actual combina esquema PostgreSQL, claves e índices completos,
FK, CHECK, disparadores de secuencia, modelos y recorridos de escritura
pertinentes. Se inspeccionaron las columnas y referencias de las 84 relaciones;
273 FK y 1.021 restricciones de catálogo. Ninguna FK del alcance está sin
validar. Hay 69 tablas pobladas y 15 vacías: que una tabla esté vacía no prueba
ninguna forma normal.

La extracción se ejecutó en una transacción PostgreSQL `REPEATABLE READ, READ
ONLY`, confirmada desde la conexión y revertida al terminar. El script
[auditar-normalizacion-negocio.php](../../scripts/auditar-normalizacion-negocio.php)
emite exclusivamente metadatos y agregados, sin nombres ni filas personales.
La evidencia detallada queda en almacenamiento privado:
`storage/app/private/auditoria-5fn-20261120.json`.

## Qué se puede afirmar

La 5FN exige que las dependencias de reunión estén implicadas por las claves
candidatas. Incluye los requisitos de las formas normales previas. Una PK
artificial, tener menos de 30 columnas o poder reconstruir los datos actuales
no la demuestra. Referencias primarias: [Fagin, PJ/NF, 1979](https://research.ibm.com/publications/normal-forms-and-relational-database-operators)
y [Fagin, 4FN, 1977](https://research.ibm.com/publications/multivalued-dependencies-and-a-new-normal-form-for-relational-databases).

La teoría clásica se aplica a relaciones de valores. El esquema SQL usa NULL y
uniones tipadas: una dependencia condicionada a un tipo o a una FK no nula no
debe anunciarse como una dependencia global de todas las filas. Por eso se
documenta el subconjunto exacto. `MATCH SIMPLE` deja de verificar una FK
compuesta cuando alguno de sus componentes es NULL.

El dictamen **no certificado** responde a dependencias comprobadas en segmentos
poblados y a contratos condicionales aún pendientes. No equivale a afirmar que
las 84 tablas estén mal ni que sus datos sean incoherentes. Tampoco se emite una
certificación formal de ausencia de toda dependencia semántica no documentada
para las tablas sin hallazgos.

## R1 · Dependencias comprobadas en subconjuntos poblados

En estos casos una FK compuesta incluye una clave única del padre y otro
atributo suyo. La clave del padre determina ese segundo atributo en todas las
referencias completas admisibles. El determinante no es superclave del
subconjunto hijo: existen varias filas para su mismo valor.

| Tabla y subconjunto | Dependencia | Evidencia actual |
| --- | --- | --- |
| `mediciones_clinicas`, atención no nula | `cod_atencion → cod_residente` | 270 filas; 135 atenciones repetidas, hasta 2 mediciones por atención. |
| `registros_observaciones_cuidado`, atención no nula | `cod_atencion → cod_residente` | 270 filas; 135 atenciones repetidas, hasta 2 observaciones por atención. |
| `nodos_semanticos`, dominio no nulo | `cod_dominio_valores → cod_version_modelo` | 87 filas; 6 dominios repetidos, hasta 48 nodos por dominio. |
| `nodos_semanticos`, propietario primario no nulo | `cod_nodo_propietario_primario → cod_version_modelo` | 87 filas; 20 propietarios repetidos, hasta 8 nodos por propietario. |

La FK clínica referencia `atenciones(cod_atencion,cod_residente)` y la PK de
atenciones es `cod_atencion`. Las dos FK semánticas referencian los pares
dominio/versión y nodo/versión, cuyos primeros componentes son únicos. Las
restricciones verificadas sustentan la dependencia; la muestra aporta además
contraejemplos a considerar el determinante una clave del hijo.

Estas dependencias violan BCNF en esas relaciones lógicas filtradas, por lo
que esas proyecciones no cumplen 4FN/5FN. La situación exige resolver el diseño
de los tipos y del contexto antes de certificar el conjunto físico. No se
propone borrar globalmente `cod_residente`: otros tipos y filas sin atención
lo necesitan. Tampoco se retira la versión de todos los nodos: existen nodos
sin dominio ni propietario de los que derivarla.

Se ensayó cada separación **sin ejecutar DDL**: proyección DISTINCT del
determinante/dependiente, proyección del resto y reunión por el determinante.
Los cuatro ensayos recuperaron respectivamente 270, 270, 87 y 87 filas sin
pérdidas ni adicionales, comprobando ambos sentidos con `EXCEPT ALL`. Los dos
ensayos de nodos pueden compartir filas; no se suman como entidades distintas.
Esto demuestra recuperación de la muestra, no equivalencia de todo el sistema
tras una futura migración.

## C1 · Contextos condicionales y otras redundancias del dominio

La misma dependencia atención/residente figura en aplicaciones instrumentales,
controles cognitivos, IADL, derivaciones, documentos, registros clínicos,
textos clínicos y valoraciones psicopedagógicas. No hay unicidad declarada por
atención en esas relaciones. En Psicología la atención es obligatoria por
CHECK; en Pedagogía no existe y el residente directo es necesario. En la
muestra hay 444 valoraciones psicológicas y ninguna pedagógica.

En los hechos expertos IADL, la funcionalidad identifica a su evaluación y
viceversa por las claves únicas de `evaluaciones_expertas`. La FK repite ambas
referencias en cada hecho. El subconjunto IADL conservado está vacío en esta
base; no se presenta un ensayo vacío como prueba de 5FN. El CHECK sí distingue
la caracterización IADL de los hechos sin ella.

En las valoraciones Petersen, la coincidencia atención/residente se exige en
`RegistrarValoracionPetersenOriginal` al adquirir la atención. En respuestas
instrumentales, `RespuestaInstrumento` exige que la opción pertenezca a la
pregunta: en las respuestas de opción, `cod_opcion → cod_pregunta`, mientras una
opción puede aparecer en distintas aplicaciones. En medicación, el horario
identifica su prescripción; el servicio lo verifica y puede administrarse en
varias fechas. Son contratos condicionales; no hay filas con opción ni
administraciones programadas en la muestra para contrastarlos. Requieren
preservar respuestas libres, dosis PRN y rectificaciones históricas.

Otros tres contextos redundantes del contrato institucional:

- `consentimientos`: la admisión identifica al residente, y admite diferentes
  documentos/tipos de consentimiento por admisión.
- `ocupaciones_cama`: la admisión identifica al residente; los cambios de cama
  permiten varias ocupaciones históricas de esa admisión.
- `ejecuciones_cuidado`: intervención → plan → residente; la intervención puede
  ejecutarse en distintas ocasiones.

Los tres últimos tienen 30 filas cada uno, sin desacuerdos con su referencia.
Esta coherencia actual no elimina la redundancia lógica. Su documentación y
los recorridos de formalización/cuidado justifican el vínculo; no se confunde
con una FK compuesta ya existente. Faltan garantías SQL equivalentes para
algunas escrituras directas. Las alternativas deben preservar contexto
histórico, autorización y la imposibilidad de trasladar el registro a otra
persona. Se requiere autorización estructural y pruebas de migración antes de
retirar campos.

## A1 · Días de semana y D1 · autorías históricas

`horarios_prescripcion.dias_semana` y `programaciones_cuidado.dias_semana`
almacenan una selección como texto. `MiTurnoService::correspondeDiaSemana`
divide ese contenido en tokens. Si los días se consultan como miembros
individuales, hay un conjunto codificado: pendiente de atomicidad/1FN antes
de hablar de 5FN. No se clasifica automáticamente cualquier texto como lista.
La muestra tiene 0 horarios y 30 programaciones, todas sin selección explícita.
Una transición debe distinguir todos los días, ninguna selección y valores
históricos desconocidos. Esta auditoría no añade una tabla ni sustituye el
campo por JSON.

En `valoraciones_enfermeria_preadmision` y `revisiones_evaluacion_experta`,
cuenta del acto y profesional deben analizarse temporalmente. Consultar la
cuenta actual del personal no prueba quién registró el acto histórico. Se
mantienen como asuntos semánticos pendientes; no se declaran duplicados
prescindibles a partir de su coincidencia actual. El mismo cuidado se aplica
a las autorías históricas de IADL y Petersen. El IMC almacenado también requiere
concretar medición original, cálculo, redondeo e importaciones antes de tratarlo
como una función universal de peso y talla.

## Dependencias de reunión y falsos positivos descartados

1. En las asignaciones de residentes se proyectó el triple
   `(jornada,personal,residente)` sobre sus tres pares y se los reunió.
   **1.150 triples originales producen 1.241: 91 combinaciones espurias.**
   Por tanto esa reunión no es una dependencia válida ni una separación segura
   de los datos actuales. La coincidencia de parejas no constituye asignación.
   Fecha, estado e identidad del acto también deben conservarse.
2. En `relaciones_semanticas`, los 130 triples origen/tipo/destino se recuperan
   actualmente sin adicionales por sus pares. Eso solo describe esta muestra:
   ningún contrato exige que toda combinación compatible sea una relación
   semántica verdadera. No se infiere una JD universal ni un PASS desde ese dato.
3. El detector encontró 19 dependencias candidatas por FK compuesta. En
   revisiones expertas y correcciones Petersen el antecesor es único, por lo
   que esa dependencia sí está respaldada por una clave en las sucesoras.
4. En historial laboral y versiones del vínculo, los triggers `hlp_secuencia`
   y `ger_secuencia_versiones_vinculo_contacto` exigen el anterior inmediato.
   Junto a la unicidad de `(propietario,número)` eso implica un único sucesor.
   No se denuncian como infracciones solo porque falte un índice único directo
   sobre el antecesor. Las versiones siguen siendo hechos históricos distintos.
5. Los índices parciales o con expresiones no se trataron como claves globales.
   Por ejemplo, la unicidad de una administración programada incluye la fecha
   y solo se aplica a filas programadas. No hace único al horario en toda la tabla.
6. Campos escalares como `pais_documento`, `tipo_documento` y `pais_telefono`
   en personal no constituyen, por sí solos, una dependencia multivaluada. Son
   hechos distintos de una fila, no colecciones cruzadas.

## Inventario revisado y dictamen por relación

La matriz siguiente cubre exactamente 84 nombres actuales. **S no significa
«5FN certificada»**: significa sin otra infracción demostrada en el esquema y
contrato revisados, pendiente de una especificación exhaustiva de FD/MVD/JD
del dominio. PK y claves alternativas no se asumen completas por el mero hecho
de estar declaradas. Las tablas anchas tampoco se rechazan por su anchura.

- **R:** dependencia no clave demostrada en subconjuntos poblados (3 tablas).
- **C:** dependencia condicional o subtipo que impide cerrar la certificación (12).
- **L:** contexto lógicamente derivable del contrato, por resolver integralmente (3).
- **A:** selección de días codificada; atomicidad pendiente (2).
- **D:** contrato histórico/autoría requiere una prueba semántica adicional (2).
- **S:** sin otra infracción demostrada; no certificación formal global (62).

| # | Tabla | Grupo | Grano observado | PK | Filas | Dictamen |
| ---: | --- | --- | --- | --- | ---: | --- |
| 1 | `actividades` | operativa | Actividad institucional con responsable y fecha | `cod_actividad` | 20 | S |
| 2 | `administraciones_medicacion` | operativa | Administración u omisión de una prescripción | `cod_administracion` | 0 | C |
| 3 | `admisiones` | operativa | Acto de ingreso formal de un residente | `cod_admision` | 30 | S |
| 4 | `alertas` | operativa | Alerta individual con ciclo de atención | `cod_alerta` | 1 | S |
| 5 | `aplicaciones_instrumento` | operativa | Aplicación original de un instrumento | `cod_aplicacion` | 52 | C |
| 6 | `areas` | operativa | Área institucional | `cod_area` | 18 | S |
| 7 | `asignaciones_jornada` | operativa | Asignación laboral o de residente por jornada | `cod_asignacion_jornada` | 2540 | S |
| 8 | `atenciones` | operativa | Episodio de atención con contexto original | `cod_atencion` | 659 | S |
| 9 | `camas` | operativa | Cama identificada dentro de una habitación | `cod_cama` | 30 | S |
| 10 | `componentes_estudio` | operativa | Componente definido para un tipo de estudio | `cod_componente` | 3 | S |
| 11 | `componentes_regla_experta` | experta | Condición o consecuencia de una regla | `cod_componente_regla` | 122 | S |
| 12 | `consentimientos` | operativa | Consentimiento asociado a la admisión | `cod_consentimiento` | 30 | L |
| 13 | `contactos` | operativa | Persona de contacto; cuenta opcional | `cod_contacto` | 62 | S |
| 14 | `controles_cognitivos` | operativa | Observación cognitiva fechada | `cod_control_cognitivo` | 40 | C |
| 15 | `controles_funcionales_iadl` | operativa | Registro original de una actividad IADL | `cod_control_iadl` | 12 | C |
| 16 | `curaciones_herida` | operativa | Curación o evolución de una herida | `cod_curacion` | 1 | S |
| 17 | `derivaciones` | operativa | Derivación entre áreas en un contexto asistencial | `cod_derivacion` | 0 | C |
| 18 | `destinatarios_solicitud_documental` | operativa | Plazo documental de un trabajador en una solicitud | `cod_destinatario_documental` | 0 | S |
| 19 | `documentos` | operativa | Documento institucional o clínico tipado | `cod_documento` | 30 | C |
| 20 | `dominios_valores_expertos` | experta | Dominio semántico de una versión de conocimiento | `cod_dominio_valores` | 22 | S |
| 21 | `ejecuciones_cuidado` | operativa | Ejecución programada o realizada de una intervención | `cod_ejecucion` | 30 | L |
| 22 | `entregas_solicitud_documental` | operativa | Documento entregado frente a una solicitud personal | `cod_entrega_documental` | 0 | S |
| 23 | `estudios_clinicos` | operativa | Estudio solicitado desde una atención | `cod_estudio` | 0 | S |
| 24 | `evaluacion_criterios` | experta | Criterio evaluado con resultado/traza singular | `cod_evaluacion_criterio` | 75 | S |
| 25 | `evaluaciones_expertas` | experta | Ejecución y resultado institucional conservado | `cod_evaluacion_experta` | 13 | S |
| 26 | `evaluaciones_reglas` | experta | Evaluación de regla o de condición, por tipo | `cod_elemento_evaluacion` | 1206 | S |
| 27 | `eventos_alerta` | operativa | Evento del ciclo de una alerta | `cod_evento_alerta` | 3 | S |
| 28 | `evidencias_evaluacion` | experta | Evidencia admitida en una ejecución | `cod_evidencia_evaluacion` | 12 | S |
| 29 | `fotografias_institucionales` | operativa | Archivo fotográfico con identidad única | `cod_fotografia` | 32 | S |
| 30 | `fuentes_datos_expertas` | experta | Definición versionada de una fuente | `cod_fuente_dato_experta` | 4 | S |
| 31 | `habitaciones` | operativa | Habitación y capacidad planificada | `cod_habitacion` | 16 | S |
| 32 | `hechos_instantanea_experta` | experta | Hecho escalar y caracterización conservada | `cod_hecho_instantanea` | 4049 | C |
| 33 | `heridas` | operativa | Herida individual y su identificación | `cod_herida` | 1 | S |
| 34 | `historial_estados_residente` | operativa | Acto histórico de cambio de estado | `cod_historial_estado` | 30 | S |
| 35 | `historial_laboral_personal` | operativa | Acto laboral o evento de una asignación | `cod_historial_laboral` | 211 | S |
| 36 | `horarios_prescripcion` | operativa | Horario de una orden médica | `cod_horario_prescripcion` | 0 | A |
| 37 | `incidentes` | operativa | Incidente individual con fecha y autor | `cod_incidente` | 1 | S |
| 38 | `instrumentos` | operativa | Instrumento identificado por código | `cod_instrumento` | 35 | S |
| 39 | `intervenciones_cuidado` | operativa | Intervención perteneciente a un plan | `cod_intervencion` | 30 | S |
| 40 | `jornadas` | operativa | Fecha operativa de un turno | `cod_jornada` | 1278 | S |
| 41 | `mapeos_valores_fuente` | experta | Correspondencia de valor original a valor semántico | `cod_mapeo_valor_fuente` | 60 | S |
| 42 | `mapeos_variables_fuente` | experta | Extracción de una variable desde una fuente | `cod_mapeo_variable_fuente` | 30 | S |
| 43 | `medicamentos` | operativa | Presentación farmacológica del catálogo | `cod_medicamento` | 20 | S |
| 44 | `mediciones_clinicas` | operativa | Medición tipada: signos, antropometría o dolor | `cod_registro_medicion` | 270 | R |
| 45 | `motivos_dcl_instantanea` | experta | Motivo ordenado de una clasificación conservada | `cod_motivo_dcl_instantanea` | 0 | S |
| 46 | `nodos_semanticos` | experta | Nodo versionado con variable/criterio singular | `cod_nodo_semantico` | 205 | R |
| 47 | `objetivos_signos_vitales` | operativa | Versión temporal de objetivos por parámetro y residente | `cod_objetivo_signo` | 0 | S |
| 48 | `ocupaciones_cama` | operativa | Ocupación histórica de una cama en una admisión | `cod_ocupacion` | 30 | L |
| 49 | `opciones_pregunta` | operativa | Opción perteneciente a una pregunta | `cod_opcion` | 128 | S |
| 50 | `participantes_actividad` | operativa | Participación de un residente en una actividad | `cod_participante` | 88 | S |
| 51 | `personal` | operativa | Trabajador y vínculo único con su cuenta | `cod_personal` | 19 | S |
| 52 | `planes_cuidado` | operativa | Plan de cuidado de un residente | `cod_plan` | 30 | S |
| 53 | `preadmisiones` | operativa | Solicitud de ingreso con datos de su momento | `cod_preadmision` | 38 | S |
| 54 | `preguntas_instrumento` | operativa | Pregunta perteneciente a un instrumento | `cod_pregunta` | 180 | S |
| 55 | `prescripciones` | operativa | Orden médica emitida durante una atención | `cod_prescripcion` | 0 | S |
| 56 | `programaciones_cuidado` | operativa | Periodicidad de una intervención de cuidado | `cod_programacion` | 30 | A |
| 57 | `registros_clinicos` | operativa | Antecedente, diagnóstico, alergia o dispositivo tipado | `cod_registro_clinico` | 1 | C |
| 58 | `registros_consumo` | operativa | Registro individual de ingesta, hidratación o eliminación | `cod_registro_consumo` | 345 | S |
| 59 | `registros_estudio` | operativa | Resultado de componente o informe del estudio | `cod_registro_estudio` | 0 | S |
| 60 | `registros_observaciones_cuidado` | operativa | Observación de movilidad, conducta o sueño | `cod_observacion_cuidado` | 305 | R |
| 61 | `registros_textuales_clinicos` | operativa | Nota, indicación o pase de turno tipado | `cod_registro_textual` | 136 | C |
| 62 | `reglas_aplicadas_dcl_instantanea` | experta | Regla o condición copiada de una emisión histórica | `cod_elemento_regla_dcl` | 0 | S |
| 63 | `reglas_expertas` | experta | Regla identificada dentro de una versión | `cod_regla_experta` | 32 | S |
| 64 | `relaciones_evidencias_evaluacion` | experta | Relación explícita entre dos evidencias | `cod_relacion_evidencia` | 0 | S |
| 65 | `relaciones_semanticas` | experta | Afirmación origen/tipo/destino con significado propio | `cod_relacion_semantica` | 130 | S |
| 66 | `residentes` | operativa | Identidad residencial actual | `cod_residente` | 30 | S |
| 67 | `residentes_contactos` | operativa | Vínculo actual de un contacto con un residente | `cod_residente_contacto` | 30 | S |
| 68 | `respuestas_instrumento` | operativa | Respuesta original a una pregunta en una aplicación | `cod_respuesta` | 498 | C |
| 69 | `revisiones_evaluacion_experta` | experta | Dictamen histórico independiente por disciplina | `cod_revision_experta` | 0 | D |
| 70 | `seguros_residente` | operativa | Afiliación o cobertura de un residente | `cod_seguro` | 30 | S |
| 71 | `solicitudes_documentales_personal` | operativa | Solicitud documental institucional emitida | `cod_solicitud_documental` | 0 | S |
| 72 | `soportes_valoracion_petersen` | operativa | Fuente tipada que soporta una valoración original | `cod_soporte_petersen` | 152 | S |
| 73 | `tipos_estudio_clinico` | operativa | Tipo de estudio y requisitos propios | `cod_tipo_estudio` | 2 | S |
| 74 | `turnos` | operativa | Definición de un turno institucional | `cod_turno` | 22 | S |
| 75 | `usuarios` | operativa | Cuenta de acceso, sin identidad profesional duplicada | `cod_usuario` | 51 | S |
| 76 | `valoraciones_enfermeria_preadmision` | operativa | Valoración inicial de una solicitud de ingreso | `cod_valoracion_enfermeria` | 2 | D |
| 77 | `valoraciones_petersen_originales` | operativa | Juicio original o rectificación por elemento | `cod_valoracion_petersen` | 91 | C |
| 78 | `valoraciones_profesionales` | operativa | Valoración funcional o nutricional con atención obligatoria | `cod_valoracion_profesional` | 0 | S |
| 79 | `valoraciones_psicopedagogicas` | operativa | Valoración psicológica o seguimiento pedagógico | `cod_valoracion_profesional` | 444 | C |
| 80 | `valores_semanticos` | experta | Valor perteneciente a un dominio semántico | `cod_valor_semantico` | 76 | S |
| 81 | `versiones_modelo_experto` | experta | Versión de conocimiento identificada por código | `cod_version_modelo` | 6 | S |
| 82 | `versiones_vinculo_contacto` | operativa | Versión inmutable del vínculo y sus autorizaciones | `cod_version_vinculo` | 30 | S |
| 83 | `vinculos_evidencia_experta` | experta | Soporte de criterio o condición con evidencia concreta | `cod_vinculo_evidencia` | 24 | S |
| 84 | `visitas` | operativa | Visita individual de contacto a residente | `cod_visita` | 1034 | S |

## Prioridad de tratamiento posterior

1. Resolver el contexto atención/residente por tipo y el contexto versión de
   los nodos. Son los hallazgos con prueba SQL y contraejemplos de unicidad
   poblados. Conservar los vínculos históricos antes de derivar referencias.
2. Revisar los tres contextos de admisión/plan y los subtipos sin muestra.
   `ejecuciones_cuidado` sí tiene un trigger de pertenencia al plan; no se
   generaliza esa garantía a consentimientos, ocupaciones u opciones de
   respuesta, que no tienen ese disparador. Una forma normal y una garantía de
   integridad son asuntos relacionados, pero distintos.
3. Definir una representación relacional de la selección de días y resolver
   los contratos de importación, autoría y valores derivados antes de migrar.
4. Completar por contrato el conjunto de FD/MVD/JD admisibles y la cobertura
   sintética de los subtipos vacíos. Contrastar reconstrucción, autorización,
   historia, escrituras directas y reversión por cada cambio aprobado.

No hay una migración autorizada por este pedido de verificación. Sigue vigente
el máximo de 100 tablas físicas. No se propone otra unión artificial ni JSON/EAV
para encubrir las dependencias. Tampoco se retiran columnas solo para cumplir
un límite de anchura. El informe separa los hallazgos de cualquier propuesta
estructural futura.

## Evidencia de implementación consultada

- [FormalizarAdmision](../../app/Modulos/Admisiones/Aplicacion/Acciones/FormalizarAdmision.php): admisión, ocupación y consentimiento del mismo residente.
- [GestionarEjecucionesAgenda](../../app/Modulos/Cuidados/Aplicacion/Acciones/GestionarEjecucionesAgenda.php) y [RegistrarCuidadosDeTurno](../../app/Modulos/Cuidados/Aplicacion/Acciones/RegistrarCuidadosDeTurno.php): pertenencia de intervención al plan del residente.
- [RespuestaInstrumento](../../app/Models/RespuestaInstrumento.php): aplicación/pregunta y opción/pregunta.
- [RegistrarAdministracionMedicacionService](../../app/Modulos/Medicacion/Aplicacion/Acciones/RegistrarAdministracionMedicacionService.php): horario de la prescripción y ocurrencia temporal.
- [RegistrarValoracionPetersenOriginal](../../app/Modulos/ApoyoDecision/Aplicacion/Acciones/RegistrarValoracionPetersenOriginal.php): coincidencia de atención y residente al registrar.
- [MiTurnoService](../../app/Modulos/Cuidados/Aplicacion/Consultas/MiTurnoService.php): interpretación de los días de semana mediante tokens.
- [InventarioExperto](../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/InventarioExperto.php): clasificación de las 20 relaciones expertas actuales.

## Reproducción y límites

```powershell
php -l scripts/auditar-normalizacion-negocio.php
php scripts/auditar-normalizacion-negocio.php > storage/app/private/auditoria-5fn-20261120.json
```

Ambos comandos se ejecutaron correctamente. La salida contiene 100 tablas
físicas, 84 de negocio, las 19 candidatas por FK compuesta, siete comprobaciones
agregadas de contexto/selección, dos reuniones ternarias y cuatro ensayos de
reconstrucción. No contiene filas personales. Los resultados vacíos quedan
identificados, sin interpretarlos como garantías.

No se insertaron fixtures ni se ejecutaron migraciones, seeders, cambios de
índices, pruebas destructivas o modificaciones del motor experto. No se
ejecutaron las suites de aplicación ni un build frontend: no hay cambios de
producto en esta auditoría. La evidencia de solo lectura no sustituye pruebas
de migración, concurrencia, reversión ni validación clínica.
