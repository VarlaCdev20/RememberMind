# Contrato funcional de Petersen institucional: C01–C05

**Versión:** 1.0 · **Fecha:** 21/10/2026 · **Ámbito:** O.R.I.O.N. institucional, personas mayores de 60 años.

Este documento concreta el contrato **E–H adoptado el 19/10/2026** y la captura original aprobada ese mismo día. Describe cómo acreditar requisitos y cuándo producir un **patrón orientativo compatible con posible DCL**. No constituye diagnóstico, no atribuye pruebas a residentes ni introduce umbrales, nuevas reglas clínicas, tablas o permisos. La interpretación original corresponde al profesional competente; el motor comprueba procedencia, coherencia y reglas. Medicina y Psicología revisan el resultado **después y por separado**.

## 1. Unidad de información y estados

Cada valoración original se registra en `valoraciones_petersen_originales`, con soportes tipados en `soportes_valoracion_petersen`. Sus campos comunes obligatorios para un registro vigente son: `solicitud_uuid`, `cod_residente`, `cod_personal` y `cod_usuario_registro` derivados de la sesión autorizada, `elemento`, `estado_original`, `justificacion` concreta, `fecha_hora` clínica, `tipo_registro` y al menos un soporte original. `registrado_en` es fecha técnica: nunca sustituye a `fecha_hora`. `cod_atencion` identifica el episodio cuando existe. `cod_documento_clinico` y `localizador_fuente` se consignan juntos si el acto procede de un documento. `cod_valoracion_anterior` enlaza rectificación/anulación y conserva el antecedente. La anulación no lleva estado ni soportes; una rectificación no sobrescribe el original.

Cada soporte tiene `tipo_soporte`, `orden` y **exactamente una** FK permitida por elemento. La cadena verificable es: fuente original identificada → soporte → juicio original de dominio/requisito → estado derivado → regla versionada → patrón o impedimento. La justificación no reemplaza el vínculo a la fuente.

| Estado | Significado operativo | Regla de captura |
|---|---|---|
| `S` | Requisito acreditado con valoración original y comprobaciones positivas. | Sólo C01, C03, C04, C05; C02 se deriva. |
| `N` | Requisito expresamente no acreditado, con motivo y soporte. | No equivale a dato ausente ni a `false` por defecto. |
| `A` | Dominio alterado según juicio profesional original sustentado. | Requiere medida objetiva interpretable y contexto. |
| `P` | Dominio con rendimiento esperado según juicio original sustentado. | Requiere soporte positivo; no se presume por silencio. |
| `I` | Dominio valorado pero no interpretable. | Motivo explícito; impide perfil completo. |
| `U` | Sin valoración admisible o con evidencia insuficiente. | Estado derivado, nunca una respuesta clínica rellenada por defecto. |
| `X` | Valoraciones originales vigentes y admisibles incompatibles. | Conservar ambas y abstenerse; no votar ni elegir la más favorable. |

Las comprobaciones de los formularios son `true`, `false` o `NULL` según lo documentado. `NULL` significa **no consta**. Sólo `true` acredita una condición positiva. No se convierte automáticamente en `false` ni en normalidad.

## 2. Prerrequisito: cinco dominios cognitivos

La clasificación consulta **COG-MEM** (aprendizaje y memoria), **COG-ATE** (atención), **COG-EJE** (funciones ejecutivas), **COG-LEN** (lenguaje) y **COG-VIS** (habilidades visuoespaciales). Para cada uno se captura `estado_original` `A`, `P` o `I`; `alcance_dominio`, `contexto_interpretado`, `justificacion` y soporte `MEDICION → cod_aplicacion`. Tanto `A` como `P` necesitan las dos comprobaciones positivas.

