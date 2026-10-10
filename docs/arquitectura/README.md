---
title: "Arquitectura vigente de RememberMind"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-10
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [code_organization]
verified_against_commit: ed9dd523e8a5287f7fc6d7d90c75aaab6fea1dc8
verification_scope: STATIC_REPOSITORY_REVIEW_AND_SCOPED_CLEANUP
runtime_verified: false
supersedes: []
related_docs: ["ADR_ARQUITECTURA_HIBRIDA.md", "ARQUITECTURA_VIGENTE.md", "AUDITORIA_LIMPIEZA_2026_10_10.md"]
related_modules: []
---

# Arquitectura vigente de RememberMind

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
  Models/             # 96 identidades Eloquent conservadas explícitamente
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
- App\Models conserva 96 FQCN por compatibilidad con relaciones, autenticación, factories y referencias polimórficas. La [excepción por modelo](MODELOS_ELOQUENT.json) sigue vigente.
- Los módulos clínicos tienen coordinación entre propietarios. Es acoplamiento real que debe vigilarse, no código que pueda borrarse por falta de independencia total.
- Las cuatro capas se mantienen aunque una no tenga implementación. Los marcadores de carpetas vacías describen estructura prevista, no capacidades clínicas terminadas.

## Motor experto

El [motor técnico](../../motor-experto/README.md) contiene 22 PHP sin dependencia de Laravel/Eloquent. [ApoyoDecision](../../app/Modulos/ApoyoDecision/README.md) adapta la consulta y persistencia técnica existente. El contrato y el evento tras commit están preparados; no existe consumidor clínico asíncrono activado. Se conservan las decisiones pendientes de conocimiento, transporte durable, validación y revisión profesional.

## Evidencia

- [Mapa de responsabilidades y límites](ARQUITECTURA_VIGENTE.md).
- [Migración ejecutada y verificaciones de su corte](INFORME_MIGRACION_HIBRIDA.md).
- [Auditoría posterior, eliminaciones y comprobaciones](AUDITORIA_LIMPIEZA_2026_10_10.md).
- [Pruebas estructurales](../../tests/Feature/Architecture/FolderOrganizationTest.php), [frontera de presentación](../../tests/Feature/Architecture/PresentationBoundaryTest.php) y [cuatro capas](../../tests/Unit/EstructuraModularTest.php).

Los inventarios de architecture-audit y refactorizacion-total se conservan como historia. Los alias públicos, nombres lógicos de vistas y algunos nombres AdultoMayor se conservan por compatibilidad. El baseline BDD y sus decisiones aprobadas siguen gobernando el esquema.
