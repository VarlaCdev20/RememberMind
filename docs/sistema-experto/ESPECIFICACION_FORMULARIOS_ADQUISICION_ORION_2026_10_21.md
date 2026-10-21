# O.R.I.O.N. — Especificación de formularios para adquirir evidencia profesional

Fecha: **21/10/2026**, America/La_Paz. Versión documental 1.0.
Solicitud: detallar componente por componente qué necesitan aportar los
profesionales, qué debe contener cada formulario, dónde presentarlo y sus
restricciones para alimentar el experto en población de **60 años o más**.

**Alcance:** especificación documental contrastada con código, contratos y
fuentes. No implementa vistas, catálogos, permisos, tablas, nuevas reglas,
umbrales ni datos de residentes. No prescribe aplicar todos los instrumentos
a cada persona. El catálogo de 23 métodos describe alternativas de cálculo
para 19 componentes, no 23 pruebas obligatorias ni una batería clínica adoptada.

## 1. Autoridad, estado y convenciones

Fuentes vigentes:

- [Baseline actual y decisiones posteriores](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md): 104 tablas de negocio en el corte rector consultado; no se modifica su estructura.
- [Contrato institucional Petersen C01–C05](CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md), concreción de E–H adoptado y captura original aprobada.
- [Diccionario de adquisición de los 23 métodos](DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md) y [cierre técnico de 19 componentes](CIERRE_TECNICO_FASE_3_2026_10_19.md): cálculo y condiciones; no adopción clínica automática.
- [Matriz de inferencia institucional](FASE_2_MATRIZ_MAESTRA_INFERENCIA_2026_10_20.md): C1/C2/C3 para las quince reglas G09; los cuatro conceptos de memoria no son cuatro reglas instrumentales adicionales.
- Evidencia documental local concurrente, aún sin commit al elaborar esta especificación: `PUNTO_1_EVIDENCIA_COGNITIVA_ORIGINAL_2026_10_21.md` y `PETERSEN_REVISION_Y_CONSERVACION_ESTADO_2026_10_21.md`. Su descripción se cotejó con las clases de captura, consulta y revisión; no se incorporan esos archivos ajenos en este entregable.
- [Aprobación IADL](../base-de-datos/DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md): tabla/modelo/lectura, sin autorización de pantallas, permisos o competencias de escritura.
- [Lectura IADL para Petersen aprobada](PROPUESTA_LECTURA_IADL_PETERSEN_2026_10_19.md): lectura contextual de Medicina/Psicología y consulta técnica de Superadministración; no escritura ni provisión de lectura a Enfermería por este flujo.
- [Aporte interdisciplinario](APORTE_INTERDISCIPLINARIO_GENERAL_2026_10_20.md): propuesta de adquisición, no batería adoptada.

Convenciones para implementar:

| Marca | Significado |
| --- | --- |
| G | Exigido por el guardado actual del acto correspondiente. |
| M | Necesario para que el método interprete esa medida. Su ausencia puede conservarse en el original, pero impide o limita el cálculo. No se transforma en campo obligatorio de toda la residencia. |
| D | Derivado por servidor; se presenta en lectura, no como resultado editable. |
| P | Propuesta de captura/UX; comprobar representación y decisión antes de imponer obligatoriedad o persistir. |
| GAP | Falta vínculo, soporte estructural, permiso, decisión o implementación; no simular que existe. |

La cifra «19/19 componentes con método disponible» no acredita 19 componentes evaluados para
un residente. Tampoco `COMPLETA` en una aplicación SQL prueba que todas las
tareas requeridas fueron respondidas. La ausencia de un perfil interpretativo
no se resuelve añadiendo un selector «alterado» a una puntuación.

## 2. Vistas, actores y competencias

No crear un nuevo menú por cada prueba. Reutilizar captura compartida y secciones
por rol; una aplicación puede alimentar más de un componente sin duplicarse.

| Vista/proceso | Aporte y formulario | Responsabilidad y límites |
| --- | --- | --- |
| Psicología → evaluaciones / ficha → Registrar mediciones | F01 aplicación instrumental; F02 interpretación original; F03/F05/F06 según competencia | Propuesta de recorrido principal cognitivo. Selección/aplicación/interpretación exige competencia y derechos del instrumento; un rol informático no acredita capacitación. |
| Medicina → ficha clínica / valoración / resultados | Historia y diferencial F03/F05; interpretación F02; suficiencia F06; revisión F07 | Mantener autoría propia y permisos/Policy. No atribuir a Medicina las pruebas aplicadas por otro profesional. |
| Enfermería → seguimiento / Observación cognitiva | F00 observaciones concretas; consulta de resultados permitidos | Residentes asignados, jornada y permiso vigentes. No habilita juicio original Petersen ni firma profesional del resultado. |
| Valoraciones → funcionalidad instrumental | F04 IADL, tras decisión de captura | Fuente aprobada y lectura existentes. Actor, Action, Policy y pantalla de escritura asistencial pendientes; no asignar automáticamente a Enfermería o Fisioterapia. |
| Resultados → valoración original Petersen | Cinco dominios y C01/C03/C04/C05 | Policy actual permite Medicina/Psicología competentes y autorizados; Superadministración consulta. No hay entrada C02 ni resultado DCL editable. |
| Resultados conservados → revisión | F07, cadenas Medicina y Psicología separadas | Solo evaluación íntegra y revisable. No firmar el cálculo Petersen transitorio ni confirmar una consulta actual con la firma de otra evaluación. |
| Conocimiento y reglas | Configuración de versiones, relaciones y vínculos | Personal autorizado según gobierno existente. No es captura clínica ni una vía para introducir hechos esperados. |

El backend genérico `AplicarInstrumento::ROLES_APLICADORES` incluye Medicina,
Enfermería, Psicología, Nutrición, Fisioterapia y Pedagogía, con autorización
contextual. **Eso no constituye una autorización clínica específica para que
cualquiera interprete WAIS, TOL-F o toda la batería.** La selección por
competencia/instrumento es una decisión a resolver sin ampliar permisos por UI.
Familia aporta información al profesional; no se concede por este documento
acceso al experto ni escritura de juicios. Gerencia/Administración no adquieren
escritura clínica por su función administrativa.

### 2.1 Otros profesionales y registros de contexto

No todos los profesionales necesitan un formulario nuevo del experto. Los
siguientes aportes son P de integración contextual desde su módulo propietario,
según el caso y sus permisos actuales, no nuevos antecedentes automáticos G09:

| Profesional/fuente | Información que puede documentar dentro de su competencia | Restricción para el experto |
| --- | --- | --- |
| Fisioterapia / funcionalidad | Movilidad, limitación motora, ayudas y desempeño en tareas que realmente evaluó, con fecha y ejemplo | Puede explicar dificultades en una tarea gráfica/táctil; no se transforma en alteración cognitiva ni en C03 sin juicio e IADL admitidos. |
| Pedagogía / actividades | Tarea realizada, instrucciones, apoyos, participación y desempeño observado en sesiones | Seguimiento contextual; actividad de estimulación no equivale a una prueba normada por llevar un nombre parecido. |
| Nutrición | Hallazgos de valoración nutricional/ingesta relevantes, fecha y fuente propia | Contexto para el profesional clínico; no asignar deterioro o causalidad desde un hallazgo nutricional. |
| Enfermería / medicación, sueño, conducta, dolor, incidencias | Administración e incidencias realmente registradas, cambios observados y contexto temporal | Reutilizar sus registros; no prescribir, editar órdenes ni inferir factores excluidos por ausencia de anotación. |
| Familia/cuidador | Ejemplos, funcionamiento previo, contexto y ayuda referidos; informante y relación documentados por quien entrevista | No firma profesional ni acceso al expediente completo; fuente referida distinguida de observación directa. |

Una fuente contextual puede ser útil para Medicina/Psicología aunque no tenga
un enlace de soporte admitido por Petersen. La matriz de `ContratoFuentesPetersen`
es cerrada: no añadir otra FK o convertir toda nota en DIFERENCIAL por esta tabla.
La revisión clínica puede quedar documentada en la fuente admitida con sus
referencias, sin inventar que el motor ya lee automáticamente todos los módulos.

## 3. Cabecera común y tipos de captura

### 3.1 Identificación, procedencia y fechas

| Campo / etiqueta | Tipo / origen | Regla |
| --- | --- | --- |
| Residente | PK string institucional, G; contexto servidor | No editable como identidad clínica; revalidar alcance en cada acción. Nunca nombre parecido ni primer residente disponible. |
| Profesional actual y cuenta | Relaciones de sesión, G/D | Cuenta ACTIVO y personal competente/activo. No aceptar `cod_personal` o `cod_usuario` arbitrarios desde el navegador. |
| Aplicador original | D en acto propio; selección cotejada en transcripción autorizada | Distinguir de transcriptor; el aplicador histórico no debe sustituirse por quien registra hoy. |
| Fecha/hora clínica | Fecha explícita de aplicación, G | No futura. Zona institucional conserva el instante; no inventar hora con fecha planificada ni `now()` para un original histórico. |
| Fecha técnica | Instante de servidor, D | Separada de aplicación. |
| Atención / episodio | FK del mismo residente, cuando corresponde | Para corroboración independiente, el episodio debe poder acreditarse; NULL no produce un episodio artificial. |
| Instrumento | PK activa del catálogo autorizado, G | No permitir instrumento/pregunta/opción ajenos. Revalidar estado al confirmar. |
| Edición, forma, idioma, método | Perfil cotejado de servidor, M | Mostrar lectura; identidad real no se rellena desde la bibliografía por semejanza de nombre. |
| Documento original y localizador | FK privada + página/sección, condicional en transcripción | Documento del residente, vigente y verificable. No URL pública a una historia. Los permisos de metadatos no conceden descarga. |
| Antecesora y motivo | Vínculo de rectificación, condicional | Nueva fila y cadena conservada; no editar respuestas históricas ni borrar para ocultar un error. |
| Solicitud/idempotencia y huella de catálogo | Generación de servidor, D | Evitar doble envío y detectar catálogo modificado; no son identidad o firma clínica. |

