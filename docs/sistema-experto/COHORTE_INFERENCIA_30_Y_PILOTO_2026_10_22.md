# Cohorte de 30 casos e inventario del residente piloto

Fecha: 22/10/2026. Alcance solicitado: dejar pendiente el guardado conjunto y comprobar la inferencia desde fuentes originales, revisando un expediente existente por separado.

## Decisión y separación de datos

La propietaria confirmó a Elena Vargas Mamani como piloto. Su expediente se consulta exclusivamente en una transacción PostgreSQL `REPEATABLE READ, READ ONLY`, con rollback. No se crean mediciones, juicios ni diagnósticos en ese expediente.

Los treinta residentes de ingeniería son sintéticos, identificados como tales en nombres, documentos, observaciones, instrumentos y justificaciones. Se crean únicamente en SQLite `:memory:` o en el cluster PostgreSQL desechable del runner. No se importan en `remembermind_dev`, no sustituyen residentes existentes y no constituyen treinta historias clínicas verificadas.

No se modifican tablas, columnas, FK, permisos institucionales ni reglas del motor. La configuración instrumental del ensayo existe solo en memoria del proceso de pruebas. El inventario institucional permanece en 100 tablas de negocio. La conservación conjunta continúa con `persistido=false`; no se habilitan revisiones sobre una preparación transitoria.

## Código y recorrido ejecutado

- `database/seeders/CohorteCognitivaInvestigacionSeeder.php`: carga atómica y fuentes sintéticas normalizadas. Trasladado desde `tests/Support/CohorteInferencia30.php` en la [entrega del seeder investigado](COHORTE_INVESTIGACION_SEEDER_2026_10_22.md); conserva una sola implementación.
- `tests/Feature/CohorteInferencia30Test.php`: aceptación, cuatro perfiles, contrastes, replay, Livewire, ausencia de escrituras al consultar, fuente ajena, duplicación y rollback.
- `scripts/auditar-fuentes-residente.php`: inventario técnico privado de un residente explícito; no suplanta a un clínico ni ejecuta una valoración en su nombre.
- `scripts/comparar-cohorte30.php`: compara los dos informes sintéticos sin arrancar Laravel ni consultar ninguna BDD.

La carga reutiliza `RevisarSolicitudPreadmision`, `FormalizarAdmision`, `RegistrarExpedienteClinico`, `AplicarInstrumento` y `RegistrarValoracionPetersenOriginal`. Las observaciones de ingeniería y soportes de contexto se crean en sus modelos existentes; no se afirma que cada uno haya sido digitado mediante un formulario.

Cada residente atraviesa preadmisión pendiente → aprobación → admisión formal con cama y contacto → fuentes → cinco juicios de dominio y requisitos originales → `PrepararEvaluacionInstitucional` → motor declarativo y Petersen → replay del DTO en memoria. La excepción deliberada S24 carece de una medición/juicio visuoespacial. C02 se deriva: nunca se inserta una fila original C02.

La carga confirma su transacción antes de evaluar. El coordinador abre su propia lectura consistente, comparte la adquisición instrumental entre ambas ramas y reautoriza al salir. Los resultados esperados están fuera de las fuentes SQL, en el manifiesto de aceptación y sus assertions; no se insertan perfiles finales en SQL.

## Qué se debe capturar para obtener la inferencia

