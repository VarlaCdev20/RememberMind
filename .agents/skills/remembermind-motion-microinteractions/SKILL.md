---
name: remembermind-motion-microinteractions
description: "Especificar o implementar microinteracciones funcionales de RememberMind mediante tokens: feedback, hover/press, cambios de estado, modales, drawers, tabs y loading, con movimiento reducido. Usar cuando hay transición o falta de feedback; no para animación decorativa ni recalcular severidad clínica."
---

# Propósito

Comunicar estado y causalidad con movimiento breve, coherente y funcional, manteniendo control y lectura inmediata.

# Cuándo usar

Feedback de botones/campos, expansión contextual, overlays, tabs, toasts, carga y cambios de estado autorizados.

# Cuándo NO usar

Animación decorativa, parallax de una escena clínica, instalación de otra librería por preferencia o cambios de severidad.

# Fuentes que debe consultar

Leer el [contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) al iniciar una tarea de esta especialidad; reutilizarlo si ya se leyó. El [pipeline](../../../docs/frontend/STACK_SKILLS_UX_UI.md) determina orden y selección, sin cargar etapas ajenas.

Contrato compartido, tokens/motion.css, componentes afectados y recursos de movimiento ya instalados. Una imagen estática solo sugiere motion; no demuestra timing ni coreografía.

# Workflow obligatorio

1. Inventariar interacciones afectadas y qué comunica cada transición: estado, causalidad, jerarquía, dirección, feedback o relación espacial.
2. Crear [el mapa de transiciones](references/motion-map.md) con trigger, estados, propiedades, timing token, foco y fallback reduced motion.
3. Comparar tiempos actuales con rangos del brief; consolidar tokens en el alcance autorizado. Preferir CSS/Alpine existentes, sin nuevos plugins innecesarios.
4. Implementar sin bloquear interacción, cambiar bounds de layout ni depender de animationend para completar una operación. Manejar interrupciones y cambios rápidos.
5. Verificar teclado/touch, contenido largo, carga, crítico y movimiento reducido; registrar evidencia y anomalías para QA.

# Reglas no negociables

Aplicar gobernanza de BDD, terminología, autoridad visual y UX GAP DETECTADO del contrato común. Referencia visual aprobada conserva relieve y geometría. Cada animación tiene propósito. Usar tokens y los límites del contrato; no flashing, shake agresivo ni pulse infinito en estados críticos.

# Criterios UX

Feedback rápido, entradas discretas, foco estable y resultado visible con movimiento reducido. No animar cientos de filas ni ocultar información detrás de efectos.

# Errores frecuentes que debe evitar

Un timing para todo; spinners gigantes; skeleton en estructura impredecible; duplicar transiciones al morph; mover un botón fuera del cursor; hacer esperar a que termine una animación para guardar o mostrar una alerta.

# Definition of Done

Mapa cubre estados, triggers, tokens e interrupción. Revisión normal/reduced motion demuestra legibilidad y respuesta; el movimiento mejora comprensión y no determina estado de negocio.

Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN según el alcance; distinguir lo comprobado de lo pendiente.
