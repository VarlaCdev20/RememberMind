---
title: "Detección temprana de DCL: ficha y matriz de datos"
status: HISTORICAL
version: "0.7"
last_reviewed: 2026-10-31
owner: ApoyoDecision
source_of_truth: false
verification_scope: STATIC_SOURCE_AND_CODE_REVIEW
runtime_verified: false
---

# Detección temprana de DCL: ficha y matriz de datos

> Corte histórico del27–29/10/2026, conservado íntegramente como antecedente. Desde el31/10 la guía de trabajo es la [matriz única dato → instrumento → regla → resultado](MATRIZ_TRAZABILIDAD_DCL.md), con los mismos identificadores D01–D27, conexiones contrastadas y requisitos de conservación/4FN. No usar este corte como estado actual del experto.

## 1. Alcance de esta entrega

Primera entrega documental solicitada por la propietaria: localizar el dato necesario, su captura, conservación, consumidor y faltante antes de ampliar el experto. La población indicada en la conversación es **personas mayores de 60 años** residentes del centro. El objetivo es reunir evidencia para detección temprana y evaluación profesional; no emitir diagnósticos autónomos desde una prueba o desde observaciones.

La ficha v0.1 y la búsqueda ampliada de instrumentos son propuestas de trabajo revisables. Los contratos clínicos ya adoptados conservan su alcance. Esta matriz no selecciona una batería obligatoria, no incorpora reactivos, no activa reglas, no concede permisos y no autoriza modificaciones de catálogos o estructura. Los 45 candidatos enumerados en la conversación no son 45 instrumentos instalados ni 45 evaluaciones necesarias por residente.

Revisión estática del árbol local sobre `b17c04d12d2e48a23b3180009d3a6b9f96116b7c`, con cambios previos de otros trabajos presentes y conservados. No se consultaron historias de residentes, secretos ni la base de datos de ejecución. No se verificaron pantallas, configuración efectiva, instrumentos instalados, cantidad de aplicaciones ni cobertura real de pacientes.

## 2. Fuentes y resolución de autoridad

| Fuente | Estado y alcance aplicado |
|---|---|
| Instrucciones actuales de la propietaria y ficha de esta conversación | Población mayor de 60 años; catálogo ampliado de candidatos; autorización para levantar esta matriz. No aprobación implícita de reglas, puntos de corte o instrumentos concretos. |
| [Decisión de no crear tablas](../base-de-datos/DECISION_SIN_NUEVAS_TABLAS_2026_10_27.md) y [índice BDD](../base-de-datos/README.md) | Restricción vigente: ninguna tabla nueva; inventario documentado 106 de negocio +16 técnicas. Extensiones de campos deben respetar grano, integridad y contratos. No se ejecutó inventario físico en esta entrega. |
| [Baseline](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md) y decisiones específicas | Autoridad estructural; el nombre «70 tablas» describe el núcleo histórico. |
| [Contrato C01–C05](CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md), [captura original](PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md) y [procedencia instrumental](PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md) | Contratos adoptados en sus alcances: originales, soportes, comprobaciones, permisos y rectificaciones. No equivalen a una regla clínica universal. |
| [Índice experto](README.md) y [módulo ApoyoDecision](../../app/Modulos/ApoyoDecision/README.md) | Navegación y límites actuales; conservación observacional diferenciada de la evaluación conjunta transitoria. |
| [Diccionario de adquisición de 23 métodos](DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md) | Investigación y contratos de captura. La existencia documental de un método no acredita su administración, licencia ni adopción para un residente. |
| [Auditoría del 25/10](../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) | Evidencia de su corte. La brecha de conservación se volvió a contrastar en código; el resto no se declara reparado ni revalidado globalmente. |

Conflictos resueltos o delimitados:

