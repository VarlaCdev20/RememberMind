# Correcciones por puntos — 18/10/2026

Solicitud: corregir los seis puntos y entregar un resumen al cerrar cada uno. Este registro distingue implementación, verificación y dependencias pendientes. No es una aprobación clínica ni un registro de residentes.

| Punto | Corrección de este incremento | Estado verificable |
| --- | --- | --- |
| 1. Captura instrumental | Fecha original obligatoriamente completa cuando se aporta; normalización del instante a la zona institucional; precisión SQL validada; tipos de pregunta y pertenencia; autor derivado de sesión; NULL distinto de cero; transacción y rollback; lectura autorizada con originales y perfiles exactos | Implementado. Primera verificación conjunta con punto 2: 26 pruebas / 187 aserciones en SQLite y PostgreSQL desechable. Sin cambios de BDD ni carga clínica |
| 2. Cinco dominios / 19 componentes | Proyección de la fuente documental O.R.I.O.N., 24 nodos y 19 pertenencias primarias; perfil institucional sin generadores sintéticos; mediciones HVLT-R por aplicación y método; desconocidos explícitos | Implementada la red y consulta instrumental. Pendiente interpretación clínica completa de los 19 componentes. No se ha implementado ni declarado un ejecutor clínico completo |
| 3. Conservación integral | Auditoría independiente del contrato físico, de las fuentes y de las limitaciones de la RFC previa | Abierto: las instantáneas observacionales se conservan íntegramente; el nuevo perfil instrumental se consulta sin guardado. No se ha presentado un archivo técnico como persistencia institucional |
| 4. Petersen institucional | Cotejo de requisitos y soportes independientes; las cuatro salidas siguen probadas en el circuito experimental | Abierto: no se derivan C02, C03 o C05 de observaciones, z, Barthel, ausencia de diagnóstico ni atestaciones de laboratorio |
| 5. Revisión final separada | Contrato común de revisión; integración de OBS-SNAP-1/2 con la tabla de revisiones existente; lectura íntegra antes de firma; confirmación/rechazo y rectificación MED/PSI independientes | Implementado. Regresión SQLite 139 pruebas / 829 aserciones; PostgreSQL 89 pruebas / 326 aserciones, incluida carrera de firmas. El alcance cerrado es la revisión de resultados representables, no del perfil nuevo sin conservar |
| 6. Consolidación y QA | 43 clases Domain al motor puro; 11 Application y 19 Infrastructure a ApoyoDecision; codec puro con identidad histórica | Código trasladado y huellas de referencia verificadas. Regresión del traslado 745 pruebas / 5535 aserciones; posterior al traslado del codec 83 pruebas / 6186 aserciones SQLite y 85 / 492 PostgreSQL. QA visual parcial y bloqueado de nuevo por configuración de Work |

## Lo que calcula y lo que falta en los datos

`AplicarInstrumento` registra mediciones originales; no identifica el protocolo por el nombre del instrumento. `CapturasInstrumentalesResidente` exige permisos de aplicaciones, respuestas, instrumentos, preguntas y opciones antes de consultar. Un perfil configura PK de instrumento, versión exacta y PK/campo de cada respuesta. No se crearon catálogos ni se atribuyó HVLT-R a aplicaciones nominales existentes.

La edad se obtiene del nacimiento original a la fecha de aplicación. Los otros datos de aplicabilidad deben constar en respuestas de protocolo identificadas: escolaridad, español nativo, nacimiento y residencia en Bolivia, lectoescritura, administración estandarizada y demora. El motor conserva las respuestas y su procedencia, incluso cuando no puede normalizarlas.

`PerfilConceptualCognitivo` usa la fuente documental existente y conserva definiciones, límites y procedencia. EJE-S03 tiene propietario primario ejecutivo. La consulta presenta todas las mediciones compatibles; no elige la última o la más favorable ni cuenta una aplicación como dos soportes independientes. En el primer corte se normalizaban dos mediciones HVLT-R. La ampliación V2 descrita al final añade seis componentes con cálculo. Los ocho componentes con normalización disponible no equivalen a cinco dominios clínicamente interpretados ni acreditan Petersen.

## Brecha concreta de conservación — punto 3

Inventario aprobado: 73 tablas operativas y 33 expertas, total 106. Este incremento no modifica tablas, columnas, FK, checks, catálogos ni permisos.

La cabecera experta actual tiene una sola versión real. El laboratorio compone Memoria/G09/DCL/puente de versiones distintas. No se puede suprimir la comprobación de pertenencia a versión ni fabricar una versión común. Una futura revisión institucional coherente puede compartir una versión, pero debe contener verdaderamente sus nodos, mapeos y reglas.

Las copias escalares existentes conservan hechos interpretados, no las colecciones originales completas de instrumento, aplicación, pregunta, opción y respuesta, ni todos los contextos y datos rechazados. Las FK a fuentes mutables y una huella no recuperan el contenido original. El nuevo perfil declara `persistido=false` y no se permite firmarlo como una evaluación guardada.

