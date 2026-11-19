# Continuidad y carga de Enfermería — 12/11/2026

## Estado y autoridad

Análisis del comportamiento solicitado y de referencias externas. No acredita implementación ni adopta límites clínicos, laborales o legales nuevos. No modifica estructura ni permisos.

La propietaria establece que los residentes asignados acompañan al enfermero durante el período programado de horarios, por ejemplo un mes, a través de sus distintos turnos. Durante el turno puede registrar los actos permitidos a Enfermería; fuera del turno conserva consulta. El residente no debe desaparecer del directorio al terminar una guardia o durante un descanso. La propietaria confirma que el enfermero realiza también higiene, alimentación y movilización, sin auxiliares/cuidadores de apoyo.

Esta indicación posterior sustituye, en su alcance temporal, la decisión de consulta limitada a asignaciones propias del día documentada el 10/10/2026. No habilita prescripción, escritura fuera de turno, atribución de actos a terceros, eliminación de historia ni consulta de información restringida por otra competencia.

## Hallazgo de implementación

`TurnoEnfermeriaService::asignacionesVigentesParaConsulta` limita las jornadas a hoy o ayer cuando el turno nocturno termina hoy. `MiTurnoService` utiliza esa consulta fuera de turno y puede devolver una lista vacía al no encontrar esas jornadas. `GestionarAsignacionesJornada` guarda el vínculo residente-personal-jornada individual. El formulario actual no crea un vínculo por período.

El diccionario de `asignaciones_residente_jornada` define grano residente-personal-jornada con nivel de supervisión, fecha de registro, estado y observación. No define una vigencia mensual maestra. Antes de implementar se debe contrastar el esquema acumulado aprobado y resolver la representación explícita del período y su finalización, sin deducir una continuidad ficticia del intervalo entre dos asignaciones aisladas, usar observaciones como estructura ni crear tablas no autorizadas.

## Comportamiento requerido y propuesta de interacción

| Situación | Comportamiento esperado |
|---|---|
| Vínculo del período vigente y guardia propia activa | Ver residente y registrar solo las operaciones autorizadas a Enfermería |
| Vínculo vigente y fuera de turno, noche de descanso o día sin jornada | Mismo residente visible en consulta; mutaciones denegadas en backend |
| Otra persona cubre el turno | Responsable efectivo explícito para esa guardia y pase de turno; sin responsabilidad simultánea ficticia |
| Cambio de horario | Revisar cobertura y conservar historia; no replicar asignaciones asistenciales sin validar |
| Fin del período o reasignación | Conservar trazabilidad y distinguir asignaciones anteriores; visibilidad histórica no implica acceso clínico indefinido |

Propuesta para Gerencia: elegir trabajador, período del horario y residentes mediante búsqueda; mostrar turnos efectivos, descansos, cobertura faltante y carga estimada; revisar antes de confirmar. Un grupo estable de profesionales debe cubrir al residente a lo largo del día. Un vínculo mensual no significa cuidado permanente por una sola persona.

## Evidencia externa y límites

- [SEDEGES La Paz, Decreto Departamental 059, artículo 13](https://www.sedegeslapaz.gob.bo/files/uploads/DD_059.pdf): el apartado de centros para personas adultas mayores enumera enfermería profesional, auxiliares y asistentes de cuidados básicos. No proporciona allí una cifra mínima/máxima universal de residentes por enfermero. No se ha completado una revisión jurídica de todas las normas vigentes o de la acreditación del centro. Las proporciones del apartado de discapacidad no se trasladan a adultos mayores.
- [CQC, Regulation 18](https://www.cqc.org.uk/guidance-regulation/providers/regulations-service-providers-and-managers/health-social-care-act/regulation-18): dotación y combinación de competencias ajustadas a necesidades y cambios, con cobertura también para emergencias. Referencia extranjera, no norma boliviana.
- [Gobierno de Australia, care minutes](https://www.health.gov.au/our-work/care-minutes-registered-nurses-aged-care/care-minutes): referencia sectorial de 215 minutos totales por residente/día, incluidos 44 de enfermería registrada. Incluye varias categorías de trabajadores y no constituye una ratio segura para un enfermero que realiza todo el cuidado ni un reparto idéntico en cada turno.
- [NICE NG97, 1.3.5](https://www.nice.org.uk/guidance/ng97/chapter/Recommendations): favorecer continuidad y consistencia de cuidados en demencia. No establece que cambiar de enfermero cada mes sea óptimo.
- [NIOSH, diseño de horarios](https://www.cdc.gov/niosh/work-hour-training-for-nurses/longhours/mod5/04.html) y [recomendaciones](https://www.cdc.gov/niosh/work-hour-training-for-nurses/longhours/mod5/05.html): no existe un único horario óptimo demostrado; limitar noches consecutivas, favorecer turnos de ocho horas o menos por la tarde/noche y prever recuperación. Recomienda uno o dos días completos tras cinco turnos consecutivos de ocho horas. No certifica automáticamente el ciclo elegido para RememberMind.
- [NIOSH, movilización segura](https://www.cdc.gov/niosh/healthcare/sphm/): movilizar manualmente residentes expone a lesiones; ayudas mecánicas, formación y organización de la tarea forman parte de la prevención. Un recuento bajo de residentes por sí solo no garantiza seguridad.

## Cómo calcular la carga sin inventar límites

Separar el número de residentes vinculados durante el período de la demanda que debe resolverse en cada turno. Considerar cuidados programados, duración y horario, dependencia funcional real, movilizaciones que requieren ayuda, medicación, curaciones, apoyo cognitivo/conductual, documentación y pase. Descontar pausas y reservar capacidad para imprevistos. Revisar coincidencias: que la suma de minutos quepa no garantiza que dos necesidades simultáneas puedan atenderse.

Ejemplo exclusivamente aritmético: con 360 minutos utilizables de un turno hipotético y demandas medias de 60, 90 o 120 minutos por residente en ese turno, la división produce 6, 4 o 3 residentes. Los tiempos no son estándares clínicos ni una recomendación de cupos. Si faltan mediciones, mostrar carga no calculable y los datos ausentes, sin fabricar un resultado «seguro».

No se identifica un mínimo de residentes que todo enfermero deba recibir. Tampoco se adopta un máximo único. La propuesta es un límite configurable aprobado según la composición real de residentes y equipo, con revisión ante cambios de necesidades y cobertura.

La programación mensual solicitada es una unidad de planificación administrativa, no un límite probado de duración de la relación terapéutica. Conservar continuidad mientras sea adecuada, revisando los cambios clínicos, preferencias, carga y reemplazos.

## Próxima implementación y verificación

1. Resolver persistencia explícita del vínculo por período contra las extensiones aprobadas. Cualquier cambio estructural concreto necesita autorización específica.
2. Separar consulta por vínculo de período y escritura por jornada efectiva en políticas/servicios compartidos y consumidores.
3. Integrar asignación por período, cobertura de descansos, cambios de horario, cierre/reasignación e historial en una transacción cuando corresponda.
4. Probar visibilidad en días de descanso y entre jornadas, bloqueo de escritura fuera de turno incluso por petición manipulada, cruce de medianoche, acceso ajeno, cuentas inactivas, continuidad en los módulos permitidos, duplicados, rollback y auditoría.

En este análisis se inspeccionaron fuentes y código. No se ejecutaron pruebas para acreditar una función nueva ni se cambió comportamiento de producción.
