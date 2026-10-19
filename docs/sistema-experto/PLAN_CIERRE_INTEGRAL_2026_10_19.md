# Plan de trabajo para terminar el sistema experto

Fecha: 19/10/2026. Estado: plan de ejecución; no acredita la implementación de sus pendientes. Corte de referencia: `fb719a87`. Alcance: cerrar los ocho entregables institucionales reutilizando el motor, la integración y las autorizaciones existentes.

## Resultado final esperado

El profesional registra o consulta mediciones originales; el sistema identifica el instrumento y método compatibles, extrae hechos con procedencia, ejecuta reglas sobre una red semántica versionada, explica los resultados de los cinco dominios y evalúa los requisitos de Petersen. Conserva la evaluación completa y permite revisiones posteriores independientes de Medicina y Psicología.

Un resultado puede ser inconcluso por falta de información, contradicción o inaplicabilidad. Debe indicar la causa y qué dato original falta. La aceptación incluye casos completos que sí producen resultados sustentados y casos incompletos que se abstienen correctamente. No se insertan resultados esperados en historias ni se generan mediciones para conseguir una clasificación.

## Base que se conserva

- [x] Arquitectura híbrida: motor puro en `motor-experto`, integración Laravel en `ApoyoDecision`, captura en `Valoraciones`.
- [x] Diccionario de adquisición de 19 componentes; siete perfiles y once medidas normativas distribuidas en ocho componentes.
- [x] Captura por pregunta conectada con resultados e historial; tipos originales, fecha/hora, autor propio, confirmación, reenvío protegido y rollback.
- [x] Gobierno institucional de reglas: sucesoras, archivo, revisión y preservación de historia; autorizado a Medicina/Psicología y consulta de Superadministración.
- [x] Conservación y revisión profesional del alcance observacional representable.
- [x] Pruebas del último incremento: SQLite 127 / 2849 aserciones y PostgreSQL desechable 45 / 497; build PASS.

Estas verificaciones corresponden a sus respectivos alcances; no prueban que el perfil instrumental completo o Petersen institucional estén terminados.

## Fases y criterios de cierre

### 1. Consolidar el corte y la cobertura

- [x] Incorporar en una matriz única los ocho entregables, sus componentes existentes, pendientes y pruebas.
- [x] Revisar los cambios en curso relacionados antes de integrarlos, preservando trabajo ajeno e historia.
- [x] Fijar la revisión coherente de conocimiento, motor y contrato de captura que se entregará.
- [x] Mantener una lista explícita de decisiones de dominio o estructura aún no resueltas.

**Entregable:** matriz requisito → fuente → componente → archivo → prueba → evidencia. **Cierre:** cada pendiente tiene ubicación, dependencia y criterio verificable; lo ya entregado no se reimplementa.

**Cerrado el 19/10/2026:** [acta, matriz única y registro de versiones](CONSOLIDACION_PUNTO_1_2026_10_19.md), con [manifiesto de archivos y cambios locales](CORTE_PUNTO_1_2026_10_19.csv). Se fija el código integrado `f3d56ed2`, se distingue el laboratorio pendiente de integración y se registran P01–P09. No existe todavía un paquete institucional integral; se conserva esa brecha sin crear una versión común ficticia. Este cierre corresponde a la consolidación, no a haber terminado la red operativa ni las fases 2–8.

### 2. Cerrar adquisición y vinculación de fuentes originales

**Cierre de fuentes del lote confirmado:** la propietaria aclaró «Son observaciones clínicas por componente». Se conciliaron sus 648 filas con CSV retenidos, huellas, plan autorizado y auditoría individual: 19 definiciones, 19 preguntas, 38 opciones, 286 aplicaciones y 286 respuestas. Las pruebas cognitivas estandarizadas no administradas no son mediciones históricas faltantes de ese lote. [Dictamen, decisión vigente, fuentes primarias y verificación](DICTAMEN_CIERRE_FASE_2_2026_10_19.md). La QA visual del recorrido permanece pendiente; no se afirma aceptación integral de la interfaz.

