---
name: remembermind-ui-review
description: "Seleccionar y coordinar las etapas pertinentes del stack UX/UI de RememberMind para una pantalla o proceso que combina varias especialidades. Usar como entrada para diseño, refactor o revisión integral; para una preocupación puntual cargar directamente su skill especializada."
---

# Propósito

Ser el punto de entrada del stack, seleccionando responsabilidades sin duplicar
sus instrucciones. El recorrido monolítico anterior se sustituye por etapas.

# Cuándo usar

Diseño, refactor o revisión de una pantalla/proceso de RememberMind que combina
flujo, estética, componentes y comportamiento.

# Cuándo NO usar

Para una preocupación puntual, cargar directamente su especialidad. Backend,
schema y reglas institucionales no se resuelven desde este selector.

# Fuentes que debe consultar

Leer [pipeline y ejemplos](../../../docs/frontend/STACK_SKILLS_UX_UI.md),
[contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) y
`resources/AGENTS.md`. Inspeccionar la superficie actual y referencia aprobada.

# Workflow obligatorio

1. Identificar superficie, rol, tarea y alcance autorizado.
2. Seleccionar etapas del pipeline en su orden. Marcar omisiones relevantes
   con motivo; no cargar diez skills para una corrección puntual.
3. Anunciar skills utilizadas y entregar UX FLOW/REFERENCE ANALYSIS antes de
   diseñar cuando correspondan. La imagen aprobada prevalece sobre gustos.
4. Transferir salidas entre especialidades: flujo, fidelidad, arte, componentes,
   captura clínica/datos si aplican, motion, acceso y copy.
5. Usar auxiliares solo para dudas concretas. Verificar sus recursos; no
   instalar otra arquitectura ni generar otro Design System.
6. Pasar la superficie implementada a visual-functional-qa para dictamen.
   Las correcciones pertenecen a las otras etapas; QA no edita producto.

# Reglas no negociables

Aplicar límites de BDD, permisos, terminología y UX GAP DETECTADO del contrato.
No inventar gravedad clínica, datos o operaciones. Conservar alertas persistentes.

# Criterios UX

Cada preocupación tiene una especialidad responsable; el recorrido conserva
contexto del residente, recuperación de errores y siguiente acción.

# Errores frecuentes que debe evitar

Cargar todo el stack sin necesidad, duplicar checklist de cada especialidad,
tratar una heurística como autoridad institucional o dar PASS sin evidencia.

# Definition of Done

Etapas pertinentes ejecutadas, salidas conectadas y verificación declarada.
Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN. Tests/build pertinentes
complementan la revisión visual cuando se modifica producto.
