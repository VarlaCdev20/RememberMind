---
title: "Sistema experto V1 — implementación técnica"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-08
owner: RememberMind
source_of_truth: false
verified_against_commit: null
code_snapshot: "Working tree de codex/sistema-experto-v1; base 7a6758e8; sin commit"
verification_scope: TECHNICAL_SCHEMA_AND_SYNTHETIC_EXECUTIONS
runtime_verified: true
clinical_runtime_verified: false
clinical_knowledge_loaded: false
documentary_knowledge_loaded: true
documentary_knowledge_load_approved: true
clinical_activation: false
owner_activation_authorized: true
investigated_package: MEM-INV-20261008
investigated_package_status: PROPOSED_OBSERVATIONAL_ONLY
administrative_ui_qa: PASS
administrative_ui_qa_scope: READ_ONLY_LIGHT_THEME_SYNTHETIC_LIVEWIRE
automated_global_gate_status: FAIL
---

# Sistema experto V1

## Autoridad y alcance

Este documento registra implementación y evidencia técnica; no constituye un
contrato clínico ni autorización para activar conocimiento. Las pruebas se
realizaron con datos artificiales y con el código de trabajo sin commit.
`verified_against_commit: null` impide atribuir sus resultados a un commit
certificado. `runtime_verified` no significa validación clínica.

Fuentes aplicadas:

