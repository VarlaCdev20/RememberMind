# Ensayo de conservación institucional reducida

Fecha: 20/10/2026. Estado: **candidato técnico sin aprobación estructural ni
prueba de round-trip**. El inventario instalado permanece en 104 tablas de
negocio. No se ha creado ninguna de las relaciones de este ensayo.

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
son **19 vínculos**, no cero. Una prueba de integración verificó que se
reconstruyen exactamente desde mapeo, pregunta e instrumento. Esa derivación
solo es segura si quedan sellados el conjunto de mapeos y los originales; antes
de guardar se debe comparar el mapa reconstruido con el V3 adquirido.
Vínculos interpretativos, configuraciones personalizadas o discrepancias fallan
como no representables. Los contratos de relaciones/extracción deben coincidir
exactamente con los del release retenido.

El diseño anterior de 25 relaciones fue rechazado. Se utiliza como
[antecedente analítico de columnas y granos](PROPUESTA_CONSERVACION_COMPUESTA_FASE_6_2026_10_20.md),
sin reactivar su autorización ni su número.

**Condición temporal todavía no resuelta:** `registrado_en` de la aplicación
puede ser NULL en historia anterior a la ampliación; `estado` puede cambiar
después por anulación o rectificación; las respuestas carecen de fecha técnica
individual. Una transcripción con `registrado_en` posterior al corte se excluye
ahora de la captura, pero una fila anterior mutada después del corte no revela
por sí sola su estado pasado. El sello propuesto protege el estado al preparar
una evaluación nueva, no certifica cómo estaba la base en un corte antiguo.
El guardado debe registrar el instante de adquisición y fallar si se pretende
afirmar una reconstrucción histórica no demostrable. Esta condición debe
formar parte del ensayo de round-trip y de la aceptación del contrato.

## Reutilización y costo del sellado

| Origen existente | Se reutiliza | Protección pendiente |
|---|---|---|
| Doce relaciones de conocimiento y cabecera de versión | Definición exacta por versión | Sello SQL del conjunto completo. La primera firma y el retiro admitidos se registran por evaluación sin reescribir el pasado. |
| Instrumentos, preguntas y opciones | Metadata original incluso para selector sin respuesta | Sellar instrumento y sus hijos; bloquear inserción, actualización y borrado posteriores. Nueva edición: nuevo instrumento enlazado con antecesor. |
| Aplicaciones y respuestas | Brutos y procedencia | Sellar aplicación y toda la colección de respuestas, incluido INSERT tardío. Rectificar por aplicación sucesora. |
| Juicios y soportes Petersen | Contenido ya inmutable | Guardar membresía y orden observados por evaluación. |
| Residente, personal, atenciones y contexto médico/IADL/documental | Identidades originales | Copiar solo los atributos consumidos que pueden cambiar. No congelar personas. |

Los sellos de Eloquent no bastan. Se necesitan triggers equivalentes en
PostgreSQL y SQLite, con bloqueo transaccional al sellar. El primer sello
captura el estado cotejado en ese momento, sin atribuirlo a fechas anteriores.
Estas garantías modifican estructura y reglas físicas; requieren aprobación.

## Inventario candidato de catorce relaciones

De aprobarse y superar todas las pruebas, el inventario sería **118 tablas
de negocio: 75 operativas y 43 expertas**. Es una reducción frente al
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

Quedarían fuera las tablas candidatas 1, 4–6, 8–10, 12, 14, 15, 17 y 18
del diseño rechazado: release por huella y archivo privado, contratos
estáticos del release, originales sellados, unión de perfiles sin producto
cartesiano y vínculos observacionales reconstruidos y cotejados desde
mapeos/preguntas originales. No se elimina
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
Fecha clínica observada: fecha_clinica_utc datetime(6),
fecha_clinica_zona string(80), fecha_clinica_offset integer y
fecha_hora_sql_texto text. Registro técnico observado:
registro_tecnico_utc datetime(6) NULL, registro_tecnico_zona
string(80) NULL, registro_tecnico_offset integer NULL y
registrado_en_sql_texto text NULL. estado_documento string(40).
UNIQUE evaluación+aplicación y evaluación+orden. Trigger comprueba
que autor, atención, documento y perfil pertenecen a la misma
evaluación. Las proyecciones de aplicación y respuestas se recuperan
de originales sellados; los motivos de adquisición vienen de 13.

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
5. Antes del guardado, reconstruir desde conocimiento y originales sellados
   cada vínculo observacional y compararlo con el mapa del DTO. Rechazar un
   perfil interpretativo o vínculo no derivable. La comparación de 19 vínculos
   en SQLite sintético ya pasó; falta probar mutaciones concurrentes y el
   round-trip íntegro.
6. Solo con esa evidencia convertir este ensayo en diccionario
   estructural final para aprobación específica de la propietaria.

No se afirma todavía que catorce sea el mínimo suficiente.
Fase 6 y el informe histórico/aceptación dependientes siguen abiertos.
