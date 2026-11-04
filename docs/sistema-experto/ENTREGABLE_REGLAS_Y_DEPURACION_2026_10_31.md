---
title: "Entregable único: reglas existentes, correcciones y depuración operativa"
status: CURRENT
version: "1.2"
last_reviewed: 2026-11-04
owner: ApoyoDecision
source_of_truth: false
verification_scope: RULE_GOVERNANCE_RUNTIME_CLEANUP_AND_CONSISTENT_SOURCE_READS
runtime_verified: true
---

# Resultado y alcance

**Actualización posterior del04/11:** se [activaron los tres perfiles iniciales de captura y puntuación](ENTREGA_ACTIVACION_PERFILES_INICIALES_2026_11_04.md). Las menciones de este corte a perfiles iniciales pendientes quedan sustituidas en ese alcance. La conservación conjunta instrumental/Petersen sigue separada y pendiente; no se habilitó clasificación de dominios desde los totales de cribado.

Se cierra la corrección técnica de la prueba de reglas, se detalla el catálogo real y se retiran ocho componentes exclusivamente experimentales que seguían dentro de `app/`. No se modifican las definiciones médicas, condiciones, consecuencias, umbrales o criterios institucionales. Tampoco se crean tablas, relaciones o permisos: se mantiene la decisión de la propietaria de conservarlos.

**Actualización posterior del mismo día:** se corrigen también la adquisición ordinaria de fuentes y la consulta de Enfermería; alcance y evidencia en la sección9. Este documento reúne ambos cambios para conservar un solo entregable de cierre técnico.

**Actualización del04/11/2026:** la sección10 incorpora la corrección de admisibilidad documental Petersen antes de acceder al archivo privado, con pruebas por motor. Los inventarios y verificaciones anteriores conservan sus fechas originales.

**No se declara cerrado el experto integral:** los perfiles instrumentales iniciales y la conservación conjunta siguen pendientes. La limpieza del código y una prueba lógica correcta no sustituyen esos componentes.

## 1. Inventario real, actualizado el 31/10/2026

Comprobado mediante `php scripts/inventariar-experto.php` dentro de una transacción de solo lectura PostgreSQL, sin adquirir historias, aplicaciones ni hechos de residentes. El comando ahora incluye `catalogo_reglas`: código, nombre, versión, estado, revisión, antecedentes y consecuencias de cada regla. Indica por separado su inclusión en el catálogo institucional; no certifica que pueda ejecutar con los datos de un caso.

| Familia | Reglas almacenadas | Alcance |
|---|---:|---|
| Cognitiva institucional | 17 | Quince reglas por componente y dos agregadas de memoria. |
| Petersen institucional | 4 | Cuatro patrones orientativos, condicionados por C01–C05 y el perfil interpretable. |
| Observacional | 3 | Hallazgos cotidianos documentados; no clasifican DCL por sí solos. |
| Histórica de simulación | 8 | Excluidas del catálogo institucional y del circuito operativo. |
| Total físico | 32 | No equivale a32 reglas operativas de DCL. |

Hay90 condiciones,32 consecuencias,205 nodos y130 relaciones. Las21 reglas cognitivas/Petersen registran revisión PENDIENTE; ese estado **no exige una firma ni un aval para desarrollar o ejecutar técnicamente**. Inactividad y rechazo sí se distinguen. El inventario sigue mostrando0 perfiles instrumentales y0 interpretativos configurados; no se los declara instalados por tener definiciones de reglas.

## 2. Las17 reglas cognitivas

Las quince reglas siguientes comparten esta forma institucional:

**SI** C1 interpretación específica versionada **Y** C2 atribución a la tarea **Y** C3 contexto suficiente están ACREDITADA, con conjunción válida dentro de la misma aplicación/componente, **ENTONCES** emitir el hallazgo acotado correspondiente. Los nombres C1/C2/C3 no son respuestas que se puedan marcar arbitrariamente ni un punto de corte universal.