### 3.2 Entradas y respuesta ausente

Cada respuesta enlaza `cod_aplicacion → cod_pregunta → valor`. Un único tipo
original por pregunta: número, lógico, texto u opción válida. Una entrada
ENTERO no acepta fracciones; NUMERO requiere valor finito y unidad visible.
`valor_numero` admite hasta cuatro decimales en el contrato genérico, pero el
método puede exigir entero, medio punto, rango o duración exacta. El transporte
SQL/HTTP puede entregar cadenas numéricas: la adaptación tipada pertenece al
backend; nunca convertir `null`, texto vacío o booleano en cero.

Los lógicos se presentan como **Sí / No / No consta**, sin opción preseleccionada.
La tercera representa NULL/ausencia, no una tercera respuesta clínica verdadera.
Para calcular, las condiciones positivas del método deben ser `true`; se
conservan los `false` que impiden esa comparación. Los catálogos nominales
observacionales mantienen su identidad; no son aciertos ni escalas objetivas.

Mostrar cada tarea no administrada y su motivo es P: usar la observación de
pregunta/aplicación o original documental disponible, sin fabricar una nueva
columna. El guardado actual exige al menos una respuesta; no admite un acto
vacío. Una aplicación puede quedar sin comparación por entradas ausentes; no
rellenar para cumplir un contador ni confundir persistencia con suficiencia.

### 3.3 Contextos de referencia que deben acompañar las fichas

Las fichas §5–9 referencian estos grupos de condiciones exactas. Son M para el
método respectivo, no preguntas universales a todos los profesionales.

| Grupo | Campos exactos / restricciones del contrato actual |
| --- | --- |
| **BO** | `escolaridad_anios` entero ≥1; `espanol_nativo`, `nacimiento_bolivia`, `residencia_bolivia`, `lectoescritura`, `administracion_estandarizada`: lógicos positivos. Para el recorrido 60+: edad 60–95. No inferir nacimiento/lectura por domicilio o idioma de la pantalla. |
| **SABIEX** | Edad objetivo 60–87; `escolaridad_anios` 3–22; `contexto_normativo_espana_documentado`, `condiciones_sensoriales_compatibles`, `administracion_estandarizada`, `espanol_nativo`, `nacimiento_crianza_espana`, `actividad_cognitiva_universitaria`: positivos. La muestra selectiva no es equivalente a residentes bolivianos por tener español. |
| **API-CO** | Edad 61–64 o 66–86; 65 excluido por solapamiento editorial. `escolaridad_anios` 1–25; `espanol_nativo`, `lectoescritura`, `contexto_normativo_antioquia_documentado`, `condiciones_sensoriales_compatibles`, `administracion_estandarizada`: positivos. |
| **SCAND** | Edad 60–98; `nivel_educativo_scand` entero 1–4: <6 / 6–11 / 12–15 / >15 años según categorías publicadas; `zona_explorada_cotejada`, `practica_comprendida`, `lectoescritura`, `espanol_nativo`, `contexto_normativo_espana_documentado`, `condiciones_sensoriales_motoras_compatibles`, `administracion_estandarizada`: positivos. |
| **TOL-F** | Edad 60–79; `sexo_norma` 0 varón / 1 mujer según tabla; `nivel_educativo_ghs` 2–5 según clasificación original, no equivalencia automática de años bolivianos; `contexto_normativo_alemania_documentado`, `modalidad_tactil`, `comprension_instrucciones_verificada`, `condiciones_sensoriales_motoras_compatibles`, `administracion_estandarizada`: positivos; identidad de idioma `de`. |
| **TOKEN** | Edad 60–90; `escolaridad_anios` 0–18; `lectoescritura`, `contexto_normativo_neuronorma_colombia_documentado`, `audicion_vision_compatibles`, `administracion_estandarizada`: positivos. Escolaridad cero no prueba analfabetismo ni lectura. |
| **TB-A** | Edad objetivo 60–90; `audicion_compatible`, `contexto_normativo_espana_documentado`, `administracion_estandarizada`: positivos. Sin corrección escolar/sexual adicional inventada para estas dos subtareas. |

Cuando un modelo requiere `sexo_norma`, conservar su significado estadístico y
procedencia; no deducir de nombre o apariencia ni sustituir por identidad de
género. Si no está documentado o no puede aplicarse la codificación del estudio,
no forzar una categoría para obtener un cálculo. Una casilla «contexto
documentado» no autoriza transferir una norma a otro país. Se necesita justificar
pertinencia; documentar un contexto incompatible no equivale a cumplirlo.

## 4. F00 — Observación cognitiva longitudinal

Vista existente: `Cuidados/Presentacion/Livewire/Paginas/ObservacionCognitiva`,
seguimiento de Enfermería y registro rápido. Acción:
`Cuidados/Aplicacion/Acciones/RegistrarObservacionCognitiva`.

| Bloque | Campos actuales y valores |
| --- | --- |
| Orientación | `orientacion_persona`, `orientacion_lugar`, `orientacion_tiempo`: ORIENTADO / PARCIALMENTE_ORIENTADO / DESORIENTADO; ausencia separada. |
| Memoria | `memoria_reciente`, `memoria_remota`: CONSERVADA / ALTERACION_LEVE / ALTERADA / NO_VALORABLE; son estados descriptivos registrados. |
| Atención | `atencion`: CONSERVADA / FLUCTUANTE / DISMINUIDA / NO_VALORABLE. |
| Comprensión | `comprension`: CONSERVADA / PARCIAL / ALTERADA / NO_VALORABLE. |
| Lenguaje | `lenguaje`: CONSERVADO / LIMITADO / ALTERADO / NO_VALORABLE. |
| Hechos concretos | `sigue_instrucciones`, `repite_preguntas`, `olvida_indicaciones`, `reconoce_personas`, `reconoce_entorno`, `confusion`, `cambio_cognitivo`: Sí/No/No consta, separados. |
| Descripción | `observacion`: hecho, situación, ejemplo concreto y límites de lo observado; al menos un indicador o descripción significativa para guardar. No introducir aciertos inferidos. |
| Contexto de episodio | `cod_atencion` si existe; mismo residente. Residente, autor y jornada proceden del contexto validado. |

**Brecha de fecha:** la Action actual registra `fecha_hora=now()`; no recibe una
fecha clínica histórica. No presentar en esa vista un campo retroactivo que el
backend ignore. Diferenciar anotación contemporánea de reporte tardío requiere
un cambio explícito de contrato si se incorpora. La acción exige jornada activa
para Enfermería y autorización del residente. Medicina también está entre los
roles autorizados de esta Action; no asumir autorización de Psicología aquí.

**Contenido contextual propuesto P:** intervalo observado, fuente directa o
referida, antecedente habitual, intervención y respuesta, momento agudo o
fluctuante. Puede guiar la descripción original; no hay campos estructurados
confirmados para cada uno. No crear casillas booleanas en columnas inexistentes.
Nunca interpretar «no observado» como «observado que no ocurrió»: la lectura
actual usa esa etiqueta para NULL y necesita mantener explícita la distinción.

No exigir marcar todos los indicadores. Un solo control no demuestra pérdida
de memoria; dos filas del mismo episodio no son corroboración independiente.
Las observaciones contextualizan el experto y pueden apoyar C01 bajo su contrato;
no activan C02 ni estados A/P por sí solas.

## 5. F01 — Memoria: cuatro componentes

Las fichas F01 se capturan en el modal compartido de Valoraciones → Registrar
mediciones, con tareas según instrumento activo y perfil exacto. Profesional:
aplicador competente y autorizado; el recorrido cognitivo propuesto se ubica
principalmente en Psicología, sin conceder competencia por el rol genérico.

### 5.1 MEM-S01 — Aprendizaje/adquisición

Método principal investigado `HVLT-R-BO-2015-F5`, identidad HVLT-R / forma 5 / es.

| Etiqueta | Campo | Tipo / rango | Uso |
| --- | --- | --- | --- |
| Aciertos del primer ensayo | `ensayo_1` | ENTERO 0–12, M | Conteo original. |
| Aciertos del segundo ensayo | `ensayo_2` | ENTERO 0–12, M | No copiar del anterior. |
| Aciertos del tercer ensayo | `ensayo_3` | ENTERO 0–12, M | No estimar desde total. |
| Contexto boliviano documentado | Grupo BO | M | Edad/escolaridad y condiciones. |
| Total de aprendizaje | D | Suma 0–36 | Lectura; comparación del método, no diagnóstico. |

La adaptación reúne los tres campos en `ensayos=[e1,e2,e3]` para el normalizador;
el profesional no introduce un arreglo ni una z. Una aplicación puede compartir
MEM-S02 sin duplicarse. Faltante en un ensayo impide calcular ese total; no asignar cero.

**Alternativa separada:** `PFCSRT-ES-2025-IMAGENES-A-LIBRE`, pFCSRT+IR / AEC-IMAGENES-IR-A / es;
`libre_1/2/3` enteros 0–16; `codificacion_verificada=true`; edad objetivo 60–88;
escolaridad 0–30; `sexo_norma` 0/1; contexto español, condiciones sensoriales y
administración positivos. Solo aprendizaje libre de imágenes. No transferir
la identidad/norma de HVLT ni palabras FCSRT, ni forzar varianza no positiva.

