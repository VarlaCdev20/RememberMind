# Patrones de copy operativo

## Vocabulario

Aplicar las cinco entidades del contrato común. Usar «residente» para la persona admitida, «postulante» en preadmisión, «usuario» para acceso, «personal» para trabajadores/profesionales y «contacto» para relaciones familiares/responsables. Usar «familiar» solo cuando esa relación está determinada. No renombrar identificadores/rutas durante una tarea de copy.

## Mensajes con fuente

| Situación | Patrón |
|---|---|
| Validación | Qué revisar + razón comprobada + cómo corregir |
| Error backend | Qué no pudo completarse + recuperación segura |
| Éxito | Qué operación se confirmó + efecto real + continuidad |
| Empty | Qué falta + por qué no hay contenido + acción autorizada |
| Confirmación sensible | Acción + entidad/residente + consecuencia real |
| Disabled | Condición que falta y cómo resolverla si se permite |
| Loading | Acción en curso; no resultado anticipado |
| Preview | Sin guardar, cercano al dato/gráfico |

Ejemplo de validación, solo si la regla existe: «Revisa la temperatura ingresada. El valor está fuera del intervalo admitido para esta medición».

Ejemplo de éxito, después de confirmación: «Signos vitales registrados. Las mediciones fueron incorporadas al seguimiento clínico».

Acciones según capacidad real: Confirmar y registrar, Volver a revisar, Descartar cambios, Atender alerta, Registrar intervención. Si «Atender alerta» solo abre un detalle, utilizar «Ver alerta»; el verbo no promete una mutación que no ocurre.

## Lectura y tono

Label identifica el dato; hint explica unidad/formato; placeholder da ejemplo sin reemplazar label. Headings describen tarea/sección, sin términos técnicos internos. Tooltips amplían ayuda y no contienen la única explicación crítica.

No usar lenguaje alarmista, infantil o condescendiente. Mensaje crítico permanece fuerte, concreto y reconocible por icono/texto/color. Escribir claro no equivale a reducir su importancia.

En resultados inciertos no afirmar fallo total ni éxito: explicar que no se pudo confirmar y ofrecer comprobar el estado antes de reintentar según el contrato. No prometer notificaciones, acciones futuras o intervención automática sin evidencia.

Revisar textos con nombres largos, conteos 0/1/muchos y móvil. Si humanizer interviene como auxiliar, conservar hechos, severidad, términos y acciones de este contrato.
