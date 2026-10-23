# O.R.I.O.N. — expediente de entrega integral

Fecha: 20/10/2026, America/La_Paz. Estado: **EN CURSO; NO ACREDITA CIERRE
INSTITUCIONAL COMPLETO**. Solicitud: completar y documentar entradas, reglas,
motor, conservación histórica, informe, revisión y aceptación en una entrega.

Este documento reúne el contrato, operación, arquitectura y evidencia. Los
documentos enlazados conservan el detalle de cada versión; no se copian
protocolos clínicos ni se convierten propuestas en decisiones aprobadas.

## 1. Fuentes vigentes y límites de la entrega

1. Instrucción actual de completar el proceso y documentarlo.
2. Baseline y [decisiones BDD vigentes](../base-de-datos/README.md): **104
   tablas de negocio instaladas**, 75 operativas y 29 expertas. Las 16 técnicas
   se excluyen del inventario de negocio.
3. [Límite de diseño de 100](DICTAMEN_LIMITE_100_TABLAS_2026_10_20.md), pedido
   posterior de la propietaria. Los candidatos de 25 y 15 tablas adicionales
   fueron rechazados. La reducción ya instalada no aprueba otras fusiones.
4. Contratos [cognitivo A–D](FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md) y
   [Petersen E–H](FASE_5_CAPTURA_PETERSEN_Y_REPRODUCCION_2026_10_19.md), con
   [matriz ejecutable de reglas y abstenciones](FASE_2_MATRIZ_MAESTRA_INFERENCIA_2026_10_20.md).
5. [Preparación compuesta](FASE_6_PREPARACION_COMPUESTA_2026_10_20.md),
   [brecha relacional](FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md),
   [estabilización](FASE_0_1_ESTABILIZACION_2026_10_20.md) e
   [interfaz y explicabilidad](../frontend/ORION_EXPLICABILIDAD_ENTREGA_2026_10_20.md).

La petición de completar autoriza el desarrollo y la verificación. No equivale
a firmar por un profesional, crear mediciones ausentes, aprobar un nuevo
baremo o instalar una modificación estructural todavía no especificada.

## 2. Resultado y estados que deben distinguirse

| Estado | Significado y acción autorizada |
|---|---|
| Original registrado | Existe una aplicación, respuesta o valoración original con residente, autor, fecha y procedencia. Puede requerir rectificación por su mecanismo propio. |
| Consulta al corte | Se calculó desde las fuentes autorizadas disponibles. `persistido=false`; no se presenta como evaluación compuesta histórica guardada. |
| Abstención | Faltan antecedentes, hay contradicción, gobierno incompatible o calidad insuficiente. Se explican los motivos; no significa normalidad. |
| Evaluación conservada | Solo se declara para un contrato que realmente guarda, recupera y verifica sus entradas. Hoy existen alcances D137, observacional y simulación; no se extiende su garantía al compuesto institucional pendiente. |
| Revisión profesional | Una cuenta competente registra su decisión, justificación y disciplina sobre una evaluación revisable concreta. No modifica el resultado original ni firma por la otra disciplina. |
| Rectificación | Se crea una sucesora enlazada con motivo, identidad del autor y fecha. El original conserva su historia. |

`ACTIVO` en una regla, permiso de lectura IADL, un puntaje aislado, observación
descriptiva o aprobación técnica de tests no equivalen a aprobación clínica.

## 3. Personas, permisos y responsabilidades

Medicina y Psicología utilizan las capacidades existentes de consulta,
captura y gobierno según permiso explícito, profesión activa y Policy del
residente. La lectura IADL mantiene su permiso específico y contexto. Ese
permiso no acredita C03 ni autoriza firmar una valoración de otro profesional.

Superadministración conserva lectura técnica; no obtiene escritura clínica
por su rol. Enfermería consulta únicamente lo permitido por su asignación y
turno; no recibió lectura IADL ni gobierno clínico por este incremento.
Gerencia y Administración tampoco reciben esas capacidades. Familiar queda
limitado a las funciones autorizadas para sus residentes vinculados.

