---
title: "Auditoría: cuidado de alimentación en Nuevo registro"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: false
verified_against_commit: 7a6758e8
verification_scope: WORKTREE_STATIC_REVIEW_INGESTA_V2
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Evidencia localizada con partes propuestas: no autoriza nuevas columnas/reglas. Aplicar decisiones aprobadas posteriores en su alcance, incluida V2.2 para objetivos; consultar [índice](README.md).

La revisión del 09/10/2026 corresponde al árbol de trabajo sin commit, con base `7a6758e8`. El hash identifica la base, no una versión publicada de Ingesta V2. Las pruebas y la cobertura runtime parcial se registran en [resultado Ingesta V2](../frontend/FORMULARIO_INGESTA_V2_RESULTADO.md); queda pendiente la comprobación runtime de movimiento reducido.

# Auditoría: cuidado de alimentación en Nuevo registro

Referencia: BDD Operativa V2.1, tablas 42 (`registros_ingesta`) y 43 (`registros_hidratacion`), migraciones y modelos vigentes. No se modificó el esquema.

| Dato | Clasificación | Persistencia y decisión |
| --- | --- | --- |
| Residente, profesional, jornada | Existente | FK `cod_residente`, `cod_personal`, `cod_jornada`; derivados en servidor tras comprobar turno, asignación y permiso. |
| Fecha/hora | Existente | `fecha_hora`; hora del servidor Laravel. |
| Tipo de comida | Existente | `tipo_comida` varchar(40). Catálogo usado por Seguimiento Diario: `DESAYUNO`, `MEDIA_MANANA`, `ALMUERZO`, `MERIENDA`, `CENA`, `COLACION`. No existe `OTRO`, así que no se ofrece campo de especificación. |
| Porcentaje consumido | Existente | `porcentaje_consumido` decimal(5,2), opcional. Cualquier valor entre 0 y 100 con hasta dos decimales. |
| Tolerancia | Existente | `tolerancia` varchar(30), opcional. Se usa el catálogo de Seguimiento Diario (`BUENA`, `REGULAR`, `MALA`, `NAUSEAS`, `VOMITO`). Otro formulario usa opciones distintas; conviene unificar ambos catálogos en una decisión de dominio posterior. |
| Dificultad de deglución | Existente | `dificultad_deglucion` boolean; respuesta explícita obligatoria. La captura inicia sin respuesta; opciones observacionales true/false, sin diagnóstico automático. |
| Observación | Existente | `observacion` text, opcional; se recortan espacios y se valida un máximo aplicativo de 5000 caracteres sin truncar. |
| Apetito, estado | Existente | `apetito` y `estado` en ingesta. Ingesta V2 no captura apetito y persiste NULL; su catálogo sigue pendiente de decisión CURRENT frente a la semántica diferente de Seguimiento Diario; `estado` se guarda como `VIGENTE`. |
| Líquidos consumidos | Relacionado | `registros_hidratacion.cantidad_ml` decimal(8,2). Si se informa, se crea un registro independiente de hidratación en la misma transacción, con las mismas FK y fecha/hora. Es opcional, de 0 a 999999,99 mL. No existe FK directa entre ingesta e hidratación. |
| Tipo de dieta, textura, asistencia | Propuesto | Sin columnas en `registros_ingesta` ni catálogo aplicable. No se muestran como controles persistentes. La asistencia se documenta en observaciones. |
| Otras dificultades | Propuesto | Sin persistencia estructurada; se describen en observaciones. |

El servicio conserva la alerta de baja ingesta con el umbral configurado en `enfermeria.porcentaje_baja_ingesta`. El formulario no clasifica automáticamente la tolerancia. Los campos propuestos no se guardan.

## Ingesta V2 — contrato de interacción aprobado (2026-10-09)

- Comida y tolerancia son opciones visuales; no cambian los catálogos existentes.
- Porcentaje vacío permanece NULL; 0 % es una lectura válida.
- Líquidos se habilitan explícitamente y solo con permiso. Desactivar el aporte borra la cantidad; no se persiste cantidad oculta.
- Historial: diez registros VIGENTE del residente autorizado, fecha no futura, orden descendente. Los porcentajes NULL permanecen en el texto y no forman puntos gráficos.
- Umbral del preview: configuración del servidor, sin autoridad clínica en JavaScript. La alerta se evalúa dentro de la transacción al guardar y conserva la deduplicación de alerta activa por residente y tipo.
- Resultado se presenta solo tras persistir. Reenvío desde la etapa de resultado es rechazado.
- No hay nuevos campos, relaciones, reglas clínicas ni cambios a Seguimiento Diario, Signos Vitales o Dolor.
- Los períodos 7/30/90 días no forman parte de esta consulta acotada; la interfaz muestra «Últimos registros».

Evidencia del lote: [resultado Ingesta V2](../frontend/FORMULARIO_INGESTA_V2_RESULTADO.md).

## Revisión de campos y contraste (2026-10-09)

Revisados el diccionario de `registros_ingesta`, `RegistroIngesta`, `registrarAlimentacion` y la captura V2. No falta un campo de captura exigido por el contrato aprobado: comida, porcentaje, tolerancia, deglución, observación y aporte opcional están presentes; residente, profesional, jornada, fecha y estado se resuelven en servidor.

- **Apetito:** columna existente, pero catálogo/semántica CURRENT pendientes. Mantener NULL hasta decisión; no añadir un selector con valores inventados.
- **Dieta/textura y asistencia estructurada:** sin campos en este registro. La asistencia y otras dificultades se documentan en Observaciones. Su estructuración requeriría una propuesta y aprobación separadas.
- **Tipo de líquido:** existe en hidratación; la captura combinada aprobada registra solo volumen. No es un campo requerido de Ingesta. No ampliar el formulario de Hidratación en este lote.

Ajuste visual acotado a `ingesta.css`: superficies de sección más claras, inputs separados por fondo/borde, selección esmeralda con tinta más contrastada, ámbar/coral más reconocibles y borde interior para distinguir selección sin depender solo del color. Se consumen tokens existentes y se conservan variantes oscuras; no se cambian reglas, validaciones, datos ni otros formularios.
