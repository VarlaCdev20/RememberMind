# Reutilización y reducción del sistema experto

Fecha: 20/10/2026. Estado: **análisis de reducción y dirección de diseño;
sin migración ni cambio de estructura aprobado**.

**Primer resultado concreto:** [diccionario de cuatro consolidaciones](PROPUESTA_REDUCCION_CUATRO_TABLAS_2026_10_20.md).
Propone pasar de 108 a 104 sin nuevas tablas en esa transición. El ensayo
reversible de filas está implementado y comprobado; la migración, lectores
sobre esquema reducido y guardado del recálculo íntegro siguen pendientes.

## Decisión vigente de la propietaria

«NO podemos crear tantas tablas, de hecho hay que reducirlas, en base a lo que
hay mejorala». Rechaza la propuesta de 25 relaciones adicionales. En la
aclaración posterior eligió: «Recalcular íntegramente la evaluación histórica;
buscar una estructura menor».

Por tanto, no se implementa el diseño de 133 tablas y no se sustituye el
recálculo íntegro por un informe categórico. El inventario aprobado e instalado
sigue siendo **108 tablas de negocio: 75 operativas y 33 expertas**. Las 25
candidatas nunca se crearon. La preparación compuesta del commit `8695cb64`
sigue siendo en memoria; el guardado histórico completo permanece abierto.

## Fuentes y autoridad

- [Baseline con las extensiones aprobadas](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md).
- [Diccionario operativo](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md).
- [Decisión de conservación del 17/10](../base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md):
  aprobó incorporar nueve tablas y estudiar después una reducción preservando
  los contratos; no autorizó eliminarlas.
- [Revisiones profesionales](../base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md).
- [Originales Petersen](PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md).
- `InventarioExperto`, `ConservarObservaciones`, `AlmacenInstantaneas`,
  `ConsultarObservacionesConservadas`, lectores/escritores de simulación y
  migraciones vigentes: evidencia de consumidores y representación física.

El diseño rechazado se mantiene como antecedente con su estado explícito.
No se altera el baseline para presentar una reducción todavía no ejecutada.

## Mejora implementada: inventario reproducible de lectura

Se completó el comando existente, antes vacío:

```powershell
php artisan app:auditar-base-datos-remember-mind
php artisan app:auditar-base-datos-remember-mind --contar-filas
```

Reutiliza `InventarioExperto`, consulta metadata, enumera FK entrantes y permite
conteos agregados opcionales. No muestra registros, nombres de personas,
respuestas, notas, rutas privadas o credenciales. No contiene DDL ni escrituras.
Una tabla ausente del inventario experto produce salida de fallo; las tablas
ajenas al inventario se mantienen visibles como otras tablas físicas.

Lectura ejecutada sobre la conexión local configurada: PostgreSQL, **33/33
expertas presentes**, 91 tablas físicas adicionales que incluyen las 75
operativas y las 16 técnicas. Las nueve tablas de conservación contienen filas.
Este conteo no clasifica su contenido como clínico o experimental ni permite
retirarlas. Tampoco cero filas o cero FK entrantes demostrarían desuso: hay
consumidores de aplicación y contratos históricos.

## Reutilización antes de nuevas entidades

| Responsabilidad | Estructura existente que debe aprovecharse |
|---|---|
| Cabecera, residente, solicitante, corte e identidad | `evaluaciones_expertas` |
| Dos familias de conocimiento | `versiones_modelo_experto`; estudiar una segunda FK cerrada en la evaluación, sin crear una versión común ficticia |
| Definiciones y relaciones semánticas | Las 13 relaciones de conocimiento D-137, con cierre de versión usado |
| Criterios, resultados y justificación | `evaluacion_criterios`, `resultados_criterio` |
| Reglas y decisiones de condiciones | `trazas_inferencia`, `evaluaciones_reglas`, `evaluaciones_condiciones_regla` |
| Evidencias, independencia y soportes | `evidencias_evaluacion`, `relaciones_evidencias_evaluacion`, `evidencias_criterio_evaluacion`, `evidencias_soporte_condicion` |
| Hechos clínicos originales | `hechos_instantanea_experta`, con orígenes/FK cerrados y representación específica aprobada |
| Aplicaciones y respuestas originales | `aplicaciones_instrumento`, `respuestas_instrumento`, con rectificación existente y cierre SQL de contenido/colecciones |
| IADL y juicios originales | `controles_funcionales_iadl`, `valoraciones_petersen_originales`, `soportes_valoracion_petersen` |
| Revisión de Medicina/Psicología | `revisiones_evaluacion_experta`; no duplicar firmas |
| Release recuperable | ZIP de software existente, identificado por huella en evaluación y ruta privada determinista; no exige por sí solo otra tabla |

