# Matriz de adaptación y acceso

| Ancho CSS px | Comportamiento a verificar |
|---|---|
| 1440 | Sidebar, workspace y panel contextual con jerarquía y ancho legible |
| 1280 | Mismo flujo, columnas/espacios adaptados sin encoger letras para encajar |
| 1024 | Sidebar compacta/drawer si hace falta; panel secundario según espacio real |
| 768 | Dos columnas solo si son legibles; contexto secundario pasa debajo |
| 390 | Una columna, header compacto, navegación drawer/bottom cuando corresponda, cards apiladas y charts adaptados |

Son viewports de aceptación, no nuevos breakpoints CSS obligatorios. Usar los breakpoints compartidos del proyecto y comprobar también anchos intermedios si aparece un fallo.

No scroll horizontal en página ni componentes aceptados por este brief. Tablas densas: priorizar columnas, disclosure o tarjetas según tarea, con acceso al detalle completo. No ocultar comparación indispensable ni usar overflow oculto como arreglo. Si el contrato y la referencia impiden una adaptación usable, documentar UX GAP sin modificar dominio.

## Escenarios de acceso

- Tab/Shift+Tab recorren orden lógico; Enter/Space activan controles según semántica.
- Modal/drawer gestiona entrada, confinamiento apropiado, Escape/captura pendiente y retorno de foco.
- Cada label visible se asocia a control; error/hint con aria-describedby cuando corresponda; aria-invalid refleja validación.
- Iconos decorativos junto a texto se ocultan al lector; controles de icono tienen nombre y estado accesible.
- Estados clínicos: icono + texto + color. Normal/Advertencia/Alto/Crítico solo si el dominio los suministra.
- Touch aproximado mínimo 44 × 44 CSS px, incluso si el icono visible es menor; no confundir px con pt/dp nativos.
- Contraste: criterio de proyecto AA, medir texto normal 4.5:1 y texto grande/elementos significativos 3:1 cuando aplique, sobre superficies compuestas. No prometer certificación global.
- Zoom/texto ampliado, contenido largo, datos ausentes y muchos badges no rompen grid ni tapan acciones.
- Reduced motion mantiene feedback; modo oscuro se verifica aparte si la superficie lo soporta.

## Evidencia

Registrar viewport, tema, estado/datos sintéticos, método de inspección, resultado y archivo de captura si existe. Revisar foco no oculto por header/CTA sticky y campos al abrir teclado móvil. Una captura prueba apariencia; el recorrido de teclado prueba interacción. Ninguno sustituye al otro.
