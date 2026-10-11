---
name: remembermind-reference-fidelity
description: "Analizar y reproducir una imagen visual aprobada de RememberMind, conservando layout, proporciones, geometría, densidad, tipografía, profundidad y glass. Usar antes de editar una pantalla con referencia; no para explorar libremente una estética sin imagen aprobada."
---

# Propósito

Convertir una imagen aprobada en un contrato visual verificable y conservar su identidad al implementarla.

# Cuándo usar

Pantalla, modal, dashboard, directorio, header o componente con referencia visual aprobada adjunta o localizada.

# Cuándo NO usar

Exploración sin referencia aprobada, interpretación clínica de una imagen o invención de imágenes faltantes.

# Fuentes que debe consultar

Leer el [contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) al iniciar una tarea de esta especialidad; reutilizarlo si ya se leyó. El [pipeline](../../../docs/frontend/STACK_SKILLS_UX_UI.md) determina orden y selección, sin cargar etapas ajenas.

Ver la imagen con herramientas disponibles; inspeccionar pantalla actual, dimensiones, tokens y componentes. Una descripción textual de una imagen que no se pudo abrir no equivale a haberla analizado.

# Workflow obligatorio

1. Identificar referencia, aprobación, pantalla objetivo y dimensiones disponibles. Si el archivo requerido falta, solicitarlo por el canal de conversación y continuar únicamente trabajo independiente.
2. Medir grid, proporciones, ancho de columnas, alturas, spacing, alineación, densidad y jerarquía. Marcar mediciones aproximadas; no fingir precisión de un archivo sin escala.
3. Analizar tipografía, tamaños/pesos, radio, sombras, gradientes, glass, botones, cards, inputs, badges, iconografía y estados.
4. Antes de editar entregar REFERENCE ANALYSIS usando [la ficha de fidelidad](references/reference-analysis.md). Distinguir estructura exacta y adaptaciones justificadas de RememberMind.
5. Implementar en el stack y componentes existentes cuando el encargo lo incluya. Comparar a igual viewport, tema y estado; registrar diferencias residuales y su motivo.

# Reglas no negociables

Aplicar gobernanza de BDD, terminología, autoridad visual y UX GAP DETECTADO del contrato común. Imagen aprobada por encima de preferencia estética del agente. Mantener estructura visual; adaptar contenido, permisos, reglas clínicas, datos y responsive, con ajuste mínimo de accesibilidad cuando sea necesario. Explicar imposibilidad técnica concreta y alternativa más cercana.

# Criterios UX

La comparación evalúa composición y geometría, además de colores. No reemplazar identidad por una versión parecida o supuestamente más moderna.

# Errores frecuentes que debe evitar

Cambiar una grilla aprobada sin razón; adivinar la imagen; añadir cards/decoración; copiar datos personales; declarar fidelidad a partir de código sin captura comparable.

# Definition of Done

REFERENCE ANALYSIS existe antes de la edición; elementos esenciales coinciden y toda adaptación tiene motivo verificable. Evidencias de comparación disponibles o limitación de revisión claramente declarada.

Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN según el alcance; distinguir lo comprobado de lo pendiente.
