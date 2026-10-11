# Contratos de ingeniería del conocimiento y validación

Fuente de alcance: tarea actual de skills; diseños existentes son propuestas, según [índice experto](../../../../docs/sistema-experto/README.md). Esta referencia no aprueba conocimiento clínico, reglas, algoritmo o cambios de persistencia.

## Artefactos Buchanan

| Fase | Entrega | Límite |
|---|---|---|
| Identificación | Problema cognitivo/población, propósito de apoyo, usuarios/responsables, decisiones y criterios de aceptación | Sin diagnóstico o tratamiento autónomos; confirmar decisión institucional |
| Conceptualización | Fuentes, hechos, variables/criterios, relaciones, contexto, incertidumbre y falta de datos | No transformar dato ausente en normal |
| Formalización | Representación, normalización, pesos, reglas, agregación/inferencia y conflictos | Aplicar método aprobado; Buchanan no elige algoritmo |
| Implementación | Contratos versionados/reproducibles, motor separado, explicación e integración | Arquitectura vigente, no raíces/repositorios/servicios externos impuestos por propuestas |
| Prueba | Casos esperados, consistencia, sensibilidad/fronteras, comparación profesional y límites | Separar verificación técnica y validación clínica |

## Vocabulario y cadena

Dato fuente → hecho con contexto → conocimiento → criterio/variable → normalización/peso → regla/operador → agregación multicriterio → inferencia → riesgo/resultado → explicación → recomendación → seguimiento/alerta si el contrato lo autoriza.

Es cadena conceptual para explicitar responsabilidades, no pipeline clínico universal. Un método puede no incluir alguna etapa; justificar no aplica. Red semántica, AHP, Mamdani, LLM, Python y FastAPI no son requisitos aprobados por mencionarse históricamente.

| Concepto | Contrato |
|---|---|
| Dato | Valor original, unidad, fecha, residente, autor/fuente y calidad |
| Hecho | Dato interpretado/contextualizado de acuerdo a definición explícita |
| Variable | Entrada identificable y significado/población, dominio/unidades y ausencia |
| Criterio | Aspecto evaluado, procedencia y relación con decisión apoyada |
| Peso | Influencia justificada/versionada por metodología; no probabilidad inventada |
| Umbral | Frontera aprobada con población/exclusiones; no número extraído de UI |
| Normalización | Transformación documentada conservando original/unidades |
| Regla | Identidad/versión, fuente, variables, condición/exclusión, salida, motivo y validación |
| Agregación | Operador del método, contribuciones reconstruibles y prevención de evidencia duplicada |
| Inferencia | Entradas a fecha de corte y contexto, método/versión, resultado y limitaciones |
| Explicación | Evidencia/versiones/contribuciones/reglas/faltantes y fundamento de recomendación |
| Validación humana | Juicio profesional independiente, autor/fecha/motivo por mecanismo aprobado; no sobrescribe ejecución original |

## Ficha de regla y decisión de implementación

Identificador, descripción, fuente exacta y estado, versión, responsables/decisión, población/exclusiones, variables/unidades, condición, resultado, motivo explicable y casos esperados.

Si falta aprobación, distinguir propuesta académica de regla ejecutable. Puede prepararse contrato/fixture sintético sin activar interpretación clínica. Reglas operativas de signos vitales existentes no acreditan un motor cognitivo.

Conocimiento no pertenece a if/else médico disperso en Livewire; descubrir componentes reales antes de proponer extracción. No construir engine/tablas/editor de reglas por anticipación. La estructura de persistencia, integración con alertas o efecto clínico requieren decisiones específicas.

## Explicación reproducible

Para cada resultado documentar: procedencia/fecha de datos, versión del conocimiento/método/instrumento, criterios usados y contribuciones cuando el método las define, reglas/operadores activados, faltantes/exclusiones, incertidumbre/limitaciones y fundamento de recomendación revisable.

No todos los métodos producen un score global; no inventarlo. No convertir riesgo en diagnóstico. No diseñar textos que afirmen validación clínica inexistente. Registro del juicio profesional y trazabilidad técnica separados según modelos aprobados.

## Instrumentos y derechos

Confirmar versión, población, autor responsable, reglas de aplicación/puntuación y permiso legal/metodológico para usar contenido. Mencionar MMSE, MoCA, Pfeiffer, Barthel o Katz no acredita derechos. No copiar reactivos/material protegido en docs, seeders o tests; usar instrumentos sintéticos para probar infraestructura.

Instrumentos V2 ya tienen versión según diccionario; no crear instrument_versions, expert_runs u otra estructura porque aparece en historia. Si el modelo aprobado no permite trazabilidad necesaria, elevar DOMAIN GAP con opciones y continuar diseño independiente.

## Validación independiente

1. Fijar método/versiones, fuente profesional, casos/dataset autorizado y aceptación.
2. Conocimiento: completitud y consistencia, reglas imposibles, solapamientos, contradicciones, prioridades/exclusiones.
3. Multicriterio: normalización/unidades, pesos, agregación, evidencia duplicada y sensibilidad conforme método. No asumir monotonía/umbral global.
4. Casos: esperado/actual, normales/frontera/faltantes/contradictorios aplicables; reproducibilidad de explicación.
5. Clínica: referencia profesional independiente; desacuerdos y limitaciones registrados.
6. Métricas de confusión/exactitud/sensibilidad/especificidad/Kappa solo cuando metodología, referencia y datos aprobados permiten su cálculo. Sin muestra/resultados inventados.

Reporte por dimensión: criterio + fuente + caso + esperado/actual + evidencia + PASS/FAIL/BLOQUEADO + límite. Sin motor, verificación de diseño únicamente; sin método/referencia humana, validación clínica BLOQUEADA, aunque algún contrato técnico esté comprobado.
