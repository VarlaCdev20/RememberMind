---
title: "RememberMind — Arquitectura Frontend Canónica"
status: DEPRECATED
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

> DEPRECATED como norma: contiene objetivos/rutas y criterios visuales anteriores. Consultar [estructura canónica](README.md), [resources/AGENTS](../../resources/AGENTS.md), [contrato visual vigente](../frontend/CONTRATO_VISUAL_UX_UI.md) y [tokens implementados](../../resources/frontend/styles/design-system/README.md). El contenido siguiente se conserva como antecedente, no obliga a migrar a Features/Pages ni a cambiar UX.

# RememberMind — Arquitectura Frontend Canónica

**Versión:** 1.0 (Fase 1: Consolidación)
**Estado:** DEPRECATED como norma; se conserva como antecedente de transición.
**Ámbito:** Capa de Presentación, Sistema de Diseño, Componentes Blade, Livewire 4 y Maquetación.

---

## 1. Principios de Diseño Arquitectónico

La arquitectura frontend de RememberMind se fundamenta en dos paradigmas complementarios:
*   **Component-Based Architecture:** La interfaz de usuario se construye a partir de piezas pequeñas, independientes, encapsuladas y con contratos semánticos explícitos.
*   **Feature-Based Organization:** La lógica funcional y de negocio reactiva se agrupa por dominio de capacidades clínicas y operativas (no fragmentada ni duplicada arbitrariamente por rol).

### Jerarquía de Capas (Top-Down)

```text
┌────────────────────────────────────────────────────────┐
│                        SHELL                           │ (Layouts institucionales: AppShell, navegación y topbar)
├────────────────────────────────────────────────────────┤
│                        PAGES                           │ (Endpoints HTTP completos, coordinación de alto nivel)
├────────────────────────────────────────────────────────┤
│                       FEATURES                         │ (Capacidades de negocio reactivas y autónomas)
├────────────────────────────────────────────────────────┤
│                       PATTERNS                         │ (Composiciones visuales recurrentes: Cabeceras, Gráficos)
├────────────────────────────────────────────────────────┤
│                          UI                            │ (Primitivas visuales genéricas de presentación pura)
├────────────────────────────────────────────────────────┤
│                     DESIGN SYSTEM                      │ (Tokens, accesibilidad WCAG AA, tipografía, CSS base)
└────────────────────────────────────────────────────────┘
```

---

## 2. Estado Actual vs. Arquitectura Objetivo

| Capa | Estado Actual (Real en Disco) | Arquitectura Objetivo | Brecha / Transición |
|---|---|---|---|
| **Design System** | Canónico en `design-system/`, coexiste con `styles/components/` legados. | Fuente única de estilos y tokens en `resources/frontend/styles/design-system/`. | Fase 1: Desacoplar entrada duplicada de Vite y planificar absorción de legados. |
| **UI** | 13 componentes genéricos en `components/ui/` conviviendo con 6 widgets de dominio y 4 patterns. | `components/ui/` contiene exclusivamente primitivas de presentación pura agnósticas de dominio. | Fase 1: Mapear extracción de widgets y patterns sin romper referencias. |
| **Patterns** | Estilos en `design-system/patterns/`; componentes Blade dispersos en `ui/` y `residentes/`. | `components/patterns/` centraliza composiciones recurrentes (`resident-summary`, `dashboard-kpis`, etc.). | Fase 2: Trasladar formalmente los patterns a su directorio definitivo. |
| **Features** | Pestañas incrustadas como partials masivos en monolitos (`FichaPaciente`, `UsuariosPanel`). | Componentes Livewire autónomos bajo `app/Frontend/Livewire/Features/<Dominio>/`. | Fase 2: Extraer signos, medicación y documentos de `FichaPaciente`. |
| **Pages** | Clases en `app/Frontend/Livewire/` agrupadas por rol (`Medico`, `Enfermeria`, `Superadministrador`). | Clases bajo `app/Frontend/Livewire/Pages/<Contexto>/` enfocadas solo en ensamblado de Features. | Fase 3: Alinear nomenclatura y rutas. |
| **Shell** | Dos layouts separados (`sistema.blade.php` y `enfermeria.blade.php`) con duplicación de CSS y CDNs. | `AppShell` unificado con configuración dinámica de sidebar y header según el rol activo. | Fase 3: Reemplazar duplicación inline manteniendo variantes legítimas de turno. |

