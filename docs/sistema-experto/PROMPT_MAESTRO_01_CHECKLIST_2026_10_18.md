# Sistema experto — Fase 01: memoria de alcance, entregables y checklist

Fecha: 18/10/2026. **Estado: PROTOTIPO TÉCNICO VERIFICADO, con conservación experimental según §14**. Implementación autorizada por la propietaria: «OKEY EMPIEZA Y VE MARCANDO LOS CHECKLIST». Evidencia en [auditoría de Fase 01](FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md).

La instrucción inicial fue analizar y guardar el alcance sin implementar. Ese corte documental se completó; la autorización posterior inicia auditoría, implementación y pruebas, manteniendo las restricciones estructurales y clínicas del prompt.

Esta es la memoria persistente del trabajo en el repositorio; no supone memoria global fuera de este proyecto.

**Corte final: 53 casillas verificadas y 1 reservada.** Es un conteo de comprobaciones, no un porcentaje de aceptación clínica. Los recorridos A–M se detallan en la [matriz integral](FASE01_ACEPTACION_INTEGRAL_2026_10_18.md). Acceso documental y regresión resueltos. La casilla pendiente corresponde a conservación institucional completa en BDD: la fase utiliza la alternativa compatible del §14 y entrega la RFC, sin ampliar el esquema.

## Fuente conservada

- [Prompt Maestro 01 original completo](PROMPT_MAESTRO_01_FUENTE_2026_10_18.md), copiado sin modificar el adjunto.
- Procedencia: `C:/Users/CARLAENCINAS/.codex/attachments/30d58fc2-ed59-4c9c-8df7-2f7dc29a3ea2/Texto pegado.txt`.
- SHA-256 de ambos archivos: `14CDC0420D1C6282684D2356769E95A43987E373A479F35FAEE1FD1542F373CD`.
- Selección de fuentes y organización: `docs/sistema/STACK_SKILLS_SISTEMA.md`, AGENTS aplicables y `docs/arquitectura/ADR_ARQUITECTURA_HIBRIDA.md`.

## Objetivo de la fase

Completar e integrar el sistema existente hasta obtener un **prototipo de ingeniería ejecutable, explicable y reproducible**, con cinco dominios cognitivos, conocimiento administrable y Petersen experimental sobre datos sintéticos controlados. Un resultado real significa calculado por el motor y reconstruible desde sus hechos; no significa diagnóstico clínico ni información sintética presentada como observación real.

Se reutilizan `motor-experto/` y `app/Modulos/ApoyoDecision/`. No se recrea la arquitectura antigua en `app/SistemaExperto` ni se elimina automáticamente su implementación previa: primero se localizan consumidores y responsabilidades. Nuevos textos de usuario emplearán «sistema experto», conservando identificadores técnicos necesarios.

## Resolución de fuentes y límites

| Punto | Evidencia y decisión para el plan |
|---|---|
| Inventario de BDD | El adjunto cita 71 operativas y 23 expertas. El baseline y las extensiones aprobadas vigentes registran **73 operativas + 33 expertas = 106 tablas de negocio**. `InventarioExperto` separa 23 originales, 9 de conservación y 1 de revisiones. No se interpreta el número antiguo como orden de borrar o revertir tablas; verificar el inventario instalado en la auditoría inicial. |
| Cambios estructurales | Esta solicitud no aprueba tablas, columnas, estados, catálogos, PK/FK ni permisos nuevos. Reutilizar lo aprobado; si algo no cabe, entregar RFC concreta y mantener la acción protegida. No fingir archivado con una bandera temporal o auditoría usada como estado de negocio. |
| Alcance experimental | El nuevo documento exige datos artificiales y la indicación visible `SIMULACIÓN TÉCNICA / NO VÁLIDO PARA DECISIONES ASISTENCIALES`. Esa exigencia gobierna las salidas experimentales de esta fase y sustituye, en ese ámbito, el pedido anterior de ocultar la condición sintética. Este análisis no cambia la pantalla actual. |
| Administración y ejecución | La autorización anterior para que Medicina/Psicología guarden observaciones no les concede edición/publicación/archivo de conocimiento. Auditar cada capacidad y sus permisos existentes; si no existe responsable autorizado, proteger esa mutación y documentar la decisión pendiente. |
| Nuevos parámetros | La investigación previa de ventanas de recurrencia no es un protocolo ni autoriza umbrales para esta fase. No introducir ventanas, pesos, baremos o equivalencias clínicas por conveniencia técnica. |
| Historia | Nueva versión o sucesora conserva reglas, fuentes, condiciones, resultados y trazas originales. No reinterpretar resultados históricos con fuentes actuales ni eliminar historia. |
| Seguridad clínica | No conectar ejecuciones experimentales a historia asistencial, alertas, cuidados, prescripciones o tratamientos. Un permiso técnico o TEST_ONLY no habilita uso asistencial. |

