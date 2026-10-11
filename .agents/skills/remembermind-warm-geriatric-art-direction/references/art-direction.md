# Brief artístico de una superficie

Usar la paleta y funciones tipográficas de [la autoridad común](../../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md). No repetir una paleta distinta en esta referencia.

Entregar: superficie/rol/tarea, identidad que debe conservarse, composición, densidad, paleta por función, jerarquía tipográfica, depth por región, glass por propósito, semántica de estados y riesgos de legibilidad.

## Material y relieve

- Depth 0: lienzo estable y cálido.
- Depth 1: card normal, sombra discreta y superficie neutral.
- Depth 2: panel importante con jerarquía adicional.
- Depth 3: control/acción flotante, separación clara del lienzo.
- Depth 4: diálogo con scrim que permite leer y decidir.
- Glass soft: toolbar, segmented controls, header contextual.
- Glass elevated: acción flotante, cabecera de modal, overlay secundario.

El efecto debe seguir la función; no elevar cada card ni convertir cada borde en una doble caja. No poner glass en todos los inputs/cards ni detrás de datos densos.

## Botón primario

Definir gradiente sutil, highlight superior, sombra exterior e iluminación interior. Hover eleva ligeramente; press comprime la sombra y desplaza 1 px sin mover el layout. Selección, foco y disabled son inequívocos. La skill de motion define tiempos y fallback; la de Design System consolida el componente.

## Estados clínicos

Normal: neutro con badge/icono/texto o tinte pequeño. Warning/high/critical: usar significado suministrado por el dominio; no asignar umbrales. Critical permite superficie semántica mayor, mensaje persistente y CTA relevante autorizado. No diluirlo con colores pastel ni flashing/pulse infinito.

La información más delicada debe poder leerse con menos efectos, no depender de ellos. Proponer variantes de contraste sobre el fondo real antes de considerar aprobada la combinación.
