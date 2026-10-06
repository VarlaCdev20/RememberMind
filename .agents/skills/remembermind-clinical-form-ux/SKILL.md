---
name: remembermind-clinical-form-ux
description: "Diseñar la interacción de formularios clínicos de RememberMind: contexto del residente y autor, captura progresiva, validación, interpretación del backend, confirmación, dirty state y continuidad. Usar para registros y valoraciones clínicas; no para formularios administrativos simples ni inventar reglas médicas."
---

# Propósito

Guiar captura clínica compacta desde contexto hasta continuidad, preservando datos y diferenciando validación de interpretación.

# Cuándo usar

Signos vitales, dolor, hidratación/ingesta, eliminación, movilidad, sueño, conducta, cognición, heridas, incidentes y valoraciones profesionales existentes.

# Cuándo NO usar

CRUD administrativo simple, nuevos requerimientos clínicos no aprobados, prescripción/competencias o persistencia inventadas por UX.

# Fuentes que debe consultar

Leer el [contrato compartido](../../../docs/frontend/CONTRATO_VISUAL_UX_UI.md) al iniciar una tarea de esta especialidad; reutilizarlo si ya se leyó. El [pipeline](../../../docs/frontend/STACK_SKILLS_UX_UI.md) determina orden y selección, sin cargar etapas ajenas.

Documentación funcional y auditoría del formulario en docs/base-de-datos/ si existe, Request/Livewire, Action/Service, Policy, modelo y componentes actuales. Contexto debe proceder de relaciones reales.

# Workflow obligatorio

1. Confirmar quién registra, a qué residente, con qué jornada/contexto y qué acción backend realiza. Mostrar residente/profesional/datos conocidos sin pedirlos otra vez; fecha/hora del servidor solo si el flujo la define, conservando la fecha de medición real.
2. Diseñar contexto → captura → validación → interpretación → confirmación → registro → continuidad. Elegir disclosure según ramas aprobadas, sin añadir campos obligatorios.
3. Separar formato, coherencia e interpretación clínica. Consumir estados del backend y diseñar [la matriz de captura/recuperación](references/clinical-interaction.md).
4. Definir dirty state para Cancelar/X/volver/Escape/backdrop y cambios de residente. Prevenir pérdida de captura y doble envío; distinguir validación fallida de resultado persistido.
5. Implementar interacción dentro del alcance autorizado. Verificar negativos relevantes, cierre limpio/sucio, fallo backend, carga y resultado; nunca afirmar registro antes de confirmación backend.

# Reglas no negociables

Aplicar gobernanza de BDD, terminología, autoridad visual y UX GAP DETECTADO del contrato común. Reference-first y reutilización del Design System. No atribuir un registro a otro profesional desde input manipulable. No bloquear o rebajar una medición por umbrales de JS; separar coherencia técnica y lectura clínica.

# Criterios UX

Inputs compactos, label/unidad visibles, ramas relevantes, contexto inequívoco, error cercano al campo, lectura clínica comprensible y continuidad. Al fallar, conservar valores válidos y ofrecer corrección.

# Errores frecuentes que debe evitar

Mostrar todas las ramas a la vez; input dentro de input; doble borde; repetir datos conocidos; usar toast como única validación; cerrar y perder captura; presentar crítico como error de formato; autosave clínico sin contrato aprobado.

# Definition of Done

Flujo completo y matriz de estados definidos. Cancelación protege cambios, errores conservan captura, autor/contexto son fiables, interpretación proviene del dominio y resultado refleja persistencia real. Verificación de interacción realizada o pendiente declarada.

Informar ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN según el alcance; distinguir lo comprobado de lo pendiente.
