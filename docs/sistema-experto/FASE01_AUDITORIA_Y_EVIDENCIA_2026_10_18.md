# Sistema experto — auditoría y evidencia de Fase 01

Estado: implementación incremental en curso, autorizada por la propietaria el 18/10/2026: «OKEY EMPIEZA Y VE MARCANDO LOS CHECKLIST». Seguimiento en [checklist maestro](PROMPT_MAESTRO_01_CHECKLIST_2026_10_18.md).

## Referencia antes de cambios de esta fase

Checkout: `feature/apoyo-decision-informe-neurocognitivo`, HEAD `b8ee4b77`. Los cambios observacionales previos permanecen en el árbol y se preservan. No se cambia de rama, fusiona, hace commit o push como efecto de esta auditoría.

Comando: `php vendor/phpunit/phpunit/phpunit --filter 'SistemaExperto|ObservacionesExpertasTest|HallazgosObservacionalesTest|RolesBaselineCongeladoTest|PresentacionG09AisladaTest'` con JUnit y log en TEMP `sistema-experto-fase01-baseline.sqlite.*`.

Resultado inicial: **782 pruebas / 5.496 aserciones PASS**, SQLite en memoria, PHP 8.3.33, PHPUnit 12.5.36. El alcance es la suite relacionada, no toda la aplicación ni validación clínica. Laravel instalado 13.33.0; autoload `MotorExperto` ya existe.

PR abiertos consultados mediante API pública de GitHub el 18/10/2026: #15 contiene G09 experimental; #13 contratos de los cuatro dominios; #12 laboratorio de candidatos; #11 endurecimiento de soportes; #16 IADL y #17 observaciones básicas. Se revisa su alcance sin fusionarlos ni adjuntarlos como artefactos de trabajo de este chat. Una referencia local o página cacheada no sustituye el estado de esa consulta remota.

## Fuentes recuperadas y autoridad

Exportaciones de fuentes oficiales del proyecto consultadas el 18/10/2026, conservadas temporalmente para lectura; los hashes son del texto UTF-8 recibido:

| Fuente | SHA-256 | Estado y uso |
|---|---|---|
| Documento Maestro, ID `1d68S-EZTyYVKsYm-cOtaktYSNUBfDAi59FHexkEd88I` | `EF9F3A9852EAD41519EB5A32E1D7F92A91C280F863340AC0DEFEDA4898D0BCFF` | Rector para D-123–D-144; los conteos históricos se concilian con extensiones aprobadas actuales. |
| DCL-03, ID `108-74YgGmw3JmA3Lba_bmwMcDeR39oVKIkamtyqJLgw` | `BCDCDD38F68A99D1B5296DC860B690980B7C053EF161DF63615B7C031E39A7D7` | Contrato experimental de clasificación; no aprobación asistencial. |
| DCL-04, ID `1wZqJRreRVbQld2WakWssWhehq8ZZshgEQg7RYHg78Pk` | `5A0BD370E160690116DB419DB5A52E7FD5B483AC457321324F86CAC022294D0C` | Explicación y límites de presentación. |
| Matriz G09, ID `1uWfx3CAbcJNQj6isFYmaFdOvK-RMJYgyYIIMrahMZcc` | `45A00D1055D2665708F178C6EDEE85F7E73FC4ABA874AAAF15B74C2E11C94A92` | Quince candidatos adicionales; no convertirlos en interpretación clínica. |
| G12-DCL, ID `1_o4E-r0wlKjvrFzkBfxCXSCNI7yff5cobEovZSbuyTY` | `BD832DA90A364F7B5CC801AE364DBC0002D3193DEF0D418AA9E57B11DA9332D7` | Plan de validación; no se declara realizado por pruebas técnicas. |

Las exportaciones EXP-01 y G09-EXP inicialmente fallaron por HTTP; se continúa recuperando fuentes y se conserva esa limitación hasta verificar acceso. No se marca consultado el conjunto completo G06–G13 por disponer de extractos.

BDD: baseline actual y decisiones de conservación/IADL/revisión profesional prevalecen sobre conteos antiguos del Maestro. Inventario esperado **106 tablas de negocio: 73 operativas + 33 expertas**; no se cambia estructura en esta fase sin decisión expresa.

## Matriz inicial de brechas y decisiones de reutilización

