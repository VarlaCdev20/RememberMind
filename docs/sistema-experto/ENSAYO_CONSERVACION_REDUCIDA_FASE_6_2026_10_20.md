# Ensayo de conservación institucional reducida

Fecha: 20/10/2026. Estado: **candidato técnico sin aprobación estructural ni
prueba de round-trip**. El inventario instalado permanece en 104 tablas de
negocio. No se ha creado ninguna de las relaciones de este ensayo.

**Límite de alcance comprobado:** catorce relaciones no bastan para conservar
el contrato G09 cuando una regla usa un vínculo instrumental con perfil
interpretativo. `ConocimientoCognitivoInstitucionalTest::test_g09_sql_exige_perfil_definido_y_medida_bruta_de_la_misma_aplicacion`
ejecuta precisamente ese camino. El candidato de 14 solo cubre la variante
observacional fija derivable de mapeos y originales sellados. El diseño reducido
que merece ensayo completo usa **15 granos en total**: la relación adicional
conserva **todos** los vínculos adquiridos, tanto observacionales como
interpretativos. Así la recuperación no depende de volver a ejecutar
`ContratosConocimiento::porVersion()` ni de la configuración futura. El
inventario candidato sería 119 tablas de negocio, sujeto todavía a round-trip
y aprobación. No es un nuevo inventario aprobado ni autorización para DDL.

## Alcance conservado

El objetivo es recuperar y recalcular exactamente INSTRUMENTAL-DECLARATIVO-3,
PETERSEN-ORIGINALES-1 y EVALUACION-INSTITUCIONAL-1 con el release original.
Incluye brutos, NULL, preguntas seleccionadas sin respuesta, versiones,
perfiles, estado de reglas observado, membresías, fuentes, exclusiones,
constancias y orden. Los resultados calculados son salida del replay, nunca
reemplazo de esas entradas.

Este ensayo se limita al contrato institucional A–D/E–H actual. La plantilla
`PaqueteCognitivoInstitucional::contratos()` inicia con vínculos vacíos, pero
`ContratosConocimiento::porVersion()` incorpora los vínculos observacionales
que identifica en mapeos y preguntas originales. En el caso sintético completo
son **19 vínculos**, no cero. Una prueba de integración verificó que se derivan
exactamente desde mapeo, pregunta e instrumento. Para conservar la evaluación,
la relación 15 propuesta guardaría el mapa **efectivamente adquirido** y lo
cotejaría con el V3 antes de finalizar. La misma relación representa perfiles
interpretativos, configuraciones explícitas, mapa vacío y sucesoras sin
regenerar claves desde la versión actual. Los contratos de relaciones y
extracción aún deben coincidir con los del release retenido.

El diseño anterior de 25 relaciones fue rechazado. Se utiliza como
[antecedente analítico de columnas y granos](PROPUESTA_CONSERVACION_COMPUESTA_FASE_6_2026_10_20.md),
sin reactivar su autorización ni su número.

**Condición temporal todavía no resuelta:** `registrado_en` de la aplicación
puede ser NULL en historia anterior a la ampliación; `estado` cambia por
anulación o rectificación y una conciliación posterior puede vincular
`cod_documento_clinico` y `localizador_fuente` a la misma aplicación. Las
respuestas carecen de fecha técnica individual. Una transcripción con
`registrado_en` posterior al corte se excluye ahora de la captura, pero una
fila anterior mutada después del corte no revela por sí sola su estado pasado.
El sello de contenido no puede bloquear esas transiciones operativas actuales:
el guardado debe copiar los tres valores mutables observados, registrar el
instante de adquisición y fallar si pretende afirmar una reconstrucción
histórica anterior no demostrable.

## Reutilización y costo del sellado

| Origen existente | Se reutiliza | Protección pendiente |
|---|---|---|
| Doce relaciones de conocimiento y cabecera de versión | Definición exacta por versión | Sello SQL del conjunto completo. La primera firma y el retiro admitidos se registran por evaluación sin reescribir el pasado. |
| Instrumentos, preguntas y opciones | Metadata original incluso para selector sin respuesta | Sellar instrumento y sus hijos; bloquear inserción, actualización y borrado posteriores. Nueva edición: nuevo instrumento enlazado con antecesor. |
| Aplicaciones y respuestas | Brutos y procedencia | Sellar contenido y colección completa de respuestas, incluido INSERT tardío. Permitir las transiciones autorizadas de estado y la vinculación documental tardía; copiar su valor observado en la membresía de evaluación. Rectificar el contenido por aplicación sucesora. |
| Juicios y soportes Petersen | Contenido ya inmutable | Guardar membresía y orden observados por evaluación. |
| Residente, personal, atenciones y contexto médico/IADL/documental | Identidades originales | Copiar solo los atributos consumidos que pueden cambiar. No congelar personas. |

