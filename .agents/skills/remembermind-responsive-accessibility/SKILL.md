---
name: remembermind-responsive-accessibility
description: "Adaptar y verificar interfaces de RememberMind en 1440, 1280, 1024, 768 y 390 px, con teclado, foco, labels, touch, contraste, reflow y movimiento reducido. Usar para navegación, formularios, overlays, tablas y gráficas responsive o accesibles; no para redefinir dirección artística ni permisos."
---

# Propósito

Garantizar que la misma tarea pueda completarse en desktop, laptop, tablet y móvil mediante comportamiento adaptado y acceso por teclado/asistencia.

# Cuándo usar

Shell, sidebars, tablas/directorios, formularios, modales/drawers, charts y acciones sticky; defectos de overflow, foco o interacción.

# Cuándo NO usar

Nueva dirección artística, modificación de permisos o declaración de cumplimiento legal a partir de un checklist.

# Fuentes que debe consultar

Leer el [contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) al iniciar una tarea de esta especialidad; reutilizarlo si ya se leyó. El [pipeline](../../../docs/frontend/STACK_SKILLS_UX_UI.md) determina orden y selección, sin cargar etapas ajenas.

Contrato compartido, recursos/tokens de accesibilidad/layout/sizing, DOM real y patrones del componente. Para criterios normativos inciertos consultar documentación oficial vigente antes de atribuir nivel WCAG.

# Workflow obligatorio

1. Identificar tarea y contenido que deben permanecer accesibles, con referencia aprobada y fuentes de datos reales.
2. Definir comportamiento por ancho usando [la matriz de adaptación](references/responsive-matrix.md), conservando jerarquía y composición esenciales de la referencia.
3. Adaptar navegación, grid, panel secundario, acciones, modales/tablas y gráficas; evitar scroll horizontal y resolver long text antes de reducir tipografía.
4. Verificar teclado, foco visible/no oculto, labels, nombres accesibles, touch aproximado mínimo 44 × 44 CSS px, contraste, icono+texto+color y reduced motion.
5. Inspeccionar los cinco anchos en la interfaz renderizada, registrar evidencias y limitaciones; no inferir responsive solo de clases CSS.

# Reglas no negociables

Aplicar gobernanza de BDD, terminología, autoridad visual y UX GAP DETECTADO del contrato común. Reference-first con adaptación mínima de accesibilidad. No depender del hover/color. Reflow no oculta alertas clínicas ni acciones esenciales. Contraste se mide sobre el resultado real, no se presume por nombre de token.

# Criterios UX

Desktop ofrece workspace/contexto; tablet reorganiza paneles; móvil usa una columna, navegación accesible y acciones que no tapan contenido/foco. Labels y errores siguen unidos al control.

# Errores frecuentes que debe evitar

Resolver con flex-wrap únicamente; esconder overflow con overflow-x:hidden; truncar identidad/alerta; targets pequeños; focus ring eliminado; modal sin trap/retorno; barra sticky cubre teclado/campos; red/green sin texto.

# Definition of Done

Matriz de comportamiento y evidencias de 1440/1280/1024/768/390 disponibles. Teclado, foco, labels, contraste y touch revisados; no hay scroll horizontal ni pérdida de información esencial. Limitaciones reales declaradas.

Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN según el alcance; distinguir lo comprobado de lo pendiente.