| Regla | Componente | Consecuencia registrada |
|---|---|---|
| R-ATE-S01-P01 | Atención sostenida | ATE-R-SOSTENIMIENTO |
| R-ATE-S02-P01 | Atención selectiva | ATE-R-SELECCION |
| R-ATE-S03-P01 | Atención dividida | ATE-R-DISTRIBUCION |
| R-EJE-S01-P01 | Inhibición | EJE-R-INHIBICION |
| R-EJE-S02-P01 | Flexibilidad | EJE-R-FLEXIBILIDAD |
| R-EJE-S03-P01 | Memoria de trabajo/actualización | EJE-R-ACTUALIZACION |
| R-EJE-S04-P01 | Planificación | EJE-R-PLANIFICACION |
| R-EJE-S05-P01 | Razonamiento | EJE-R-RAZONAMIENTO |
| R-LEN-S01-P01 | Denominación | LEN-R-DENOMINACION |
| R-LEN-S02-P01 | Fluidez | LEN-R-FLUIDEZ |
| R-LEN-S03-P01 | Comprensión | LEN-R-COMPRENSION |
| R-LEN-S04-P01 | Repetición | LEN-R-REPETICION |
| R-LEN-S05-P01 | Discurso | LEN-R-DISCURSO |
| R-VIS-S01-P01 | Percepción visuoespacial | VIS-R-PERCEPCION |
| R-VIS-S02-P01 | Construcción visuoespacial | VIS-R-CONSTRUCCION |

Las dos reglas restantes son:

| Regla | Antecedente | Consecuencia |
|---|---|---|
| MEM-DIFICULTAD | DIFICULTAD_INSTRUMENTAL en la variable instrumental de memoria | DIFICULTAD_EVIDENCIADA. |
| MEM-SIN-DIFICULTAD | SIN_DIFICULTAD_INSTRUMENTAL en esa variable | SIN_DIFICULTAD_EVIDENCIADA en el alcance evaluado; no normalidad cognitiva general. |

La red contiene cuatro componentes de memoria, pero no existen cuatro reglas G09 de memoria independientes: hay dos reglas agregadas y controles de contexto del evaluador. Confundir19 componentes con19 reglas ocultaría esta diferencia.

Fuente de definiciones: `PaqueteCognitivoInstitucional`. El gobierno y la edición de versiones registradas permanecen en la ruta existente `/admin/sistema-experto/reglas`, sujetos a autorización.

## 3. Las cuatro reglas Petersen

Todas requieren C01–C05 acreditados y los cinco dominios interpretables según el contrato institucional vigente. C02 se deriva; no se captura manualmente.

| Regla | Memoria | Dominios alterados | Resultado orientativo |
|---|---|---|---|
| PET-01 | Alterada | Solo memoria | Posible DCL amnésico de dominio único. |
| PET-02 | Alterada | Memoria y al menos otro | Posible DCL amnésico multidominio. |
| PET-03 | Preservada en lo evaluado | Uno no mnésico | Posible DCL no amnésico de dominio único. |
| PET-04 | Preservada en lo evaluado | Dos o más no mnésicos | Posible DCL no amnésico multidominio. |

C01: cambio corroborado. C02: alteración objetiva derivada. C03: independencia instrumental relativa. C04: revisión diferencial. C05: calidad e interpretabilidad. No son por sí solos cinco reglas guardadas adicionales: hay lógica de acreditación y abstención en `EvaluarPetersenInstitucional`.

La precedencia actual conserva contradicción, impedimento diferencial, insuficiencia/no interpretabilidad y requisitos negativos antes de emitir un patrón. No activar una regla no equivale a normalidad.

## 4. Las tres reglas observacionales

| Regla | Hecho original afirmativo | Hallazgo permitido |
|---|---|---|
| OBS-MEM-01 | repite_preguntas | Episodio documentado de repetición de preguntas. |
| OBS-MEM-02 | olvida_indicaciones | Episodio documentado de olvido de indicaciones. |
| OBS-COG-01 | cambio_cognitivo | Cambio señalado que requiere corroboración. |

Exigen registro vigente, fecha válida al corte, autor y contexto coherentes. No significa ausencia de enfermedad cuando el campo es No. NULL es no evaluable. Estas reglas no interpretan MoCA/RUDAS ni establecen DCL.

## 5. Correcciones técnicas realizadas

La herramienta de gobierno probaba pertenencia de un valor a su dominio, pero ofrecía y aceptaba valores inactivos o no aprobados. El motor operativo exige dependencias habilitadas. Se corrige esa discrepancia:

- Selector: ofrece valores ACTIVO/APROBADO.
- Backend: rechaza valores, variables o dominios de entrada inactivos y valores sin aprobación, también ante solicitud manipulada.
- Prueba: conserva el cumplimiento lógico y muestra **por separado** la disponibilidad del catálogo y sus motivos. Una regla archivada o rechazada puede inspeccionarse lógicamente sin aparentar habilitación.
- Disponibilidad: comprobación informativa de estados, vigencia y dependencias directas. No acredita pertenencia completa de familia/linaje, mediciones, aplicabilidad ni permiso de ejecución. El filtro institucional conserva su responsabilidad independiente.
- Sin escrituras: probar no crea evaluaciones, decisiones de revisión o aprobaciones automáticas.
- Gobierno backend: la exclusión del catálogo institucional y su linaje se aplica también al probar, revisar y crear versiones; una llamada directa no reabre un paquete retirado que la interfaz ya ocultaba.

No se cambia la semántica AND ni la clasificación institucional para obtener pruebas verdes.

## 6. Cómo deberían revisarse las reglas médicas

Las siguientes son **propuestas para la siguiente revisión**, no nuevas reglas instaladas. La bibliografía no sustituye una decisión institucional ni aporta mediciones de un residente.

| Prioridad | Problema o diferencia | Corrección propuesta |
|---|---|---|
| 1 | C1/C2/C3 son antecedentes generales y faltan perfiles concretos. | Para cada componente documentar instrumento/edición, medida original, unidad, método, referencia aplicable, predicado exacto y motivo de abstención. No acreditar un antecedente desde el nombre de la prueba o una nota nominal. |
| 1 | La selección inicial incluye cribados globales, mientras el contrato exige interpretación de cinco dominios. | Definir el propósito del cribado y la evaluación ampliada por separado. No repartir el total de MoCA/RUDAS entre las cinco áreas ni prometer que un cribado global cumple el contrato completo. |
| 1 | El contrato impide reutilizar soportes/episodios entre dominios; una atención integral puede evaluar varias áreas. | Revisar expresamente si la independencia debe exigirse a cada medición y a la corroboración de cambio, en lugar de invalidar por compartir toda la atención. Mantener la regla actual hasta decidirlo; no presentarla como una exigencia universal de la medicina. |
| 2 | Cuatro componentes de memoria se resumen mediante dos reglas agregadas. | Precisar qué componentes sustentan la conclusión y qué ausencias impiden integrarla; decidir si se necesitan reglas por componente. No hacer que un único dato represente toda la memoria. |
| 2 | IADL propio y FAQ son fuentes diferentes. | Mantener actividades, oportunidad y causa de ayuda; no convertir ocho actividades propias en FAQ ni usar la vida en residencia como dependencia cognitiva. |
| 2 | El diferencial puede confundirse con ausencia de un diagnóstico registrado. | Exigir la revisión original prevista, conservar factores no resueltos y abstenerse cuando corresponda; no inferir exclusión de causas por tabla vacía. |
| 3 | Exigir cinco dominios completamente interpretables es una decisión local más estricta. | Decidir su alcance: clasificación completa de subtipos frente a señal de posible deterioro que necesita evaluación adicional. Conservar ambas salidas diferenciadas si se adoptan; no reducir requisitos silenciosamente. |
| 3 | Contenido de reglas, acreditación de datos y restricciones técnicas están en capas distintas. | Mantener un índice único que enlace definición SI/ENTONCES, predicados, fuentes y controles de adquisición. No duplicar reglas en Blade o Livewire. |

El fundamento médico respalda integrar historia fiable, cambio respecto al nivel previo, funcionamiento cotidiano y pruebas pertinentes al contexto, sin decidir por un número aislado. No establece las claves C01–C05, cinco episodios independientes ni una equivalencia universal entre cribados. La evaluación por dominios y las circunstancias educativas, lingüísticas y clínicas requieren interpretación contextual. [Guía profesional DETeCD, instrumentos de evaluación clínica](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/).

Toda modificación adoptada se hará como versión sucesora, con caso esperado positivo, negativo, insuficiente y contradictorio, sin sobrescribir la versión anterior. Durante el desarrollo no se pedirán estudios con residentes, firmas, referencias humanas ni avales externos.

## 7. Depuración de simulaciones

Retirados del autoload del producto y trasladados, con contenido idéntico, a fixtures de pruebas:

- SolicitudInferenciaDTO, EvidenciasEntradaDTO, ResultadoInferenciaDTO, ResultadoDclDTO e InformeExpertoDTO.
- PresentarPatronExperimental.
- ClasificarDclUseCase y GenerarInformeUseCase del recorrido experimental.

