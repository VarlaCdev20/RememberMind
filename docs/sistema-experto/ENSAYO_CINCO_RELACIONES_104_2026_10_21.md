# O.R.I.O.N. — ensayo de cinco relaciones bajo 104 tablas

Fecha: 21/10/2026. Estado: **DISEÑO CANDIDATO / NO APROBADO / NO INSTALADO**.
No reemplaza la [decisión vigente de máximo 104](DECISION_MAXIMO_104_TABLAS_2026_10_20.md) ni las decisiones del baseline. La aprobación del máximo no autoriza fusiones, columnas, FK, checks o triggers concretos.

## Contrato que debe cumplir

`EVALUACION-INSTITUCIONAL-1` debe volver de SQL con las mismas dos ramas, conocimiento y reglas observados, fuentes y ausencias, preguntas sin respuesta, orden de cada colección, autor, corte y release ejecutable. Una huella, una FK a originales mutables o una salida recalculada con la configuración actual no sustituyen esas entradas. La revisión de Medicina y Psicología ocurre después de una emisión completa y conservada.

La estructura instalada contiene **75 operativas + 29 expertas = 104** tablas de negocio. El cotejo exploratorio del candidato anterior de cuatro tablas nuevas identificó B01–B03. Sus mapas de 51 colecciones y 783 rutas permanecen en preparación fuera de esta entrega y no constituyen evidencia reproducible de ida y vuelta.

## Presupuesto provisional de relaciones

| Cinco consolidaciones candidatas, todas sin autorización | Relaciones liberadas |
| --- | ---: |
| `resultados_criterio` y `trazas_inferencia` como bloques opcionales distintos de `evaluacion_criterios` | 2 |
| `evidencias_soporte_condicion` como variante de fila independiente de `evidencias_criterio_evaluacion` | 1 |
| `clasificaciones_dcl_instantanea` como bloque opcional de `evaluaciones_expertas` | 1 |
| `variables_expertas` como bloque opcional de su `nodos_semanticos` | 1 |

| Cinco relaciones nuevas candidatas | Grano principal |
| --- | --- |
| `perfiles_instrumentales_versionados` | Perfil adquirido de captura o interpretación |
| `entradas_perfil_instrumental` | Selector, medición requerida o pregunta declarativa original adquirida, en variantes cerradas |
| `predicados_perfil_instrumental` | Predicado interpretativo ordenado |
| `vinculos_declarativos_instantanea` | Vínculo efectivamente adquirido por conocimiento y mapeo |
| `contratos_relaciones_instantanea` | Contrato de relación o una de sus modalidades, cada cual como fila independiente |

**Cuenta:** 104 − 5 + 5 = 104. La cuenta no demuestra 4FN, conservación completa ni viabilidad de las consolidaciones. Una unión de subtipos cerrados solo puede considerarse si cada fila conserva un único hecho, tiene checks excluyentes, FK originales, orden, cierre e inmutabilidad y reconstruye sin ambigüedad el DTO. La mantenibilidad y 4FN necesitan revisión independiente antes de pedir aprobación de DDL.

## Resolución propuesta para las tres brechas

**B01 — contratos y modalidades de ambas ramas.** `contratos_relaciones_instantanea` identifica evaluación y rama. La fila `CONTRATO` conserva orden, tipo de relación, origen, destino y diferencia entre clave `habilita_participacion` ausente y `false`. Cada fila `MODALIDAD` refiere al contrato de la misma evaluación/rama, conserva su orden y distingue modalidad NULL de texto. Cero hijas representa lista vacía; no se repite el contrato por modalidad. Un cierre impide inserciones tardías y las filas emitidas son inmutables. El [ensayo SQLite](../../prototipos/orion-consolidacion-104/ensayar-contratos-relaciones-sqlite.php) pasó con dos ramas, tres contratos, tres modalidades, NULL, lista vacía, ausencia/false, orden, FK, checks y rollback.

