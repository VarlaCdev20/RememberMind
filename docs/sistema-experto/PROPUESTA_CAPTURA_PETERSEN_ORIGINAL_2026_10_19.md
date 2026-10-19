# Captura original de Petersen: extensión concreta propuesta

19/10/2026. **APROBADA por la propietaria: «Sí, aprobar las dos tablas y permisos».**
Implementación instalada en PostgreSQL local `remembermind_dev` el 19/10/2026.
La [acta y evidencia del incremento](FASE_5_CAPTURA_PETERSEN_Y_REPRODUCCION_2026_10_19.md)
distingue pruebas técnicas, QA pendiente y conservación de fase 6 aún abierta.
Contenido clínico: E–H adoptado en
[Petersen institucional](PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md).
Esta decisión estructural y de permisos es distinta de aquella adopción.

## Problema, reutilización y alcance

La BDD identifica pruebas aplicadas, respuestas, IADL y documentos. No representa
el juicio original integrado por dominio/C01/C03/C04/C05 ni sus enlaces a varios
soportes originales. `revisiones_evaluacion_experta` es la revisión posterior del
resultado del motor; usarla como entrada confundiría actos diferentes. Tampoco
corresponde guardar esos vínculos como códigos escritos en texto, observaciones
libres o filas genéricas campo/valor.

Se proponen **dos relaciones clínicas**, compartidas por todos los dominios y
requisitos. Se reutilizan las tablas originales, documentos, usuarios, personal,
atenciones y revisiones posteriores. C02 y el patrón son salidas calculadas: no
hay una columna para introducir el resultado DCL que se desea obtener.

El inventario pasaría de **73 operativas + 33 expertas = 106** a
**75 operativas + 33 expertas = 108 tablas de negocio**. Estas dos relaciones no
son las nueve candidatas de conservación instrumental, que siguen sin aprobarse.
La captura original no equivale a conservar la ejecución completa de fase 6.

## 1. `valoraciones_petersen_originales` — una valoración original de un elemento

| Columna | Tipo / nulabilidad | Relación o significado |
|---|---|---|
| cod_valoracion_petersen | string(20), PK | Prefijo correlativo técnico VPO |
| solicitud_uuid | string(36), requerida, UNIQUE | Identidad de solicitud generada por el servidor; reintento del mismo acto |
| cod_residente | string(20), requerida | FK residentes.cod_residente |
| cod_personal | string(20), requerida | FK personal.cod_personal; profesional clínico propio |
| cod_usuario_registro | string(20), requerida | FK usuarios.cod_usuario; cuenta autenticada |
| cod_atencion | string(20), nullable | FK atenciones.cod_atencion; mismo residente |
| cod_documento_clinico | string(20), nullable | FK documentos_clinicos.cod_documento_clinico; original identificado si existe |
| localizador_fuente | string(120), nullable | Página/sección; pareja con documento |
| cod_valoracion_anterior | string(20), nullable | Self-FK restrictiva; rectificación/anulación enlazada |
| tipo_registro | string(15), requerida | REGISTRO, RECTIFICACION o ANULACION |
| elemento | string(10), requerida | COG-MEM/ATE/EJE/LEN/VIS o C01/C03/C04/C05 |
| estado_original | string(1), nullable | A/P/I para dominio; S/N para requisito; NULL solo en ANULACION |
| justificacion | text, requerida | Juicio original o razón de la anulación; no vacía |
| fecha_hora | datetime(6), requerida | Instante clínico, zona institucional America/La_Paz |
| registrado_en | datetime(6), requerida | Instante técnico del servidor, separado del clínico |
| alcance_dominio | boolean, nullable | Solo dominios |
| contexto_interpretado | boolean, nullable | Solo dominios |
| cambio_respecto_previo | boolean, nullable | Solo C01 |
| corroboracion_independiente | boolean, nullable | Solo C01 |
| revision_integrada | boolean, nullable | Solo C03 |
| oportunidad_adecuada | boolean, nullable | Solo C03 |
| atribucion_revisada | boolean, nullable | Solo C03 |
| revision_explicita | boolean, nullable | Solo C04 |
| factores_pertinentes_revisados | boolean, nullable | Solo C04 |
| impedimento_no_resuelto | boolean, nullable | Solo C04; NULL no equivale a false |
| suficiencia_conjunto | boolean, nullable | Solo C05 |
| condiciones_revisadas | boolean, nullable | Solo C05 |
| exclusiones_revisadas | boolean, nullable | Solo C05 |