### 5.2 MEM-S02 — Recuerdo libre diferido

Misma aplicación y método HVLT-R anterior, grupo BO.

| Etiqueta | Campo | Tipo / rango |
| --- | --- | --- |
| Palabras recordadas sin claves | `recuerdo_diferido` | ENTERO 0–12, M |
| Demora efectiva | `demora_minutos` | NUMERO finito 20–25 minutos para comparar, M |

No copiar aprendizaje ni usar reconocimiento como recuerdo libre. Preservar
el tiempo real aunque esté fuera de rango; entonces esa referencia no se usa.
El diferido y el aprendizaje pueden tener distinta disponibilidad en una misma
aplicación; un faltante no debe borrar la medida válida.

### 5.3 MEM-S03 — Recuerdo con claves

Método `FCSRT-ES-SABIEX-2021-APRENDIZAJE`, FCSRT / NEURONORMA-2009-PALABRAS / es,
grupo SABIEX y `sexo_norma` 0/1.

| Etiqueta | Campos | Tipo / restricción |
| --- | --- | --- |
| Recuerdo libre en tres ensayos | `libre_1`, `libre_2`, `libre_3` | ENTERO 0–16 cada uno, M |
| Aciertos adicionales con claves | `adicional_claves_1/2/3` | ENTERO 0–16 cada uno, M; libre + adicional ≤16 en cada ensayo |
| Codificación verificada | `codificacion_verificada` | LÓGICO positivo, M |
| Total con claves de aprendizaje | D | Suma libre + adicionales, 0–48; no contar dos veces los libres |

La comparación SABIEX es del **total libre + adicional**, no del adicional
aislado ni un diagnóstico de almacenamiento. Si se registra también diferido,
el selector descriptivo `FCSRT-PALABRAS-NEURONORMA-BRUTO-1` añade
`libre_diferido`, `adicional_claves_diferido` (cada uno 0–16, suma ≤16) y
`demora_minutos=30`. Ese diferido conserva un bruto sin norma; no usar la
regresión conflictiva descartada. No convertir la tarea de palabras en imágenes.

### 5.4 MEM-S04 — Reconocimiento

Método `CERAD-RECONOCIMIENTO-CO-API-2019`, CERAD-Col /
API-COLOMBIA-2019-RECONOCIMIENTO-20 / es; grupo API-CO.

| Campo | Tipo / restricción |
| --- | --- |
| `aciertos_objetivos` | ENTERO 0–10, M |
| `falsas_alarmas` | ENTERO 0–10, M |
| `objetivos_presentados`, `distractores_presentados` | ENTERO, exactamente 10 y 10 para comparar, M |
| `demora_minutos` | NUMERO finito, 7 para este contrato, M |
| `aprendizaje_tres_ensayos_documentado` | LÓGICO positivo, M |

Salida D: `max(0, aciertos_objetivos − falsas_alarmas)`, media/DE del grupo y
distancia descriptiva. No intercambiar con HVLT-R. La alternativa descriptiva
HVLT de reconocimiento recibe `aciertos_reconocimiento` 0–12,
`falsas_alarmas_relacionadas` y `falsas_alarmas_no_relacionadas` 0–6 cada una,
demora 20–25 y administración positiva. Su diferencia firmada puede ser negativa;
no aplicar el truncamiento CERAD ni atribuirle norma de reconocimiento boliviana.

## 6. F01 — Atención: tres componentes

### 6.1 ATE-S01 — Atención sostenida en una tarea breve

Método `CANCELACION-A-CO-API-2019`, Cancelacion-A / API-COLOMBIA-2019-16 / es,
grupo API-CO. `aciertos` ENTERO 0–16; `objetivos_presentados` ENTERO 16;
`tiempo_segundos` NUMERO finito >0: M. Conservar recuento y tiempo por separado;
la comparación implementada usa aciertos, no aciertos/segundo.

No hay celda normativa para mayores del grupo API de baja escolaridad (1–4);
no usar el grupo vecino. La tarea no acredita vigilancia prolongada ni normaliza
Ruff 2 y 7. No sumar la misma tarea como segunda evidencia de atención selectiva.

### 6.2 ATE-S02 — Atención selectiva

Método `LC-PW-ES-SCAND-2023-EFICIENCIA`, Cancelacion-PW /
ELES-SCAND-2023-26X30-65 / es; grupo SCAND.

| Campo | Tipo / restricción |
| --- | --- |
| `aciertos` | ENTERO 0–65, M |
| `omisiones_corregidas` | ENTERO 0–65, M; aciertos + omisiones ≤65 |
| `comisiones` | ENTERO 0–715, M |
| `objetivos_del_protocolo` | ENTERO 65, M |
| `filas_del_protocolo`, `columnas_del_protocolo` | ENTERO 26 y 30, M |
| `segundos` | NUMERO finito, 60 para comparar, M |
| `nivel_educativo_scand` | ENTERO 1–4 según categoría publicada, M |

Las constantes de forma se muestran desde perfil cotejado con el original;
no son valores de una aplicación presumidos por seleccionar el nombre de prueba.
Registrar zona realmente explorada; no calcular omisiones como 65 menos aciertos.
Salida D: eficiencia = aciertos − omisiones − comisiones, escalar y ajuste por
edad/educación. Brutos fuera de −42..28 no tienen conversión implementada.
Conservar negativos y no recortar escala ajustada a 18. El rango percentilar
previo al ajuste no es el percentil final.

### 6.3 ATE-S03 — Atención dividida

Método `BTA-BO-2015-NL`, BTA / Schretlen-1997-NL / es; grupo BO.
`forma_n_aciertos` y `forma_l_aciertos`: ENTERO 0–10 cada una, M;
`audicion_compatible`: LÓGICO positivo, M. Salida D: suma N+L 0–20 y comparación.
Una sola forma no constituye el total. No interpretar este resultado como norma
de atención sostenida ni duplicar las dos formas como episodios independientes.

## 7. F01 — Funciones ejecutivas: cinco componentes

### 7.1 EJE-S01 — Control inhibitorio

Método `STROOP-BO-2015-GOLDEN`, STROOP / Golden-2007-45s / es; grupo BO.

| Campo | Tipo / restricción |
| --- | --- |
| `palabras_correctas`, `colores_correctos`, `palabra_color_correctas` | ENTERO ≥0, M; respetar máximo real de la lámina cotejada, sin límite inventado |
| `segundos_palabras`, `segundos_colores`, `segundos_palabra_color` | NUMERO finito, 45 cada condición para comparar, M |
| `condiciones_sensoriales_motoras_compatibles` | LÓGICO positivo, M |

Salida D: `WC − W×C/(W+C)`, requiere W+C>0; conserva índice firmado e intervalo
percentilar publicado. Sin z o diagnóstico inventados. Tiempos distintos o
ediciones Victoria/D-KEFS no reciben esta referencia. Un denominador cero no se
soluciona añadiendo un acierto. Registro detallado de errores/ayudas es P o parte
del protocolo original: no nuevas columnas automáticas.

### 7.2 EJE-S02 — Flexibilidad cognitiva

Método `TMT-B-BO-2015`, TMT / Parte-B-25-300s / es; grupo BO.
`segundos_parte_b`: NUMERO finito >0 y ≤300 para comparar;
`errores_observados`: ENTERO ≥0;
`finalizada`, `condiciones_sensoriales_motoras_compatibles`: LÓGICOS positivos,
todos M. Registrar el tiempo efectivo y si se interrumpió. No asignar 300 como
tiempo exacto de finalización a quien no terminó. La comparación tiene dirección
MENOR_ES_MEJOR; z positiva indica mayor lentitud respecto de la referencia.
No trasladar TMT-A o versiones no equivalentes a esta tarea.

### 7.3 EJE-S03 — Memoria de trabajo

Métodos alternativos DSB, LN o SPAN de SABIEX, identidad WAIS-III española
de longitudes identificadas; grupo SABIEX. Campos M:

- `longitud_inversa`: ENTERO 0–8 si se usa DSB.
- `longitud_letras_numeros`: ENTERO 0–8 si se usa LN.
- Ambos si se usa `SPAN-ES-SABIEX-2021-LONGITUDES`, misma aplicación y resultados separados.

El apéndice técnico da los códigos de edición de cada alternativa. La longitud
máxima no es el total de aciertos de la subprueba. No usar dígitos directos como
manipulación inversa, WAIS-IV como WAIS-III o dos resultados del mismo acto como
soportes independientes. La referencia SABIEX selectiva no cubre automáticamente
la población institucional boliviana.

### 7.4 EJE-S04 — Planificación

Método `TOL-F-DE-GHS-2019-24`, TOL-F / GHS-2019-TACTIL-24 / de; grupo TOL-F.
`aciertos_minimos_movimientos`: ENTERO 0–24, M;
`problemas_del_protocolo`: ENTERO 24, M. Registrar desde la salida original de
la tarea informatizada táctil. Sexo de tabla y clasificación educativa original
también son M. Los detalles por problema de movimientos/tiempos/violaciones
pueden conservarse en original documental o tareas catalogadas con derecho,
pero no son entradas numéricas del normalizador actual.

Salida D: percentil tabulado según estrato. Bruto cero se conserva sin percentil
porque no hay fila publicada; no cambiarlo a uno. No aplica a 80+, TOL-DX,
tablero físico ni otra versión. Un protocolo en español no puede rotularse `de`
para superar la validación. El límite de una referencia impide su uso, no la
atención o el registro clínico del residente.

### 7.5 EJE-S05 — Razonamiento no verbal