- [Documento Maestro O.R.I.O.N.](https://docs.google.com/document/d/1d68S-EZTyYVKsYm-cOtaktYSNUBfDAi59FHexkEd88I):
  4.16/4.19 y 4.27–4.29; D-123, D-130, D-131, D-133, D-135, D-137, D-143 y D-144.
- [Matrices O.R.I.O.N.](https://docs.google.com/spreadsheets/d/1lPFoZD6LlIoOkuWsLN3VTjvsgBowBl0mBQekjyOiyVA):
  conceptos, relaciones, decisiones, evaluabilidad, fuentes y mapeos.
- [Baseline operativo](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md),
  [diccionario V2.1](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md) y
  [extensión V2.2](../base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md).

El DOCX consultado tiene SHA-256
`70d1b0e968fd9e77b3e9d36fd30959f180fc7cbe593fb23949482b1d85f7d8c2`.
El contrato físico extraído, contrastado independientemente y preservado en
[fixture D-137](../../tests/Fixtures/SistemaExperto/contrato-d137.json), identifica
fuentes, secciones y tipos por columna. Su hash de extracción es
`6cb9607d7f8f6a37349460f9d9f158d7cbc2f15bd4a353377184646ee5086fbf`.
Son instantáneas consultadas, no una promesa de sincronización futura con Drive.

Los tres diseños locales de árboles permanecen PROPOSED. No aportan reglas,
scores ni alertas al motor cognitivo. La estructura vigente es red semántica,
evidencias versionadas, condiciones positivas conjuntivas y resolución D-131.

## Inventario y persistencia

El inventario inicial no encontró un módulo experto ejecutable ni el adaptador
D-144 en la rama de partida. Se creó el módulo canónico, sin duplicar una
implementación existente. No se incorporaron dependencias nuevas.

Hay **71 tablas operativas + 23 expertas**, contadas separadamente. Las tablas
técnicas del framework y paquetes quedan fuera de ambos inventarios. La prueba
operativa excluye solamente los 23 nombres expertos conocidos, por lo que una
tabla inesperada sigue provocando un fallo.

Contrato experto: 190 columnas, 39 anulables, 23 PK string(20), 65 FK (58 simples
y 7 compuestas), 24 UNIQUE y 52 índices explícitos adicionales. FK con borrado
RESTRICT y actualización NO ACTION. Sin autoincremento, timestamps automáticos,
soft deletes, JSON/EAV, enums, defaults ni triggers.

### Orden físico D-137

Las migraciones en `database/migrations/2026_10_08_120001_…120023_*` siguen:

| Orden | Tabla |
|---|---|
| 1 | versiones_modelo_experto |
| 2 | nodos_semanticos |
| 3 | relaciones_semanticas |
| 4 | dominios_valores_expertos |
| 5 | valores_semanticos |
| 6 | variables_expertas |
| 7 | fuentes_datos_expertas |
| 8 | mapeos_variables_fuente |
| 9 | mapeos_valores_fuente |
| 10 | criterios_dominios_resultado |
| 11 | reglas_expertas |
| 12 | condiciones_regla_experta |
| 13 | consecuencias_regla_experta |
| 14 | evaluaciones_expertas |
| 15 | evidencias_evaluacion |
| 16 | relaciones_evidencias_evaluacion |
| 17 | evaluacion_criterios |
| 18 | evidencias_criterio_evaluacion |
| 19 | resultados_criterio |
| 20 | trazas_inferencia |
| 21 | evaluaciones_reglas |
| 22 | evaluaciones_condiciones_regla |
| 23 | evidencias_soporte_condicion |

Se declara la PK antes de las FK para evitar que PostgreSQL procese una FK
autorreferencial antes de crear su clave referida. Cada `down` retira solamente
su tabla; Laravel aplica el rollback en orden inverso.

### Ejecución autorizada

La propietaria autorizó expresamente SQLite en memoria, una PostgreSQL nueva
desechable y después la incorporación de las 23 tablas vacías a
`remembermind_dev`, conservando sus datos.

Antes de instalar se generó un backup `pg_dump` privado y se restauró en otra
base desechable. Se verificaron huellas de todas las filas preexistentes y la
estructura de las 71 tablas operativas. La instalación transaccional ejecutó
únicamente las 23 migraciones expertas. Resultado real:

- 23 tablas expertas instaladas y vacías.
- 71 tablas operativas y todos los datos preexistentes preservados.
- Restauración del backup contrastada con las huellas anteriores.
- Ningún conocimiento clínico cargado o activado.
- Ningún `migrate:fresh` contra `remembermind_dev`.

Backup privado: `C:/Users/CARLAENCINAS/.codex/private-backups/remembermind/20261008/remembermind_dev-before-expert-3cda7468.dump`.
No se guarda en Git ni se expone desde la aplicación. SHA-256:
`cab3b5ca48376be5f3fc2ca7415c1e7711f9011d7f9e4c70d04a4a28a9765893`.
Reporte local: `remembermind-expert-install-result.json`, dentro del TEMP de la
sesión. Los backups y bases desechables se conservaron; no se solicitó borrarlos.

## Arquitectura implementada

Actualización 08/10/2026: la propietaria autorizó posteriormente la carga del
conocimiento. Se cargó la red documental O.R.I.O.N. en PostgreSQL: 55 conceptos,
29 relaciones, versión `ORION-V1-20261008`. Los datos preexistentes se conservaron
y no se generaron evaluaciones clínicas. La versión no activa inferencia;
los mapeos y las reglas clínicas faltantes no se completan por analogía.
Véase [alcance y evidencia de la carga](INTERFAZ_MEDICA_RESULTADOS.md#datos-existentes-y-carga-documental-autorizada--08102026).

Módulo: `app/Backend/Modulos/SistemaExperto/`. Frontend administrativo:
`app/Frontend/Livewire/Superadministrador/SistemaExperto/`.

| Responsabilidad | Implementación |
|---|---|
| Modelos y relaciones | 23 modelos concretos en `app/Models/`, `ModeloExperto` y `BuilderExperto` |
| Lectura por versión | `Conocimiento/CargadorConocimiento.php` |
| Grafo dirigido y procedencia | `Conocimiento/RedSemantica.php` |
| Paquete y validación | `Conocimiento/PaqueteConocimiento.php`, `ValidadorPaqueteConocimiento.php` |
| Mapeo exacto | `Conocimiento/MapeadorSemantico.php` |
| Aislamiento e identidad | `DTO/ContextoEjecucion.php`, `EvidenciaExperta.php`, `MemoriaTrabajo/MemoriaTrabajo.php` |
| Fuentes COG-MEM | `Adaptadores/AdaptadorFuentesCOGMEM.php`, `InspectorComponenteInstrumental.php` |
| Compuertas técnicas | `Evaluadores/` |
| Condiciones AND positivas | `Inferencia/EvaluadorReglas.php` |
| Resolución COG-MEM D-131 | `Resolucion/ResolvedorSoportesCOGMEM.php` |
| Resultado y perfil separado | `Servicios/MotorExperto.php` |
| Explicación reconstruible | `Trazabilidad/ConstructorTraza.php` |
| Guardado normalizado | `Acciones/PersistirEvaluacionTecnica.php`, exclusivamente en pruebas desechables |
| Casos artificiales reproducibles | `Conocimiento/EjemploTecnicoCOGMEM.php`, `Servicios/DemostracionCOGMEM.php` |
| Candidatos inactivos | `Conocimiento/PaquetesCandidatosCognitivos.php` |

Los modelos tienen PK string, sin incremento ni timestamps, asignación masiva
cerrada y relaciones reales. Se bloquean modificación histórica y borrado
ordinario, incluidos métodos masivos del builder. Todavía no existe un flujo
aprobado de edición/retirada de conocimiento ni de ejecuciones clínicas abiertas;
el guardado técnico conserva una ejecución completa nueva en una transacción.

### Evidencias y reglas

- Identidad atómica: evaluación + mapeo de variable + registro raíz. Una misma
  evidencia puede participar en varios criterios sin duplicarse.
- Memoria aislada por residente, evaluación, versión y fecha de corte.
- Valor bruto original y valor tipado separados; metadatos de fuente cotejados
  contra columnas reales, no SQL generado por texto arbitrario.
- Mapeo activo, aprobado, exacto, tipado, compatible y vigente en el corte.
  NULL, ausencia, literal desconocido o sin mapa nunca significan normalidad.
- Extracción exige un contrato explícito de campo directo o selector de
  componente. No hay NLP, equivalencias léxicas, trim/case folding o fallback.
- Relaciones de evidencias: mismo episodio canónico, corroboración y confusor.
  Una relación de confusión no invalida por sí sola una evidencia.
- Las reglas configuradas de resultado exigen todas sus condiciones positivas.
  NO_CUMPLE no produce la conclusión contraria.
- D-131 produce dificultad evidenciada, sin dificultad evidenciada o hallazgos mixtos únicamente desde
  soportes positivos válidos. Un mismo soporte/episodio no fabrica independencia.
  EV-CM-2 sin soporte se rechaza como inconsistencia de cobertura.
- EV-CM-0/1 no emiten resultado. Basal ausente no impide por sí solo EV-CM-2;
  MOD-CM-1 permanece separado de la evaluabilidad y del resultado.
- Perfil por criterio, sin promedio, peso, score, prioridad inventada o agregación.

### Fuentes y límites del piloto

El adaptador lee controles cognitivos y aplicaciones instrumentales existentes.
Comprueba residente, profesional, fecha, estado, instrumento/versión,
preguntas/opciones/respuestas y método exacto del componente. COMPLETA, totales,
clasificación o narrativa no sustituyen esas comprobaciones. Las respuestas y
su procedencia se ordenan canónicamente antes de comparar instantáneas.

Repetición y olvido requieren verificación explícita de contexto y atribución;
FALSE no demuestra preservación. La metaevidencia de cambio pertenece a BL-LON.
El productor operativo actual combina flags y valores predeterminados: no está
acreditado que FALSE represente una observación negativa explícita. Su consumo
permanece limitado por el adaptador; no se modificó ese formulario en esta tarea.

La persistencia reextrae y remapea las fuentes bajo bloqueo antes de guardar.
Rechaza cambio de respuestas, versión instrumental, conocimiento o DTO semántico
manipulado sin filas parciales. El autor se deriva de una cuenta/personal activos.
La fecha de corte permanece contextual; inicio, fin e incorporación registran
tiempo técnico real de ejecución. Un reintento no sobrescribe la evaluación.

Las cuatro tablas de trazabilidad conservan regla, condición y evidencia soporte.
Eloquent reconstruye resultado → criterio → traza → regla → condición → evidencia
→ variable/valor → mapeo → registro operacional. La traza en memoria conserva
también bruto original, residente, corte y motivos de no emisión.

## Otros criterios

COG-ATE/EJE/LEN/VIS tienen manifiestos documentales inactivos en memoria con los
15 subcomponentes candidatos identificados en `2_Conceptos`. Los criterios están
conceptualmente definidos; esto no formaliza compuertas, reglas ni resultados.
No se crearon variables, dominios de resultado o resolvedores por analogía.
Los manifiestos no son paquetes ejecutables ni filas persistidas.

COG-ORI/VEL y BL-LON/FUN/PSI/CONF/RISK conservan sus roles transversales/contextuales.
No se elevaron a criterios adicionales.

## Consulta administrativa

**UX FLOW:** Superadministrador real → Gestión del sistema → Sistema experto ·
revisión → seleccionar versión → explorar detalles → consultar caso artificial
y su explicación. Versiones y conceptos están primero; contenido extenso se
despliega mediante `details`. En móvil las columnas pasan a una, conservando
labels, estados y acciones. No hay modal de captura ni acciones de edición.

La Policy `VersionModeloExpertoPolicy::consultar` exige cuenta activa, rol real,
permiso explícito `auditoria.ver` y ausencia de preview. Se verifica al montar,
renderizar y cambiar selecciones. Usa `checkPermissionTo`, porque el Gate global
concede `.ver` y `viewAny` a Superadministración. No se añadieron permisos.
Selecciones bloqueadas contra hidratación y versiones existentes comprobadas.

La consulta lee únicamente las 12 tablas de conocimiento por versión. La cadena
de versiones se valida; la validación semántica institucional completa queda
pendiente de sus contratos específicos. ACTIVO no se presenta como APROBADO;
`estado_aprobacion` se muestra solamente donde existe. No se consultan datos,
evidencias, resultados ni trazas clínicas de residentes.

La demostración ejecuta el motor real con cinco casos artificiales en memoria:
dificultad evidenciada, sin dificultad evidenciada, mixtos, contexto insuficiente e inconsistencia de
cobertura. No escribe BDD, no consulta residentes y no puede usar conocimiento
institucional ni activar el persistidor. Los IDs/literales artificiales se
rotulan como técnicos y no son una escala clínica.

## Avance de entregables

| Etapa | Estado técnico | Archivos creados/modificados | Funcionalidad y decisión |
|---|---|---|---|
| 1 Inventario | COMPLETA | `InventarioExperto`, este reporte | Ausencia inicial del módulo/adaptador; 71 y 23 separados; O.R.I.O.N. 4.29 |
| 2 Migraciones | COMPLETA | 23 migraciones; `BddOperativaV2Test` | D-123/D-137 exactos; revisión, pruebas y ejecución autorizadas |
| 3 Modelos | COMPLETA | 23 modelos + base/builder | Relaciones, tipos, casts e inmutabilidad histórica |
| 4 Red | COMPLETA | `RedSemantica`, `CargadorConocimiento` | Grafo dirigido versionado, contratos explícitos, sin jerarquía rígida |
| 5 Memoria/adaptadores | COMPLETA técnica | DTO, memoria y adaptadores | Aislamiento, idempotencia y procedencia D-144 |
| 6 Mapeos | COMPLETA técnica | Mapeador y validador | D-143 exacto; contenido clínico no cargado |
| 7 Compuertas | COMPLETA para piloto técnico | Evaluadores | Admisibilidad, contexto y EV-CM; no se generaliza a otros criterios |
| 8 Motor/resolvedor | COMPLETA para piloto técnico | Inferencia, resolución, motor | Reglas AND y D-131; perfil no agregado |
| 9 Resultados/trazas | COMPLETA en BDD desechable | Constructor y acción | Persistencia normalizada, cuatro tablas y rechazo atómico |
| 10 COG-MEM | COMPLETA técnica | Adaptador, inspector, ejemplo y pruebas | Fuentes reales con contenido artificial verificable; sin validación clínica |
| 11 Pruebas | COMPLETA específica; gate global FAIL | `tests/Unit/SistemaExperto`, `tests/Feature/SistemaExperto` | AST/SQLite/PostgreSQL; cinco fallos ajenos al módulo clasificados abajo |
| 12 Documentación | COMPLETA | Este reporte, índice experto y routing documental | Implementado/verificado/migrado/cargado/aprobado/activo separados |

Fuentes/decisiones por etapa: secciones de autoridad, persistencia y arquitectura
de este reporte. Riesgos y bloqueos por etapa: apartado siguiente. Siguiente
acción institucional pendiente: aprobar contratos de conocimiento y completar
la validación profesional antes de autorizar ejecución clínica. El cierre
técnico y la aceptación del panel de consulta están completados.

## Verificaciones reales

Últimos resultados específicos ejecutados el 2026-10-08:

| Verificación | Resultado real | Alcance |
|---|---|---|
| Unitarias y contratos AST | PASS: 66 pruebas, 1973 aserciones | Sin crear tablas; esquema/modelos, mapeos, memoria, reglas, resolvedor, adaptadores y candidatos |
| Feature SQLite `:memory:` | PASS: 23 pruebas, 1275 aserciones | DDL, FK/UNIQUE/rollback, guardado/traza y HTTP/Livewire |
| Feature PostgreSQL desechable | PASS: 23 pruebas, 1414 aserciones | Esquema, guardado y acceso, misma batería |
| `BddOperativaV2Test` | PASS: 19 pruebas, 223 aserciones | 71 operativas; exclusión explícita de las 23 expertas |
| Instalación PostgreSQL `remembermind_dev` | PASS | 23 vacías, 71 y datos preservados; backup restaurado |
| `npm run build` | PASS | Vite 8.3.1; ninguna dependencia nueva |
| Pint de archivos nuevos de esta tarea | PASS | No reformateó archivos compartidos con cambios anteriores |
| Suite PHP completa SQLite | FAIL: 888 pruebas, 18.077 aserciones, cinco fallos, un error y 15 omitidos | El error experto fue corregido y revalidado en las dos baterías específicas; cinco fallos ajenos al módulo permanecen |
| QA visual y funcional administrativa | PASS independiente | Chrome con Livewire HTTP real; 30 combinaciones de estados/anchos, teclado, carga, lectura y contraste; SQLite QA desechable sin residentes |
| Enlaces documentales locales | PASS: 235 referencias en nueve archivos | Archivos y anclas; no certifica acceso futuro a enlaces externos |

Comandos específicos, con PHP 8.3.33 y PHPUnit 12.5.36:

```powershell
php vendor/phpunit/phpunit/phpunit tests/Unit/SistemaExperto tests/Unit/InventarioExpertoTest.php --no-progress
php vendor/phpunit/phpunit/phpunit tests/Feature/SistemaExperto --no-progress
npm run build
```

PostgreSQL requiere `APP_ENV=testing`, conexión `pgsql` y base dedicada con nombre
`remembermind_experto_test_YYYYMMDD_identificador`. No ejecutar la batería
estructural sobre `remembermind_dev`. Los guards rechazan esa base.
La base usada para la última batería fue
`remembermind_experto_test_20261008_0de3e225`.

### Resultado global y límites de regresión

La suite global duró aproximadamente 15 minutos. Cargó una fixture experta que
carecía de `fecha_hora_creacion` en un nodo artificial; ese error se corrigió
durante la ejecución y la batería completa del módulo pasó después en SQLite y
PostgreSQL. No se reejecutó toda la suite para sustituir ese registro histórico.
El gate global permanece FAIL por estas cinco expectativas ajenas al experto,
contrastadas independientemente con el código y `HEAD`:

| Test / ubicación | Fallo comprobado |
|---|---|
| [FiltrosDisenoUnificadoTest:43](../../tests/Feature/FiltrosDisenoUnificadoTest.php) | La búsqueda de preadmisiones no está dentro de `rm-filter-bar`; combinación ya presente en `HEAD` |
| [MiTurnoServiceTest:593](../../tests/Feature/MiTurnoServiceTest.php) | Espera «Mi turno» donde la cabecera actual muestra el saludo con nombre; expectativa modificada en trabajo previo |
| [MisPacientesRedisenadaTest:1738](../../tests/Feature/MisPacientesRedisenadaTest.php) | Espera `250.00` y la vista renderiza `250 mL`; vista, modelo y test iguales en `HEAD` |
| [PaseTurnoReconstruidoTest:71](../../tests/Feature/PaseTurnoReconstruidoTest.php) | Expectativa de `250.00 ml` frente a presentación actual `250 ml` |
| [RolesVistasPermisosMatrixTest:89](../../tests/Feature/RolesVistasPermisosMatrixTest.php) | Espera HTTP 200 en una ruta de habitaciones que redirige con HTTP 302, también en `HEAD` |

No se modificaron esos flujos ni se debilitaron sus tests. Las aserciones de
historia/solo lectura posteriores a una expectativa fallida no fueron
alcanzadas: este log no acredita esas aserciones ni demuestra pérdida de datos.
Las 15 omisiones de la suite global no pertenecen a la batería experta, que
terminó sin omisiones. Pruebas específicas PASS no equivalen a PASS global.

### Aceptación del panel de consulta

Se ejercitó `/admin/sistema-experto` mediante login real de una cuenta artificial
activa de Superadministración con `auditoria.ver`. El servidor QA separado usó
una SQLite nueva y desechable, con cero residentes; no utilizó la conexión de
`remembermind_dev`. El primer recorrido mostró conocimiento vacío; después se
insertaron exclusivamente en QA una versión y un nodo artificiales inactivos.
La demostración continuó sin persistir ejecuciones expertas.

Matriz: vacío y cinco casos técnicos en 1440, 1280, 1024, 768 y 390 CSS px, tema
claro y movimiento reducido. Se comprobaron 30 cambios HTTP/Livewire con carga
visible, botones deshabilitados durante la petición y selección actualizada.
Los `summary` conservan foco visible y responden a Enter. No se observaron
desbordes horizontales, controles menores de 44 px, errores JavaScript ni los
literales visibles prohibidos.

El contraste incluyó las tramas, gradientes y transparencias reales. Se midieron
rangos RGB compuestos conservadores de todos los stops, con conversión de CSS
`oklab`/`color` a sRGB en Chrome y espera de estabilización de las transiciones.
Cota mínima 4,62:1; aviso de cobertura 5,62:1. Diez mediciones de estados finales
en escritorio/móvil no dejaron superficies sin resolver. Las mismas superficies
y tokens se observaron en los tres anchos intermedios.

Un revisor independiente examinó código, métricas y 19 capturas y emitió PASS,
sin cambios de producto durante QA. Formularios de captura, dirty state,
guardado clínico y gráficas son N/A en este panel de lectura. No existe una
imagen aprobada específica para él; usa los componentes y el contrato visual
común. El dictamen no certifica otras pantallas, tema oscuro ni operación clínica.

Artefactos locales temporales: `AppData/Local/Temp/remembermind-expert-qa`, bajo
el perfil de Windows usado para la tarea. `live-empty-metrics.json`,
`live-full-metrics.json`, `live-contrast-bounds.json` y capturas `live-*.png`
identifican el recorrido y sus límites; no están incorporados a Git.

Comprobación final de QA: cero residentes, cero evaluaciones expertas y sólo dos
filas de conocimiento artificial inactivo (versión y nodo). El servidor QA fue
detenido. La eliminación solicitada de cinco archivos temporales (credenciales
artificiales, cookies y SQLite con auxiliares) fue rechazada por el control
automático con motivo «blocked by policy»; permanecen en el directorio temporal,
fuera del repositorio. No contienen credenciales de cuentas institucionales.

Las revisiones independientes fueron de lectura: esquema contra O.R.I.O.N.,
integridad, seguridad y contratos UI. Detectaron y originaron correcciones de
orden de PK, inmutabilidad masiva, origen bruto, remapeo, respuestas/versiones
desactualizadas, tiempos técnicos, independencia de soportes, corte y selección
segura/accesible. No son certificación clínica.

## Pendientes y límites

### Paquete investigado COG-MEM

La propietaria autorizó la activación y solicitó investigar e implementar el
paquete faltante el 08/10/2026. Esa autorización está aceptada. No existe todavía
un dictamen profesional identificable sobre el **contenido nuevo** producido
por esta investigación; no se atribuye a un experto un dictamen no documentado.

`MEM-INV-20261008` es una **propuesta observacional implementada**, no un paquete
clínico completo. Se registra mediante `sistema-experto:cargar-paquete-memoria
{autor}`, con cuenta activa de Superadministración y `auditoria.ver`.
Contiene 3 nodos, 2 dominios, 6 valores, 2 variables, 1 fuente, 2 mapeos de
variable, 6 correspondencias literales y 1 asociación criterio/dominio.
Todos quedan INACTIVO; valores y correspondencias quedan PENDIENTE, sin
vigencia. Las 3 tablas de reglas permanecen vacías para esta versión. No se
importan preguntas protegidas, instrumentos nuevos, pesos ni umbrales.

| Literal de ambos campos de memoria | Correspondencia propuesta | Condición que debe validar el profesional |
|---|---|---|
| `CONSERVADA` | `SIN_DIFICULTAD_OBSERVADA` | Oportunidad real de observación, sin convertirlo en normalidad global |
| `ALTERACION_LEVE` / `ALTERADA` | `DIFICULTAD_OBSERVADA` | Dificultad concreta documentada; no asigna severidad ni diagnóstico |
| `NO_VALORABLE` | Sin mapeo automático | No acredita por sí solo intención de valoración y motivo exigidos para `NO_DETERMINABLE` |
| NULL, indicadores FALSE, texto libre/desconocido | Sin mapeo | No equivalen a ausencia de dificultad |

Los vocabularios candidatos proceden de O.R.I.O.N. D-119/D-120; los literales
fuente proceden del formulario actual. La literatura no prescribe esa
equivalencia exacta. D-123 permite persistir propuestas sin elevar su aprobación.
D-125 separa dominio observacional y resultado integrado. D-130 advierte que
una condición positiva aislada no representa exhaustividad del conjunto de
evidencias: no se instala una regla «hay CONSERVADA → resultado sin dificultad».
Quedan pendientes los predicados completos de conjunto, el componente
instrumental específico, los vínculos de participación y las reglas revisadas.
D-131 solo resolverá soportes después de EV-CM-2; EV-CM-0/1 no emiten resultado.

Fuentes primarias consultadas el 08/10/2026:

- [DETeCD-ADRD: instrumentos clínicos de evaluación](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/):
  integrar pruebas validadas, historia, función y contexto; una puntuación
  aislada no reemplaza el juicio profesional.
- [Mini-Cog: puntuación e interpretación](https://mini-cog.com/scoring-the-mini-cog/):
  su total combina recuerdo y reloj; el cribado no es diagnóstico ni proporciona
  un umbral validado de memoria aislada. No se adopta el total como COG-MEM.
- [Mini-Cog: derechos de uso](https://mini-cog.com/contact/): distingue uso clínico,
  comercial e investigación. No se presupone autorización comercial ni para
  modificar el instrumento o incorporar sus reactivos al software.
- [Barthel, Shirley Ryan AbilityLab](https://www.sralab.org/rehabilitation-measures/barthel-index):
  evalúa diez actividades de autonomía/movilidad; no constituye una prueba de
  memoria. Se conserva el catálogo existente sin modificarlo.

Auditoría de Elena Vargas Mamani en PostgreSQL de desarrollo: 40 controles
cognitivos y 10 aplicaciones de Barthel; ningún componente específico de
memoria ni evaluación experta guardada. Son registros del dataset de desarrollo
existente, no mediciones clínicas reales acreditadas. No se completa una prueba
inventando respuestas ni se atribuyen nuevos actos a Rosa u otro profesional.

La consulta muestra cantidades y requisitos pendientes sin remapear los datos,
asignar estados EV-CM, ejecutar reglas o guardar resultados. Conserva Policy y
alcance contextual; para consultar aplicaciones requiere además
`aplicaciones_instrumento.ver`. No se conceden permisos adicionales a Enfermería.
La carga es transaccional, auditable e idempotente; rechaza versiones divergentes
sin sobrescribir. No modifica ORION-V1, el esquema ni historia operativa.

Aceptación clínica: **BLOQUEADA**, aunque la implementación y sus pruebas técnicas
pasen. Falta registrar una evaluación instrumental válida y contexto clínico,
validar este contenido nuevo y completar reglas/activación institucional.

Verificación de esta entrega: 66 pruebas / 553 aserciones PASS en SQLite en
memoria y PostgreSQL desechable, incluyendo núcleo, carga ORION, propuesta y
consulta de resultados. Build y Pint PASS. La carga principal contrastó las
huellas de 79 tablas preexistentes y la instantánea ORION, sin cambios.
En Enfermería se comprobaron selección Memoria/Atención y vuelta, cantidades,
límites y ausencia de errores JavaScript. El bloque nuevo se revisó a 1440,
1281, 1024, 768 y 390 CSS px (1281 es el ancho real alcanzado al solicitar 1280
con el zoom del navegador); no hubo desbordes ni literales inválidos visibles.
Estas pruebas no sustituyen la validación clínica ni cambian el FAIL global
histórico de la suite descrito arriba. No se hizo commit ni push.

1. Aprobar contenido, vocabularios, estados y contratos de predicados/extracción
   aplicables al conocimiento institucional. El código no inventa esos catálogos.
2. Validar profesionalmente variables, contexto, instrumental/versiones/métodos,
   casos de referencia y derechos de uso. Las preguntas actuales son artificiales.
3. Formalizar cada criterio adicional antes de implementar su compuerta/resolvedor.
4. Aprobar gobernanza de edición/retiro y ejecución clínica, autorización
   contextual por residente/competencia e integración antes de abrir escritura.
5. [DEC-OPEN-005](../DECISIONES_PENDIENTES.md#dec-open-005) sigue abierto para estos
   pendientes; la aprobación de D-123/D-137 y de pruebas no lo cierra globalmente.

No se conecta el experto con diagnósticos, tratamientos o alertas automáticas.
No hay escritura clínica HTTP, seed clínico, publicación ni despliegue. No se
introdujo legacy V1. El legacy ajeno al módulo permanece fuera de esta tarea.
**Commit/push/merge: no realizados**, conforme a la instrucción de esta tarea.
