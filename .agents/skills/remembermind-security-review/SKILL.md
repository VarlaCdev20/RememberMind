---
name: remembermind-security-review
description: "Revisar independientemente la seguridad de un flujo RememberMind: autorización contextual, IDOR, autoría, mass assignment y exposición de datos. Usar como gate de operaciones sensibles o revisión solicitada; reporta hallazgos sin implementar correcciones."
---

# Purpose

Aceptar/rechazar seguridad de la implementación con evidencia de accesos y efectos.

# Use when

Gate final o revisión solicitada de permisos, clínica, familia, descargas, APIs, exportaciones y datos sensibles.

# Do not use when

Diseñar competencias o conceder permisos, editar mientras se audita o validar seguridad solo con botones/403; authorization-guardian guía implementación.

# Mandatory sources

[app/AGENTS](../../../app/AGENTS.md), baseline roles/BDD, contrato de recurso, documentación de excepción temporal si aplica, rutas/Policies/servicios/controllers/Livewire/tests.

# Domain assumptions

Cuenta activa y permiso son necesarios pero no suficientes. Lectura del residente no autoriza expediente completo; familia carece de publicación general por simple vínculo.

# Workflow

1. Reconstruir actor/operación/recurso/residente/estado/competencia y fuente aplicable.
2. Verificar cadena autenticación + ACTIVO + permiso + Policy + regla + vínculo/scope/competencia en todas las entradas.
3. Revisar actor negativo: Superadmin clínico, Admin RRHH/clínico, Enfermería prescripción, profesional fuera de competencia y familia recurso/contenido no autorizado.
4. Revisar IDs anidados, mass assignment, autor derivado, scopes de consulta, payload/logs, private storage y reautorización de descarga/export.
5. Reconocer excepción local/testing solo en alcance documentado; probar fuera del entorno/flag y preview excluido. No certificar producción desde excepción de pruebas.
6. Ejecutar negativos relevantes verificando ausencia de efectos, datos y audit falsos; comprobar ruta directa sin botón.
7. Emitir PASS/FAIL/BLOQUEADO con ubicación, acción reproducible, fuente, impacto y solución propuesta; no editar. Tras corrección por implementador repetir evidencia afectada.

# Invariants

No broad bypass, permiso de residente como autorización total, atributo profesional manipulado, logs sensibles ni error interno al usuario.

# Failure conditions

Negativo solo HTTP, test privilegiado por flag que oculta frontera real, exposición familiar por carga excesiva o hallazgo omitido para emitir PASS.

# Escalation rules

Nueva frontera sensible necesita propietaria. Vulnerabilidad contra contrato se devuelve para reparación autorizada; falta de entorno/cobertura impide afirmar aceptación, no otras revisiones.

# Tests required

AUTHORIZATION/IDOR por actor/cuenta/permiso/estado/recurso/autor; contenido/export/archivo, denegación sin escritura y exception flag/entorno; casos aplicables, sin suite global obligatoria.

# Definition of Done

Dictamen independiente reproducible, fronteras comprobadas, no verificado explícito y reparaciones revisadas en fase separada.
