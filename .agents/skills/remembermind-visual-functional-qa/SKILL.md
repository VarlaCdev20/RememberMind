---
name: remembermind-visual-functional-qa
description: "Aceptar o rechazar una interfaz de RememberMind mediante evidencia visual y funcional de referencia, viewports, estados, formularios y datos. Usar al verificar una pantalla terminada o solicitar revisión; produce PASS o FAIL y hallazgos, sin diseñar ni implementar correcciones."
---

# Propósito

Emitir PASS o FAIL con evidencia de una interfaz ya implementada. Esta skill observa y dictamina; no diseña ni edita producto.

# Cuándo usar

Aceptación final, revisión solicitada o verificación tras correcciones visuales/funcionales.

# Cuándo NO usar

Diseño, implementación, reparación de hallazgos o aprobación de reglas clínicas. Una auditoría de skills/documentos no es QA de una pantalla.

# Fuentes que debe consultar

Leer el [contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) al iniciar una tarea de esta especialidad; reutilizarlo si ya se leyó. El [pipeline](../../../docs/frontend/STACK_SKILLS_UX_UI.md) determina orden y selección, sin cargar etapas ajenas.

Referencia aprobada, UX FLOW/criterios de tarea, contrato común, pantalla renderizada con datos sintéticos, pruebas pertinentes y matriz de viewports/estados. No usar capturas históricas como prueba de código actual sin verificar vigencia.

# Workflow obligatorio

1. Fijar superficie/versión, referencia, alcance, rol autorizado, datos sintéticos y criterios. Preparar [la matriz y dictamen](references/qa-acceptance.md).
2. Inspeccionar referencia/layout/typography/spacing/colors/depth/glass/motion/responsive/accessibility/states/data/forms/charts en los cinco viewports.
3. Ejercitar estados aplicables y revisar teclado, foco, dirty state, errores/carga/éxito y fidelidad de datos. Buscar explícitamente los cinco literales prohibidos en contenido visible.
4. Registrar hallazgos con evidencia, impacto, ubicación y criterio incumplido. No implementar correcciones ni ocultar fallos. Marcar no aplica con razón; evidencia no disponible es pendiente.
5. Emitir PASS solo con criterios aplicables revisados sin fallos. Emitir FAIL por defecto observado o evidencia requerida incompleta, distinguiendo ambos; transferir correcciones a la especialidad correspondiente.

# Reglas no negociables

Aplicar gobernanza de BDD, terminología, autoridad visual y UX GAP DETECTADO del contrato común. No aceptar por tests/build/compilación únicamente. Referencia aprobada prevalece sobre gustos. Cualquier null, NaN, undefined, Infinity o [object Object] visible implica FAIL. No inventar PASS ni defectos cuando falta acceso.

# Criterios UX

Revisión visual real más verificación funcional pertinente; resultados reproducibles, con viewport/estado/tema y fuente. No convertir una revisión acotada en una auditoría de todo el sistema.

# Errores frecuentes que debe evitar

PASS desde lectura de CSS; tratar N/A como defecto; producir mock clínico en app para probar; corregir dentro de QA; omitir estado crítico; esconder chart con datos; repetir verificaciones sin cambio ni riesgo concreto.

# Definition of Done

Dictamen PASS/FAIL, matriz cubierta, hallazgos y limitaciones concretos. Cada criterio aplicable tiene evidencia; no hay cambios de producto durante esta etapa. Tras correcciones externas, nueva revisión proporcional.

Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN según el alcance; distinguir lo comprobado de lo pendiente.
