# Inventario estático de reutilización UI — 21/10/2026

Conteo estático; ocurrencias no equivalen a defectos o a verificación visual. Style puede ser geometría de gráfico; HEX puede ser export o gráfico. No incluye componentes dinámicos ni vistas no modulares.

Componentes Blade en `resources/views/components`: **128**. No es un conteo de pantallas.

| Módulo | Vistas Blade | Referencias x-* | Componentes distintos | Atributos style | Vistas con HEX | Señales de consultas en Blade |
|---|---:|---:|---:|---:|---:|---:|
| Admisiones | 10 | 47 | 11 | 1 | 0 | 0 |
| Alertas | 16 | 83 | 17 | 3 | 1 | 0 |
| ApoyoDecision | 33 | 60 | 5 | 1 | 0 | 0 |
| Clinica | 71 | 112 | 20 | 25 | 1 | 0 |
| Cuidados | 68 | 355 | 52 | 6 | 0 | 0 |
| Identidad | 48 | 49 | 15 | 168 | 28 | 0 |
| Institucion | 29 | 80 | 19 | 17 | 0 | 0 |
| Medicacion | 15 | 55 | 17 | 0 | 0 | 0 |
| Reportes | 24 | 42 | 14 | 215 | 16 | 0 |
| Residentes | 27 | 56 | 17 | 15 | 4 | 0 |
| Social | 7 | 45 | 8 | 2 | 0 | 0 |
| Valoraciones | 11 | 41 | 12 | 1 | 0 | 0 |

## Componentes compartidos más referenciados

| Componente | Referencias |
|---|---:|
| `ui.field` | 112 |
| `ui.empty-state` | 80 |
| `ui.action-button` | 79 |
| `ui.metric-card` | 72 |
| `ui.status-badge` | 61 |
| `ui.section-card` | 60 |
| `ui.form-section` | 52 |
| `ui.filter-bar` | 52 |
| `ui.modal-livewire` | 51 |
| `ui.selector` | 31 |
| `ui.page-header` | 28 |
| `ui.collection-header` | 28 |
| `sistema-layout` | 26 |
| `ui.section-header` | 24 |
| `ui.card` | 21 |
| `validation-errors` | 17 |
| `ui.clinical-consultation-bar` | 14 |
| `ui.paginacion` | 12 |
| `ui.resultado-operacion-clinica` | 12 |
| `ui.callout` | 11 |
| `ui.drawer-livewire` | 10 |
| `residentes.navegacion-ficha` | 9 |
| `ui.clinical-clear-action` | 9 |
| `ui.calendario` | 8 |
| `secondary-button` | 8 |
| `ui.skeleton` | 8 |
| `ui.collection-results` | 8 |
| `input` | 7 |
| `ui.clinical-trend-window` | 7 |
| `button` | 6 |
| `ui.elimination-choice-group` | 6 |
| `ui.clinical-capture-choice` | 6 |
| `app-layout` | 6 |
| `ui.free-text-select` | 5 |
| `ui.resident-card` | 5 |

## Lectura correcta

- Reutilizar un modal/input no demuestra que todos sus consumidores conserven foco, validación o permisos.
- Un formulario de alta no implica que existan corrección, anulación, historial y exportación autorizados.
- El estado completo de UI requiere comprobar 1440/1280/1024/768/390 px, claro/oscuro, teclado, carga, vacío, error, éxito y permisos.
- La sesión auditada no tiene navegador habilitado. Se registra QA manual pendiente; los tests renderizados existentes solo acreditan sus fixtures.
- Consolidar estilos funcionalmente equivalentes en tokens/patrones y eliminar consultas de Blade por fases. No reemplazar el sistema visual aprobado.