Todas las FK indexadas y restrictivas; índices `(cod_residente,fecha_hora)` y
`(cod_residente,elemento,fecha_hora)`; UNIQUE `cod_valoracion_anterior` cuando no
es NULL para impedir ramas de corrección. Checks: elemento/estado/tipo válidos;
REGISTRO sin antecesora, RECTIFICACION/ANULACION con antecesora; documento y
localizador juntos; booleans ajenos al elemento en NULL; ANULACION sin estado ni
comprobaciones. No timestamps automáticos ni soft deletes.

CHECK antecesora distinta de la propia PK. Clave alternativa
`(cod_valoracion_petersen,cod_residente,elemento)` y self-FK compuesta
`(cod_valoracion_anterior,cod_residente,elemento)` garantizan pertenencia de la
cadena. La Action bloquea su cabeza vigente, rechaza ciclos y exige orden
técnico de sucesión. La rectificación conserva la fecha clínica del acto
corregido; corregirla exige identificar expresamente el error de fecha en la
justificación y mantenerla no futura. ANULACION conserva la fecha del acto,
es terminal y no recibe soportes propios; una valoración posterior es otro
REGISTRO. Su efecto solo existe desde su instante técnico de registro.

El reintento con `solicitud_uuid` idéntica compara el contenido autorizado y
devuelve el mismo registro; contenido distinto produce conflicto sin escritura.
No se deduplica por residente, fecha o semejanza clínica.

Se conservan NULL y false. La suficiencia clínica la determina E–H, no un NOT
NULL que transforme un formulario incompleto en valoración acreditada. La
Action exige mismo residente/elemento en antecesora, fecha no futura y soporte
coherente; bloquea la cadena y deriva cuenta/profesional de la sesión.

## 2. `soportes_valoracion_petersen` — un vínculo original tipado por valoración

| Columna | Tipo / nulabilidad | Relación o significado |
|---|---|---|
| cod_soporte_petersen | string(20), PK | Prefijo correlativo técnico SVP |
| cod_valoracion_petersen | string(20), requerida | FK a la cabecera propietaria valoraciones_petersen_originales.cod_valoracion_petersen |
| tipo_soporte | string(25), requerida | HISTORIA_PREVIA, CORROBORACION, MEDICION, IADL, DIFERENCIAL, CALIDAD o DOMINIO_REVISADO |
| orden | unsigned integer, requerida | Orden original de presentación, desde cero |
| cod_aplicacion | string(20), nullable | FK aplicaciones_instrumento.cod_aplicacion |
| cod_control_cognitivo | string(20), nullable | FK controles_cognitivos.cod_control_cognitivo |
| cod_control_iadl | string(20), nullable | FK controles_funcionales_iadl.cod_control_iadl |
| cod_valoracion_psicologica | string(20), nullable | FK valoraciones_psicologicas.cod_valoracion_psicologica |
| cod_antecedente | string(20), nullable | FK antecedentes_clinicos.cod_antecedente |
| cod_diagnostico | string(20), nullable | FK diagnosticos.cod_diagnostico |
| cod_documento_clinico | string(20), nullable | FK documentos_clinicos.cod_documento_clinico |
| cod_valoracion_revisada | string(20), nullable | FK valoraciones_petersen_originales; solo C05 → juicio de dominio revisado |
| metodo_original | string(80), nullable | Solo MEDICION: método exacto del registro del motor |
| huella_perfil_original | char(64), nullable | Solo MEDICION: SHA-256 del contrato aplicado, calculado por servidor |

Check: exactamente una de las ocho FK de fuente no NULL. MEDICION exige
cod_aplicacion, metodo_original y huella_perfil_original; esos dos atributos son
NULL para otros tipos. IADL exige cod_control_iadl. DOMINIO_REVISADO exige
cod_valoracion_revisada y solo lo admite C05. La Action exige mismo residente,
elemento de dominio, original anterior o simultáneo a C05 y ausencia de ciclos.
C05 identifica los cinco juicios concretos; no escoge «el último» por PK o fecha.
Una rectificación posterior de uno de esos juicios no cambia el conjunto ya
revisado; una nueva evaluación requiere nueva valoración C05 si el conjunto cambia.
FK indexadas/restrictivas. UNIQUE `(cod_valoracion_petersen,orden)` y ocho
unicidades `(cod_valoracion_petersen,FK_fuente)` cuando la FK no es NULL.
Sin texto que sustituya un vínculo, JSON, EAV o cascadas.