## Estado preliminar y reutilización

Lectura de código, documentación y referencias locales; **no equivale a la auditoría exhaustiva ni a la aceptación integral de esta fase**.

| Componente | Estado preliminar | Base localizada / brecha a comprobar |
|---|---|---|
| Motor COG-MEM | Existente; reutilizable | `motor-experto/src/motor_experto/aplicacion/casos_uso/MotorExperto.php`, compuertas COG-MEM, `EvaluadorReglas`, `ResolvedorSoportesCOGMEM` y constructor de traza. Verificar contratos D-130/D-131 y regresión antes de integrar. |
| Memoria, grafo y mapeo | Existentes | `MemoriaTrabajo`, `PaqueteConocimiento`, `ValidadorPaqueteConocimiento`, `MapeadorSemantico` y `RedSemantica`. Falta acreditar el flujo común completo y el manifiesto de cinco dominios. |
| G08/G09, cuatro dominios adicionales | Experimental en otra rama | Rama local `feature/orion-g09-experimental`; contiene guía, catálogo de quince reglas y ejecutor/cotejo D-130. El checkout actual tiene presentación G09 aislada, no esa integración completa. Revisar diferencias y procedencia antes de reutilizar; no fusionar la rama por este análisis. |
| Orquestación y puente Petersen experimentales | Reutilizables en otro worktree; reconciliación pendiente | `C:/Users/CARLAENCINAS/.codex/worktrees/pr15-ci-utf8/RememberMind/motor-experto/src/motor_experto/investigacion/dcl/` contiene `OrquestadorCincoDominiosExperimental`, `AdaptadorEstadosPetersenExperimental` e `IntegradorReferenciasDCLExperimental`. Revisar contra los caminos locales `MotorInferenciaService` y `AnalizarCasoExperimentalUseCase`; no duplicar ni dar por integrada su ejecución canónica. |
| Petersen | Implementación y oráculos existentes | `app/SistemaExperto/Domain/Services/Dcl/ClasificadorPetersenService.php` y pruebas; CSV locales con 30 casos y 32 configuraciones. Comprobar integración con soportes independientes, no rehacer el clasificador por su ubicación. |
| Fuentes y persistencia | Parcialmente integradas | Adaptadores y conservación V2 existentes; nueva conservación observacional aprobada pertenece a una entrega previa. Auditar todos los metadatos requeridos y el replay de los cinco dominios; guardar tres booleanos no acredita base de hechos integral. |
| Gestor dinámico de reglas | Flujo completo pendiente de acreditar | Existen Models de versión/regla/condiciones/consecuencias y restricciones de inmutabilidad. No se ha demostrado crear → probar → suceder → archivar → recuperar historia desde un gestor autorizado. Prioridad de esta fase. |
| Presentación Laravel | Existente, con entradas experimentales acotadas | `ResultadosExpertoResidente`, lectores y DTOs del informe. No confundir narrativa/fixtures con resultados de un orquestador integral. |
| Fuentes maestras | Auditoría pendiente | Localizar/consultar Documento Maestro, D-123–D-144, EXP-01, G06–G13 y contratos de clasificación/explicación. Hay referencias externas y material en otras ramas; no se consideran todos leídos por haber leído este prompt. |

