---
title: "Plan de unificación visual de RememberMind"
status: HISTORICAL
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

> HISTORICAL — DO NOT USE AS CURRENT SOURCE OF TRUTH. Plan/snapshot fechado; consultar contratos vigentes y estado actual.

# Plan de unificación visual de RememberMind

## Estado inicial auditado

Fecha de corte: 2026-09-28.

- 104 vistas Blade Livewire revisadas.
- 48 vistas contienen estilos inline o bloques `<style>`.
- 23 vistas contienen colores HEX locales.
- El Design System canónico ya está cargado globalmente desde `resources/frontend/styles/design-system/index.css`.
- No se requieren cambios de base de datos ni de reglas de negocio.

Los estilos dinámicos indispensables —por ejemplo, anchos de progreso calculados por Livewire— pueden conservar `style`, pero sus colores, medidas recurrentes y estados deben provenir de tokens.

## Referencias maestras

| Arquetipo | Vista de referencia | Contrato obligatorio |
|---|---|---|
| Operación y monitoreo | Alertas clínicas | `page-header`, métricas, filtros, tabla, modal/drawer |
| Detalle longitudinal | Ficha médica del residente | cabecera 360, tabs, cards de detalle, acciones contextuales |
| Flujo farmacológico | Medicación | cabecera, formularios, tablas clínicas y confirmaciones |
| Captura clínica | Formularios de Alertas, Medicación y Signos Vitales | `modal-livewire`, `form-section`, `field`, `choice-card`, `callout`, `action-button` |
| Acceso público | Landing y Login | misma identidad de marca con densidad propia, sin trasladar decoración al área clínica |

## Reglas de implementación

1. Usar exclusivamente tokens `--rm-*` para color, superficie, borde, sombra, radio, espaciado y movimiento.
2. No agregar colores HEX ni paletas por rol o módulo.
3. Componer las pantallas con componentes de `resources/views/components/ui` y patrones de `components/patterns`.
4. Mantener colores clínicos semánticos para peligro, advertencia, éxito e información; el verde institucional representa marca y acción primaria, no sustituye la severidad clínica.
5. Mantener la densidad compacta operacional y texto nunca menor a 11 px.
6. Validar 390 px, 768 px, 1024 px y 1440 px, además de modo claro y oscuro.
7. Toda nueva pantalla debe pasar `FrontendArchitectureTest`.

## Fases de migración

### Fase 1 — Núcleo y pantallas maestras

- [x] Consolidar tokens, componentes de formularios y responsividad base.
- [x] Normalizar cabeceras, acciones y avisos de Alertas.
- [x] Normalizar cabeceras, acciones y avisos de Medicación.
- [x] Normalizar cabecera y navegación visual de Ficha médica.
- [x] Incorporar profundidad ambiental y parallax sutil con movimiento reducido.
- [x] Unificar jerarquía, validaciones y estructura de modales.
- [x] Convertir drawers clínicos a paneles laterales flotantes y responsivos.
- [ ] Completar cards, filtros, tablas y modales secundarios de estas tres pantallas.

### Fase 2 — Flujos clínicos frecuentes

- [ ] Mi turno y cuidados.
- [ ] Signos vitales y seguimiento.
  - [x] Cabecera, acciones, filtros y estados de Signos Vitales.
  - [x] Cabecera, métricas y expediente lateral flotante de Seguimiento Clínico.
  - [ ] Homologar tarjetas internas, tabs y formularios secundarios de Seguimiento.
- [ ] Valoraciones y admisiones.
- [ ] Formularios clínicos restantes.

### Fase 3 — Gestión institucional

- [ ] Residentes e identidad.
- [ ] Actividades, reportes y administración.
- [ ] Estados vacíos, errores y confirmaciones.

### Fase 4 — Control de calidad

- [ ] Eliminar estilos inline no dinámicos y colores locales restantes.
- [ ] Revisión de teclado, foco, contraste y movimiento reducido.
- [ ] Capturas de regresión visual en los cuatro anchos canónicos.
- [ ] Revisión final de consistencia en modo claro y oscuro.

## Criterio de terminado por pantalla

Una pantalla se considera unificada cuando no introduce colores locales, usa primitivas canónicas, conserva jerarquía y densidad, es usable por teclado, responde correctamente en los cuatro anchos y pasa las pruebas de arquitectura y compilación frontend.
