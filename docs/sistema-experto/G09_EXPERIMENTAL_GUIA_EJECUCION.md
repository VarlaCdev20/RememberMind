# O.R.I.O.N. — Guía de ejecución del prototipo experimental G08/G09

Versión inicial: 2026-10-17. **No autorizado para atención clínica.**

## Alcance real del código

Implementados en **PHP independiente de Laravel** (sin acceso a BDD de producción) los siguientes componentes:

- Catálogo de 15 reglas candidatas, con los códigos documentales G09.
- Evaluador de compuertas y antecedentes positivos (C1/C2/C3) y conservación de trazas de pruebas.
- Perfil instrumental **exclusivamente sintético**, uno por cada subcomponente; sin equivalencia con instrumento real ni norma clínica.
- Adaptador de capturas sintéticas con **estructura** de `aplicaciones_instrumento`, `preguntas` y `respuestas_instrumento`; reutiliza `InspectorComponenteInstrumental` ya existente.
- Constructor de informe de **prueba técnica**, con interpretación limitada, condiciones y evidencia referenciadas.
- Auditoría G08 de 15 subcomponentes basada en expediente y publicaciones normativas vinculadas.

Las tablas operativas y las 23 expertas NO se modifican. No existe adaptador Laravel/BDD en este incremento. La presencia de un campo operacional en el proyecto no equivale a que una fuente esté clínicamente autorizada.

## Ejecución reproducible

Requiere PHP 8.3 o superior:

```bash
php motor-experto/tests/g09_experimental.php
php motor-experto/tests/g08_g09_puente.php
```

El flujo `.github/workflows/orion-g09-experimental.yml` realiza análisis sintáctico de las clases y ejecuta ambos programas de prueba en cada PR que modifique este módulo.

## Ejemplo de funcionamiento (conceptual, no clínico)

Para una tarea completamente inventada de ATE-S01, se declara instrumento `SIM-ATE-S01` edición `G08-TEST-20261017`, una respuesta `FALLO_SIMULADO`, contexto técnico suficiente y el indicador `fixture_sintetico=true`. El inspector admite el patrón exacto sintético y el adaptador genera una evidencia de prueba; G09 examina las condiciones C1/C2/C3 y puede emitir el estado técnico `HALLAZGO_ACOTADO`.

Si la respuesta no coincide, la edición cambia, falta un campo, el episodio excede el corte o el contexto es insuficiente, el flujo **no fabrica el resultado**. Una entrada `uso=ASISTENCIAL` o `fixture_sintetico=false` se rechaza.

El informe técnico reconstruye: identificador de regla → condiciones satisfechas → identificador de evidencia. No implica que el hallazgo observado sea real ni válido clínicamente.

## Políticas conservadoras

- Evaluabilidad `EV-CA/CE/CL/CV-0/1/2`, diferente de COG-MEM.
- Resultados G09 **no copian** los valores `DIFICULTAD_EVIDENCIADA`, `SIN_DIFICULTAD_EVIDENCIADA`, `HALLAZGOS_MIXTOS` de Memoria.
- Dos hallazgos específicos simultáneos no se priorizan arbitrariamente; quedan pendientes de resolución clínica del contrato G09.
- Una puntuación global, un texto libre, un valor NULL o FALSE sin significado autorizado no prueba ni normalidad ni deterioro.
- Los componentes G08 no tienen perfiles clínicos admitidos en esta rama: las tareas con nombres reales (BTA, Stroop, TMT-B, Boston Naming, etc.) quedan **sin interpretación ejecutable**.

## Qué falta para versión integral y evaluación médica

**En G08:** por cada subcomponente, identificar y justificar instrumento/edición, subprueba y método de interpretación apropiado para edad, escolaridad, idioma y población, con derechos de uso, sin usar cortes arbitrarios. El estado actual se documenta en `G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md`.

**En G09:** pasar de simulación a un adaptador versionado con fuentes operacionales autorizado para investigación, compuertas completas A01–A14, control de confusores, episodios, comparación longitudinal y resolvedor acordado para múltiples hallazgos, sin afectar D-131 de Memoria.

**En integración:** conexión a motor genérico persistente y reporte segregado de investigación; pruebas sobre SQLite/PostgreSQL efímeros, validación de permisos y acciones desactivadas fuera de pruebas.

**En validación clínica posterior:** con base legal/ética, casos desidentificados interpretados por profesionales, comparación ciega, análisis de discrepancias y evaluación independiente. Solo después se considerará G13 y posible publicación asistencial.

**La clasificación global de DCL todavía no se incluye**: requiere contrato de criterios generales, autonomía funcional, subtipos y validación diferenciada de G09 por subcomponentes.

Referencias: [EXP-01](https://docs.google.com/document/d/1jLV4WU1PMvNHk7hnERVCmVCT6QaXtBe2eIyQMKBACPc/edit) y [G09-EXP](https://docs.google.com/document/d/1b4Vf4MrkodLJIUQF_PtQ_7frOe8F4-nnasedktlaMfw/edit).
