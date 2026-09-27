# RememberMind — Arquitectura Frontend Canónica

**Versión:** 1.0 (Fase 1: Consolidación)  
**Estado:** VIGENTE (Norma Arquitectónica)  
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
*   **Responsabilidad:** Contrato visual formal del sistema (colores con contraste WCAG AA $\ge 4.5:1$, escala tipográfica Inter/Outfit, espaciados de 8pt, radios semánticos, sombras difusas y presets de Chart.js).
*   **Tokens Canónicos Requeridos:**
    *   `--rm-primary`: `#B05D40` (Terracota institucional; contraste 4.68:1 con blanco).
    *   `--rm-surface`: `#E8DFD5` (Superficie canónica beige cálido).
    *   `--rm-border`: `#A49384` (Borde estructural estándar).
    *   `--rm-text-title`: `#2E241F` (Jerarquía de títulos y cifras).
    *   `--rm-text-muted`: `#6F5F53` (Metadatos, fechas y textos secundarios).
    *   `--rm-accent`: `#B05D40` (Terracota institucional para acciones y foco).
    *   `--rm-danger`: `#A7443B` (Coral para acciones destructivas irreversibles y alertas críticas).

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
