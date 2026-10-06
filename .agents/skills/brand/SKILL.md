---
name: brand
description: "Revisar identidad, tono y coherencia de marca en comunicación institucional o assets solicitados de RememberMind. No definir copy clínico, reglas ni otra paleta de UI; usar remembermind-ux-writing y la dirección artística especializada en pantallas."
metadata:
  original-argument-hint: "[update|review|create] [args]"
  author: claudekit
  version: "1.0.0"
---

# Brand

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

Brand identity, voice, messaging, asset management, and consistency frameworks.

## When to Use

- Brand voice definition and content tone guidance
- Visual identity standards and style guide development
- Messaging framework creation
- Brand consistency review and audit
- Asset organization, naming, and approval
- Color palette management and typography specs

## Quick Start

**Inject brand context into prompts:**
```bash
node scripts/inject-brand-context.cjs
node scripts/inject-brand-context.cjs --json
```

**Validate an asset:**
```bash
node scripts/validate-asset.cjs <asset-path>
```

**Extract/compare colors:**
```bash
node scripts/extract-colors.cjs --palette
node scripts/extract-colors.cjs <image-path>
```

## Brand Sync Workflow

```bash
# 1. Edit docs/brand-guidelines.md (or use /brand update)
# 2. Sync to design tokens
node scripts/sync-brand-to-tokens.cjs
# 3. Verify
node scripts/inject-brand-context.cjs --json | head -20
```

**Files synced:**
- `docs/brand-guidelines.md` → Source of truth
- `assets/design-tokens.json` → Token definitions
- `assets/design-tokens.css` → CSS variables

## Subcommands

| Subcommand | Description | Reference |
|------------|-------------|-----------|
| `update` | Update brand identity and sync to all design systems | `references/update.md` |

## References

| Topic | File |
|-------|------|
| Voice Framework | `references/voice-framework.md` |
| Visual Identity | `references/visual-identity.md` |
| Messaging | `references/messaging-framework.md` |
| Consistency | `references/consistency-checklist.md` |
| Guidelines Template | `references/brand-guideline-template.md` |
| Asset Organization | `references/asset-organization.md` |
| Color Management | `references/color-palette-management.md` |
| Typography | `references/typography-specifications.md` |
| Logo Usage | `references/logo-usage-rules.md` |
| Approval Checklist | `references/approval-checklist.md` |

## Scripts

| Script | Purpose |
|--------|---------|
| `scripts/inject-brand-context.cjs` | Extract brand context for prompt injection |
| `scripts/sync-brand-to-tokens.cjs` | Sync brand-guidelines.md → design-tokens.json/css |
| `scripts/validate-asset.cjs` | Validate asset naming, size, format |
| `scripts/extract-colors.cjs` | Extract and compare colors against palette |

## Templates

| Template | Purpose |
|----------|---------|
| `templates/brand-guidelines-starter.md` | Complete starter template for new brands |

## Routing

1. Parse subcommand from `$ARGUMENTS` (first word)
2. Load corresponding `references/{subcommand}.md`
3. Execute with remaining arguments
