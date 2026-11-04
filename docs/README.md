---
title: "Documentación de RememberMind"
status: CURRENT
version: "3.0"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [document_governance_and_routing]
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
---

# Documentación de RememberMind

Entrada vigente de navegación documental, revisada el **04/11/2026**. El producto mantiene doce módulos Laravel y el motor PHP independiente en `motor-experto/`. La revisión es documental y no una nueva aceptación funcional. Los informes de entregas conservan su corte; el estado actual y las decisiones específicas resuelven qué aplica hoy.

## Empezar aquí

- [Estado actual](ESTADO_ACTUAL.md): capacidades presentes, límites y evidencia por alcance.
- [Inventario actual](sistema/INVENTARIO_ACTUAL.md):12 módulos,10 roles, código, rutas y versiones de los locks.
- [Catálogo de toda la documentación](INDICE_DOCUMENTAL.md): fuentes vigentes, decisiones, propuestas e historia.
- [Mapa del motor experto](../motor-experto/README.md): reglas, hechos, red e inferencia en sus carpetas actuales.

Avisos de planificación del 27/10: [alcance compartido adoptado y columnas instaladas](base-de-datos/DECISION_AVISO_COMPARTIDO_CAMPOS_2026_10_27.md), sin nuevas tablas; [integración, pruebas y operación](especificacion-funcional/vistas/gerencia/44-INTEGRACION-AVISO-COMPARTIDO.md). Inventario106 conservado.

Actualización del 27/10/2026: [Gerencia35/37 autorizadas e instaladas](base-de-datos/DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md), inventario 106 de negocio y [pruebas/aceptación acotada](especificacion-funcional/vistas/gerencia/40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md). Los informes anteriores mantienen su fecha.

| Necesidad | Fuente |
|---|---|
| Qué funciona, qué falló y qué falta | [Estado actual](ESTADO_ACTUAL.md); la [auditoría del 25/10](auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) es evidencia histórica |
| Qué debe hacer cada módulo | [Índice funcional de los doce módulos](modulos/README.md) |
| Especificación detallada por módulo, vista y rol | [Carpeta de elaboración funcional](especificacion-funcional/README.md) |
| Instrucciones de desarrollo | [AGENTS raíz](../AGENTS.md); app/database/resources/tests por ámbito |
| Estructura y decisiones BDD | [Índice BDD](base-de-datos/README.md) |
| Ubicación del código | [Arquitectura híbrida](arquitectura/README.md) |
| Roles y autorización | [Competencias](arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md) y [modelo de autorización](seguridad/MODELO_AUTORIZACION.md) |
| Inferencia y Petersen | [Sistema experto](sistema-experto/README.md) |
| Diseño y accesibilidad | [Instrucciones frontend](../resources/AGENTS.md), [contrato visual](frontend/CONTRATO_VISUAL_UX_UI.md) y [Design System](../resources/frontend/styles/design-system/README.md) |
| Términos y procesos compartidos | [Glosario](GLOSARIO_DOMINIO.md), [mapa funcional](sistema/MAPA_DOMINIO_FUNCIONAL.md) y [continuidad](sistema/CONTINUIDAD_ASISTENCIAL.md) |
| Decisiones, deuda y pruebas históricas | [Decisiones](DECISIONES_PENDIENTES.md), [deuda](DEUDA_TECNICA.md) y [trazabilidad de cortes](TRAZABILIDAD.md) |
| Desarrollo y despliegue | [Desarrollo seguro](sistema/DESARROLLO_SEGURO.md), [Git Flow](sistema/GIT_FLOW.md) y [procedimiento de producción](produccion/DESPLIEGUE_SEGURO.md) |

## Autoridad y uso

Instrucción actual de la propietaria → baseline/decisiones específicas aplicables → contrato funcional → código como evidencia → tests como evidencia → auxiliares → historia. Consultar [gobernanza documental](GOBERNANZA_DOCUMENTAL.md). Una fecha, nombre FINAL o etiqueta CURRENT no aprueba todo el contenido.

Inventario instalado vigente tras la decisión del 27/10/2026: **106 tablas de negocio + 16 técnicas**. El corte auditado del 25/10 tenía 100 de negocio; se conserva como evidencia histórica. El núcleo original de 70 y los demás inventarios describen etapas; el techo de diseño 110 no instala una migración.

Para desarrollar comportamiento ya aprobado no se pide una firma profesional antes de cada ejecución. Aprobación estructural, medición original, prueba técnica y evaluación posterior en uso real se documentan por separado.

## Historia y catálogo

- [Archivo histórico y cortes anteriores](historico/README.md).
- [Análisis arquitectónicos anteriores](architecture-audit/README.md) y [planes anteriores](refactorizacion-total/README.md): contexto, no ubicaciones ni reglas vigentes.
- [Inventario de Markdown y AGENTS](auditorias/2026-10-25/INVENTARIO_DOCUMENTAL.md): fotografía del inventario original, no aprobación de cada documento.
- [Cambios documentales](CHANGELOG.md) y [acta de limpieza y verificaciones](auditorias/2026-10-25/LIMPIEZA_DOCUMENTAL.md).