Los sellos de Eloquent no bastan. Se necesitan triggers equivalentes en
PostgreSQL y SQLite, con bloqueo transaccional al sellar. El primer sello
captura el estado cotejado en ese momento, sin atribuirlo a fechas anteriores.
Estas garantías modifican estructura y reglas físicas; requieren aprobación.

## Inventario candidato de quince relaciones para el alcance institucional

De aprobarse y superar todas las pruebas, el inventario sería **119 tablas
de negocio: 75 operativas y 44 expertas**. Es una reducción frente al
antecedente de 25 relaciones, pero aumenta el costo de triggers y gobierno
de originales; menos tablas no equivale por sí solo a menos mantenimiento.

Las entradas marcadas «mismo detalle» heredan, como especificación candidata,
las columnas, tipos, FK restrictivas, nulabilidad, índices y CHECK de la
relación numerada del antecedente de 25 tablas. No se hereda su estado de
aprobación. Las cuatro entradas cambiadas se precisan después de la tabla.

| N.º | Relación | Grano | Especificación candidata |
|---:|---|---|---|
| 1 | conocimientos_evaluacion_experta | Evaluación y rama cognitiva/Petersen | Relación 2, mismo detalle |
| 2 | estados_reglas_evaluacion_experta | Rama y regla observada | Relación 3, mismo detalle |
| 3 | autores_instantanea_experta | Evaluación y etiqueta del profesional | Relación 7, mismo detalle |
| 4 | aplicaciones_instantanea_experta | Evaluación y aplicación adquirida | Membresía reducida descrita abajo |
| 5 | perfiles_instrumentales_versionados | Evaluación y perfil de captura/interpretación | Unión discriminada de relaciones 13 y 15 |
| 6 | entradas_perfil_instrumental | Perfil y selector/medida | Unión discriminada de relaciones 14 y 17 |
| 7 | predicados_perfil_instrumental | Perfil interpretativo y predicado ordenado | Relación 16 con FK al perfil 5 |
| 8 | valoraciones_petersen_evaluacion | Evaluación y juicio adquirido | Relación 19, mismo detalle |
| 9 | fuentes_petersen_instantanea | Evaluación y fuente contextual mutable | Relación 20, FK de atención a relación 11 |
| 10 | soportes_petersen_evaluacion | Juicio y soporte original | Relación 21, FK de aplicación a relación 4 |
| 11 | atenciones_instantanea_experta | Evaluación y atención observada | Relación 22, mismo detalle |
| 12 | exclusiones_adquisicion_petersen | Evaluación y exclusión ordenada | Relación 23, mismo detalle |
| 13 | motivos_adquisicion_instrumental | Aplicación y motivo ordenado | Relación 24, FK a relación 4 |
| 14 | constancias_documentales_evaluacion | Evaluación y comprobación ordenada | Relación 25, FK de soporte a relación 10 |
| 15 | vinculos_declarativos_instantanea | Conocimiento adquirido y mapeo vinculado | Ambos tipos de vínculo; detalle cerrado abajo, reutiliza la relación 18 del antecedente solo como análisis |

Quedarían fuera las tablas candidatas 1, 4–6, 8–10, 12, 14, 15 y 17
del diseño rechazado: release por huella y archivo privado, contratos
estáticos del release, originales sellados, unión de perfiles sin producto
cartesiano y vínculos conservados por su mapeo adquirido. No se elimina
ninguna tabla o fila instalada.

### Ampliaciones de tablas existentes

- evaluaciones_expertas: cod_usuario_ejecucion string(20) NULL, FK a
  usuarios.cod_usuario; huella_release_motor char(64) NULL;
  fecha_nacimiento_original date NULL; huella_evaluacion_compuesta
  char(64) NULL. El release se localiza por ruta privada determinista
  derivada de la huella, se verifica antes de finalizar y no se envía al
  navegador. Se reutilizan id_evaluacion_dominio, version_conservacion
  y la tripleta fecha_corte ya aprobadas.
