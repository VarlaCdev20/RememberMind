# Stack de skills UX/UI de RememberMind

Ubicación: `.agents/skills/`. Diez especialidades y un punto de entrada existente (`remembermind-ui-review`). Auditoría previa: [mapa de conservación y transición](AUDITORIA_SKILLS_UX_UI.md). Autoridad común: [contrato visual y límites](CONTRATO_VISUAL_UX_UI.md).

## Pipeline y selección

Mantener este orden para las etapas que apliquen. No cargar diez skills para una corrección puntual. Registrar «no aplica» y motivo cuando se omite una etapa. Referencia visual se analiza solo si existe; no bloquear esperando una imagen que la tarea no requiere. Responsive y writing pueden aportar restricciones desde el principio; su revisión final ocupa su posición en el pipeline.

| Orden | Skill y ubicación | Objetivo | Description exacta |
|---|---|---|---|
| 1 | [remembermind-ux-flow-architect](../../.agents/skills/remembermind-ux-flow-architect/SKILL.md) | Flujo y jerarquía por rol | Diseñar el flujo de una pantalla o proceso de RememberMind por rol antes del estilo: objetivo, entrada, acciones, jerarquía, excepciones, recuperación y continuidad. Usar para dashboards, navegación y flujos nuevos o reorganizados; no para retoques CSS aislados. |
| 2 | [remembermind-reference-fidelity](../../.agents/skills/remembermind-reference-fidelity/SKILL.md) | Fidelidad a imagen aprobada | Analizar y reproducir una imagen visual aprobada de RememberMind, conservando layout, proporciones, geometría, densidad, tipografía, profundidad y glass. Usar antes de editar una pantalla con referencia; no para explorar libremente una estética sin imagen aprobada. |
| 3 | [remembermind-warm-geriatric-art-direction](../../.agents/skills/remembermind-warm-geriatric-art-direction/SKILL.md) | Dirección artística | Especificar el lenguaje visual cálido geriátrico de RememberMind para una pantalla o familia: superficies, acentos, jerarquía tipográfica, profundidad y glass sutil. Usar al definir dirección visual; no para cambiar flujos, contratos clínicos o implementar componentes. |
| 4 | [remembermind-design-system](../../.agents/skills/remembermind-design-system/SKILL.md) | Tokens y componentes compartidos | Traducir una dirección o referencia aprobada de RememberMind a tokens y componentes Blade/CSS compartidos, localizando equivalentes y consumidores antes de crear o modificar. Usar para fundamentos, variantes, geometría repetida o cambios maestros; no para elegir otra estética ni rediseñar el dominio. |
| 5 | [remembermind-clinical-form-ux](../../.agents/skills/remembermind-clinical-form-ux/SKILL.md) | Captura clínica guiada | Diseñar la interacción de formularios clínicos de RememberMind: contexto del residente y autor, captura progresiva, validación, interpretación del backend, confirmación, dirty state y continuidad. Usar para registros y valoraciones clínicas; no para formularios administrativos simples ni inventar reglas médicas. |
| 6 | [remembermind-data-visualization-ux](../../.agents/skills/remembermind-data-visualization-ux/SKILL.md) | Datos temporales y visualización | Definir el contrato plano y la UX de históricos, tendencias, gráficas, métricas y comparaciones de RememberMind, diferenciando datos guardados, preview y ausencia de datos. Usar cuando una vista presenta o compara datos; no para charts decorativos, umbrales inventados o cambios de persistencia. |
| 7 | [remembermind-motion-microinteractions](../../.agents/skills/remembermind-motion-microinteractions/SKILL.md) | Movimiento funcional | Especificar o implementar microinteracciones funcionales de RememberMind mediante tokens: feedback, hover/press, cambios de estado, modales, drawers, tabs y loading, con movimiento reducido. Usar cuando hay transición o falta de feedback; no para animación decorativa ni recalcular severidad clínica. |
| 8 | [remembermind-responsive-accessibility](../../.agents/skills/remembermind-responsive-accessibility/SKILL.md) | Adaptación y acceso | Adaptar y verificar interfaces de RememberMind en 1440, 1280, 1024, 768 y 390 px, con teclado, foco, labels, touch, contraste, reflow y movimiento reducido. Usar para navegación, formularios, overlays, tablas y gráficas responsive o accesibles; no para redefinir dirección artística ni permisos. |
| 9 | [remembermind-ux-writing](../../.agents/skills/remembermind-ux-writing/SKILL.md) | Copy operativo | Redactar o revisar labels, ayudas, acciones, errores, confirmaciones, alertas, empty states y resultados de RememberMind con terminología institucional y lenguaje humano preciso. Usar para texto visible de interacción; no para marketing, diagnóstico clínico ni afirmar operaciones que el backend no confirmó. |
| 10 | [remembermind-visual-functional-qa](../../.agents/skills/remembermind-visual-functional-qa/SKILL.md) | Aceptación | Aceptar o rechazar una interfaz de RememberMind mediante evidencia visual y funcional de referencia, viewports, estados, formularios y datos. Usar al verificar una pantalla terminada o solicitar revisión; produce PASS o FAIL y hallazgos, sin diseñar ni implementar correcciones. |

