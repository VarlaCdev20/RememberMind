# Fase 4 — Integración del contrato institucional A–D

Fecha: 19/10/2026. Alcance: implementación y verificación del contrato adoptado por la propietaria; no equivale a validación clínica ni a conservación integral de evaluaciones.

## Decisión que gobierna esta entrega

La propietaria aprobó explícitamente el [contrato A–D](PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md). Las 286 entradas conciliadas son observaciones clínicas por componente. Su realidad no las transforma en medidas de una prueba estandarizada. La [fase 2](DICTAMEN_CIERRE_FASE_2_2026_10_19.md) conserva su conciliación y la [fase 3](CIERRE_TECNICO_FASE_3_2026_10_19.md), los métodos de cálculo investigados.

Esta entrega añade un paquete institucional independiente de las fixtures: `ORION-COGNITIVO-1`, cinco dominios, diecinueve componentes, quince reglas G09 y dos reglas de Memoria. No añade tablas, columnas, permisos, dependencias, registros de residentes ni mediciones. Mantiene la estructura relacional y el inventario vigente de 106 tablas de negocio.

## Checklist funcional

- [x] Paquete institucional declarativo, generado desde la red documental cotejada, sin importar el paquete experimental.
- [x] Cinco dominios y diecinueve pertenencias primarias; EJE-S03 permanece en Función ejecutiva.
- [x] Diecinueve variables observacionales contextuales sin participación en antecedentes instrumentales.
- [x] Quince reglas G09 con tres antecedentes cada una y consecuencias específicas; dos reglas de Memoria reservadas a D-131.
- [x] Tipos, identidad de aplicación/pregunta/opción, corte, autor y edad en la aplicación de 60 años o más.
- [x] Registro atómico e idempotente por Medicina/Psicología, con auditoría y revisión profesional PENDIENTE.
- [x] Selección de la familia adoptada y su única sucesora vigente; ausencia o ambigüedad explícitas, sin elegir una versión de laboratorio.
- [x] AND por aplicación; atención compartida agrupa soportes sin juntar condiciones parciales.
- [x] Compuerta de contexto por tarea de Memoria; una aplicación incompleta no invalida otra completa.
- [x] Contradicciones conservadas y conclusión suspendida; sin mayoría, ganador ni puntuación global.
- [x] Perfil interpretativo completo y medidas originales de la misma aplicación; un nombre de perfil no basta.
- [x] Traza de regla, condición, variable, componente, dominio, fuente, método y medida.
- [x] Archivo mediante sucesora con reproducción del resultado anterior independiente del SQL y configuración actuales.
- [x] Consulta de observaciones y antecedentes en el perfil existente; registro del paquete desde el gobierno existente.
- [x] Registrar la base en la instalación mediante la sesión de Psicología iniciada por la propietaria: cinco dominios, 19 componentes, 17 reglas y 19 mapeos; revisiones pendientes. [Registro y evidencia posterior](FASE_4_REGISTRO_Y_VERIFICACION_UI_2026_10_19.md).
- [x] Comprobar visual y funcionalmente el gobierno en navegador habilitado, corregir el recorte de selectores y verificar los cinco tamaños previstos. PASS acotado al recorrido documentado; el cierre global de interfaz permanece en fase 7.

El checklist distingue el software del estado de una instalación concreta. La base quedó registrada el 19/10/2026 y ya se utiliza en la consulta autorizada. Esto no significa que sus residentes hayan obtenido nuevas clasificaciones instrumentales o Petersen.

## Observaciones de los diecinueve componentes

`FuentesObservacionalesCognitivas` localiza únicamente las identidades naturales exactas ya cotejadas: instrumento `TEST-{componente}`, edición `G08-TEST-v1`, tipo actual OBSERVACIONAL, pregunta `ITEM-{componente}` y dominio declarado correspondiente. Dos coincidencias son un error; una ausente no se inventa. El catálogo inactivo no se reactiva por consultar su historia.