La revisión independiente de lectura localizó código/documentos G08/G09 y el puente de cinco dominios en el worktree citado, pero no copias completas locales del conjunto Maestro/D-123–D-144/EXP-01/G06–G13. No se declara que esos documentos no existan: su recuperación y cotejo siguen pendientes. El panel `ConocimientoPanel` localizado consulta versiones/demostraciones; eso no acredita administración dinámica. Las columnas actuales de vigencia/retiro/versión anterior son reutilizables, pero sus restricciones deben examinarse antes de afirmar que permiten el archivado durable.

La rama de trabajo inspeccionada es `feature/apoyo-decision-informe-neurocognitivo`; HEAD observado `b8ee4b77`. Hay cambios de trabajo previos: conservarlos y distinguirlos del trabajo futuro. Las referencias locales de ramas no prueban el estado remoto actual ni las solicitudes de integración abiertas; estas últimas siguen pendientes de consulta durante la auditoría.

## Entregables finales A–M y aceptación

Los entregables A–M se aceptan por recorrido completo. Las casillas siguientes se actualizan por incremento; la existencia de clases o pruebas previas no acredita por sí sola el cumplimiento integral.

| ID | Entregable | Evidencia mínima de aceptación |
|---|---|---|
| A | Código funcional integrado y matriz de brechas inicial/final | Un caso sintético recorre fuentes → hechos → motor → resultado → conservación → lectura, reutilizando componentes existentes. |
| B | Catálogo de hechos, extractores y mapeos | Identidad, valor/tipo original, procedencia, autor, fechas, episodio, versión, admisibilidad y motivo reconstruibles; NULL preservado. |
| C | Red semántica y catálogo versionado de reglas | Cinco dominios y 19 subcomponentes conforme al Maestro; variables, relaciones, condiciones AND, consecuencias, fundamento y casos; sin imponer una regla por subcomponente. |
| D | Gestor técnico de conocimiento | Crear, probar, comparar, suceder y retirar reglas de nuevas ejecuciones sin editar el motor ni alterar historia; permisos y archivado durable compatibles o RFC exacta protegida. |
| E | Ejecutor de cinco dominios | Memoria + quince reglas G09 (Atención 3, Ejecutiva 5, Lenguaje 5, Visuoespacial 2); compuertas, confusores, contradicción, independencia y abstención. |
| F | Petersen experimental integrado | C01–C05 y A/P/U/I/X con soportes sintéticos independientes; PET-01–04, abstenciones y rechazo de incoherencias; oráculos de 30/32 conservados. |
| G | Contrato versionado de resultados y explicaciones | Perfil, estados, versiones, reglas/condiciones/soportes, faltantes, contradicciones, comparaciones y limitaciones; cada frase enlaza con traza real. |
| H | Integración técnica Laravel | Actions/casos de uso, autorización, lectura/ejecución/historial y contratos consumibles por Livewire; motor independiente y salidas experimentales identificadas. |
| I | Casos sintéticos y reproducción | Casos positivos, negativos, incompletos, inválidos, contradictorios y multidominio; comandos repetibles y resultados derivados de hechos, sin informes prefabricados. |
| J | Pruebas automatizadas y reporte | Unitarias, integración SQLite/PostgreSQL desechable, seguridad, regresión y aceptación; comandos, entorno, cantidades, fallos y límites explícitos. |
| K | Documentación, manual y diagrama | Arquitectura implementada, flujo, versiones, operación/reproducción, estados y recuperación de errores, responsables y límites de uso. |
| L | Matriz de trazabilidad final | Requisito → componente → archivo → prueba → evidencia, incluida causa verificable de no emisión. |
| M | Restricciones y autorizaciones pendientes | Brecha, impacto, alternativa compatible y RFC concreta; separar pendiente institucional/estructural de defecto técnico. |

