---
name: remembermind-system-testing
description: "Diseñar y ejecutar pruebas de RememberMind por invariantes y efectos reales: unitarias, feature, integración, autorización, workflow y regresión. Usar para cambios de dominio/persistencia o brechas de cobertura; no equipara SQLite, build o mocks con prueba clínica/integración."
---

# Purpose

Probar el contrato del proceso y los efectos permitidos/prohibidos, con evidencia reproducible.

# Use when

Implementación o corrección de flujos, reglas, Policies, persistencia, negativos de seguridad y aceptación funcional.

# Do not use when

Crear tests que copian implementación, inflar suites sin riesgo concreto, sustituir prueba PostgreSQL con llamadas secuenciales SQLite o validar clínicamente un motor experto solo con tests técnicos.

# Mandatory sources

[tests/AGENTS](../../../tests/AGENTS.md), contrato vigente, [matriz de prueba](references/test-contract.md), phpunit.xml, CI y tests relacionados; revisar entorno sin imprimir secretos.

# Domain assumptions

Unit aísla lógica; Feature usa entradas reales; integración verifica colaboradores/DB; autorización verifica scope; workflow recorre estados/actores; regresión reproduce defecto. Categorías pueden coexistir en un test, sin clases separadas por obligación.

# Workflow

1. Mapear requisitos/invariantes y riesgo a UNIT, FEATURE, INTEGRATION, AUTHORIZATION, WORKFLOW, REGRESSION.
2. Escribir cada caso con estado antes, acción, estado después, efectos permitidos y efectos prohibidos.
3. Probar positivos y negativos significativos: autor/rol/estado/vínculo/residente cruzado, historial, duplicados y rollback.
4. Usar datos sintéticos, freezeTime/travel y fakes solo periféricos; no simular Policy/Action/DB central para declarar integración.
5. Ejecutar primero tests estrechos y luego suite relacionada; ampliar solo por riesgo transversal/gate requerido. Parar cuando cobertura y evidencia son suficientes.
6. PostgreSQL para restricciones/bloqueos/concurrencia, con conexiones/procesos independientes y sincronización controlada; SQLite :memory: para rápido. Confirmar DB desechable antes de reset.
7. Registrar comando, entorno, resultado y alcance no demostrado; build y QA visual son evidencias separadas.

# Invariants

No debilitar regla correcta para verde; ningún 403 sin verificar ausencia de mutación como única prueba negativa de escritura. No datos reales, sleeps temporales arbitrarios ni éxito fingido.

# Failure conditions

Tests meramente status, mocks del núcleo, carrera secuencial, configuración CI presentada como ejecución, suite fallida omitida o instrumentos protegidos copiados.

# Escalation rules

Contradicción test/contrato se resuelve por autoridad; no cambiar norma por test viejo. Si falta PostgreSQL o método clínico, marcar no verificado y continuar pruebas independientes.

# Tests required

La matriz de referencia guía contratos: admisión/ocupación/rollback, familia IDOR, medicación cruzada, historia preservada, preview y eventos. Para la propia skill, revisión de selección/cobertura por escenarios.

# Definition of Done

Casos cubren riesgo proporcional y efectos, resultados reproducibles por entorno; implementado/verificado/no verificado distinguidos.