Además se extraen `presentarG09Prueba` y el catálogo `estadoG09` a soporte de pruebas. El presentador operativo ya no expone esas capacidades. `ConsultaCognitivaEnfermeria` rechaza el payload simulado y elimina el fallback de perfil de demostración. Las identidades históricas solo se cargan desde el bootstrap de pruebas; un proceso con únicamente el autoload del producto no puede resolver las ocho clases.

**Alcance físico preciso:** no se borraron filas ni historia de la BDD. El inventario todavía conserva ocho reglas históricas de simulación y su versión física; quedan marcadas fuera del catálogo institucional. Tampoco se borran las pruebas automatizadas aisladas ni los filtros que impiden reintroducir esas fuentes. No se afirma que la base instalada esté purgada físicamente. Su eliminación requeriría un inventario de referencias compartidas por registro; borrar por un nombre parecido podría afectar historia ajena.

No se conectó un motor ficticio ni se crearon valores de respaldo para completar casos.

## 8. Evidencia, integración y pendientes

Verificación ejecutada el31/10/2026:

| Comprobación | Resultado |
|---|---|
| SQLite: gobierno de reglas, gobierno Petersen, sucesión observacional y contratos/casos de uso Application | **172 pruebas /1568 aserciones correctas**. |
| Repetición final SQLite tras cerrar exclusión de paquetes por linaje en backend | **54 pruebas /239 aserciones correctas** en gobierno, Petersen y sucesión observacional. |
| SQLite: disponibilidad de catálogo, retirada, G09 aislado, narrativa histórica, DTO y contrato sin residentes | **42 pruebas /1041 aserciones correctas**. |
| SQLite: informes cognitivo/neurocognitivo y consulta integrada | **65 pruebas /693 aserciones correctas**. |
| Contratos finales de inventario/disponibilidad | **11 pruebas /383 aserciones correctas**. |
| Interfaz final de prueba lógica frente a regla archivada | **1 prueba /12 aserciones correctas**; no publica PK de condición en el resultado. |
| PostgreSQL18 desechable, gobierno/Petersen/sucesión/retirada/contratos/presentación | **90 pruebas correctas**,723 aserciones y1 omitida porque comprueba PRAGMA exclusivo deSQLite;91 casos descubiertos. No se omitió un fallo para conseguir verde. |
| Repetición final PostgreSQL del gobierno con bloqueo directo de paquetes retirados | **35 pruebas /141 aserciones correctas**. |
| Navegador sobre aplicación real aislada | **1 prueba correcta**: regla institucional, selección/ejecución lógica, cinco anchos1440/1280/1024/768/390, teclado, controles≥44px, sin overflow ni errores JS. Tema claro y menú móvil cerrado para comprobar el contenido; capturas390/1440 inspeccionadas. |
| Build Vite | Correcto; aviso previo de bundle mayor de500kB. |
| Autoload | Regenerado; prueba en proceso independiente confirma que el autoload del producto no resuelve ninguna de las ocho clases trasladadas. Los ocho archivos tienen SHA-256 idéntico aHEAD antes del traslado. |
| Gate independiente | **PASS acotado**: conserva prueba lógica, no concede ejecución, inventario solo de metadatos y aislamiento de componentes experimentales. No modifica reglas, FK o permisos. |

Los grupos de pruebas pueden compartir casos; no se suman como un número de pruebas únicas. No se ejecutó la suite global ni se afirma desempeño diagnóstico en uso real.

Comandos reproducibles: `php vendor/phpunit/phpunit/phpunit --no-progress --do-not-cache-result tests/Feature/GobiernoReglasExpertasTest.php tests/Feature/GobiernoPetersenInstitucionalTest.php tests/Feature/SucesionObservacionalTest.php tests/Unit/SistemaExperto/Application`; pruebas de retirada con `tests/Feature/RetiradaLaboratorioExpertoTest.php`; integración con `tests/Feature/InformeCognitivoTest.php tests/Feature/InformeNeurocognitivoTest.php tests/Feature/EjecucionConsultaCognitivaTest.php`; E2E con `RM_REGLAS_E2E=1 node --test tests/Frontend/gobierno-reglas-prueba.test.js`; `npm run build`. El perfilPostgreSQL se ejecutó en clúster propio temporal, con usuario sin privilegios y apagado/limpieza confirmados; no en la BDD operativa.