## Checklist de ejecución por incrementos

### 0. Preparación y auditoría

- [x] Leer el Prompt Maestro 01 completo y conservar copia verificable.
- [x] Guardar esta memoria, entregables y checklist en el repositorio.
- [x] Identificar la discrepancia del inventario y la exigencia actual de procedencia experimental.
- [x] Localizar el núcleo, integración Laravel, oráculos Petersen y rama G09 para reutilización.
- [x] Consultar y versionar las fuentes rectoras: Maestro, D-123–D-144, EXP-01, G06–G13 y contratos de clasificación/explicación. Acceso completo a EXP-01/G09-EXP resuelto tras autenticación de la propietaria; [cotejo, hashes y matriz VT](FASE01_COTEJO_DOCUMENTAL_Y_VT_2026_10_18.md). Las matrices se consultaron en las secciones pertinentes, sin afirmar nueva revisión externa de toda su bibliografía.
- [x] Inspeccionar diferencias de ramas/commits y PR abiertos sin sobrescribir avances ni confundir investigación con integración. Evidencia: matriz inicial y API de PR; importación posterior selectiva, sin merge.
- [x] Auditar Models, migraciones, adaptadores, fuentes, estados, Policies, permisos, rutas y vistas; comprobar inventario instalado sin reset. Revisión estática independiente y prueba de inventario BDD en PostgreSQL desechable: 73 operativas + 33 expertas.
- [x] Ejecutar y registrar baseline pertinente en entorno de prueba; clasificar existente/verificado, incompleto, aislado, defectuoso y pendiente. SQLite: 782 pruebas / 5.496 aserciones PASS; matriz inicial enlazada.
- [x] Entregar matriz de brechas con criterios de aceptación y reutilización, antes de implementar. Véase auditoría de Fase 01.

### 1. Contratos de caso, hechos y conocimiento — A, B, C

- [x] Definir identidad de caso/evaluación, corte y manifiesto común de versiones. `EjecutarPerfilExperimental` y orquestador: contrato `PERFIL-EXPERIMENTAL-1`, identidades/corte comunes y versiones separadas Memoria/G09/DCL/puente.
- [x] Mapear explícitamente las fuentes permitidas. Captura instrumental G08 y adquisición común de ocho Models, con campos/tipos/roles explícitos. `AdquisicionPerfilExperimentalTest` recorre Models → captura → perfil → replay; el catálogo describe qué campos permanecen contextuales sin mapeo cognitivo.
- [x] Conservar los metadatos del hecho exigidos en §4 para el contrato de prueba: bruto/tipado, identidad, fecha/precisión/corte, autor o procedencia, estado, versión, rol, causas y relaciones. La captura rechazada sigue consultable; el archivo técnico conserva el conjunto original.
- [x] Validar caso, origen, autor, corte, oportunidad, tipos y compatibilidad. Rechaza fecha imposible/futura con valores, fuente sin fecha con valores, no finitos, tabla ajena y procedencia incoherente. No hay NLP ni normalidad por false sin observación.
- [x] Deduplicar por identidad y episodio. Captura idéntica no se recuenta; relectura incompatible falla. Relaciones diferencian atención/actividad/jornada y no acreditan independencia por compartir un registro.
- [x] Completar cinco dominios y 19 conceptos. `CatalogoPerfilCincoDominiosTest` valida 24 nodos, 19 relaciones, EJE-S03 y versiones. La red conceptual es inactiva; cuatro MEM sin contrato de tarea permanecen explícitamente sin regla/resultado específico.
- [x] Preservar bruto instrumental y contexto TEST_ONLY: versión, tarea, idioma/población/norma artificiales, aplicación y autor explícito o NULL. No se copian reactivos clínicos ni se afirma disponer de una licencia normativa.

### 2. Inferencia y cinco dominios — E