- versiones_modelo_experto: huella_definicion_sellada char(64) NULL,
  fecha_hora_sellado datetime(6) NULL y cod_usuario_sellado string(20)
  NULL, FK a usuarios.cod_usuario. Las tres son NULL juntas o no NULL
  juntas; el sello no se reescribe.
- instrumentos: fecha_hora_sellado datetime(6) NULL y
  cod_instrumento_anterior string(20) NULL, FK a su propia PK.
- aplicaciones_instrumento: fecha_hora_sellado datetime(6) NULL.

Toda FK nueva lleva índice, DELETE RESTRICT y UPDATE NO ACTION. Las
evaluaciones previas conservan NULL; no se rellenan retrospectivamente.
La política de retención y respaldo del release aún requiere decisión
institucional: un hash no garantiza que el ZIP exista años después.

### Relación 4: aplicación incluida

PK cod_aplicacion_instantanea string(20); FK obligatoria
cod_evaluacion_experta a evaluaciones_expertas; FK obligatoria
cod_aplicacion a aplicaciones_instrumento; orden_captura integer
no negativo. FK obligatoria cod_autor_instantanea a relación 3;
FK nullable cod_atencion_instantanea a relación 11; FK nullable
cod_fuente_documento_instantanea a relación 9, rama DOCUMENTO;
FK nullable cod_perfil_instrumental a relación 5, tipo CAPTURA.
estado_original_observado string(20), que reconstruye tanto
`estado_original` como `aplicacion_actual.estado` del V3;
cod_documento_clinico_observado string(20) NULL, FK al documento original,
y localizador_fuente_observado string(120) NULL, que reconstruyen
`aplicacion_actual` y `procedencia`. Sus NULL se conservan explícitamente.
Fecha clínica observada: fecha_clinica_utc datetime(6),
fecha_clinica_zona string(80), fecha_clinica_offset integer y
fecha_hora_sql_texto text. Registro técnico observado:
registro_tecnico_utc datetime(6) NULL, registro_tecnico_zona
string(80) NULL, registro_tecnico_offset integer NULL y
registrado_en_sql_texto text NULL. estado_documento string(40).
UNIQUE evaluación+aplicación y evaluación+orden. Trigger comprueba
que autor, atención, documento y perfil pertenecen a la misma
evaluación, y que los tres valores observados coinciden con la fila original
durante la adquisición. La FK documental de la membresía debe coincidir con
la de la fuente documental 9 cuando exista; la ausencia de un documento
recuperable no se inventa. Las demás columnas de aplicación y respuestas se
recuperan de originales sellados; los motivos de adquisición vienen de 13.
El trigger de la aplicación debe limitar las actualizaciones a las formas
de transición de `estado` y vinculación posterior de documento/localizador
admitidas. La autorización contextual sigue en las acciones institucionales;
Eloquent por sí solo no protege las escrituras directas de SQL.
No se permite cambiar brutos, autor, fecha ni colección
de respuestas después del sello. El sello tampoco prueba estados anteriores
a su primera instalación.

### Relaciones 5–7: perfiles de dos variantes cerradas

Relación 5: PK cod_perfil_instrumental string(20), FK evaluación,
tipo string(20) CAPTURA/INTERPRETACION. CAPTURA conserva
id_perfil_original text, metodo string(80), cod_instrumento
string(20) FK, codigo_instrumento string(30),
version_instrumento string(30), identidad_presente boolean,
los cuatro flags declara_instrumento/forma/edicion_protocolo/idioma
y cuatro escalares tipados identidad_instrumento/forma/
edicion_protocolo/idioma de la relación 13 anterior.
INTERPRETACION conserva codigo text, version text, componente
string(30), instrumento_codigo string(30),
instrumento_version string(30), fuente text y metodo compartido.
CHECK impide poblar columnas de la otra variante. UNIQUE por
evaluación+tipo+id_perfil_original para CAPTURA; UNIQUE por
evaluación+tipo+codigo+version+componente para INTERPRETACION.

