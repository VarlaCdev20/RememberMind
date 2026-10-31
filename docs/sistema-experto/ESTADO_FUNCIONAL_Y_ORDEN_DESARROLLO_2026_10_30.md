---
title: "Estado funcional del experto y orden de desarrollo"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-30
owner: ApoyoDecision
source_of_truth: true
source_of_truth_scope: [development_sequence_without_external_signoff]
verification_scope: READ_ONLY_CATALOG_CODE_AUDIT_AND_TARGETED_TECHNICAL_TESTS
runtime_verified: true
---

# Decisión de la propietaria

Durante todo el desarrollo se completará primero el funcionamiento integral mediante ingeniería y pruebas técnicas. **No solicitar estudios con residentes, firmas, referencias humanas ni avales externos como condición para programar, probar, corregir o integrar.** La evaluación posterior en uso real corresponde a una etapa posterior al funcionamiento y no bloquea esta entrega técnica.

Esta instrucción sustituye cualquier formulación anterior que colocara esa evaluación como un requisito previo al desarrollo. No cambia los datos originales que requiere una regla, la autorización contextual, las relaciones aprobadas o la necesidad de explicar datos insuficientes. Tampoco convierte un test aprobado en evidencia de desempeño real o autoriza a fabricar resultados.

# Qué tenemos

| Componente | Estado real |
|---|---|
| Captura original | Formularios de entrevista/contexto, aplicaciones instrumentales y funcionamiento cotidiano, con autoría y consulta autorizada. |
| Base de conocimiento | Catálogo versionado; inventario de solo lectura del 29/10: 205 nodos, 130 relaciones, 6 versiones y 32 reglas. |
| Reglas | 17 cognitivas institucionales, 4 Petersen, 3 observacionales y 8 de simulación histórica. Estas últimas están excluidas del circuito operativo; no se presentan las 32 como reglas operativas del DCL. |
| Motor de inferencia | Existe y ejecuta condiciones y consecuencias, conocimiento versionado y explicaciones. No hay que construirlo de nuevo. |
| Cinco dominios y Petersen | Lógica implementada para cinco dominios/19 componentes, derivación de C02, cuatro perfiles orientativos y abstenciones justificadas. |
| Revisión de reglas | `PENDIENTE` no impide por sí solo la ejecución; las reglas inactivas o `RECHAZADA` se excluyen. No hay que aprobar artificialmente filas pendientes para desarrollar. |
| Contratos | Aunque la configuración adicional está vacía, existen contratos institucionales incorporados desde código. La versión puede resolverse por familia vigente; un valor de configuración vacío no prueba ausencia del motor. |
| Circuito instrumental inicial | MoCA/RUDAS/FAQ todavía no tienen perfiles instrumentales e interpretativos efectivos ni vínculos al conocimiento. El inventario local registra 0 perfiles de ambos tipos. |
| Conservación observacional | Guardado y recuperación operativos. Esta rama no clasifica DCL por sí sola. |
| Conservación instrumental/Petersen conjunta | Falta guardado y recuperación institucionales completos. El contrato vigente conserva `persistido=false` y `release_retenido=false`. |

**Conclusión funcional:** el motor y sus reglas funcionan técnicamente; el circuito integral de adquisición instrumental → hechos → inferencia → guardado → recuperación todavía no está terminado. La causa principal es integración y conservación pendientes, no falta de un aval externo.

# Qué falta y en qué orden

Actualización acotada del 31/10: [entregable de fuentes médicas y guía integrada de captura](ENTREGABLE_INTEGRACION_Y_FUENTES_DCL_2026_10_31.md). El contrato incorporado sí admite memoria vacía y abstención; la falta de vínculos impide interpretar instrumentos específicos, no ejecutar ese motor. La conservación conjunta sigue pendiente.