Método `RAVEN-A-CO-API-2019`, Raven / API-COLOMBIA-2019-PARTE-A-12 / es,
grupo API-CO. `aciertos`: ENTERO 0–12; `elementos_puntuados`: ENTERO 12, M.
Salida D: distancia a media/DE de grupo, no inteligencia general ni percentil
gaussiano. No sustituir Matrices WAIS o Raven completo; no contabilizar esta
misma comparación como otra medición independiente de VIS-S01.

## 8. F01 — Lenguaje: cinco componentes

### 8.1 LEN-S01 — Denominación

Método `BNT-BO-2015-60`, BNT / Kaplan-1983-60 / es; grupo BO.
`aciertos_espontaneos` y `aciertos_clave_semantica`: ENTERO 0–60 cada uno,
suma ≤60; `condiciones_sensoriales_motoras_compatibles`: positivo, M.
Ayudas semánticas son aciertos adicionales, no recuento duplicado. Ayudas
fonémicas se conservan separadas en protocolo/documento; no se suman al total
normativo. No adoptar forma corta de 15 elementos como equivalente.

### 8.2 LEN-S02 — Fluidez verbal

Método `FLUIDEZ-BO-2015-FAS-ANIMALES`, FLUIDEZ / FAS-Animales-60s / es; grupo BO.
Campos M: `f_validas`, `a_validas`, `s_validas`, `animales_validas`, ENTERO ≥0;
`f_segundos`, `a_segundos`, `s_segundos`, `animales_segundos`, NUMERO finito,
60 para cada comparación. Conservar cada condición por separado. Repeticiones,
intrusiones, respuestas literales y criterio de exclusión pertenecen al protocolo
original o tareas autorizadas; no enviar texto libre al motor para inventar
la corrección. Puede haber cuatro resultados o disponibilidad parcial. No
promediar sus z para crear un dominio ni sumar cuatro episodios.

### 8.3 LEN-S03 — Comprensión verbal

Método `TOKEN-CO-UNAL-2017-TABLAS`, Token-Test /
NN-CO-2017-DE-RENZI-FAGLIONI-36 / es; grupo TOKEN.
`puntaje_total`: NUMERO 0–36, paso 0.5; `instrucciones_del_protocolo`: ENTERO 36,
M. La puntuación es corregida por el profesional según original, no un análisis
de la nota «comprende instrucciones» de Enfermería.

Salida D: escalar edad y ajuste escolar tabulados. Abstenerse en bruto 27.5 de
63–65 años y bruto ≤22 de 66–68 por lagunas/ambigüedad de la fuente, además de
fuera de rango. No interpolar ni redondear a la celda vecina. Referencia de tesis
original UNAL, no manual clínico aprobado; su muestra excluyó cuidados
residenciales especializados. No presentar esa muestra como validación propia.

### 8.4 LEN-S04 — Repetición

Método `TB-A-ES-NEURONORMA-2011-LENGUAJE`, TB-A /
NEURONORMA-2011-ABREVIADO / es; grupo TB-A.
`repeticion_palabras_aciertos`: ENTERO 0–10;
`repeticion_elementos_administrados`: ENTERO 10, M.
No inferir aciertos desde un comentario ni usar baremo WAB/TB revisado.
Salida D publicada: 10 → escalar 18; 0–9 → escalar 2, con fuerte efecto techo.
El máximo no significa capacidad superior ni un error demuestra DCL/gravedad.

Alternativa descriptiva WAB castellana 2000:
`repeticion_total` ENTERO 0–100; audición y administración positivas; salida
total/10 sin norma poblacional. No sustituir WAB-R ni aplicar la tabla TB-A.

### 8.5 LEN-S05 — Discurso: alcance fluencia/gramática

Mismo método y grupo TB-A, una aplicación compartida con LEN-S04.
`fluencia_gramatica`: ENTERO 0–10;
`muestra_discurso_documentada`, `tareas_habla_espontanea_completadas`: LÓGICOS
positivos, M. Conservar referencia a conversación, descripción y narración
realmente realizadas, con su muestra original donde sea legítimo. El normalizador
recibe la puntuación profesional, no genera puntuaciones desde una grabación.

Misma conversión de techo TB-A, con advertencia visible. No afirmar valoración
integral de coherencia, contenido o pragmática. Alternativa WAB castellana 2000:
`contenido_informacion` y `fluidez` ENTERO 0–10 cada uno; muestra y administración
positivas; suma 0–20 solo descriptiva. La identidad WAB y sus condiciones propias
se conservan separadas; una nota de conversación cotidiana no sustituye la tarea.

## 9. F01 — Habilidades visuoespaciales: dos componentes

### 9.1 VIS-S01 — Relaciones espaciales

Método `JLO-ES-SABIEX-2021-30`, JLO / JLO-30-NEURONORMA / es;
grupo SABIEX y `sexo_norma` 0/1. `aciertos` ENTERO 0–30 y
`elementos_puntuados` ENTERO 30, M. Cada acierto exige ambos segmentos correctos
según el protocolo, no medio acierto por segmento. No usar un total VOSP
genérico, forma corta o visión no compatible como equivalentes.

### 9.2 VIS-S02 — Visuoconstrucción

Método `ROCF-BO-2015-COPIA-A`, ROCF / Rey-2009-A-Copia / es; grupo BO.
`puntaje_copia`: NUMERO 0–36, paso 0.5;
`elementos_revisados`: ENTERO 18;
`condiciones_sensoriales_motoras_compatibles`: positivo, M.
La producción gráfica original debe ser identificable. El detalle de los 18
elementos se conserva en protocolo/documento o preguntas catalogadas autorizadas;
el normalizador recibe el total corregido. No generar el dibujo, reconstruirlo
desde una etiqueta ni trasladar la norma de copia a recuerdo diferido.

## 10. F02 — Interpretación profesional original de los cinco dominios

Vista existente: `CapturaPetersenPanel`, bloque por dominio. Action:
`RegistrarValoracionPetersenOriginal`. Política: `ValoracionPetersenPolicy`.
Medicina/Psicología autorizadas registran su propio juicio. La asignación a una
disciplina exclusiva no está impuesta por la Policy actual.

| Campo | Tipo / condición |
| --- | --- |
| `elemento` | Uno de COG-MEM, COG-ATE, COG-EJE, COG-LEN, COG-VIS; seleccionado del contrato, G |
| `estado_original` | A: alteración sustentada / P: rendimiento esperado sustentado / I: no interpretable; G para REGISTRO/RECTIFICACION |
| `justificacion` | Texto no vacío, máximo 10000 caracteres, G; explicar alcance, medidas y límites concretos |
| `fecha_hora` | Acto profesional real, no futuro, G |
| `alcance_dominio` | LÓGICO triestado, M para acreditar A/P |
| `contexto_interpretado` | LÓGICO triestado, M para acreditar A/P |
| `soportes` | G: lista presente y ordenada, máximo 100; el validador admite lista vacía. M para acreditar el juicio: soportes MEDICION → `cod_aplicacion` admisibles. |
| Método/huella del perfil | Servidor, D; no editable para forzar admisibilidad |

Cada soporte MEDICION debe conservar método, aplicación y perfil verificables;
el juicio debe estar sustentado conforme al contrato de mismo residente/autor,
fecha, estado, medida aplicable y contexto. No basta que el instrumento sea
«cognitivo» o que exista una puntuación normalizada cualquiera en otro dominio.

La futura vista deberá mostrar las mediciones admitidas y descartadas con causa
(P de interfaz); actualmente el selector presenta fuentes y sus metadatos. El
profesional elige las que realmente sustentan su acto. Un estado P necesita
soporte positivo tanto como A. I requiere explicar el impedimento, no marcar
un resultado favorable. U (insuficiencia) y X (contradicción) son estados
derivados; no respuestas a rellenar para completar el formulario.

Los juicios de cinco dominios son cinco elementos, no cinco totales creados por
sumar z de tareas. La comparación técnica no selecciona automáticamente A/P.

## 11. F03 — Historia y cambio corroborado, C01

Ubicación: valoración original de Medicina/Psicología, con enlace a historia,
control cognitivo, valoración psicológica o documento original del residente.

| Contenido | Campo / tipo y restricción |
| --- | --- |
| Funcionamiento previo | Fuente tipo HISTORIA_PREVIA; `cod_control_cognitivo`, `cod_valoracion_psicologica` o `cod_documento_clinico` |
| Cambio respecto del previo | `cambio_respecto_previo` LÓGICO triestado; positivo para acreditar |
| Corroboración independiente | `corroboracion_independiente` LÓGICO triestado; positivo para acreditar; soporte CORROBORACION |
| Naturaleza y cronología | `justificacion` G: qué cambió, respecto de qué y en qué período; sin fechas estimadas por el programa |
| Identidad del informante | P en historia/documento original: residente/familiar/cuidador, relación y conocimiento del funcionamiento previo; no atribuir autoría clínica al informante |
| Estado | `estado_original` S/N, G; no hay selección de U/X como acto original |

Historia previa anterior a corroboración, con episodios distintos y verificables.
La fuente y su papel se eligen explícitamente; no repetir la misma fila como
ambas. Registros distintos o separados por días no prueban independencia por
sí solos. El relato del familiar se documenta por el profesional en una fuente
admitida; no se inventa una FK de informante ni un permiso familiar de escritura.
Sin evidencia suficiente C01 queda U; un N requiere valoración negativa explícita.

## 12. F04 — Funcionalidad instrumental IADL y su integración C03

**Fuente existente aprobada:** `ControlFuncionalIadl`. **Escritura asistencial y
su actor específico: GAP**, excluidos expresamente de la aprobación inicial.
Propietario funcional Valoraciones. Diseñar el formulario no concede su permiso;
antes de activarlo se necesita definir responsabilidad y un flujo de Action/Policy.

Una fila corresponde a una actividad observada o referida, no a un puntaje
global. Campos del catálogo vigente:

| Campo | Tipo / opciones exactas |
| --- | --- |
| `actividad` | MEDICACION, PERTENENCIAS, RUTINAS, CITAS, COMUNICACION, FINANZAS, COMPRAS, TRANSPORTE |
| `oportunidad` | OBSERVADA, REFERIDA, SIN_OPORTUNIDAD, NO_HABITUAL |
| `desempeno` | INDEPENDIENTE, CON_APOYO, NO_EVALUABLE |
| `tipo_ayuda` | NINGUNA, RECORDATORIO, SUPERVISION, SUSTITUCION, NO_APLICA |
| `causa_ayuda` | NINGUNA, COGNITIVA, FISICA, SENSORIAL, INSTITUCIONAL, MIXTA, NO_DETERMINADA, NO_APLICA |
| `nivel_previo` | INDEPENDIENTE, CON_APOYO, NO_HABITUAL, DESCONOCIDO |
| `fuente` | OBSERVACION_DIRECTA, RESIDENTE, FAMILIAR, EQUIPO_CUIDADOS |
| `observacion` | Texto original; ejemplos, contexto, comparación y límites, nullable según fuente aprobada |
| Contexto/procedencia | Residente, autor, usuario, fecha clínica, fecha técnica; atención y antecesora cuando correspondan |

La matriz de coherencia del futuro escritor requiere decisión/verificación;
no imponer por este documento reglas nuevas sobre todas las combinaciones.
La lectura vigente exige `controles_funcionales_iadl.ver`, competencia y Policy;
no mostrar esa fuente en la vista de Enfermería por el mero hecho de tener
permiso de ficha o de conocer el contexto del cuidado.
Mostrar incompatible/sin evaluar sin transformar SIN_OPORTUNIDAD en dependencia.
Que la residencia administre medicamentos o transporte no demuestra incapacidad.
No permitir modificaciones de prescripciones desde «medicación» de IADL.

**Juicio integrado C03**, ya representado en captura Petersen, exige:
`revision_integrada`, `oportunidad_adecuada`, `atribucion_revisada` triestado;
S/N, justificación, fecha y soporte IADL → `cod_control_iadl`. Para acreditar S,
las tres comprobaciones son positivas y la fuente tiene oportunidad y contexto
interpretables. No acreditar con Barthel/Katz solos, ni con suma IADL aislada.
Ayuda cognitiva no acredita causalidad por seleccionar el rótulo; requiere juicio.

## 13. F05 — Revisión diferencial C04

Medicina/Psicología según competencia, a través de fuentes clínicas existentes y
captura original Petersen; no ampliar atribuciones por el formulario.

| Campo/parte | Especificación |
| --- | --- |
| `revision_explicita` | LÓGICO triestado; positivo para S; acto original distinto de firma posterior |
| `factores_pertinentes_revisados` | LÓGICO triestado; positivo para S, con explicación de cuáles y su alcance |
| `impedimento_no_resuelto` | LÓGICO triestado; debe ser false explícito para acreditar; NULL no prueba ausencia |
| `justificacion` | Qué se revisó, datos encontrados, factores pendientes y razonamiento; G |
| Estado y fecha | S/N, fecha original no futura, G |
| Soporte DIFERENCIAL | `cod_valoracion_psicologica`, `cod_antecedente`, `cod_diagnostico` o `cod_documento_clinico`; al menos el original pertinente según acto |

Contenido clínico orientativo P, no lista universal obligatoria: evolución aguda
o fluctuante, medicamentos/cambios, enfermedades pertinentes, sueño, ánimo,
dolor y condiciones sensoriales. Si existen prescripciones/laboratorios/informes,
se consultan por sus permisos y se fundamenta la revisión en fuentes admitidas;
no inventar enlaces tipados que no estén en la matriz. No ordenar exámenes por
defecto ni inferir causalidad o etiología desde una casilla. Impedimento pendiente
produce abstención explicada aunque haya puntuaciones bajas.

## 14. F06 — Calidad e interpretabilidad del conjunto, C05

Medicina/Psicología autorizadas; misma captura original. Campos M:
`suficiencia_conjunto`, `condiciones_revisadas`, `exclusiones_revisadas`, lógicos
triestado y positivos para S. G: S/N, fecha, justificación y lista `soportes`
presente, ordenada y de hasta 100 entradas; el guardado actual admite lista
vacía. M: conjunto de cinco juicios revisados y soporte CALIDAD admisibles.

Seleccionar **exactamente los cinco juicios originales de dominio examinados**
como soportes DOMINIO_REVISADO → `cod_valoracion_revisada`, uno por dominio;
además soporte CALIDAD → aplicación, valoración psicológica o documento clínico.
Mostrar fecha, autor, estado y limitaciones de cada uno. Para acreditar, juicios
A/P vigentes, previos o simultáneos a C05 y sin competidores incompatibles.
No elegir automáticamente «el último» de cada dominio ni sustituir una
rectificación posterior dentro de un C05 ya registrado.

**Diferencia de contrato e implementación:** el contrato funcional exige
soportes para el acto vigente, mientras el validador de guardado admite la lista
vacía. No presentar ese guardado como acreditación de C05. La futura integración
debe resolver la validación de captura contra el contrato vigente y probar la
abstención ante originales incompletos; este documento registra la brecha y
no relaja el contrato ni modifica la Action concurrente.

El porcentaje de cobertura no es suficiencia clínica. C05 no corrige fuentes
inválidas ni confirma retrospectivamente condiciones desconocidas. Datos futuros,
ajenos, anulados o no verificables se excluyen y explican.

## 15. C02, hallazgos G09 y patrón: salidas, no formularios de resultado esperado

**C02 se deriva** de los juicios originales admisibles: al menos un A puede
acreditar alteración objetiva; para clasificar sigue siendo necesario el conjunto
completo de cinco dominios y C05. No pedir «¿Tiene DCL?», «porcentaje de riesgo»,
«C02 sí/no» ni una conclusión del motor precargada como entrada.

Para las quince reglas G09, el contrato debe vincular en **la misma aplicación**:
C1 dificultad específica interpretada con método/perfil y medición originales;
C2 atribución exacta a componente/tarea/instrumento/edición;
C3 condiciones, confusores y corroboración requeridas para esa tarea.
Son condiciones del perfil versionado, no tres casillas universales que
autorice el clínico sin soporte. Declarar un z o un hallazgo G09 no equivale a
un estado de dominio A/P; no alimentar C02 directamente con G09.

No hay contrato interpretativo operativo para toda alternativa por el simple
hecho de tener su normalizador. Antes de configurar `perfiles_interpretativos`
definir entradas exactas, significado, evidencia, condiciones/exclusiones y
criterio adoptado de interpretación, sin inventar umbrales. La misma fuente no
se multiplica por varias respuestas, formularios o variables verdaderas.

Petersen exige C01–C05 acreditados, cinco dominios A/P, al menos un A, ausencia
de impedimento/contradicción y regla vigente. Su salida es orientativa. El
contrato de cinco dominios y corroboración independiente es institucional, no
una exigencia universal literal de toda definición médica de DCL.

## 16. F07 — Revisión posterior, separada de las entradas

Para evaluaciones íntegramente conservadas y revisables:

| Campo | Regla |
| --- | --- |
| Evaluación exacta | FK a `evaluaciones_expertas`, no al residente ni al cálculo actual |
| Disciplina | MEDICINA o PSICOLOGIA, según competencia y permiso propios |
| Decisión | CONFIRMADA o RECHAZADA; no altera el resultado del motor |
| Justificación | Texto no vacío, hasta 10000 caracteres, incluso en confirmación |
| Autor/cuenta/fecha | Servidor; firma propia, nunca personal arbitrario |
| Revisión anterior | Cadena por evaluación/disciplina; nueva fila para corregir |

La opción «no puedo concluir» significa mantener PENDIENTE sin firma; no existe
tercera decisión persistida aprobada. Cada disciplina firma su propia cadena.
Rechazar no borra el cálculo ni firma por la otra profesión.

**GAP actual Petersen:** la consulta produce `persistido=false`; no hay una
evaluación compuesta íntegra recuperable a la que vincular su revisión. No crear
cabecera parcial ni guardar JSON/texto para obtener una FK de firma. La tabla
de revisiones ya existe; el problema es conservar exactamente el objeto a revisar.
La captura de nueve juicios y el cálculo actual no equivalen a ese historial.

## 17. Qué ve cada profesional y estados de interacción

Recorrido propuesto: contexto del residente → elegir acto/protocolo → capturar
originales → verificar → confirmar registro → consultar cálculo/faltantes →
interpretación profesional → evaluación conservada, cuando exista → revisión.

| Estado de UI | Comportamiento requerido para la futura implementación |
| --- | --- |
| Sin protocolo configurado | Mostrar qué identidad/vínculo falta; no formulario que promete interpretar cualquier instrumento. |
| Protocolo compatible | Cargar solo preguntas activas y ramas del método; unidad, ayuda y límites junto al campo. |
| Respuesta ausente | Conservarla ausente; indicar su efecto en el cálculo. No responder cero ni Sí automáticamente. |
| Original fuera de referencia | Conservar hecho real por el flujo permitido; mostrar «comparación no disponible» y causa; no impedir atención por falta de baremo. |
| Error de formato/coherencia | Error junto al campo y resumen accesible; conservar resto de captura; validación servidor equivalente. |
| Catálogo/permiso/autor revocado | Revalidar; rechazar operación, conservar captura recuperable en interfaz sin afirmar guardado; no exponer recursos ajenos. |
| Confirmación | Mostrar residente, acto, fecha y profesional; un solo envío en curso, protección de reintento. |
| Cancelación con cambios | Proteger cierre/X/Escape/backdrop/volver/cambio de residente; no autosave clínico no autorizado. |
| Guardado confirmado | Identificador del registro y acceso al original; distinguir «guardado» de «interpretable» y de «evaluación conservada». |
| Resultado incompleto/contradictorio | Motivo principal y concurrentes, fuentes utilizadas/excluidas; no mostrar Normal por ausencia. |

