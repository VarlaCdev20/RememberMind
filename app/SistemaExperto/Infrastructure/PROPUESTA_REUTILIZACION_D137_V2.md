# Conservación con reutilización de D-137: propuesta de nueve tablas adicionales

Fecha: 17/10/2026. Estado: **ALTERNATIVA APROBADA POR LA PROPIETARIA**.

Tras revisar la mantenibilidad, la propietaria indicó expresamente realizar la
incorporación de nueve tablas y reducirlas en una tarea posterior. La instrucción
posterior sustituye el estado de revisión anterior. El diccionario físico aplicado
está en [la decisión de conservación V2](../../../docs/base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md).
Las futuras reducciones conservarán historia y contratos mediante otra transición.

## Alcance y conteo

El alcance aprobado conserva las **23 tablas expertas existentes** y añadir **9**:
**32 tablas expertas**. Con las **73 operativas aprobadas**, incluidas Galería
e IADL, serían **105 tablas de negocio**. Las tablas técnicas de Laravel,
Spatie y secuencias no están incluidas. Nueve es el alcance elegido por la propietaria; no se afirma que sea el mínimo
matemático. La evidencia de ejecución se registra en VERIFICACION_FASES.md.

El conteo depende de ampliar de forma expresa el contrato de las tablas
reutilizadas. No basta con insertar nuevos códigos en columnas string. Si se
exigiera mantener intactas todas las columnas y catálogos D-137, este diseño
no sería aplicable y habría que reconsiderar la alternativa suplementaria.

## Fuentes y autoridad

- VIGENTE: instrucción actual de reutilización y alcance previo de conservar
  casos sintéticos e instantáneas de fuentes institucionales.
- VIGENTE: `docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md`,
  `REMEMBERMIND_BDD_70_TABLAS.md` y decisiones aprobadas de Galería e IADL.
  Inventario actual de negocio: 73 operativas + 23 expertas = 96.
- EVIDENCIA DE IMPLEMENTACIÓN: las 23 migraciones
  `database/migrations/2026_10_08_120001..120023`, sus Models y
  `PersistirEvaluacionTecnica` / `LecturaResultadosExperto` de ApoyoDecision.
- CONTRATO A CONSERVAR: `EvaluacionExpertaRepositoryInterface`,
  `MemoriaTrabajo`, `VectorCognitivo`, `ResultadoClasificacionDcl` y sus
  entidades referenciadas en `app/SistemaExperto/Domain`.
- PROPUESTA ALTERNATIVA: `PROPUESTA_CONSERVACION_V1.md`, de 16 tablas nuevas;
  permanece como antecedente comparativo, sin aprobación ni migraciones.
- PRUEBAS LOCALIZADAS: `BddOperativaV2Test` y pruebas del repositorio de
  evaluaciones. En esta revisión documental no se ejecutan suites ni DB.

El contrato nuevo tiene hallazgos y estados experimentales distintos de G09.
Reutilizar las tablas no autoriza convertir resultados descriptivos en A/P ni
presentar el nuevo contrato como aprobación clínica.

## Qué se reutiliza

| Responsabilidad | Tablas existentes | Ampliación propuesta |
|---|---|---|
| Evaluación, residente y versión | `evaluaciones_expertas` | Corte original con precisión, zona y offset; versión de conservación y huella tipada. |
| Vocabulario y significado | `nodos_semanticos`, `dominios_valores_expertos`, `valores_semanticos`, `criterios_dominios_resultado` | Catálogos experimentales versionados de cinco dominios, 19 subcomponentes posibles y C01–C05; sin reinterpretar el vocabulario G09. |
| Instancias de dominios, subcomponentes y requisitos DCL | `evaluacion_criterios` | Discriminador del contrato/tipo, padre para subcomponentes y orden. No se crea una segunda tabla de resultados por cada nivel. |
| Resultado de cada instancia | `resultados_criterio` | Justificación original exacta y vínculo con el catálogo propio del tipo. |
| Evidencias de fuentes institucionales | `evidencias_evaluacion` y sus mapeos/fuentes existentes | Mantener la FK obligatoria a un mapeo real y enlazar la copia del hecho utilizado. |
| Soportes institucionales de criterios | `evidencias_criterio_evaluacion` | Conservar el orden y formalizar los roles del contrato nuevo. |
| Conocimiento y trazas compatibles con D-130 | Reglas, condiciones, consecuencias y trazas existentes | Reutilizar su representación vigente cuando sea compatible; conservar sus consumidores. |