- Los documentos anteriores «60+» no sustituyen silenciosamente la formulación actual «mayores de 60 años». La edad de una prueba se calcula en su fecha de aplicación. Esta entrega no implementa un filtro nuevo ni cambia rangos normativos; la especificación de elegibilidad debe expresar sin ambigüedad el límite de edad antes de programarlo.
- Los límites históricos de 104/110 tablas no habilitan tablas nuevas. Una prueba SQL experimental no es una instalación institucional.
- La petición de estudiar de nuevo el problema sin usar la denominación anterior no equivale a eliminar código o contratos ya adoptados. Se emplea «sistema experto» en este documento y se conservan las rutas existentes como evidencia.
- La descripción antigua de algunas skills como «motor futuro/propuesta» no revoca las adopciones específicas posteriores. Tampoco permite presentar como terminado el guardado conjunto.

## 3. Cómo leer los estados

- **C — Capacidad localizada:** fuente/código localizado; no certifica datos disponibles, funcionamiento extremo a extremo ni evaluación posterior en uso real.
- **P — Parcial:** hay soporte, pero falta precisión, captura, enlace o comprobación.
- **N — No localizado en el alcance revisado:** no afirmar ausencia en todo el producto; ampliar el inventario antes de proponer campos.
- **B — Brecha confirmada:** el recorrido o contrato declara explícitamente un pendiente comprobado.

Los estados pertenecen a esta matriz de auditoría; no son nuevos catálogos de la aplicación.

## 4. Matriz: contexto, cambio y fuentes personales

**Decisión inicial del 28/10/2026:** la propietaria eligió [mantener la BDD actual](../base-de-datos/PROPUESTA_CONTEXTO_ATENCION_COGNITIVA_2026_10_28.md) frente a seis campos de contexto en `atenciones`. La captura instrumental y la entrevista muestran la escolaridad general del expediente como referencia de solo lectura. La guía orienta las observaciones existentes, sin tratarlas como campos estructurados ni hechos extraídos. Esta mejora no cierra D02/D03/D06, no activa correcciones educativas ni elige un informante automáticamente.

**Instrucción siguiente del mismo día:** «amplia los campos de ser necesario pero no rompas 4fn». La [extensión acotada por atención](../base-de-datos/DECISION_CONTEXTO_ESCOLARIDAD_4FN_2026_10_28.md) incorpora años de escolaridad y capacidad referida de lectura/escritura, con desconocidos explícitos e historia conservada. No se transfieren automáticamente a una aplicación instrumental ni se reutilizan como si fueran de otra fecha. D02 continúa parcial hasta resolver las fuentes/perfiles de cada método; D03/D06 no se cierran.

**Continuidad implementada:** [selección explícita de la atención de la aplicación](ENLACE_ATENCION_APLICACION_2026_10_28.md), en ambas entradas. El contexto se consulta como referencia autorizada de su episodio mediante la FK ya existente; no se copia a respuestas ni cierra la aplicabilidad de D02.

**Revisión de D03/D06:** el [informe de idioma e informantes](CONTEXTO_IDIOMA_INFORMANTES_2026_10_28.md) distingue vínculo maestro, participación episódica e idioma de material. El inventario verifica la estructura sin consultar pacientes y conserva pendientes explícitos; las preguntas de cardinalidad no autorizan nuevas tablas.

**Cierre de captura principal del29/10:** [entrega de contexto y procedencia](ENTREGA_FASE_CONTEXTO_COGNITIVO_2026_10_29.md), con FK expresamente autorizada e instalada para el informante principal, idioma principal y conocimiento previo referido. Complementos narrativos no se extraen como hechos. Se resuelve la captura principal por episodio; aplicabilidad cultural/instrumental y calidad del relato siguen requiriendo contrato y valoración profesional.

