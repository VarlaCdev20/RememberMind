---
title: "Gobernanza documental de RememberMind"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: true
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# Gobernanza documental

## Metadatos de documentos importantes

```yaml
---
title: Título descriptivo
status: CURRENT
version: "1.0"
last_reviewed: YYYY-MM-DD
owner: RememberMind
source_of_truth: false
verified_against_commit: commit-base
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---
```

version es versión del documento, salvo que se indique explícitamente versión de contrato; no etiqueta global del producto. La convención inicial 1.0 en documentos antes sin metadatos identifica su primera cabecera controlada en esta fase, no una release histórica inventada. last_reviewed indica revisión de alcance documentada, no tests ni aprobación clínica. verified_against_commit es referencia base; si hay cambios sin commit, describir árbol de trabajo y alcance inspeccionado.

source_of_truth true siempre está limitada al propósito: estado describe implementación observada; glosario fija terminología; BDD fija estructura; ninguno autoriza nuevas reglas por contener YAML. La jerarquía de AGENTS y decisiones aprobadas sigue vigente.

En las cabeceras revisadas durante Fase 1, `verification_scope: STATIC_REPOSITORY_REVIEW` y `runtime_verified: false` identifican exclusivamente el contraste documental/estático de esta revisión. `verified_against_commit` no certifica ejecución, instalación, pruebas, build ni una BDD real; tampoco transforma resultados históricos en pruebas ejecutadas hoy. Fase 1 no ejecutó `migrate:fresh`, seed, suites, build ni comprobación de una BDD real. Una verificación runtime futura debe registrar comandos, entorno, fecha, resultado y alcance antes de modificar estos metadatos.

El portal `docs/README.md` delimita su autoridad con `source_of_truth_scope: [document_governance, source_routing]`. Gobierna documentación y routing de fuentes; no sustituye baseline BDD, contratos clínicos, matriz de permisos ni decisiones aprobadas específicas.

## Estados oficiales

| Estado | Significado |
|---|---|
| DRAFT | En construcción; no norma aprobada |
| PROPOSED | Propuesta para decisión; no implementación/aprobación |
| APPROVED | Decisión explícitamente aprobada; indicar quién, fecha y alcance |
| CURRENT | Fuente aplicable hoy en su ámbito, con conflictos parciales visibles |
| DEPRECATED | Sustituido para uso normativo; conservar motivo y reemplazo |
| HISTORICAL | Snapshot/antecedente; DO NOT USE AS CURRENT SOURCE OF TRUTH |

CONFLICTING y UNKNOWN son hallazgos de auditoría, no estados YAML. Declarar parte afectada y enlazar decisión/deuda. No usar FINAL, FINAL2 o NUEVO como criterio de autoridad.

## Autoridad y evidencia

Instrucción actual de propietaria → contrato BDD/decisiones aprobadas aplicables → documentación funcional vigente → código V2 → tests → auxiliares → historia. No elegir fuente por fecha/nombre solamente.

Para una afirmación separar documentación vigente, código presente, tests definidos, tests ejecutados, propuesta e incertidumbre. Comando/motor/fecha/resultado cuando se ejecuta prueba. SQLite no demuestra locks/carreras PostgreSQL; configuración CI no acredita un run.

## Actualizar sin inventar

1. Leer fuente y consumidores; localizar decisión anterior.
2. Clasificar ámbito/autoridad y verificar contra implementación pertinente.
3. Si es hecho descriptivo incorrecto, corregir con evidencia; si es regla sensible contradictoria, registrar decisión sin cambiar regla.
4. Añadir supersedes solo cuando existe sustitución explícita; no aplicar extensión a todo un baseline.
5. Actualizar portal/enlaces, estado o roadmap según corresponda; registrar cambio documental.
6. Revisar enlaces y diff; revisión independiente. Commit/push siguen instrucción actual.

No replicar diccionario/matriz clínica por conveniencia. Estado, roadmap, decisiones y deuda tienen funciones distintas; enlazar IDs en vez de copiar todo.

## Historia y revisión

Mantener rutas históricas durante Fase 1. Avisar en carpeta y documentos que podrían confundirse con norma. Migrar a docs/historico solo tras conocer enlaces entrantes de README/AGENTS/skills/docs.

Cuando cambian locks, extensión BDD, contrato institucional o entrega con evidencia, revisar documentos afectados. No fijar fechas de entrega ficticias ni afirmar validación clínica de una skill. Para futuros agentes empezar en [el portal](README.md).

## Alcance de la Fase 1 histórica

La restricción «solo Markdown, sin producto ni commit» describe exclusivamente el corte documental original del 06/10/2026 y su [verificación](VERIFICACION_DOCUMENTAL_FASE_1.md). No limita tareas posteriores de implementación autorizadas por la propietaria.

## Mantenimiento vigente

- Estado actual apunta al último corte comprobado; los informes fechados conservan sus resultados originales.
- Separar requisito aprobado, implementación observada, pruebas ejecutadas y validación clínica. No inventar una aprobación para resolver un conflicto de nombres.
- Una aprobación vigente del mismo alcance se reutiliza: no pedirla otra vez por el nombre PROPUESTA de un archivo.
- La investigación, una skill y una prueba sintética no sustituyen datos originales ni aprobación estructural. Las correcciones técnicas independientes continúan.
- Antes de retirar un documento comprobar enlaces entrantes, decisiones únicas y consumidores. Sin duplicados exactos no eliminar evidencia solo para reducir un contador.
- Enlaces a clases movidas se actualizan a su ubicación real. Si el artefacto no existe y no hay sustituto comprobado, retirar el enlace roto y conservar el aviso de ausencia con la ruta original.
- Las especificaciones de módulos se localizan en [el índice funcional](modulos/README.md); las instrucciones de AGENTS orientan el trabajo y no reemplazan esos contratos.
