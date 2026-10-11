---
name: remembermind-system-guardrails
description: "Detectar y aislar cambios que romperían contratos institucionales, BDD congelada, autorización o historia clínica de RememberMind. Usar ante un DOMAIN GAP o decisiones transversales; no sustituye diseño de dominio ni gates finales."
---

# Purpose

Mantener los límites institucionales mientras se implementa trabajo autorizado.

# Use when

Diseño transversal, carencia del modelo, atajos de persistencia, cambios de responsabilidad, transición crítica o revisión de una propuesta técnica.

# Do not use when

Redefinir UX, conceder permisos, aprobar reglas clínicas o convertir toda elección reversible en consulta a la propietaria.

# Mandatory sources

[AGENTS raíz](../../../AGENTS.md), AGENTS del ámbito, [mapa funcional](../../../docs/sistema/MAPA_DOMINIO_FUNCIONAL.md), contrato resuelto con source-of-truth, baseline/diccionario/decisiones pertinentes.

# Domain assumptions

Autonomía técnica decide cómo implementar; la institución decide reglas, competencias y estructura reservadas. Los huecos se documentan sin inventar reemplazos.

# Workflow

1. Identificar operación, entidades, actores y contrato vigente.
2. Contrastar invariantes y localizar el hueco exacto, sin ampliar a refactor global.
3. Si existe `DOMAIN GAP`, emitir Problema, Contrato vigente, Impacto, Alternativas, Cambio estructural necesario: SÍ/NO y Requiere decisión: SÍ para decisiones reservadas. Para una corrección técnica resuelta registrar que no requiere decisión nueva.
4. Separar corrección técnica compatible de cambio institucional/estructural.
5. Implementar lo compatible dentro del encargo; aislar dependencias bloqueadas y elevar opciones concretas.

# Invariants

BDD congelada y extensiones aprobadas; sin campos/relaciones ficticios, JSON/EAV o FK reemplazadas por texto. Aprobación ≠ admisión; residente solo en admisión formal. Backend autoriza; historial/autoría se preservan. Superadmin no recibe competencia clínica por rol. Ningún fallback crea entidades o selecciona la primera FK para fingir éxito.

# Failure conditions

Modificar catálogo/estructura sin aprobación; borrado clínico ordinario; éxito falso; atribución manipulada; bypass crítico; solicitud de permiso por una decisión técnica ya autorizada.

# Escalation rules

Explicar hueco, impacto y alternativas antes de pedir cambio reservado. No ejecutar destrucción de datos ni cambios irreversibles por inferencia de un pedido genérico.

# Tests required

Pruebas proporcionales de los invariantes afectados según system-testing; negativa que demuestre ausencia de efectos. Una revisión documental puede limitarse a escenarios de guardrails.

# Definition of Done

Ningún atajo viola el contrato; decisiones reservadas visibles, dependencias aisladas y correcciones técnicas autorizadas verificadas.