Matriz cerrada de combinaciones de escritura:

| Elemento | tipo_soporte | FK de fuente admitida |
|---|---|---|
| COG-MEM/ATE/EJE/LEN/VIS | MEDICION | cod_aplicacion |
| C01 | HISTORIA_PREVIA / CORROBORACION | cod_control_cognitivo, cod_valoracion_psicologica o cod_documento_clinico |
| C03 | IADL | cod_control_iadl |
| C04 | DIFERENCIAL | cod_valoracion_psicologica, cod_antecedente, cod_diagnostico o cod_documento_clinico |
| C05 | CALIDAD | cod_aplicacion, cod_valoracion_psicologica o cod_documento_clinico |
| C05 | DOMINIO_REVISADO | cod_valoracion_revisada |

Una fuente tiene un único rol por valoración; no se repite el mismo original
como historia y corroboración independientes. El enlace no acredita contenido.
MEDICION exige comprobar brutos, edición/idioma, condiciones y método aplicable
60+; el hash del perfil evita reinterpretarlo silenciosamente con configuración
distinta. No equivale a un artefacto recuperable: la retención del contrato y
del software sigue en fase 6. Si no hay perfil exacto verificable, se conserva
la entrada incompleta y no se habilita un resultado positivo.

La Action comprueba residente, autor original, fecha, vigencia y permisos de cada
fuente; conserva el orden y no acepta fuentes futuras. No se copian datos de
otro residente para explicar un rechazo. Las fuentes de mera fecha, sin hora o
episodio corroborable, pueden contextualizar: no se les inventan esos valores
para acreditar independencia.

## 4FN e historia

Una fila de valoración tiene un único elemento y un único juicio original; sus
condiciones fijas no constituyen una colección multivaluada. Los soportes forman
otra relación y no se cruzan con mediciones, revisores o resultados. PK y claves
alternativas determinan los atributos de su grano; no hay dos colecciones
independientes en la misma fila. Se conserva la 4FN dentro de este contrato.

Encabezado y soportes son inmutables. Rectificar o anular inserta otra cabecera;
no cambia ni borra la anterior. Solo una sucesora por fila; la cadena se consulta
al corte por fecha clínica y técnica. No hay actualización de estado para ocultar
el original. Activitylog registra la operación sin copiar el payload clínico.

## Autorización concreta propuesta

- `valoraciones_petersen.ver`: Medicina, Psicología y Superadministración, con
  cuenta ACTIVO, permiso y alcance contextual del residente.
- `valoraciones_petersen.crear`: Medicina/Psicología con personal activo de la
  profesión correspondiente, permiso y Policy; autor derivado de la sesión.
- `valoraciones_petersen.rectificar`: las mismas condiciones y roles; justificación
  obligatoria, cadena y fuentes verificadas en transacción. Incluye anulación
  enlazada. No se concede escritura a Superadministración, Enfermería, Gerencia
  ni Administración por su rol.

La facultad propuesta se limita a **cadenas propias**: `cod_personal` de la
antecesora coincide con el profesional autenticado. Un desacuerdo con otro
autor crea una valoración independiente y el motor conserva la discrepancia;
no autoriza anular ni rectificar el trabajo ajeno. El autor de una fuente
histórica puede estar inactivo hoy; quien registra ahora sí debe estar activo.

El primer alcance captura valoraciones nuevas con autor propio. No atribuye
juicios retrospectivos a otros profesionales ni incorpora una transcripción
histórica sin su contrato específico. El profesional puede registrar información
incompleta explícita; el motor se abstiene cuando E–H no se satisface.

## Alternativas y aceptación

Mantener únicamente las fuentes existentes permite preparar el expediente,
pero no capturar las nuevas valoraciones integradas con sus vínculos completos.
Usar un documento firmado como único contenedor conserva un archivo, pero no
permite validar estas relaciones sin lectura manual; no cierra el flujo pedido.
No se propone sustituir relaciones por campos de texto para ahorrar una tabla.

Antes del cierre: captura→lectura→motor con cuatro patrones en fixtures sintéticas;
requisitos incompletos/contradictorios; mismo episodio; fuentes incompatibles;
autorización y cuenta revocada; corrección/anulación sin pérdida; idempotencia,
rollback y concurrencia PostgreSQL. La instalación en desarrollo se verificaría
por separado. Esta aprobación no autoriza reset ni carga de mediciones inventadas.