- [x] Reutilizar D-130 positivo conjuntivo y D-131 de memoria; preservar EV-CM-0/1/2 y MOD-CM-1. Invariantes originales preservadas; la validación COG-MEM se extrajo al dominio para evitar dependencia hacia el adaptador. Suites sintéticas y revisión independiente del refactor registradas.
- [x] Revisar/importar selectivamente el trabajo experimental G09: 3 + 5 + 5 + 2 reglas, sus pruebas y cotejo con el núcleo. Importación selectiva de 34 archivos del worktree HEAD `8085ec0e`, sin merge ni sustitución del núcleo.
- [x] Implementar compuertas específicas de los cuatro dominios G09, controles por tarea y resolución descriptiva; D-130 ejecuta la revisión declarativa seleccionada. No extiende consecuencias MEM por analogía.
- [x] Controlar confusores/contradicción mediante declaraciones explícitas por dominio/tarea. A01–A14, presencia triestado, efecto/fuente/justificación y advertencias conservados; NULL/invalidación excluyen. No deduce fatiga, idioma, escolaridad ni déficits sensoriales desde narrativa.
- [x] Diferenciar cumplida, no cumplida, no evaluada, evidencia inadmisible, contradicción y error de integridad sin inventar estados físicos. Proyecciones y trazas experimentales; NULL de condiciones se conserva.
- [x] Probar que cambiar un hecho/condición cambia o impide la salida; no emitir respuestas escritas de antemano. Pruebas G09 de cada condición y nueva regla declarativa sobre el evaluador real.
- [x] Mantener `EVALUABLE_SIN_SOPORTE` separado de función conservada y abstenerse cuando corresponda. Regresión de fuentes hasta resultado, sin convertirlo en A/P.

### 3. Gestión de reglas — D, prioridad del prompt

- [x] Auditar autorización real y representación persistente para crear, probar, versionar, publicar técnicamente, comparar, archivar y consultar. Sin facultad institucional de gestión HTTP; se mantiene cerrada. Gestor limitado a consola/testing/base desechable y actor técnico explícito.
- [x] Crear condiciones positivas AND usando variables/valores existentes, consecuencia válida, fundamento, restricciones y explicación. `EditorPaqueteExperimental`, validación del paquete y pruebas de nueva regla sin editar el evaluador; no añade nuevos predicados clínicos.
- [x] Ejecutar casos sintéticos y validar integridad antes de considerar cualquier habilitación. El paquete se valida al construir/guardar y se prueba con condiciones mutadas; ninguna ejecución de pruebas constituye publicación institucional.
- [x] Crear sucesora sin modificar reglas publicadas/usadas; mostrar diferencias e impacto en la misma batería de casos. `compararImpacto` verifica originales idénticos, cortes/criterios/versiones; pruebas positivas, negativas y NULL, también con PK regeneradas en BDD. La recuperación de evaluaciones históricas G09 permanece pendiente en la casilla de ciclo completo.
- [x] Retirar la anterior del conjunto elegible para nuevas ejecuciones con responsable, fecha y motivo durables y compatibles. Alcance técnico: paquete TEST- completo, fecha existente y Activitylog para actor/motivo, sin cambiar estado/contenido. No se declara archivado institucional individual de reglas.
- [x] Conservar consultas históricas exactas y seleccionar revisiones técnicas elegibles. `ejecutarConRevision` carga el paquete SQL bajo bloqueo; el archivo privado conserva conocimiento/entrada/salida. Una declaración libre con identidad SQL se rechaza; retiro no impide consulta/replay histórico.
- [x] Si falta capacidad estructural o autoridad, entregar RFC precisa y acción protegida, sin simular que el ciclo completo funciona. [RFC de conservación](RFC_CONSERVACION_PERFIL_EXPERIMENTAL_2026_10_18.md): brecha comprobada, alternativas, diccionario candidato acotado y aceptación futura; sin aprobación ni migración. El diccionario completo de las instantáneas por fuente sigue pendiente.
- [x] Demostrar el ciclo compatible completo: crear → probar/ejecutar → sucesora → retirar → recuperar/reproducir original intacto. `ArchivoPerfilExperimentalTest` usa nuevas reglas, PK/FK SQL reales, efecto calculado y rechazo de ejecución retirada incluso por declaración copiada. La evaluación histórica es evidencia técnica de §14; no se afirma conservarla en BDD institucional.

