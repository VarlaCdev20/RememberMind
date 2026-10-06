---
name: remembermind-database-audit
description: "Auditar independientemente esquema, mappings y persistencia de RememberMind contra baseline congelado y extensiones aprobadas. Usar como gate BDD o revisión de relaciones/migraciones; emite evidencia por capa y motor sin implementar correcciones."
---

# Purpose

Aceptar/rechazar el contrato de persistencia con evidencia independiente.

# Use when

Auditoría solicitada, gate de persistencia, migraciones aprobadas, integridad cruzada, V2/legacy relacionado o revisión de inventario cuando se pide global.

# Do not use when

Implementar durante gate, contar todo el esquema por cada cambio local, alterar baseline para conseguir PASS; database-integrity guía desarrollo.

# Mandatory sources

[database/AGENTS](../../../database/AGENTS.md), baseline/diccionario/decisiones vigentes, contrato resuelto, migraciones reales/Models/Actions/seeders/factories/tests del ámbito.

# Domain assumptions

Inventario se deriva de versión/decisiones actuales, no del nombre del diccionario. Garantía SQL, Model, operación y Policy se revisan separadamente; SQLite no demuestra locks/concurrencia PostgreSQL.

# Workflow

1. Fijar ámbito y autoridad. Descubrir migraciones presentes, no confiar en índice de rutas obsoleto.
2. Comparar esquema/PK/FK/longitudes/nullability/precisión/unique/index/delete con contrato; revisar mapping/casts/relaciones y grano.
3. Revisar admisión/camas, medicación/residente, instrumento/pregunta/opción, consentimiento/contacto, estudios/componentes y longitudinalidad según ámbito.
4. Verificar transacciones, garantías bajo SQL directo cuando se afirma integridad física, reintentos y prueba real de concurrencia/rollback.
5. Ejecutar solo pruebas aplicables y seguras; reset únicamente DB confirmada desechable. Revisar PostgreSQL además de SQLite cuando pertinente.
6. Emitir PASS/FAIL/BLOQUEADO con hallazgo, fuente, capa, motor, evidencia, impacto y reparación propuesta. Sin edición durante el gate.
7. Clasificar desajuste de implementación, legacy a retirar o propuesta estructural; devolver al implementador y revisar corrección luego.

# Invariants

No cambios congelados sin decisión, inventario fijo eterno, deletions clínicas ordinarias ni portable a motor no soportado por simple configuración.

# Failure conditions

PASS sin cobertura, Eloquent llamado garantía SQL, carrera no ejecutada presentada como verificada o corrección realizada por el revisor sin fase separada.

# Escalation rules

Propuesta estructural requiere aprobación; fuente conflictiva material requiere resolución. Falta de entorno se registra no verificado/BLOQUEADO para esa aceptación, sin frenar comprobaciones independientes.

# Tests required

Pruebas relevantes por capa/motor; comando, resultados y no cubierto. No ejecutar migrate:fresh --seed universalmente ni simular garantía SQL con mocks.

# Definition of Done

Dictamen verificable y proporcional, baseline intacto salvo extensión autorizada; implementado/verificado SQLite/verificado PostgreSQL/no verificado claramente separados.
