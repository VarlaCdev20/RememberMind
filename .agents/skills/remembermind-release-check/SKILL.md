---
name: remembermind-release-check
description: "Consolidar la verificación final y el commit local de una tarea RememberMind: aceptación, tests, gates pertinentes, build, UX y legacy. Usar antes de dar por terminada o commitear; no repite automáticamente todas las suites ni autoriza push/despliegue."
---

# Purpose

Cerrar trabajo propio con evidencia suficiente y límites honestos.

# Use when

Tarea sustancial completa, precommit o consolidación final de verificaciones.

# Do not use when

Nueva funcionalidad, aceptación sin evidencia, push/despliegue automático ni revisión global para un ajuste aislado.

# Mandatory sources

AGENTS aplicables, alcance/criterios de aceptación, contrato vigente, git status/diff, resultados de system-testing/gates/QA y [pipeline](../../../docs/sistema/STACK_SKILLS_SISTEMA.md).

# Domain assumptions

Implementado, verificado y validado clínicamente difieren. Tarea solo de documentación/skills requiere validación de esos artefactos; PHP/build no acreditan su calidad y pueden no aplicar.

# Workflow

1. Inspeccionar status/diff y separar cambios propios de preexistentes; revisar aceptación y flujo conectado.
2. Confirmar permisos/invariantes/traceabilidad pertinentes con evidencia de gates realizados, no repetir todo si cobertura sigue válida.
3. Buscar en ámbito cambiado legacy AdultoMayor/adultos_mayores/cod_am/cod_usu, TODO/FIXME/mock/placeholder/debug, catch vacío, éxito falso, deletes, creación directa y escritura UI sin backend. Referencias históricas se clasifican, no se borran por grep.
4. Ejecutar tests estrechos/relacionados faltantes; suite amplia solo por riesgo transversal. Build para frontend significativo; QA visual/accesibilidad real cuando aplica, nunca deducirla de build.
5. Persistencia: validación segura según baseline actual; reset solo DB desechable confirmada. No fijar inventario anterior ni afirmar PostgreSQL por SQLite.
6. Documentar comando/resultado, fallo y no verificado, exacto pendiente. Reparar regresiones introducidas antes de declarar final.
7. Si tarea coherente totalmente verificada, stage únicamente archivos/hunks propios y commit convencional local conforme AGENTS; revisar cached diff. No push sin instrucción.
8. Informe: Implementado, Integración, Seguridad, Pruebas, Build, Legacy, Commit, Pendientes; seleccionar no aplica con razón.

# Invariants

No contaminar commit con trabajo previo, secretos/datos/debug; no verde ficticio, push/despliegue inferidos o silencio ante pendiente esencial.

# Failure conditions

DoD satisfecho por archivos existentes, pruebas no ejecutadas declaradas verdes, trabajo ajeno staged o necesidad de permiso técnico inventada al cerrar.

# Escalation rules

Cambios reservados/destructivos siguen decisión explícita; bloqueo de entorno se informa con comando/requisito y alcance pendiente. No declarar terminado si falta aceptación esencial.

# Tests required

Verificar cobertura existente y ejecutar solo lo que falta para aceptación. Documentación/skills: frontmatter, secciones, enlaces, selección por escenarios y diff; app/build no aplica sin cambio funcional.

# Definition of Done

Aceptación y evidencias consolidadas, reportes sinceros, cambios propios revisados/commiteados cuando aplica y pendientes esenciales visibles.
