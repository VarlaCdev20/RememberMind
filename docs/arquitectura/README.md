---
title: "Arquitectura vigente de RememberMind"
status: CURRENT
version: "2.1"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [code_organization]
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
supersedes: []
related_docs: ["ADR_ARQUITECTURA_HIBRIDA.md", "ARQUITECTURA_VIGENTE.md", "AUDITORIA_LIMPIEZA_2026_10_10.md"]
related_modules: []
---

# Arquitectura vigente de RememberMind

[Inventario actual de módulos, modelos, vistas, rutas y locks](../sistema/INVENTARIO_ACTUAL.md). El motor tiene seis componentes principales y dos carpetas auxiliares; la integración Laravel pertenece a ApoyoDecision.

Revisión de navegación del 04/11/2026: la organización híbrida sigue vigente. Los conteos y verificaciones de migración enlazados describen sus cortes originales; el [estado actual](../ESTADO_ACTUAL.md) y el [índice de módulos](../modulos/README.md) localizan la implementación y brechas posteriores.

Verificación posterior: [límites comprobados el 11/10/2026](LIMITES_VERIFICADOS_2026_10_11.md) y [preparación para desarrollo](../sistema/PREPARACION_DESARROLLO_2026_10_11.md). Incluyen pruebas de dependencias entre capas y del núcleo experto independiente, con excepciones pendientes explícitas.

La [decisión híbrida aprobada del 10 de octubre](ADR_ARQUITECTURA_HIBRIDA.md) gobierna las ubicaciones del código. Sustituye la organización anterior Backend/Frontend. Laravel permanece en la raíz; las responsabilidades funcionales pertenecen a doce módulos.

Las cuatro capas de cada propietario se conservan incluso si alguna todavía está vacía. Las subcarpetas se crean cuando existe implementación. La [limpieza posterior de archivos y carpetas](LIMPIEZA_CARPETAS_2026_10_10.md) documenta la reducción de marcadores y las comprobaciones realizadas.

## Estructura canónica

```text
app/
  Modulos/{propietario}/
    Dominio/          # reglas y valores puros
    Aplicacion/       # operaciones, consultas y coordinación
    Infraestructura/  # persistencia específica, archivos, correo, PDF e integración
    Presentacion/     # Http, Livewire, Policies, Vistas y Rutas
  Compartido/         # responsabilidades transversales reales
  Models/             # 112 archivos de modelos Eloquent; no equivalen a tablas físicas
  Providers/          # integración de Laravel, vistas, aliases y Policies
  Http/               # middleware y Controller base del framework
resources/
  frontend/           # Design System, CSS y JavaScript fuente
  views/              # layouts, componentes y vistas compartidas
motor-experto/        # motor técnico PHP independiente de Laravel
contratos/            # contratos técnicos v1 y eventos preparados
routes/               # composición ordenada de registros modulares
```

Propietarios: **Identidad, Institucion, Residentes, Admisiones, Clinica, Medicacion, Valoraciones, Cuidados, Social, Alertas, Reportes y ApoyoDecision**. Roles profesionales y administrativos consumen módulos; no definen propietarios adicionales.

## Dependencias y excepciones

- Presentacion recibe entradas, valida su forma, autoriza, delega y presenta resultados. Las transacciones y escrituras Eloquent de los flujos extraídos viven en Aplicacion.
- Dominio conserva reglas puras, sin Laravel, Eloquent ni capas exteriores.
- Aplicacion usa modelos y capacidades existentes. El corte es pragmático: no introduce repositorios ni interfaces vacías por cada servicio.
- Infraestructura implementa detalles externos y de persistencia; Aplicacion e Infraestructura no dependen de Presentacion.
- `App\Models` contiene 112 archivos PHP. El [inventario de96 modelos](MODELOS_ELOQUENT.json) conserva el corte de la migración del 10/10; el [inventario actual](../sistema/INVENTARIO_ACTUAL.md) separa esos archivos del número de tablas físicas.
- Los módulos clínicos tienen coordinación entre propietarios. Es acoplamiento real que debe vigilarse, no código que pueda borrarse por falta de independencia total.
- Las cuatro capas se mantienen aunque una no tenga implementación. Los marcadores de carpetas vacías describen estructura prevista, no capacidades clínicas terminadas.

## Motor experto

El [motor técnico](../../motor-experto/README.md) mantiene independencia de Laravel/Eloquent. [ApoyoDecision](../../app/Modulos/ApoyoDecision/README.md) adapta fuentes, evaluación, consulta y conservación en el alcance existente. Los contratos institucionales adoptados y los límites de persistencia se localizan en el [índice experto vigente](../sistema-experto/README.md). La conservación observacional y la conjunta instrumental/Petersen no tienen el mismo estado de implementación. Activación asíncrona, transporte durable y validación requieren su contrato/evidencia propios; una carpeta o un evento preparado no los acredita.

## Evidencia

- [Desacoplamiento de contexto, efectos externos y consultas de presentación](DESACOPLAMIENTO_2026_10_11.md).

- [Mapa de responsabilidades y límites](ARQUITECTURA_VIGENTE.md).
- [Migración ejecutada y verificaciones de su corte](INFORME_MIGRACION_HIBRIDA.md).
- [Auditoría posterior, eliminaciones y comprobaciones](AUDITORIA_LIMPIEZA_2026_10_10.md).
- [Pruebas estructurales](../../tests/Feature/Architecture/FolderOrganizationTest.php), [frontera de presentación](../../tests/Feature/Architecture/PresentationBoundaryTest.php) y [cuatro capas](../../tests/Unit/EstructuraModularTest.php).

Los inventarios de architecture-audit y refactorizacion-total se conservan como historia. Los nombres lógicos de vistas se resuelven mediante providers. Las dependencias V1 residuales constituyen deuda; no autorizan una capa permanente de compatibilidad ni nuevas dependencias obsoletas. El baseline BDD y sus decisiones aprobadas siguen gobernando el esquema.
