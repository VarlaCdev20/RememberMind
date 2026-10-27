---
title: "Stack tipográfico de RememberMind"
status: CURRENT
version: "1.3"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: AUTH_UI_AND_LOCAL_FONT_ASSETS
---

# Tipografía por función

La propietaria pidió ampliar la variedad tipográfica el 26/10/2026 manteniendo
una composición limpia. Esta actualización se aplica al acceso y recuperación;
no cambia automáticamente las fuentes de pantallas clínicas o administrativas.
La fuente de implementación sigue siendo el Design System y sus tokens.

| Función | Familia y tratamiento | Aplicación actual |
|---|---|---|
| Título redondeado | Plus Jakarta Sans 700; caja natural | Bienvenida y recuperación |
| Lema centrado en cursiva | Plus Jakarta Sans 700 italic auténtica | «Cuidado, memoria y dignidad.» completo, por corrección posterior de la propietaria |
| Etiqueta y acción | Plus Jakarta Sans 600 | Labels y botones |
| Lectura y captura | Inter regular | Campos, explicaciones, errores y ayuda |
| Cursiva editorial | Inter 400 italic auténtica | Una descripción lateral breve |
| Pincel disponible | Caveat Brush 400, token `--rm-font-handwritten` | Disponible en el sistema; retirado del lema de acceso por corrección posterior de la propietaria |
| Mayúsculas | Inter, `text-transform: uppercase`, tracking compartido | Identificación pequeña del centro |

Las cursivas y mayúsculas son tratamientos de texto; no requieren una familia
nueva por cada variante. La corrección posterior de la propietaria unifica
«Cuidado, memoria y dignidad.» en Plus Jakarta Sans 700, mismo tamaño, color
y estilo. La siguiente corrección centra el lema y aplica cursiva a toda la
frase, con el archivo local de Plus Jakarta Sans 700 italic. El acceso combina
ahora dos familias. Nunito Sans y Outfit siguen disponibles para sus consumidores
actuales, sin incorporarlas además al mismo formulario.

## Implementación

- Carga local: `resources/css/app.css`, paquetes `@fontsource` ya instalados.
- Contratos globales: `resources/frontend/styles/design-system/tokens/typography.css`.
- Composición del acceso: `resources/frontend/styles/design-system/patterns/auth-access.css`.
- Tarjeta compartida en acceso, solicitud y nueva contraseña:
  `resources/views/components/auth/access-card.blade.php`.
- `--rm-auth-font-heading` y `--rm-auth-font-body`
  acotan la selección al patrón de acceso.

Se añade el archivo local de Inter 400 italic que ya existe en la dependencia;
no se instala un paquete ni se solicita una fuente a un proveedor externo.
La escala permanece en tokens: título 28 px en escritorio y 25 px en móvil;
texto 14 px; campos al menos 16 px en móvil. El pincel ya no se aplica al lema.

No usar pincel o cursiva para contraseñas, datos clínicos, tablas, errores o
instrucciones esenciales. No aplicar mayúsculas a párrafos completos. Mantener
pesos reales disponibles y evitar imitar negritas/cursivas inexistentes.

## Evidencia

La prueba de navegador del acceso comprueba familias calculadas, carga local
efectiva de Plus Jakarta Sans, título uniforme, cursiva y mayúsculas, además de
responsive, foco y contraste acotado. Capturas y comandos en
[la entrega de acceso](ACCESO_Y_RECUPERACION_2026_10_26.md).
