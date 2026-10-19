# Fase 01 — aceptación técnica y trazabilidad integral

Fecha: 18/10/2026. Alcance: **prototipo de ingeniería**, conforme al Prompt Maestro 01. La aceptación clínica queda fuera de esta entrega. La conservación del perfil compuesto usa la alternativa experimental expresamente permitida por §14; no se afirma que el perfil se guarde en las tablas institucionales.

Estado final: **A–M implementados y verificados en ese alcance compatible**. Regresión global de 2.271 casos sin fallos (23 omisiones previstas); batería final de 431/9.955 PASS en SQLite y configuración PostgreSQL desechable, doce programas independientes PASS y VT01–15 registrados para las quince reglas. Detalle y límites en [auditoría](FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md); los conteos se solapan.

## Recorrido implementado A–M

| Inciso | Entrega concreta | Prueba principal | Límite explícito |
|---|---|---|---|
| A | Reutilización del núcleo, integración de capturas y fuentes, resultado y consulta histórica | `ArchivoPerfilExperimentalTest::test_ciclo_completo_con_conocimiento_sql_nueva_regla_sucesora_retiro_e_historia_intacta` | SQL conserva conocimiento; el archivo privado conserva evidencia técnica, sin escribir historia asistencial |
| B | `RecolectorContextoPerfilExperimental` e `IncorporadorContextoExperimental`: ocho fuentes desde Models; captura instrumental G08 aparte | `AdquisicionPerfilExperimentalTest` y `PerfilExperimentalTest` | Contexto narrativo sin NLP ni equivalencia A/P; capturas instrumentales artificiales |
| C | `CatalogoPerfilCincoDominios`: cinco dominios, 19 conceptos, 24 nodos y 19 relaciones; paquete G09 de variables/reglas | `CatalogoPerfilCincoDominiosTest` | Red conceptual inactiva, distinta de los paquetes ejecutables; no inventa cuatro reglas MEM |
| D | Editor declarativo, creación SQL, sucesión, comparación, retiro y selección elegible bloqueada | `GestionConocimientoExperimentalTest`, `ReglasDeclarativasPerfilExperimentalTest`, ciclo integral | Gestión técnica con actor explícito en testing; publicación institucional cerrada |
| E | Memoria D-131 y quince tareas G09; controles A01–A14, confusores declarados y evaluación D-130 | `ControlesTareaG09ExperimentalTest`, suites G09 y perfil | Solo perfiles instrumentales TEST_ONLY; ninguna nueva escala, peso o baremo clínico |
| F | Referencias independientes, C01–C05, PET-01–04 y abstenciones | `PerfilExperimentalTest` y tres programas Petersen/puente | No deriva A/P desde hallazgos descriptivos ni BADL |
| G | `ConstructorResultadoExperimental` y constructores de informes: fuentes, condiciones, soportes y causas de no emisión | `ResultadoPerfilExperimentalTest`, reglas declarativas | Se conserva informe calculado; no se presenta un texto fijo como inferencia |
| H | Actions y tres comandos: probar perfil, gestionar conocimiento, archivar/consultar/reproducir | `ConsolaPerfilExperimentalTest`, pruebas CLI de gestión/archivo | Sin nuevas rutas HTTP, permisos clínicos o conexión con la pantalla operativa |
| I | Casos sintéticos completos/incompletos/contradictorios y reproducción exacta | Casos de consola, fixtures Petersen y archivo | Una entrada diferente bajo la misma identidad se rechaza |
| J | Unitarias, integración SQLite/PostgreSQL, seguridad, mutaciones y regresión | Reporte de auditoría con logs/JUnit | Los conteos solapados no se suman; CI remoto se distingue de ejecución local |
| K | Manual, catálogo de hechos, catálogo de reglas, arquitectura, cotejo de fuentes y límites | Documentos de Fase 01 enlazados en README | EXP-01/G09-EXP accesibles y cotejados; no se crean actas profesionales |
| L | Esta matriz y los 24 grupos siguientes | Archivo + método/clase + reporte de ejecución | Cada pendiente tiene ámbito, sin presentar cobertura clínica inexistente |
| M | RFC de conservación y decisiones reservadas | Sección de restricciones del manual y RFC | Sin cambios de tablas, columnas, catálogos o permisos en este incremento |

Las rutas de pruebas anteriores son relativas a `tests/Feature/` o `tests/Unit/`; los programas independientes están en `motor-experto/tests/`. Las clases del motor están bajo `motor-experto/src/motor_experto/`, las Actions/CLI/adaptadores bajo `app/Modulos/ApoyoDecision/`.

## Los 24 grupos de prueba del §16

