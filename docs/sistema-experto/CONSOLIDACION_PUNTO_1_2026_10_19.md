# Punto 1 — Consolidación del sistema experto

Fecha: 19/10/2026. Estado: **CERRADO en su alcance de consolidación documental y revisión estática**. No significa cierre de los ocho entregables ni aceptación clínica. No se modifican motor, datos, estructura, permisos o pantallas en este punto.

Continuación posterior autorizada: la [integración del laboratorio](INTEGRACION_LABORATORIO_2026_10_19.md) incorpora y verifica el conjunto que aquí se encontró sin integrar. Este documento y su CSV conservan la fotografía del corte `f3d56ed2`; la continuación no cambia retrospectivamente sus hashes ni cierra E3/E4/E6 institucionales.

## Corte y contrato aplicable

- Código de referencia: **`f3d56ed2515a71b61a1e708a3dca97b0e8a84447`**. El último incremento funcional es `fb719a87`; `f3d56ed2` añade el plan, no cambia ese comportamiento.
- Este documento es la **matriz vigente de estado de los ocho entregables** al corte. El plan conserva el orden de las fases; los informes anteriores conservan la evidencia de sus respectivos incrementos.
- Los números **E1–E8** identifican entregables. La fase/punto **1** del plan es esta consolidación; no equivale a haber terminado E1, la red operativa.
- Motor independiente en `motor-experto`; integración Laravel en `ApoyoDecision`; captura en `Valoraciones`. `app/SistemaExperto` es antecedente documental, no ubicación ejecutable vigente.
- Medicina/Psicología gestionan conocimiento y registran sus revisiones separadas; Superadministración consulta. El desarrollo no exige una firma previa; no se precargan decisiones ni mediciones.
- Estructura aprobada de referencia: **73 operativas + 33 expertas = 106 tablas de negocio**. La cifra proviene de las decisiones vigentes; este punto no inspecciona ni modifica el esquema instalado.
- No existe todavía una revisión institucional integral que una interpretación de los 19 componentes, cinco dominios, C01–C05 y conservación instrumental completa. No se inventa un código de versión común para aparentar ese cierre.

El [manifiesto del corte](CORTE_PUNTO_1_2026_10_19.csv) registra archivos seleccionados del núcleo y los cambios locales preexistentes dentro del alcance auditado: commit, ruta, blob Git del corte, SHA-256 de los bytes de trabajo, estado y disposición. No contiene registros de residentes, secretos, respaldos ni resultados clínicos. Un hash de trabajo identifica lo observado; no convierte un archivo sin seguimiento en parte del commit.

Incluye 173 archivos: 56 de base existente seleccionada, 112 de trabajo local relacionado sin integrar en este punto y 5 de cambios independientes a preservar. Las familias de `work/` quedan fuera de sus contenidos y huellas.

## Fuentes y autoridad

