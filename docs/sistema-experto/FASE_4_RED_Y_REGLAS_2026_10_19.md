---
title: "Fase 4 — Red cognitiva y reglas"
status: CURRENT
last_reviewed: 2026-10-19
source_of_truth: false
verification_scope: SOFTWARE_INTEGRATION
---

# Fase 4: integración técnica de red y reglas

**Ampliación vigente posterior:** [contrato institucional A–D adoptado e integrado](FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md). Añade el paquete institucional, el registro autorizado, las observaciones de los 19 componentes y los predicados de G09 con perfiles/medidas originales. El incremento técnico descrito a continuación conserva su alcance histórico; el acta nueva distingue pruebas, instalación y QA visual.

**Incremento posterior del mismo día:** [compatibilidad nominal, sucesión y replay](FASE_4_PUENTE_NOMINAL_Y_SUCESION_2026_10_19.md). La aclaración y conciliación de fase 2 identifican observaciones clínicas por componente. El puente ya conserva sus parejas opción/texto concordantes y traduce las PK de vínculos al heredar contratos; esto no activa un paquete institucional ni interpreta esas observaciones como mediciones instrumentales.

Esta entrega integra el conocimiento existente. No define nuevos umbrales, pesos ni equivalencias entre puntuaciones instrumentales, hallazgos descriptivos y estados Petersen. La carga y el cotejo de documentos originales de la fase 2 permanecen separados de esta verificación de software.

## Checklist

- [x] Validar los cinco dominios y sus 19 componentes (MEM 4, ATE 3, EJE 5, LEN 5, VIS 2).
- [x] Mantener EJE-S03 en Función ejecutiva.
- [x] Verificar propietario primario, pertenencia explícita y participación inferencial de las variables usadas por una regla.
- [x] Integrar D-131/Memoria y las 15 reglas G09 en una revisión única del conocimiento del laboratorio integral.
- [x] Añadir una entrada declarativa independiente del laboratorio para hechos originales remapeados y paquetes completos.
- [x] Conservar versión, SHA-256 y origen de las filas de cada familia integrada.
- [x] Exigir una conjunción completa por componente y aplicación; impedir combinar condiciones parciales de aplicaciones diferentes.
- [x] Permitir conjunciones entre componentes expresamente declarados, conservando el requisito anterior en cada componente.
- [x] Preservar NULL y distinguirlo de false y de una fuente ausente en la proyección final.
- [x] Mantener las contradicciones en la traza y suspender las consecuencias que el contrato no permite resolver.
- [x] Deduplicar por registro original; unir adicionalmente soportes con unidad/episodio explícitos, sin dividir un mismo original.
- [x] Extender el gestor institucional existente a variables de subcomponentes; validar el contrato del servidor antes de publicar una definición nueva.
- [x] Excluir reglas rechazadas también en el mapper ORION.
- [x] Archivar mediante sucesora, conservando las definiciones y los resultados originales.
- [x] Reproducir la ejecución técnica anterior desde su instantánea de entrada después de archivar reglas de Memoria y G09.
- [x] Mantener la ausencia de conservación institucional del perfil integral indicada por `persistido=false`.

## Componentes y recorridos

| Responsabilidad | Implementación |
|---|---|
| Pertenencia estructural, sin transitividad inferida | `motor-experto/src/motor_experto/dominio/conocimiento/PertenenciaVariableCognitiva.php` |
| Compilación de dominios, componentes y rutas de reglas | `motor-experto/src/motor_experto/dominio/conocimiento/CompiladorPerfilCognitivo.php` |
| Alcance de la conjunción por aplicación/componente | `motor-experto/src/motor_experto/dominio/conocimiento/reglas/EvaluadorReglasCognitivas.php` |
| Integración explícita de las familias de conocimiento | `motor-experto/src/motor_experto/investigacion/dcl/PaqueteCincoDominiosExperimental.php` |
| Entrada integral y reproducción | `motor-experto/src/motor_experto/aplicacion/casos_uso/EjecutarPerfilExperimental.php` |
| Entrada declarativa desde fuentes originales, sin generadores | `motor-experto/src/motor_experto/aplicacion/casos_uso/EvaluarConocimientoCognitivo.php` |
| Gobierno, autoría, revisión, sucesión y archivo | `app/Modulos/ApoyoDecision/Aplicacion/Acciones/GestionarReglasExpertas.php` |