1. **Cerrar el contrato técnico de los instrumentos iniciales:** edición/forma/idioma exactos, material y protocolo documentados, respuestas, ausencias, unidades y transformaciones. Conservar el alcance de uso del material sin reproducir contenido de terceros fuera de lo permitido; no exigir una firma o estudio externo para avanzar técnicamente.
2. **Implementar perfiles y vínculos exactos:** conectar PK/versiones/preguntas reales con entradas de métodos registrados y componentes del conocimiento. No repartir un total de cribado entre dominios ni identificar una edición por semejanza de nombre.
3. **Probar la cadena completa de adquisición e inferencia:** originales autorizados y fecha de corte → mediciones → hechos → condiciones/reglas → resultado o abstención → explicación. Cubrir faltantes, contradicciones, formatos incorrectos y fuentes ajenas. La falta de datos de un caso produce una limitación explícita, no una prohibición de desarrollar.
4. **Completar la conservación conjunta:** guardar y recuperar hechos, conocimiento/método/versiones, resultado, explicación y procedencia, con el modelo aprobado y sin nuevas tablas. Un resultado transitorio no debe aparentar haberse guardado.
5. **Cerrar navegación e historial:** resultado conservado accesible por rol, revisión posterior enlazada al resultado correcto y comportamiento responsive; mantener autorización en backend.
6. **Aceptar el funcionamiento integral con pruebas técnicas:** casos controlados, determinismo, integridad, permisos, errores, rollback y recuperación histórica. La evaluación en uso real se hará después; no se solicitará durante estos pasos.

# Cambios de esta entrega

- Instrucción incorporada a AGENTS y a las guías de ingeniería/revisión del experto, preservando los cambios locales anteriores.
- Terminología anterior retirada de los textos del repositorio y mensajes de interfaz; los informes históricos conservan sus resultados, fechas y limitaciones sin afirmar evaluaciones no realizadas.
- Eliminado el requisito de aceptación posterior de la lista de faltantes del perfil de preparación. Esta lista es informativa: el cambio no activa instrumentos ni modifica reglas.
- Conservados controles de originales, permisos, coherencia entre residente/fuente, reglas aprobadas, abstención por insuficiencia e historia clínica. No se borraron archivos ni registros de residentes.
- No se envió correo, se pidieron firmas, se contactó a terceros o se modificó la estructura de BDD.

# Evidencia y ubicaciones

Inventario local: `output/estado-experto-20261029.json`, transacción de solo lectura, sin consultar historias. Revisión independiente de código: distingue correctamente reglas institucionales, simulación excluida, fallback de contratos y conservación pendiente.

- Motor institucional: `motor-experto/src/motor_experto/aplicacion/casos_uso/EvaluarPerfilInstitucional.php`.
- Evaluador de reglas: `motor-experto/src/motor_experto/dominio/conocimiento/reglas/EvaluadorReglas.php`.
- Petersen: `motor-experto/src/motor_experto/dominio/petersen/EvaluarPetersenInstitucional.php` y `PaquetePetersenInstitucional`.
- Contratos incorporados: `app/Modulos/ApoyoDecision/Aplicacion/Servicios/ContratosConocimiento.php`.
- Vínculos pendientes: `ConsultarConocimientoCognitivo` y `CapturasInstrumentalesResidente` en ApoyoDecision.
- Persistencia conjunta pendiente: `InstantaneaEvaluacionInstitucional`.

Verificación del cierre:

- SQLite: 120 pruebas correctas/1017 aserciones en Petersen, perfil institucional adoptado, inferencia declarativa, preparación y recorridos institucionales.
- Comprobación focalizada adicional: 8 pruebas/54 aserciones, incluyendo los cuatro patrones, ausencia, contradicción, recorrido de cinco dominios y falta de vínculos originales.
- Regresión del requisito eliminado: 4 pruebas/33 aserciones correctas; la preparación no exige firmas, avales o referencias humanas externas.
- Sintaxis correcta en 21 archivos PHP afectados. Búsqueda de la terminología retirada: cero coincidencias en fuentes del proyecto, excluyendo dependencias y salidas generadas.
- Build Vite correcto; conserva el aviso previo de bundle mayor de 500 kB.
- La revisión independiente detectó una instrucción residual en la guía de ingeniería; se corrigió también para evitar que reaparezca el requisito. No se alteraron condiciones, umbrales, permisos o juicios originales.
- Guías internas: encabezados conservados y secuencia revisada. El validador auxiliar de skills no pudo ejecutarse por ausencia de PyYAML en su runtime; no se instaló una dependencia en el proyecto para ese fin.

Las ejecuciones técnicas y el build se registran en `output/estado-motor-y-orden-desarrollo-*` y `output/estado-motor-casos-focalizados.txt`. No se modifican criterios para conseguir pruebas verdes; esta instrucción organiza la secuencia de desarrollo, no altera el significado de las reglas. No se realizó push.
