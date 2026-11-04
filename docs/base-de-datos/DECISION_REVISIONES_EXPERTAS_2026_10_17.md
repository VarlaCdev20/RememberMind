# Revisiones profesionales separadas de la valoración experta

Estado: **APROBADA POR LA PROPIETARIA**, 17/10/2026.

Respuestas explícitas de esta tarea:

- «Psicólogo y médico, con revisiones separadas».
- «Una tabla de revisiones vinculada a la evaluación (Recomendado)», referida
  a `docs/sistema-experto/PROPUESTA_VALORACION_PROFESIONAL_2026_10_17.md`, opción B.

La estructura añade exclusivamente `revisiones_evaluacion_experta`: 33 tablas
expertas + 73 operativas = **106 tablas de negocio**. No modifica las 32
expertas anteriores ni las operativas. Tablas técnicas quedan fuera del conteo.

## Diccionario aprobado

| Columna | Tipo | Contrato |
|---|---|---|
| cod_revision_experta | string(20), PK | Correlativo RVE |
| cod_evaluacion_experta | string(20), FK requerida | Evaluación original |
| disciplina | string(20) requerida | MEDICINA o PSICOLOGIA; discrimina las revisiones separadas solicitadas |
| cod_personal | string(20), FK requerida | Profesional firmante, derivado de la sesión |
| cod_usuario | string(20), FK requerida | Cuenta que registra el dictamen |
| fecha_hora_revision | datetime requerida | Fecha de la firma |
| decision | string(20) requerida | CONFIRMADA o RECHAZADA |
| justificacion | text requerido | Fundamento profesional no vacío |
| cod_revision_anterior | string(20), FK propia nullable | Rectificación de la cabeza de la misma disciplina/evaluación |

Las FK son restrictivas e indexadas. La self-FK compuesta exige igualdad de
evaluación y disciplina. Una sola raíz por evaluación/disciplina; una sola
sucesora por revisión. La PK y la clave compuesta mantienen el contexto.
No se admiten autorreferencias. UPDATE y DELETE se rechazan en SQL y Model.
El rollback estructural se rechaza si hay dictámenes almacenados.

Pendiente se deriva de la ausencia de revisión de una disciplina. Una firma
de Psicología no confirma Medicina ni viceversa. El resultado del motor y
cada dictamen original permanecen intactos; corregir crea otra fila.

## Competencia y alcance

Se autorizan operaciones separadas para rol MEDICO GENERAL/GERIATRA con
personal MEDICINA, y rol PSICOLOGO/A con personal PSICOLOGIA. Exigen cuenta y
personal activos, permiso explícito, Policy, acceso al residente y ausencia
de previsualización. Los nuevos permisos técnicos son
`evaluaciones_expertas.revisar_medicina` y
`evaluaciones_expertas.revisar_psicologia`. El rol de superadministración por
sí solo no concede competencia clínica.

La firma aplica a una valoración operativa con contrato de revisión aprobado
y soporte íntegro. No autoriza firmar fixtures, convertir los datos 03A en
pruebas aplicadas, activar versiones pendientes ni inventar baremos. Aprobar
esta tabla no equivale a aprobar un protocolo de interpretación clínica.
No crea diagnósticos, alertas, prescripciones o publicación a familiares.

La fuente de verdad estructural anterior se conserva con su alcance histórico.
El contrato clínico y su matriz de interpretaciones siguen pendientes de
validación profesional antes de habilitar inferencia operativa Petersen.