Cada interacción relevante revalida cuenta ACTIVO, permiso, Policy, alcance,
estado del residente y competencia. Ocultar un botón no sustituye el control
del backend. No se crean cuentas, personal ni residentes para completar FK.

## 4. Manual de operación

### 4.1 Registrar y consultar originales

1. Entrar con la cuenta personal y abrir el residente dentro del alcance.
2. Registrar la aplicación o valoración en su formulario original. Identificar
   instrumento y edición reales, profesional, fecha clínica, atención y
   documento/localizador cuando corresponda.
3. Capturar los brutos por la pregunta y canal correctos: número, lógico,
   texto u opción. No completar un valor ausente con cero o FALSE.
4. Atender los mensajes de validación. Un intento fallido no se considera
   guardado ni debe dejar una operación parcial.
5. Si hay error en un original, utilizar la rectificación/anulación admitida;
   no borrar historia ni sustituir el autor mediante entrada manipulada.

La transcripción técnica posterior al corte no se presenta como disponible
en aquel corte. Un original legado sin fecha técnica conserva esa ausencia;
el sistema no puede demostrar retrospectivamente un estado que no registró.
Documentos posteriores, ajenos o no verificables no se convierten en soporte.

### 4.2 Configurar un perfil instrumental válido

Los contratos de servidor se encuentran en `config/sistema_experto.php`:
`perfiles_instrumentales`, `perfiles_interpretativos` y
`contratos_conocimiento`. No son campos libres enviados por el navegador.

Un perfil necesita identidad exacta de instrumento, forma, edición/protocolo
e idioma; método y versión; selectores de preguntas y canales; medidas brutas
requeridas; componente y predicados interpretativos aplicables. Sus fuentes,
población, edad/escolaridad, confusores y derechos deben corresponder al
método efectivamente administrado. La selección por parecido de nombre o por
la primera fila disponible es inadmisible.

Los arrays vacíos por defecto no se rellenan automáticamente desde una
bibliografía o fixture. La investigación aporta métodos, no demuestra qué
prueba recibió un residente. Las 286 aplicaciones observacionales conciliadas
mantienen su significado descriptivo y no se transforman en medición objetiva.

### 4.3 Gobierno y revisión de reglas

En **Conocimiento y reglas**, Medicina/Psicología puede registrar los paquetes
adoptados mediante las acciones existentes. El registro es atómico e
idempotente y no firma previamente sus revisiones.

- `ORION-COGNITIVO-1`: 17 reglas, 15 G09 y dos de Memoria.
- `ORION-PETERSEN-1`: cuatro patrones PET-01 a PET-04.

Probar condiciones no crea una evaluación de residente. Revisar, crear una
sucesora y archivar conservan autor, motivo y versión. Una regla rechazada o
fuera de vigencia no participa en nuevas emisiones conforme al gobierno.
Una versión ambigua o ausente produce un resultado explícito de
indisponibilidad/abstención, sin elegir un paquete de laboratorio.

### 4.4 Consultar el resultado y su explicación

Abrir el informe del residente y comprobar primero su alcance, fecha de corte
y versión. Revisar conclusión/abstención, cinco dominios, originales admitidos
y excluidos, condiciones pendientes y reglas que realmente participaron.

El explorador y los grafos representan relaciones conservadas o conocimiento
de la versión identificada. No completan una traza histórica con valores
actuales ni convierten una relación conceptual en una conclusión clínica.
El historial conserva la evaluación seleccionada al abrir detalle o revisión.
Los cuidados se consultan con sus permisos propios y mantienen su agenda;
una inferencia no ejecuta un cuidado, diagnóstico, prescripción o alerta.

### 4.5 Revisar y rectificar una evaluación

