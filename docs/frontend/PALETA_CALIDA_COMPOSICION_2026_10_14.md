# Paleta cálida y composición — 14/10/2026

## Dirección vigente de la propietaria

Sustituye las combinaciones anteriores de este lote: sensación acogedora, café grisáceo suave con trama, cards más claras que el fondo, sin dominante amarilla, sin bisel. Se adapta la paleta entregada por la propietaria mediante sus aclaraciones posteriores.

| Función | Token canónico | Valor final |
|---|---|---|
| Lienzo café grisáceo | `--rm-palette-page` | `#D8D2CD` |
| Card greige clara | `--rm-palette-surface` | `#ECE9E6` |
| Apoyo interior | `--rm-palette-surface-soft` | `#DFD9D4` |
| Ventana elevada | `--rm-palette-surface-elevated` | `#F0EDEA` |
| Campo | `--rm-palette-input` | `#E7E2DE` |
| Tinta principal | `--rm-palette-ink` | `#4F4742` |
| Acción salvia | `--rm-palette-primary` | `#7D9282` |
| Hover | `--rm-palette-primary-hover` | `#5F7567` |
| Información | `--rm-palette-info` | `#7D94A8` |
| Advertencia / crítica | Tokens de estado existentes | `#C7A56B` / `#B95C55` |

Las cards translúcidas mezclan la superficie clara al 84% con el lienzo; la comprobación mide la superficie compuesta. La tinta secundaria pequeña utiliza el alias legible, más oscuro que el tono decorativo `#7D6F65`. Las alertas mantienen clasificación del backend: el ámbar no se utiliza como fondo ordinario. Se conserva el modo oscuro existente.

## Composición e integración

- Dashboard de Enfermería: bienvenida compacta, indicadores, alertas y agenda, controles, residentes y seguimiento. El DOM y el orden visual coinciden.
- Títulos de sección compartidos de 18 px / 700, ayudas de 14 px; acciones alineadas al extremo del encabezado y con reflow.
- Estados vacíos más breves; profundidad mayor en alertas/agenda que en información secundaria. Sin blur repetido en cada card del dashboard.
- Acentos salvia, azul y lavanda concentrados en función/encabezados. Sin bisel en botones ni desplazamiento decorativo de los bloques principales.
- Se excluye el dashboard compartido de una regla antigua de Enfermería que alteraba sus cards respecto a los demás roles.
- `priority-alert` oculta únicamente el prefijo técnico de vínculo de herida al presentar la descripción. No modifica el texto persistido ni el vínculo backend.
- Los formularios y directorios consumen los mismos tokens de superficie. No cambian BDD, responsabilidades profesionales, validación clínica ni permisos.

## Verificación

- PHP relacionado: **19 PASS / 135 assertions**, Dashboard de Enfermería, encabezado y complementos.
- JS: **194 PASS** durante la integración. Tras los últimos ajustes de tonos y alineación: **5 pruebas visuales de shell/sidebar PASS**.
- Build final: **PASS**; permanece el aviso general de bundle mayor de 500 kB.
- Dashboard sintético renderizado: **1440, 1280, 1024, 768 y 390 px**, sin overflow ni errores JS; contraste muestreado mínimo de 4.5:1 y cards compuestas más claras que el lienzo.
- Regresión de consultas: **60 casos PASS**, 12 capturas en cinco tamaños, sin errores JS. El último ajuste solo cambia tonos neutrales.

La evidencia usa Blade, CSS/JS reales y datos sintéticos en testing con transporte Livewire simulado. No acredita una operación clínica desde el navegador ni una certificación global de accesibilidad. La presentación compartida y el alcance de rol están cubiertos por los tests indicados.

Evidencias locales ignoradas por Git: `storage/app/qa/composicion-calida/`. Sin commit/push en este lote; se conserva el trabajo previo mezclado del checkout.

## Ajuste posterior: distribución y tipografía

La propietaria aprueba la paleta anterior y pide corregir distribución y texto. Se conserva la paleta y se ajusta el componente compartido de indicadores a cifras de 32 px, rótulos de 16 px y descripciones de 13 px. Los títulos de sección conservan 18 px / 700 y utilizan tinta salvia contrastada; los datos conservan tinta café. Las ayudas de encabezado usan 13 px y mayor separación respecto al título.

Los encabezados utilizan dos columnas flexibles para alinear sus acciones. Alertas y agenda igualan la altura dentro de su fila; el dashboard gana separación y padding consistente. A partir de 1200 px, Controles presenta contexto y acciones en una barra compacta; en móvil conserva captura de ancho completo y botones con reflow. Las filas de residentes ganan espacio vertical y nombres de 15 px.

Verificación de este ajuste CSS: build PASS, cuatro pruebas de consistencia visual entre roles PASS y dashboard sintético en 1440/1280/1024/768/390 px PASS, sin overflow ni errores JS y con contraste muestreado mínimo de 4.5:1. No se modifica backend ni se atribuye una nueva ejecución PHP. Evidencia en `storage/app/qa/dashboard-tipografia/`.