## Recorridos por tipo de entrega

Los números corresponden al orden anterior. En todos los casos, etapa 2 únicamente cuando hay referencia visual aprobada y etapa 6 cuando existen datos que visualizar.

| Entrega | Etapas |
|---|---|
| Dashboard | 1, 2, 3, 4, 6, 7, 8, 9, 10 |
| Formulario clínico | 1, 2, 3, 4, 5, 6 si aplica, 7, 8, 9, 10 |
| Sidebar / header operativo | 1, 2, 3, 4, 7, 8, 9, 10 |
| Modal simple | 2, 3, 4, 7, 8, 9, 10; añadir 1 si cambia el flujo |
| Directorio / tabla / cards | 1, 2, 3, 4, 6 si compara datos, 7, 8, 9, 10 |
| Copy puntual | 9; 10 si se solicita aceptación de la interfaz renderizada |
| Corrección puntual de overflow/foco | 8 y QA sobre el alcance afectado; 4 si requiere modificar un componente maestro |
| Futuro sistema experto | Etapas según superficie, usando únicamente reglas/versiones aprobadas; no habilita un motor clínico por sí mismo |

La creación de componentes pertenece a etapa 4; la captura y recuperación a 5; el transporte/representación de series a 6; las transiciones a 7. QA observa y dictamina; las correcciones vuelven al responsable correspondiente y se solicita una nueva revisión. No declarar QA `PASS` por intención de corregir.

## Auxiliares y transición

`design` ofrece exploración gráfica general. `design-system` conserva conocimiento genérico y presentaciones, pero cede la aplicación institucional a `remembermind-design-system`. `ui-styling` ayuda con CSS/Tailwind puntual. `ui-ux-pro-max` aporta heurísticas dirigidas al problema. `brand`, `banner-design` y `slides` se utilizan en comunicación solicitada, fuera de la UI operativa; `humanizer` edita prosa conservando significado.

No cargar sus workflows genéricos por defecto. Verificar recursos antes de ejecutar: las copias locales genéricas no incluyen los scripts y referencias anunciados. Las skills globales permanecen intactas y no son dependencias obligatorias del nuevo stack.

## Ejemplos de invocación

```text
Usa $remembermind-ui-review para reorganizar el dashboard de Enfermería.
Selecciona las etapas necesarias y respeta la referencia aprobada adjunta.

Usa $remembermind-ux-flow-architect para ordenar Mi turno desde tareas pendientes
hasta pase de turno, con las capacidades reales. Entrega UX FLOW antes de editar.

Usa $remembermind-reference-fidelity para implementar el modal de esta imagen
aprobada. Entrega REFERENCE ANALYSIS y documenta adaptaciones de accesibilidad.

Usa $remembermind-design-system para revisar si las cards de residente, usuario,
personal y contacto pueden compartir geometría sin mezclar datos ni permisos.

Usa $remembermind-clinical-form-ux y $remembermind-data-visualization-ux para
mejorar la captura de signos vitales y el histórico, conservando el backend.

Usa $remembermind-visual-functional-qa para revisar la pantalla ya implementada
en los cinco anchos y estados aplicables. Entrega PASS o FAIL sin editar producto.
```

## Entrega y aceptación

Cada skill declara ANTES, PLAN, IMPLEMENTACIÓN y VERIFICACIÓN de forma proporcional. Las de análisis pueden entregar especificación sin código; QA indica «sin edición». No crear referencias vacías, dependencias, nuevos componentes ni scripts por cumplir un catálogo conceptual.

Una pantalla no se acepta con referencia ignorada, jerarquía ausente, inputs gigantes/dobles bordes, critical débil, foco perdido, mobile roto, scroll horizontal, glass ilegible, animación distractora, cards arbitrarias, error que borra captura, gráficas ocultas con datos, `null`/`NaN`/`undefined`/`Infinity`/`[object Object]` visibles o geometrías duplicadas sin justificación. QA necesita evidencia visual real, además de pruebas funcionales pertinentes.

Esta tarea crea infraestructura. Validar su frontmatter, nombres, enlaces, selección y límites; no ejecutar migraciones ni modificar pantallas para demostrar el stack. No se añaden scripts a QA: las herramientas existentes de navegador y tests del proyecto cubren la evidencia; una búsqueda textual por sí sola no garantiza la UX.