---

## 3. Especificación de Capas

### 3.1 Design System (`resources/frontend/styles/design-system/`)
*   **Responsabilidad:** Contrato visual canónico formal del sistema (fundamentos normalizados, escala tipográfica Nunito Sans empaquetada localmente, paleta institucional cálida con contraste WCAG AA $\ge 4.5:1$, espaciados, sizing de controles, radios, sombras orgánicas, layout, z-index y presets de Chart.js).
*   **Tipografía Oficial:**
    *   **Familia Canónica:** `Nunito Sans` con fallback `"Segoe UI", Arial, sans-serif`.
    *   **Empaquetado:** Distribuida localmente vía paquete npm `@fontsource/nunito-sans` (pesos 400, 500, 600, 700, 800, 900) e importada en `fuentes.css` (cero dependencias de CDN externos en tiempo de ejecución).
    *   **Escala de Tamaños y Pesos:**
        *   *Page Title:* 32px (`--rm-font-size-title`), peso 800 (`--rm-font-weight-extrabold`).
        *   *Section Title:* 24px (`--rm-font-size-section`), peso 800.
        *   *Card Title:* 18px (`--rm-font-size-card-title`), peso 700 (`--rm-font-weight-bold`).
        *   *Body:* 16px (`--rm-font-size-body`), peso 500 (`--rm-font-weight-medium`).
        *   *Table Cell:* 14px (`--rm-font-size-table`), peso 600 (`--rm-font-weight-semibold`).
        *   *Form Label:* 14px (`--rm-font-size-label`), peso 700 (`--rm-font-weight-bold`).
        *   *Metadata / Caption:* 13px (`--rm-font-size-meta`), peso 600 (`--rm-font-weight-semibold`).
        *   *Button:* 15px (`--rm-font-size-button`), peso 800 (`--rm-font-weight-extrabold`).
        *   *KPI Metric:* 32px (`--rm-font-size-kpi`), peso 800 (`--rm-font-weight-extrabold`).
    *   **Regla de Mayúsculas:** Prohibido el uso de `uppercase` en nombres de personas, correos electrónicos, párrafos y textos extensos; reservado exclusivamente a badges compactos o metadatos de categoría.

*   **Paleta Institucional Oficial y Colorimetría:**
    *   **Identidad:** Beige medio/oscuro + Café tierra + Terracota institucional + Verde oliva claro/medio.
    *   **Reglas cromáticas estrictas:**
        *   *Azul:* Uso exclusivo informativo/clínico neutro (`--rm-info`).
        *   *Rojo:* Acciones destructivas irreversibles y alertas clínicas críticas (`--rm-danger`).
        *   *Cobre/Tierra:* Advertencias y estados de precaución (`--rm-warning`).
        *   *Prohibido:* Amarillo como color de advertencia principal.
        *   *Prohibido:* Blanco puro (`#FFFFFF`) como fondo general dominante de la aplicación.
        *   *Prohibido:* Superficies masivas café casi negro.