Evidencias locales, excluidas deGit: `output/experto-reglas-20261031-*`, `output/experto-depuracion-20261031-*` y `output/experto-auditoria-reglas-20261031-inventario.json`. El inventario no contiene historias y no se incorpora al commit.

El trabajo mantiene las rutas y permisos existentes. No se amplía la escritura clínica, no se instala DDL, no se envía correo y no se realiza push. Los perfiles iniciales y el guardado/historial conjuntos no se completan mediante este cambio: permanecen separados de la corrección de reglas y de la depuración operativa.

La entrega se conserva en un commit local de alcance propio. Los cambios locales de otros trabajos permanecen sin incorporar.

## 9. Cierre de lectura consistente y consulta de Enfermería

La carga ordinaria del perfil adquiría las medidas instrumentales y Petersen en transacciones separadas. El botón de análisis ya utilizaba una adquisición conjunta consistente; una interacción posterior volvía al recorrido ordinario. Se comparte ahora `LecturaCognitivaConsistente` entre ambos recorridos y la consulta de mediciones:

- Abre una transacción propia; en PostgreSQL establece REPEATABLE READ antes del primer SELECT. No acepta una transacción ajena ni eleva silenciosamente un savepoint.
- Recarga cuenta/residente y comprueba los permisos y Policies actuales. Después de liberar la instantánea vuelve a autorizarlos, incluidos documentos, funcionalidad y acceso a originales Petersen. No entrega la lectura si se pierde una capacidad que tenía al adquirirla.
- En la carga ordinaria, ambas ramas comparten una adquisición de mediciones. La proyección del análisis explícito sigue utilizando exclusivamente el DTO adquirido en esa petición.
- Conserva el recorrido parcial autorizado: sin versión se muestran los originales sin afirmar inferencia; sin permiso Petersen no se consulta ni expone esa rama.
- Enfermería usa exclusivamente `consultarMediciones()` en el render. No ejecuta la adquisición/inferencia conjunta al abrir la pantalla o cambiar un área; conserva consulta histórica publicable, seguimiento cognitivo y continuidad de cuidados según sus permisos.

No se guardan resultados en sesión, archivos, JSON, texto que sustituya relaciones ni otra persistencia alternativa. Las consultas continúan declarando `persistido=false`. Una nueva petición no se presenta como el análisis anterior.

Las fixtures afectadas usan el soporte existente `EsquemaSinTransaccion`: el coordinador debe poder abrir y liberar su transacción real. Se conserva la guarda de producción, en lugar de relajarla para satisfacer una transacción externa del arnés de pruebas. No se reducen las aserciones de autorización, procedencia o historia.

La revisión independiente de lectura emitió PASS acotado sobre autorización, fallbacks y separación de Enfermería. Las carreras PostgreSQL comprueban ambos recorridos frente a nuevos juicios/revisión de reglas y frente a revocación de cuenta, permiso documental y acceso Petersen.

Evidencia de esta actualización: `output/experto-cierre-lectura-*`, excluida del commit. El grupo final PostgreSQL18 desechable pasa **24 pruebas/193 aserciones**, incluidas ambas carreras de lectura, las revocaciones y los dos recorridos de Enfermería; sin omisiones. El informe cognitivo completo repetido en SQLite pasa **32 pruebas/319 aserciones**; la prueba de mediciones/componentes/evolución y la de historial/asignación pasan **2/41**. Los grupos se solapan y no se suman como pruebas únicas.

La comprobación funcional de navegador pasa **1 prueba**, con Medicina, Enfermería y administración del conocimiento en1440/1280/1024/768/390px: cero desbordamiento horizontal medido y cero errores JS; también comprueba teclado, navegación, IDOR y rechazo de la URL de simulación. Las capturas de Enfermería390/1440 confirman el fallback sin resultado publicado. Esto no acredita un rediseño visual global: la barra lateral compacta preexistente aún muestra etiquetas verticales; no se modificó en este alcance. El build Vite pasa, con el aviso previo de tamaño de bundle.

