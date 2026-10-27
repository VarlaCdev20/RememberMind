---
title: "Documentación de RememberMind"
status: CURRENT
version: "2.1"
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [document_governance_and_routing]
verified_against_commit: 6d7b8c6d6bc03eecd2ee35add04677f2778272cd
verification_scope: DOCUMENTATION_RECONCILIATION_WITH_LOCAL_AUDIT
runtime_verified: false
---

# Documentación de RememberMind

Entrada vigente de navegación documental, revisada el **27/10/2026**. El producto mantiene doce módulos Laravel y el motor PHP independiente en `motor-experto/`. Los informes de entregas conservan su corte; el estado actual y las decisiones específicas resuelven qué aplica hoy.

## Empezar aquí

Actualización del27/10/2026: [Gerencia35/37 autorizadas e instaladas](base-de-datos/DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md), inventario106 de negocio y [pruebas/aceptación acotada](especificacion-funcional/vistas/gerencia/40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md). Los informes anteriores mantienen su fecha.

| Necesidad | Fuente |
|---|---|
| Qué funciona, qué falló y qué falta | [Estado actual](ESTADO_ACTUAL.md) y [auditoría del 25/10](auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) |
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

Para desarrollar comportamiento ya aprobado no se pide una firma profesional antes de cada ejecución. Aprobación estructural, medición original, prueba técnica y validación clínica se documentan por separado.

## Historia y catálogo

- [Archivo histórico y cortes anteriores](historico/README.md).
- [Análisis arquitectónicos anteriores](architecture-audit/README.md) y [planes anteriores](refactorizacion-total/README.md): contexto, no ubicaciones ni reglas vigentes.
- [Inventario de Markdown y AGENTS](auditorias/2026-10-25/INVENTARIO_DOCUMENTAL.md): fotografía del inventario original, no aprobación de cada documento.
- [Cambios documentales](CHANGELOG.md) y [acta de limpieza y verificaciones](auditorias/2026-10-25/LIMPIEZA_DOCUMENTAL.md).