La aplicación debe corresponder al mismo residente y autor, estar completa y vigente al corte, conservar instrumento/edición/idioma/método y respuestas o tiempos originales, perfil de captura verificable y al menos una medida normalizada aplicable al dominio y a la edad 60+. Deben documentarse escolaridad, condiciones y límites del método. Una observación nominal de componente, un puntaje global aislado, una z sin método aplicable, G09 o D-131 no acreditan `A` ni `P`. El estado del dominio es un **juicio original sustentado**, no una deducción automática de una respuesta. Si alguna pieza falta, queda `U` o `I` con motivo; las 286 aplicaciones observacionales no se transforman retrospectivamente en mediciones.

## 3. Requisitos C01–C05

### C01 — Cambio cognitivo corroborado

| Campo | Obtención y obligatoriedad para `S` | Calidad exigida |
|---|---|---|
| `cambio_respecto_previo = true` | Psicología/Medicina documenta el cambio frente al funcionamiento anterior; `justificacion` explica naturaleza y período. | Debe existir historia previa identificable, no sólo un indicador o una queja sin contexto. |
| `corroboracion_independiente = true` | Se enlaza otra fuente que corrobora el cambio. | Fuente y episodio distintos del antecedente; no contar dos formularios del mismo episodio como dos observaciones independientes. |
| `HISTORIA_PREVIA` y `CORROBORACION` | Cada soporte apunta a `cod_control_cognitivo`, `cod_valoracion_psicologica` o `cod_documento_clinico`. | Ambas descripciones con contenido, episodios explícitos distintos y fecha previa anterior a la corroboración. Autor, estado y corte verificables. |

Si hay sólo observaciones repetidas, comparación de instrumentos no equivalentes, episodio desconocido o antecedente no anterior, C01 queda `U`. Un `N` exige una conclusión original negativa explícita con soporte y motivo; no se obtiene de no encontrar registros.

### C02 — Alteración objetiva derivada

**No tiene formulario ni fila original propia.** El motor la obtiene de los cinco dominios: `S` si existe al menos un dominio `A` admisible; `N` si los cinco son `A/P` y ninguno es `A`; `X` si un dominio es contradictorio; de otro modo `U`. La salida conserva `derivado_de` con los dominios pertinentes. Aunque C02 resulte `S` por un dominio `A`, **no se clasifica** hasta disponer del perfil completo de cinco dominios y C05 acreditado. El resultado de una regla G09 o una observación no puede activar C02.

### C03 — Independencia funcional instrumental relativa

| Campo | Obtención y obligatoriedad para `S` | Calidad exigida |
|---|---|---|
| `revision_integrada = true` | Profesional valora conjuntamente las actividades instrumentales y explica en `justificacion` el nivel de independencia relativo. | No basta Barthel, Katz o actividades básicas; tampoco una IADL aislada interpretada fuera del entorno residencial. |
| `oportunidad_adecuada = true` | Valora si la persona tuvo ocasión real de realizar la actividad. | `oportunidad` original `OBSERVADA` o `REFERIDA`; la falta de oportunidad institucional no equivale a incapacidad. |
| `atribucion_revisada = true` | Compara ayuda actual con nivel previo y causa documentada. | Distingue causa cognitiva, física, sensorial, institucional, mixta o no determinada; no atribuye toda ayuda a cognición. |
| Soporte `IADL → cod_control_iadl` | Al menos un control original fechado y atribuible. | Debe conservar `actividad`, `oportunidad`, `nivel_previo`, `desempeno`, `tipo_ayuda`, `causa_ayuda` y `fuente`; sus valores han de ser compatibles entre sí. |

La conclusión `S` expresa **independencia relativa valorada**, no independencia absoluta ni ausencia de ayuda. Una ayuda `SUSTITUCION`, un cambio sustancial o causa `NO_DETERMINADA` requieren interpretación explícita; el motor no inventa la conclusión a partir del código. Sin IADL interpretable o sin oportunidad queda `U`. Un `N` debe documentar qué impide acreditar independencia; no surge de un campo vacío.

### C04 — Revisión diferencial