Las tablas no mencionadas se conservan. No se borra ninguna de las 23.

### Condiciones de la ampliación de criterios

- El tipo distingue registros anteriores, dominio experimental, subcomponente
  experimental y requisito DCL. El contrato anterior no cambia de significado.
- El subcomponente tiene un padre dominio de la misma evaluación. Un requisito
  C01–C05 no se presenta como sexto dominio ni como subcomponente.
- La evaluabilidad 0/1/2 pertenece al resultado de dominio. Subcomponentes y
  requisitos DCL no tienen ese atributo en Domain. Se propone permitir NULL
  **solo para esos tipos nuevos**, con restricción por tipo; no asignar una
  evaluabilidad ficticia. Los registros anteriores conservan su obligación.
- Los resultados mantienen sus propios vocabularios: A/P/U/I/X para dominios,
  hallazgos instrumentales para subcomponentes y estados C01–C05 para DCL.
- La justificación nueva no sustituye `motivo_evaluabilidad`, que tiene otra
  responsabilidad. Los registros anteriores no reciben explicaciones inventadas.
- El orden de soportes se conserva entre los vínculos institucionales y los
  sintéticos, sin duplicar una misma evidencia. El escritor verifica unicidad
  conjunta en la transacción; dos índices aislados no prueban esa garantía.
- Deben actualizarse lectores, Models y validadores para seleccionar el tipo
  correcto y reconstruir todos los registros. El mapper actual espera cinco
  dominios y no puede consumir indiscriminadamente la colección ampliada.

## Las nueve tablas que faltan

Los nombres siguientes son propuestos. Cada PK nueva conserva el patrón
`cod_*` string(20), con el generador existente; las FK preservan tipos, índices
y borrado restrictivo. La evaluación y sus identidades institucionales se
reutilizan: no se crean residentes, usuarios o mapeos de conveniencia.

| N.º | Tabla nueva propuesta | Grano, relaciones y contenido |
|---|---|---|
| 1 | `hechos_instantanea_experta` | Un hecho original por evaluación: ID Domain, fuente, registro, episodio, concepto, fecha exacta, valor tipado y orden. FK a `evaluaciones_expertas` y, para un origen D-137, a su `evidencias_evaluacion` real. |
| 2 | `evidencias_cognitivas_instantanea` | Una caracterización cognitiva por hecho: dominio, subcomponente opcional, hallazgo, instrumento, justificación, orden y las siete referencias SIM fijas. FK única a la tabla 1. |
| 3 | `soportes_sinteticos_criterio` | Un vínculo ordenado entre `evaluacion_criterios` y un hecho sintético de la tabla 1. Sirve para soportes cognitivos y C01–C05 sin falsear un mapeo operativo. Los institucionales mantienen su tabla existente. |
| 4 | `funcionalidad_instantanea_experta` | Un perfil funcional por evaluación: independencia relativa declarada, revisor sintético y justificación. FK única a `evaluaciones_expertas`. |
| 5 | `evidencias_iadl_instantanea` | Una evidencia IADL del perfil: hecho, actividad, desempeño, oportunidad, nivel previo, causa de ayuda, atribución nullable, revisor y orden. FK a las tablas 1 y 4. |
| 6 | `clasificaciones_dcl_instantanea` | Una clasificación por evaluación: versión de contrato, diferencial, candidato nullable, justificación, marcador experimental y huella. FK a la evaluación y al perfil funcional. C01–C05 y sus resultados se recuperan de los criterios reutilizados de esa misma evaluación. |
| 7 | `motivos_dcl_instantanea` | Una razón ordenada de abstención por clasificación. FK a la tabla 6; vocabulario cerrado y sin duplicados. |
| 8 | `reglas_aplicadas_dcl_instantanea` | Una copia de cada regla declarada como aplicada por clasificación: ID Domain, fuente, habilitación, consecuencia tipada y orden. FK a la tabla 6 y FK a `reglas_expertas` si el origen es D-137. No se inventan reglas para llenar la colección vacía actual. |
| 9 | `condiciones_regla_instantanea` | Un nodo del árbol de una regla de la tabla 8: operador, padre, orden y literal tipado cuando corresponde. Conserva Y/O/NO/IGUAL_A/DISTINTO_DE sin convertir D-130 en otra gramática. |

