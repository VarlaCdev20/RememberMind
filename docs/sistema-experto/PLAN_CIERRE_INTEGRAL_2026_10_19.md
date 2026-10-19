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

- [ ] Cotejar las aplicaciones realmente existentes: instrumento, edición, forma, idioma y condiciones; registrar los vínculos exactos de preguntas/columnas con cada entrada del método.
- [ ] Distinguir profesional que aplicó una evaluación histórica de quien transcribe ahora. El formulario actual atribuye la escritura al profesional de sesión; no debe utilizarse para suplantar al autor original.
- [ ] Completar la captura de contexto que exija cada método con soporte verificable: escolaridad, fechas, condiciones sensoriales/motoras y desviaciones.
- [ ] Preparar importación controlada: validación previa, informe de conflictos, transacción, reintento y conciliación por fuente. Una corrección preserva el registro anterior; no sustituye longitudinalidad por borrado.
- [ ] Probar NULL, cero, «No», preguntas/opciones ajenas, fuentes anuladas/futuras, autor ausente, aplicaciones incompletas y revocación de acceso.

**Entregable:** catálogo de vínculos verificables y adquisición reproducible. **Cierre:** cada dato usado tiene residente, aplicación, origen, fecha y autor identificables; los datos no aportados siguen faltantes.

### 3. Completar interpretación instrumental

**Trabajo activo autorizado el 19/10/2026:** verificar ocho componentes con
cálculo y resolver los once restantes. Objetivo, fuentes y seguimiento en
[Fase 3: componentes](FASE_3_COMPONENTES_2026_10_19.md). No se marca cerrado
por disponer únicamente de campos o motivos de abstención.

- [ ] Verificar los siete perfiles implementados contra su edición y las fuentes originales que se vinculen.
- [ ] Resolver once componentes sin normalizador: MEM-S03/S04, ATE-S01/S02, EJE-S03/S04/S05, LEN-S03/S04/S05 y VIS-S01.
- [ ] Para cada uno, documentar instrumento, administración, población, rúbrica, entradas, cálculo, dirección y límites. Cotejar manuales y derechos de uso cuando correspondan.
- [ ] Si una referencia no es aplicable, identificar una alternativa pertinente y la medición que requiere; no trasladar baremos de otra edición o población. Una sustitución de instrumento o nuevo criterio institucional debe quedar decidida y registrada.
- [ ] Implementar los métodos aplicables con ejemplos numéricos independientes, límites y casos fuera de población.
- [ ] Definir explícitamente cómo una medición contribuye a un hallazgo clínico y a su dominio. Un z aislado no determina automáticamente DCL ni un score global.

**Entregable:** matriz ejecutable de los 19 componentes con contratos de interpretación definidos para el alcance acordado. **Cierre:** todos los componentes comprometidos tienen un método aplicable y prueba de cálculo; una causa de abstención no se cuenta como normalizador completado.

### 4. Cerrar red semántica y ejecutor de cinco dominios

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
