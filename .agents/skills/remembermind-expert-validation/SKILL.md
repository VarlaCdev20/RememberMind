---
name: remembermind-expert-validation
description: "Validar independientemente conocimiento, reglas, normalización, pesos, agregación, inferencia y explicaciones del experto de RememberMind contra metodología y casos aprobados. Usar para aceptar/rechazar un motor o cambio; no inventa muestra, métricas ni aprobación clínica."
---

# Purpose

Separar corrección técnica del experto de su validación clínica/metodológica.

# Use when

Revisión de base de conocimiento, reglas/modelo multicriterio, algoritmo, versión o explicación; comparación con referencia profesional.

# Do not use when

Diseñar o corregir el motor durante el gate, validar solo “ejecuta”, declarar sensibilidad/Kappa sin referencia o resolver incertidumbre inventando resultados.

# Mandatory sources

Metodología y casos aprobados, [contratos de conocimiento](../remembermind-expert-system-engineering/references/knowledge-contract.md), docs/sistema-experto con estado, reglas/versiones/code/tests y dictámenes profesionales disponibles.

# Domain assumptions

Ausencia de protocolo, muestra o aprobación clínica limita la conclusión. Verificación técnica satisfactoria no prueba validez clínica. Gate de lectura, separado del implementador.

# Workflow

1. Fijar versión, población/problema, referencia profesional, método, casos/dataset autorizado y criterios de aceptación aprobados.
2. Revisar completitud/consistencia: entradas faltantes, reglas imposibles, superpuestas/contradictorias, prioridades y exclusiones según contrato.
3. Verificar normalización/unidades, pesos, agregación, evidencia duplicada, sensibilidad y fronteras solo con propiedades definidas por método; no asumir monotonía universal.
4. Comparar esperado/actual y reconstruir reglas/contribuciones/explicación y limitaciones de cada caso.
5. Comparar con juicio profesional independiente cuando exista; desacuerdo no se oculta ajustando caso/umbral para coincidir.
6. Métricas de confusión, exactitud, sensibilidad/especificidad o Kappa únicamente si método/reference/datos y aceptación aprobados las justifican.
7. Emitir PASS/FAIL/BLOQUEADO por verificación técnica y validación clínica separadas, con casos, evidencia, límites y reparación propuesta; no editar en gate.

# Invariants

No números fabricados, sobreajuste de fixtures, causalidad clínica supuesta, prueba sintética llamada validación humana ni instrumentos protegidos copiados.

# Failure conditions

Conclusión sin referencia, versión o dataset; explicación incoherente; conflicto de reglas no resuelto por método; falta de derechos o umbrales aprobados.

# Escalation rules

Falta de metodología/derechos/referencia humana implica BLOQUEADO para aceptación clínica; sí puede continuar verificación técnica independiente y reportar su resultado.

# Tests required

Casos normales/frontera/faltantes/contradictorios según protocolo; reproducibilidad y sensibilidad documentada; comparación profesional/métricas solo cuando autorizadas. Reportar no ejecutado si aún no hay motor.

# Definition of Done

Resultado independiente por dimensión, conocimiento e inferencia trazables, límites visibles y aceptación clínica únicamente respaldada por método y evidencia.
