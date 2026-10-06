# Mapa de microinteracciones

Por interacción registrar trigger, estado inicial/final, mensaje que comunica, propiedades animadas, token/rango, foco y fallback reducido. Aplicar rangos instant/fast/standard/panel/layout del contrato común; no repetir una escala alternativa aquí.

| Elemento | Transición funcional |
|---|---|
| Botón | Hover: elevación leve/highlight. Press: translateY(1px) y compresión de sombra. Release: recuperación suave |
| Card interactiva | Elevación aproximada de 2 px y borde suave; la card informativa no finge ser clicable |
| Input | Foco: borde, ring y énfasis de label sin mover captura |
| Modal | Backdrop fade + translateY 6px→0 + scale .985→1, dentro del rango de panel |
| Drawer | Dirección coherente con su aparición y foco administrado |
| Toast | Fade y slide corto, sin robar foco |
| Tabs | Indicador activo en transición; contenido y estado accesible actualizados |
| Loading | Skeleton para estructura predecible; indicador discreto para acción localizada |
| Critical | Badge, resumen, CTA y panel actualizados inmediatamente; animación opcional, nunca retrasa la alerta |

No convertir los valores ilustrativos en CSS inline repetido. Resolver mediante tokens/componentes comunes. Shadow y highlight deben conservar contraste y rendimiento.

## Interrupción y reduced motion

Cambios rápidos cancelan o reemplazan transiciones y establecen el último estado real. Livewire puede recrear nodos: revisar cleanup y evitar listeners duplicados. El estado final no depende de eventos de animación.

Con prefers-reduced-motion, eliminar desplazamientos/escala y efectos no necesarios; conservar estado/texto, foco y feedback inmediato. No usar reducción de movimiento para ocultar loading. Probar cerrar/reabrir overlay, cambiar tabs rápido y recibir una alerta durante transición.

Para una referencia estática, declarar que la coreografía es una inferencia funcional del contrato. Si la referencia incluye comportamiento aprobado, conservarlo dentro de límites de seguridad/accesibilidad.