Relación 6: PK cod_entrada_perfil string(20), FK relación 5;
tipo_entrada string(20) SELECTOR/MEDIDA; nombre string(80);
selector_nulo boolean; identificador_pregunta_declarado text
NULL; cod_pregunta string(20) NULL FK a preguntas_instrumento;
campo string(30) NULL, cerrado a valor_numero/valor_logico.
UNIQUE perfil+tipo_entrada+nombre. SELECTOR requiere CAPTURA y
puede representar NULL explícito; MEDIDA requiere INTERPRETACION,
identificador, campo valor_numero y selector_nulo=false. Un
identificador no resuelto conserva su literal con FK NULL; una
pregunta existente al corte requiere FK y definición sellada.
El SELECTOR también preserva una pregunta sin respuesta.

Relación 7: PK cod_predicado_perfil string(20), FK relación 5
tipo INTERPRETACION; orden integer no negativo, predicado
string(30), UNIQUE perfil+orden. Repeticiones y orden se
conservan; no hay producto cartesiano con los selectores.

### Relación 15: vínculo declarativo adquirido

PK cod_vinculo_declarativo string(20); FK obligatoria
cod_conocimiento_evaluacion a relación 1; FK obligatoria
cod_mapeo_variable_fuente a mapeos_variables_fuente; FK obligatoria
cod_instrumento a instrumentos; cod_pregunta string(20) NULL FK a
preguntas_instrumento.
campo string(30), cerrado a valor_logico/valor_texto;
declara_componente boolean y componente string(30) NULL;
declara_perfil_interpretativo boolean y cod_perfil_instrumental
string(20) NULL FK a relación 5, tipo INTERPRETACION.
UNIQUE conocimiento adquirido+mapeo. Los flags conservan exactamente
la diferencia entre clave omitida y clave presente con NULL en el DTO.
La pregunta inexistente conserva FK NULL; su identificador declarado se
recupera de `mapeos_variables_fuente.clave_selector` sellada. Una pregunta
existente exige que la FK coincida con ese selector y con el instrumento.
`codigo_instrumento` y `version_instrumento` se recuperan del instrumento
sellado, no se repiten en cada vínculo; el escritor coteja ambos con el V3.
Esto evita dependencias funcionales redundantes en el grano de vínculo.
El perfil compartido se guarda una sola vez en 5–7
y se referencia desde varios vínculos sin duplicar sus predicados.
El mapeo y la relación 1 deben pertenecer a la misma versión, comprobado
en la misma transacción; el vínculo de una sucesora usa el mapeo de esa
sucesora, no un alias resuelto desde configuración actual. El escritor
rechaza claves o tipos fuera del mapa cerrado de
`ConstructorMemoriaInstrumental` y coteja cardinalidad, claves y valores
reconstruidos con los vínculos de la instantánea V3 antes de confirmar.
Una colección vacía se representa por cero filas, no por una fila nula.

## Prueba de suficiencia pendiente

1. Proyectar un DTO sintético completo a estas relaciones en memoria,
   reconstruirlo sin SQL actual y exigir igualdad canónica de las
   tres huellas y de cada campo. Probar NULL, lista vacía, pregunta
   sin respuesta, fuentes compartidas y contradicción.
2. Probar triggers de sello/colecciones y sucesoras en SQLite y
   PostgreSQL desechables, incluida concurrencia de captura y
   mutación de un original.
3. Auditar 4FN, FK cruzadas de evaluación/residente, transición
   poblada, rollback y restauración real del ZIP privado.
4. Simular transcripción tardía, anulación/rectificación posterior al corte y
   respuesta insertada después de una consulta. Exigir que el escritor solo
   declare fiel el estado realmente adquirido y no presente ese estado como
   una vista temporal histórica que no puede reconstruirse.
5. Proyectar a la relación 15 todos los vínculos adquiridos, reconstruirlos
   sin `ContratosConocimiento` ni configuración actual y comparar el mapa
   exacto con el V3. La derivación sintética de los 19 vínculos observacionales
   ya pasó, pero **no** prueba esta nueva proyección ni su round-trip.
6. Probar cero/uno/múltiples vínculos, NULL frente a clave ausente,
   perfiles compartidos y sucesión de versiones. No mezclar este grano
   con entradas de perfil ni usar una configuración posterior para replay.
7. Solo con esa evidencia convertir este ensayo en diccionario
   estructural final para aprobación específica de la propietaria.

No se afirma todavía que catorce ni quince sean el mínimo suficiente.
Fase 6 y el informe histórico/aceptación dependientes siguen abiertos.
