# Auditoría: cuidado de alimentación en Nuevo registro

Referencia: BDD Operativa V2.1, tablas 42 (`registros_ingesta`) y 43 (`registros_hidratacion`), migraciones y modelos vigentes. No se modificó el esquema.

| Dato | Clasificación | Persistencia y decisión |
| --- | --- | --- |
| Residente, profesional, jornada | Existente | FK `cod_residente`, `cod_personal`, `cod_jornada`; derivados en servidor tras comprobar turno, asignación y permiso. |
| Fecha/hora | Existente | `fecha_hora`; hora del servidor Laravel. |
| Tipo de comida | Existente | `tipo_comida` varchar(40). Catálogo usado por Seguimiento Diario: `DESAYUNO`, `MEDIA_MANANA`, `ALMUERZO`, `MERIENDA`, `CENA`, `COLACION`. No existe `OTRO`, así que no se ofrece campo de especificación. |
| Porcentaje consumido | Existente | `porcentaje_consumido` decimal(5,2), opcional. Cualquier valor entre 0 y 100 con hasta dos decimales. |
| Tolerancia | Existente | `tolerancia` varchar(30), opcional. Se usa el catálogo de Seguimiento Diario (`BUENA`, `REGULAR`, `MALA`, `NAUSEAS`, `VOMITO`). Otro formulario usa opciones distintas; conviene unificar ambos catálogos en una decisión de dominio posterior. |
| Dificultad de deglución | Existente | `dificultad_deglucion` boolean; único checkbox estructurado de dificultades. |
| Observación | Existente | `observacion` text, opcional; se recortan espacios y se valida un máximo aplicativo de 5000 caracteres sin truncar. |
| Apetito, estado | Existente | `apetito` y `estado` en ingesta. Este formulario no captura apetito; `estado` se guarda como `VIGENTE`. |
| Líquidos consumidos | Relacionado | `registros_hidratacion.cantidad_ml` decimal(8,2). Si se informa, se crea un registro independiente de hidratación en la misma transacción, con las mismas FK y fecha/hora. Es opcional, de 0 a 999999,99 mL. No existe FK directa entre ingesta e hidratación. |
| Tipo de dieta, textura, asistencia | Propuesto | Sin columnas en `registros_ingesta` ni catálogo aplicable. No se muestran como controles persistentes. La asistencia se documenta en observaciones. |
| Otras dificultades | Propuesto | Sin persistencia estructurada; se describen en observaciones. |

El servicio conserva la alerta de baja ingesta con el umbral configurado en `enfermeria.porcentaje_baja_ingesta`. El formulario no clasifica automáticamente la tolerancia. Los campos propuestos no se guardan.
