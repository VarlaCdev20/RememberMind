---
name: remembermind-domain-architect
description: "Diseñar casos de uso y responsabilidades Laravel de RememberMind: Policy, Action, Service, Models, entrada, transacciones y eventos. Usar para flujos nuevos o responsabilidades mal ubicadas; no para elegir estilo visual ni imponer otra arquitectura."
---

# Purpose

Ubicar comportamiento institucional en capas existentes y hacer explícito su contrato.

# Use when

Nueva operación, coordinación multientidad, lógica crítica en Livewire/controlador, extracción o límites de un módulo.

# Do not use when

CRUD por plantilla, interfaces para cada clase, CQRS/Repository/arquitectura alternativa sin necesidad aprobada; coordinación completa de entrega corresponde a module-delivery.

# Mandatory sources

[Arquitectura vigente](../../../docs/arquitectura/README.md), [app/AGENTS](../../../app/AGENTS.md), [mapa funcional](../../../docs/sistema/MAPA_DOMINIO_FUNCIONAL.md), contrato vigente; composer.lock y package-lock.json para versiones.

# Domain assumptions

Controllers delgados; Livewire maneja interacción; Requests forma de entrada; Policy autorización; Action operación explícita/transaccional; Service capacidad reutilizable; Models relación/casts/invariante local. Reutilizar antes de crear.

# Workflow

1. Mapear llamadores, consumidores, entidades y variantes del caso de uso.
2. Definir actor, entrada, estados, pre/postcondiciones y efectos permitidos/prohibidos.
3. Emitir `DOMAIN DESIGN`: Caso de uso, Actor, Entrada, Policy, Action, Service, Models, Transaction boundary, Events, Tests. Marcar no aplica con motivo, sin clases vacías.
4. Mantener backend en app/Backend/Modulos/<Area> y UI en app/Frontend/Livewire; namespaces/rutas PSR-4 coherentes.
5. Diseñar atomicidad y momento de efectos externos; no confundir eventos clínicos persistidos con eventos Laravel.
6. Elegir extracción mínima; si solo cambia ubicación usar safe-refactor con preservación de conducta.

# Invariants

Sin SQL/institucionalidad en Blade, dependencia Backend→Frontend/Http ni Models gigantes coordinando múltiples agregados. No duplicar lógica crítica en varias entradas. No crear raíces app/Livewire o app/Services.

# Failure conditions

God Service/Model, Action meramente wrapper sin propósito, regla crítica solo en UI, evento emitido como éxito antes del commit o ruta de propuesta histórica impuesta.

# Escalation rules

Aplicar system-guardrails para cambios de contrato. Resolver elecciones técnicas reversibles con evidencia local; investigar documentación oficial solo ante API incierta de versión instalada.

# Tests required

Contrato positivo/negativo, transacción y equivalencia entre entradas relevantes. Pruebas de arquitectura existentes para cambios de ubicación; no tests que reflejen solo estructura privada.

# Definition of Done

DOMAIN DESIGN vinculable a implementación y pruebas, responsabilidades claras, dependencias canónicas y ausencia de una arquitectura paralela.