La batería inicial SQLite descubre188 casos:180 correctos,7 carreras exclusivas de PostgreSQL omitidas y1 error del arnés nuevo al intentar simular una clase final. Se corrigió la prueba sin modificar esa clase de producción y se repitió su grupo completo32/319, además del recorrido ampliado de Enfermería. Una batería separada de172 casos detectó dos expectativas antiguas: prohibir la lectura observacional aprobada y prohibir una gráfica que ahora representa cobertura. Se sustituyen por cero escrituras y por ausencia de patrón con cinco dominios desconocidos, conservando la prohibición de inferencia instrumental y de clasificaciones guardadas. La repetición completa de sus dos grupos pasa **57 pruebas/254 aserciones**, sin fallos ni pruebas riesgosas; la comprobación final explícita de cinco dominios pasa **1/12**. No se cambió producción para satisfacer las expectativas obsoletas. La revisión independiente de esas correcciones emitió PASS acotado.

La batería PostgreSQL ampliada de188 casos se interrumpió deliberadamente antes de completarse; no se presenta como aprobada. Se ejecutó en otro clúster propio el grupo final24/193 que cubre el cambio. Ambos runners terminaron su apagado/limpieza; no se migró ni se modificó la BDD operativa. No se ejecutó la suite global.

Comandos finales reproducibles: `php vendor/phpunit/phpunit/phpunit --no-progress --do-not-cache-result tests/Feature/InformeCognitivoTest.php`; el mismo comando para `tests/Feature/EstabilizacionExpertoContinuidadTest.php tests/Feature/HallazgosObservacionalesTest.php`; perfiles PostgreSQL desechables con filtro `PreparacionCompuestaCoherenteTest|EjecucionConsultaCognitivaTest|test_enfermeria_consulta_medicion_componente_y_evolucion_sin_inferencia|test_enfermeria_asignada_consulta_informe_y_pierde_acceso_al_revocar_asignacion`; navegador `RM_EXPERTO_E2E=1 RM_EXPERTO_FASE=cierre-lectura node --test tests/Frontend/experto-workspace-e2e.test.js`; `npm run build`. El perfil PostgreSQL requiere el runner seguro y su base temporal, no la configuración operativa.

### Límites que permanecen

1. **Perfiles iniciales MoCA/RUDAS/FAQ:** siguen sin una edición operativa, identidad/selectores y método de interpretación adoptados. Las capturas propias ya disponibles no se convierten en esos instrumentos ni el total de un cribado en cinco dominios. No se fabrican perfiles para eliminar el pendiente.
2. **Guardado e historial conjuntos instrumental/Petersen:** siguen sin implementación. La propietaria rechazó las relaciones/permisos propuestos y pidió conservar los actuales; esta corrección no revoca esa decisión ni la vuelve a solicitar. El historial observacional existente conserva su funcionamiento separado.
3. **Filas históricas de simulación:** permanece el alcance físico de la sección7. La exclusión operativa no se presenta como borrado de la BDD ni se elimina historia para aparentar depuración completa.

Por tanto, se cierra esta corrección de lectura, autorización y separación por rol; no se declara terminado el experto integral ni se cambian reglas médicas bajo esa afirmación.

## 10. Documentos Petersen: admisibilidad antes del acceso privado —04/11/2026

El lector podía intentar verificar los bytes de un documento antes de descartar su fecha posterior al corte. En soportes también abría el archivo antes de comprobar la compatibilidad del tipo, el autor y la atención. Aunque después excluyera la evidencia, realizaba un acceso privado innecesario y podía comunicar un motivo genérico de archivo no verificable en lugar del motivo temporal.

`ConsultarPetersenInstitucional` comprueba ahora pertenencia al residente y fecha antes de verificar un documento original. Para soportes comprueba además el tipo admitido, autor y coherencia con la atención. Conserva las constancias de exclusión con `verificado=false`, sin abrir el archivo, y prioriza el motivo `DOCUMENTO_ORIGINAL_FUERA_DE_CORTE` cuando corresponde. Un documento ajeno no aporta metadatos al caso. Los documentos admisibles continúan verificándose por sus bytes y hash; una modificación de contenido sigue causando exclusión.

No cambian reglas clínicas, umbrales, versiones, Policies, permisos, relaciones ni columnas. Las valoraciones originales, sus soportes e identidades permanecen intactos; la consulta no guarda una evaluación institucional ni la presenta como persistida. Los DTO siguen comprobando integridad y reconstrucción de su explicación sin volver a consultar los originales.

### Verificación técnica acotada

