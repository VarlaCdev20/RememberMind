# Sistema experto — RFC de conservación del perfil experimental

Estado: **diseño pendiente de decisión estructural; no aprobado ni aplicado**. Fecha: 18/10/2026. Alcance: contrato `PERFIL-EXPERIMENTAL-1`, exclusivamente laboratorio sintético. No solicita autorización asistencial.

## Problema comprobado

El perfil ejecuta Memoria, G09, Petersen y su puente con versiones diferentes. `evaluaciones_expertas.cod_version_modelo` admite una sola FK; `id_evaluacion_dominio` es único. Las lecturas de `AlmacenInstantaneas` y `EvaluacionExpertaEloquentRepository` contrastan criterios, fuentes y mapeos contra esa misma versión. El contrato `ORION-SNAP-2` tiene una memoria con una versión, no el manifiesto compuesto del perfil nuevo.

No basta con quitar esas comprobaciones: permitiría mezclar reglas y evidencias ajenas. Tampoco sirve guardar varias cabeceras bajo el mismo `id_evaluacion_dominio`, escribir el manifiesto en observaciones o crear una versión común ficticia. El inventario vigente es 73 tablas operativas + 33 expertas; esta RFC no lo cambia.

Hay una segunda brecha independiente: deben conservarse las atestaciones de contexto, valores de respuestas descartadas y metadatos originales del instrumento, autor/estado y rechazo. Las FK a fuentes actuales no prueban qué decía una fuente mutable cuando se ejecutó. Resolver únicamente el manifiesto no resuelve toda la instantánea.

## Alternativas acotadas

| Alternativa | Cambios | Alcance y coste |
|---|---|---|
| A. Conservar evidencia experimental según Prompt§14 | Cero tablas, columnas, estados y catálogos nuevos | Perfil en memoria más archivo técnico privado cifrado: entrada, salida, conocimiento, huellas y replay exacto. Implementado y probado; no equivale a conservación institucional en BDD. |
| B. Manifiesto fijo en la cabecera | Cero tablas nuevas; tres FK nullable y una columna de contrato de orquestación, sujetas a aprobación | Adecuado al alcance fijo Memoria/G09/DCL/puente. Requiere adaptador de conservación propio y lectura que valide cada componente contra su versión exacta. No amplía por sí solo la conservación de fuentes. |
| C. Manifiesto normalizado extensible | Una relación nueva evaluación/componente/versión con PK/FK/UNIQUE expresas, sujeta a aprobación | Permite componentes futuros, pero añade mantenimiento. No se justifica todavía una colección de tablas nuevas ni sustituye las copias escalares existentes. |

**Decisión técnica compatible para esta fase:** utilizar A, expresamente permitida por §14. El prototipo ya dispone de historia técnica reproducible. B continúa como candidato para conservación institucional; requiere resolver también las capturas originales. No ejecutar una migración ni ampliar el contrato congelado a partir de esta recomendación.

## Diccionario candidato de B — únicamente manifiesto

Tabla afectada: `evaluaciones_expertas`. Su PK, FK actuales, unicidad y registros históricos se conservan.

| Columna candidata | Tipo / nulabilidad | Referencia y significado |
|---|---|---|
| `cod_version_g09` | string(20), nullable | FK restrictiva a `versiones_modelo_experto.cod_version_modelo`; versión real y conservada de G09. |
| `cod_version_dcl` | string(20), nullable | Misma FK restrictiva; conocimiento/contrato técnico de Petersen. |
| `cod_version_puente_dcl` | string(20), nullable | Misma FK restrictiva; revisión exacta del puente de referencias. |
| `version_orquestacion` | string(30), nullable | Identificador inmutable del contrato técnico que combina los componentes, no una aprobación clínica. |

`cod_version_modelo` seguiría identificando el paquete real de Memoria para este nuevo contrato. La política de nulabilidad completa, índices, checks y valores de contrato se deberá aprobar junto con el diccionario final. Una cabecera histórica sin el nuevo contrato conserva la lectura estricta actual. No se rellenan columnas históricas con versiones actuales ni se reinterpretan resultados antiguos.

Este diccionario **no es una migración lista para aplicar**: falta cerrar, por fuente, la representación de las atestaciones e instantáneas originales. No se propone repetir documentos completos, guardar arrays JSON/EAV ni usar Activitylog como almacén de resultados.

## Contrato que debe cerrar el adaptador antes de escribir

1. Registrar revisiones reales de conocimiento y contratos técnicos, conservando sus códigos y contenidos inmutables. Una FK existente no demuestra por sí sola que la revisión sea elegible.
2. Aislar memoria y reglas de cada componente; validar las referencias contra la versión del componente y la cabecera/caso/corte común. El camino histórico `ORION-SNAP-2` conserva sus invariantes.
3. Mapear cada dato de entrada a una copia escalar tipada, FK original o relación normalizada aprobada. La matriz debe incluir captura bruta de respuestas admitidas y rechazadas, contexto de Memoria/G09 y referencias independientes DCL, con sus fechas, autor/procedencia y estado originales.
4. No exigir que un autor histórico identificable siga activo hoy; sí revalidar la cuenta y autorización del ejecutor actual. No inventar un autor ausente.
5. Guardar cabecera, hechos, soportes, condiciones, resultados, causas de no emisión y manifiesto en una sola transacción. Error en cualquier paso revierte el conjunto.
6. Identidad técnica idempotente: misma entrada/versiones recupera el mismo resultado; contenido diferente bajo la misma identidad se rechaza.
7. Leer exclusivamente la instantánea conservada para explicar o repetir una evaluación. La modificación posterior de una fuente no cambia su resultado histórico.
8. Rechazar explícitamente una entrada que contenga información todavía no representable. No afirmar éxito de conservación parcial.

## Aceptación verificable de la futura conservación

- SQLite y PostgreSQL desechables: guardar → recuperar → comparar exactamente entrada, corte, versiones, datos y trazas.
- Cambiar respuestas, estado/autor de la fuente o interpretación vigente después del guardado: la instantánea anterior permanece intacta.
- Cambiar una versión del manifiesto o mezclar criterio/mapeo/soporte de otro componente: rechazo sin escrituras parciales.
- Duplicado idéntico e intento de reutilizar identidad con contenido diferente; rollback a mitad del conjunto.
- Retirar una versión: impide nueva ejecución elegible y permite consultar la evaluación histórica exacta.
- Autorización contextual del laboratorio y cero efectos en historias, alertas, cuidados, prescripciones o tratamientos.

La aprobación futura de un diccionario no equivale a su implementación o verificación. El ciclo de vida técnico ya está probado mediante conocimiento SQL y archivo experimental cifrado: crear → ejecutar → sucesora → retirar → consultar/reproducir original. La conservación integral en BDD sigue abierta. Véase [aceptación integral](FASE01_ACEPTACION_INTEGRAL_2026_10_18.md).