### 4. Longitudinalidad y Petersen — F

- [x] Comparar solo mismo caso y componentes equivalentes, con fechas, protocolos, versiones, oportunidades de observación y contextos compatibles. Comparador experimental integrado al contrato; sin ventana ni interpretación de magnitud inventadas.
- [x] Distinguir comparable, no disponible, no válida, cambio no corroborado y diferencia interpretable solo con contrato específico. Se conserva `diferencia_interpretada=null`, sin contrato clínico que autorice otra cosa.
- [x] Reutilizar clasificador y acreditar C01 cambio, C02 objetividad, C03 autonomía/IADL, C04 diferencial y C05 calidad mediante soportes sintéticos explícitos independientes. Puente y casos explícitos de laboratorio; no acreditación asistencial.
- [x] Conservar estados A/P/U/I/X; no convertir hallazgos, EV-2 o ausencia de reglas positivas en A/P. Perfiles descriptivos conservan U; referencias independientes alimentan el clasificador.
- [x] Demostrar PET-01 amnésico único, PET-02 amnésico multidominio, PET-03 no amnésico único y PET-04 no amnésico multidominio. Pruebas puras y recorrido SQLite aislado.
- [x] Demostrar abstención por insuficiencia, contradicción, diferencial y requisitos, además de rechazo por incoherencia. Suites Petersen y puente originales ejecutadas.
- [x] Mantener intactos los 30 casos y 32 configuraciones documentales de referencia. Se incorporan sin alterar sus resultados esperados.

### 5. Resultados, persistencia e integración — G, H

- [x] Entregar contrato estable versionado con todos los campos del §13: fuentes, admitidas/excluidas/faltantes, perfil/subcomponentes, reglas, soportes, contradicciones, comparaciones, DCL si procede, patrón/abstención y límites. `ConstructorResultadoExperimental` consolida el laboratorio, preserva bruto/tipo de fuentes excluidas, enumera 19 componentes y deja los cuatro MEM sin resultados específicos. Esto no cierra la cobertura de fuentes del §4 ni la conservación durable.
- [x] Generar narrativa exclusivamente desde resultados estructurados; permitir explicar tanto emisión como no emisión. Constructores G09/Petersen, trazas y salida técnica consumible por consola/Laravel; no un informe fijo por escenario.
- [ ] Conservar todo el perfil compuesto transaccionalmente en la BDD institucional. El contrato congelado no representa manifiesto/capturas completos; RFC protegida. **Alternativa compatible implementada según §14:** conocimiento SQL transaccional y archivo técnico privado cifrado con publicación atómica, huellas y replay. SQL/filesystem no comparten una transacción distribuida.
- [x] No reconstruir historia desde valores actuales. Consulta devuelve el documento original, replay exige motor idéntico. El archivo experimental autorizado no es JSON/EAV en BDD ni auditoría paralela; Activitylog conserva solo acciones/contexto mínimo.
- [x] Proteger las acciones y exportación técnica: actor fresco/Policy, consola/testing/destino desechable, identidades de caso, selección SQL elegible y almacenamiento privado. Cuenta revocada, rol incompatible, rutas manipuladas, otro caso y entorno incorrecto se rechazan.
- [x] Auditar gestión e historia con Activitylog: creación, lectura, prueba, comparación de impacto, retiro, intención/confirmación de archivo y consultas. No se amplían permisos. La intención previa permite recuperar un artefacto si falla la confirmación, sin simular éxito.
- [x] Consumir contratos desde Laravel sin lógica de reglas en presentación y mostrar la procedencia experimental exigida. Nuevo CLI `experto:probar-perfil`; Livewire institucional permanece fuera del laboratorio. Administración declarativa de prueba mediante otro CLI.
- [x] Probar que no escribe conclusiones en historia asistencial, alertas, cuidados, prescripciones ni tratamientos. Prueba CLI registra cero escrituras en la conexión institucional; SQLite del laboratorio es otra conexión en memoria.

