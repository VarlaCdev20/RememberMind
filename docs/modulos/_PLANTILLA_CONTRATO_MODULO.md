---
title: "Plantilla de contrato de módulo"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [module_template_structure]
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: ["../GOBERNANZA_DOCUMENTAL.md", "../TRAZABILIDAD.md"]
related_modules: []
---

# Plantilla de contrato de módulo

CURRENT como plantilla documental de 36 secciones; contenido de un futuro módulo permanece DRAFT/PROPOSED hasta contraste y aprobación aplicable. No demuestra un módulo implementado ni runtime. Sin migraciones/seed/suites/build/BDD real ejecutados.

Al reutilizar, completar title/status/version/last_reviewed/owner/source_of_truth_scope/verified_against_commit/verification_scope/runtime_verified/related_docs/related_modules según [gobernanza](../../docs/GOBERNANZA_DOCUMENTAL.md). `verified_against_commit` solo identifica base estática, incluido árbol sucio si aplica. Clasificar afirmaciones: APPROVED CONTRACT, OBSERVED IMPLEMENTATION, TESTED EXPECTATION, PROPOSED, HISTORICAL, OPEN DECISION, TECHNICAL DEBT, CONFLICT, NOT VERIFIED. No marcar todo CURRENT como regla aprobada.

## 1. Propósito

Identificar proceso y resultado institucional; citar contrato aprobado y evidencia observada por separado.

## 2. Alcance

Enumerar operaciones y fronteras incluidas, sin confundir tablas con módulo completo.

## 3. Fuera de alcance

Registrar exclusiones concretas y dependencias; no prometer implementación no entregada.

## 4. Actores

Identificar roles/cuentas y participación por acción.

## 5. Competencias

Definir profesión/responsabilidad según fuente aprobada; permiso no equivale a profesión.

## 6. Precondiciones

Registrar estados, relaciones, datos y contexto requeridos por operación.

## 7. Entidades y tablas

Enlazar diccionario y Models reales; núcleo congelado y extensión aplicable.

## 8. Relaciones

Expresar FK/cardinalidad/ownership sin convertir todo en aggregate DDD.

## 9. Estados

Para cada entrada describir valores/transiciones reales o aprobadas; conflicto explícito.

## 10. Caso de uso principal

Actor, entrada, autorización, operaciones, salida y estado final verificables.

## 11. Flujos alternativos

Variantes, excepciones y entradas heredadas; no convertir bug en contrato.

## 12. Validaciones técnicas

Separar forma/tipos/exists de reglas de negocio y scope.

## 13. Reglas de negocio

Asignar RULE/INV y citar fuente normativa, implementación y cobertura.

## 14. Reglas clínicas aprobadas

Citar aprobación clínica específica; si no aplica, explicar. No inventar umbral/método.

## 15. Autorización

Permiso + Policy aplicable + competencia + relación/scope/contexto; negativos.

## 16. Transaction boundary

Indicar transacción real, locks, entidades, rollback y efectos externos no atómicos.

## 17. Persistencia

Describir create/update y campos de procedencia; no esquema nuevo.

## 18. Longitudinalidad

Nuevo evento/nuevo registro; estrategia de corrección particular y limitaciones.

## 19. Alertas

Origen/decisión/estado/eventos/consumidores cuando aplica; de lo contrario NONE OBSERVED.

## 20. Auditoría

Activitylog aprobado separado de procedencia clínica/eventos de dominio.

## 21. Continuidad asistencial

Hechos, pendientes, alertas, acciones futuras y siguiente lector autorizado.

## 22. Entrada UI

Enlazar vistas/rutas reales; sin rediseño.

## 23. Livewire / Controllers

Clases reales y métodos; explicar controles de cada entrada.

## 24. Actions

Actions reales o NONE OBSERVED; no wrapper por simetría.

## 25. Services

Services reales/capacidades/límites; no asumir que toda entrada los llama.

## 26. Policies

Policies reales/abilities; indicar NONE OBSERVED cuando corresponde.

## 27. Models

Models reales y responsabilidades locales; evitar coordinación universal.

## 28. Eventos / Listeners / Jobs

Separar Event Laravel, Listener, Job y evento persistido. NONE OBSERVED cuando ausentes.

## 29. Errores

Validación esperada, denegación, conflicto y errores inesperados; no éxito ficticio.

## 30. Efectos secundarios permitidos

Listar escrituras/eventos/archivos/auditoría autorizados con boundary.

## 31. Efectos secundarios prohibidos

Listar violaciones de contrato que nunca son fallback permitido.

## 32. Tests existentes

Enlazar archivo+método exactos y alcance DEFINED/STATICALLY_MAPPED; no PASS ficticio.

## 33. Tests faltantes

Delimitar cobertura NOT_FOUND respecto al mapeo; negativos, rollback/concurrencia faltantes.

## 34. Deuda técnica

Buscar TECH existente antes de crear; gap contrato/observado/impacto/riesgo/archivo/test/acción.

## 35. Decisiones abiertas

Buscar DEC existente; preservar su significado y no aprobar opciones.

## 36. Trazabilidad

Mapear REQ/RULE/INV/AUTH/FLOW/TEST/DEC/TECH contra fuentes verificables.