Los hechos conservan su fuente y sus mapeos. La red verifica la participación de la variable en el criterio; la pertenencia de un componente por sí sola no activa una regla. El evaluador D-130 conserva su contrato general de AND positivo. El evaluador cognitivo añade el alcance por aplicación, tanto en el ejecutor declarativo G09 como en su verificador y en Memoria.

Una regla multicomponente puede reunir tareas distintas de su mismo dominio. No puede completar las condiciones de una sola tarea combinando dos aplicaciones parciales. La traza conserva las evaluaciones por unidad y los soportes originales; varios predicados verdaderos de una aplicación no se convierten en varios episodios independientes.

`EvaluarConocimientoCognitivo` recibe un paquete completo y una memoria ya extraída. Recalcula cada evidencia desde su fuente y mapeo, rechazando una modificación del valor semántico. Ejecuta el mismo evaluador de conjunciones y devuelve las consecuencias declarativas con reglas, evidencias, fuentes y soportes efectivos. Una contradicción de valores de la misma variable/soporte suspende las conclusiones de ese dominio y conserva las trazas. No recibe ni fabrica una clasificación DCL. La autorización y la extracción institucional corresponden a la integración Laravel; este incremento no añade una ruta capaz de recibir hechos del cliente ni cambia la pantalla.

Esta entrada declarativa no sustituye las compuertas de interpretabilidad ni el resolvedor D-131. El puente con `CapturasInstrumentalesResidente` debe construir la memoria mediante mapeos explícitos; una etiqueta instrumental o un z no se transforma automáticamente en un predicado G09.

El perfil integral de referencia contiene dos reglas D-131 de Memoria y las quince G09 existentes. Los cuatro conceptos MEM no se presentan como cuatro reglas instrumentales implementadas. Sus contratos específicos de tarea siguen vinculados a las fuentes instrumentales de las otras fases.

## Versiones y procedencia

Al integrar las familias se genera una identidad técnica determinista `SIM-5-*` y se conservan sus versiones fuente y hashes. Los cinco dominios ejecutan esa misma identidad; Petersen conserva su propio contrato y sus referencias independientes.

Si se entrega un paquete completo ya integrado o una sucesora suya, el origen conocido es ese paquete completo (`PERFIL`, con hash e identidades de sus filas). No se reconstruyen versiones ancestrales ausentes. Las referencias independientes deben identificar esa revisión explícita: se rechazan referencias de familias anteriores cuyo manifiesto no acompaña al paquete. No se cambia automáticamente la versión de una referencia para hacerla aceptar.

## Gobierno y archivo

Medicina y Psicología conservan la gestión y revisión existentes; Superadministración conserva consulta. Las operaciones vuelven a comprobar cuenta, permiso y Policy en el backend. Autor y revisor proceden de la sesión y la decisión original no se sobrescribe.

Para publicar una definición no observacional nueva, `sistema_experto.contratos_conocimiento` debe registrar por PK de versión el contrato exacto de relaciones/modalidades, extracción y tipo de regla. Las sucesoras lo heredan por linaje. El registro es configuración del servidor, no entrada del formulario. Un contrato ausente, un tipo incompatible o una relación decorativa impiden publicar y retirar la antecesora. Archivar reglas existentes conserva su camino administrativo sin exigir inventar un contrato.

El registro operativo de contratos sigue vacío: esta entrega no atribuye a versiones reales los predicados `TEST_APORTA` o `SIM_APORTA` de las fixtures. Su configuración requiere identificar el paquete real y sus vínculos originales; no requiere una aprobación profesional para continuar programando.