La [RFC existente](RFC_CONSERVACION_PERFIL_EXPERIMENTAL_2026_10_18.md) propone un manifiesto, pero reconoce que no resuelve las capturas. **No es un diccionario final aprobado ni una migración lista para aplicar.** Antes de pedir aprobación estructural hace falta cerrar la entrada institucional, con conjuntos y columnas fijos por fuente, y demostrar sus dependencias funcionales/multivaluadas. No se proponen tablas genéricas campo/valor, JSON ni una tabla por examen. No se aplica un cambio parcial presentándolo como conservación completa.

## Brecha concreta de Petersen — punto 4

La clasificación experimental requiere C01–C05 y estados sustentados de los cinco dominios. El puente comprueba caso, versión, corte, soporte propio y episodios; el cálculo puede abstenerse. Esa corrección técnica no acredita que los requisitos provengan de fuentes institucionales.

Para cerrar el camino institucional faltan contratos de interpretación que vinculen cada requisito con el dato original y su estado: preocupación por cambio, alteración objetiva, independencia instrumental relativa, ausencia de demencia y evaluación diferencial. IADL existe como fuente descriptiva; no basta un booleano `iadl_observadas` del laboratorio. Una valoración de actividades básicas tampoco sustituye independencia instrumental.

No hace falta aprobación profesional previa para desarrollar estos contratos. Sí hacen falta sus datos y una definición de cálculo identificable. El [informe instrumental](INFORME_INSTRUMENTAL_Y_NORMALIZACION_2026_10_18.md) contiene la matriz y las referencias investigadas; la investigación no prueba cuál de esos instrumentos se administró a cada residente.

## Revisión posterior — punto 5

Una evaluación observacional FINALIZADA con OBS-SNAP-1/2 se puede revisar después de ejecutar y guardar. Antes de consultar decisiones o registrar una firma se comprueban hechos, huella, reglas originales, participaciones, soportes y resultado. No se requiere precargar una aprobación de regla o una firma profesional para ejecutar el contrato válido.

Cada disciplina mantiene su cadena: profesional y cuenta reales, fecha, decisión y justificación; reintento idéntico y rectificación con cabeza exacta. La revisión no cambia el resultado original ni acredita otro alcance. La pantalla distingue la confirmación de hallazgos descriptivos de la valoración cognitiva. Los perfiles instrumentales de consulta y las simulaciones no reciben firmas operativas.

## Verificaciones y límites

- Datos sintéticos solo en pruebas. No se consultaron ni modificaron historias reales para este incremento.
- SQLite y PostgreSQL desechable: verificación inicial instrumental/red 26 pruebas, 187 aserciones en cada motor.
- Regresión de dominio y repositorios tras el traslado: 745 pruebas / 5535 aserciones. Comprobación posterior del codec/conservación/arquitectura: SQLite 83 / 6186; PostgreSQL 85 / 492. Incremento de captura, normalización, perfil y revisión: 47 / 338; última ampliación de metadatos y observaciones de captura: 19 / 82. Son corridas con solapamiento; no se suman como pruebas diferentes.
- Sintaxis comprobada en los 170 archivos PHP del incremento; diff sin errores de espacios. Revisión independiente estática de consolidación: sin hallazgos P1/P2; 43 clases puras idénticas al corte anterior tras normalizar namespaces/imports. El AST anidado se añadió a las huellas de referencia tras la observación del revisor.
- Build del incremento: PASS. Suite general posterior al traslado del codec: 2705 pruebas, 49861 aserciones, 28 omitidas, sin fallos. Corresponde al corte anterior a la ampliación instrumental V2; no se atribuye a esa corrida la verificación de cambios posteriores.
- Hubo acceso temporal al navegador: se comprobó el error que ocultaba fuentes por una traza técnica antigua y su recuperación tras corregir la compatibilidad de `SIM-TRACE-1`. Los cinco campos de gobierno nuevos no cambian su significado; se admiten las dos formas físicas conocidas comprobando su hash. No se reescribió una traza ni se admitió una definición semántica alterada. No se guardaron capturas con datos reales.
- Navegador: el intento posterior volvió a ser rechazado por configuración de Work. No se afirma aceptación visual/responsive completa por un build o por Livewire.
- No hay push ni modificaciones de estructura o permisos de este incremento.

## Consolidación y compatibilidad — punto 6

Ubicaciones canónicas actuales:

- `motor-experto/src/motor_experto/dominio/orion`: 43 clases puras originales, sin cambios en su comportamiento.
- `motor-experto/src/motor_experto/soporte/conservacion/CodecInstantanea.php`: codec puro de escalares, fechas y huellas.
- `app/Modulos/ApoyoDecision/Aplicacion/CasosDeUso` y `DTOs`: seis casos de uso y cinco DTOs trasladados.
- `app/Modulos/ApoyoDecision/Infraestructura`: adaptadores, configuración, mapeadores, repositorios y conservación Laravel.
- `app/SistemaExperto`: antecedentes documentales, sin PHP ejecutable ni alias de clases.