Usar los componentes Blade/Livewire y el Design System vigentes. Labels humanos;
los códigos de método/variable son detalle técnico desplegable. Responsive,
teclado, foco, labels/unidades, errores asociados y contraste se verifican al
implementar, no se declaran por esta especificación.

## 18. Restricciones de integración y representación

1. La vista no consulta SQL ni interpreta clínicamente. Livewire controla interacción; Application coordina; motor puro calcula; Policy/Action autorizan y validan.
2. Reutilizar `instrumentos`, `preguntas_instrumento`, `respuestas_instrumento` y aplicaciones por identidad, no crear formularios/SQL paralelos por componente.
3. Catalogar preguntas/contexto nuevos cambia un catálogo congelado: requiere decisión institucional. No insertar desde esta especificación ni crear JSON/EAV para evitar el modelo.
4. Perfil de servidor: PK/código/versión del instrumento, método e identidad, selector por PK exacta de pregunta y tipo de valor. Una pregunta repetida, ajena o selector ambiguo no es válido.
5. `perfiles_instrumentales`, `perfiles_interpretativos`, `contratos_conocimiento`, `version_cognitiva` y publicación son responsabilidades diferentes; configurar una no habilita las demás. No seleccionar versión TEST/SIM para residentes.
6. Original admisible → dato tipado → componente → condiciones → regla → salida. No alimentar el resultado esperado como dato, ni inferir hechos clínicos de una nota por coincidencia textual.
7. Transacciones, idempotencia pertinente y revalidación al guardar. No mensajes de éxito antes de commit ni capturas atribuidas a otro profesional.
8. Documentos privados verificados y acceso contextual; no enviar información clínica a servicios externos por esta propuesta.
9. Ningún campo permite activar por sí solo G09/Petersen, cambiar reglas, umbrales, pesos o tratamientos. Tampoco programar alertas nuevas desde una puntuación sin contrato aprobado.
10. Nuevos campos estructurales/competencias/firmas se aíslan como GAP; no se confunde autonomía técnica con autorización clínica.

## 19. Brechas concretas antes de agregar o activar las vistas

| Brecha | Acción concreta necesaria |
| --- | --- |
| Instrumentos candidatos sin adopción/catálogo local exacto | Seleccionar protocolo/edición/idioma y material legítimo, competencia, pertinencia y tareas; aprobar catálogo cuando corresponda. |
| Referencias SABIEX/API/TOL-F/Token/SCAND/TB-A no universales | Documentar pertinencia individual/poblacional; buscar alternativa si no se cumplen condiciones, sin cambiar los datos para pasar el método. |
| Observación contemporánea con `now()` | No prometer captura histórica; definir contrato de fecha clínica/reportes tardíos antes de añadirlo. |
| Contexto clínico propuesto sin campo dedicado | Reutilizar narración/documento cuando sea fiel; diseño estructural separado si se necesitan variables automáticas nuevas. |
| Juicio original guardado sin soportes | El contrato exige soporte; el validador actual admite lista vacía. Alinear el guardado con el contrato y probar originales incompletos; no tratar la fila existente como acreditación automática. |
| IADL escritura asistencial | Decidir actor/competencia/permiso y flujo transaccional; la aprobación de lectura no basta. |
| Predicados G09 sin perfil interpretativo institucional | Especificar vínculo y significado de C1/C2/C3 por tarea con referencia y pruebas antes de habilitar. |
| Conservación Petersen/compuesta incompleta | Resolver diseño y prueba de reproducción bajo máximo aprobado antes de habilitar firma o historial integral. |
| UI concurrente en desarrollo | Tratar archivos actuales como evidencia, no pantalla terminada; verificar el candidato integrado al implementar. |

## 20. Criterios de aceptación para la implementación posterior

- Los 19 componentes tienen formulario de método aplicable o motivo explícito de no disponibilidad; no exigir todas las alternativas.
- Probar cada rango, unidad, cero, ausencia, falso, duración real, medio punto y forma incompleta pertinente; el bruto válido fuera de norma no se altera.
- Probar aplicación válida parcial, falta de contexto y un componente válido junto a otro inválido en el mismo instrumento.
- Cuenta inactiva, sin permiso, otro residente, otro profesional, pregunta/opción ajenas, retirada de permiso, turno/asignación y enlace documental corrupto se rechazan en backend.
- Dos envíos concurrentes/reintento del mismo acto no duplican; rectificaciones conservan original y no firman juicios ajenos.
- C01 no usa el mismo episodio como corroboración; C03 conserva oportunidad/causa; C04 no transforma NULL en ausencia; C05 enlaza exactamente cinco juicios; C02 no se captura.
- G09 exige conjunción de tarea/aplicación; no mezcla aplicaciones parciales ni convierte varias respuestas en varios soportes.
- No mostrar firma de Petersen transitorio. Revisión de resultado íntegro conserva dos cadenas y no acepta tercera decisión inexistente.
- QA visual de flujo completo, carga/error/cancelación, teclado y viewports; pruebas PostgreSQL desechables cuando exista escritura/concurrencia, sin base operativa.

## 21. Fuentes profesionales y límites de esta búsqueda

Consulta dirigida 21/10/2026; no revisión sistemática ni capacitación para
administrar tests. El soporte primario de cada método está en el apéndice y
las fichas técnicas enlazadas, sin copiar estímulos ni rúbricas protegidas.