La revisión posterior solo está habilitada si el resultado posee un contrato
revisable y soportes recuperables. Medicina y Psicología mantienen cadenas
separadas. El profesional registra CONFIRMADA o RECHAZADA y justificación;
la autoría se deriva de su sesión y personal activo. Una cabeza de revisión
desactualizada exige recargar, sin perder ni sobreescribir la firma anterior.

La captura original de un juicio Petersen y la revisión posterior de una
evaluación son operaciones distintas. Ninguna sustituye la valoración
independiente de la otra disciplina ni el estudio de desempeño clínico.

## 5. Contrato de inferencia

La [matriz maestra](FASE_2_MATRIZ_MAESTRA_INFERENCIA_2026_10_20.md) contiene
cada identificador, variable, antecedente y consecuencia. Es el catálogo de
esta entrega; esta sección resume su funcionamiento.

Las 15 G09 exigen C1 ∧ C2 ∧ C3 dentro de una misma aplicación: dificultad
interpretada con perfil y brutos, atribución exacta al componente y contexto
de aplicación/confusores. No se combinan condiciones parciales de diferentes
aplicaciones para aparentar una conjunción. Una consecuencia específica G09
no equivale a un diagnóstico o al estado global de un dominio.

Memoria aplica su compuerta y resolvedor D131. Las observaciones contextualizan;
no acreditan por sí solas una interpretación instrumental. Se conservan
contradicciones y faltantes; no se inventan voto, mayoría o puntuación global.

Petersen exige los cinco dominios interpretables y C01–C05 sustentados:
cambio corroborado, alteración objetiva, independencia instrumental relativa,
evaluación diferencial y calidad del conjunto. Las cuatro distribuciones
orientativas son amnésico/no amnésico de dominio único/múltiple. Los requisitos
no acreditados y conflictos producen abstención explicada. BADL, IADL aislada,
un z o la ausencia de diagnóstico no sustituyen esos requisitos.

No se añaden pesos, probabilidades, severidades, umbrales universales ni
diagnóstico/tratamiento autónomo. Tales cambios exigirían otra metodología
institucional aprobada y evidencia independiente.

## 6. Arquitectura y conservación

El motor puro reside en `motor-experto`; Laravel integra adquisición,
autorización, transacciones, presentación y persistencia en `ApoyoDecision`.
`ConsultarConocimientoCognitivo` y `ConsultarPetersenInstitucional` construyen
las dos ramas. `PrepararEvaluacionInstitucional` comparte la captura y valida
residente, actor, corte, versión y mediciones coincidentes.

Los DTO canónicos son `INSTRUMENTAL-DECLARATIVO-3`,
`PETERSEN-ORIGINALES-1` y `EVALUACION-INSTITUCIONAL-1`. Preservan entradas,
ausencia frente a NULL, listas vacías, orden, preguntas sin respuesta,
perfiles, gobierno observado, exclusiones y constancias. Las salidas deben
recalcularse desde esas entradas y el ejecutor original, nunca reemplazarlas.

El backend institucional todavía prepara el compuesto en memoria con
`persistido=false` y `release_retenido=false`. El escritor del laboratorio
no es un escritor institucional ni un permiso de publicación clínica.

### 6.1 Retención y recuperación del software

El release conserva únicamente software, red documental y manifiestos por
huella. El ZIP no contiene DTO, expedientes, `.env` ni configuración privada.
La copia del motor por sí sola no conserva una evaluación. El futuro vínculo
duradero debe conservar identidad del release y todas las entradas relacionales.

El laboratorio `prototipos/orion-conservacion-0_2` añade runtime Windows,
adaptador y punto de entrada retenidos, con copias primaria/respaldo. Su
recuperación se valida por separado. No se afirma portabilidad binaria fuera
de Windows ni validez clínica desde un replay técnico.

El registro de software y sus huellas esperadas deben proceder de un origen
confiable independiente del archivo que se comprueba. Las entradas de la
evaluación se transmiten por memoria/stdin, sin incorporarlas a los artefactos
de software. Una copia ausente, alterada o de otro ejecutor produce error;
no se sustituye por el checkout actual.