*   **Arquitectura de Tokens en 3 Niveles:**
    *   **Nivel 1 (Primitivos - `tokens/primitives.css`):**
        *   Describen **color y valor puro**, nunca función o semántica.
        *   *Earth:* `--rm-earth-900` (`#33271F`) a `--rm-earth-400` (`#A68B72`).
        *   *Beige:* `--rm-beige-600` (`#9E8B77`) a `--rm-beige-100` (`#DACBBB`).
        *   *Terracotta:* `--rm-terracotta-700` (`#8F4935`) a `--rm-terracotta-300` (`#D79A82`).
        *   *Olive:* `--rm-olive-700` (`#627052`) a `--rm-olive-200` (`#C9CFBB`).
        *   *Blue:* `--rm-blue-600` (`#46677F`), `--rm-blue-500` (`#557990`), `--rm-blue-300` (`#9BB0C0`).
        *   *Red:* `--rm-red-600` (`#944239`), `--rm-red-500` (`#A94F43`), `--rm-red-300` (`#D39A94`).
        *   *Copper:* `--rm-copper-600` (`#9B6244`), `--rm-copper-500` (`#AF744F`), `--rm-copper-300` (`#D4AF97`).
    *   **Nivel 2 (Semánticos - `tokens/colors.css`):**
        *   Describen **función en la interfaz**:
            *   *Fondos:* `--rm-bg-app` (`#DACBBB`), `--rm-bg-shell` (`#DACBBB`), `--rm-surface` (`#D0C0AE`), `--rm-surface-soft` (`#C2B09A`), `--rm-surface-raised` (`#E6DDD3`).
            *   *Tipografía:* `--rm-text-primary` (`#33271F`), `--rm-text-body` (`#4A382F`), `--rm-text-secondary` (`#5F4938`), `--rm-text-muted` (`#5F4938`), `--rm-text-on-primary` (`#F8F2EC`).
            *   *Bordes y separadores:* `--rm-border` (`#AD9983`), `--rm-border-soft` (`#C2B09A`), `--rm-divider` (`rgba(74, 56, 47, 0.16)`).
            *   *Acción Principal:* `--rm-action-primary` (`#B76445`), `--rm-action-primary-hover` (`#9D5339`), `--rm-action-primary-active` (`#8F4935`), `--rm-action-primary-soft` (`#F3E2DB`).
            *   *Navegación:* `--rm-nav-bg` (`#D0C0AE`), `--rm-nav-hover` (`#C2B09A`), `--rm-nav-selected` (`#B76445`).
            *   *Estados:* `--rm-success` (`#627052`), `--rm-info` (`#46677F`), `--rm-warning` (`#9B6244`), `--rm-danger` (`#944239`).
    *   **Nivel 3 (Componentes):**
        *   Convenciones estandarizadas para componentes específicos: `--rm-button-*`, `--rm-input-*`, `--rm-card-*`, `--rm-table-*`, `--rm-modal-*`.

*   **Espaciado y Sizing (`tokens/spacing.css` y `tokens/sizing.css`):**
    *   *Espaciado geométrico:* `--rm-space-0` (0px), `--rm-space-1` (4px), `--rm-space-2` (8px), `--rm-space-3` (12px), `--rm-space-4` (16px), `--rm-space-5` (20px), `--rm-space-6` (24px), `--rm-space-8` (32px), `--rm-space-10` (40px), `--rm-space-12` (48px), `--rm-space-16` (64px).
    *   *Sizing de Controles:*
        *   Default: **44px** (`--rm-control-md`) para inputs, selects, botones estándar y filtros reactivos.
        *   Compacto: **38px** (`--rm-control-sm`) para barras de herramientas densas y tablas.
        *   Acción prioritaria: **48px** (`--rm-control-lg`) para CTAs principales.
    *   *Sizing de Iconos:* 16px (`sm`), 20px (`md`), 24px (`lg`), 32px (`xl`).
    *   *Sizing de Avatares:* 32px (`sm`), 40px (`md`), 56px (`lg`), 72px (`xl`).

*   **Radios y Sombras (`tokens/radius.css` y `tokens/shadows.css`):**
    *   *Escala de Radios:* `--rm-radius-sm` (8px), `--rm-radius-md` (12px), `--rm-radius-lg` (16px), `--rm-radius-xl` (20px), `--rm-radius-pill` (999px).
    *   *Convención de Radios:* Inputs → `md` (12px), Botones → `md` (12px), Cards → `lg` (16px), Modales → `xl` (20px), Badges/Chips → `pill` (999px).
    *   *Escala de Sombras:* `--rm-shadow-none`, `--rm-shadow-sm` (sutil cálida), `--rm-shadow-md` (elevación media), `--rm-shadow-lg` (paneles/drawers), `--rm-shadow-overlay` (modales).
    *   *Convención de Sombras:* Inputs → `none`, Cards → `sm`, Cards hover → `md`, Dropdowns → `md`, Drawers → `lg`, Modales → `overlay`.

*   **Layout y Z-Index (`tokens/layout.css` y `tokens/z-index.css`):**
    *   *Layout:* `--rm-sidebar-width` (260px), `--rm-sidebar-collapsed` (76px), `--rm-topbar-height` (64px), `--rm-page-max-width` (1400px), `--rm-page-padding-x` (24px), `--rm-page-padding-y` (24px), `--rm-content-gap` (24px). Drawers (`sm: 360px`, `md: 480px`, `lg: 640px`) y Modales (`sm: 420px`, `md: 560px`, `lg: 720px`, `xl: 900px`).
    *   *Z-Index:* Escala predecible: base (`0`), sticky (`100`), dropdown (`200`), sidebar (`300`), topbar (`400`), drawer (`500`), modal (`600`), toast (`1000`), tooltip (`1200`).

