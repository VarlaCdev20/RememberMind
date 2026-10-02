# Auditoría: cuidado de eliminación en Nuevo registro

La BDD Operativa V2.1 tiene una sola tabla para ambos tipos: `registros_eliminacion` (tabla 44). La migración y el modelo `RegistroEliminacion` confirman la misma estructura. No se modificó el esquema.

| Dato | Estado | Persistencia y decisión |
| --- | --- | --- |
| Residente, profesional y jornada | Existente | `cod_residente`, `cod_personal`, `cod_jornada`; se obtienen en el servidor tras validar permiso, asignación y turno. |
| Fecha y hora | Existente | `fecha_hora`; se fija con `now()` al guardar. |
| Tipo | Existente | `tipo_eliminacion` varchar(30). El catálogo de Seguimiento Diario contiene `URINARIA`, `INTESTINAL`, `AMBAS`. Este popup registra un tipo a la vez y ofrece los dos primeros. |
| Cantidad | Existente | `cantidad` varchar(40), opcional. Este formulario acepta solo valores numéricos no negativos de hasta 40 caracteres y los conserva como texto, sin redondeo ni unidad inventada. |
| Características | Existente | `caracteristica` varchar(120), opcional. Un campo de texto distinto por tipo en la interfaz alimenta la misma columna. |
| Continencia | Existente | `continencia` varchar(30), opcional. Se usan valores del catálogo de Seguimiento Diario aplicables al tipo: `CONTINENTE`, `INCONTINENCIA_URINARIA` o `INCONTINENCIA_FECAL`. |
| Observaciones | Existente | `observacion` text, opcional; espacios recortados, máximo aplicativo de 5000 caracteres sin truncamiento. |
| Estado | Existente | `estado` string(20), guardado como `VIGENTE`. |
| Color, aspecto, olor, consistencia | Propuesto como campos separados | No tienen columnas ni catálogos propios en esta tabla. Se describen en `caracteristica`; no se muestra una escala externa. |
| Dispositivo y asistencia | Propuesto como campos estructurados | Sin columnas en `registros_eliminacion`. Se describen en observaciones. |

Al cambiar entre Urinaria e Intestinal, Livewire limpia cantidad, característica y continencia de ambos paneles. El servidor recibe únicamente los campos del tipo elegido; valores ocultos manipulados no se persisten.

Los flujos anteriores de eliminación en `CuidadosEnfermeriaService::registrar` y `RegistrosEnfermeria` intentan escribir atributos (`consistencia`, `es_continente`, `usa_dispositivo`) que no figuran en la tabla 44; el nuevo formulario usa `registrarEliminacion` y las columnas reales. Su corrección general queda fuera del alcance de este formulario.