### 6.2 Reducción y bloqueo estructural

El [ensayo anterior](ENSAYO_CONSERVACION_REDUCIDA_FASE_6_2026_10_20.md) contiene
42 relaciones de laboratorio; su prueba de contenido no acredita un esquema
institucional de 100 tablas. No se instalan en la base local.

Se estudia un diseño de **104 − 8 consolidaciones + 4 relaciones nuevas =
100**. Debe demostrar granos nominales cerrados, referencias reales, 4FN,
transición poblada y reversión. Los contratos adquiridos no pueden omitirse
por conveniencia ni regenerarse desde configuración futura. El número
aritmético no equivale a un diccionario aprobado o a suficiencia probada.

Hasta disponer de ese diccionario y su aprobación específica, quedan aislados
DDL, escritor/lector institucional compuesto y sus acciones de guardado,
historial y revisión dependientes. No se guarda el DTO en JSON/EAV, campos
observacionales, ZIP clínico o un archivo paralelo para sortear el modelo.

## 7. Aceptación técnica ejecutada en este trabajo

| Selección | Resultado | Límite |
|---|---|---|
| Captura, reglas institucionales, Petersen, permisos, DTO compuesto, perfiles, releases, revisiones, navegación y explorador | SQLite: **256 pruebas / 2663 aserciones, PASS**. PostgreSQL desechable: **256 / 2662, PASS** | Flujo y contratos actuales; no writer institucional compuesto inexistente. |
| Frontend del explorador | **2 pruebas, PASS** | Distribución/ciclo de gráfica; no acredita por sí solo QA visual de cinco tamaños. |
| Build | `npm run build`, **PASS**, Vite 8.3.1 | Compilación; no firma clínica ni despliegue. |
| Backend del laboratorio, revisión independiente posterior al launcher retenido | **1 prueba / 17 aserciones, PASS** | Datos sintéticos; conserva en esquema aislado, no en evaluaciones institucionales. |
| Corpus del laboratorio posterior al launcher retenido | **141 casos / 186 aserciones, PASS** | Requiere repetir el caso interpretativo tras corregir su selección exacta. No prueba el diseño de 100. |

Las selecciones se solapan; no se suman sus totales. PostgreSQL usa base,
runner y cluster propios en loopback, detenidos y eliminados. No se ejecuta
`migrate:fresh` en `remembermind_dev`. La evidencia queda en
`storage/app/private/entrega-integral-orion`, ignorada por Git.

Se preservaron los numerosos cambios locales previos de interfaz, cuidados,
tests y estabilización. Su snapshot inicial está en esa carpeta privada;
no se atribuye su autoría a este incremento.

## 8. Condiciones de cierre

- [x] Revalidar el flujo y contratos actuales en SQLite y PostgreSQL.
- [x] Compilar frontend y comprobar las pruebas del explorador.
- [x] Reconocer la brecha del guardado compuesto sin fingir persistencia.
- [ ] Cerrar y probar el diccionario compatible con 100 tablas.
- [ ] Aprobar específicamente esa modificación del modelo congelado.
- [ ] Implementar, migrar y probar guardar → recuperar → reproducir del
  compuesto institucional con transacción, idempotencia, inmutabilidad y
  permisos actuales; verificar cambios de fuentes/reglas y concurrencia.
- [ ] Conectar su informe, historial y las dos revisiones posteriores con
  distinción de estados y QA visual/funcional real.
- [ ] Resolver por los profesionales las revisiones y perfiles interpretativos
  concretos pendientes; registrar la evidencia de validación clínica.
- [ ] Consolidar gates finales, documentación, commit de alcance y revisión
  para integración. Push y despliegue requieren instrucción explícita.

La entrega no se declara completa mientras queden condiciones esenciales sin
cumplir. Las aprobaciones humanas pendientes no se precargan ni se sustituyen
por una suite verde.

