# Auditoría: cuidado de movilidad en Nuevo registro

Referencia: BDD Operativa V2.1, tabla 45 `registros_movilidad`, migración y modelo vigentes. No se modificó el esquema.

| Dato | Estado | Persistencia y decisión |
| --- | --- | --- |
| Residente, profesional y jornada | Existente | `cod_residente`, `cod_personal`, `cod_jornada`. Se derivan y validan en servidor. |
| Fecha y hora | Existente | `fecha_hora`, fijada al guardar con la zona horaria Laravel. |
| Movilidad observada | Existente | `marcha` varchar(40). Se exige una opción del catálogo de Seguimiento Diario: `INDEPENDIENTE`, `ASISTIDA`, `SILLA_RUEDAS`, `ENCAMADO`. Es un estado, no una actividad específica. |
| Traslado | Existente | `traslado` varchar(40). Catálogo de Seguimiento Diario: `INDEPENDIENTE`, `SUPERVISION`, `AYUDA_UNA_PERSONA`, `AYUDA_DOS_PERSONAS`, `GRUA`. |
| Nivel de ayuda | Existente | `tipo_apoyo` varchar(60). Opciones ya usadas en Registros de Enfermería: `INDEPENDIENTE`, `SUPERVISION`, `PARCIAL`, `COMPLETA`, `UNA_PERSONA`, `DOS_PERSONAS`. |
| Equilibrio, fatiga, riesgo de caída | Existente | `equilibrio`, `fatiga`, `riesgo_caida`; se usan sus catálogos de Seguimiento Diario. La fatiga observada no se presenta como una interpretación automática de tolerancia. |
| Dispositivo | Existente sin catálogo | `dispositivo` varchar(80). Seguimiento Diario lo captura como texto libre; `dispositivos_clinicos` registra otros dispositivos clínicos, no un catálogo de ayudas de movilidad. Este popup no ofrece un select inventado. Se puede describir el dispositivo en observaciones. |
| Observaciones | Existente | `observacion` text, opcional; se recortan espacios y se valida un máximo aplicativo de 5000 caracteres sin truncar. |
| Estado | Existente | `estado` se guarda como `VIGENTE`. |
| Actividad específica, origen, destino y duración | Propuesto | No hay columnas ni FK para estos datos en `registros_movilidad`. `actividades` es otra entidad transaccional y `intervenciones_cuidado` pertenece al plan, sin catálogo de actividades de movilidad enlazado a este registro. No se capturan ni guardan aquí. Por ello no hay campos condicionados que mostrar o resetear. |
| Resultado general y tolerancia | Propuesto | No hay columnas con esos nombres. Se presentan los estados estructurados reales: equilibrio, fatiga y riesgo de caída. |
| Incidencia vinculada | Propuesto | `registros_movilidad` no tiene FK ni indicador de incidente. El CTA abre la ruta real `admin.enfermeria.incidentes`; guardar movilidad no crea un incidente ni una caída. |

El formulario limita el envío a columnas reales. El servicio rechaza claves adicionales como origen, destino, duración, dispositivo e incidencia para evitar pérdida silenciosa de datos. La BDD congelada impide agregar estructuras para vincularlas sin aprobación previa.
