---
name: remembermind-authorization-guardian
description: "Diseñar e implementar autorización contextual de RememberMind durante desarrollo: cuenta activa, permiso, Policy, estado, vínculo y competencia. Usar en mutaciones clínicas, permisos, familia, archivos o exportaciones; security-review conserva el gate final independiente."
---

# Purpose

Prevenir acceso y atribución indebidos antes y durante implementación.

# Use when

Nueva operación/recurso, cambios de roles/permisos, consultas sensibles, descarga/exportación, autoría clínica o scopes profesionales.

# Do not use when

Conceder privilegios desde un dashboard o rol conceptual, revisar solo botones, aprobar permisos sensibles nuevos; gate final corresponde a security-review.

# Mandatory sources

[app/AGENTS](../../../app/AGENTS.md), [roles vigentes](../../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [excepción temporal](../../../docs/arquitectura/PERMISOS_TEMPORALES_SUPERADMIN.md), contrato de operación y Policies/servicios/rutas actuales.

# Domain assumptions

La lectura del residente no habilita todo el expediente, exportación o descarga. Autor clínico se deriva del personal activo asociado al usuario; actor técnico es distinto cuando el modelo lo distingue.

# Workflow

1. Crear matriz actor–operación–recurso–residente–estado–permiso–vínculo/scope–competencia con fuente.
2. Aplicar sesión + usuario ACTIVO + permiso explícito + Policy contextual + regla de negocio + relación/alcance/competencia según operación.
3. Revisar HTTP/Livewire/API/jobs/downloads, IDs/códigos anidados, mass assignment y serialización; denegar antes de escribir o exponer contenido.
4. Mantener Gerente RRHH/planificación, Administrador operación diaria, médico prescripción, Enfermería administración/cuidado; otras disciplinas dentro de su competencia. Superadmin no escritura clínica automática.
5. Para familia comprobar vínculo/contacto activos, autorización informativa y contenido permitido; no asumir publicación clínica institucional.
6. Identificar excepción Superadmin local/testing con flag, personal propio ACTIVO, área cuando requerida y exclusiones documentadas, incluido preview; no trasladarla a producción ni competencia permanente.
7. Probar negativos sin cambios persistidos y entregar matriz a security-review.

# Invariants

Sin bypass global, primera fila para completar contexto, autor elegido por cliente, cuenta inactiva habilitada, permiso genérico del residente usado para toda información clínica ni datos sensibles sobreexpuestos.

# Failure conditions

Solo authorize en UI; Policy por rol sin contexto; IDOR; usuario atribuye a otro profesional; excepciones temporales fuera de alcance.

# Escalation rules

Cambios de competencias, fronteras de permisos o publicación familiar necesitan decisión. Un defecto compatible con contrato vigente se corrige dentro de alcance sin pedir autorización rutinaria.

# Tests required

AUTHORIZATION por cuenta inactiva, sin permiso, actor no competente, estado inválido, recurso/residente cruzado y autor manipulado; familia vinculada/no vinculada/contenido no publicable; descarga directa y efectos cero.

# Definition of Done

Matriz respaldada por contrato, aplicación backend consistente entre entradas y negativos con evidencia; revisión final independiente pendiente o realizada explícitamente.