| ID / dato necesario | Captura y fuente localizada | Conservación y consumidor | Estado | Trabajo siguiente |
|---|---|---|---|---|
| D01. Identidad y edad en evaluación | Admisión formal; `fecha_nacimiento` del residente. E01/E02. | `residentes`; E03 calcula años a la fecha de la aplicación y conserva la fuente en la proyección. | C | Verificar límite de población y casos de cumpleaños/fecha desconocida; no usar edad actual para interpretar pruebas antiguas. |
| D02. Escolaridad y alfabetización | Admisión recibe `nivel_educativo`; los métodos pueden exigir `escolaridad_anios` y `lectoescritura` en respuestas explícitas. E02/E04. | El texto educativo del residente no equivale a años de escolaridad de una aplicación; E03/E05 exigen perfiles y preguntas exactas. | P | Precisar dato y fuente para cada instrumento elegido. No convertir categorías educativas a años por suposición. |
| D03. Idioma y contexto cultural | Idioma principal por atención, opcional, con referencia autorizada desde la aplicación. Otros idiomas, intérprete y limitaciones en notas para lectura profesional. | `atenciones.idioma_principal_evaluacion`; no equivale al idioma del material ni acredita aplicabilidad normativa. | P | Captura principal implementada. Cerrar versión/perfil y aplicabilidad cultural de cada método sin deducir idioma desde país o nombre. |
| D04. Visión, audición y condiciones de aplicación | El catálogo de métodos incluye comprobaciones sensoriales según prueba; respuestas lógicas originales. E04/E05. | `respuestas_instrumento.valor_logico`, enlazado a su pregunta/aplicación. | P | Confirmar qué método pide cada condición y qué formulario la solicita. No marcar compatibilidad por defecto. |
| D05. Ocupación previa y funcionamiento habitual | Posible soporte documental/valoración psicológica; no se localizó una captura específica consolidada en las fuentes revisadas. E08/E14. | Narrativa y documento son candidatos, no un campo estructurado ni un hecho extraído automáticamente. | N | Revisar fuentes de Admisiones, Social y clínica antes de plantear captura adicional. |
| D06. Informante y calidad del relato | Informante principal explícito por atención mediante FK al vínculo existente y conocimiento previo referido nullable; otros relatos y discrepancias se documentan en notas. | Identifica participación principal conservada. El vínculo maestro aislado no prueba participación; la selección no certifica fiabilidad ni acredita C01 automáticamente. | P | Captura principal implementada. Corroboración y calidad siguen siendo valoración profesional con los soportes admitidos; no activar hechos desde narrativa. |
| D07. Cambio respecto al estado previo | Observación cognitiva y juicio original C01 con `cambio_respecto_previo`, justificación y soportes. E07/E08. | `controles_cognitivos`, `valoraciones_petersen_originales` y `soportes_valoracion_petersen`. | C | Verificar inicio, evolución y ejemplos en la captura; una señal aislada no sustituye historia previa. |
| D08. Corroboración e independencia de episodios | C01 exige `HISTORIA_PREVIA` y `CORROBORACION`; tipos de fuente cerrados. E07. | FK a control cognitivo, valoración psicológica o documento; comprobación profesional y contrato de episodios. | C | Probar que dos formularios del mismo episodio no se cuenten como corroboración independiente. Esta exigencia es institucional, no una duración universal del DCL. |

## 5. Matriz: instrumentos, cognición y autonomía