El paquete almacena sus selectores en las tablas expertas existentes. El adaptador exige que opción y literal pertenezcan a esa pregunta y coincidan exactamente. Solo se adoptan estas correspondencias:

| Literal original | Hecho descriptivo | Límite |
| --- | --- | --- |
| `SIM_DIFICULTAD` | `DIFICULTAD_DOCUMENTADA` | Sin grado de severidad, puntuación ni diagnóstico |
| `SIM_SIN_DIFICULTAD` | `SIN_DIFICULTAD_DOCUMENTADA` | No demuestra preservación cognitiva |
| NULL / valor desconocido | Ausencia / valor no reconocido | No se completa ni se considera normalidad |

Se conservan la aplicación, pregunta, respuesta, opción, literal, fecha y autor original. Las observaciones se presentan simultáneamente. Su relación `CONTEXTUALIZA` no habilita participación inferencial: ni G09 ni D-131 pueden consumirlas como si fueran instrumentos.

## Reglas y requisitos de emisión

Las quince reglas y sus consecuencias son las de la tabla B del [contrato adoptado](PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md). Cada una exige `C1 AND C2 AND C3` de la misma aplicación del componente. La ausencia de un antecedente explica la no emisión; no genera una conclusión negativa equivalente a normalidad.

| Grupo | Reglas | Consecuencias |
| --- | --- | --- |
| Atención | R-ATE-S01/S02/S03-P01 | Sostenimiento, selección, distribución |
| Función ejecutiva | R-EJE-S01/S02/S03/S04/S05-P01 | Inhibición, flexibilidad, actualización, planificación, razonamiento |
| Lenguaje | R-LEN-S01/S02/S03/S04/S05-P01 | Denominación, fluidez, comprensión, repetición, discurso |
| Visuoespacial | R-VIS-S01/S02-P01 | Percepción, construcción |
| Memoria | MEM-DIFICULTAD / MEM-SIN-DIFICULTAD | Dificultad / sin dificultad evidenciada, después de EV-CM-2 |

Las reglas se registran ACTIVO con revisión PENDIENTE, tal como permite la decisión de desarrollo. Actividad y firma profesional son estados distintos. Una revisión RECHAZADA o el archivo excluyen la regla de nuevas emisiones según el gobierno existente. No se atribuye aprobación a un profesional por haber registrado el paquete.

### Contrato interpretativo exigido

El mapa de servidor `sistema_experto.perfiles_interpretativos` permanece vacío por defecto. La investigación bibliográfica no completa resultados históricos. Un vínculo puede identificar un contrato del servidor, pero un identificador inexistente o una cadena arbitraria son inadmisibles.

Antes de admitir un predicado, se comprueba un contrato íntegro con:

- código y versión; componente exacto;
- instrumento y edición coincidentes con la captura original;
- método y fuente identificables, y predicados a los que aplica;
- selectores de las medidas numéricas originales requeridas;
- respuestas brutas de esas preguntas y de la misma aplicación, con identidad, tipo numérico, canal exclusivo y valor disponible/finito.

El contrato y las respuestas originales se conservan en el DTO y la traza. La selección de un perfil no calcula automáticamente dificultad a partir de z, no adopta un umbral universal y no certifica que una bibliografía corresponda a la prueba de un residente. El antecedente interpretado debe existir en una fuente explícita del método. Los perfiles clínicos específicos no definidos siguen sin acreditarse.

### Memoria, episodios y conflicto

Una observación de Memoria disponible lleva a EV-CM-1; no produce D-131. La contribución instrumental exige, en su propia aplicación, componente validado, condiciones conocidas y confusores revisados. Se excluye solo la contribución que carece de contexto, conservando su identificador y motivo. Las demás se evalúan de nuevo.