| Fuente | Clasificación y alcance aplicado |
| --- | --- |
| Instrucción actual y [AGENTS](../../AGENTS.md) | VIGENTE: cerrar primero esta consolidación; autonomía técnica, seguridad e historia preservadas |
| [Baseline](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [índice BDD](../base-de-datos/README.md) y decisiones aprobadas enlazadas | VIGENTE para estructura en su cadena de extensiones; los conteos anteriores describen sus cortes históricos |
| [ADR híbrida](../arquitectura/ADR_ARQUITECTURA_HIBRIDA.md) | VIGENTE para ubicación/responsabilidades; su inventario de tablas del 10/10 no sustituye extensiones posteriores |
| [Gobierno de reglas](PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md) y [contrato observacional](CONTRATO_OBSERVACIONAL_VERSIONADO_2026_10_18.md) | VIGENTES en su alcance: sucesión/revisión de reglas y observación descriptiva, no interpretación integral cognitiva |
| [Métodos investigados V2](METODOS_Y_CAMPOS_INVESTIGADOS_V2_2026_10_18.md) y [captura conectada](CAPTURA_INSTRUMENTAL_FORMULARIO_2026_10_19.md) | VIGENTES para el incremento implementado y sus límites; no prueban qué instrumento recibió cada residente |
| [Revisión profesional aprobada](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md) | VIGENTE: dos cadenas posteriores e historia de decisiones |
| [Análisis de conservación instrumental](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md) | PROPUESTA: nueve relaciones candidatas y alternativas; no aprobadas ni aplicadas |
| RFC experimental y actas del laboratorio presentes sin seguimiento | TRABAJO LOCAL / PROPUESTA en su alcance; no se utilizan como prueba de release institucional integrado |
| Diseños originales `app/SistemaExperto`, estructura Hexagonal anterior y conteos 23/32 tablas expertas | HISTÓRICOS en esos aspectos; no gobiernan ubicaciones actuales ni el inventario vigente |

## Matriz única: requisito → componente → archivo → prueba → evidencia

Estados: **PARCIAL** significa que hay implementación reutilizable y falta el criterio completo. **BASE IMPLEMENTADA / EXTENSIÓN PENDIENTE** distingue un alcance operativo concreto del alcance integral solicitado. Una prueba localizada no se presenta como ejecutada en esta fase.

| ID y requisito | Fuente vigente | Componente y archivos principales | Pruebas localizadas en el corte | Evidencia y estado | Pendiente y aceptación restante |
| --- | --- | --- | --- | --- | --- |
| **E1. Red operativa:** cinco dominios, 19 componentes, fuentes y reglas conectados | Cierre 1–8; métodos V2; ADR | `PerfilConceptualCognitivo` — [archivo](../../motor-experto/src/motor_experto/dominio/conocimiento/PerfilConceptualCognitivo.php); [fuente conceptual](../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/datos/orion-v1-conceptos.json) | [PerfilInstrumentalInstitucionalTest](../../tests/Unit/PerfilInstrumentalInstitucionalTest.php): 24 nodos, 19 pertenencias y rechazo de segundo dominio primario | **PARCIAL.** EV1 incluye este test. La red declara `ejecutable_clinico=false`; pertenencia documental no equivale a inferencia | P03/P04: conectar variables, fuentes, condiciones y reglas institucionales. Cierre: recorrer conclusión → regla → hecho → fuente con versión exacta |
| **E2. Perfiles instrumentales:** identidad, captura y cálculo aplicables | Métodos V2; captura conectada | [RegistroMetodosInstrumentales](../../motor-experto/src/motor_experto/dominio/instrumentos/RegistroMetodosInstrumentales.php), [normalizador HVLT](../../motor-experto/src/motor_experto/dominio/instrumentos/NormalizadorHvltBolivia2015.php), [normalizador de batería](../../motor-experto/src/motor_experto/dominio/instrumentos/NormalizadorBateriaBolivia2015.php), [ExtractorCapturaInstrumental](../../motor-experto/src/motor_experto/dominio/instrumentos/ExtractorCapturaInstrumental.php) | [NormalizacionInstrumentalInvestigadaTest](../../tests/Unit/NormalizacionInstrumentalInvestigadaTest.php), [NormalizacionBateriaBolivia2015Test](../../tests/Unit/NormalizacionBateriaBolivia2015Test.php), [CapturaInstrumentalInstitucionalTest](../../tests/Feature/CapturaInstrumentalInstitucionalTest.php) | **PARCIAL.** EV1/EV2. Siete métodos y once medidas en ocho componentes; vínculos predeterminados vacíos | P01–P03: identificar aplicaciones originales, completar once componentes y definir interpretación aplicable. Cierre: métodos contrastados e identidad/procedencia verificable, sin confundir z con diagnóstico |
| **E3. Ejecutor de cinco dominios:** integración y resolución de conflictos | Cierre 1–8 y métodos V2 | [MotorInferenciaService](../../motor-experto/src/motor_experto/dominio/orion/Services/Inferencia/MotorInferenciaService.php), [PerfilInstrumentalCognitivo](../../motor-experto/src/motor_experto/dominio/instrumentos/PerfilInstrumentalCognitivo.php), [ConsultarPerfilInstrumental](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarPerfilInstrumental.php) | [ContratoServiciosTest](../../tests/Unit/SistemaExperto/Domain/Services/ContratoServiciosTest.php), PerfilInstrumentalInstitucionalTest | **PARCIAL.** Servicios puros existentes; el perfil institucional devuelve `NO_INTERPRETADO` y resultados clínicos nulos. G09 integral del laboratorio está fuera de HEAD | P03/P04: contratos de combinación y conocimiento institucional coherente. Cierre: cinco dominios derivados de datos originales, incluidos faltantes, conflictos y soportes compartidos |
| **E4. DCL/Petersen:** cuatro patrones y abstenciones sustentadas | Cierre 1–8; C01–C05 documentados en métodos V2 | [ClasificadorPetersenService](../../motor-experto/src/motor_experto/dominio/orion/Services/Dcl/ClasificadorPetersenService.php), [PerfilRequisitosDcl](../../motor-experto/src/motor_experto/dominio/orion/Entities/Dcl/PerfilRequisitosDcl.php), [AnalizarCasoExperimentalUseCase](../../app/Modulos/ApoyoDecision/Aplicacion/CasosDeUso/AnalizarCasoExperimentalUseCase.php) | [ClasificadorPetersenServiceTest](../../tests/Unit/SistemaExperto/Domain/Services/Dcl/ClasificadorPetersenServiceTest.php), [ContratoUseCasesTest](../../tests/Unit/SistemaExperto/Application/UseCases/ContratoUseCasesTest.php) | **PARCIAL / CONTRATO EXPERIMENTAL.** Pruebas localizadas, no reejecutadas aquí. El coordinador exige declaraciones DCL explícitas; no deriva todavía todo C01–C05 institucional | P05: soportes originales independientes y derivación institucional. Cierre: patrones y abstenciones desde evidencia, sin introducir respuestas esperadas ni usar ausencia de diagnóstico como diferencial |
| **E5. Adquisición:** registros tipados, fechas, autores, contexto y coherencia | Captura conectada; métodos V2 | [AplicarInstrumento](../../app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php), [PrepararCapturaInstrumental](../../app/Modulos/Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental.php), [CapturaInstrumentalModal](../../app/Modulos/Valoraciones/Presentacion/Livewire/Funcionalidades/CapturaInstrumentalModal.php), [CapturasInstrumentalesResidente](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/CapturasInstrumentalesResidente.php) | [CapturaInstrumentalFormularioTest](../../tests/Feature/CapturaInstrumentalFormularioTest.php), CapturaInstrumentalInstitucionalTest | **BASE IMPLEMENTADA / EXTENSIÓN PENDIENTE.** EV1/EV2: cero/No/NULL, referencias ajenas, autor propio, reenvío, catálogo modificado, rollback y revocaciones | P01/P02: importación conciliada y distinción autor histórico/transcriptor. Cierre: toda entrada institucional conserva procedencia original; no se sobrescribe longitudinalidad para deduplicar |
| **E6. Conservación:** guardar/recuperar todo e historia inmutable | Contrato observacional; análisis instrumental candidato | [ConservarObservaciones](../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/ConservarObservaciones.php), [ConsultarObservacionesConservadas](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarObservacionesConservadas.php), [AlmacenInstantaneas](../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/Conservacion/AlmacenInstantaneas.php), [CodecInstantanea](../../motor-experto/src/motor_experto/soporte/conservacion/CodecInstantanea.php) | [HallazgosObservacionalesTest](../../tests/Feature/HallazgosObservacionalesTest.php), [SucesionObservacionalTest](../../tests/Feature/SucesionObservacionalTest.php), [ConservacionExpertaTest](../../tests/Feature/SistemaExperto/Infrastructure/Repositories/ConservacionExpertaTest.php), [CodecIdentidadHistoricaTest](../../tests/Unit/CodecIdentidadHistoricaTest.php) | **BASE OBSERVACIONAL IMPLEMENTADA / PERFIL INTEGRAL PENDIENTE.** EV3 documental. `ORION-SNAP-2` conserva su contrato experimental; `PERFIL-INSTRUMENTAL-1` sigue `persistido=false` | P06/P07: DTO, representación mínima aprobada, artefactos recuperables y replay. Cierre: igualdad completa tras cambiar fuentes, reintentos, corrupción, rollback y concurrencia |
| **E7. Gobierno:** crear/probar/sustituir/archivar/revisar sin perder historia | Gobierno aprobado; contrato observacional | [GestionarReglasExpertas](../../app/Modulos/ApoyoDecision/Aplicacion/Acciones/GestionarReglasExpertas.php), [GobiernoReglasPanel](../../app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/GobiernoReglasPanel.php), [VersionModeloExpertoPolicy](../../app/Modulos/ApoyoDecision/Presentacion/Policies/VersionModeloExpertoPolicy.php), [CompiladorObservacional](../../motor-experto/src/motor_experto/dominio/conocimiento/CompiladorObservacional.php) | [GobiernoReglasExpertasTest](../../tests/Feature/GobiernoReglasExpertasTest.php), [GobiernoReglasConcurrenciaPostgresTest](../../tests/Feature/GobiernoReglasConcurrenciaPostgresTest.php), SucesionObservacionalTest | **BASE IMPLEMENTADA / EXTENSIÓN PENDIENTE.** EV3 documental. Gestión y selección causal observacional existentes; el alcance no equivale al gobierno de toda la interpretación de cinco dominios | P04/P08: extender solo con los contratos institucionales definidos y verificar UI. Cierre: archivo/sustitución afectan nuevas ejecuciones, nunca la historia; MED/PSI gestionan y Superadministración consulta |
| **E8. Resultados y revisión:** explicación, historial y dos cadenas posteriores | Captura conectada; decisión de revisiones | [ResultadosExpertoResidente](../../app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php), [RevisarValoracionExperta](../../app/Modulos/ApoyoDecision/Aplicacion/Acciones/RevisarValoracionExperta.php), [EvaluacionExpertaPolicy](../../app/Modulos/ApoyoDecision/Presentacion/Policies/EvaluacionExpertaPolicy.php) | [InformeNeurocognitivoTest](../../tests/Feature/InformeNeurocognitivoTest.php), [RevisionObservacionalExpertaTest](../../tests/Feature/RevisionObservacionalExpertaTest.php), [RevisionValoracionExpertaTest](../../tests/Feature/RevisionValoracionExpertaTest.php), [RevisionExpertaConcurrenciaPostgresTest](../../tests/Feature/RevisionExpertaConcurrenciaPostgresTest.php) | **BASE DE CONSULTA/REVISIÓN IMPLEMENTADA / PERFIL INTEGRAL PENDIENTE.** EV1 cubre apertura/captura/historial; revisiones localizadas. No hay firma operativa de un perfil aún sin conservación integral | P06–P09: informe desde instantánea integral, revisiones compatibles, QA y aceptación. Cierre: recorrido autorizado completo y explicación reconstruible |

## Registro de identidades y versiones

El commit fija la revisión de código donde no hay identificador propio. Las identidades siguientes tienen funciones distintas; no son versiones intercambiables de una sola evaluación.

| Elemento | Identidad al corte | Límite |
| --- | --- | --- |
| Motor COG-MEM original | `MotorExperto::evaluarCOGMEM`, revisión Git del corte; sin constante de versión de productor | `VER_TEST` es fixture de conocimiento; exige prueba técnica. No nombrarlo versión clínica instalada |
| Motor puro trasladado | `ORION-DOMAIN-EXPERIMENTAL-1` | Contrato experimental de inferencia; código integrado |
| Red conceptual | `CONCEPTOS-2026-10-08` | 24 nodos, 19 pertenencias; nodos/aristas INACTIVOS, no ejecutable clínicamente |
| Diccionario | `CAPTURA-COGNITIVA-2-20261018` | Define entradas de 19 componentes; no contiene mediciones de residentes |
| Perfil institucional de consulta | `PERFIL-INSTRUMENTAL-1` | Agrupa mediciones; interpretación conjunta y conservación pendientes |
| Familia observacional | `OBS-20261017-v1` | La versión usada se resuelve por la cadena SQL; no se consultó la instalada en este punto |
| Conservación observacional | `OBS-SNAP-1`, `OBS-SNAP-2` | Lectura histórica y ejecución versionada respectivamente; tres indicadores descriptivos |
| Conservación experimental | `ORION-SNAP-2` | Memoria/vector/clasificación representables del contrato técnico; no instantánea instrumental completa |
| Requisitos DCL | `DCL-PET-20261017-v0.1` | Patrones y abstenciones experimentales, pendiente derivación institucional |
| Laboratorio local | `PERFIL-EXPERIMENTAL-1`, `INTEGRA5-EXP-01`, `G09-EXP-20261017`, `ARCHIVO-PERFIL-TEST-1` | Declarados en archivos sin seguimiento; fuera del corte integrado |
| Paquetes del stack | Laravel `13.33.0`, Livewire `4.4.6`, PHPUnit `12.5.36`, Vite `8.3.1` | Leídos de locks; no equivalen a comprobación de un servidor desplegado |

Métodos incorporados: `HVLT-R-BO-2015-F5` (MEM-S01/S02), `BTA-BO-2015-NL` (ATE-S03), `STROOP-BO-2015-GOLDEN` (EJE-S01), `TMT-B-BO-2015` (EJE-S02), `BNT-BO-2015-60` (LEN-S01), `FLUIDEZ-BO-2015-FAS-ANIMALES` (LEN-S02, cuatro medidas separadas) y `ROCF-BO-2015-COPIA-A` (VIS-S02). Total: siete métodos, once medidas, ocho componentes. Pendientes: MEM-S03/S04, ATE-S01/S02, EJE-S03/S04/S05, LEN-S03/S04/S05 y VIS-S01.

La fuente conceptual declara huellas de documento `70d1b0e968fd9e77b3e9d36fd30959f180fc7cbe593fb23949482b1d85f7d8c2` y matrices `5495b104a7c62311193d24e5f32cee96b2b8e450fcebd03f230c5e4f56a053c4`. Son referencias declaradas por el archivo, no un nuevo cotejo de documentos externos. El SHA-256 de sus bytes observados es `b60f5d043e0062a6aa75c9057785987417f41057b115cdbe0ffac446f0948e23`.

Árboles Git del corte: motor `0b8213bd42c33676c1e3023ee27fecc6ce380488`; ApoyoDecision `b0bcd979ee610bbf97a27683f08579ca1c69cbba`; Valoraciones `849482d70b7191c1ac663df1d6fb89abb4407784`; pruebas `56d56ccd48f81a4f7badc64581de3bfd59065821`. Identifican código versionado, no contenido de la BDD ni garantía de retención de un release clínico.

## Cambios preexistentes revisados y disposición

Se revisaron el diff de archivos modificados y las dependencias de las familias locales mediante dos revisiones independientes de lectura. La presencia o hash de un archivo en el manifiesto no acredita una auditoría línea a línea de todo el laboratorio.

| Grupo | Evidencia de dependencia | Disposición |
| --- | --- | --- |
| Extracción del core | [AdaptadorHechosCOGMEM](../../motor-experto/src/motor_experto/adaptadores/entrada/AdaptadorHechosCOGMEM.php) modificado usa `AdmisibilidadFuenteCOGMEM`, sin seguimiento | Preservar ambos. No integrar solo el wrapper; refactor separado con equivalencia y regresión antes de aceptarlo |
| Exportación/validación de paquetes | [PaqueteConocimiento](../../motor-experto/src/motor_experto/dominio/conocimiento/PaqueteConocimiento.php) y [ValidadorPaqueteConocimiento](../../motor-experto/src/motor_experto/dominio/conocimiento/ValidadorPaqueteConocimiento.php) modificados | Preservar. La nueva unicidad de códigos puede rechazar paquetes antes aceptados; verificar contratos y huellas antes de incorporar |
| Contenedor y consola | [Provider](../../app/Providers/ArquitecturaModularServiceProvider.php) modificado referencia almacén/archivo y comandos nuevos sin seguimiento | Fuera de este punto. Integrar el conjunto de dependencias como candidato completo, nunca solo imports/bindings |
| Laboratorio web | [ConocimientoPanel](../../app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ConocimientoPanel.php), vistas y [config](../../config/vistas_modulares.php) modificados dependen de servicios, catálogos y parciales sin seguimiento | Preservar como herramienta experimental local. No cuenta como ejecución institucional; requiere pruebas de entorno, permisos y sesión |
| CSS | [index.css](../../resources/frontend/styles/design-system/index.css) importa una hoja nueva del laboratorio sin seguimiento | No integrar el import aislado; comprobar build en candidato completo |
| CI | [ci.yml](../../.github/workflows/ci.yml) añade clases experimentales ausentes de HEAD; workflow experimental nuevo | No integrar el filtro aislado: puede ejecutar otras clases y quedar verde sin descubrir las nuevas. Exigir descubrimiento explícito y dependencias presentes |
| Documentos experimentales | README modificado y actas/checklists sin seguimiento describen pruebas del laboratorio | Mantener como evidencia local de su alcance, sin convertirlas en cierre institucional o archivos disponibles desde HEAD |
| Cambios independientes | Consultas de Clínica, prueba de enlaces de arquitectura, horarios de Institución y su prueba; `work/` | Fuera del commit de consolidación. Preservados; no se inspeccionaron contenidos privados de `work/` |

**Antes de integrar el laboratorio:** inventario completo de dependencias, candidato desde checkout limpio, pruebas explícitamente descubiertas, independencia del motor, SQLite/PostgreSQL desechable, límites local/testing, build y QA. Su aceptación técnica seguiría siendo de laboratorio. Este punto no borra, descarta ni incorpora esos cambios.

## Conflictos resueltos y decisiones aisladas

| ID | Diferencia detectada | Resolución o decisión pendiente |
| --- | --- | --- |
| C01 | Primeros diseños colocan todo en `app/SistemaExperto`; ADR/AGENTS vigentes separan motor e integración | Resuelto por arquitectura aprobada posterior: usar las ubicaciones canónicas, sin reintroducir clases antiguas |
| C02 | Documentos de distintos cortes mencionan 23/32/33 expertas y 71/73 operativas | Resuelto por cadena de decisiones: 33 + 73 = 106 aprobadas al corte; no extrapolar una cifra histórica |
| C03 | Actas locales describen cinco dominios completos, pero el perfil institucional sigue sin interpretar/conservar todo | Resuelto por alcance: laboratorio y perfil institucional son contratos distintos. No reutilizar la aceptación sintética como cierre de E3/E4/E6 |
| C04 | Pruebas locales verdes frente a archivos sin seguimiento y modificados | Resuelto: evidencias de árbol local, no certificación de checkout limpio de `f3d56ed2`; no atribuirles reproducibilidad de release |
| C05 | Investigación de métodos frente a identidad/mediciones realmente aplicadas | Resuelto: la bibliografía aporta cálculo y límites, no valores ni autoría de residentes. P01/P02 quedan abiertos |
| C06 | RFC de manifiesto experimental frente a conservación instrumental | Resuelto: tres FK del laboratorio no representan todo `PERFIL-INSTRUMENTAL-1`. P06/P07 quedan abiertos; ninguna nueva ampliación aprobada por esta consolidación |
| C07 | Revisión profesional previa frente a instrucción vigente de revisión posterior | Resuelto: desarrollar/probar sin firma previa; registrar después las decisiones reales. No sustituye datos, métodos ni decisiones institucionales todavía ausentes |

| Pendiente | Ubicación responsable | Dependencia | Aceptación para cerrarlo |
| --- | --- | --- | --- |
| **P01. Identidad y vínculos originales** | `config/sistema_experto.php`, captura/extractor | Protocolos y aplicaciones identificables; fase 2 | Vínculos exactos de instrumento/edición/pregunta/método con procedencia, sin adivinar por nombre |
| **P02. Autor histórico y transcripción/importación** | Valoraciones; fuentes operativas | Firmas/documentos originales y representación compatible; fase 2 | Conservar autor original y actor de carga sin suplantación, conciliar duplicados sin borrar historia; cualquier brecha estructural se presenta antes de DDL |
| **P03. Normalizadores de once componentes e interpretación** | `dominio/instrumentos` | Edición, manual legítimo y referencia aplicable; fase 3 | Cálculos y criterios documentados con casos independientes; una abstención no se cuenta como método completado |
| **P04. Integración de red/reglas/dominos** | Motor y gobierno de ApoyoDecision | P01/P03; fase 4 | Paquete institucional coherente, reglas elegibles, conflictos resueltos por contrato y trazas sin doble conteo |
| **P05. Soportes C01–C05/Petersen** | Motor DCL y adaptadores institucionales | P01–P04; fase 5 | Derivación desde evidencias originales independientes, IADL pertinente y diferencial documentado; cuatro patrones/abstenciones probados |
| **P06. Contrato de instantánea y release** | Conservación/codec | Contratos de entradas/salidas; diseño desde fase 1, cierre en 6 | DTO tipado completo, fuentes descartadas/selectores incluidos y versiones/artefactos recuperables |
| **P07. Representación estructural mínima** | Diccionario de conservación | P06 y decisión concreta de la propietaria; fase 6 | Reutilización justificada y 4FN, diccionario aprobado si hay extensión; guardar/recuperar exacto y concurrencia probados |
| **P08. QA visual** | Presentación de resultados, captura y gobierno | Navegador habilitado; fase 7 | Evidencia real de interacción, teclado, estados y cinco viewports; no deducida de Livewire o build |
| **P09. Aceptación integral y release** | Flujo completo/CI/documentación | P01–P08; fase 8 | Candidato limpio, pruebas por alcance, revisión independiente y trazabilidad completa; push/despliegue solo con instrucción |

No hay solicitud de permiso pendiente para terminar el punto 1. Las decisiones futuras P03/P04 sobre reglas institucionales nuevas y P07 sobre estructura se concretarán en su fase; se continúa el trabajo independiente autorizado.

## Evidencias verificadas y su alcance

Los dos XML EV1/EV2 se abrieron para comprobar sus contadores y huellas; **no se reejecutaron suites ni se accedió a BDD** durante esta consolidación. Se encuentran en `storage/app/private/gobierno-reglas-20261018/`, fuera de Git. Los grupos se solapan y no se suman.

| ID | Evidencia | Resultado registrado | Atribución válida |
| --- | --- | --- | --- |
| **EV1** | `captura-formulario-sqlite.xml` | 127 pruebas, 2849 aserciones, 0 errores/fallos/omitidas | Último incremento y regresión del árbol local; no checkout limpio del corte |
| **EV2** | `captura-formulario-postgres.xml` y log | 45 pruebas, 497 aserciones, 0 errores/fallos/omitidas | Integración de captura/preparación en clúster desechable del incremento; no suite clínica ni toda la concurrencia |
| **EV3** | Contrato observacional y documento de gobierno aprobados, enlazados arriba | Corridas históricas y límites por incremento documentados | Evidencia documental localizada, no nueva ejecución ni confirmación de instalación actual |
| **EV4** | Lectura estática de archivos/blobs, locks, fuente conceptual, pruebas y grafo de dependencias locales | Dos revisiones de lectura y manifiesto del corte | Cierre de este punto: clasificación, trazabilidad, versiones y brechas; no aprobación clínica |
| **EV5** | Documento de captura conectada | Build PASS del incremento `fb719a87` | Resultado previo del árbol local, no nuevo build de esta tarea documental |

SHA-256 EV1: `a494353f2100389db18fe048dcc1b9e7e0e63e20aecd9c460ad439cbd01f7104`. EV2: `18f6cac693d209e1294db95468a5906931f9c5c5e69e2b33ea64da0883bea296`.

## Cierre del punto 1

Verificación de esta entrega: ocho filas únicas E1–E8, 64 enlaces del acta, enlaces de los tres documentos modificados, 50 referencias a código/pruebas existentes en el commit del corte y 173 huellas/blobs del manifiesto comprobados. Las cuatro casillas de la fase 1 están cerradas. Revisión independiente documental: coherencia confirmada; se corrigió «once métodos» por «normalizadores de once componentes» para no fijar una cardinalidad de métodos que no se ha definido. `git diff --check` sin errores. Tests de aplicación y build no se repiten porque solo cambia documentación.

- [x] Matriz única de E1–E8 con fuente, componente/archivo, prueba, evidencia, pendiente y aceptación restante.
- [x] Cambios preexistentes revisados por familias/dependencias y clasificados para integración separada, sin sobrescribirlos.
- [x] Revisión de código, identidades de conocimiento/captura/conservación y métodos fijadas; ausencia de paquete institucional integral registrada.
- [x] Conflictos resueltos por autoridad y pendientes P01–P09, con sus decisiones necesarias, aislados por ubicación y dependencia.
- [x] Verificación de enlaces/archivos del corte, blobs/huellas y cobertura de las ocho filas; referencias locales del laboratorio diferenciadas.

**Siguiente fase:** fase 2, identidad y vinculación de fuentes originales. Este cierre no modifica la habilitación clínica ni declara completados los siete puntos restantes del plan.