| Grupo | Datos y vínculo necesarios | Comprobación del ensayo |
|---|---|---|
| Identidad | Residente admitido, edad al acto, escolaridad y cama/contacto propios | Admisión formal, ocupación única y edad ≥60 |
| Autoría | Cuenta y personal activos, rol, permiso y asignación clínica vigente | Capturas y juicios atribuidos por las Actions a la sesión sintética |
| Medición | Instrumento/versionado, identidad de edición/forma/idioma, pregunta, respuesta tipada y fecha | Preguntas propias del instrumento, precisión y procedencia conservadas |
| Contexto instrumental | Escolaridad, idioma, población de referencia, administración y condiciones pertinentes | Entradas explícitas del método; ninguna observación nominal se transforma en puntuación |
| Dominios | Original A/P/I con alcance/contexto y MEDICION aplicable | Cinco áreas independientes; ausencia → U, discrepancia → X |
| C01 | Cambio y corroboración con historia previa y episodio independiente posterior | Dos fuentes identificadas; no se duplica el mismo episodio |
| C03 | IADL, desempeño previo/actual, oportunidad, ayuda, atribución y revisión integrada | Fuente instrumental funcional; no se sustituye por actividades básicas |
| C04 | Revisión diferencial original, factores y conclusión sobre impedimento pendiente | Impedimento bloquea el patrón, aun con mediciones completas |
| C05 | Calidad, condiciones/exclusiones y referencia exacta a cinco originales revisados | Un segundo juicio posterior exige revisar el conjunto ampliado |

La cohorte no rellena cada columna de cada tabla institucional. Conserva NULL cuando el dato no se observó o no aplica. No crea prescripciones, facturación, firmas, licencias ni tratamientos para completar artificialmente formularios ajenos al objetivo. Quedan cinco instrumentos de captura técnica, sin reproducir reactivos protegidos. Los nombres HVLT/BTA/TMT/BNT/ROCF identifican métodos ya implementados, no una autorización para distribuir sus materiales.

## Composición de la cohorte

Edades: 61–89 años; escolaridad de ensayo: 4, 8 y 12 años. Quince casos incluyen caída contextual. Hay seis antecedentes diagnósticos de ingeniería —hipertensión, diabetes tipo 2, artrosis, hipoacusia, catarata e hipotiroidismo— distribuidos independientemente del perfil cognitivo. Ninguno determina automáticamente DCL. Las condiciones de aplicación compatibles son entradas explícitas del caso; no se concluyen por la mera existencia de esos antecedentes.

| Casos | Originales/variante | Resultado esperado y comprobado por el motor |
|---|---|---|
| S01–S03 | Memoria A; otras áreas P | Posible DCL amnésico de dominio único |
| S04–S06 | Memoria y otro dominio A | Posible DCL amnésico multidominio |
| S07–S09 | Un dominio no amnésico A | Posible DCL no amnésico de dominio único |
| S10–S12 | Dos dominios no amnésicos A | Posible DCL no amnésico multidominio |
| S13–S18 | Cinco dominios P | C02=N; sin patrón clasificable de posible DCL |
| S19 | C01=N con soporte | Requisito negativo explícito |
| S20 | C03=N, ayuda documentada | Requisito negativo explícito |
| S21 | C04=N | Abstención por revisión diferencial |
| S22 | C04=S e impedimento no resuelto | Abstención por revisión diferencial |
| S23 | C05=N | Requisito negativo explícito |
| S24 | Sin medición/juicio visuoespacial | Información insuficiente; conserva los demás dominios |
| S25 | Visuoespacial I | No interpretable; información insuficiente |
| S26 | Memoria A sin contexto interpretado | Memoria U, C02 U; información insuficiente |
| S27 | Historia previa sin corroboración | C01 U; información insuficiente |
| S28 | IADL sin oportunidad real | C03 U; identifica la fuente no interpretable |
| S29 | Originales de memoria A y P concurrentes | Memoria X; abstención por contradicción |
| S30 | Segundo original memoria A no incluido en C05 | Memoria A, C05 U; revisión del conjunto pendiente |

El juicio original A/P es una entrada sintética explícita para probar el contrato, no un estado inferido automáticamente de un z ni una aprobación profesional real. El motor decide C02, requisitos derivados, exclusiones, abstenciones y patrón final. Se reutilizan cinco métodos existentes que aportan seis componentes; este ensayo no afirma que cada uno de los 19 subcomponentes disponga aquí de un protocolo independiente ni que se hayan validado clínicamente los 23 métodos del catálogo.

## Volumen realmente cargado en cada base desechable

