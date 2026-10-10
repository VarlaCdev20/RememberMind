---
title: "Auditoría del stack de skills UX/UI"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Auditoría de la entrega de skills UX; evidencia de instrucciones, no certificación de pantallas actuales.

# Auditoría del stack de skills UX/UI

Fecha: 6 de octubre de 2026. Alcance: infraestructura de skills y documentación; sin cambios de producto.

## Inventario leído antes de crear el stack

Se leyeron los `SKILL.md` de las nueve skills del repositorio relacionadas con diseño, interfaz o contenido: `design`, `design-system`, `ui-styling`, `ui-ux-pro-max`, `remembermind-ui-review`, `banner-design`, `brand`, `slides` y `humanizer`. También se revisaron `design-engineer` e `impeccable` del entorno global como auxiliares potenciales. No se modifican instalaciones globales ni cachés de plugins.

El proyecto contiene 33 skills. Las otras 24 se ocupan de ingeniería, dominio, seguridad, entrega o Caveman y quedan fuera de esta reorganización visual. Las cinco skills `remembermind-*` preexistentes están versionadas. La regla `SKILL.md` de `.gitignore` deja fuera las otras 28; la entrega debe habilitar explícitamente el versionado de las skills propias del proyecto y añadir solo las involucradas.

## Mapa de decisiones

| Skill existente | Decisión | Solapamiento | Contenido conservado o reutilizado | Delimitación final |
|---|---|---|---|---|
| `design` | Dejar como auxiliar | Dirección artística, tokens, UI, marca y presentaciones en una sola entrada | Inspección de referencias, coherencia de iconos y distinción entre tipos de entregable | Exploración gráfica general y piezas de comunicación solicitadas; no orquesta pantallas operativas |
| `design-system` | Reemplazar gradualmente en UI de RememberMind | Tokens y componentes con la nueva `remembermind-design-system`; mezcla adicional de slides | Tres capas de tokens, especificación de variantes/estados, integración Tailwind | Auxiliar conceptual para entregables generales; la nueva skill asume la aplicación institucional |
| `ui-styling` | Dejar como auxiliar | Implementación, responsive, accesibilidad y design system | Composición, utilidades, estabilidad de layout, reutilización y foco | CSS/Tailwind puntual sobre Blade/Livewire; los ejemplos React/shadcn/Tailwind 4 no autorizan migrar el stack |
| `ui-ux-pro-max` | Conservar como auxiliar | Heurísticas de casi todas las diez responsabilidades nuevas | Consulta por problema concreto, contraste, teclado, feedback, motion y gráficas | Evidencia de heurísticas, subordinada al contrato y referencia aprobada; no genera un segundo design system |
| `remembermind-ui-review` | Evolucionar | Diseño, implementación y QA mezclados | Proceso por rol, contexto del residente, validación, avisos persistentes y límites clínicos | Punto de entrada que selecciona las skills pertinentes; la aceptación pertenece a `remembermind-visual-functional-qa` |
| `brand` | Dejar como auxiliar | Dirección artística y UX writing | Coherencia de marca y tono, conservación de identidad | Comunicación institucional y assets solicitados; no determina mensajes clínicos ni paletas de pantalla |
| `banner-design` | Conservar como auxiliar | Dirección artística y fidelidad | Legibilidad, composición, tamaño del entregable | Banners y piezas gráficas; un header operativo no es un banner publicitario |
| `slides` | Conservar como auxiliar | Tokens y visualización | Jerarquía narrativa y claridad de datos en presentaciones | Presentaciones solicitadas, fuera del pipeline operativo |
| `humanizer` | Conservar como auxiliar | UX writing | Prosa directa, conservación de hechos y voz | Edición de prosa; la terminología y los mensajes operativos pertenecen a `remembermind-ux-writing` |

## Problemas comprobados

