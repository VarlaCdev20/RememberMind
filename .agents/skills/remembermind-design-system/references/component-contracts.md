# Catálogo conceptual y contrato de componentes

Este mapa es evidencia al 6 de octubre de 2026. Verificar archivos y APIs cada vez. Los nombres Rm*/Clinical* son responsabilidades conceptuales, no órdenes de crear clases o archivos.

## Core

| Concepto | Equivalente o punto de partida actual |
|---|---|
| RmButton | ui/action-button.blade.php y components/buttons.css |
| RmIconButton | Variantes del botón y accesible name; comprobar API antes de extender |
| RmInput | components/input.blade.php, ui/field.blade.php y components/forms.css |
| RmSelect / RmTextarea | Controles nativos con styles de forms y field; no hay equivalente único genérico confirmado |
| RmSearch | ui/filter-search.blade.php; verificar contrato |
| RmCheckbox | components/checkbox.blade.php |
| RmRadio / RmSwitch | Buscar controles nativos/patrón actual; crear wrapper solo con uso real |
| RmCard / RmPanel | ui/card.blade.php, ui/section-card.blade.php |
| RmGlassPanel | Superficie/variante de panel y tokens existentes, no otra familia |
| RmBadge | ui/status-badge.blade.php; significado ya resuelto por dominio |
| RmTabs | components/tabs.css y patrón actual de interacción |
| RmTooltip | Buscar patrón accesible de shell/popover; no se confirmó componente genérico |
| RmToast | ui/toast.blade.php; separar alertas persistentes |
| RmModal / RmDrawer | ui/modal-livewire.blade.php, ui/drawer-livewire.blade.php |
| RmSkeleton / RmEmptyState | ui/skeleton.blade.php, ui/empty-state.blade.php |
| RmDataTable | Tabla actual y components/tables.css / patterns/unified-data-tables.css; no hay genérico único confirmado |

Los nombres de Blade anteriores son relativos a resources/views/components/ y CSS a resources/frontend/styles/design-system/. No importar props de otro framework.

## Clínicos

| Concepto | Responsabilidad / punto de partida |
|---|---|
| ResidentClinicalContext | ui/resident-summary.blade.php, ui/clinical-info-card.blade.php |
| ClinicalForm | Composición de ui/form-section.blade.php y flujo clínico existente |
| ClinicalField | ui/field.blade.php y control; distinguir error técnico de interpretación |
| ClinicalMeasurementCard | Card de medición actual; mapear antes de extraer |
| ClinicalStateBadge | status-badge y payload de clasificación autorizado; no clasificar en Blade |
| ClinicalChart | RMCharts y charts/ del Design System |
| ClinicalHistory | Listado/timeline existente con origen y fecha |
| ClinicalRecommendation | Mensaje del servicio aprobado; no generar recomendaciones clínicas |
| CriticalReviewDialog | Modal existente con revisión contextual y acciones autorizadas |
| ClinicalOperationResult | ui/resultado-operacion-clinica.blade.php |

## Foundations e impacto

Especificar por componente: función, fuente de datos admitida, props/slots, variantes, estados, teclado/foco, responsive, tokens y consumidores. Una primitiva genérica solo presenta; si requiere dominio, usar composición en la capa apropiada.

PersonEntityCard → ResidentCard/UserCard/StaffCard/ContactCard puede reutilizar geometría. Mantener adaptadores/payloads distintos y autorización fuera de la primitiva.

Para cambio maestro, localizar invocaciones con rg; listar vistas, variantes y extremos de contenido afectados. Quitar overrides dentro del alcance solo cuando sean redundantes. Verificar estado clínico, confirmaciones y carga además de apariencia. No crear los 33 conceptos de este catálogo si no hay una necesidad real.
