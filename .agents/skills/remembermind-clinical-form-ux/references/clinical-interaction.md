# Matriz de interacción clínica

Para cada grupo/campo definir: dato y unidad, fuente, condición de visibilidad, regla backend, estado técnico, estado clínico, mensaje, siguiente acción y persistencia prevista.

## Validación

| Capa | Ejemplo de UX | Autoridad |
|---|---|---|
| Formato | Dato no numérico, unidad esperada | Request/validación backend |
| Coherencia | PA con sistólica y diastólica incompleta | Regla existente del flujo |
| Interpretación | Medición válida con estado warning/high/critical | Servicio clínico aprobado |

No introducir aquí cifras fisiológicas ni rangos. La UX puede representar estados suministrados y explicar qué revisar; no inventa clasificaciones.

## Estados

Revisar neutral, focus, filled, evaluating, valid, warning, high, critical, technical-error, disabled y loading. No todos son estados persistidos: neutral/focus/evaluating describen interacción, mientras severidad procede del dominio. Marcar estados que el flujo no soporta, sin fabricarlos.

Progressive disclosure: por ejemplo, «¿Consumió alimentos?» muestra cantidad/porcentaje/tolerancia o motivo/contexto solo si esos campos y ramas ya existen. Cambiar de rama no debe guardar información oculta contradictoria ni borrarla silenciosamente; respetar contrato actual y explicar cambios.

## Dirty state y recuperación

- Comparar contra el estado inicial o último resultado confirmado, no contra un formulario supuesto vacío.
- Sin cambios: cerrar.
- Con cambios: ofrecer Volver a revisar y Descartar cambios.
- Escape, backdrop, X, volver y cambio de residente siguen la misma protección.
- Envío en curso: impedir duplicación y cierre que oculte un resultado incierto; mostrar feedback.
- Validación fallida: conservar datos válidos, errores inline y foco pertinente.
- Fallo backend: mensaje seguro y recuperación; no inventar éxito ni reenviar ciegamente una operación de resultado incierto.
- Éxito confirmado: mostrar qué se registró y continuar al seguimiento/actividad natural.
- Reintento: usar garantías del backend; deshabilitar un botón no garantiza idempotencia.

Evitar guardar borradores clínicos en localStorage o servicios externos sin autorización y contrato de privacidad. Contexto leído no otorga escritura. La fecha de registro y la fecha real de observación no son intercambiables.