Una FK de atención verificada para el mismo residente forma un grupo compartido de soporte. La conjunción sigue exigiendo una aplicación completa. Sin una atención común explícita no se agrupa por fecha. El número de aplicaciones no acredita independencia clínica; el DTO lo declara expresamente. Hallazgos opuestos de un mismo soporte suspenden la conclusión. No se introduce una ventana de caducidad clínica: se conserva y aplica el corte autorizado.

## Integración y uso

1. Medicina/Psicología entra en **Conocimiento y reglas**, ruta existente `/admin/sistema-experto/reglas`, y usa **Registrar base institucional adoptada**. La acción vuelve a comprobar cuenta activa, permiso y competencia en backend. Superadministración conserva consulta.
2. El registro inserta cabecera y las doce relaciones de conocimiento existentes en una transacción, con códigos correlativos y Activitylog. Un fallo revierte todo; repetir la operación no duplica el paquete.
3. La consulta de resultados usa la configuración explícita cuando existe; en su ausencia busca exclusivamente la raíz adoptada y su familia. Varias revisiones vigentes producen error, sin selección arbitraria.
4. Una sola captura autorizada alimenta hechos, métricas y perfil. Si no hay fuentes suficientes, los cinco dominios explican sus antecedentes faltantes. La pantalla conserva las observaciones y los métodos disponibles.
5. Probar condiciones, crear sucesora, sustituir, archivar y revisar reutilizan el gobierno actual. La prueba manual del gobierno no crea una evaluación de residente.

La ruta se cotejó en `ApoyoDecision/Presentacion/Rutas/web.php` y su grupo `/admin` en `routes/web.php`. No se creó una ruta paralela ni se amplió el acceso de Enfermería a la gestión del conocimiento.

## Reproducción y límites de conservación

La nueva preparación en memoria usa `INSTRUMENTAL-DECLARATIVO-2`: incluye atención original, unidad de conjunción y contrato interpretativo con sus medidas. `INSTRUMENTAL-DECLARATIVO-1` continúa verificándose con su formato histórico; la fixture anterior mantiene la huella `d96661ed285c7b257fcc7a87df0768f8555be6e5e49d59e66d62794aa903d631`.

Ambos DTO permanecen `persistido=false`. La reproducción técnica en memoria no constituye guardar/recuperar íntegro de la evaluación institucional. Eso corresponde a fase 6; Petersen institucional corresponde a fase 5. No se introduce almacenamiento JSON clínico para aparentar resolverlo.

## Verificación

La selección ampliada se ejecuta sobre candidato aislado de `83a490f8` más los archivos propios, sin `.env`, con vendor local y almacenamiento separado por motor. Las pruebas usan datos sintéticos y bases desechables. Los cambios simultáneos de horarios/proveedor modular quedan fuera.

| Verificación ejecutada | Resultado |
| --- | --- |
| SQLite `:memory:`, selección ampliada | **226 pruebas / 6439 aserciones**, cero errores, fallos u omisiones; 4 min 58 s |
| PostgreSQL desechable, misma selección y cinco carreras de gobierno | **231 pruebas / 6498 aserciones**, cero errores, fallos u omisiones; 6 min 40 s; parada confirmada |
| Ampliación final de render de observaciones y hallazgo instrumental | **2 casos / 138 aserciones** en SQLite; incluye la nueva comprobación del HTML con método, perfil, respuestas y motivos. Son repeticiones dirigidas de casos de la selección anterior, no pruebas adicionales para inflar su total |
| Oráculo final del contrato y compatibilidad V1 | **6 casos / 143 aserciones**, PASS; consecuencias esperadas transcritas de la tabla B, independientes del constructor de reglas. Repetición dirigida, no se suma a la selección ampliada |
| Build Vite 8.3.1 del candidato | PASS; 107 módulos, sin cambiar assets de la instalación |
| Independencia del motor | Los dos ejecutables de independencia PASS, sin Laravel ni SQL |
| PHP/Pint | Lint de 21 PHP propios y Pint PASS; `git diff --check` sin errores |
| Candidato y workspace | SHA-256 coincidente de los 23 PHP/Blade del incremento |
| Revisión independiente | PASS técnico y de seguridad acotados; sin P1/P2 pendientes identificados después de las correcciones |
| Navegador | En este corte inicial estaba deshabilitado; el [registro y QA posteriores](FASE_4_REGISTRO_Y_VERIFICACION_UI_2026_10_19.md) documentan su habilitación, el ajuste visual y el PASS del alcance examinado |

