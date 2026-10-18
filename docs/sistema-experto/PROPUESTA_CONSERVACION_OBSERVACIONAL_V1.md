# Conservación observacional V1 — Decisión aprobada

Estado: **APROBADA por la propietaria el 18/10/2026**. Confirmación literal: «Sí, aprobar esas dos FK». Comprende las dos columnas/FK, índices y checks descritos, cero tablas nuevas y ningún borrado de historia. Se implementa en `2026_10_18_001000_extend_origen_observacional_instantaneas.php`; la aplicación local se registra en la entrega funcional.

## Problema

Los controles cognitivos válidos pueden no tener atención asociada. La rama D137 del check `snap_episodio_original` en `hechos_instantanea_experta` exige atención o actividad. Inventar esa relación para conservar una observación violaría la BDD. La referencia polimórfica `cod_registro_fuente` de `evidencias_evaluacion` tampoco conserva por sí sola la autoría original si luego cambia el control.

## Diccionario aprobado: cero tablas nuevas

Extender únicamente `hechos_instantanea_experta`:

| Columna nueva | Tipo | FK | Nullability |
|---|---|---|---|
| `cod_control_cognitivo` | string(20) | `controles_cognitivos.cod_control_cognitivo`, índice y restrictOnDelete | NULL para filas históricas y otros orígenes |
| `cod_personal_autor` | string(20) | `personal.cod_personal`, índice y restrictOnDelete | NULL para filas históricas y otros orígenes |

Añadir el origen permitido `CONTROL_COGNITIVO` a los checks existentes. Para ese origen: ambas nuevas FK y `cod_evidencia_evaluacion` serán obligatorias; `cod_actividad` será NULL; `cod_atencion` conservará la atención original o NULL, nunca una creada para completar el contrato. La fecha clínica queda en las columnas existentes `fecha_hecho_*`; el booleano original o NULL queda en `valor_*`. No se introduce texto clínico libre, JSON ni otro almacén de auditoría.

Los checks existentes conservarán las ramas D137 y sintéticas históricas y exigirán que las nuevas FK sean NULL en ellas. No se modifica ni borra una instantánea previa.

En Application se verificará: residente de control/evaluación, autor original, atención del mismo residente cuando exista, fecha <= corte, estado, tipo de valor y correspondencia entre evidencia/mapeo/campo. Se capturará fuente y autor dentro de la transacción. Las tablas actuales de reglas/condiciones/evaluación/traza/soportes conservarán versión y las decisiones `CUMPLE`, `NO_CUMPLE`, `NO_EVALUABLE` y `ERROR_TECNICO`, este último sin fabricar un resultado positivo. Un error de conexión que impida registrar una ejecución se reportará como fallo de conservación; nunca como guardado correcto.

El inventario de negocio permanece en **106 tablas (73 operativas + 33 expertas)**. Son dos columnas nullable, dos FK restrictivas, sus índices y ajustes de checks en una tabla ya aprobada. Una migración reversible no podrá retirar columnas si contiene instantáneas de este nuevo origen; el rollback debe bloquearse antes de perder datos.

## Autorización aprobada

La propietaria decidió «Solo Medicina y Psicología». El permiso `evaluaciones_expertas.ejecutar_observaciones` se concede únicamente a `MEDICO GENERAL/GERIATRA` y `PSICOLOGO/A`. La Policy exige además cuenta activa, personal propio activo con profesión correspondiente al rol, lectura autorizada, residente ADMITIDO y ausencia de preview. Enfermería conserva consulta; Superadministración no recibe esta escritura automáticamente. No se habilita inferencia clínica, firmas SIM, prescripción ni clasificación DCL.

La acción se ejecuta explícitamente, con solicitud UUID idempotente, y conserva todas las fuentes anteriores al corte, no solo una página. Una regla cumple cuando existe un registro válido afirmativo. Sin positivos, si algún registro es desconocido/no interpretable queda no evaluable; si todos los registros disponibles son negativos, no cumple. No se calcula recurrencia ni se declara normalidad.

Cada control conserva tres hechos booleanos y su estado original como metadato tipado compartido, sobre `valor_*` ya existente. No se capturan textos clínicos libres. Los hechos conservan control, autor, fecha y episodio originales. El estado compartido referencia la evidencia del primer indicador del mismo control; no es una condición clínica adicional. La huella cubre los datos originales completos y las decisiones. La reconstrucción verifica conocimiento, huella, criterios, condiciones, resultados y conjuntos exactos de soportes/participaciones, sin consultar valores actuales del control.

Un error técnico o de persistencia revierte la operación y se muestra como fallo de guardado; no se confirma una ejecución parcial. La auditoría Spatie registra referencia y actor sin payload clínico completo ni otra tabla de auditoría.

## Alternativa sin cambios físicos

El cálculo de consulta permanece disponible cuando todavía no hay ejecución conservada. Se identifica como no guardado y nunca sustituye silenciosamente una instantánea corrupta. No se eligió esta alternativa como solución de conservación; el guardado aprobado usa las tablas existentes.