| Registros | Cantidad |
|---|---:|
| Residentes/admisiones/ocupaciones | 30 de cada tipo |
| Atenciones | 749 |
| Diagnósticos/antecedentes contextuales | 30 de cada tipo |
| Incidentes de caída | 15 |
| Signos vitales | 90 |
| Notas clínicas/valoraciones de dolor | 30 de cada tipo |
| Instrumentos/preguntas de entrada | 5 / 48 |
| Aplicaciones/respuestas | 149 / 1.431 |
| Controles cognitivos | 480 |
| IADL | 30 |
| Valoraciones psicológicas | 60 |
| Juicios originales Petersen/soportes | 271 / 449 |
| Evaluaciones expertas conjuntas guardadas | 0, pendiente por decisión de alcance |

Las mediciones seriadas de contexto tienen instantes distintos. Se conservan cero, FALSE y NULL como estados diferentes. La segunda carga sobre el mismo conjunto se rechaza antes de crear filas; no es una actualización de pacientes ni una conciliación de fuentes reales.

## Reproducción

Desde la raíz del repositorio, la configuración PHPUnit fuerza SQLite en memoria y `APP_ENV=testing`:

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter 'CohorteInferencia30Test|PetersenInstitucionalTest|EjecucionConsultaCognitivaTest' --log-junit docs/auditorias/cohorte-inferencia-30-2026-10-22/sqlite.junit.xml
```

PostgreSQL requiere la instalación local de PostgreSQL 18 y utiliza un cluster nuevo, puerto propio, rol sin privilegios de administrador y base propios. El runner detiene y elimina únicamente su cluster temporal al terminar:

```powershell
./scripts/probar-postgres.ps1 -Filtro CohorteInferencia30Test -Informe "$PWD/docs/auditorias/cohorte-inferencia-30-2026-10-22/postgres.junit.xml"
```

Para exportar la matriz sintética calculada, establecer `RM_COHORTE30_INFORME` a una ruta de informe antes del comando y quitar la variable después. Los JSON de [SQLite](../auditorias/cohorte-inferencia-30-2026-10-22/sqlite-resultados.json) y [PostgreSQL](../auditorias/cohorte-inferencia-30-2026-10-22/postgres-resultados.json) contienen las salidas verificadas por caso, estados, motivos, cantidades y huella de esa ejecución. No contienen protocolos reales ni credenciales.

Comparación reproducible de ambas ejecuciones:

```powershell
php scripts/comparar-cohorte30.php docs/auditorias/cohorte-inferencia-30-2026-10-22/sqlite-resultados.json docs/auditorias/cohorte-inferencia-30-2026-10-22/postgres-resultados.json
```

Las huellas incluyen identidades originales y UUID generados en cada carga: se comparan estrictamente dentro de cada ejecución, no como si dos bases nuevas compartieran identidades. Para comparar motores se usan los casos, estados, motivos, métodos y conteos, excluyendo esas identidades técnicas.

El inventario privado se reproduce con `php scripts/auditar-fuentes-residente.php COD_RES_000028`. Su salida está seudonimizada y permanece en `storage/app/private/auditorias-locales`, ignorada por Git. No debe copiarse a documentación pública. El informe distingue conteo bruto, estados, fechas/corte, autor no documentado, referencias incompatibles y nueve originales faltantes; no certifica admisibilidad clínica ni completitud instrumental sin un método adoptado.

## Resultados de esta ejecución

| Verificación | Resultado |
|---|---|
| SQLite en memoria: cohorte + contrato Petersen + consulta integrada | **61 pruebas, 2.372 aserciones; 0 fallos, errores u omisiones** |
| PostgreSQL 18 desechable: cohorte | **3 pruebas, 2.144 aserciones; 0 fallos, errores u omisiones** |
| Comparación entre motores | PASS: 30 casos, estados, motivos y cantidades equivalentes |
| Formato PHP de los cuatro archivos de código nuevos | Pint PASS |
| Sintaxis PHP | PASS |
| Revisión independiente de cohorte | PASS acotado; corrección de catálogo IADL incorporada |
| Revisión independiente del inventario privado | Apto como inventario técnico agregado; no certifica admisibilidad clínica |
| Build/QA visual | No ejecutados: no se modificó frontend; Livewire se verificó funcionalmente |

Los JUnit conservan comandos/suites, tiempos y conteos en `docs/auditorias/cohorte-inferencia-30-2026-10-22`. Las 3 pruebas PostgreSQL repiten la cohorte, el rechazo de entorno local y el fallo parcial; no se suman a las de SQLite como si fueran 64 casos clínicos diferentes. El runner PostgreSQL confirmó el cierre del cluster temporal.

El `git diff --check` global detecta espacios al final de línea en la agenda de Enfermería, dentro del trabajo simultáneo preexistente; no se modifican esos archivos. El control del README tocado y el formato de los archivos propios pasan. La rama y las modificaciones ajenas se preservan; no se ejecutan operaciones de stage, commit o push.

## Aceptación y límites

- Implementado y probado: carga de 30 casos aislados; fuentes SQL; cuatro perfiles, abstenciones y conflictos; consulta de ambas ramas; verificación y replay en memoria; integridad de referencias y rechazo de fuente ajena; ausencia de efectos al consultar; rechazo de carga duplicada; rollback completo ante fallo deliberado.
- Implementado y probado en Livewire: consulta/acción de análisis para los cuatro patrones con Psicología y consulta del patrón amnésico único con Medicina. No equivale a QA visual en cinco tamaños ni a una base de laboratorio publicada en el navegador.
- Hallazgo de autorización: el rol médico predeterminado no recibe `valoraciones_psicologicas.ver`. El prefijo `valoraciones_` del seeder no coincide con los recursos separados por punto. Cuando C04/C05 usan esa fuente, la consulta completa responde 403. El ensayo comprueba la denegación y asigna **solo a su cuenta médica sintética** el permiso explícito para verificar la lectura autorizada. No se amplía el rol ni se cambia la instalación institucional. Una eventual ampliación institucional de lectura de esta fuente necesita decisión específica de la propietaria, conforme a AGENTS.md.
- Pendiente por decisión de la propietaria: conservación SQL completa del DTO conjunto y revisiones profesionales sobre ese resultado conservado.
- Pendiente de fuentes reales: protocolos, perfiles y juicios originales del residente piloto. Las observaciones ya existentes siguen disponibles; no se convierten retrospectivamente en pruebas.
- Pendiente de validación clínica: concordancia con casos revisados por profesionales y autorización de los perfiles instrumentales. Las pruebas sintéticas acreditan lógica y flujo técnico.

No se cambia la interfaz institucional, no se despliega otro servidor, no se crea un acceso alternativo y no se ejecutan commit ni push. La cohorte se reconstruye en las pruebas; no queda instalada en el expediente operativo al terminar el runner.

## Trazabilidad

| Requisito | Implementación | Evidencia |
|---|---|---|
| 30 residentes y admisión válida | `CohorteInferencia30::cargar` | 30 admisiones/ocupaciones; cronología y unicidad verificadas |
| Fuentes y autoría | `catalogo`, `contexto`, `cognicion`; Actions existentes | Aplicación/pregunta propias y autoría de sesión |
| ≥10 perfiles y cuatro categorías | `PrepararEvaluacionInstitucional` sin cambios | 12 clasificaciones, tres por categoría |
| Ausencias, negativos y conflictos | Manifiesto S19–S30 | Estado y elemento afectado; no solo patrón null |
| No mezclar residentes | `comprobarFuenteAjena` | Excepción y cero efectos en juicios, soportes y auditoría |
| Carga limpia/atómica | Guarda, transacción y test de fallo parcial | Sin duplicación; rollback de filas, auditoría y correlativos |
| Mismo motor en pantalla | `ResultadosExpertoResidente::analizarEvidencia` existente | Assertions Livewire con salidas calculadas |
| Replay de ambas ramas | `InstantaneaEvaluacionInstitucional::verificar` | Contenido y huella idénticos; consulta sin escrituras |
| Inventario del piloto | Script de auditoría READ ONLY | Informe local privado; sin mutaciones ni valoración atribuida |