**B02 — preguntas declarativas sin respuesta.** Una variante `PREGUNTA_DECLARATIVA` de `entradas_perfil_instrumental` pertenece a la aplicación adquirida, aunque no exista perfil ni respuesta. Conserva por columnas nominales `cod_pregunta`, instrumento, código, enunciado, dominio nullable, tipo, puntaje máximo nullable, orden, estado y orden de la colección. Las variantes SELECTOR/MEDIDA tienen propietario perfil y campos distintos, con checks excluyentes. El [ensayo SQLite](../../prototipos/orion-consolidacion-104/ensayar-preguntas-declarativas-sqlite.php) reconstruyó dos preguntas sin respuestas, conservó NULL y escala decimal, y mantuvo la copia al cambiar después el catálogo original; probó rollback y cierre. Falta cotejar que las dos rutas del DTO —captura instrumental y soporte Petersen— referencien la misma aplicación adquirida sin perder identidad ni duplicar un original.

**B03 — membresías de soportes Petersen.** Una tercera variante `MEMBRESIA_PETERSEN` de `evidencias_criterio_evaluacion` representa cada vínculo entre juicio adquirido y soporte original, con orden y FK compuestas a la misma valoración/evaluación. El juicio adquirido permanece como padre aun con cero soportes. Las otras variantes de participación de criterio y soporte de condición conservan sus granos propios; no se convierten en una lista dentro de una celda. El [ensayo SQLite](../../prototipos/orion-consolidacion-104/ensayar-membresias-petersen-sqlite.php) pasó para dos juicios, uno sin soportes, orden, rechazo de soporte ajeno, duplicado, rollback y cierre. Debe integrarse con el [ensayo de unión previo](../../prototipos/orion-consolidacion-104/ensayar-vinculos-sqlite.php), sus columnas reales y todos sus consumidores antes de considerarlo apto.

Un [ensayo conjunto adicional](../../prototipos/orion-consolidacion-104/ensayar-dto-compuesto-sqlite.php) usa la instantánea compuesta sintética del test unitario vigente: recuperó exactamente tres contratos instrumentales, uno Petersen y nueve membresías de juicios desde filas tipadas del candidato; rechazó un vínculo entre ramas, una valoración ajena, nuevas filas, modificaciones, borrados y reapertura después del cierre. La muestra del test tiene cero preguntas declarativas originales, por lo que **no amplía** la prueba B02 de preguntas no respondidas ni demuestra el DTO completo.

## Evidencia y límites de este corte

Los siete scripts SQLite aislados —criterio, vínculos, clasificación, variable/nodo y B01–B03— y el ensayo conjunto parcial verifican subproblemas con datos sintéticos. El cotejo de solo lectura sobre la instalación local reconstruyó 205 nodos y 87 variables y el cotejo anterior, 225 participaciones y 150 soportes de condición. **No existe todavía un escritor/lector SQL integrado de todas las colecciones del DTO ni una igualdad canónica `guardar → recuperar → replay` bajo este diseño de cinco relaciones.** Tampoco existe prueba PostgreSQL de las fusiones, transición poblada de todas las FK, concurrencia de sellado, reversión operativa ni revisión independiente de 4FN y mantenibilidad.

Los originales operativos pueden cambiar de estado o vincularse después a un documento; sus colecciones pueden recibir filas tardías. Una evaluación cerrada debe copiar el valor observado al adquirir, sellar definiciones y membresías, y no afirmar que puede reconstruir un estado anterior que nunca se registró. Retener el release por huella requiere respaldo, restauración y verificación, no solo una ruta privada. Estos requisitos se detallan en el [ensayo reducido anterior](ENSAYO_CONSERVACION_REDUCIDA_FASE_6_2026_10_20.md).

**Siguiente gate antes de solicitar DDL:** completar el diccionario nominal de cinco relaciones y todas las columnas añadidas a tablas existentes; publicar el mapa de colecciones y rutas con su cardinalidad y orden; implementar el escritor, lector y replay en SQLite/PostgreSQL desechables; probar sellado, FK cruzadas, rollback, concurrencia y reversión; someter el resultado a auditoría 4FN/seguridad. Si una unión no supera ese gate, no se instala ni se rebaja la integridad de la historia para conservar el número 104.

La configuración actual de perfiles instrumentales e interpretativos permanece vacía. Estos ensayos de persistencia no generan mediciones clínicas, baremos, firmas ni permisos de instrumentos protegidos. La consulta existente debe continuar mostrando observaciones reales y abstenciones mientras no haya fuentes objetivas admisibles.