- [NICE NG97, recomendaciones 1.2](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations/): historia del residente e informante y contexto de evaluación. Consulta del PDF oficial indexado en esta revisión; es referencia clínica, no nueva regla local.
- [Iñesta et al., SABIEX 2021](https://doi.org/10.3390/ijerph18199958): muestra española independiente y cognitivamente activa; no representatividad residencial boliviana supuesta. El original editorial se localizó en la búsqueda actual.
- [Calderón-Rubio et al., SABIEX memoria/JLO](https://doi.org/10.3390/ijerph182412977), [API Colombia](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/), [SCAND](https://doi.org/10.1080/13854046.2023.2173304), [TB-A original](https://test-barcelona.com/wp-content/uploads/2025/09/2010-Quint-TB-Norms-1.pdf) y [tesis Token UNAL](https://repositorio.unal.edu.co/handle/unal/59359): lecturas completas previas documentadas con acceso, cotejos y límites; no se afirma nueva descarga completa de todas ellas el 21/10.
- [Unterrainer et al., TOL-F original](https://pmc.ncbi.nlm.nih.gov/articles/PMC6669988/): confirma versión informatizada de 24 problemas y referencia alemana. La modalidad táctil es pantalla, no tablero físico.

No se convierten recomendaciones bibliográficas en obligaciones institucionales
ni se copian reactivos de MoCA, WAIS o otros tests. MoCA/Pfeiffer/MMSE pueden
considerarse por separado para tamizaje si se decide su protocolo y uso; un
total global no se reparte automáticamente entre los 19 componentes. Tampoco
se obliga a incorporar una nueva escala de ánimo, delirium o laboratorio por
este documento.

## 22. Entrega y verificación documental

Entregable propio: este archivo. No se modifica el README concurrente ni código,
BDD, permisos, datos, configuración de activación o resultados de residentes.
Se contrastan metadatos ejecutables, validadores, Policy, contratos y enlaces.
Las pruebas de aplicación mencionadas son referencias existentes, no ejecuciones
nuevas de esta tarea documental. Build/QA visual no aplican al artefacto Markdown.

Verificación automática documental: **19 fichas únicas**, **23 selectores** y
**224 entradas con nombre/tipo coincidentes** con el registro ejecutable; las
entradas se repiten entre alternativas y no son 224 preguntas nuevas de BDD.
Ocho enlaces locales comprobados. Se revisaron rangos y condiciones contra los
normalizadores y separaciones de competencia contra Actions/Policies; la
verificación documental no acredita capacitación o validación clínica.

La revisión independiente de lectura contrastó roles y restricciones de captura:
sin discrepancias de rangos o unidades en las 19 fichas. Sus tres observaciones
documentales se corrigieron: contador de componentes frente a selectores;
lista de mediciones admitidas/excluidas como propuesta de UI; guardado de lista
de soportes frente a conjunto requerido para acreditación. No se alteró código
concurrente para disimular esa diferencia.

Mapa de implementación para el siguiente incremento, sin nuevas clases impuestas:

| Función | Pieza a reutilizar |
| --- | --- |
| Preguntas/tipos y selección del protocolo | `Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental.php`, `Valoraciones/Aplicacion/DTOs/CatalogoPreguntasInstrumento.php` |
| Modal e interacción de aplicación | `Valoraciones/Presentacion/Livewire/Funcionalidades/CapturaInstrumentalModal.php` y vista `evaluacion-geriatrica-modal.blade.php` |
| Aplicar/transcribir/conciliar | `Valoraciones/Aplicacion/Acciones/AplicarInstrumento.php` |
| Lectura autorizada y comparación | `ApoyoDecision/Aplicacion/Consultas/CapturasInstrumentalesResidente.php`, `ConsultarPerfilInstrumental.php`; registro/extractor/normalizadores del motor |
| Observación cognitiva | `Cuidados/Dominio/ObservacionCognitiva.php`, `Cuidados/Aplicacion/Acciones/RegistrarObservacionCognitiva.php` y su vista/página existente |
| Juicios originales y soportes | `ApoyoDecision/Presentacion/Livewire/Paginas/CapturaPetersenPanel.php`, `Aplicacion/Acciones/RegistrarValoracionPetersenOriginal.php`, `Aplicacion/Servicios/ContratoFuentesPetersen.php` |
| Cálculo Petersen actual | `ApoyoDecision/Aplicacion/Consultas/ConsultarPetersenInstitucional.php`, `motor-experto/.../dominio/petersen/EvaluarPetersenInstitucional.php` |
| Revisión de evaluación exacta | `ApoyoDecision/Aplicacion/Acciones/RevisarValoracionExperta.php`, `Presentacion/Policies/EvaluacionExpertaPolicy.php` |

Las rutas abreviadas de la tabla están bajo `app/Modulos`, salvo motor-experto.
No se reintroducen raíces antiguas de arquitectura. Este mapa no certifica la
versión concurrente de cada vista como probada.

## Apéndice A — Contratos exactos de los 23 selectores

Las siguientes tablas se extraen del registro ejecutable al elaborar este
documento. Complementan las restricciones de las fichas: no sustituyen sus
validadores de rango, duración y coherencia. `protocolo` es el selector técnico;
`edad_anios` se deriva de nacimiento y fecha del acto por la integración.
Los códigos de edición son contratos locales, no una certificación editorial.


### `HVLT-R-BO-2015-F5`

- Instrumento: **HVLT-R**; componentes: `MEM-S01`, `MEM-S02`.
- Identidad exacta: `{"instrumento":"HVLT-R","forma":5,"idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3233/NRE-151286).
- Edad del contrato de cálculo: 18–95; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años; sin techo añadido por esta especificación.

| Entrada técnica original | Tipo |
| --- | --- |
| `ensayo_1` | ENTERO |
| `ensayo_2` | ENTERO |
| `ensayo_3` | ENTERO |
| `recuerdo_diferido` | ENTERO |
| `demora_minutos` | NUMERO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `nacimiento_bolivia` | LOGICO |
| `residencia_bolivia` | LOGICO |
| `lectoescritura` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `BTA-BO-2015-NL`

- Instrumento: **BTA**; componentes: `ATE-S03`.
- Identidad exacta: `{"instrumento":"BTA","edicion_protocolo":"Schretlen-1997-NL","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3233/NRE-151283).
- Edad del contrato de cálculo: 18–95; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años; sin techo añadido por esta especificación.

| Entrada técnica original | Tipo |
| --- | --- |
| `forma_n_aciertos` | ENTERO |
| `forma_l_aciertos` | ENTERO |
| `audicion_compatible` | LOGICO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `nacimiento_bolivia` | LOGICO |
| `residencia_bolivia` | LOGICO |
| `lectoescritura` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `STROOP-BO-2015-GOLDEN`

- Instrumento: **STROOP**; componentes: `EJE-S01`.
- Identidad exacta: `{"instrumento":"STROOP","edicion_protocolo":"Golden-2007-45s","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3233/NRE-151281).
- Edad del contrato de cálculo: 18–95; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años; sin techo añadido por esta especificación.

| Entrada técnica original | Tipo |
| --- | --- |
| `palabras_correctas` | ENTERO |
| `colores_correctos` | ENTERO |
| `palabra_color_correctas` | ENTERO |
| `segundos_palabras` | NUMERO |
| `segundos_colores` | NUMERO |
| `segundos_palabra_color` | NUMERO |
| `condiciones_sensoriales_motoras_compatibles` | LOGICO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `nacimiento_bolivia` | LOGICO |
| `residencia_bolivia` | LOGICO |
| `lectoescritura` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `TMT-B-BO-2015`

- Instrumento: **TMT**; componentes: `EJE-S02`.
- Identidad exacta: `{"instrumento":"TMT","edicion_protocolo":"Parte-B-25-300s","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3233/NRE-151284).
- Edad del contrato de cálculo: 18–95; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años; sin techo añadido por esta especificación.

| Entrada técnica original | Tipo |
| --- | --- |
| `segundos_parte_b` | NUMERO |
| `errores_observados` | ENTERO |
| `finalizada` | LOGICO |
| `condiciones_sensoriales_motoras_compatibles` | LOGICO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `nacimiento_bolivia` | LOGICO |
| `residencia_bolivia` | LOGICO |
| `lectoescritura` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `BNT-BO-2015-60`

- Instrumento: **BNT**; componentes: `LEN-S01`.
- Identidad exacta: `{"instrumento":"BNT","edicion_protocolo":"Kaplan-1983-60","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3233/NRE-151278).
- Edad del contrato de cálculo: 18–95; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años; sin techo añadido por esta especificación.

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos_espontaneos` | ENTERO |
| `aciertos_clave_semantica` | ENTERO |
| `condiciones_sensoriales_motoras_compatibles` | LOGICO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `nacimiento_bolivia` | LOGICO |
| `residencia_bolivia` | LOGICO |
| `lectoescritura` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `FLUIDEZ-BO-2015-FAS-ANIMALES`

- Instrumento: **FLUIDEZ**; componentes: `LEN-S02`.
- Identidad exacta: `{"instrumento":"FLUIDEZ","edicion_protocolo":"FAS-Animales-60s","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3233/NRE-151279).
- Edad del contrato de cálculo: 18–95; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años; sin techo añadido por esta especificación.

| Entrada técnica original | Tipo |
| --- | --- |
| `f_validas` | ENTERO |
| `f_segundos` | NUMERO |
| `a_validas` | ENTERO |
| `a_segundos` | NUMERO |
| `s_validas` | ENTERO |
| `s_segundos` | NUMERO |
| `animales_validas` | ENTERO |
| `animales_segundos` | NUMERO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `nacimiento_bolivia` | LOGICO |
| `residencia_bolivia` | LOGICO |
| `lectoescritura` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `ROCF-BO-2015-COPIA-A`

- Instrumento: **ROCF**; componentes: `VIS-S02`.
- Identidad exacta: `{"instrumento":"ROCF","edicion_protocolo":"Rey-2009-A-Copia","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3233/NRE-151285).
- Edad del contrato de cálculo: 18–95; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años; sin techo añadido por esta especificación.

| Entrada técnica original | Tipo |
| --- | --- |
| `puntaje_copia` | NUMERO |
| `elementos_revisados` | ENTERO |
| `condiciones_sensoriales_motoras_compatibles` | LOGICO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `nacimiento_bolivia` | LOGICO |
| `residencia_bolivia` | LOGICO |
| `lectoescritura` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `DSB-ES-SABIEX-2021-LONGITUD`

- Instrumento: **WAIS-III**; componentes: `EJE-S03`.
- Identidad exacta: `{"instrumento":"WAIS-III","edicion_protocolo":"WAIS-III-ES-LONGITUD-INVERSA","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3390/ijerph18199958).
- Edad del contrato de cálculo: 55–87; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 3 años hasta 22.

| Entrada técnica original | Tipo |
| --- | --- |
| `longitud_inversa` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |
| `espanol_nativo` | LOGICO |
| `nacimiento_crianza_espana` | LOGICO |
| `actividad_cognitiva_universitaria` | LOGICO |

### `LN-ES-SABIEX-2021-LONGITUD`

- Instrumento: **WAIS-III**; componentes: `EJE-S03`.
- Identidad exacta: `{"instrumento":"WAIS-III","edicion_protocolo":"WAIS-III-ES-LONGITUD-LETRAS-NUMEROS","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3390/ijerph18199958).
- Edad del contrato de cálculo: 55–87; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 3 años hasta 22.

| Entrada técnica original | Tipo |
| --- | --- |
| `longitud_letras_numeros` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |
| `espanol_nativo` | LOGICO |
| `nacimiento_crianza_espana` | LOGICO |
| `actividad_cognitiva_universitaria` | LOGICO |

### `SPAN-ES-SABIEX-2021-LONGITUDES`

- Instrumento: **WAIS-III**; componentes: `EJE-S03`.
- Identidad exacta: `{"instrumento":"WAIS-III","edicion_protocolo":"WAIS-III-ES-LONGITUDES-DSB-LN","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3390/ijerph18199958).
- Edad del contrato de cálculo: 55–87; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 3 años hasta 22.

| Entrada técnica original | Tipo |
| --- | --- |
| `longitud_inversa` | ENTERO |
| `longitud_letras_numeros` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |
| `espanol_nativo` | LOGICO |
| `nacimiento_crianza_espana` | LOGICO |
| `actividad_cognitiva_universitaria` | LOGICO |

### `JLO-ES-SABIEX-2021-30`

- Instrumento: **JLO**; componentes: `VIS-S01`.
- Identidad exacta: `{"instrumento":"JLO","edicion_protocolo":"JLO-30-NEURONORMA","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3390/ijerph182412977).
- Edad del contrato de cálculo: 55–87; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 3 años hasta 22.

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos` | ENTERO |
| `elementos_puntuados` | ENTERO |
| `sexo_norma` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |
| `espanol_nativo` | LOGICO |
| `nacimiento_crianza_espana` | LOGICO |
| `actividad_cognitiva_universitaria` | LOGICO |

### `FCSRT-ES-SABIEX-2021-APRENDIZAJE`

- Instrumento: **FCSRT**; componentes: `MEM-S03`.
- Identidad exacta: `{"instrumento":"FCSRT","edicion_protocolo":"NEURONORMA-2009-PALABRAS","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3390/ijerph182412977).
- Edad del contrato de cálculo: 55–87; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 3 años hasta 22.

| Entrada técnica original | Tipo |
| --- | --- |
| `libre_1` | ENTERO |
| `adicional_claves_1` | ENTERO |
| `libre_2` | ENTERO |
| `adicional_claves_2` | ENTERO |
| `libre_3` | ENTERO |
| `adicional_claves_3` | ENTERO |
| `codificacion_verificada` | LOGICO |
| `sexo_norma` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |
| `espanol_nativo` | LOGICO |
| `nacimiento_crianza_espana` | LOGICO |
| `actividad_cognitiva_universitaria` | LOGICO |

### `PFCSRT-ES-2025-IMAGENES-A-LIBRE`

- Instrumento: **pFCSRT+IR**; componentes: `MEM-S01`.
- Identidad exacta: `{"instrumento":"pFCSRT+IR","edicion_protocolo":"AEC-IMAGENES-IR-A","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.1177/13872877251376906).
- Edad del contrato de cálculo: 50–88; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 0 años hasta 30.

| Entrada técnica original | Tipo |
| --- | --- |
| `libre_1` | ENTERO |
| `libre_2` | ENTERO |
| `libre_3` | ENTERO |
| `codificacion_verificada` | LOGICO |
| `sexo_norma` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `RAVEN-A-CO-API-2019`

- Instrumento: **Raven**; componentes: `EJE-S05`.
- Identidad exacta: `{"instrumento":"Raven","edicion_protocolo":"API-COLOMBIA-2019-PARTE-A-12","idioma":"es"}`.
- Fuente primaria: [original del método](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/).
- Edad del contrato de cálculo: 61–86; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años hasta 25.
- Edades expresamente excluidas por conflicto: 65.

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos` | ENTERO |
| `elementos_puntuados` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `lectoescritura` | LOGICO |
| `contexto_normativo_antioquia_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `CERAD-RECONOCIMIENTO-CO-API-2019`

- Instrumento: **CERAD-Col**; componentes: `MEM-S04`.
- Identidad exacta: `{"instrumento":"CERAD-Col","edicion_protocolo":"API-COLOMBIA-2019-RECONOCIMIENTO-20","idioma":"es"}`.
- Fuente primaria: [original del método](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/).
- Edad del contrato de cálculo: 61–86; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años hasta 25.
- Edades expresamente excluidas por conflicto: 65.

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos_objetivos` | ENTERO |
| `falsas_alarmas` | ENTERO |
| `objetivos_presentados` | ENTERO |
| `distractores_presentados` | ENTERO |
| `demora_minutos` | NUMERO |
| `aprendizaje_tres_ensayos_documentado` | LOGICO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `lectoescritura` | LOGICO |
| `contexto_normativo_antioquia_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `CANCELACION-A-CO-API-2019`

- Instrumento: **Cancelacion-A**; componentes: `ATE-S01`.
- Identidad exacta: `{"instrumento":"Cancelacion-A","edicion_protocolo":"API-COLOMBIA-2019-16","idioma":"es"}`.
- Fuente primaria: [original del método](https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/).
- Edad del contrato de cálculo: 61–86; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 1 años hasta 25.
- Edades expresamente excluidas por conflicto: 65.

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos` | ENTERO |
| `objetivos_presentados` | ENTERO |
| `tiempo_segundos` | NUMERO |
| `escolaridad_anios` | ENTERO |
| `espanol_nativo` | LOGICO |
| `lectoescritura` | LOGICO |
| `contexto_normativo_antioquia_documentado` | LOGICO |
| `condiciones_sensoriales_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `TOL-F-DE-GHS-2019-24`

- Instrumento: **TOL-F**; componentes: `EJE-S04`.
- Identidad exacta: `{"instrumento":"TOL-F","edicion_protocolo":"GHS-2019-TACTIL-24","idioma":"de"}`.
- Fuente primaria: [original del método](https://doi.org/10.1017/S1355617718001248).
- Edad del contrato de cálculo: 60–79; recorrido objetivo a partir de 60, sin ampliar techo.

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos_minimos_movimientos` | ENTERO |
| `problemas_del_protocolo` | ENTERO |
| `sexo_norma` | ENTERO |
| `nivel_educativo_ghs` | ENTERO |
| `contexto_normativo_alemania_documentado` | LOGICO |
| `modalidad_tactil` | LOGICO |
| `comprension_instrucciones_verificada` | LOGICO |
| `condiciones_sensoriales_motoras_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `TOKEN-CO-UNAL-2017-TABLAS`

- Instrumento: **Token-Test**; componentes: `LEN-S03`.
- Identidad exacta: `{"instrumento":"Token-Test","edicion_protocolo":"NN-CO-2017-DE-RENZI-FAGLIONI-36","idioma":"es"}`.
- Fuente primaria: [original del método](https://repositorio.unal.edu.co/handle/unal/59359).
- Edad del contrato de cálculo: 60–90; recorrido objetivo a partir de 60, sin ampliar techo.
- Escolaridad: desde 0 años hasta 18.

| Entrada técnica original | Tipo |
| --- | --- |
| `puntaje_total` | NUMERO |
| `instrucciones_del_protocolo` | ENTERO |
| `escolaridad_anios` | ENTERO |
| `lectoescritura` | LOGICO |
| `contexto_normativo_neuronorma_colombia_documentado` | LOGICO |
| `audicion_vision_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `LC-PW-ES-SCAND-2023-EFICIENCIA`

- Instrumento: **Cancelacion-PW**; componentes: `ATE-S02`.
- Identidad exacta: `{"instrumento":"Cancelacion-PW","edicion_protocolo":"ELES-SCAND-2023-26X30-65","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.1080/13854046.2023.2173304).
- Edad del contrato de cálculo: 60–98; recorrido objetivo a partir de 60, sin ampliar techo.

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos` | ENTERO |
| `omisiones_corregidas` | ENTERO |
| `comisiones` | ENTERO |
| `objetivos_del_protocolo` | ENTERO |
| `filas_del_protocolo` | ENTERO |
| `columnas_del_protocolo` | ENTERO |
| `segundos` | NUMERO |
| `nivel_educativo_scand` | ENTERO |
| `zona_explorada_cotejada` | LOGICO |
| `practica_comprendida` | LOGICO |
| `lectoescritura` | LOGICO |
| `espanol_nativo` | LOGICO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `condiciones_sensoriales_motoras_compatibles` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `TB-A-ES-NEURONORMA-2011-LENGUAJE`

- Instrumento: **TB-A**; componentes: `LEN-S04`, `LEN-S05`.
- Identidad exacta: `{"instrumento":"TB-A","edicion_protocolo":"NEURONORMA-2011-ABREVIADO","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.1093/arclin/acq098).
- Edad del contrato de cálculo: 50–90; recorrido objetivo a partir de 60, sin ampliar techo.

| Entrada técnica original | Tipo |
| --- | --- |
| `repeticion_palabras_aciertos` | ENTERO |
| `repeticion_elementos_administrados` | ENTERO |
| `fluencia_gramatica` | ENTERO |
| `muestra_discurso_documentada` | LOGICO |
| `tareas_habla_espontanea_completadas` | LOGICO |
| `audicion_compatible` | LOGICO |
| `contexto_normativo_espana_documentado` | LOGICO |
| `administracion_estandarizada` | LOGICO |

### `HVLT-R-F5-RECONOCIMIENTO-BRUTO-1`

- Instrumento: **HVLT-R**; componentes: `MEM-S04`.
- Identidad exacta: `{"instrumento":"HVLT-R","forma":5,"idioma":"es"}`.
- Fuente primaria: [original del método](https://fitbir.nih.gov/dictionary/publicData/dataElementAction!view.action?dataElementName=HVLTRRecogDiscrimIndexRawScore&publicArea=true&style.key=fitbir-style).

| Entrada técnica original | Tipo |
| --- | --- |
| `aciertos_reconocimiento` | ENTERO |
| `falsas_alarmas_relacionadas` | ENTERO |
| `falsas_alarmas_no_relacionadas` | ENTERO |
| `demora_minutos` | NUMERO |
| `administracion_estandarizada` | LOGICO |

Salida descriptiva: no acredita normalización ni hallazgo clínico.

### `WAB-ES-2000-ORAL-BRUTO-1`

- Instrumento: **WAB**; componentes: `LEN-S04`, `LEN-S05`.
- Identidad exacta: `{"instrumento":"WAB","edicion_protocolo":"Nau-Llibres-2000-Castellana","idioma":"es"}`.
- Fuente primaria: [original del método](https://talkbank.org/aphasia/protocol/languages/Spanish/WAB.pdf).

| Entrada técnica original | Tipo |
| --- | --- |
| `repeticion_total` | ENTERO |
| `contenido_informacion` | ENTERO |
| `fluidez` | ENTERO |
| `audicion_compatible` | LOGICO |
| `muestra_discurso_documentada` | LOGICO |
| `administracion_estandarizada` | LOGICO |

Salida descriptiva: no acredita normalización ni hallazgo clínico.

### `FCSRT-PALABRAS-NEURONORMA-BRUTO-1`

- Instrumento: **FCSRT**; componentes: `MEM-S03`.
- Identidad exacta: `{"instrumento":"FCSRT","edicion_protocolo":"NEURONORMA-2009-PALABRAS","idioma":"es"}`.
- Fuente primaria: [original del método](https://doi.org/10.3390/ijerph182412977).

| Entrada técnica original | Tipo |
| --- | --- |
| `libre_1` | ENTERO |
| `adicional_claves_1` | ENTERO |
| `libre_2` | ENTERO |
| `adicional_claves_2` | ENTERO |
| `libre_3` | ENTERO |
| `adicional_claves_3` | ENTERO |
| `libre_diferido` | ENTERO |
| `adicional_claves_diferido` | ENTERO |
| `demora_minutos` | NUMERO |
| `codificacion_verificada` | LOGICO |
| `administracion_estandarizada` | LOGICO |

Salida descriptiva: no acredita normalización ni hallazgo clínico.