| ID / dato necesario | Captura y fuente localizada | Conservación y consumidor | Estado | Trabajo siguiente |
|---|---|---|---|---|
| D09. Instrumento, versión e identidad | `CapturaInstrumentalModal` y `PrepararCapturaInstrumental`; E09. | `instrumentos`, `preguntas_instrumento`, `opciones_pregunta`, `aplicaciones_instrumento`. E03 exige coincidencia de PK/versión y perfil explícito. | P | Inventariar catálogo realmente instalado y revisar licencia, formación, edad, idioma y normas de cada candidato. No identificarlo por parecido de nombre. |
| D10. Resultados originales | Action `AplicarInstrumento`; pregunta/opción perteneciente al instrumento; E09/E10. | Respuestas numéricas, texto, booleano y puntaje; total e interpretación en aplicación. | C | Para la batería seleccionada, verificar unidades, resultados por área, rangos y NULL; no sustituir resultados por un total global. |
| D11. Autor, fecha clínica y transcripción | E09 distingue aplicación y transcripción; requiere original, localizador y comprobación documental en esta última. | `cod_personal`, `fecha_hora`, `cod_usuario_registro`, `registrado_en`, documento y antecesora. E10. | C | Probar originales incompletos, autor ajeno, documento de otro residente y rectificación sin sobreescritura. |
| D12. Juicios de dominios cognitivos | `CapturaPetersenPanel` ofrece cinco dominios; cada juicio se apoya en mediciones originales. E07/E11. | `valoraciones_petersen_originales` con estado original, contexto y soportes. C02 se deriva; no se captura manualmente. | C | Revisar que el formulario ayude a seleccionar soportes válidos y no convierta observaciones en alteración objetiva. Los cinco dominios son parte del contrato vigente, no cinco déficits requeridos. |
| D13. Actividades, oportunidad y ayuda | Formulario propio de funcionamiento cotidiano, Action y Policy de Valoraciones; Medicina/Psicología aprobadas el 28/10. E12 y entrega posterior enlazada abajo. | `controles_funcionales_iadl`; registro inmutable, rectificaciones vinculadas, lectura descriptiva al corte y autoría. | C en captura descriptiva | [Captura y verificación del 28/10](FORMULARIO_FUNCIONAMIENTO_COTIDIANO_2026_10_28.md). No calcula FAQ ni acredita C03; las condiciones clínicas de interpretación siguen separadas. |
| D14. Independencia funcional relativa | Juicio original C03 con revisión integrada, oportunidad y atribución revisadas. E07/E11. | Soportes IADL originales, separados del juicio; el lector E12 declara que no acredita C03. | C | Comprobar capacidad frente a falta de oportunidad institucional. Barthel/Katz o una observación IADL no acreditan C03 automáticamente. |

## 6. Matriz: factores contribuyentes y evaluación clínica

| ID / dato necesario | Captura y fuente localizada | Conservación y consumidor | Estado | Trabajo siguiente |
|---|---|---|---|---|
| D15. Ánimo y conducta | `RegistrarValoracionProfesional` para Psicología: ánimo, afecto, ansiedad, apatía, conducta e impresión cognitiva. E13. | `valoraciones_psicologicas`; puede apoyar revisión diferencial autorizada. | P | No equiparar narrativa con GDS/PHQ-9/NPI-Q administrados. Revisar catálogo antes de adoptar una escala o su interpretación. |
| D16. Sueño y otros factores del episodio | Posibles antecedentes/documentos; no se verificó aquí una extracción específica de sueño para este circuito. E14/E07. | Fuente clínica posible, no factor automáticamente acreditado por el motor. | N | Localizar registro operativo y precisar fecha/contexto; conservar «desconocido» mientras no exista fuente admisible. |
| D17. Medicación y sustancias | El módulo Medicacion conserva prescripciones y administraciones; la revisión diferencial admite fuentes cerradas E07. | La prescripción no es soporte directo habilitado por `ContratoFuentesPetersen`; no crear una FK o lectura inferencial nueva por iniciativa técnica. | P | Definir cómo el profesional documenta la revisión pertinente usando fuentes actualmente admitidas. Revisar alcance antes de automatizar extracción farmacológica. |
| D18. Antecedentes, diagnósticos y exploración | Entidades `AntecedenteClinico`, `Diagnostico`, atención/nota médica y documentos. E14. | Soportes admitidos de C04: antecedente, diagnóstico, valoración psicológica o documento. La nota/atención no es un soporte directo adicional por estar disponible. | P | Identificar episodio y conclusión pertinente. No interpretar ausencia de diagnóstico como descarte de una causa. |
| D19. Laboratorio e imagen indicados | `DocumentoClinico` admite vínculo con estudio clínico y conserva archivo/huella. E14. | Documento original del residente; su mera presencia no acredita resultado normal, revisión ni indicación universal de estudios. | P | Comprobar resultados disponibles y lectura profesional según caso; no imponer una batería médica desde el software. |
| D20. Revisión diferencial | C04 contiene revisión explícita, factores revisados e impedimento no resuelto. E07/E11. | Juicio original, autor, fecha y soportes; distinción booleana de NULL. | C | Probar impedimento pendiente, revisión incompleta y contradicción; no sustituirlos por una clasificación deseada. |
| D21. Calidad e interpretabilidad conjunta | C05 identifica exactamente las cinco valoraciones originales revisadas y sus comprobaciones. E07/E11. | Soportes tipados y referencias a juicios originales; una revisión posterior no reescribe las fuentes. | C | Verificar que cada dominio y condición correspondan al conjunto efectivamente revisado, no al último dato disponible. |