| Campo | Obtención y obligatoriedad para `S` | Calidad exigida |
|---|---|---|
| `revision_explicita = true` | Medicina/Psicología registra el acto clínico original, fechado y atribuido. | No basta ausencia de diagnósticos ni firma posterior del resultado experto. |
| `factores_pertinentes_revisados = true` | `justificacion` identifica los factores realmente revisados según el caso: afecciones agudas, fármacos, sueño, ánimo, déficits sensoriales, patología neurológica u otros pertinentes. | No obliga a una lista universal de pruebas; sí a consignar el razonamiento y el alcance de la revisión. |
| `impedimento_no_resuelto = false` | Conclusión explícita para poder acreditar C04. | `NULL` no significa ausencia de impedimento. Si es `true`, se abstiene por evaluación diferencial aun cuando haya otras evidencias. |
| Soporte `DIFERENCIAL` | `cod_valoracion_psicologica`, `cod_antecedente`, `cod_diagnostico` o `cod_documento_clinico`. | Contenido verificable, identidad, autor, fecha y vigencia. |

`N` significa revisión original que no acredita el requisito; bloquea el patrón. El motor no emite etiología, diagnóstico de demencia ni tratamiento.

### C05 — Calidad e interpretabilidad del conjunto

| Campo | Obtención y obligatoriedad para `S` | Calidad exigida |
|---|---|---|
| `suficiencia_conjunto = true` | Juicio original de que las valoraciones son suficientes para este perfil. | No se sustituye por porcentaje de cobertura o estado `COMPLETA` de un formulario. |
| `condiciones_revisadas = true` y `exclusiones_revisadas = true` | Se documentan condiciones de aplicación y exclusiones pertinentes. | La justificación identifica limitaciones por aplicación y por qué el conjunto sigue siendo interpretable. |
| Soporte `CALIDAD` | `cod_aplicacion`, `cod_valoracion_psicologica` o `cod_documento_clinico`. | Descripción original con contenido, fecha, autor, corte y estado válidos. |
| Cinco soportes `DOMINIO_REVISADO` | Uno por dominio, cada uno `cod_valoracion_revisada` de la valoración original exacta. | Los cinco juicios deben ser `A/P`, previos o simultáneos a C05, vigentes y sin interpretaciones competidoras. |

Si falta un dominio, está `I/U/X`, hay más de un juicio vigente competidor por dominio, o C05 no identifica exactamente los juicios examinados, C05 queda `U` o `X` según el conflicto. Una revisión de calidad posterior no corrige retroactivamente las fuentes originales.

## 4. Admisibilidad y resolución reproducible

1. **Corte único.** Cargar únicamente originales y soportes existentes a la fecha clínica y al corte técnico de la consulta. Excluir futuro, anulación, estado inválido, fuente ajena, autor/episodio incompatible o documento cuya integridad no se pueda comprobar. Mostrar cada exclusión y su causa.
2. **Identidad y permisos.** Verificar residente, profesional, cuenta activa, permiso de lectura de cada fuente y alcance contextual. El autor de la valoración se toma de la sesión autorizada; ninguna entrada puede atribuirla a otra persona.
3. **Calidad del soporte.** Exigir FK tipada permitida, registro original localizado, fecha clínica, estado, contenido pertinente y no duplicidad. En `MEDICION`, comprobar método/perfil original y medida aplicable 60+. La misma fuente o episodio no acredita requisitos/dominos independientes; se conserva como contexto pero los elementos afectados pasan a `U` salvo contradicción/negación/inaplicabilidad ya explícitas.
4. **Conflictos.** Dos juicios admisibles vigentes incompatibles para un mismo elemento producen `X`; no se resuelven por mayoría. Las correcciones enlazadas conservan historia, pero el cálculo toma la cabeza vigente al corte.
5. **C02 y C05.** Derivar C02; comprobar cinco dominios `A/P` y que C05 referencia exactamente uno vigente de cada uno. Aplicar gobierno versionado y vigente de las reglas Petersen. Archivar una versión no reescribe salidas históricas.