- Las ocho skills genéricas del repositorio contienen únicamente `SKILL.md`. Las referencias, datasets y scripts citados en varios de ellos no vienen incluidos. No deben ejecutarse comandos a rutas inexistentes ni anunciarse esas capacidades como disponibles. El texto de heurísticas de `ui-ux-pro-max` sí puede consultarse sin scripts; la copia global es distinta y se verifica antes de usar recursos adicionales.
- `ui-styling` mezcla React/Radix/shadcn con ejemplos de Tailwind 4. El proyecto usa Laravel, Blade, Livewire 4, Alpine y Tailwind 3.4. No se copia su instalación ni su arquitectura.
- `design`, `design-system`, `brand` y `banner-design` citan rutas `.claude`, herramientas o fuentes genéricas ajenas al contrato local. La nueva delimitación debe prevalecer sobre esos ejemplos.
- Algunas entradas genéricas contienen `argument-hint` de otra plataforma. Se conserva su valor como metadato de origen, manteniendo frontmatter compatible con el validador de Codex.
- `docs/arquitectura/REMEMBERMIND_ARQUITECTURA_FRONTEND.md` contiene valores tipográficos/cromáticos y carpetas objetivo que difieren del código y del índice de arquitectura vigente. Se utiliza como contexto arquitectónico; no como inventario literal de componentes ni como orden de crear `Features/Pages`.
- La nueva tarea establece otros fondos, jerarquía tipográfica y tiempos que los tokens actuales. Se documentará el objetivo aprobado y su correspondencia con el contrato existente. Esta tarea no aplica esos valores al CSS del producto.
- `design-engineer` e `impeccable` globales cubren gran parte del mismo proceso. Se mantienen como auxiliares opcionales, sin prioridad sobre la referencia aprobada ni obligación de instalar sus herramientas.

## Responsabilidad única de las nuevas skills

| Nueva skill | Responsabilidad y salida |
|---|---|
| `remembermind-ux-flow-architect` | Objetivo, jerarquía y continuidad por rol; `UX FLOW` |
| `remembermind-reference-fidelity` | Medición y conservación de imagen aprobada; `REFERENCE ANALYSIS` |
| `remembermind-warm-geriatric-art-direction` | Lenguaje visual cálido, profundidad y tipografía; especificación artística |
| `remembermind-design-system` | Traducción a tokens y componentes compartidos; mapa de reutilización/impacto |
| `remembermind-clinical-form-ux` | Captura clínica guiada y recuperación; contrato de interacción del formulario |
| `remembermind-data-visualization-ux` | Contrato plano, históricos y preview; especificación de datos/visualización |
| `remembermind-motion-microinteractions` | Movimiento que comunica estado/causalidad; mapa de transiciones |
| `remembermind-responsive-accessibility` | Adaptación por ancho y operación accesible; matriz de comportamiento |
| `remembermind-ux-writing` | Terminología y mensajes operativos; inventario de copy |
| `remembermind-visual-functional-qa` | Aceptación con evidencia; `PASS` o `FAIL`, sin diseñar ni implementar |

## Transición gradual y condición de cierre

No se borra ni renombra ninguna skill. Se acotan descriptions y se añade un enlace al contrato local en las genéricas involucradas. Su contenido original permanece, con advertencias sobre recursos ausentes. `remembermind-ui-review` pasa a ser un selector del pipeline, sin duplicar las diez especialidades.

Se depreca gradualmente el uso de `design-system` como autoridad de UI institucional y el antiguo recorrido monolítico de `remembermind-ui-review`. No se deprecan las capacidades de marketing/presentaciones de las auxiliares. La retirada de archivos solo se considera en otra tarea, tras comprobar que no quedan invocaciones útiles.

Los principios extraídos se adaptan; no se copian manuales ni se cargan todas las skills por defecto. El contrato transversal reside una sola vez en documentación. Cada nueva skill incluye una referencia específica útil y la carga cuando corresponde.

La creación queda validada mediante frontmatter, nombres, enlaces y casos de selección/conflicto. La calidad visual de pantallas futuras requiere evidencia de navegador y no se deduce de esta validación documental.

## Verificación de la entrega

- Validador oficial de `skill-creator`: 19 entradas válidas (diez nuevas, selector y ocho auxiliares). Se utilizó Python instalado con PyYAML; el runtime Python empaquetado de Codex carece de ese módulo.
- Diez nombres/descriptions distintos y coincidentes con el registro del pipeline; nueve secciones requeridas presentes en cada skill nueva.
- Diez referencias específicas no vacías. Los 52 enlaces locales de los 24 documentos nuevos/revisados del stack resuelven dentro del repositorio. Las rutas históricas ausentes de las auxiliares siguen documentadas como recursos no disponibles.
- Revisión de consistencia por casos: sidebar con imagen conserva las etapas visuales sin formulario clínico; captura clínica sin imagen omite fidelidad sin bloquear; un dato estructural ausente produce UX GAP; QA sin evidencia requerida da FAIL por cobertura incompleta; una imagen no autoriza severidad ni permisos; el CLI ausente de una auxiliar no se ejecuta.
- Revisión de diff y alcance: infraestructura de skills, índices y reglas de trabajo. No se modificó código de producto, tokens CSS, persistencia, dependencias de la aplicación ni instalaciones globales.
- Build y tests de aplicación no aplican a esta entrega documental. La comprobación no constituye aceptación visual de ninguna pantalla ni una evaluación con un agente independiente.