*   **Gráficos Institucionales (`tokens/chart-colors.css`):**
    *   Serie categórica: Terracota (`--rm-chart-1`), Oliva medio (`--rm-chart-2`), Azul (`--rm-chart-3`), Café (`--rm-chart-4`), Terracota suave (`--rm-chart-5`), Oliva suave (`--rm-chart-6`), Cobre (`--rm-chart-7`), Tierra medio (`--rm-chart-8`). Retícula cálida (`--rm-chart-grid`), etiquetas café (`--rm-chart-label`) y tooltip oscuro (`--rm-chart-tooltip-bg`). Prohibido amarillo puro.

*   **Accesibilidad y Contraste (WCAG AA $\ge 4.5:1$):**
    *   Verificación matemática rigurosa en modo claro:
        *   `--rm-text-primary` (`#33271F`) sobre `--rm-bg-app` (`#DACBBB`): **9.13:1** (Pasa AAA).
        *   `--rm-text-body` (`#4A382F`) sobre `--rm-bg-app` (`#DACBBB`): **6.98:1** (Pasa AAA).
        *   `--rm-text-secondary` (`#5F4938`) sobre `--rm-bg-app` (`#DACBBB`): **5.30:1** (Pasa AA $\ge 4.5:1$).
        *   `--rm-text-on-primary` (`#F8F2EC`) sobre `--rm-action-primary` (`#B76445`): **4.55:1** (Pasa AA).
    *   Verificación matemática rigurosa en modo oscuro:
        *   `--rm-text-primary` (`#F8F2EC`) sobre `--rm-bg-app` (`#262422`): **13.92:1** (Pasa AAA).
    *   Soporte asistivo obligatorio: Foco visible de 2px con offset (`:focus-visible`), respeto estricto a `prefers-reduced-motion: reduce` y clase utilitaria `.rm-sr-only`.

### 3.2 UI Primitivas (`resources/views/components/ui/`)
*   **Responsabilidad:** Presentación genérica reutilizable.
*   **Regla de Oro:** **Cero conocimiento de dominio**. Prohibido importar modelos (`App\Models\*`), ejecutar consultas a base de datos (`query()`, `where()`, `find()`) o verificar permisos específicos de negocio (`@can('atenciones.crear')`).
*   **Componentes UI Canónicos:**
    *   `action-button` (Botón con variantes semánticas y estados de carga).
    *   `metric-card` (Tarjeta KPI con icono y valor numérico).
    *   `status-badge` (Mapeo automático de cadenas de estado a badges accesibles).
    *   `modal-livewire` (Modal accesible flotante con scroll central).
    *   `drawer-livewire` (Panel lateral deslizable para consulta).
    *   `filter-bar` (Contenedor normalizado de filtros de 38px).
    *   `page-header` (Cabecera de página con slot de acciones).
    *   `empty-state` (Indicador de listado vacío).
    *   `section-card` (Contenedor agrupador de información).
    *   `form-section` (Estructura de formulario de dos columnas).
    *   `sparkline` (Micrográfico SVG para tendencias de signos).
    *   `toast` y `sweetalert` (Retroalimentación al usuario).

### 3.3 Patterns (`resources/views/components/patterns/`)
*   **Responsabilidad:** Composiciones visuales recurrentes de componentes UI que resuelven una necesidad recurrente de maquetación:
    *   `ResidentSummary`: Cabecera 360 del residente (avatar, cama, datos biométricos, badges de caída/alergia).
    *   `ExpedienteNavigation`: Barra horizontal de acceso continuo entre secciones del residente.
    *   `EncabezadoDashboard`: Bienvenida, estado de guardia y fecha.
    *   `KpisDashboard`: Grilla adaptativa de métricas institucionales.
    *   `GraficoDashboard` / `SeccionGraficosDashboard`: Contenedores normalizados de gráficos con leyenda y filtros.

### 3.4 Features (`app/Frontend/Livewire/Features/` y `resources/views/livewire/features/`)
*   **Responsabilidad:** Unidades autónomas de valor de negocio y experiencia interactiva:
    *   Manejan estado propio (`wire:model`, eventos, paginación local).
    *   Consumen servicios de backend (`app/Backend/Modulos/*/Servicios/`).
    *   Autorizan mediante permisos explícitos de Spatie.
    *   **Regla Fundamental:** **Las Features no se duplican por rol**. La Feature `SignosVitalesPanel` es la misma para Medicina que para Enfermería; las distinciones operativas se controlan por permisos de visibilidad/edición, no clonando código.

