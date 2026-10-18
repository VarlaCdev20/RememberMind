# Gobierno de reglas: diccionario mínimo

Fecha: 18/10/2026. Estado: DICCIONARIO APROBADO POR LA PROPIETARIA. Autorización expresa: «Sí, aprobar las cinco columnas propuestas», con condición posterior «MIENTRAS 4FN NO SE ROMPA ESTA BIEN». Autoridad funcional: solicitud actual y confirmación de gestión/aprobación por Medicina y Psicología, con Superadministración de consulta. La aprobación no acredita aplicación ni verificación de la migración.

## Problema e impacto

La tabla `reglas_expertas` conserva autor y fecha de creación, pero no una revisión identificable ni la relación directa con la regla sustituida. `versiones_modelo_experto` ya conserva sucesión, motivo y fechas de creación/retiro. El editor existente permite dejar la regla INACTIVO en una sucesora sin borrar su definición anterior.

Para mostrar quién propuso, quién aprobó/rechazó y qué regla la reemplazó se necesita conservar esos datos explícitamente. No se guardarán en texto libre, JSON o una tabla de auditoría paralela.

## Cambio propuesto: cero tablas nuevas, cinco columnas nullable

Única tabla afectada: `reglas_expertas`. PK, columnas actuales, relaciones y registros previos se conservan.

| Columna | Tipo | Relación / contrato |
| --- | --- | --- |
| `cod_regla_anterior` | string(20), nullable | Self-FK a `reglas_expertas.cod_regla_experta`, RESTRICT al borrar, índice; sin autorreferencia ni ciclos |
| `estado_revision` | string(20), nullable | PENDIENTE, APROBADA o RECHAZADA. NULL previo significa que no hay una revisión registrada |
| `cod_usuario_revision` | string(20), nullable | FK a `usuarios.cod_usuario`, RESTRICT e índice; actor real derivado de sesión |
| `fecha_hora_revision` | datetime, nullable | Fecha técnica de la decisión humana |
| `justificacion_revision` | text, nullable | Motivo obligatorio de aprobación/rechazo |

Checks: NULL/PENDIENTE exige los tres campos de firma NULL. APROBADA/RECHAZADA exige actor, fecha y justificación no vacía. La fecha de revisión no puede ser anterior a la creación. Una regla no se enlaza consigo misma. El servicio valida además pertenencia de familia/sucesión y ausencia de ciclos.

No se rellena la historia anterior con aprobaciones actuales. Una regla nueva comienza PENDIENTE. La ausencia de revisión profesional no bloquea la ejecución técnica válida; la salida conserva su estado pendiente y no afirma validación clínica.

Archivo: reutilizar ACTIVO/INACTIVO y sucesión de versión; el motivo/autor de cambio están en la revisión del conocimiento y Activitylog. No se agrega una columna redundante de archivo. Un rechazo o sustitución no borra resultados ni permite modificar la definición histórica.

## Alternativas

1. Solo versiones existentes: permite retirar reglas y conservar su autor, pero no satisface aprobación estructurada por regla ni enlace explícito entre reglas.
2. Cinco columnas propuestas: cumple el alcance sin tablas nuevas.
3. Nueva tabla de decisiones: admite varias decisiones por regla, pero añade una relación que no es necesaria para este incremento; las rectificaciones se registrarán mediante una sucesora.

## Seguridad y reversión

Crear permisos específicos de gestionar y revisar conocimiento para Medicina/Psicología conforme a la decisión recibida. No reutilizar un permiso de lectura como escritura. La revisión de una regla no firma la evaluación de un residente.

Migración forward y reversible con SQLite/PostgreSQL; si existen datos de aprobación o sustitución, el rollback deberá rechazar la pérdida de esa historia. No borrar revisiones para facilitar una reversión. No modificar migraciones aplicadas ni resetear la base local.

La propietaria aprobó el diccionario concreto antes de escribir la migración. Se mantienen las reglas de database/AGENTS.md para su aplicación y comprobación.

Aplicación comprobada el 18/10/2026 en `remembermind_dev`, exclusivamente mediante la migración forward indicada, con respaldo previo e índice de restauración verificado. Ocho reglas originales comparadas con el respaldo: idénticas. Evidencia privada en `storage/app/private/gobierno-reglas-20261018/`, excluida de Git. Se añadieron los permisos específicos sin resembrar ni sustituir los permisos restantes de los roles.

Verificación: 47 pruebas y 249 aserciones en PostgreSQL desechable, incluyendo tres carreras independientes; 37 pruebas y 191 aserciones SQLite en revisión de seguridad independiente. Revisión estática de integridad y 4FN: PASS del incremento. La aceptación visual y el ejecutor institucional integral tienen su estado separado en el checklist 1–8.

## Cuarta forma normal

Una revisión de regla tiene como máximo una antecesora y una decisión de revisión. La PK determina estado, actor, fecha y motivo de esa decisión; no se almacenan conjuntos independientes de aprobadores o sustitutas en la misma fila. Varias propuestas o decisiones posteriores producen revisiones distintas con su propia PK. Las revisiones de Medicina y Psicología sobre una evaluación permanecen en la relación existente, sin duplicarlas en reglas. No hay nuevas tablas, atributos multivaluados, listas en texto ni JSON/EAV. La posible ramificación de sucesoras se representa mediante varias filas con FK a la antecesora.