- [x] Cotejo ampliado de la base, siete documentos privados, Excel de 37 hojas, JSON y metadatos auxiliares; listado privado de las 487 aplicaciones para vinculación y conciliación. Investigación primaria final y requisitos precisos para resolver los pendientes documentados.
- [x] Contrastar el paquete instrumental disponible y completar la comprobación automática de identidad, entradas, procedencia y faltantes desde la captura autorizada; diccionario de los 23 métodos y métricas de completitud 60+. [Contrato y evidencia del incremento](FASE_2_COMPROBACION_ADQUISICION_2026_10_19.md).
- [x] Identificar el acto y cotejar las aplicaciones existentes del lote: observaciones por componente, con preguntas, respuestas, autor, fechas y referencias exactas. Edición/forma y medidas normativas no aplican a estas observaciones; la captura de pruebas futuras conserva su contrato instrumental.
- [x] Distinguir profesional que aplicó una evaluación histórica de quien transcribe ahora: autor original cotejado, cuenta actual e instante técnico separados. Permiso específico de Medicina/Psicología y cinco columnas aprobadas instalados en desarrollo.
- [x] Conservar el contexto aportado y su procedencia sin convertir categorías escolares, horarios de agenda o condiciones genéricas en entradas de un método que no se aplicó. El diccionario completo queda preparado para futuras mediciones.
- [x] Preparar importación controlada: API/Action de conciliación sin escrituras, conflictos, transacción y reintento por fuente; vinculación explícita de registros previos y rectificaciones enlazadas. No se ha ejecutado una nueva carga histórica sin originales identificables.
- [x] Probar NULL, cero, «No», preguntas/opciones ajenas, fuentes anuladas/futuras, autor ausente, aplicaciones incompletas y revocación de acceso; concurrencia en PostgreSQL desechable.

**Avance técnico del 19/10/2026:** [acta de fase 2 y matriz de evidencia](FASE_2_FUENTES_ORIGINALES_2026_10_19.md), con [investigación de los métodos y diccionario de procedencia](ORION_FUENTES_ORIGINALES_E_INVESTIGACION_2026_10_19.md). Se conservan íntegras 487 aplicaciones y 2296 respuestas locales. Permanecen abiertos el cotejo de instrumentos realmente aplicados y las mediciones/condiciones originales ausentes; la bibliografía no permite generarlas. QA visual pendiente por navegador deshabilitado en Work.

**Resolución posterior del mismo día:** el pendiente anterior de identificar si las 286 entradas eran pruebas quedó resuelto por la propietaria: son observaciones. Su conciliación está completada; no se asignan perfiles normativos ni se repite la carga. Los 201 Barthel previos no se incluyen en el cierre documental de ese lote. Se mantiene el intento de QA visual como no verificado y el trabajo de interpretación de pruebas futuras en sus fases correspondientes.

**Entregable:** catálogo de vínculos verificables y adquisición reproducible. **Cierre:** cada dato usado tiene residente, aplicación, origen, fecha y autor identificables; los datos no aportados siguen faltantes.

### 3. Completar interpretación instrumental

**Trabajo autorizado el 19/10/2026:** verificar ocho componentes con
cálculo y resolver los once restantes. Objetivo, fuentes y seguimiento en
[Fase 3: componentes](FASE_3_COMPONENTES_2026_10_19.md). No se marca cerrado
por disponer únicamente de campos o motivos de abstención.

- [x] Verificar los siete perfiles implementados contra su edición y las fuentes originales bibliográficas. El cotejo de aplicaciones de residentes permanece en fase 2.
- [x] Resolver técnicamente los once componentes: MEM-S03/S04, ATE-S01/S02, EJE-S03/S04/S05, LEN-S03/S04/S05 y VIS-S01, con métodos de referencia y condiciones expresas.
- [x] Documentar instrumento, administración conceptual, población, puntuación, entradas, cálculo, dirección, límites, acceso y derechos; sin reproducir materiales protegidos ni atribuir manuales no obtenidos.
- [x] Identificar alternativas y su medición exacta, sin trasladar baremos entre ediciones o poblaciones. La elección técnica no impone una sustitución institucional.
- [x] Implementar las comparaciones con ejemplos numéricos independientes, límites y casos fuera de población.
- [ ] Decidir adopción institucional de las alternativas/materiales legítimos y validar explícitamente la contribución de una medición a un hallazgo clínico y su dominio. Se conserva como pendiente institucional y de integración de fases 4–5; un z aislado no determina DCL ni un score global.

**Cierre técnico el 19/10/2026:** [acta, matriz completa, fuentes y pruebas](CIERRE_TECNICO_FASE_3_2026_10_19.md).
Catálogo versión 8, **19/19 componentes** con comparación bajo condiciones;
373 pruebas aprobadas tanto en SQLite como en PostgreSQL desechable. La revisión
independiente de las tres últimas conversiones es PASS técnico. El alcance de
cálculo/documentación solicitado queda cerrado; adopción y validación clínica
continúan expresamente pendientes. No se cambia la historia ni se ejecuta
Petersen para completar la cobertura. Los avances de otras fases se documentan
en sus propias actas y no se atribuyen a este cierre.

**Entregable:** matriz ejecutable de los 19 componentes con contratos de interpretación definidos para el alcance acordado. **Cierre:** todos los componentes comprometidos tienen un método aplicable y prueba de cálculo; una causa de abstención no se cuenta como normalizador completado.

### 4. Cerrar red semántica y ejecutor de cinco dominios

