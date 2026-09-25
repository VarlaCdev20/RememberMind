# Requerimientos del cliente simulado

Son hipótesis para validación institucional, no acuerdos clínicos ya aprobados. Alcance inicial: una institución, residentes identificados, personal autorizado y datos de simulación. No multi-tenant ni facturación clínica completa por defecto.

| ID | Actor/proceso | Necesidad | Criterio observable de aceptación | Fase |
|---|---|---|---|---|
| R01 | Administración | Identificar y admitir | Una persona no se duplica por reingreso; una estancia activa y cama disponible | Núcleo |
| R02 | Dirección | Ocupación y calidad | Indicadores con fecha, denominador, exclusiones y enlace a fuente permitida | Núcleo |
| R03 | Médico | Priorizar y registrar | Diagnóstico/prescripción con autor y fecha, solo dentro de permisos | Núcleo |
| R04 | Enfermería | Turno y administración | Dosis asignada al residente correcto, ejecución única u omisión justificada | Núcleo |
| R05 | Psicología | Cognición, afectividad y conducta | Instrumento y versión identificables; distinguir no evaluado de resultado normal | Núcleo |
| R06 | Fisioterapia | Movilidad/rehabilitación | Evaluación basal, plan, sesión y evolución comparables | Ampliación |
| R07 | Nutrición | Peso, ingesta, hidratación y dieta | Unidad, fecha y fuente; plan alimentario versionado | Ampliación |
| R08 | Trabajo Social | Familia y apoyo | Vínculo vigente, seguimiento y visitas sin acceso clínico indiscriminado | Ampliación |
| R09 | Cuidador | Tareas y observaciones | Lista por turno/asignación, registrar ejecución sin prescribir | Núcleo |
| R10 | Familiar | Información autorizada | Solo residentes vinculados y campos explícitamente compartidos | Después de P0 |
| R11 | Técnico | Operación segura | Salud de Jobs/backups; sin acceso clínico rutinario por ser técnico | Núcleo |
| R12 | Todos los autorizados | Expediente 360 | Una URL conceptual, secciones filtradas por servidor y timeline paginado | Núcleo |
| R13 | Profesionales | Comparar estados | Consultar fecha del hecho y fecha de registro, sin reinterpretar con una escala nueva | Núcleo |
| R14 | Equipo asistencial | Alertar y actuar | Alerta → responsable → acción → cierre o reevaluación, sin cierres silenciosos | Núcleo |
| R15 | Evaluador autorizado | Apoyo experto | Resultado explicable, versionado, no sustituye diagnóstico ni crea prescripción | Investigación |
| R16 | Coordinación | Agenda/documentos | Citas con responsable; documentos privados, versión y recepción verificables | Ampliación |

## Flujo del producto

Conocer (personas/admisión) → evaluar (Clinical/Assessment) → cuidar (Care/Medication) → observar (signos/observaciones) → comparar (consultas longitudinales) → detectar (Safety) → inferir (ExpertSystem) → alertar → actuar (tareas/acciones) → reevaluar. La etapa experto es opcional: su indisponibilidad no bloquea cuidado ni registro.

## Requerimientos no funcionales

Una desarrolladora debe poder ejecutar una suite rápida local, restaurar un respaldo y desplegar una sola aplicación. Toda escritura relevante tendrá autor, fecha, autorización e integridad. Paginar listados/timeline; no cargar la vida clínica completa en cada visita. Metas iniciales a medir con datos sintéticos representativos: p95 de lecturas habituales menor de 1 s en servidor y navegación útil menor de 2 s en red institucional; no son resultados medidos ni garantías.

Tablet y escritorio, navegación por teclado, formularios por etapas cortas, identificación del residente siempre visible y guardado explícito. Evitar inferir éxito de una acción clínica antes de confirmación del servidor. Respaldos: proponer RPO 24 h/RTO 4 h para simulación, a redefinir antes de un piloto operativo.

## Validación pendiente

Confirmar quién admite, quién valida VGI, qué profesiones pueden prescribir, quién asigna camas, contenido autorizado a familiares, turnos cruzando medianoche, retención de documentos, consentimientos, instrumentos y sus derechos de uso. Probar escenarios sintéticos con cada perfil; registrar desacuerdos como cambios de requerimiento. No codificar umbrales clínicos sin validación profesional.
