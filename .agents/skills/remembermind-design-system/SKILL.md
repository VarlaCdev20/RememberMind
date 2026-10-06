---
name: remembermind-design-system
description: "Traducir una dirección o referencia aprobada de RememberMind a tokens y componentes Blade/CSS compartidos, localizando equivalentes y consumidores antes de crear o modificar. Usar para fundamentos, variantes, geometría repetida o cambios maestros; no para elegir otra estética ni rediseñar el dominio."
---

# Propósito

Convertir lenguaje visual aprobado en contratos compartidos de presentación, manteniendo una única implementación de geometría, variantes y estados.

# Cuándo usar

Foundations, botones, campos, cards, panels, overlays, tablas, feedback o composiciones clínicas reutilizables; cambios a un componente maestro.

# Cuándo NO usar

Exploración de otra estética, creación de un catálogo entero sin uso, migración de framework o cambios de schema para acomodar componentes.

# Fuentes que debe consultar

Leer el [contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) al iniciar una tarea de esta especialidad; reutilizarlo si ya se leyó. El [pipeline](../../../docs/frontend/STACK_SKILLS_UX_UI.md) determina orden y selección, sin cargar etapas ajenas.

Inspeccionar resources/frontend/styles/design-system/, resources/views/components/, tailwind.config.js, entradas CSS/JS y consumidores reales. Leer package-lock/composer.lock antes de usar APIs inciertas.

# Workflow obligatorio

1. Localizar tokens, componentes y patterns equivalentes. Comparar responsabilidad, API, datos y estados antes de proponer archivos.
2. Mapear conceptos Rm*/Clinical* a [los equivalentes y brechas](references/component-contracts.md). Elegir reutilizar, extender o crear con justificación.
3. Definir foundations: colors, typography, spacing, radius, shadow/depth, glass, motion y breakpoints como primitivo → semántico → componente. Diferenciar objetivos aprobados y código actual.
4. Para un cambio maestro identificar vistas afectadas, props/slots/variantes, consumidores y overrides. Propagar por el componente compartido; evitar geometría paralela por pantalla.
5. Implementar solo lo requerido, en Blade/Livewire/CSS del proyecto, cuando esté autorizado. Revisar consumidores, pruebas pertinentes y build; delegar aceptación visual a QA.

# Reglas no negociables

Aplicar gobernanza de BDD, terminología, autoridad visual y UX GAP DETECTADO del contrato común. Referencia aprobada gobierna apariencia. UX no modifica BDD ni permite SQL/lógica clínica en componentes. Compartir geometría no comparte permisos/datos. No introducir React/shadcn/Tailwind 4 ni una segunda paleta por copiar auxiliares.

# Criterios UX

Misma función implica mismo contrato visual. Componentes incluyen hover/focus/press/disabled/loading/error donde aplican y funcionan con contenido largo y temas existentes.

# Errores frecuentes que debe evitar

Crear RmButton si action-button ya resuelve el caso; botón distinto en Signos/Dolor/Movilidad; override local que rompe propagación; copiar HEX/sombras/duraciones por vista; asumir que un componente existe solo porque aparece en el catálogo.

# Definition of Done

Mapa de reutilización y alcance de consumidores comprobados. Tokens/API/estados documentados; implementación proporcional con regresiones pertinentes y build verificados si se modificó producto. Sin geometrías duplicadas injustificadas.

Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN según el alcance; distinguir lo comprobado de lo pendiente.