**Avance técnico del 19/10/2026:** [checklist, recorrido y evidencia de fase 4](FASE_4_RED_Y_REGLAS_2026_10_19.md).

- [x] Compilador de la topología de cinco dominios/19 componentes y de las rutas de las reglas.
- [x] Revisión integrada D-131/G09 con versiones y hashes de origen; reproducción tras archivo.
- [x] Entrada declarativa desde fuentes originales remapeadas, sin generadores del laboratorio; cada conclusión conserva reglas/evidencias.
- [x] Conjunciones por aplicación/componente, NULL explícito, contradicciones y agrupación conservadora de soportes.
- [x] Gobierno de variables de subcomponentes y comprobación del contrato inferencial del servidor antes de publicar una regla.
- [x] Verificación SQLite/PostgreSQL desechable, concurrencia, independencia del motor y build del candidato.
- [x] Conector de lectura SQL autorizada hacia hechos y reglas, con instrumento/versión/pregunta exactos, fecha de corte, autor original y soporte compartido por aplicación.
- [x] Aplicabilidad por edad en la aplicación y métricas de cobertura 60+, sin duplicar componentes ni convertir cobertura en exactitud diagnóstica. [Fuentes y contrato del incremento](FASE_4_FUENTES_Y_METRICAS_60_MAS_2026_10_19.md).
- [x] Revisión independiente y corrección de captura duplicada/procedencia omitida; una captura compartida, preguntas sin respuesta y fechas completas. [Acta y fuentes verificadas](CIERRE_TRAZABILIDAD_Y_REPRODUCCION_2026_10_19.md).

**Pendiente de cierre institucional:** conservar y vincular el paquete real con sus reglas y preguntas originales aún no cotejadas en fases 2/3; cerrar la conservación completa en fase 6. El conector de extracción/autorización está implementado y se verifica con datos sintéticos. El catálogo local conserva dos versiones no experimentales inactivas y sin reglas; la única activa pertenece al laboratorio. Las casillas institucionales siguientes no se marcan por la sola aceptación técnica del conector.

- [ ] Conectar fuente → hecho → variable → componente → dominio → regla → resultado.
- [ ] Validar tipos, referencias, versiones y cobertura de los 19 componentes.
- [ ] Integrar Memoria y las quince reglas G09 mediante contratos institucionales verificables. Las referencias de laboratorio no se promueven a evidencia clínica.
- [ ] Aplicar criterios documentados de combinación, contradicción, antigüedad y suficiencia; impedir doble conteo de aplicaciones o episodios compartidos.
- [ ] Conectar el gobierno existente con todo el conocimiento ejecutable: probar, crear sucesora, sustituir y archivar preservando la evaluación anterior y el autor/revisor/motivo.
- [ ] Producir trazas que permitan recorrer cada conclusión hasta las condiciones y fuentes originales.

**Entregable:** ejecutor institucional integrado y red navegable. **Cierre:** casos de cada dominio muestran por qué se activó o no una regla; retirar una regla afecta nuevas ejecuciones y conserva las anteriores.

### 5. Integrar Petersen institucional

- [ ] Especificar la derivación de C01–C05 desde soportes originales: cambio corroborado, alteración objetiva, independencia instrumental relativa, evaluación diferencial y calidad/interpretabilidad.
- [ ] Utilizar IADL descriptiva con oportunidad, ayuda y contexto; actividades básicas no sustituyen la independencia instrumental.
- [ ] Comprobar independencia de soportes, fechas, corte, residente y versión. La ausencia de un diagnóstico no constituye una revisión diferencial.
- [ ] Integrar los cuatro patrones y abstenciones con los resultados sustentados de los dominios.
- [ ] Probar patrones completos, criterios ausentes, contradicciones y fuentes incompatibles sin incorporar la respuesta esperada como entrada.

**Entregable:** clasificación orientativa o abstención explicada, con soportes individualizados. **Cierre:** el motor obtiene el resultado desde la evidencia y permite reconstruirlo; no depende de declaraciones sintéticas del laboratorio.

### 6. Completar conservación exacta