## 7. Matriz: conocimiento, conservación y continuidad

| ID / dato necesario | Captura y fuente localizada | Conservación y consumidor | Estado | Trabajo siguiente |
|---|---|---|---|---|
| D22. Perfil de adquisición y conocimiento aplicable | Configuración E15 con listas por defecto vacías; resolución institucional por contrato/versiones en E16. | El árbol fuente no muestra la configuración efectiva ni demuestra ausencia de versiones en BDD. | P | Inventariar configuración efectiva y catálogo instalado mediante lectura autorizada, sin publicar datos de pacientes ni secretos. |
| D23. Reglas, condiciones y relaciones | Gestión/versionado y cargador de conocimiento; E16. | `reglas_expertas`, condiciones/consecuencias y relaciones semánticas, por versión. Variables se proyectan sobre nodos conforme consolidación aprobada. | C | Unificar consulta de conocimiento y distinguir institucional/laboratorio. Los 45 candidatos no generan reglas nuevas automáticamente. |
| D24. Evaluación conjunta conservada | Preparación conjunta y consulta instrumental E17. | `InstantaneaEvaluacionInstitucional` exige `persistido=false`, `release_retenido=false`; la consulta devuelve también `persistido=false`. | B | Diseñar writer/reader completo dentro del modelo permitido: hechos, versiones, resultado, explicación, idempotencia y reproducción histórica. No instalar el prototipo de 229 tablas. |
| D25. Revisión profesional del resultado | `RevisarValoracionExperta` y `RevisionEvaluacionExperta`; E18. | Revisiones vinculadas a una evaluación persistida; no equivalen a las valoraciones originales previas E11. | P | Usar solo en el alcance realmente conservado. No atribuir revisión a un DTO conjunto transitorio ni sobrescribir el resultado automático. |
| D26. Acceso y restricción de fuentes | Policies/contexto y permisos por tipo de fuente; E03/E07/E11. | Autorización backend; enfermería consulta dentro de su scope; escritura profesional exige competencia. | P | Verificar casos por rol, permiso revocado y fuente ajena. Separar acceso restringido, inexistencia, error y dato no interpretable. No cambiar permisos sensibles en esta entrega. |
| D27. Comparación longitudinal | Hay fechas, antecesoras y lectores; la consulta instrumental filtra estado actual. E03/E10/E17. | Un corte retrospectivo sobre fuentes mutables no es replay de la evaluación pasada; conservación conjunta pendiente. | B | Comparar instrumento/edición/método y condiciones compatibles; demostrar que rectificar hoy no modifica un resultado histórico conservado. |

## 8. Evidencias de código localizadas

