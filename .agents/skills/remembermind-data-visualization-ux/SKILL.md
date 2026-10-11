---
name: remembermind-data-visualization-ux
description: "Definir el contrato plano y la UX de históricos, tendencias, gráficas, métricas y comparaciones de RememberMind, diferenciando datos guardados, preview y ausencia de datos. Usar cuando una vista presenta o compara datos; no para charts decorativos, umbrales inventados o cambios de persistencia."
---

# Propósito

Hacer legibles datos reales y su procedencia, usando un contrato de presentación plano y estados explícitos.

# Cuándo usar

Históricos, timelines, tendencias clínicas, KPIs, tablas de comparación y gráficas administrativas/familiares autorizadas.

# Cuándo NO usar

Chart decorativo, datos ficticios en producto, modelos Eloquent completos en frontend o modificaciones de schema para alimentar un gráfico.

# Fuentes que debe consultar

Leer el [contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) al iniciar una tarea de esta especialidad; reutilizarlo si ya se leyó. El [pipeline](../../../docs/frontend/STACK_SKILLS_UX_UI.md) determina orden y selección, sin cargar etapas ajenas.

Service/consulta/DTO actual, documentación funcional, units/objetivos aprobados y resources/frontend/styles/design-system/charts/README.md. Inspeccionar RMCharts antes de crear otra librería o integración.

# Workflow obligatorio

1. Definir pregunta que responde la visualización, origen autorizado, período, unidad, denominador, estados incluidos y ausencia de datos.
2. Especificar backend → DTO plano → primitives → frontend → visualization. Leer [el contrato de series y casos límite](references/visualization-contract.md); validar sin coerción silenciosa de ausentes a cero.
3. Separar histórico persistido y current preview sin guardar. Resolver 0/1/2+ registros válidos y referencias solo con objetivos estructurados aprobados.
4. Elegir representación informativa y reutilizar RMCharts, tokens, leyendas, ejes/unidades, tooltips, marcador actual, loading/empty/error y alternativa textual.
5. Verificar actualización Livewire/tema/tamaño y casos extremos, incluidos PA en dos series y fallos de datos; pasar evidencia a QA.

# Reglas no negociables

Aplicar gobernanza de BDD, terminología, autoridad visual y UX GAP DETECTADO del contrato común. Referencia aprobada guía composición. Nunca mostrar null, NaN, undefined, Infinity o [object Object]. No inferir rangos de SpO₂ ni objetivos desde texto libre; no fabricar tendencias o conclusiones clínicas.

# Criterios UX

Datos y período son comprensibles; las series se distinguen sin depender del color. Preview indica Sin guardar y no se contabiliza como registro. Valores no representables tienen mensaje contextual seguro.

# Errores frecuentes que debe evitar

Pasar relaciones/modelos completos al chart; parseFloat de texto arbitrario; null convertido en 0; chart oculto con datos; dibujar solo sistólica; gráfica de ejes vacíos; tooltip inaccesible; inventar banda de referencia.

# Definition of Done

Contrato plano, fuente y cardinalidad documentados. Históricos/preview y objetivos tienen origen verificable; estados y datos inválidos manejados sin éxito falso. Gráfica y alternativa legible verificadas cuando hay implementación.

Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN según el alcance; distinguir lo comprobado de lo pendiente.