El codec conserva el nombre contractual anterior al firmar objetos y enums del prefijo exacto trasladado. La cadena antigua es parte del formato histórico, no una clase cargable. Siete huellas previas permanecen idénticas: escalar/enum/fecha, memoria, perfil DCL, vector, clasificación, abstención y regla con AST anidado. Esta última se obtuvo en un proceso separado cargando las tres clases originales desde Git, sin Composer. Se conservan también los órdenes originales y la comprobación de corrupción. Pruebas aisladas: `php motor-experto/tests/independencia.php` y `php motor-experto/tests/orion_independencia.php`, ambas PASS sin Laravel ni SQL.

La prueba combinada PostgreSQL detectó dos funciones de triggers ya existentes después de `migrate:fresh`. Se corrigió la reinstalación técnica con `CREATE OR REPLACE FUNCTION`, sin alterar firma, cuerpo, permisos ni tablas. Se verificó nuevamente la carrera de firmas y las restricciones vigentes en una base desechable. No se ejecutó una migración en la base operativa.

## Fichas solicitadas posteriormente

La propietaria pidió crear los protocolos y mediciones faltantes. Se elaboró [el paquete de captura V1](PROTOCOLOS_CAPTURA_COGNITIVA_V1_2026_10_18.md): cabecera, ficha de entradas HVLT-R, matriz de diecinueve componentes, ficha C01–C05 y checklist. Son fichas vacías para aplicar o transcribir una evaluación documentada. No se generaron aciertos, tiempos ni firmas para residentes. No se crearon nuevos catálogos ni se presentaron candidatos bibliográficos como instrumentos administrados.

## Investigación y campos completados — ampliación V2

Solicitud posterior: investigar, documentar y completar los campos. Entregado [Métodos y campos investigados V2](METODOS_Y_CAMPOS_INVESTIGADOS_V2_2026_10_18.md), sin introducir valores de residentes.

- [x] Diccionario de adquisición para 19 componentes: datos, tipos/unidades, fuente exacta y límites por edición.
- [x] Seis perfiles nuevos en `NormalizadorBateriaBolivia2015`: BTA, Stroop, TMT-B, BNT, fluidez y ROCF copia; junto a HVLT-R, siete perfiles, once medidas en ocho componentes.
- [x] Cuatro condiciones de fluidez independientes en cálculo y compartidas en procedencia; no se crea un promedio, total normativo ni episodios ficticios.
- [x] Stroop A35: intervalos aproximados y extremos abiertos, sin z o interpolación. Discrepancia de denominador en la publicación localizada y contraste de Golden documentados.
- [x] Extractor común para todos los métodos, manteniendo PK/versión, respuesta, tipo, bruto y contexto original. Sin alias del extractor anterior ni detección de instrumento por nombre.
- [x] Diccionario visible por componente y cálculos instrumentales adaptados a z o intervalo percentilar, con dirección explícita del tiempo en TMT-B.
- [x] Fichas documentales C01–C05 especificadas; no son afirmaciones clínicas ni columnas nuevas.
- [ ] Mediciones originales e identidad de las aplicaciones: no se completan mediante investigación. La configuración de bindings reales permanece vacía.
- [ ] Normalizadores compatibles de los otros once componentes, interpretación conjunta de dominios y conservación integral institucional siguen abiertos.

Evidencia del incremento: SQLite 84 pruebas / 2615 aserciones, incluyendo los contratos de aplicación/presentación/frontend; PostgreSQL desechable 63 / 993 (corte previo a dos casos unitarios adicionales). Los últimos casos unitarios se verificaron en SQLite; no cambian consultas ni persistencia. Build PASS. Son corridas solapadas; no se suman. Revisión independiente de lectura del cálculo y sus publicaciones: PASS técnico sin hallazgos P1/P2; no acredita validez clínica ni disponibilidad de mediciones originales. La aceptación visual/responsive completa sigue pendiente; Livewire y build no la sustituyen.

Trazabilidad: definiciones → `CatalogoCapturaCognitiva`; ecuaciones → `NormalizadorBateriaBolivia2015`; identidad/procedencia → `ExtractorCapturaInstrumental` y `CapturasInstrumentalesResidente`; integración → `PerfilInstrumentalCognitivo`; pruebas → `NormalizacionBateriaBolivia2015Test` y `CapturaInstrumentalInstitucionalTest`. Sin cambios de tablas, columnas, catálogos BDD, permisos, datos clínicos o V1. Commit local del incremento, sin push.

## Continuación 19/10 — formulario de adquisición

Se reemplazaron los dos guardados manuales de total/clasificación por una captura compartida de respuestas originales, conectada con resultados y el historial. Se reutiliza `AplicarInstrumento`, con validación de catálogo confirmado dentro de la transacción y reenvío bajo la misma identidad de solicitud. Se corrigieron el estado sin residente, la actualización del historial, la etiqueta automática de normalidad y las revocaciones de acceso tras abrir. [Recorrido, checklist y evidencias](CAPTURA_INSTRUMENTAL_FORMULARIO_2026_10_19.md).

No se modificó el inventario de 106 tablas ni se importaron datos inventados. La conservación integral sigue separada y documentada en el [análisis del contrato instrumental](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md); no se aplicaron las ampliaciones candidatas. El desarrollo continúa sin exigir una firma profesional previa, preservando las mediciones ausentes como ausentes.
