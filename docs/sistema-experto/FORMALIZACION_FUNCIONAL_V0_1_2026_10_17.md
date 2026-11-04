# Formalización incremental del sistema experto funcional

> Registro histórico del primer corte de lectura. La situación de conservación pendiente descrita abajo fue sustituida el 18/10/2026 por la [decisión aprobada de dos FK](PROPUESTA_CONSERVACION_OBSERVACIONAL_V1.md), ya implementada y aplicada en desarrollo sin tablas nuevas ni borrado. La [memoria de la nueva Fase 01](PROMPT_MAESTRO_01_CHECKLIST_2026_10_18.md) distingue esta capacidad previa del alcance integral todavía pendiente; este registro inicial no invalida la aprobación posterior.

Fuente: documento «Formalización del sistema experto funcional — Propuesta de alcance incremental v0.1» enviado por la propietaria el 17/10/2026. Las tres reglas literales solicitadas constituyen el alcance implementable sin inventar interpretación clínica. No se aprueban automáticamente ventanas, cantidades, permisos sensibles ni ampliaciones de BDD.

## SOURCE OF TRUTH

- Instrucción actual: tres observaciones booleanas; ausencia no equivale a resultado negativo ni a normalidad.
- Arquitectura híbrida vigente: motor independiente en `motor-experto`; integración en `ApoyoDecision`. Se conserva el evaluador D-130 existente; su búsqueda existencial de soportes positivos no representa por sí sola los cuatro estados solicitados. La capacidad observacional acotada distingue explícitamente `false` de `null` sin alterar el motor clínico.
- Baseline BDD y extensiones aprobadas: 73 tablas operativas y 33 expertas. Se reutilizan `controles_cognitivos` y sus relaciones; ninguna migración ni catálogo nuevo en esta entrega.
- La revisión profesional vigente es posterior a la presentación. No representa una valoración independiente previa y no debe reutilizarse para calcular concordancia.
- Los documentos anteriores que restringían toda salida a la clasificación cognitiva no sustituyen esta petición de hallazgos descriptivos. Permanecen las restricciones de Petersen, instrumentos y aprobación clínica.

## DOMAIN DESIGN: primer corte

Actor: quien ya puede consultar resultados de ese residente, con cuenta activa, permiso explícito, Policy y asignación aplicable. No se amplían roles ni facultades de escritura.

Entrada: controles del mismo residente y anteriores al corte de consulta. Se validan estado, fecha, autor, contexto de atención y tipos originales. La baja actual del autor no invalida una autoría histórica identificable. Los lotes marcados de simulación permanecen segregados por el filtro operativo existente; no se convierten en registros ordinarios.

Capacidad pura: `EvaluadorObservaciones`, versión `OBS-20261017-v1`, tres reglas declarativas inmutables. Cada registro genera tres decisiones: `CUMPLE`, `NO_CUMPLE` o `NO_EVALUABLE`. Una entrada técnica incoherente se rechaza con excepción y la capa de presentación reporta el error; nunca se traduce un error a «no cumplió». No hay diagnóstico, puntuación ni recuento de recurrencia. Fuentes duplicadas idénticas se deduplican; declaraciones incompatibles de una misma fuente se rechazan.

Consulta: `ConsultarHallazgosObservacionales` autoriza antes de leer. Obtiene diez controles por página con total explícito, sin una supuesta ventana clínica. Conserva la instantánea escalar en el DTO de la consulta y devuelve fuente, autor, fecha, episodio y cada condición. La consulta no escribe ni finge una evaluación conservada.

Interfaz: sección «Hallazgos observacionales del seguimiento» en la ruta actual de resultados. Se distingue el cálculo actual de la evaluación histórica. Todos los detalles son desplegables y los valores nulos permanecen como no registrados/no interpretables.

Transacción/eventos: no aplican a este corte de solo lectura. No se activa ningún listener clínico, alerta, firma o actualización de versiones. Error SQL o de contrato utiliza el manejo seguro existente.

## Conservación: diseño concreto pendiente de decisión

La Entrega 1 del documento **no está completa** hasta conservar y recuperar la traza y la instantánea íntegra. El DTO no sustituye la persistencia. No se guardarán datos clínicos en Activitylog, JSON, archivos temporales ni campos de texto como solución alternativa. [Diccionario mínimo propuesto](PROPUESTA_CONSERVACION_OBSERVACIONAL_V1.md): cero tablas nuevas, dos FK de origen/autor y ajustes de checks. La respuesta «si no nos sirve o es pasado elimínala» no se interpreta como aprobación inequívoca de esas modificaciones ni como autorización de borrar historia; se solicitó aclaración.