La explicación debe exponer por cada requisito: estado, juicio original, condiciones comprobadas, soportes/episodios y exclusiones. Debe distinguir dato ausente, estado negativo explícito, inaplicabilidad y contradicción; mostrar causa principal **y** causas concurrentes.

## 5. Decisión de clasificación

Se produce un patrón **sólo** si C01–C05 están `S`, los cinco dominios están `A/P`, existe al menos un `A`, C05 revisó esos cinco juicios, no hay impedimento diferencial ni contradicción, y está vigente/habilitada la regla versionada que corresponde al patrón.

| Memoria | Otros dominios `A` | Salida orientativa |
|---|---:|---|
| `A` | 0 | Posible DCL amnésico de dominio único. |
| `A` | ≥1 | Posible DCL amnésico multidominio. |
| `P` | 1 | Posible DCL no amnésico de dominio único. |
| `P` | ≥2 | Posible DCL no amnésico multidominio. |
| `P` | 0 | Sin patrón DCL clasificable por ausencia de alteración objetiva; no equivale a declarar cognición normal en general. |

**Precedencia del resultado principal:** contradicción → impedimento diferencial → información insuficiente/no interpretable → requisito negativo explícito → patrón si hay regla habilitada. Se conservan todos los impedimentos, incluidos los secundarios. Los códigos ejecutables actuales son `ABSTENCION_CONTRADICCION`, `ABSTENCION_EVALUACION_DIFERENCIAL`, `ABSTENCION_INFORMACION_INSUFICIENTE`, `SIN_PATRON_CLASIFICABLE_POR_REQUISITO`, `ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE` y `ABSTENCION_SIN_REGLA_PETERSEN_HABILITADA`. Un dominio `I`, requisito `U` o soporte inválido obliga a abstención explicada; no a un resultado favorable o desfavorable por defecto. Si falta permiso de lectura, el backend **deniega la consulta**; no presenta una evaluación parcial como si fuese completa.

**Nota metodológica:** exigir los cinco dominios `A/P`, corroboración en episodios independientes y C05 exacto es una condición de **este contrato institucional**, no un requisito universal literal de Petersen ni de NIA-AA. No se introducen puntos de corte universales, probabilidades ni equivalencias entre instrumentos. Un perfil orientativo requiere revisión clínica posterior y puede no ser confirmado por Medicina o Psicología.

### Matriz mínima de aceptación

| Caso reproducible con datos sintéticos | Resultado exigido |
|---|---|
| C01, C03, C04, C05 `S`, cinco dominios `A/P`, sólo memoria `A`, regla vigente. | Patrón orientativo amnésico de dominio único, con nueve juicios originales y soportes rastreables. |
| Igual, memoria y otro dominio `A`. | Patrón orientativo amnésico multidominio. |
| Memoria `P`, un dominio no mnésico `A`. | Patrón orientativo no amnésico de dominio único. |
| Memoria `P`, dos o más dominios no mnésicos `A`. | Patrón orientativo no amnésico multidominio. |
| Cinco dominios `P`. | C02 `N`; sin patrón DCL, aunque los demás registros estén completos. |
| Falta una medida válida, edición/método, edad aplicable o valoración original de un dominio. | Dominio `U/I`, C05 no acreditado y abstención con campo/fuente faltante. |
| C01 reutiliza el mismo episodio, C03 carece de oportunidad o C05 omite una valoración exacta. | Requisito correspondiente `U` y abstención; ninguna inferencia por defecto. |
| C04 documenta impedimento pendiente; otro elemento tiene dos juicios incompatibles. | Conservar ambas causas; contradicción como motivo principal y diferencial como concurrente. |
| Soporte posterior al corte, anulado, ajeno o documento no verificable. | Excluir con motivo; no usarlo para acreditar el requisito. |
| Regla archivada o paquete institucional sin versión vigente. | Sin patrón nuevo; el resultado histórico previo conserva su regla/versionado. |