### 3.5 Pages (`app/Frontend/Livewire/Pages/` o Controllers)
*   **Responsabilidad:** Punto de entrada acoplado al enrutamiento HTTP (`routes/web.php`):
    *   Define el layout/shell a utilizar.
    *   Resuelve el modelo principal por route-model binding (ej. `cod_residente`).
    *   Ensambla y orquesta las Features necesarias mediante slots o directivas de plantilla.
    *   Mantiene mínima lógica interna (delega en Features y Services).

### 3.6 Shell (`resources/views/layouts/`)
*   **Responsabilidad:** Marco estructural envolvente (HTML, head, viewport, scripts Vite, conmutador de tema, drawer móvil, topbar y sidebar).
*   **Evolución hacia `AppShell`:**
    *   Unificación del núcleo común (evitando duplicar estilos y librerías).
    *   Sidebar y Topbar configurables dinámicamente según el contexto del usuario autenticado (Enfermería asistencial vs. Sistema administrativo).

---

## 4. Matriz de Dependencias y Acoplamiento

### 4.1 Dependencias Permitidas
*   `Blade UI` $\longrightarrow$ Consume únicamente tokens CSS y slots/props.
*   `Patterns` $\longrightarrow$ Consumen componentes UI y estilos del Design System.
*   `Features (Livewire)` $\longrightarrow$ Consumen Backend Services (`App\Backend\Modulos\*`), Modelos Eloquent (`App\Models\*`), FormRequests/Validaciones y renderizan vistas con componentes UI/Patterns.
*   `Pages` $\longrightarrow$ Consumen Features y asignan el Shell correspondiente.

### 4.2 Dependencias Prohibidas
*   **Prohibido:** Componentes en `components/ui/` importando modelos o accediendo a la base de datos.
*   **Prohibido:** Clases en `app/Frontend/Livewire/` llamando a `app/Services/` o `app/Livewire/` (ambos directorios están vedados y erradicados).
*   **Prohibido:** Componentes Blade conteniendo reglas médicas hardcodeadas (ej. umbrales de presión o escalas diagnósticas codificados en condicionales Blade).
*   **Prohibido:** Estilos CSS asignando significado clínico destructivo al color rojo fuera de acciones irreversibles (`.rm-btn-danger`).

---

## 5. Convenciones de Nomenclatura y Enlace Livewire ↔ Blade

1.  **Claves Primarias:** Toda entidad de usuarios utiliza `cod_usuario` (string). La entidad central clínica utiliza `cod_residente` (string, jamás `cod_am`).
2.  **Registro de Componentes:** Los componentes incrustables como tag `<livewire:... />` deben estar registrados formalmente en `AppServiceProvider::boot()`. Los componentes que actúan como Page completa se enrutan mediante su clase FQCN en `routes/web.php`.
3.  **Vistas Livewire:** Toda vista Livewire debe corresponder biunívocamente a su dominio funcional. Las vistas no deben dispersarse por carpetas de rol si pertenecen al mismo dominio de negocio.

---

## 6. Estrategia Progresiva de Migración (Roadmap)

```text
FASE 1 (Actual - Consolidación)
├── Documentar la arquitectura objetivo y delimitar responsabilidades.
├── Eliminar la doble compilación de Vite en design-system/index.css.
├── Incorporar tests arquitectónicos automatizados para components/ui.
└── Mapear la reubicación conceptual de widgets y patterns.

FASE 2 (Desacoplamiento de Features Críticas)
├── Extraer pestañas monolíticas de FichaPaciente (Signos, Medicación, Documentos) a Features autónomas.
├── Migrar modales incrustados hacia componentes modales Livewire individuales.
└── Mover formalmente los Patterns desde components/ui/ hacia components/patterns/.

FASE 3 (Unificación de Shell y Estandarización de Pages)
├── Consolidar layouts/sistema.blade.php y layouts/enfermeria.blade.php en un AppShell parametrizado.
├── Eliminar inyecciones inline y CDNs redundantes (unificar Chart.js y Phosphor).
└── Reubicar los widgets de dashboard en features/dashboard/.
```
