---
name: slides
description: "Crear presentaciones HTML solicitadas con estructura narrativa y datos verificables. Auxiliar de comunicación de RememberMind; no usar para dashboards, charts clínicos ni componentes del producto. Comprobar referencias disponibles antes de seguir subcomandos."
metadata:
  original-argument-hint: "[topic] [slide-count]"
  author: claudekit
  version: "1.0.0"
---

# Slides

## Ámbito en RememberMind

Esta entrada es auxiliar. Para UI operativa seguir el
[pipeline especializado](../../../docs/frontend/STACK_SKILLS_UX_UI.md) y el
[contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md).
Imagen aprobada: analizar antes de editar, conservar estructura, proporciones,
densidad, profundidad y glass; adaptar contenido, permisos, reglas clínicas,
datos, responsive y accesibilidad necesaria. No sustituirla por gustos del agente.
No cambiar schema, relaciones, terminología ni severidad desde UX.

Este alcance prevalece sobre el material genérico conservado abajo. La copia
local incluye solo SKILL.md: referencias/scripts citados no están incluidos.
Comprobar existencia y herramientas antes de ejecutar; usar capacidades
disponibles sin instalar servicios ni inventar resultados.

Strategic HTML presentation design with data visualization.

## When to Use

- Marketing presentations and pitch decks
- Data-driven slides with Chart.js
- Strategic slide design with layout patterns
- Copywriting-optimized presentation content

## Subcommands

| Subcommand | Description | Reference |
|------------|-------------|-----------|
| `create` | Create strategic presentation slides | `references/create.md` |

## References (Knowledge Base)

| Topic | File |
|-------|------|
| Layout Patterns | `references/layout-patterns.md` |
| HTML Template | `references/html-template.md` |
| Copywriting Formulas | `references/copywriting-formulas.md` |
| Slide Strategies | `references/slide-strategies.md` |

## Routing

1. Parse subcommand from `$ARGUMENTS` (first word)
2. Load corresponding `references/{subcommand}.md`
3. Execute with remaining arguments