## 6. Formulario y recorrido existente

La pantalla `CapturaPetersenPanel` ya ofrece nueve elementos: cinco dominios y C01, C03, C04, C05. No debe aparecer una entrada manual C02. Para cada elemento presenta estado, fecha clínica, justificación, condiciones pertinentes, atención/documento cuando procedan y selector de soportes originales por tipo. La Action valida también en backend y conserva rectificación/anulación sin borrado. La consulta institucional deriva estados y patrón; la revisión del resultado es un acto posterior separado. El formulario debe ayudar a elegir el soporte correcto y explicar el faltante antes de guardar; la visualización de `U`/`X` debe mantener el motivo y el vínculo original.

| Requisito del contrato | Componente actual | Verificación existente |
|---|---|---|
| Captura original, autor, fecha, rectificación, permisos | `RegistrarValoracionPetersenOriginal`, `CapturaPetersenPanel` | `tests/Feature/CapturaPetersenOriginalTest.php` |
| Fuentes y FKs admisibles | `ContratoFuentesPetersen`, `ConsultarPetersenInstitucional` | `tests/Feature/CapturaPetersenOriginalTest.php` |
| Acreditación, C02, cuatro patrones, abstenciones | `EvaluarPetersenInstitucional`, `PaquetePetersenInstitucional` | `tests/Unit/PetersenInstitucionalTest.php` |

**Límites abiertos:** este contrato no demuestra que todos los residentes tengan mediciones originales interpretables, IADL y revisión diferencial. Tampoco certifica la licencia, capacitación o pertinencia de un instrumento concreto, ni resuelve por sí mismo la conservación/reproducción relacional íntegra de toda la evaluación conjunta dentro del máximo de 104 tablas. Esos faltantes se muestran como tales; nunca se completan desde bibliografía o datos de prueba.

## 7. Fundamento y autoridad

- [Contrato institucional E–H adoptado](PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md) y [diccionario de captura original aprobado](PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md): autoridad de las reglas locales aquí concretadas.
- Petersen RC. *Mild cognitive impairment as a diagnostic entity*. Journal of Internal Medicine. 2004;256:183–194. [doi:10.1111/j.1365-2796.2004.01388.x](https://onlinelibrary.wiley.com/doi/10.1111/j.1365-2796.2004.01388.x): tipología amnésica/no amnésica, uno o varios dominios.
- Winblad B et al. *Mild cognitive impairment—beyond controversies, towards a consensus*. Journal of Internal Medicine. 2004;256:240–246. [doi:10.1111/j.1365-2796.2004.01380.x](https://onlinelibrary.wiley.com/doi/10.1111/j.1365-2796.2004.01380.x): cambio cognitivo, objetivación y función cotidiana en la valoración clínica.
- Albert MS et al. *The diagnosis of mild cognitive impairment due to Alzheimer's disease*. Alzheimer's & Dementia. 2011;7:270–279. [Texto original](https://pmc.ncbi.nlm.nih.gov/articles/PMC3312027/): preocupación/cambio, alteración en al menos un dominio, independencia funcional y revisión de otras causas. O.R.I.O.N. **no** infiere etiología Alzheimer.
- Alzheimer's Association. *DETeCD-ADRD: executive summary for primary care* y *Validated clinical assessment instruments*. 2025. [Guía](https://pmc.ncbi.nlm.nih.gov/articles/PMC12173843/) · [Instrumentos](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/): historia, contexto, funcionalidad e interpretación de pruebas; no proporcionan una equivalencia automática entre observación y medición por dominio.

La bibliografía fundamenta las dimensiones clínicas generales. Los nombres de campos, la exigencia de cinco dominios y las compuertas exactas provienen de las decisiones institucionales E–H y de su implementación versionada, no son citas literales de esas publicaciones.
