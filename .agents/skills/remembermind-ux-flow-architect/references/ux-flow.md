# Especificación de flujo por rol

Entregar antes de diseñar el layout, con nombres de rutas/acciones realmente existentes:

```text
UX FLOW
Objetivo: tarea y resultado observable.
Usuario: rol, contexto operativo y alcance autorizado.
Entrada: ruta/origen, residente o entidad y estado previo.
Flujo principal: pasos numerados y acción prioritaria de cada paso.
Excepciones: carga, vacío, falta de relación/permiso, validación y fallo técnico.
Salida: resultado confirmado y estado observable.
Siguiente acción natural: destino o actividad que permite continuar.
```

Añadir a la especificación una tabla de información: elemento, fuente, prioridad, motivo de permanencia y alternativa en móvil. Distinguir frecuencia de urgencia: una acción infrecuente puede requerir máxima visibilidad si el dominio la marca crítica.

## Ejemplos de análisis

- Enfermería: Mi turno → qué requiere atención → mis residentes → medicación → controles → cuidados → alertas → continuidad → pase de turno. Es un ejemplo de orden de trabajo; comprobar asignaciones y jornadas reales, sin inventar horarios ni registros.
- Formulario: contexto → acción → captura progresiva → validación → interpretación autorizada → confirmación → resultado → continuidad.
- Administración: preadmisión, revisión y admisión formal conservan sus estados; aprobar una preadmisión no crea un residente.
- Familiar: priorizar información autorizada y legible del residente vinculado. No inferir acceso al expediente completo.
- Médico: facilitar valoración y consulta de historia; mostrar acciones de prescripción solo con permiso/competencia real.

## Recuperación y navegación

Comprobar volver, filtros, selección, paginación y cierre con captura pendiente. Distinguir no hay datos, no hay resultados, no hay permiso y no se pudo cargar. Cada excepción tiene explicación y una salida viable. Evitar pasos que vuelven a preguntar residente, profesional o información ya disponible en el contexto.

No crear métricas para rellenar un dashboard. Si la consulta carece de un dato necesario, emitir UX GAP DETECTADO y seguir con las partes independientes.
