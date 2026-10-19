# O.R.I.O.N. — G08: especificación de captura cruda y comparabilidad de muestra

> Documento de investigación importado selectivamente del worktree `pr15-ci-utf8`, HEAD `8085ec0e`, el 18/10/2026. Se incorporó su programa `g08_captura_y_muestras.php` y se verificó localmente; las menciones de CI y cotejos previos pertenecen al expediente de origen. Evidencia actual en [auditoría Fase 01](FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md).

**Fecha:** 17 de octubre de 2026
**Estado:** especificación investigativa y control de datos ficticios; **no hay 15/15 baremos aprobados, sino 0/15**. Ninguna clínica conectada; no se interpretan casos ni se autorizan percentiles o estados DCL A/P.

## 1. Fuente del contrato

Las fichas parten de [G08_LOCALIZADORES_BOLIVIA_PUBLICACIONES_2015.md](G08_LOCALIZADORES_BOLIVIA_PUBLICACIONES_2015.md) y [G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md](G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md), más las secciones **2.1 Participantes** y **2.2 Instrument administration** de publicaciones primarias:

- [BTA, Rivera et al. (2015), 10.3233/NRE-151283](https://journals.sagepub.com/doi/10.3233/NRE-151283): dos formas N y L, 10 series auditivas por forma y puntuación cruda que suma respuestas correctas de ambas formas. **No importar los estímulos, secuencias o normas**.
- [Trail Making Test, Arango-Lasprilla et al. (2015), 10.3233/NRE-151284](https://journals.sagepub.com/doi/10.3233/NRE-151284): TMT-A y TMT-B se analizan y norman por separado; registrar la Parte B de modo inequívoco y sus condiciones de tiempo/errores según el protocolo que se adopte.
- [Stroop, Rivera et al. (2015), 10.3233/NRE-151281](https://journals.sagepub.com/doi/10.3233/NRE-151281): tareas Word, Color, Word-Color e Interference son medidas diferenciadas. La publicación administra las tres páginas en condiciones temporales estandarizadas. La **fórmula exacta del índice de interferencia de la edición aplicable** sigue pendiente de adoptar y verificar, no debe inferirse de cualquier «puntuación Stroop».
- [BNT estándar, Olabarrieta-Landa et al. (2015), 10.3233/NRE-151278](https://journals.sagepub.com/doi/10.3233/NRE-151278): no trasladar baremos de forma estándar a versiones abreviadas ni registrar solo una puntuación general sin referencia a edición y criterios de ayuda.
- [Fluidez verbal, Olabarrieta-Landa et al. (2015), 10.3233/NRE-151279](https://journals.sagepub.com/doi/10.3233/NRE-151279): letra F, A, S y animales tienen anexos Bolivia distintos. La condición de consigna, duración y exclusiones de respuestas debe estar documentada antes de calcular ninguna medida comparable.
- [M-WCST, Arango-Lasprilla et al. (2015), 10.3233/NRE-151280](https://journals.sagepub.com/doi/10.3233/NRE-151280): categorías, perseveraciones y errores totales son métricas diferentes; las categorías no sustituyen el recuento de errores ni justifican por sí mismas el constructo de razonamiento general.
- [Rey–Osterrieth, Rivera et al. (2015), 10.3233/NRE-151285](https://journals.sagepub.com/doi/10.3233/NRE-151285): administra Figura A en copia y, después, recuerdo; para VIS-S02 solo corresponde **copia**. El artículo describe 18 elementos evaluables y máximo de **36 puntos crudos** para cada condición. No se importa la figura ni su rúbrica protegida.

Estos son **métodos descritos por los investigadores**, no una confirmación de que el centro disponga de la misma edición o derecho de aplicación.

## 2. Compatibilidad de la población: brecha de mayor impacto

Los apartados de participantes de los estudios BTA, TMT, Stroop y BNT describen **adultos sanos, voluntarios de la comunidad**, seleccionados con criterios tales como: edad **18–95**, nacidos y residentes en el país, español materno, al menos **un año de educación formal**, lectura y escritura. Los participantes también cumplían criterios de cribado empleados **para seleccionar la muestra normativa**: MMSE ≥23, PHQ-9 ≤4, Barthel ≥90, y exclusión de antecedentes neuropsiquiátricos autodeclarados. **Estos números no son umbrales diagnósticos del proyecto ni condiciones automáticas para excluir a un residente de evaluación**.

La incorporación de Bolivia en el conjunto multicéntrico NO significa que las edades extremas, la educación mínima, el bilingüismo, el deterioro funcional o las condiciones clínicas habituales en un geriátrico tengan la misma representación que una muestra sana de la comunidad. La existencia de percentiles en columnas `>77` tampoco demuestra representación de toda la población institucionalizada.

El módulo `AuditorCapturaYComparabilidadG08Investigacion` registra **alertas de discrepancia**, no fallos de un paciente ni juicios clínicos:

| Dimensión | Si no consta o no coincide con la muestra | Resultado de auditoría |
|---|---|---|
| Edad | Desconocida o fuera de 18–95 en la publicación | Alerta de no comparabilidad; no extrapolar |
| Escolaridad | Desconocida o 0 años de educación formal | Alerta de límite de muestra; no inventar equivalencia |
| Idioma y alfabetización | Español materno no acreditado; no se verifica lectura/escritura | Alerta de transferencia cultural y de tarea |
| Origen normativo | Nacimiento/residencia en Bolivia no acreditados | No atribuir automáticamente la tabla de Bolivia |
| Población institucionalizada | Voluntarios comunitarios vs residente institucionalizado | **Alerta expresa de no equivalencia automática** |
| Criterios de selección y exclusión | No consta revisión de salud / criterios del estudio | Requiere comparación metodológica y revisión competente |

Las preguntas sobre cribado y exclusiones deberán ser examinadas conforme a la fuente primaria y por profesionales; el software **no almacena en este ensayo los resultados clínicos reales**. No se emplean cortes normativos importados ni reglas para descartar personas.

## 3. Contratos de captura por subresultado

Se incorporó `ContratoCapturaCrudaG08Investigacion` con 12 pares componente/tarea. Contiene los nombres y los tipos esperados de **mediciones crudas** sin ecuaciones ni baremos:

| Tarea | Datos crudos de prueba | Interpretación expresamente pendiente |
|---|---|---|
| BTA N + L | aciertos forma N, aciertos forma L | Estímulos y edición; no pasar de suma cruda a percentil |
| Stroop Interference | resultados de palabras, colores y palabra–color | Fórmula del índice y equivalencia de la edición concreta |
| TMT-B | segundos de la Parte B y errores observados | Protocolo de tiempo límite, corrección de errores y tarea concreta |
| M-WCST categorías | categorías completadas | Regla de puntuación y edición específica |
| M-WCST perseveraciones | errores perseverativos | Definición de error y protocolo |
| M-WCST errores totales | errores totales | No sustituir por perseveraciones |
| BNT estándar | denominaciones correctas | Criterios de ayuda/clave, versión y láminas autorizadas |
| Fluidez F/A/S/animales | respuestas válidas y segundos administrados | Consigna, duración y exclusiones por cada modalidad |
| ROCF copia | puntuación de copia y elementos revisados | Rúbrica exacta y edición de Figura A autorizada |

Algunos controles se basan únicamente en la descripción metodológica **publicada**: el BTA del artículo incluye 10 series por forma; la ROCF del artículo puntúa 18 elementos y total máximo de 36. Se verifican sus límites *solo en fixtures*, no se reproducen estímulos ni se implantan algoritmos para residentes.

## 4. Resultado del auditor técnico

Se incorporaron `ContratoCapturaCrudaG08Investigacion` y `AuditorCapturaYComparabilidadG08Investigacion`, junto con `g08_captura_y_muestras.php`. En los casos falsos se contrastan las claves de medida, unidad, tabla y DOI; los campos numéricos tipados, las dos formas BTA, la condición ROCF-copia y las diferencias de muestra.

**Incluso una captura con todos los campos declarados completos devuelve**:

- `estado_g08=NO_INTERPRETABLE_POR_G08`
- `estado_poblacion=NO_HOMOLOGADO_A_GERIATRICO`
- `puntuacion_normativa=null` y `percentil=null`
- `interpretacion_A_P=null`
- `uso_asistencial_autorizado=false`

Esto es intencional: una checklist o un formulario sintético NO demuestra que el instrumento y el baremo estén autorizados.

## 5. Cierre científico todavía pendiente

Para pasar de un contrato **de estructura de datos** a una interpretación instrumentada se debe demostrar *por cada tarea y edición*:

1. Edición/manual exactos disponibles, y derecho de aplicación e implementación.
2. Procedimiento de administración, captura de crudos, errores y calificación conforme al manual.
3. Comprobación de metodología, cobertura y celdas normativas correspondientes a edad/escolaridad y población real.
4. Algoritmo de normalización, manejo de valores imposibles y trazabilidad matemática; sin licencia no importar tablas ni ecuaciones protegidas.
5. Criterios interpretativos positivos para `A` y `P`, consensuados científicamente y sujetos a revisión profesional, no deducidos de una «regla que no se cumple».
6. Revisión de contexto clínico, confusores, diferencial, independencia/IADL y G12/G13.

**No se modifica la BDD de 71 tablas operacionales ni el conjunto de conocimiento experto congelado.** Los nombres de los campos de `datos_crudos` son claves SEMÁNTICAS DE PRUEBA, todavía **sin correspondencia validada** con `preguntas_instrumento.cod_pregunta` / `respuestas_instrumento.valor_numero`. Esa asignación tendrá que formalizarse por versión de instrumento.

**Estado G08:** 12 contratos de captura de laboratorio; 6 subcomponentes con publicaciones candidatas; **0/15 perfiles interpretativos clínicamente admitidos**.