La propietaria sí definió expresamente quién podrá guardar ejecuciones: **solo Medicina y Psicología**. Enfermería conserva consulta dentro de su alcance. Esta decisión no otorga a Superadministración escritura clínica automática ni convierte una firma posterior en valoración independiente.

La prioridad es reutilizar estas tablas, sin crear una tabla de ejecución paralela:

| Necesidad | Reutilización propuesta | Pendiente de formalización/aprobación |
|---|---|---|
| Paquete y reglas | `versiones_modelo_experto`, `reglas_expertas`, condiciones y consecuencias | Catálogo observacional propio y su estado de autorización; no activar un contrato clínico cognitivo |
| Fuente y valor | `fuentes_datos_expertas`, `mapeos_variables_fuente`, valores semánticos y `evidencias_evaluacion` | Representar verdadero, falso y desconocido sin inventar valores |
| Ejecución y condiciones | `evaluaciones_expertas`, `evaluacion_criterios`, `trazas_inferencia`, `evaluaciones_reglas`, `evaluaciones_condiciones_regla`, `evidencias_soporte_condicion` | Estados observacionales y operación autorizada; no disfrazar una ejecución normal como PRUEBA_TECNICA |
| Instantánea exacta | `hechos_instantanea_experta` y evidencia original | El contrato D137 actual exige atención o actividad; `controles_cognitivos.cod_atencion` es nullable. Además la fecha y el autor originales deben conservarse, no releerse como si fueran históricos. Diseñar el mapping y aprobar solo el ajuste indispensable si no es representable |

El registro de auditoría de negocio seguirá usando Spatie Activitylog con actor, versión y referencia, sin duplicar la historia ni almacenar un payload clínico completo.

## Entregas siguientes y decisiones abiertas

1. **Recurrencia:** no se elige ventana ni mínimo por conveniencia técnica. Deben definirse por regla, versionarse y deduplicar episodios, sin contar dos registros de la misma atención como independientes. Signos vitales usa objetivos individuales vigentes en la fecha de la medición; jamás implica deterioro cognitivo automáticamente.
2. **Gestión de reglas:** el catálogo actual es de consulta y las guardas de Models impiden sobrescritura. Borrador/prueba/piloto/revisión/suspensión/archivo y sus autorizadores requieren flujo explícito. Se preservan reglas y evaluaciones anteriores; no se eliminan ni activan desde un rol genérico.
3. **Concordancia:** diseñar una valoración profesional independiente previa, comparación por hallazgo equivalente y causa/decisión de discrepancia. Las firmas actuales de confirmación/rechazo no reemplazan ese registro. Se necesita diccionario concreto antes de cualquier modificación de BDD. No hay métricas de validez fabricadas ni reuso de los mismos casos para modificar y validar reglas sin distinguirlos.
4. **Petersen:** permanece en el ámbito académico/sintético autorizado; las tres reglas observacionales no acreditan dominios A/P, autonomía ni C01–C05.

## Evidencia

Prueba inicial: 19 pruebas y 55 aserciones aprobadas en SQLite. La comprobación local con transacción PostgreSQL READ ONLY detectó una colisión entre la columna clínica `atencion` y la relación Eloquent del mismo nombre; se corrigió accediendo explícitamente a la relación cargada. Se añadieron regresiones de atención propia y rechazo físico de atención de otro residente. Cierre dirigido posterior: 21 pruebas y 58 aserciones aprobadas en SQLite.

Comprobación local posterior de solo lectura: tres expedientes ficticios renderizan la sección, diez controles por página y treinta decisiones por página, con total histórico explícito. No se escribe una evaluación ni se modifican controles. Los resultados negativos no se interpretan como ausencia de enfermedad. Build aprobado en 3,98 s; independencia del motor aprobada (cinco casos técnicos sin Laravel/Eloquent).

La regresión conjunta y PostgreSQL se registrarán al finalizar su ejecución. No se declara completa la conservación, administración de reglas, comparación independiente ni ampliación cognitiva por disponer de clases o pruebas de consulta. QA visual en navegador no ejecutado; el renderizado y build no lo sustituyen.
