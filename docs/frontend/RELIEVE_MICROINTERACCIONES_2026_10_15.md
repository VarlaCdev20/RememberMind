# Relieve y microinteracciones compartidas — 15/10/2026

## Implementado

Las tarjetas, paneles, encabezados, filtros y secciones clínicas reutilizan la elevación cálida de `--rm-shadow-card`. Se consolidan los aliases de captura y relieve y se retiran los overrides del dashboard que anulaban las sombras. Los diálogos conservan su nivel de elevación superior. La paleta, las transparencias existentes, las dimensiones y la semántica de estados se mantienen.

No se añade bisel, degradado, librería ni blur por tarjeta. Los paneles informativos permanecen quietos al pasar el puntero; el movimiento leve identifica controles o tarjetas con acción.

## Mapa de interacción

| Trigger | Feedback | Duración | Foco / movimiento reducido |
| --- | --- | --- | --- |
| Hover de acción | Elevación de 2 px; botones con escala 1.015 y sombra visible; flecha avanza 2 px | `--rm-duration-fast` / `--rm-duration-normal` | Solo puntero fino; sin desplazamiento en reduce |
| Pulsar botón/enlace de acción | Compresión de sombra, desplazamiento de 1 px y escala .985 | `--rm-duration-fast` | No afecta el layout ni espera `animationend` |
| Teclado en acción | Contorno visible con separación | Inmediato | Permanece con reduce |
| Foco dentro de captura | Elevación contextual, ring del campo existente | `--rm-duration-normal` | Foco estable; no se mueve la captura |
| Deshabilitado / ocupado | Conserva estado y bloqueo existentes | Inmediato | Sin movimiento de pulsación |
| Tarjeta informativa | Relieve permanente | Sin desplazamiento | No simula una acción |

Las transiciones CSS se reemplazan al cambiar rápidamente de estado; no se agregan listeners ni se altera el ciclo de Livewire.

Refuerzo solicitado posteriormente: las filas enlazadas de residentes y ubicaciones usan el tinte de acción al pasar el puntero. Las cards con acción incorporan transición de transform para evitar saltos; las informativas conservan su posición. Las pruebas verifican elevación de 2 px y compresión perceptible, además del fallback reducido.

## Verificación

- `npm run build`: PASS. Persiste la advertencia previa de bundle JS superior a 500 kB.
- `node --test tests/Frontend/surface-depth-motion.test.js tests/Frontend/shell-visual-consistency.test.js`: 5 PASS. La prueba nueva cubre cinco anchos × dos temas × dos preferencias de movimiento, hover, pulsación, deshabilitado, foco, ausencia de overflow y propagación real del token al dashboard.
- Fixture Blade con componentes reales y datos sintéticos: 1 PASS. Revisado visualmente a 1440, 1280, 1024, 768 y 390 CSS px, sin texto recortado ni scroll horizontal. Campo con label/foco visible; detalle abre con Enter.
- Dashboard conectado recargado: sus 14 cards consumen la misma sombra compartida y no presentan overflow horizontal en el ancho actual.
- Evidencias aisladas: `storage/app/qa/relieve-compartido/`. El fixture no persiste datos.

La verificación cubre los componentes compartidos afectados y la integración en Mi turno; no constituye auditoría visual de cada pantalla del sistema. No se modifican datos, permisos, reglas ni backend. No se agrega legacy.

## Git

Los archivos de estilos ya contienen trabajo previo, incluido un patrón aún sin commit. Se conservan los cambios anteriores y no se crea un commit mezclado ni se realiza push.