La reutilización de tablas experimentales requiere contrato institucional
separado, columnas/checks explícitos y lectores anteriores conservados. Nunca
quitar las guardas de `ORION-SNAP-2` ni convertir una atribución simulada en
criterio clínico. Tampoco utilizar hechos como pares de atributos SQL.

## Reducción por originales protegidos

La propuesta anterior copiaba instrumentos, preguntas, opciones, aplicaciones
y respuestas a otro conjunto de tablas. La alternativa prioriza referencias a
originales realmente conservados:

1. Aplicación y respuesta ya rechazan cambios de contenido en Models y tienen
   corrección por aplicación sucesora. Hay que cerrar también SQL directo y
   evitar respuestas añadidas después del cierre de una colección.
2. IADL posee rectificación y protección Model. Debe reforzarse su garantía SQL
   y conservar la membresía/estado observados al ejecutar, sin escoger cabezas
   actuales durante el replay.
3. La definición de instrumento, pregunta y opción usada debe conservarse por
   edición real. Una corrección debe poder publicar otra edición identificada;
   bloquear cambios sin ofrecer ese recorrido no completa la solución.
4. No congelar personas o residentes. Guardar nacimiento/edad y metadata
   efectivamente consumidos en la ejecución, con su grano propio. Si se exige
   igualdad del nombre mostrado en V3, también debe conservarse su valor
   observado; consultar el nombre actual no lo reconstruye.
5. Fuentes contextuales que sigan mutables requieren conservar el contenido
   clínico utilizado. Una FK o un hash aislados no contienen ese contenido.

El cierre empieza en su fecha real; no acredita que una fila antigua nunca
cambió. Se debe validar y conservar el estado actual de la nueva ejecución,
sin inventar versiones o autorías anteriores.

## Simplificación de perfiles: tres relaciones candidatas

Las cinco responsabilidades de perfiles del antecedente pueden normalizarse
como tres entidades, pendientes de diccionario e implementación:

| Entidad candidata | Grano y límites |
|---|---|
| `perfiles_instrumentales_versionados` | Un perfil exacto CAPTURA o INTERPRETACION. Discriminador cerrado, PK propia, método e instrumento/edición; columnas específicas de cada variante restringidas por CHECK. Las dos variantes son filas independientes, sin producto cartesiano. |
| `entradas_perfil_instrumental` | Una entrada nombrada de un perfil y su pregunta/campo. La variante determina si representa selector o medición requerida; preserva ausente/NULL y rechaza referencias incompatibles. |
| `predicados_perfil_instrumental` | Un predicado ordenado de un perfil de interpretación. Colección separada, sin listas serializadas. |

No mover perfiles a una tabla de hallazgos ni colocar cualquier configuración
en un documento o ZIP. Los contratos semánticos del servidor y los vínculos
declarativos deben contrastarse con las fuentes/mapeos versionados existentes,
sin fabricar variables o mapeos para ocupar una FK.

La revisión independiente considera legítima esta unificación de perfiles y
el release por huella. **No certifica que tres tablas basten para todo el
recálculo.** Faltan resolver en el diccionario completo metadata histórica,
gobierno observado, constancias, exclusiones y cierre de colecciones. No se
presenta otro número como mínimo sin esa prueba.

## Consolidaciones de tablas actuales que deben comprobarse