Las tablas 1, 2, 4, 5, 6, 7 y 9 conservan los campos aplicables definidos en la
propuesta V1. La tabla 3 sustituye los soportes suplementarios separados. La
tabla 8 conserva directamente regla y pertenencia a la clasificación: no hace
falta una tabla de reglas por evaluación más otra unión solo para las aplicadas.
Una regla utilizada en dos clasificaciones tiene dos copias históricas, cada
una propiedad de su clasificación; no se sobrescribe una copia compartida.

La tabla 1 tiene formas de origen cerradas y las FK reales correspondientes.
No es una captura clínica universal campo/valor. Conserva exclusivamente hechos
ya extraídos mediante mapeos autorizados o referencias sintéticas admitidas por
los constructores actuales. Mantiene NULL/false/0, tipos escalares, bits de
float finito y fechas con zona/precisión conforme a la propuesta V1.

La tabla 5 guarda la instantánea usada por el análisis. La operativa
`controles_funcionales_iadl` sigue siendo la fuente aprobada y ya está incluida
en las 73 operativas; no se cuenta de nuevo como una de estas nueve.

## Cómo se reduce la alternativa de 16 a nueve

| Elementos de la propuesta anterior | Resolución | Tablas nuevas evitadas |
|---|---|---:|
| Cabecera `instantaneas_expertas` | Ampliar `evaluaciones_expertas`. | 1 |
| Resultados y soportes separados de dominios y subcomponentes | Reutilizar criterios/resultados y soportes institucionales. Los sintéticos usan una única tabla nueva común. | 4 |
| `requisitos_dcl_instantanea` | Reutilizar criterios y resultados con tipo DCL y versión explícita. | 1 |
| Regla por evaluación y unión adicional a clasificación | Guardar la copia aplicada directamente bajo su clasificación. | 1 |
| **Reducción** | **16 − 7 = 9 nuevas.** | **7** |

La tabla común de soportes sintéticos ocupa el lugar de
`soportes_requisitos_dcl_instantanea` de V1, ampliando su alcance también a los
soportes sintéticos de dominios y subcomponentes. Por eso no añade una décima.

## Implementación y verificación

El diccionario aprobado, las nueve creaciones, las ampliaciones de criterios y
los catálogos experimentales se implementan mediante una migración forward.
El repositorio conserva y recupera memoria, perfil y DCL; el contrato anterior
continúa disponible para sus lecturas existentes. El vinculador puede operar
sin Closure de memoria cuando la evaluación usa el contrato nuevo.

Las pruebas cubren reconstrucción con instancias nuevas, tipos/fechas/orden,
originales compartidos, 19 subcomponentes, cuatro PET y abstención, idempotencia,
rollback, procedencia institucional y reversión protegida. Comandos y resultados
se consolidan en [VERIFICACION_FASES.md](../VERIFICACION_FASES.md).

La implementación no incluye informe clínico HTML/PDF ni conservación de columnas
fuente no utilizadas. Se conserva el puerto de memoria/vector/DCL, sin almacenar
objetos en JSON, serialización PHP o archivos. No implica aplicar migraciones a
una base institucional o habilitar inferencia asistencial.
