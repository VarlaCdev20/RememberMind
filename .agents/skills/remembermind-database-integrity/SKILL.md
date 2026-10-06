---
name: remembermind-database-integrity
description: "Diseñar y corregir la persistencia del flujo cambiado de RememberMind conforme al baseline congelado y extensiones aprobadas: relaciones, tipos, grano, atomicidad e invariantes cruzadas. Usar durante implementación; database-audit realiza el gate independiente."
---

# Purpose

Mantener consistencia entre el contrato de la operación y sus registros reales.

# Use when

Operaciones multientidad, relaciones/residente cruzado, mappings Eloquent, migración aprobada, persistencia clínica/institucional o concurrencia.

# Do not use when

Auditoría global automática para cada feature, propuesta de tabla/índice por conveniencia, alteración estructural sin aprobación o revisión final independiente.

# Mandatory sources

[database/AGENTS](../../../database/AGENTS.md), índice BDD, baseline/diccionario/decisiones actuales, contrato funcional, Models/Actions/migraciones/seeders/factories y tests de entidades afectadas.

# Domain assumptions

Resolver versión e inventario desde fuentes actuales. PostgreSQL integrado y SQLite rápido según baseline técnico; motores presentes en configuración no implican soporte.

# Workflow

1. Mapear grano, PK/FK, tipos/longitudes/precisión, nullability, casts, scopes, delete behavior y relaciones afectadas.
2. Comparar flujo y mapping con modelo aprobado; distinguir defecto de código de cambio estructural.
3. Comprobar mismo residente en atención/registro, prescripción/horario/administración, plan/ejecución, consentimiento/contacto; pertenencia instrumento/pregunta/opción y estudio/tipo/componente.
4. Definir transaction boundary, unicidad, bloqueos y comportamiento de fallo/reintento conforme contrato. No inventar idempotencia PRN todavía abierta.
5. Distinguir garantía SQL, validación Model, validación Action/entrada y Policy; pruebas de una capa no acreditan otra.
6. Revisar historial y restricciones sin borrado ordinario. Corregir implementación compatible; proponer estructura faltante para aprobación.
7. Ejecutar pruebas relevantes en entorno confirmado; entregar capa/motor/evidencia a database-audit.

# Invariants

cod_usuario/cod_residente string y nomenclatura V2. No JSON/EAV/FK textuales, tablas por examen, BLOB documental o índices nuevos sin aprobación aplicable. Preservar precisión, historial y portabilidad entre motores soportados.

# Failure conditions

FK válida pero residente incorrecto, casting que pierde precisión, parcialidad después de fallo, unicidad solo UI, supuesto lock garantizado en SQLite o esquema alterado para acomodar código.

# Escalation rules

Cambios congelados incluso catálogo/índice fuera del modelo requieren problema/impacto/opciones/decisión. Aislar dependencia y continuar lo compatible; no ejecutar reset sobre DB desconocida.

# Tests required

INTEGRATION de constraints y coherencia cruzada, fallo intermedio y rollback, historial, reintento según contrato; carreras con conexiones independientes PostgreSQL si riesgo concurrente. SQL directo cuando se afirma integridad física.

# Definition of Done

Persistencia alineada al contrato, garantías por capa/motor explícitas, historia intacta y cobertura proporcional sin modificaciones congeladas no autorizadas.