## 9. Repetición de la aceptación y recuperación

Ejecutar desde la raíz del repositorio. La selección siguiente verifica el
flujo actual y sus fronteras; no instala conservación compuesta:

```powershell
$filtroOrion = 'ConocimientoCognitivoInstitucionalTest|CapturaInstrumentalInstitucionalTest|CapturaPetersenOriginalTest|GobiernoPetersenInstitucionalTest|PermisosPetersenOriginalTest|PermisosLecturaIadlPetersenTest|InstantaneaEvaluacionInstitucionalTest|InstantaneaInstrumentalCompletaTest|PerfilInstitucionalAdoptadoTest|PerfilInstrumentalInstitucionalTest|PetersenInstitucionalTest|ReleaseMotorInstrumentalTest|AlmacenReleasesMotorTest|RevisionValoracionExpertaTest|NavegacionSistemaExpertoTest|ExploradorOrionTest'
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter $filtroOrion
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro $filtroOrion
node --test tests/Frontend/orion-explorador.test.js
npm run build
```

El runner PostgreSQL debe crear y comprobar su base, usuario y cluster
desechables. No sustituir sus variables por las de desarrollo. El auditor
`php artisan app:auditar-base-datos-remember-mind` consulta el inventario; no
autoriza migraciones pendientes.

La recuperación autónoma tiene su prueba explícita porque `prototipos/` no
forma parte del descubrimiento ordinario de PHPUnit:

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never prototipos/orion-conservacion-0_2/ArtefactoAutonomoTest.php
```

Este test crea un directorio temporal propio, retiene solo software y ejecuta
una copia de `restaurar-offline.ps1` fuera del checkout. No inicia Laravel en el
subproceso ni consulta SQL. Los casos son sintéticos; las entradas circulan por
stdin binario y las salidas por stdout. No usar una historia real como fixture.
La limpieza comprueba padre resuelto y nombre aleatorio antes de retirar el
directorio temporal.

Para una recuperación operativa futura hacen falta tres recursos separados:
entradas históricas relacionales recuperadas, registro de software confiable
con sus huellas y respaldo de los artefactos. El bootstrap recibe la ruta de
la copia, el registro y su SHA-256 de origen confiable, un destino nuevo y las
tres huellas esperadas (compuesta, instrumental y Petersen). Ninguna huella se
debe tomar del propio archivo sospechoso como prueba de autenticidad. Un
fallo deja la restauración rechazada: no se usa el motor actual como sustituto.
Este procedimiento técnico aún no constituye un recorrido institucional
guardado→recuperación, porque falta su conservación relacional aprobada.

## 10. Evidencia exigida para la aceptación profesional

No existe en esta entrega una firma ni una muestra profesional fabricada. El
equipo competente debe identificar el perfil y material efectivamente
adoptados, su población y condiciones, y registrar la revisión mediante su
cuenta. La firma de reglas y la revisión de una evaluación son actos distintos.

Para cada caso autorizado, registrar: identificador de caso protegido, versión
de conocimiento, perfil y release, corte/adquisición, originales y exclusiones,
resultado del motor con explicación, valoración independiente por disciplina,
desacuerdo y resolución trazable. La referencia profesional se obtiene sin
copiar la conclusión del motor. El expediente clínico permanece en sus medios
institucionales autorizados; este documento público del repositorio no aloja
casos identificables ni respuestas de residentes.

La selección de casos, tamaño de muestra, métricas y criterio clínico de
aceptación requieren un protocolo aprobado. No se fijan porcentajes, Kappa,
sensibilidad o especificidad desde las fixtures. Un caso completo debe
permitir comprobar una conclusión sustentada; un caso incompleto debe explicar
su abstención. Se revisan además contradicción, fuente ajena/futura/anulada,
permisos insuficientes y desacuerdo profesional conforme a los contratos
vigentes. Solo se declara aceptación cuando la evidencia y las firmas existen.