| Requisito | Existente y evidencia | Brecha / incremento previsto |
|---|---|---|
| Memoria D-130/D-131 | Núcleo y pruebas del checkout; todos los archivos del núcleo fuera de `investigacion` coinciden por SHA-256 con el worktree G09 inspeccionado | Preservar núcleo y contratos; no sustituirlos durante importación. |
| G09 y cinco dominios | Catorce clases de `investigacion/g09` y `investigacion/dcl`, pruebas y puente independiente A/P en worktree `pr15-ci-utf8/RememberMind` | Incorporación selectiva de archivos nuevos, sin merge ni sobrescritura. Verificar y corregir límites del recorrido integrado. |
| Datos incompletos y contradicciones | Evaluador directo experimental; D-130 existencial positivo | Probar NULL, datos mal tipados, confusores, duplicados y contradicción a través del ejecutor, sin traducir desconocido a normalidad. |
| Diecinueve subcomponentes | Quince G09; cuatro MEM documentales; Memoria integrado D-131 con literal técnico | Conservar cobertura conceptual y declarar cuáles tienen tarea ejecutable. No inventar reglas para MEM por analogía. |
| Petersen | Dos implementaciones existentes y oráculos 30 casos/32 configuraciones | Reutilizar puente y contrastar equivalencia; conservar soportes independientes C01–C05/A/P, nunca deducidos de G09. |
| Gestión de conocimiento | Tablas normalizadas, validadores, inmutabilidad y consulta administrativa | Falta flujo autorizado completo. Verificar capacidad de creación/sucesión/retiro sin nuevos estados o permisos; aislar RFC si requiere autor/motivo de retiro no representados. |
| Integración Laravel | Lectura, DTOs, Policy y persistencia técnica existentes | Entrada aislada y reproducible del perfil integral; no conectar un PDO institucional al lector que exige SQLite en memoria. |
| Conservación | V2 guarda memoria/cognición/DCL estructurados; D-137 guarda trazas | Comprobar representación del perfil G09 y todos sus metadatos, replay y rollback. No guardar un informe serializado en JSON como atajo. |
| Seguridad | Lectura contextual y base técnica desechable | No reutilizar permiso de lectura como autorización genérica para administrar reglas; no ampliar competencias profesionales. |
| Entrega | Checklist y fuente preservada | Marcar solo capacidades verificadas; documentar RFC y límites sin declarar aceptación integral prematura. |

Esta matriz se entrega antes de la implementación nueva. Las verificaciones y avances siguientes se añaden por incremento, con alcance y evidencia diferenciados.

## Fuentes adicionales cotejadas

Las propuestas particulares contienen A01–A14 como controles previos, no como condiciones positivas nuevas de D-130. El Documento 16 mantiene G06/G07/G08/G09 pendientes, G10 pendiente en detalle, G11 parcial, G12 pendiente y G13 sin firma; EXP-01 permite experimentar y no cambia esos estados.