| Clave | Archivo(s) y evidencia |
|---|---|
| E01 | [Residente](../../app/Models/Residente.php): fecha de nacimiento y acceso a edad; no tomar el accessor actual como edad de una prueba antigua. |
| E02 | [FormalizarAdmision](../../app/Modulos/Admisiones/Aplicacion/Acciones/FormalizarAdmision.php) y [PreadmisionesPanel](../../app/Modulos/Admisiones/Presentacion/Livewire/Paginas/PreadmisionesPanel.php): recepción/conservación de nivel educativo. |
| E03 | [CapturasInstrumentalesResidente](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/CapturasInstrumentalesResidente.php): autorización, identidad, respuestas, perfiles y edad en aplicación. |
| E04 | [Diccionario de métodos](DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md) y [CatalogoCapturaCognitiva](../../motor-experto/src/motor_experto/instrumentos/CatalogoCapturaCognitiva.php): entradas y contexto por método; el diccionario no equivale a una adopción de batería. |
| E05 | [ExtractorCapturaInstrumental](../../motor-experto/src/motor_experto/instrumentos/ExtractorCapturaInstrumental.php): selectores e identidad explícitos. |
| E06 | [ResidenteContacto](../../app/Models/ResidenteContacto.php): vínculo y autorizaciones, sin atribución automática de un relato clínico. |
| E07 | [ContratoFuentesPetersen](../../app/Modulos/ApoyoDecision/Aplicacion/Servicios/ContratoFuentesPetersen.php) y [contrato funcional](CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md): soportes admitidos por elemento. |
| E08 | [RegistrarObservacionCognitiva](../../app/Modulos/Cuidados/Aplicacion/Acciones/RegistrarObservacionCognitiva.php): señales, observación, autor y jornada. |
| E09 | [AplicarInstrumento](../../app/Modulos/Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php), [PrepararCapturaInstrumental](../../app/Modulos/Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental.php) y [CapturaInstrumentalModal](../../app/Modulos/Valoraciones/Presentacion/Livewire/Funcionalidades/CapturaInstrumentalModal.php). |
| E10 | [AplicacionInstrumento](../../app/Models/AplicacionInstrumento.php) y [RespuestaInstrumento](../../app/Models/RespuestaInstrumento.php): identidad, fechas, respuestas y preservación/rectificación. |
| E11 | [CapturaPetersenPanel](../../app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/CapturaPetersenPanel.php), [ValoracionOriginalPetersen](../../motor-experto/src/motor_experto/hechos/ValoracionOriginalPetersen.php), [modelo](../../app/Models/ValoracionPetersenOriginal.php) y [Policy](../../app/Modulos/ApoyoDecision/Presentacion/Policies/ValoracionPetersenPolicy.php). |
| E12 | [ControlFuncionalIadl](../../app/Models/ControlFuncionalIadl.php) y [ConsultarAutonomiaDescriptiva](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarAutonomiaDescriptiva.php): fuente descriptiva y límites de acreditación. |
| E13 | [RegistrarValoracionProfesional](../../app/Modulos/Valoraciones/Aplicacion/Acciones/RegistrarValoracionProfesional.php) y [ValoracionPsicologica](../../app/Models/ValoracionPsicologica.php). |
| E14 | [AntecedenteClinico](../../app/Models/AntecedenteClinico.php), [Diagnostico](../../app/Models/Diagnostico.php), [DocumentoClinico](../../app/Models/DocumentoClinico.php), [EstudioClinico](../../app/Models/EstudioClinico.php) y [diccionario del núcleo](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md). |
| E15 | [Configuración del experto](../../config/sistema_experto.php): defaults de perfiles, publicación y laboratorio; no es una lectura de la configuración efectiva. |
| E16 | [ConsultarConocimientoCognitivo](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarConocimientoCognitivo.php), [CargadorConocimiento](../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/Eloquent/CargadorConocimiento.php), [GestionarReglasExpertas](../../app/Modulos/ApoyoDecision/Aplicacion/Acciones/GestionarReglasExpertas.php) y [BloquesExpertos](../../app/Modulos/ApoyoDecision/Infraestructura/Persistencia/Conservacion/BloquesExpertos.php). |
| E17 | [PrepararEvaluacionInstitucional](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/PrepararEvaluacionInstitucional.php), [ConsultarPerfilInstrumental](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarPerfilInstrumental.php) y [InstantaneaEvaluacionInstitucional](../../motor-experto/src/motor_experto/conservacion/InstantaneaEvaluacionInstitucional.php). |
| E18 | [RevisarValoracionExperta](../../app/Modulos/ApoyoDecision/Aplicacion/Acciones/RevisarValoracionExperta.php) y [RevisionEvaluacionExperta](../../app/Models/RevisionEvaluacionExperta.php). |

