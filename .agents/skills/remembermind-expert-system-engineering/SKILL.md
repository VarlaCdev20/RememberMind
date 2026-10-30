---
name: remembermind-expert-system-engineering
description: "Diseñar conocimiento, inferencia multicriterio y explicación del futuro sistema experto cognitivo de RememberMind mediante fases Buchanan. Usar en formalización o integración aprobada; no convierte propuestas en protocolo, diagnostica ni elige pesos/umbrales clínicos."
---

**Orden de trabajo adoptado el 30/10/2026:** completar primero el funcionamiento integral y sus pruebas técnicas. Durante todo el desarrollo no solicitar estudios con residentes, firmas, referencias humanas ni avales externos como condición para programar, probar o integrar. La evaluación posterior en uso real queda fuera de esta etapa. Conservar reglas aprobadas, datos originales, permisos, trazabilidad y abstención por datos insuficientes; no inventar resultados.

# Purpose

Separar ingeniería del conocimiento, operación residencial y decisión profesional.

# Use when

Identificación/conceptualización/formalización/implementación del apoyo cognitivo, contratos de hechos/reglas, explicación o integración propuesta.

# Do not use when

Rebautizar signos vitales como experto cognitivo, diagnóstico/tratamiento autónomo, copiar reactivos protegidos, imponer AHP/Mamdani/Python o ejecutar reglas clínicas no aprobadas.

# Mandatory sources

[docs experto](../../../docs/sistema-experto/README.md) y propuestas allí con estado explícito, source-of-truth, arquitectura vigente, baseline/decisiones y [contratos de conocimiento](references/knowledge-contract.md).

# Domain assumptions

El motor PHP institucional ya existe, con reglas, cinco dominios y clasificación orientativa de Petersen. Resolver el estado efectivo desde fuentes vigentes; el circuito instrumental y la conservación conjunta mantienen sus pendientes técnicos. El enfoque Buchanan organiza el trabajo y no exige avales externos durante el desarrollo ni autoriza a inventar mediciones o reglas.

# Workflow

1. Identificación: problema, población, usuarios, decisión apoyada, límites/responsables y aceptación.
2. Conceptualización: fuentes → hechos → conocimiento → criterios/variables/relaciones; registrar calidad, ausencia y fecha.
3. Formalización: normalización, pesos, reglas, agregación, inferencia y conflicto conforme método aprobado. Red semántica solo si contrato vigente la requiere; no impuesto por historia.
4. Implementación: separar adquisición, conocimiento, motor, explicación e integración operacional usando módulos existentes; no if/else médicos repartidos en Livewire/servicios.
5. Explicación: procedencia/fecha/versión, contribuciones y reglas activas, faltantes, resultado/riesgo/recomendación según método. Si un método no produce score global, no fabricarlo.
6. Mantener salida revisable por profesional; no alterar tratamientos ni crear alerta por inferencia sin contrato de integración aprobado.
7. Prueba: entregar reglas y casos esperados versionados a expert-validation, además de pruebas operativas system-testing.

# Invariants

Dato ≠ hecho ≠ criterio ≠ peso ≠ umbral ≠ regla ≠ resultado ≠ explicación. No doble conteo de evidencia, aprobaciones ficticias, nuevas tablas o algoritmo histórico convertido en obligación.

# Failure conditions

Fuente/regla sin versión/responsable, ausencia considerada normalidad, score/riesgo inventado, explicación no reconstruible, instrumento sin derecho/metodología o resultado autónomo clínico.

# Escalation rules

Delimitar las decisiones institucionales, permisos y estructura todavía no autorizados según AGENTS. No pedir firmas, estudios con residentes ni referencias humanas para desarrollar. Continuar implementación, documentación y pruebas técnicas; la evaluación en uso real corresponde a una etapa posterior al funcionamiento integral.

# Tests required

Determinismo, falta/dato inválido, límites y conflictos según método, contribuciones/explicación reconstruibles, versión/fecha de corte y aislamiento operativo. Sin reactivos protegidos ni afirmar evaluación posterior en uso real desde tests técnicos.

# Definition of Done

Artefactos Buchanan y contratos explícitos, fuentes/decisiones rastreables, explicación reproducible y límites; distinguir diseñado/implementado/verificado/evaluado posteriormente en uso real.