| Nº | Requisito | Componente → prueba/evidencia ejecutable |
|---|---|---|
| 1 | Hechos | Base instrumental y adquisición contextual → `PerfilExperimentalTest`, `AdquisicionPerfilExperimentalTest` |
| 2 | Mapeos tipados | Extractor vigente e inspector → `MapeoFuentesTest`, `AdaptadorInstrumentosTest`, `PerfilExperimentalTest` |
| 3 | Fuentes inválidas | Lector/collector → identidad ajena, fecha inválida/futura/ausente, NULL, tipo no finito y origen no permitido |
| 4 | Versiones | Paquete/orquestador/gestor → `CatalogoPerfilCincoDominiosTest`, `GestionConocimientoExperimentalTest`, `PerfilExperimentalTest` |
| 5 | Grafo | `RedSemantica`/catálogo conceptual → `CatalogoPerfilCincoDominiosTest` y núcleo existente |
| 6 | Reglas positivas | D-130 y G09 → `g09_nucleo_semantico.php`, `ReglasDeclarativasPerfilExperimentalTest` |
| 7 | No emisión | Constructor/evaluador → `ResultadoPerfilExperimentalTest`; ausencia/NULL/compuerta/retirada en sucesora |
| 8 | Evaluabilidad | Compuertas MEM/G09 → `PerfilExperimentalTest`, `ControlesTareaG09ExperimentalTest` |
| 9 | Confusores | Controles por tarea → invalidante, advertencia, desconocido y dominio incompatible |
| 10 | Contradicciones | Resolvedores y puente → `PerfilExperimentalTest`, `ResultadoPerfilExperimentalTest`, programas G09/Petersen |
| 11 | Doble conteo | Identidad/aplicación/episodio → perfil y adquisición; `ContratoG09ExperimentalTest` VT11 para quince reglas: originales conservados y soporte efectivo agrupado |
| 12 | Cinco dominios | Orquestador → `orion_cinco_dominios_experimental.php`, pruebas de perfil/resultado |
| 13 | PET-01–04 | Clasificador y puente → `dcl_petersen_experimental.php`, `dcl_puente_integracion_cinco_dominios.php`, consola |
| 14 | Abstenciones | C01–C05/A/P/U/I/X → programas Petersen/puente y escenarios incompleto/contradictorio/diferencial/IADL |
| 15 | Longitudinalidad | Comparador → `ComparadorLongitudinalExperimentalTest`; exige contexto/protocolo/versiones/oportunidad compatibles |
| 16 | Crear reglas | Editor y gestor SQL → `EditorPaqueteExperimentalTest`, `GestionConocimientoExperimentalTest`, ciclo integral |
| 17 | Nueva versión | Editor/PK regeneradas/antecesora → comparación de impacto y regla G09 añadida por datos |
| 18 | Retiro e historia | Selector bloqueado + archivo → ciclo SQL; anterior retirada rechaza nuevas ejecuciones y conserva consulta/replay |
| 19 | Reproducibilidad | Snapshot/huella/copia de conocimiento → `ArchivoPerfilExperimentalTest`; motor distinto impide reinterpretación |
| 20 | Acceso no autorizado | Policy/destino/actor fresco → cuenta revocada, rol incompatible, caso ajeno, rutas manipuladas, entorno incorrecto |
| 21 | Aislamiento asistencial | CLI/Actions → cero evaluaciones/residentes nuevos; no invoca historia, alertas, cuidados o prescripción |
| 22 | Persistencia | Gestión SQL transaccional + archivo privado cifrado → rollback de conocimiento; duplicado/conflicto/integridad del artefacto |
| 23 | Explicación | Condiciones/soportes efectivos → nueva conjunción cambia consecuencia y narrativa; VT15 enlaza regla/condición/ID/captura/respuesta, metadatos y fecha; código duplicado se rechaza |
| 24 | Regresión | PHPUnit completo, batería VT de quince reglas y doce programas independientes → resultados exactos en el reporte de auditoría |

## Criterio de historia y archivo

El ciclo integral guarda dos paquetes G09 en las tablas normalizadas existentes, calcula el perfil con sus PK reales, retira el primero y consulta/reproduce su ejecución conservada. La nueva regla es una conjunción ficticia entre dos tareas; cambiar una condición impide su consecuencia. Se verifica que la regla retirada de la sucesora no aparezca como activa allí.

La entrada declarativa libre admite versiones ficticias; una identidad o código registrados en SQL exige selección mediante `ejecutarConRevision`. Copiar la declaración de una versión retirada a una nueva entrada libre se rechaza. Replay histórico utiliza la copia conservada, por lo que el retiro no invalida el pasado.

SQL y filesystem tienen fronteras de atomicidad distintas. El archivo usa exclusión por caso, publicación mediante renombrado, cifrado autenticado, huellas tipadas e idempotencia. Activitylog registra intención antes de publicar y confirmación después. Si la confirmación falla, la operación falla explícitamente; el intento permite localizar y consultar el artefacto, sin prometer rollback distribuido. No se usa Activitylog como almacén de resultados.

## Restricciones que impiden una aceptación más amplia

**Acceso documental resuelto:** EXP-01/G09-EXP completos y G06/G07/G08 consultados, G10–G13 cotejados con actas/matrices y contratos. El [registro de fuentes y VT](FASE01_COTEJO_DOCUMENTAL_Y_VT_2026_10_18.md) documenta autoridad, hashes y correcciones. Los errores 401 son antecedentes, no un bloqueo vigente.

1. El manifiesto compuesto y todas las instantáneas originales todavía no caben en el contrato institucional actual. El §14 autoriza el archivo experimental reproducible empleado aquí. La RFC mantiene separada la futura extensión de BDD; no se solicitan 16 tablas ni se aplican cambios parciales.
2. Licencias, normas, interpretaciones instrumentales, reglas clínicas y responsables de publicación no se aprueban por pasar estas pruebas. Las cuatro tareas específicas de Memoria permanecen sin contrato instrumental ejecutable.
3. Bloqueos PostgreSQL y exclusión filesystem están implementados; una prueba de carrera multiproceso no se deduce de pruebas secuenciales. El reporte especifica la cobertura realmente ejecutada.

La aceptación técnica de los recorridos disponibles no cierra estos puntos ni habilita una valoración asistencial.