| Tabla actual candidata | Destino y comprobación necesaria |
|---|---|
| `evidencias_cognitivas_instantanea` | Su FK al hecho es UNIQUE: estudiar integración de columnas cognitivas fijas en el mismo hecho, conservando identidad y referencia histórica. |
| `evidencias_iadl_instantanea` | Contrastar si hecho, evaluación y funcionalidad determinan una sola fila. Integrar como subtipo IADL únicamente si se demuestra esa dependencia en todos los contratos; conservar vocabularios simulados y originales por separado. |
| `funcionalidad_instantanea_experta` | Consolidar con la raíz de clasificación para el contrato actual: el escritor guarda ambos bloques en una transacción y el lector considera funcionalidad sin clasificación un conjunto incompleto. Una fila huérfana bloquea el traslado; no se inventa una etapa nueva ni una clasificación para repararla. |
| `soportes_sinteticos_criterio` | Estudiar una FK directa al hecho en el vínculo de evidencias por criterio, con XOR de origen y rol experimental existente. Preservar orden e identidades de ambos tipos de soporte. |

Son candidatas de transformación, no órdenes de DROP. El mapa debe demostrar
reconstrucción exacta de contratos existentes, filas sin pareja, duplicados,
NULL, FK entrantes, orden, rollback y consumidores. No se suman estas posibles
retiradas al inventario ni se promete que las cuatro puedan ejecutarse.

`reglas_aplicadas_dcl_instantanea` y `condiciones_regla_instantanea` conservan
AST con OR/NOT y reglas SIM sin equivalente directo en el AND de D-130. No
se retiran para cuadrar un contador ni se pierden esas expresiones.

## Condiciones del recálculo íntegro

El contrato reducido debe recuperar entradas brutas, métodos, perfiles,
selectores, edad/contexto, membresías exactas, las dos familias y los estados
de gobierno observados. Se ejecutan otra vez normalizaciones, red y Petersen;
se comparan medidas, métricas, cinco dominios, requisitos, patrón/abstención,
motivos y explicación con la ejecución original. La igualdad del DTO V3
literal también exige conservar su metadata y orden; no se omiten campos bajo
la palabra reducción.

Ausencia o corrupción del release produce error explícito. El hash no prueba
existencia o respaldo: la retención y restauración reales deben verificarse.
No se recalcula con el checkout actual para simular recuperación histórica.

## Estado y siguiente decisión estructural

Implementado ahora: auditor de lectura reutilizando código existente,
ensayo reversible de las cuatro consolidaciones, verificación local y
diccionario concreto de transición/reversión. No hay
guardado nuevo, consolidación física, migración o borrado de datos.

El [diccionario de cuatro consolidaciones](PROPUESTA_REDUCCION_CUATRO_TABLAS_2026_10_20.md)
ya fija columnas, constraints, consumidores y mapa reversible para la primera
reducción. Queda aprobar esa transición e implementarla con comprobación sobre
esquema nuevo. El diccionario del guardado de recálculo íntegro todavía debe
cerrarse sin trasladar el diseño rechazado. La instrucción de reducir fija la
dirección; no autoriza pérdida de historia.
Mientras tanto, se mantienen las 108 tablas y sus consumidores operativos.

## Evidencia de este incremento

La selección final, incluido el ensayo reversible y negativos, obtuvo
**22 aprobadas / 783 aserciones en SQLite**, con un caso de esquemas PostgreSQL
omitido; **23 / 780, PASS en PostgreSQL desechable**. Comandos, límites y gate
independiente en el diccionario de cuatro consolidaciones. Los resultados
siguientes son evidencia parcial anterior, no totales acumulables.

`InventarioReutilizacionExpertaTest|InventarioExpertoTest`: SQLite **6 pruebas /
595 aserciones, PASS**; PostgreSQL desechable **6 / 589, PASS**. Incluye
integración real de FK, separación de conteos opcionales y ausencia de
escrituras/lecturas de payloads en las consultas del inventario. El número de
consultas de metadata difiere por motor; no son pruebas de concurrencia ni de
recálculo histórico completo.

Build y QA visual no aplican: no se modifica frontend. No se alteran permisos,
historia, guardas clínicas ni estructura; no se realiza push.