- [x] Preparar DTO canónico en memoria del alcance instrumental/declarativo y reproducción sin SQL/configuración actuales, con metadatos de preparación sin declarar guardado.
- [x] Implementar ZIP recuperable del software del motor, manifiesto por archivo y verificación de alteración/ausencia/no reemplazo, sin historia clínica.
- [ ] Vincular la evaluación a un release retenido y representar relacionalmente el DTO completo; los avances previos no equivalen a persistencia institucional.
- [ ] Empezar el diseño durante la fase 1; cerrar el contrato definitivo con las entradas/salidas de las fases 2–5.
- [ ] Reutilizar cabecera, conocimiento, hechos, trazas, soportes y revisiones existentes cuando su significado sea compatible.
- [ ] Definir DTO canónico, precisión, fechas, versiones y fuentes admitidas y descartadas; conservar también selectores sin respuesta y artefactos de release recuperables.
- [ ] Reducir y justificar cualquier extensión mínima, preservando 4FN. El análisis de nueve relaciones es candidato, no una obligación ni una autorización nueva.
- [ ] Presentar el diccionario concreto antes de modificar la estructura congelada. Conservar el inventario vigente de 106 tablas hasta una decisión explícita aplicable.
- [ ] Implementar guardar/recuperar íntegro, atomicidad, identidad idempotente e inmutabilidad; fallo explícito si hay información no representable.
- [ ] Verificar replay tras modificar fuentes y conocimiento, corrupción, cruce de residente/versión y carreras concurrentes en PostgreSQL desechable.

**Entregable:** evaluación institucional completa y reproducible. **Cierre:** guardar → recuperar devuelve el mismo contenido y explicación; ninguna fuente actual cambia el resultado histórico.

### 7. Terminar resultados, revisión y experiencia operativa

- [ ] Mostrar conclusión, cinco dominios, mediciones, fuentes, reglas, contradicciones y faltantes desde la evaluación íntegra conservada.
- [ ] Diferenciar estados de captura, cálculo, resultado y revisión; quitar textos o acciones que no correspondan al contrato real mostrado.
- [ ] Conectar las dos cadenas posteriores de Medicina y Psicología con decisiones, justificación, fecha y profesional; conservar rectificaciones.
- [ ] Completar navegación entre captura, ejecución, informe, historial y gobierno, respetando rol y alcance del residente.
- [ ] Verificar teclado, foco, errores, guardado, recuperación y responsive a 1440, 1280, 1024, 768 y 390 px.

**Entregable:** recorrido utilizable de extremo a extremo. **Cierre:** un usuario autorizado registra, ejecuta, consulta y revisa sin pasos técnicos manuales ni pérdida de datos; los actores no autorizados no leen ni escriben por llamadas directas.

### 8. Aceptación y entrega

- [ ] Mantener pruebas por fase; consolidar al final unitarias, integración, seguridad, regresión y aceptación.
- [ ] Usar casos sintéticos reproducibles positivos, negativos, incompletos, contradictorios y de límite, con resultados esperados definidos independientemente del motor.
- [ ] Revisar conocimiento y seguridad de forma independiente; corregir hallazgos antes del cierre.
- [ ] Ejecutar SQLite, PostgreSQL desechable, pruebas concurrentes pertinentes y build; registrar comandos y evidencia visual real.
- [ ] Completar manual de operación, técnico, reglas, arquitectura, reproducción y matriz de trazabilidad.
- [ ] Integrar por PR hacia `develop`, con comprobaciones requeridas. Push y despliegue requieren instrucción explícita; preparación de release no equivale a validación clínica.

**Entregable:** versión técnica aceptada y expediente de evidencia. **Cierre:** sin defectos críticos/altos pendientes en el alcance, sin sustitutos ficticios esenciales y con los límites de uso documentados. La evaluación clínica del desempeño tiene su evidencia y revisión propias; no se deduce de una suite verde.

## Orden y dependencias

Camino principal: **1 → 2 → 3 → 4 → 5 → 6 → 7 → 8**. El diseño de conservación comienza en 1; su implementación se cierra con los contratos de entrada/salida. Las pruebas y la documentación acompañan cada fase. Una decisión pendiente se aísla sin detener trabajo independiente.

Las fases 2 y 3 requieren mediciones/protocolos originales y, cuando corresponda, manuales legítimos. La investigación completa definiciones y métodos; no completa valores históricos de residentes. La fase 6 puede requerir una decisión estructural concreta. La fase 7 requiere acceso al navegador para QA real.

El desarrollo y las pruebas no esperan una firma profesional previa. Las nuevas decisiones clínicas/institucionales que no estén definidas se documentan para decisión de la propietaria; las revisiones profesionales de resultados se registran después, con su estado verdadero.

## Seguimiento por punto

Al cerrar cada punto se actualizarán las casillas y se entregará: qué cambió, archivos/componentes, pruebas ejecutadas, limitaciones pendientes y commit. Las marcas se basan en evidencia; los ocho entregables no se consideran completos por tener clases o pantallas creadas.

## Fuentes de este plan

- [Cierre y ocho entregables](CIERRE_OPERATIVO_1_A_8_2026_10_18.md).
- [Métodos y campos investigados](METODOS_Y_CAMPOS_INVESTIGADOS_V2_2026_10_18.md).
- [Captura conectada y evidencia](CAPTURA_INSTRUMENTAL_FORMULARIO_2026_10_19.md).
- [Brecha y alternativas de conservación instrumental](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md).
- [Baseline y decisiones estructurales aprobadas](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md).
