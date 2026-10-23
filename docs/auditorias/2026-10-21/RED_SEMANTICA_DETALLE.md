# Red semántica: estructura, participación e inferencia

Este detalle describe el contrato implementado del paquete cognitivo base.
Los códigos son semánticos, no las PK físicas de una versión instalada.
El diagrama no representa el resultado de ningún residente.

![Relaciones y uso de la red](graficos/04-red-semantica.png)

## Qué significa cada relación

| Relación del paquete | Origen → destino | Efecto real | Lo que no demuestra |
|---|---|---|---|
| `SUBCOMPONENTE_DE` | SUBCOMPONENTE → CRITERIO | Pertenencia primaria; por ejemplo ATE-S01 → COG-ATE | Que el componente esté alterado o que una variable participe por transitividad |
| `APORTA_EVIDENCIA` | VARIABLE → CRITERIO | El contrato permite participación inferencial; además exige relación activa y tipos/modalidad compatibles | Que exista un hecho suficiente o que la condición se cumpla |
| `CONTEXTUALIZA` | VARIABLE → CRITERIO | Conserva contexto sin habilitar participación como antecedente instrumental | Que una observación sustituya una medición instrumental |

Fuente: [PaqueteCognitivoInstitucional.php:22](../../../motor-experto/src/motor_experto/dominio/conocimiento/versiones/PaqueteCognitivoInstitucional.php:22).
Estos son predicados de ese paquete; no una lista universal para cualquier
contrato de conocimiento o versión experimental.

## Operación real, paso por paso

1. `RedSemantica` carga nodos y aristas de una sola versión. Rechaza identidad
   duplicada, referencia inexistente, versión ajena y relación sin contrato
   único de tipos/modalidad. No aprende pesos ni interpreta textos.
2. `PertenenciaVariableCognitiva` comprueba propietario primario. Si es un
   subcomponente, debe pertenecer a un único dominio primario activo y existir
   además una arista directa de la variable al criterio. La pertenencia de
   ATE-S01 a Atención no basta para habilitar cualquier variable relacionada.
3. `PaqueteConocimiento::variableParticipa` examina aristas **directas**, activas,
   y el contrato con `habilita_participacion=true`. Una arista de contexto no
   satisface esa prueba. No ejecuta una expansión transitiva de evidencia.
4. El evaluador compara hechos admisibles con los antecedentes de las reglas.
   Por ejemplo, R-ATE-S01-P01 requiere C1, C2 y C3 acreditadas para emitir su
   consecuencia. La arista habilita el vínculo; el hecho y la regla deciden el
   resultado. Las condiciones deben satisfacer también las compuertas de su
   familia. El grafo conceptual por sí solo no calcula alteración.
5. El explorador Laravel construye un payload documental desde aristas
   existentes y otro payload de inferencia histórica. El recorrido para
   organizar columnas visuales no vuelve a ejecutar reglas ni convierte todas
   las conexiones mostradas en evidencia utilizada. Reautoriza y recupera la
   lectura en el servidor antes de preparar ambos grafos.

Fuentes: [RedSemantica.php:15](../../../motor-experto/src/motor_experto/dominio/conocimiento/red_semantica/RedSemantica.php:15),
[PertenenciaVariableCognitiva.php:12](../../../motor-experto/src/motor_experto/dominio/conocimiento/PertenenciaVariableCognitiva.php:12),
[PaqueteConocimiento.php:88](../../../motor-experto/src/motor_experto/dominio/conocimiento/PaqueteConocimiento.php:88),
[ConsultarExploracionOrion.php:20](../../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarExploracionOrion.php:20).

## Cómo mejorar su explicación

Mantener visibles dos modos: **conocimiento de la versión** y **ejecución de
la evaluación**. En el segundo, cada enlace debe indicar si participó y sus
fuentes, condición y estado; no colorear como cumplidas todas las relaciones
del catálogo. Conservar relaciones transversales y contradicciones sin
inventar un árbol clínico. La explicación clínica accesible a Medicina o
Psicología necesita un contrato de contenido y autorización distinto de
suponer que todo acceso al editor concede auditoría técnica profunda.

Esto es una propuesta de explicación y prueba, no un cambio de permisos ni
una aprobación clínica de las relaciones existentes.
