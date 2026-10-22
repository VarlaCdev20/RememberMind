# Intervención de Enfermería y entrada al sistema experto

Fecha de revisión: 21/10/2026. Estado: guía de captura implementada; ampliación tipada e integración experta **pendientes de decisión institucional**.

## Evidencia y alcance

Se consultaron el baseline congelado y su diccionario, `AlertasService`, `AlertasPanel`, el catálogo de fuentes y hechos experto y el índice vigente de O.R.I.O.N. La documentación más reciente incluye ampliaciones y reducciones posteriores al inventario original; esta entrega no modifica ninguno de esos esquemas.

La [Norma técnica para el manejo del expediente clínico, Ministerio de Salud de Bolivia, apartado 12.13](https://www.minsalud.gob.bo/images/Documentacion/redes_salud/NORMA%20TECNICA%20PARA%20EL%20MANEJO%20DEL%20EXPEDIENTE%20CLINICO.pdf) distingue evolución de Enfermería y registro de tratamientos. Describe el estado, la evolución y las intercurrencias del paciente, además de la identificación temporal y profesional. Es una publicación ministerial localizada; esta consulta no certifica su vigencia jurídica ni su aplicabilidad completa a la residencia.

El [NMC, sección 10](https://www.nmc.org.uk/standards/code/read-the-code-online/) recomienda registros oportunos, exactos, atribuibles, fechados y con las actuaciones ante problemas detectados. El [BCCNM](https://www.bccnm.ca/RN/learning/documentation/Pages/Default.aspx) desarrolla observaciones, actuaciones, razonamiento, respuesta, comunicación y continuidad. Se usan como referencias profesionales internacionales; no como nuevas obligaciones locales.

## Captura inmediata implementada

El formulario mantiene una nota clínica y presenta una guía breve, sin datos precompletados ni afirmaciones de normalidad:

| Contenido de la nota | Finalidad |
|---|---|
| Observación y momento del hecho | Distinguir lo constatado o referido de una interpretación. Si el hecho ocurrió antes del registro, dejarlo explícito. |
| Actuación efectivamente realizada | Conservar qué se hizo, cuándo y su motivo, dentro de las competencias vigentes. |
| Respuesta observada | Registrar la respuesta o aclarar que aún no se ha evaluado; no asumir mejoría. |
| Comunicación y pendientes, cuando corresponda | Permitir que el siguiente profesional continúe el cuidado. |

El residente procede de la alerta autorizada. Autoría de usuario e instante de registro se asignan en el backend. No hay selector para atribuir la intervención a otro profesional. Se mantienen autorización por Enfermería, personal activo, turno y residente asignado, validación vigente y transacción con evento histórico. Registrar una intervención conduce a `EN_ATENCION`; no acredita resolución ni cierra automáticamente la alerta.

Las mediciones numéricas continúan en sus formularios existentes, con valores y unidades. No se duplican en un cuestionario de Alertas. La guía ayuda a redactar la nota; **no son cuatro campos estructurados ni se extraen hechos mediante NLP**.

## Persistencia real y brechas

| Dato | Fuente actual | Límite |
|---|---|---|
| Residente y alerta | Relación del evento con la alerta y el residente | Las claves se utilizan internamente; no se imprimen como contenido de pantalla. |
| Usuario que registra | `eventos_alerta.cod_usuario` | El esquema del evento no conserva una nueva firma digital ni una instantánea independiente del profesional. |
| Fecha de registro | `eventos_alerta.fecha_hora` | No sustituye una fecha clínica retrospectiva tipada. |
| Tipo de actuación y transición | `tipo_evento`, estados anterior/nuevo | No expresa resultado clínico por sí mismo. |
| Observación, actuación, respuesta y comunicación | Nota original en `descripcion` | Texto narrativo: no es una estructura de hechos normalizados. |
| Ejecución de un cuidado planificado | `ejecuciones_cuidado`, cuando existe una intervención y jornada reales | No crear una ejecución ficticia para guardar una nota de alerta. |
| Observación cognitiva y otras fuentes aprobadas | Controles, aplicaciones y demás adaptadores vigentes | Conservar su grano, autor, fecha, ausencia y procedencia; no inferirlas de una nota. |

## Contrato propuesto para decidir

Antes de conectar nuevas intervenciones como entrada experta debe acordarse:

1. Qué decisión experta necesita cada dato y si se usa como contexto o como evidencia de una regla aprobada.
2. Fuente canónica por variable, tipo, unidad, fecha del hecho, autor, jornada/episodio y estado del registro.
3. Representación explícita de «no observado», «no evaluado» y «no aplica», sin convertir ausencias en normalidad.
4. Vínculo verificable entre observación, intervención y respuesta, preservando las notas anteriores y las correcciones.
5. Adaptador versionado, permisos de lectura, pruebas de datos cruzados y reproducción de la entrada original.
6. Si los campos necesarios no existen en las fuentes aprobadas, diccionario y cambio relacional sometidos a aprobación antes de migrar.

No se propone almacenar un formulario serializado en `descripcion`, JSON o una tabla EAV para evitar la gobernanza. Tampoco se activan pesos, umbrales, diagnósticos o alertas expertas nuevas. La integración de esta nota como hecho experto permanece aislada hasta resolver ese contrato; la documentación clínica actual sigue funcionando.

## Verificación

La prueba del ciclo de Enfermería verifica guía visible, intervención autorizada, cambio de estado y conservación de eventos. Se conservan las negativas de ámbito/competencia de la entrega. No se acredita validación clínica del formulario ni alimentación experta nueva. Los resultados técnicos y la evidencia de pantalla se registran en [el informe de entrega](REDISENO_INTERACTIVO_2026_10_21.md).
