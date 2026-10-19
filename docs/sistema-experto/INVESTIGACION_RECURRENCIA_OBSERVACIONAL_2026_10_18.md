# Recurrencia observacional — investigación y recomendación técnica

Fecha: 18/10/2026. Instrucción de la propietaria: «INVESTIGA LO MAS OPTIMO», respecto a la ventana y mínimo de episodios de OBS-MEM-03 y OBS-SV-02.

**Estado: investigación completada; parámetros propuestos para un piloto descriptivo, todavía no activados.** No es un protocolo clínico aprobado ni una validación de exactitud. Las tres reglas booleanas de la primera entrega permanecen independientes.

## Qué permite concluir la evidencia

No se encontró en las guías consultadas un umbral universal de cantidad de olvidos en determinados días que diagnostique deterioro cognitivo, ni un mínimo universal de lecturas fuera de un objetivo individual que establezca recurrencia clínicamente significativa en una residencia geriátrica. La revisión fue dirigida a guías y recomendaciones primarias; no constituye una revisión sistemática exhaustiva.

- [NICE NG97, recomendaciones 1.2.1–1.2.3](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations/): la evaluación considera historia, repercusión cotidiana, informante y evaluación clínica/instrumental. No prescribe contar un número fijo de olvidos durante una semana para inferir DCL.
- [DETeCD-ADRD, guía de la Alzheimer's Association, recomendación 1 y 4, DOI 10.1002/alz.14333](https://alz-journals.onlinelibrary.wiley.com/doi/10.1002/alz.14333): un cambio cognitivo, conductual o funcional comunicado puede justificar evaluación; la historia incorpora cognición, función e informantes. No corresponde esperar a alcanzar el mínimo de una regla informática para permitir revisión profesional.
- [NICE NG136, recomendación 1.2.7 y 1.4.21–1.4.22](https://www.nice.org.uk/guidance/ng136/chapter/recommendations): el protocolo domiciliario para confirmar hipertensión recoge mediciones emparejadas mañana y tarde durante al menos cuatro días, idealmente siete, y utiliza promedios tras descartar el primer día. También exige juicio clínico en fragilidad/multimorbilidad. Ese protocolo no equivale a dos observaciones institucionales fuera del objetivo ni autoriza trasladar objetivos poblacionales a cada residente.
- [American Heart Association, instrucciones de medición domiciliaria](https://professional.heart.org/-/media/Files/Health-Topics/High-Blood-Pressure/How_to_Measure_Your_Blood_Pressure_Letter_Size.pdf): las lecturas repetidas en una misma sesión forman parte de la técnica de medición. Contarlas como episodios independientes inflaría artificialmente una regla de recurrencia.

## Recomendación para RememberMind

La opción más defendible es un recuento transparente y revisable, con ventanas visibles y cobertura documentada. No presentar un número elegido por ingeniería como un umbral médico óptimo.

| Regla | Ventana principal propuesta | Repetición descriptiva propuesta | Contexto adicional |
|---|---|---|---|
| OBS-MEM-03: observaciones de dificultad de memoria | Últimos 7 días hasta el corte | Al menos 2 episodios independientes en 2 días locales distintos | Recuento y fechas de los últimos 30 días; conservar también cada observación aislada |
| OBS-SV-02: presión fuera del objetivo individual | Últimos 7 días hasta el corte | Al menos 2 episodios evaluables fuera del objetivo en 2 días locales distintos | Días medidos, mediciones válidas, objetivo aplicado por fecha y detalle de los últimos 30 días |

**Siete días, treinta días y dos días son propuestas operativas de ingeniería para contrastar en un piloto.** La referencia semanal de presión se inspira en el horizonte de observación de NG136, pero no reproduce su protocolo diagnóstico. La ventana de memoria y el mínimo de dos días no proceden de un punto de corte clínico validado: permiten resumir registros recientes y reducir el doble conteo. No hay evidencia consultada que demuestre que esta combinación sea superior a otras en la población de RememberMind.

Usar días de calendario en `America/La_Paz`: siete días comprende el día del corte y los seis anteriores; treinta comprende el día del corte y los veintinueve anteriores. Nunca incluir registros posteriores al corte. La salida debe identificar esa convención y su versión para ser reproducible.

## Condiciones antes de implementar o activar la segunda entrega

1. **Independencia de episodios:** la misma atención o sesión cuenta una vez, aunque tenga varios registros, autores o relecturas. Una PK distinta no demuestra un episodio distinto. Sin sesión identificable, agrupar conservadoramente por día para este resumen y declarar la limitación; no afirmar independencia clínica no documentada.
2. **Memoria:** no sumar `repite_preguntas` y `olvida_indicaciones` del mismo episodio como dos eventos. Mantener ambos indicadores en la explicación. `cambio_cognitivo` no se convierte automáticamente en dificultad de memoria. La primera observación sigue disponible para revisión sin esperar la recurrencia.
3. **Presión:** utilizar el objetivo individual válido para la fecha y contexto de medición, con procedencia y límites originales. Un objetivo actual no debe aplicarse retrospectivamente. No sustituir un objetivo ausente por un baremo general. Mostrar por separado valores por encima y por debajo del objetivo; no describirlos indistintamente como hipertensión.
4. **Calidad y cobertura:** excluir fuentes anuladas/inválidas según el contrato, conservar motivo y desconocidos; informar días observados y días sin datos. Falta de registros o de objetivo no equivale a normalidad. El mínimo de positivos no define por sí solo cuándo una ventana incompleta permite un resultado negativo: ese contrato se debe resolver antes de activar la regla.
5. **Separación de procesos:** estos resúmenes no modifican DCL, gravedad, tratamiento, medicación, alertas de urgencia ni decisiones profesionales. El horizonte semanal no retrasa la atención de un hallazgo aislado que requiera revisión por el flujo clínico vigente.
6. **Validación local:** comparar resultados con valoraciones independientes de Medicina/Psicología sobre casos con fuentes completas. Separar casos usados para ajustar parámetros de los usados para verificar el ajuste. Medir duplicación, cobertura, desacuerdos y carga de revisión; no fabricar sensibilidad, especificidad ni concordancia. Conservar el conjunto y la versión de cada comparación.
7. **Gobierno:** documentar decisión de los responsables y versión antes de habilitar reglas. Cambiar parámetros crea otra versión; no recalcular ni sobrescribir historia conservada. Esta investigación no amplía permisos ni aprueba cambios estructurales de BDD.

## Alcance entregado

Se resuelve la investigación solicitada y se entrega una propuesta concreta para revisión. OBS-MEM-03 y OBS-SV-02 no se incorporan al paquete activo `OBS-20261017-v1`; siguen pendientes el contrato de cobertura, la representación histórica completa del objetivo de presión y la validación del piloto. El siguiente paso técnico es comprobar esas fuentes y contratos contra el repositorio, sin inventar objetivos o aprobaciones.