| Fuente pública recuperada | SHA-256 del texto recibido |
|---|---|
| [Documento 16 y adenda](https://docs.google.com/document/d/1uoa0YoAGNUCLpAeLXMtRTqGJXAuOQIupjcwqk4Xnjrs/edit) | `6EB6BF3A7F4D19DD8E12F24644C2810B03A018F67D788B21D1BA4B9E77B9429E` |
| [Fase A, PD-FA-1.0](https://docs.google.com/document/d/1eZI8f9GH88EYi3fDnfuiDNT6F-kvVRNnZPo3T9Blck8/edit) | `8C3E506B6460094C3841BCCD6EAA09068786F2FC3C7ACC12354205A29C3E87BA` |
| [G09 Atención](https://docs.google.com/document/d/10W7VU8-O_jEd_xRkIbjsrVCOGwZrw-i2Tu0QVGwgxxM/edit) | `DAFA7C762FA88DE3B5E42BC6E58B2EF1FBCAB077E266DE76A74418FB7EB18547` |
| [G09 Ejecutiva](https://docs.google.com/document/d/1UR6kAe0N_CpVCeyYPHI45pewLS8TdH9a-Yvts3wwBiY/edit) | `EB39D92D7E27FE14DF5C1A61F8C2A2289618D085B1141C776C174866CFE377A3` |
| [G09 Lenguaje](https://docs.google.com/document/d/1b-vX0b6MDgrLjl3kFBtoZRF7enIwtbTDuFtZ2J6I4_A/edit) | `9F34C9F0D84C13D47DA78ADC4FA8275A0EBFE09FBFB25E341DFD796168BBE216` |
| [G09 Visuoespacial](https://docs.google.com/document/d/1NBj4amlMW9lf3libwFXJPDIVLKBXX4kNR1KroVjHvsg/edit) | `A645C52737577C5ACE59457D798148B3A1AF8CCD45BFB22A89E9E26A4D72C7B8` |

El Anexo A se recuperó como XLSX; sus hojas de conceptos y decisiones se cotejaron. La extracción de hoja 5 con D-123–D-144 tiene SHA-256 `A3A9FAFDB581F2389D3BCB8474D344676E7FF3705D511C2613EEDA5ED467A145`. Los 19 conceptos son MEM 4, ATE 3, EJE 5, LEN 5 y VIS 2; no 19 criterios independientes. Las adendas accesibles no se presentan como lectura íntegra de los dos documentos cuyo acceso falló.

## Incremento implementado

Se incorporaron selectivamente 34 archivos nuevos de investigación, programas, fixtures y documentación desde el worktree con HEAD `8085ec0ef310de26a80a6cb6b2e3b36515283f05`. Se añadieron después tres documentos G08 y su programa de captura/muestras para reparar referencias incompletas: 38 archivos importados en total, además del código nuevo de integración. No se fusionó su rama ni sustituyó el núcleo. Se adaptó estilo PHP y se corrigieron barreras de tipo, calendario, contexto y cotejo.

- Entrada independiente `EjecutarPerfilExperimental`: capturas sintéticas en otra SQLite en memoria, cinco dominios, Petersen con referencias independientes, comparaciones longitudinales opcionales y replay de entrada.
- G09 conserva condiciones NULL, evidencia original examinada/admitida, fechas y episodios. Propaga confusores y contradicción por dominio; rechaza contextos desconocidos o NULL estructural y procedencias mal tipadas.
- El cotejo D-130 recibe identidad/corte de la evaluación. Cuando existe contradicción documentada se conserva la abstención, sin exigir una falsa coincidencia de conclusiones.
- `EditorPaqueteExperimental`: nueva regla declarativa, sucesora y retiro de reglas solo en la nueva revisión. Comparación mediante tuplas semánticas estructuradas, incluyendo dominio, tipo y nombre, sin colisiones por delimitadores.
- `GestionarConocimientoExperimental`: almacenamiento normalizado de revisiones en las tablas existentes, PK/FK regeneradas, transacción, duplicado sin sobrescritura, retiro técnico por paquete y auditoría. Cuenta/Policy/destino se revalidan; la prueba elegible mantiene bloqueo hasta terminar el cálculo.
- Dos comandos registrados: laboratorio local sin datos institucionales y administración declarativa cerrada a consola/testing/base desechable. No hay ruta HTTP, concesión de permisos ni migración nueva de esta fase.

Manual, diagrama, comandos y matriz por requisito en [manual y trazabilidad](FASE01_MANUAL_Y_TRAZABILIDAD_2026_10_18.md). Las brechas de conservación integral G09, gobierno institucional y cobertura total permanecen explícitas allí y abiertas en el checklist.

## Evidencia de ejecución

| Comprobación | Resultado |
|---|---|
| Baseline relacionada SQLite | 782 pruebas / 5.496 aserciones PASS |
| Regresión relacionada tras integración | **808 pruebas / 5.590 aserciones PASS**, SQLite en memoria |
| Integración de incremento + inventario BDD | **45 pruebas / 321 aserciones PASS**, PostgreSQL desechable |
| Correcciones del primer gate (contextos NULL y comparación estructurada) | 25 pruebas / 86 aserciones PASS en SQLite; 28 / 97 en PostgreSQL y cierre adicional de contexto Memoria 29 / 100 en SQLite |
| Ocho programas independientes G09/cinco dominios/Petersen | PASS, incluidos oráculos 30 casos + 32 configuraciones intactos |
| Independencia original del motor | Cinco casos técnicos PASS sin Laravel/Eloquent |
| Comando local `experto:probar-perfil --escenario=memoria-atencion` | Cinco dominios y PET-02 calculados; sin persistencia ni lectura del expediente |
| CLI gestor | Prueba real crea, compara y retira declaración; fuera de testing rechaza la operación |

Logs/JUnit: TEMP `sistema-experto-fase01-baseline.sqlite.*`, `sistema-experto-fase01-final.sqlite.*`, `sistema-experto-fase01-final.pg.*`, `sistema-experto-fase01-gates.sqlite.log` y `sistema-experto-fase01-gates.pg.*`. Los conteos se solapan; no sumarlos como casos independientes. Los programas integrados también invocan verificaciones auxiliares ya contabilizadas.

La revisión independiente detectó y permitió corregir: elegibilidad comprobada fuera de la transacción, comparación sin tipo de regla, códigos concatenados ambiguamente, contexto de dominio ignorado/NULL heredado y calendario/procedencia insuficientemente tipados. Las correcciones tienen regresión específica. Revisión estática y pruebas de PostgreSQL no equivalen a una prueba de carrera multiproceso ni a validación clínica.

Build/QA visual: este incremento no modifica CSS, JavaScript, Blade ni navegación de pantallas; no se declara QA visual ni un build nuevo por cambios de backend/consola. Se añadió workflow de programas independientes; sus comandos se ejecutaron localmente, la ejecución remota de CI queda pendiente de publicación. No se hizo commit, push, despliegue ni se ejecutaron migraciones o valoraciones en la base de desarrollo durante esta fase.

## Segundo incremento — contrato, fuentes y comparación de revisiones

- `ConstructorResultadoExperimental` consolida todos los campos de salida del laboratorio: identidad, manifiesto, fuentes originales, bruto/tipo incluso rechazados, hechos instrumentales, admitidas/excluidas/faltantes, reglas/condiciones/soportes, confusores, contradicciones, perfil, 19 componentes, requisitos y patrón/abstención, resumen y traza. No inventa cuatro resultados MEM específicos ni autoría clínica.
- Memoria recibe su contexto CTX-MEM-01 explícito; NULL produce la compuerta original correspondiente. El nuevo caso de uso conserva el confusor desconocido cuando se omite; la compatibilidad favorable del ejecutor antiguo no se hereda en esta entrada.
- Las colecciones de capturas NULL/mal tipadas se rechazan; los valores NULL de las respuestas se conservan. Una fuente no admitida sigue disponible en la salida con su motivo y bruto original.
- `compararImpacto` ejecuta los mismos casos positivos, negativos y desconocidos sobre dos revisiones. Verifica originales equivalentes, incluso con PK regeneradas; rechaza casos modificados. La entrada Laravel bloquea ambas revisiones y revalida elegibilidad/actor en la transacción.
- El CLI añade casos incompletos, contradictorios, diferencial pendiente e IADL insuficiente. La exportación estructurada incluye la procedencia y calcula la salida; el modo texto muestra EV-CM-1 cuando no corresponde un resultado de Memoria.
- Catálogo parcial de fuentes y RFC de conservación concretan lo integrado, lo reutilizable y lo pendiente. No modifican el esquema ni autorizan clínica.

| Verificación del segundo incremento | Resultado / artefacto |
|---|---|
| Regresión relacionada SQLite en memoria | **820 pruebas / 5.666 aserciones PASS**; TEMP `sistema-experto-fase01-incremento2.sqlite.{log,xml}` |
| Integración y baseline BDD PostgreSQL desechable | **57 pruebas / 397 aserciones PASS**; TEMP `sistema-experto-fase01-incremento2.pg.{log,xml}`; servidor parado y cluster propio retirado por el runner |
| Escenarios finales de consola, después de la regresión anterior | **3 pruebas / 33 aserciones PASS** en SQLite y PostgreSQL; TEMP `sistema-experto-fase01-consola.pg.{log,xml}` |
| Diez comandos independientes | Todos PASS, incluidos 157 controles de captura G08/12 protocolos sintéticos; oráculos Petersen 30/32 intactos; independencia original del motor |
| Sintaxis de los archivos incorporados del motor | 25 archivos PHP PASS |
| Estilo | Pint dirigido PASS |
| Comando local `experto:probar-perfil --escenario=incompleto` | EV-CM-1 y abstención por información insuficiente; cero valoración guardada |

El filtro SQLite amplía la regresión inicial con `PerfilExperimentalTest`, `ResultadoPerfilExperimentalTest`, `EditorPaqueteExperimentalTest`, `ComparadorLongitudinalExperimentalTest`, `GestionConocimientoExperimentalTest` y `ConsolaPerfilExperimentalTest`. PostgreSQL usa esas seis clases más `BddOperativaV2Test`, mediante `scripts/probar-postgres.ps1`. La consola se volvió a verificar después de añadir sus cuatro escenarios; los conteos se solapan y no deben sumarse.

El checklist continúa abierto para adquisición común de todas las fuentes, controles A01–A14 completos, grafo íntegro, conservación durable y ciclo de vida integral con evaluaciones históricas. Esta evidencia verifica ingeniería sobre datos ficticios; no equivale a aprobación clínica, activación institucional ni aceptación completa de la Fase 01.

## Cierre del gate de comparación de impacto

La revisión independiente del segundo incremento detectó un P2: era posible suministrar una evidencia con bruto idéntico pero valor semántico/admisibilidad manipulados y atribuir su cambio de salida a una revisión del conocimiento. Se reprodujo antes del arreglo con `test_comparacion_rechaza_evidencia_manipulada_aunque_el_bruto_original_sea_identico`.

`fuentesComparables` ahora rederiva cada evidencia desde fuente y mapeo de su revisión. Para COG-MEM aplica también las restricciones del adaptador específico; para otros criterios utiliza el mapeador exacto. Cualquier diferencia frente a la evidencia recibida se rechaza antes de evaluar. La memoria del llamador no se modifica.

Revisión estática independiente posterior: **PASS acotado, P2 cerrado, sin nuevos P1/P2 localizados**. Pruebas Editor + Gestor después del arreglo: **14 / 50 PASS tanto en SQLite como en PostgreSQL desechable**, JUnit TEMP `sistema-experto-fase01-impacto-gate.sqlite.xml` y `sistema-experto-fase01-impacto-gate.pg.xml`; servidor de prueba detenido correctamente. Estas verificaciones son posteriores a la regresión de 820; no se presentan como un nuevo total de aquella ejecución.

Verificación documental: enlaces locales de 13 documentos del incremento válidos; fuente maestra conservada; 37 casillas verificadas y 17 abiertas. Pint y `git diff --check` pasan, con avisos CRLF de documentos de BDD preexistentes; no se reescriben por esos avisos. Sin legacy V1 ni TODO/FIXME/debug en el código nuevo revisado. No hay commit de esta fase en curso.

## Incremento integral — adquisición, conocimiento declarativo e historia técnica

Este corte sustituye los pendientes de adquisición común, grafo, controles por tarea y ciclo histórico **técnico** de los dos incrementos anteriores. La conservación **institucional en BDD** y el cotejo íntegro de dos fuentes maestras continúan separados.

- Adquisición por lotes de ocho Models en base de pruebas, preservando bruto/tipado, fechas originales con precisión, corte, autor histórico identificable, estado y episodios. Contexto psicológico/médico sin NLP ni mapeo cognitivo inventado. Se excluyen valores futuros/sin fecha y se rechazan fechas inválidas, no finitos, contratos y casos ajenos.
- Red conceptual inactiva de cinco dominios y 19 subcomponentes, validada por `RedSemantica`. No fusiona versiones ejecutables ni inventa cuatro reglas instrumentales MEM.
- A01–A14 y confusores por tarea como declaraciones sintéticas explícitas; NULL/false excluyen con su causa, advertencias conservan modificador. La estructura del inspector/lector continúa validándose independientemente.
- Reglas G09 declarativas evaluadas por D-130. Nueva conjunción entre dos tareas cambia el perfil; mutar una condición impide el efecto. Se conservan condiciones, soportes y fundamento de la revisión seleccionada, diferenciados de los predicados extraídos por el catálogo fuente.
- Paquetes G09 guardados en las tablas normalizadas actuales, con PK/FK regeneradas y metadatos técnicos declarados. Selección SQL, actor y elegibilidad bloqueados durante el cálculo; retiro impide nuevas ejecuciones.
- Archivo privado local cifrado conforme al Prompt§14: entradas, conocimiento, salidas, huellas y autor técnico. Consulta original, historial y replay exactos; identidad idempotente y rechazo de sobrescritura/manipulación. Cambiar un control de prueba después del guardado no cambia la fuente histórica conservada.
- CLI de archivo/consulta/replay conectado y lectura declarativa local acotada. No nuevas rutas HTTP, permisos, migraciones ni efectos asistenciales.

### Correcciones detectadas por revisión y regresión

La revisión independiente encontró dos P2 y comprobó posteriormente sus arreglos por lectura/diff:

1. Dos reglas con igual código efectivo podían conservar dos consecuencias pero una sola ruta explicativa. `ValidadorPaqueteConocimiento` ahora rechaza duplicados, incluido el fallback por PK; hay regresión específica.
2. Copiar la declaración de una revisión SQL retirada a una entrada libre eludía la selección elegible. La entrada libre ahora rechaza identidades/códigos registrados o reservados; exige `ejecutarConRevision`. Consulta y replay históricos siguen disponibles. El ciclo integral prueba ambos intentos de ejecución retirada y el historial intacto.

Dictamen posterior: **PASS técnico acotado**, sin hallazgos nuevos en el ámbito revisado. El revisor ejecutó inicialmente 4/16 pruebas de reglas declarativas y 4/6 negativos del archivo en SQLite; los arreglos finales y el refactor fueron revisados por lectura/diff. Esas cifras no representan el total final ni una validación clínica.

La primera regresión integral detectó fronteras incorrectas entre capas y comprobaciones desactualizadas. Se detuvo expresamente después de 1.236 casos para corregirlas; no se presenta como una suite terminada o aprobada. Cambios:

- Invariantes COG-MEM extraídas de `AdaptadorHechosCOGMEM` a `AdmisibilidadFuenteCOGMEM`; el adaptador sigue mapear → validar → incorporar. El editor utiliza las mismas invariantes dentro del dominio.
- Proyección SQLite descartable de laboratorio ubicada en `investigacion/g09`; el caso de uso no depende del directorio de adaptadores.
- Application utiliza `ConocimientoVersionado` existente y puertos de archivo/observación; bindings en el proveedor modular. Las consultas relacionadas reutilizan los puertos existentes de actor/enlaces y el resolver de paginación, sin cambiar permisos ni URL.
- Fixture de rutas incorpora únicamente la consulta de Psicología aprobada para revisiones separadas: 301 rutas con contrato ordenado exacto. La prueba de enlaces comprueba cierres huérfanos, anidamiento y falta de cierre; permite los enlaces válidos a originales.

Los hashes de los primeros cortes conservan valor como evidencia histórica. En este incremento se modificaron tres archivos originales del motor: adaptador COG-MEM, exportación declarativa de `PaqueteConocimiento` y unicidad efectiva de reglas en el validador. No se afirma que sigan idénticos byte a byte. La revisión independiente confirmó preservación de las invariantes COG-MEM; D-130/D-131 se verifican por regresión.

### Evidencia del cierre, anterior a la suite integral final

| Verificación | Resultado |
|---|---|
| Batería dirigida SQLite | **123 pruebas / 544 aserciones PASS**, TEMP `sistema-experto-fase01-cierre-final.sqlite.{log,xml}`; después se añadió la comprobación de fuente modificada, verificada en PostgreSQL |
| Integración, arquitectura, inventario y regresión relacionada PostgreSQL desechable | **188 pruebas / 6.216 aserciones PASS**, TEMP `sistema-experto-fase01-cierre-final.pg.{log,xml}`; cluster detenido y retirado por el runner |
| Arquitectura SQLite tras las correcciones | **23 pruebas / 5.396 aserciones PASS**, TEMP `sistema-experto-fase01-arquitectura.sqlite.xml` |
| Diez programas independientes después del refactor | Todos PASS; TEMP `sistema-experto-fase01-cierre.standalone.log` |
| Sintaxis/estilo | 26 archivos de investigación sin errores de sintaxis; Pint dirigido PASS |
| Documentación | 9 documentos actuales, 35 enlaces locales válidos; SHA-256 del Prompt Maestro igual al adjunto original |

Los conjuntos se solapan; no sumar los conteos. La suite integral final está ejecutándose con código estable y tendrá su resultado registrado en un corte separado. El pipeline existente incluye las cuatro clases Feature nuevas en PostgreSQL y declara `pdo_sqlite` también en ese job; el workflow independiente ejecuta los diez programas. CI remoto todavía no ejecutado por esta entrega.

Build y QA visual nuevos no aplican: no se modifican assets ni vistas en este incremento; la corrección del test estático no acredita apariencia visual. Los cambios visuales/observacionales anteriores del árbol siguen distinguidos de este trabajo. No se ejecutaron migraciones, seeders ni inferencias en desarrollo/producción. Sin operaciones de escritura Git durante este corte.

### Acceso documental

El 18/10/2026 se reintentaron las exportaciones completas de EXP-01 y G09-EXP; ambas devolvieron **HTTP 401**. La propietaria aclaró que compartirá esos documentos de Google Docs, no que solicite permisos nuevos para Gerencia. El cotejo sigue abierto hasta recibir su contenido accesible. Los anexos accesibles permiten el laboratorio aislado y mantienen los contratos congelados y las autorizaciones clínicas pendientes.

La propietaria autorizó posteriormente revisar y habilitar el acceso a esos dos documentos, continuar los ajustes técnicos necesarios y registrar sus motivos. Se abrió EXP-01 en el navegador disponible: Google redirige a **«Inicia sesión»**. No hay sesión de una cuenta administradora de los documentos, por lo que no se pudieron inspeccionar ni modificar sus ACL. Se dejó esa pestaña para que la propietaria autentique su cuenta; no se solicitaron credenciales en el chat, no se hicieron públicos otros archivos y no se cambiaron permisos de RememberMind por esta instrucción.

## Resolución del acceso y correcciones del cotejo completo

La propietaria confirmó «listo» después de autenticar Google. Se recuperaron y leyeron EXP-01/G09-EXP completos mediante exportación del documento autenticado; también se consultaron G06/G07/G08 DOCX y las matrices pertinentes de G10–G13. **El acceso dejó de ser un bloqueo; no fue necesario cambiar ACL.** Hashes, resolución de autoridad y alcance exacto en el [cotejo documental y VT](FASE01_COTEJO_DOCUMENTAL_Y_VT_2026_10_18.md).

El cotejo detectó un P2 adicional en VT11: el ejecutor declarativo reunía dos fuentes de una misma tarea/episodio como dos soportes efectivos. Se reprodujo con el evaluador real y dos IDs originales: el catálogo emitía un soporte y la declaración dos. El arreglo conserva ambos originales y todas las rutas de condiciones, añade grupos por episodio/tarea y consolida representantes estables tanto por regla como por consecuencia. Quince regresiones prueban corroboración y episodios distintos.

Se completaron negativos de las quince reglas, todos los A01–A14 false/NULL por tarea, cada C1/C2/C3 false/NULL/ausente, identidad/versiones/cortes ajenos, invalidez localizada, contradicción, dos procesos y cadena hasta respuesta. El informe incluye ahora instrumento/tarea, edición del perfil y original, norma artificial, idioma/población, perfil, fuente/fecha, respuestas, condiciones y advertencias. No interpreta nuevas fuentes ni introduce reglas clínicas.

Se recuperaron selectivamente los dos programas G08 que faltaban (`g08_auditoria_instrumental.php`, `g08_anexos_bolivia.php`); el total importado desde el worktree de investigación pasa de 38 a **40 archivos**, con ajustes de estilo. El workflow independiente ejecuta doce programas. No hubo merge de la rama de investigación.

| Evidencia posterior al cotejo | Resultado |
|---|---|
| Contrato VT01–15 por quince reglas | **315 pruebas / 4.101 aserciones PASS**; matriz CSV de 225 cruces con IDs, fixtures, esperado/observado y versión |
| Contrato VT + controles + resultados + reglas declarativas | **357 / 4.293 PASS**, TEMP `sistema-experto-fase01-vt-g09.sqlite.{log,xml}` |
| Doce programas independientes | Todos PASS; TEMP `sistema-experto-fase01-fuentes-cierre.standalone.log`; incluye 125 controles de auditoría G08 y 125 de localizadores, además de oráculos Petersen intactos |
| Estilo del delta | Pint dirigido PASS |

La revisión independiente previa se conserva con su alcance. El nuevo intento de cotejo por agente revisor no pudo completarse por cuota de ejecución; no se presenta como una revisión independiente realizada del delta VT11/explicación. Se efectuó revisión propia de datos/rutas, pruebas parametrizadas y reejecución de los programas. La integración afectada se repite en SQLite y PostgreSQL después de la suite integral, para evitar interferencias entre los discos falsos de pruebas Laravel.

La suite integral comenzó antes de incorporar la clase VT de 315 casos; no contiene esos casos nuevos en su descubrimiento inicial. Su resultado y la batería dirigida posterior se reportan por separado, sin sumar cantidades solapadas ni afirmar que un único run congelado cubrió todos los cambios.

### Regresión integral terminada

Comando: `php vendor/phpunit/phpunit/phpunit --log-junit <TEMP/sistema-experto-fase01-integral-final.sqlite.xml>`, configuración `phpunit.xml`, SQLite `:memory:`. Resultado: **2.271 casos, 45.078 aserciones, cero errores/fallos y 23 omitidos**. Duración: 30:09.621. Log/JUnit: TEMP `sistema-experto-fase01-integral-final.sqlite.{log,xml}`.

Las omisiones corresponden a diez pruebas específicas de PostgreSQL y trece pruebas de características Jetstream deshabilitadas (registro público, API, eliminación de cuenta, verificación de email y 2FA). No se habilitan esas características para hacer correr sus tests. La ejecución PostgreSQL dirigida de esta fase no se presenta como ejecución de toda la suite ni de esas diez pruebas ajenas al delta.

El run integral se inició antes de añadir los 315 casos VT. La batería dirigida posterior verifica el código final afectado; ambos cortes se conservan, sin totalizar pruebas duplicadas.

### Código final afectado: SQLite y PostgreSQL

Filtro ejecutado:

```text
PerfilExperimentalTest|ResultadoPerfilExperimentalTest|ContratoG09ExperimentalTest|ControlesTareaG09ExperimentalTest|CatalogoPerfilCincoDominiosTest|ComparadorLongitudinalExperimentalTest|EditorPaqueteExperimentalTest|GestionConocimientoExperimentalTest|ConsolaPerfilExperimentalTest|ArchivoPerfilExperimentalTest|AdquisicionPerfilExperimentalTest|ReglasDeclarativasPerfilExperimentalTest|Architecture
```

Comandos: `php vendor/bin/phpunit --filter '<filtro>' --log-junit '<destino>'` y `./scripts/probar-postgres.ps1 -Filtro '<filtro>' -Informe '<destino>' -Log '<destino>'`.

| Verificación final | Resultado / evidencia |
|---|---|
| SQLite `:memory:` después del cotejo | **431 pruebas / 9.955 aserciones PASS**, cero omisiones; TEMP `sistema-experto-fase01-cotejo-final.sqlite.{log,xml}`; 00:42.065 |
| Configuración PostgreSQL desechable después del cotejo | **431 / 9.955 PASS**, cero omisiones; TEMP `sistema-experto-fase01-cotejo-final.pg.{log,xml}`; 01:06.542; servidor propio detenido y cluster retirado por el runner |
| Distribución de esa batería | 384 Unit, 23 Architecture y **24 Feature de integración**. Las Feature recorren SQL/Models/acciones/archivo/CLI; las Unit del motor siguen utilizando su SQLite aislada aun dentro del run PostgreSQL. No son 431 pruebas de SQL PostgreSQL. |
| Recorrido final de consola | `php artisan experto:probar-perfil --escenario=memoria-atencion --json`: PET-02, 19 subcomponentes, 15 capturas G09, advertencia experimental y `persistido=false`; salida TEMP `sistema-experto-fase01-perfil-verificado.json` |
| Integridad de referencias | Dos fixtures Petersen idénticos al origen; Prompt Maestro idéntico al adjunto; matriz VT conserva huellas de la prueba y del manifiesto de 53 archivos PHP del motor |
| Calidad estática | Sintaxis de 65 PHP del motor PASS; Pint dirigido PASS; `git diff --check` PASS con avisos CRLF preexistentes; sin legacy V1, TODO/FIXME/debug ni catch vacío en el ámbito propio revisado |
| Documentos de cierre | Nueve documentos actuales comprobados, incluido README; 44 enlaces locales válidos; matriz CSV de 225 cruces y huellas de motor/prueba vigentes |

**Cierre técnico A–M:** 53 casillas verificadas y una reservada para conservación institucional completa. La alternativa del §14 conserva conocimiento, entrada, salida, fuentes y trazas, con consulta/replay históricos probados. La RFC identifica lo necesario para una futura ampliación de BDD, sin aplicarla. No se requiere ampliar tablas para el prototipo entregado.

**Build/UX:** no aplica nuevo build o QA visual a este delta de motor/backend/consola; no acredita el trabajo de interfaz anterior del árbol. **Seguridad:** acciones de laboratorio limitadas a cuentas/Policy/destino de pruebas; cero efectos asistenciales. **Legacy:** no se crea ni se borra automáticamente la capa histórica; el código nuevo usa la arquitectura canónica. **Git/CI:** cambios previos preservados, sin commit, push, merge ni ejecución remota de CI en esta entrega. El informe deja visibles la revisión independiente no completada del último delta y la ausencia de pruebas de carrera multiproceso propias; no las deduce del PASS funcional.