Un retiro/rechazo de Memoria puede dejar sin regla con soporte: el motor devuelve abstención explícita y ningún resultado. El error D-131 de cobertura sin retiro/rechazo permanece detectable. Los resultados históricos no se recalculan con las reglas vigentes: las definiciones anteriores siguen consultables y la entrada de laboratorio reproduce su revisión original.

## Verificación

Regresiones principales:

- `IntegracionRedCognitivaTest`: topología, revisión única, procedencia, archivo de ambas familias, reproducción, separación de referencias, NULL/false/ausencia y agrupación de soportes.
- `PerfilCognitivoDeclarativoTest`: entrada independiente de generadores, fuentes originales mapeadas, contradicciones del mismo soporte, NULL y manipulación de evidencias.
- `ReglasDeclarativasPerfilExperimentalTest`: aplicaciones parciales, conjunciones multicomponente y revisión declarativa.
- `GobiernoReglasExpertasTest`: publicación con subcomponente válido; rechazo y rollback de vínculos ausentes/decorativos y contratos desconocidos; autoría y permisos existentes.
- `MapeadorReglasTest`: una regla rechazada conserva su definición y se mapea deshabilitada.
- Suites relacionadas de conservación, sucesión, concurrencia PostgreSQL y presentación experimental.

Las pruebas usan datos sintéticos y motores de base de datos desechables. No importan registros clínicos, modifican historia real ni ejecutan migraciones en la base operativa.

Verificación del candidato aislado desde `3d7191f2`, con los archivos de esta fase y dependencias locales, sin `.env` ni cambios simultáneos de interfaz:

| Comprobación | Resultado |
|---|---|
| PHPUnit SQLite, selección de motor, repositorios, gobierno, conservación, sucesión y presentación experimental | 1166 casos / 10086 aserciones; sin fallos ni errores. Cinco carreras PostgreSQL se omiten en SQLite |
| PostgreSQL desechable: gobierno, cinco carreras, sucesión, conservación, repositorios, consola y panel | 68 pruebas / 347 aserciones; sin omisiones, fallos o errores; clúster detenido y eliminado por el script guardado |
| Entrada declarativa añadida después de esa suite; mismo candidato, sin modificar los archivos anteriores | 4 pruebas / 15 aserciones, PASS |
| Independencia del motor y ORION | Ambos ejecutables PASS sin Laravel ni SQL |
| `npm run build` del candidato | PASS, 3,28 s |
| Revisiones independientes de conocimiento/gobierno y de la entrada declarativa | PASS técnico por lectura, hallazgos corregidos y cierre estático sin P1/P2 pendientes |

La primera corrida del candidato carecía de `public/build/manifest.json`: falló únicamente la prueba de ruta. Se generó el build y se repitieron ambas selecciones; los resultados de la tabla corresponden a esa repetición. El navegador y la apariencia no se presentan como verificados: esta entrega no modifica UI.

## Límites y siguientes fases

**Incremento posterior:** [fuentes profesionales, métricas 60+ y puente autorizado de capturas](FASE_4_FUENTES_Y_METRICAS_60_MAS_2026_10_19.md). `ConsultarPerfilInstrumental` ya conecta la entrada declarativa con fuentes SQL autorizadas, usando el contrato del servidor y mapeos exactos de pregunta. La vinculación de los IDs originales de la versión real continúa separada de la implementación del puente. La conservación institucional completa permanece en fase 6.

La verificación técnica no convierte los paquetes de laboratorio en conocimiento clínico. La interpretación de datos de residentes requiere los instrumentos/ediciones/preguntas/mediciones originales y sus mapeos identificados. Se conserva esa falta de información; no se rellenan mediciones para alcanzar una conclusión esperada.

La conservación institucional completa del perfil integral pertenece al cierre de persistencia. Esta fase no añade tablas, columnas, permisos ni catálogos a la base congelada; no altera la 4FN. La documentación final de operación se consolidará después de completar las fases, conforme a lo acordado.