La revisión independiente corrigió los bloqueos por contexto, perfil inexistente y medida ambigua. Comprobó además la separación entre observaciones, predicados y DCL, la ausencia de suplantación de autoría y el aislamiento por residente/corte/permisos. No se ejecutó la suite global ni se operó la BDD clínica.

Los reportes privados se conservan en `storage/app/private/fase4-adoptado-{sqlite,postgres}.xml`, `fase4-adoptado-postgres.log`, `fase4-render-institucional-final.xml` y `fase4-adoptado-candidato-verificado.json`. Están excluidos de Git y no contienen fuentes clínicas originales como fixtures.

Comandos reproducibles sobre el candidato sin `.env`, con `LARAVEL_STORAGE_PATH` distinto por motor:

```powershell
$filtro = 'ConocimientoCognitivoInstitucionalTest|PerfilInstitucionalAdoptadoTest|ReproduccionFuenteNominalTest|AdmisibilidadObservacionalTest|PerfilCognitivoDeclarativoTest|IntegracionRedCognitivaTest|GobiernoReglasExpertasTest|SucesionObservacionalTest|CapturaInstrumentalCanonicaTest|AplicabilidadGeriatricaTest|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|ApplicationBoundaryTest|FolderOrganizationTest|MapeadorReglasTest|InformeCognitivoTest'
php vendor/phpunit/phpunit/phpunit --filter $filtro --log-junit storage/app/private/fase4-adoptado-sqlite.xml
./scripts/probar-postgres.ps1 -Filtro ($filtro + '|GobiernoReglasConcurrenciaPostgresTest') -Informe storage/app/private/fase4-adoptado-postgres.xml -Log storage/app/private/fase4-adoptado-postgres.log
php motor-experto/tests/independencia.php
php motor-experto/tests/orion_independencia.php
npm run build
```

## Trazabilidad principal

| Requisito | Componente | Evidencia ejecutable |
| --- | --- | --- |
| A: observaciones fieles | PaqueteCognitivoInstitucional / FuentesObservacionalesCognitivas | ConocimientoCognitivoInstitucionalTest: diecinueve componentes, literales, autor y no activación G09 |
| B: quince AND y método explícito | AdmisibilidadCognitivaInstitucional / EvaluadorReglasCognitivas | PerfilInstitucionalAdoptadoTest: cada G09 con C1/C2/C3 y cada ausencia; Feature SQL de perfil/brutos |
| C: Memoria por tarea y episodio | EvaluarPerfilInstitucional / ConstructorMemoriaInstrumental | Contexto de otra aplicación, una incompleta más otra válida, soporte compartido y contradicción |
| D: gobierno sin firmas ficticias | RegistrarConocimientoInstitucional / GobiernoReglasPanel | Registro por Medicina/Psicología, Superadmin denegado, cuenta/profesión, rollback y Livewire |
| Historia | ContratosConocimiento / InstantaneaInstrumental | Sucesora archivada, originales SQL modificados, replay V2 y huella V1 |
| Explicación | TrazaFuentesInstrumentales / ConsultarPerfilInstrumental | Método, respuestas brutas, aplicación, autor, corte y perfil integrado |

La aceptación técnica no afirma sensibilidad, especificidad ni validez diagnóstica. Esas métricas requieren una muestra y una evaluación clínica con protocolo propio; no se deducen del número de tests ni de los casos usados para programar.