## 9. Orden de trabajo y aceptación

| Entrega | Contenido y condición de cierre |
|---|---|
| 1. Matriz de datos, esta entrega | 27 necesidades clasificadas, fuente/captura/conservación/consumidor y acción siguiente; enlaces comprobados. Revisión estática, sin certificar runtime. |
| 2. Inventario efectivo autorizado | Instrumentos/ediciones y perfiles instalados, permisos y puntos de captura. Separar catálogo, investigación, simulación y pruebas realmente disponibles. Sin consultar historias reales innecesarias ni divulgar datos identificables. |
| 3. Selección de evaluación inicial | Comparación de candidatos por edad, idioma, escolaridad, normas, derechos de uso y capacitación. Decisión clínica/institucional explícita de los instrumentos y reglas aplicables; no requiere aprobar todos los candidatos. |
| 4. Contrato de captura elegido | Para cada entrada: PK fuente, tipo, unidad, ausencia, fecha, autor, corrección y condiciones. Reutilizar tablas/formularios antes de proponer ampliaciones justificadas; sin nuevas tablas. |
| 5. Primer circuito técnico | Casos sintéticos suficientes/incompletos/contradictorios: captura → hechos → reglas adoptadas → explicación → conservación → recuperación → revisión autorizada. La conservación conjunta debe resolverse de verdad para cerrar este recorrido. |
| 6. Aceptación | Evidencia de seguridad, persistencia, determinismo e historia; QA responsive de las pantallas modificadas, y evaluación posterior en uso real independiente diferenciada de las pruebas técnicas. No inventar probabilidades, puntos de corte ni frecuencias de control. |

No programar una nueva regla diagnóstica, umbral, escala o límite de permisos a partir de esta matriz. Las brechas de configuración/captura pueden investigarse y corregirse técnicamente en el alcance ya autorizado; lo clínico o estructural reservado conserva su proceso de decisión.

## 10. Verificación y límites de esta entrega

Revisión de archivos, enlaces relativos, IDs únicos D01–D27, cobertura de estados y navegación desde los índices. Sin escrituras a BDD, migraciones, cambios de permisos, historia ni secretos.

Pruebas relacionadas **localizadas y no ejecutadas en esta entrega documental**:

- [CapturaInstrumentalInstitucionalTest](../../tests/Feature/CapturaInstrumentalInstitucionalTest.php).
- [CapturaInstrumentalFormularioTest](../../tests/Feature/CapturaInstrumentalFormularioTest.php).
- [CapturaPetersenOriginalTest](../../tests/Feature/CapturaPetersenOriginalTest.php).
- [PermisosPetersenOriginalTest](../../tests/Feature/PermisosPetersenOriginalTest.php).
- [PermisosLecturaIadlPetersenTest](../../tests/Feature/PermisosLecturaIadlPetersenTest.php).
- [CicloObservacionalConservadoTest](../../tests/Feature/CicloObservacionalConservadoTest.php).

Build y pruebas funcionales no aplican a este cambio exclusivamente documental. Ni estos archivos ni las pruebas existentes demuestran datos completos para todos los residentes, implementación del catálogo de 45 candidatos o evaluación posterior en uso real del experto. Las tareas de runtime y el inventario efectivo quedan delimitados en la entrega 2.


## Actualización acotada D13 — 28/10/2026

Se implementó y verificó el [formulario propio de funcionamiento cotidiano](FORMULARIO_FUNCIONAMIENTO_COTIDIANO_2026_10_28.md), con autorización expresa de Medicina/Psicología. D13 ya no tiene pendiente localizar la captura/writer de esta fuente. El resto de la matriz conserva su corte y sus brechas; no se declara el experto completo.
