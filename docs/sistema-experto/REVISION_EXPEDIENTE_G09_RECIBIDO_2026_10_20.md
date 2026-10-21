# Revisión del expediente G09 recibido

Fecha: 20/10/2026. Alcance: lectura técnica de los archivos entregados, sin importación de datos, cambio de reglas, permisos ni esquema. Este dictamen complementa el [dictamen instrumental previo](DICTAMEN_PROPUESTA_INSTRUMENTAL_2026_10_20.md).

## Identidad y contenido

| Archivo recibido | Huella SHA-256 | Contenido verificable |
| --- | --- | --- |
| `ORION_G09_Expediente_Tecnico_Propuesta_Expertos.docx` | `BFA9911CDD29E3FF3C9AC9F3E200DF0B107738908F40DF907D9CCA5F5FE1784B` | 93 párrafos y 12 tablas: instrumentos candidatos, formularios, correspondencias, abstenciones y rúbrica de revisión. Declara expresamente estado **PROPUESTA FUNDAMENTADA**, sin firma, licencia o evaluación de residente. |
| `ORION_G09_Matriz_Clinica_y_Registros.xlsx` | `797B66AFA8E11F9487C949F55B9EEFD8257F85542BE14037A0E27FA2E15732A5` | Diez hojas; 19 filas candidatas en `Matriz_G09`; hojas PSI/MED/ENF/FUN con `DEM-001` y ejemplos explícitamente ficticios. La matriz marca Psicología/Medicina `PENDIENTE` y habilitación `NO HABILITADA` en sus 19 filas. |

Los ejemplos `DEM-001` no son aplicaciones originales ni historia clínica de los residentes; no se importan. Los documentos no prueban que Lorena, Carolina, Rosa, Marcos u otro profesional hayan revisado o firmado su contenido.

## Cotejo con la autoridad vigente del proyecto

1. **Inventario de BDD.** Ambos archivos aluden a 69 tablas operativas como si fuera el estado actual. Es una referencia histórica. El [índice experto](README.md) y la [decisión de límite](DICTAMEN_LIMITE_100_TABLAS_2026_10_20.md) sitúan el inventario de negocio actual en **104** y registran el máximo posterior de **100**. El expediente no habilita migraciones.
2. **Identidad de los componentes.** La [matriz maestra vigente](FASE_2_MATRIZ_MAESTRA_INFERENCIA_2026_10_20.md) ya identifica los cinco criterios y 19 componentes por código. La hoja recibida declara los suyos `NO HOMOLOGADO` y usa otra distribución semántica: coloca memoria de trabajo en Atención cuando el contrato vigente la sitúa en `EJE-S03`; reemplaza Atención dividida por esa fila; incluye Secuenciación en Ejecutivas donde el contrato vigente define Memoria de trabajo. No se pueden importar sus filas por posición ni homologarlas por semejanza textual.
3. **Reglas.** El proyecto tiene **15 G09** para ATE/EJE/LEN/VIS y **dos reglas de Memoria**, con C1 ∧ C2 ∧ C3 de una misma aplicación. La hoja llama `G09-01` a `G09-19` a sus 19 filas; son identificadores de propuesta, no los códigos versionados `R-ATE-*`, `R-EJE-*`, `R-LEN-*`, `R-VIS-*` ni `MEM-*`. La etiqueta `NO HABILITADA` describe la propuesta recibida; no reemplaza el estado técnico de las reglas registradas, cuya emisión concreta sigue condicionada a evidencia instrumental admisible.
4. **Instrumentos.** MoCA Full 8.1, SPMSQ-VE, MMSE-2, Barthel, Katz y Lawton son **candidatos**. Su presencia en un catálogo de la hoja no acredita material oficial, derecho de integración, capacitación ni medición original. [MoCA](https://www.mocacognition.com/paper) requiere certificación para interpretar tareas/dominios; sus [términos](https://mocacognition.com/terms-of-use) exigen autorización separada para incorporarlo a otro producto. [MMSE-2](https://www.parinc.com/products/MMSE-2) es material editorial con formas diferenciadas. La [adaptación castellana SPMSQ de 2001](https://pubmed.ncbi.nlm.nih.gov/11472684/) estudió población de **65 años o más**; no fundamenta por sí sola transferencia a residentes de 60–64 años ni a cada subcomponente G09.
5. **Funcionalidad.** Barthel y Katz describen actividades básicas y no satisfacen por sí solos C03 IADL. La ficha de [Lawton del Hartford Institute](https://hign.org/consultgeri/try-this-series/lawton-instrumental-activities-daily-living-iadl-scale) advierte que la escala no es útil para personas mayores institucionalizadas. La distinción del expediente entre capacidad, ayuda y rutina de la residencia es aprovechable para entrevista contextual; no corresponde computar dependencia cognitiva por una actividad no realizada por política del centro.
6. **Gobierno clínico.** Las compuertas RG/A del expediente son propuestas; varias coinciden en intención con invariantes existentes, pero sus códigos, disparadores y consecuencias deben cotejarse individualmente antes de sustituir o crear una regla. La notificación clínica urgente propuesta ante confusión aguda requiere el protocolo institucional de escalamiento y su autorización; no se activa desde este documento.

## Qué aportó y qué falta pedir a los expertos

El paquete aporta un **borrador de protocolo**, campos de captura, candidatos instrumentales, confusores y ejemplos sintéticos útiles para pruebas. No aporta las tres evidencias que desbloquean un perfil por componente:

1. **Selección documentada del instrumento real**: forma, edición, idioma, material oficial, permiso aplicable al uso en RememberMind, profesional competente/certificación cuando corresponda, método y población de referencia pertinente a mayores de 60 años.
2. **Aplicación original nueva** por residente: profesional y fecha clínica, respuestas/medidas por tarea, condiciones, omisiones, puntuación conforme al manual y soporte/documento original. Las observaciones previas siguen observacionales.
3. **Homologación profesional de la matriz del proyecto**: para cada tarea, código de componente vigente, medición, método, exclusiones, C1/C2/C3, consecuencia limitada y casos de abstención. Psicología y Medicina registran sus revisiones separadas; ninguna firma se presume por la recepción de los archivos.

La adquisición y las pruebas técnicas pueden continuar con datos sintéticos y contratos existentes. Hasta recibir esas evidencias, los nuevos perfiles interpretativos permanecen sin activar; las reglas que carezcan de antecedentes admisibles deben explicar su abstención. No se crean datos de pacientes, cortes clínicos, pesos ni equivalencias a partir de este expediente.