| Comprobación | Evidencia |
|---|---|
| Casos documentales SQLite, repetición final |10 pruebas/104 aserciones correctas. Incluye original y soporte futuro, ajeno, inactivo, soporte sin autor, atención ajena, archivo válido y alterado, motivo explícito, constancia y ausencia de escritura. |
| Recorrido SQL → mediciones → juicios → inferencia y autorización, SQLite |11 pruebas/84 aserciones correctas: cuatro patrones, roles sin competencia, cuenta revocada y soporte de otro residente. |
| Motor Petersen, DTO conjunto y membresías, sin BDD |105 pruebas/264 aserciones correctas: patrones, ausencia, contradicción e integridad de reconstrucción conforme al contrato existente. |
| PostgreSQL18 desechable: documentos y cuatro patrones |13 pruebas/162 aserciones correctas, sin omisiones. |
| PostgreSQL18 desechable: atención ajena |1 prueba/7 aserciones correctas: la FK compuesta rechaza la mutación con `23503`, conserva el documento y no crea una evaluación. Sin desactivar restricciones ni omitir el caso. |
| Revisión independiente de seguridad y regresión |PASS acotado del diff y evidencia de las pruebas; sin hallazgos pendientes en el cambio productivo. |
| Sintaxis, diff y matriz de trazabilidad offline |Correctos. La matriz conserva27 datos,19 componentes y23 métodos; no ejecuta SQL ni inferencia. |

Existe una diferencia previa entre esquemas efectivos de pruebas: PostgreSQL conserva la FK compuesta documento/atención/residente; SQLite permite construir ese vínculo incoherente y el lector lo descarta antes del acceso privado. La prueba comprueba la protección efectiva de cada motor; esta entrega no instala DDL ni afirma que sus restricciones físicas sean idénticas. La discrepancia de SQLite queda registrada para la auditoría del esquema, fuera de esta corrección de lectura.

La comprobación opcional de Pint encuentra categorías de estilo ya presentes en las versiones deHEAD de ambos archivos, contrastadas sobre copias locales. No se aplica una reformateación completa para resolver este defecto. `php -l` y `git diff --check` del cambio son correctos. No se repite el build ni la QA visual porque no cambia frontend. No se ejecuta la suite global.

Comandos reproducibles: `php vendor/phpunit/phpunit/phpunit --no-progress --do-not-cache-result tests/Feature/CapturaPetersenOriginalTest.php --filter=test_documento_`; el mismo archivo con filtro `test_caso_completo_se_clasifica|test_cuenta_revocada|test_roles_sin_competencia|test_soporte_de_otro_residente`; `php vendor/phpunit/phpunit/phpunit --no-progress --do-not-cache-result tests/Unit/PetersenInstitucionalTest.php tests/Unit/InstantaneaEvaluacionInstitucionalTest.php tests/Unit/MembresiasOriginalesPetersenTest.php`; `php scripts/verificar-matriz-trazabilidad-dcl.php --check`. Para PostgreSQL se usaron clústeres temporales propios, usuario sin privilegios, almacenamiento aislado, configuración de pruebas y apagado/limpieza confirmados. No se migró ni modificó la BDD operativa.

Evidencias locales, fuera del commit: `output/experto-documentos-20261104-*`, `output/experto-documentos-atencion-20261104-*` y runners aislados correspondientes. No contienen mediciones de residentes reales. El trabajo no introduce ni reconecta simulaciones operativas, no envía correo y no hace push.

### Estado integral que no cambia con este parche

- El motor y los recorridos técnicos con fuentes originales están implementados y tienen pruebas; un caso sin datos suficientes conserva una abstención explicada.
- Los perfiles iniciales MoCA/RUDAS/FAQ todavía necesitan fijar edición, identidad/selectores y método de interpretación aplicable. No se habilitan asignando un nombre a una captura propia ni inventando umbrales.
- El guardado e historial conjuntos instrumental/Petersen siguen pendientes de resolver la decisión estructural y de permisos previamente rechazada. Una nueva solicitud general de continuar no se toma como aprobación de aquella ampliación. El historial observacional existente permanece separado y funcional.
- No se solicita evaluación externa ni estudios con residentes para desarrollar. Estos pendientes son de contrato instrumental y conservación del resultado, no requisitos de aval externo.

Esta entrega cierra el defecto documental descrito y deja el alcance integrado verificable. No declara listo el experto integral mientras permanezcan los dos componentes anteriores.