### 6. Verificación y cierre — I, J, K, L, M

- [x] Cubrir los 24 grupos de prueba del §16: hechos, mapeos, fuentes, versiones, grafo, reglas/no emisión, evaluabilidad, confusores, contradicción, doble conteo, cinco dominios, PET-01–04/abstenciones, longitudinalidad, creación/sucesión/archivo, replay, seguridad, aislamiento asistencial, persistencia compatible, explicación y regresión. Suite global: 2.271 casos/45.078 aserciones, cero fallos y 23 omisiones previstas. Batería final posterior al cotejo: 431/9.955 PASS en SQLite y configuración PostgreSQL desechable; doce programas independientes PASS. Los conteos se solapan; matriz VT y límites exactos en auditoría.
- [x] Ejecutar pruebas unitarias y de integración reales: SQLite en memoria y PostgreSQL desechable; nunca producción ni reset de la base de desarrollo. Segundo incremento: regresión relacionada 820/5.666, integración e inventario PG 57/397; escenarios finales de consola 3/33 en ambos motores. Los conteos se solapan.
- [x] Registrar comandos, entorno, versión, cantidades, fallos y artefactos; no igualar pruebas técnicas con validación clínica. Véase auditoría y evidencia, sin sumar suites solapadas.
- [x] Verificar recorrido integral y pruebas de mutación de hechos/condiciones sobre casos sintéticos. Fuentes ficticias → perfil → Petersen/abstención → contrato y replay; NULL, contexto, confusor, contradicción, requisito IADL/diferencial y sucesora. La conservación durable es una aceptación separada que permanece abierta.
- [x] Ejecutar build y QA funcional/visual si cambia la interfaz; reportar cualquier verificación no realizada. No aplica build/QA visual nuevo en estos incrementos de motor/backend/consola: no se cambió la interfaz. No se presenta el trabajo visual previo del árbol como verificado por estas pruebas.
- [x] Entregar manual, diagrama, catálogos y matriz de trazabilidad integral; identificar deuda técnica y decisiones pendientes. Se añadieron aceptación A–M, 24 grupos, cotejo documental y matriz ejecutada VT01–15 de quince reglas.
- [x] Reportar qué existía, qué cambió, reglas administrables, salidas reales, reproducción, pruebas y pendientes. Manual, catálogo parcial de fuentes, auditoría y RFC enlazados; la fase integral continúa abierta.
- [x] Revisar cambios propios antes de cualquier commit; no hacer push, merge o despliegue por esta fase. Estado/diff, sintaxis y Pint revisados; cambios observacionales anteriores preservados. No se realizó operación de escritura Git.

## Cómo se marcará terminado

Una casilla de implementación pasa a completada solo con el recorrido y su evidencia. Las restricciones estructurales pueden limitar funciones concretas: se informa la limitación, sin declararlas implementadas. La aceptación global exige cumplir el §19 y, en particular, la gestión dinámica de reglas y preservación histórica; una clase, un informe estático o una suite aislada no bastan.

La adquisición común, controles por tarea, grafo, reglas G09 declarativas, cinco dominios, Petersen y ciclo histórico técnico están implementados y verificados en el alcance A–M. El acceso documental permitió corregir VT11 y completar negativos/explicación para quince reglas. La regresión global y la integración final terminaron sin fallos. La conservación institucional completa en BDD permanece separada; la alternativa experimental del §14 está implementada y probada. No se declara aprobación clínica ni ejecución sobre residentes reales.
