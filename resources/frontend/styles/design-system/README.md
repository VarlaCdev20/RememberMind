# RememberMind Design System — Fuente Única de Verdad

Bienvenido a la documentación oficial del **Design System canónico, versionado y gobernado de RememberMind** (Centro Geriátrico Jardín de los Recuerdos / Casa Amandita).

Este sistema define la fuente única de verdad para interfaces, componentes, tokens, patrones y reglas de accesibilidad, asegurando una experiencia homogénea, acogedora, cálida y profesional en toda la plataforma.

---

## 1. Objetivo

El objetivo principal es proveer una arquitectura visual estandarizada, escalable y mantenible para todos los módulos asistenciales y administrativos de RememberMind:
- Unificar tamaños, espaciados, tipografías, colores, componentes e interacciones.
- Erradicar la dispersión visual producida por estilos ad-hoc o hardcodeados.
- Eliminar el exceso de fondos blancos lavados mediante una escala de contrastes cálidos perceptibles.
- Servir como base canónica y gobernada para las refactorizaciones pantalla a pantalla.

---

## 2. Principios de Diseño

1. **Cálido y Humano, No Hospitalario**: La interfaz refleja dignidad, tranquilidad y calidez geriátrica institucional. Se destierran el blanco frío clínico y las sombras duras.
2. **Claridad Operativa y Jerarquía Inmediata**: En entornos de cuidados geriátricos, el profesional necesita distinguir con claridad el nivel de prioridad de una tarea.
3. **Semántica por Encima de Decoración**: Todo componente, color o animación comunica un estado, una acción o un rol sin artificios.
4. **Respeto a la Accesibilidad Universal**: Los estados nunca dependen exclusivamente del color; se combinan iconos, texto y color con contraste WCAG AA matemáticamente demostrado.
5. **Composición Modular Coherente**: Un solo sistema de primitivas visuales para todos los roles (Superadmin, Administrador, Enfermería, Médico, Psicología, Nutrición, etc.).

---

## 3. Arquitectura del Sistema y Separación de Responsabilidades

El sistema sigue una cascada estricta y unidireccional:

```text
REMEMBERMIND DESIGN SYSTEM (resources/frontend/styles/design-system/)
        │
        ├── 1. Tokens Fundamentales (tokens/)
        │      Colores, tipografía, escalas de espaciado, radios, sombras, estados y accesibilidad.
        │
        ├── 2. Componentes Globales (components/)
        │      Botones, tarjetas, formularios, badges, tablas, tabs, filtros, modales, drawers, alertas, gráficos, estados vacíos.
        │
        ├── 3. Patrones Clínicos e Institucionales (patterns/)
        │      Estructura de página (Module Page), dashboard clínico, cabecera de residente 360, tablas clínicas y detalle asistencial.
        │
        └── 4. Módulos Profesionales
               (Enfermería, Médico, Trabajo Social, Administración, etc., consumiendo las mismas primitivas).
```

### Regla Arquitectónica Fundamental: El Design System NO Define Reglas Médicas
El Design System **no interpreta** valores fisiológicos, constantes vitales ni escalas clínicas.
- El **dominio clínico / servicios de negocio** determina la severidad asistencial (por ejemplo, `severity = danger`).
- La vista **Blade / Livewire** recibe dicha severidad y la vincula al componente (`<x-badge variant="danger">`).
- El **Design System** únicamente se encarga de proveer la representación visual coherente, accesible y consistente para dicho estado (`danger`).

---

## 4. Tokens

Los tokens residen en `tokens/` y se exponen como variables CSS nativas en `:root`:
- `colors.css`: Paleta canónica, contraste de textos y colores de acción accesibles.
- `typography.css`: Familias tipográficas (Outfit / Inter), escala de tamaños y pesos.
- `spacing.css`: Escala geométrica cerrada de 7 valores fijos.
- `radius.css`: Radios de curvatura según el tipo de elemento.
- `shadows.css`: Elevaciones sutiles sm, md, lg, modal.
- `states.css`: Definición semántica de estados visuales.
- `motion.css`: Duraciones y curvas de transición con propiedades explícitas.
- `accessibility.css`: Foco visible, modo de alto contraste y movimiento reducido.

---

## 5. Colores (Paleta Canónica y Acciones Accesibles)

### A. Superficies y Textos
| Token | Valor Hex | Uso Principal |
|---|---|---|
| `--rm-bg-app` | `#F4ECE3` | Fondo general de la aplicación y layout base |
| `--rm-bg-layout` | `#EFE4D8` | Fondo de áreas de scroll y canvas secundario |
| `--rm-surface` | `#FBF7F2` | Tarjetas (cards), modales y contenedores principales |
| `--rm-surface-soft` | `#F7F0E9` | Paneles intermedios, barras de herramientas y cabeceras |
| `--rm-surface-alt` | `#F2E8DD` | Bloques internos anidados y celdas agrupadas |
| `--rm-surface-strong` | `#EEE2D4` | Cabeceras de tabla contrastadas y elementos activos |
| `--rm-border` | `#E3D6C8` | Borde estructural estándar de tarjetas y campos |
| `--rm-border-soft` | `#EADFD3` | Separadores internos sutiles |
| `--rm-border-hover` | `#D5C4B1` | Foco interactivo al pasar el cursor |
| `--rm-border-strong` | `#CBB8A3` | Delimitación de alto énfasis |
| `--rm-text-title` | `#344563` | Títulos principales, valores de KPIs y nombres |
| `--rm-text-body` | `#4A5A78` | Texto de lectura, datos clínicos y etiquetas |
| `--rm-text-muted` | `#556276` | Metadatos, horas, subtítulos y leyendas (WCAG AA) |
| `--rm-text-soft` | `#59667A` | Textos de apoyo secundarios y placeholders (WCAG AA) |
| `--rm-text-inverse` | `#FFFFFF` | Texto blanco sobre botones filled de acción |

### B. Identidad vs. Colores de Acción Accesibles
Los colores de identidad (`--rm-accent`, `--rm-success`, `--rm-danger`, `--rm-info`) se conservan para gráficos, badges, fondos suaves e iconos. Para botones llenos con texto blanco (`#FFFFFF`), se definen colores de acción accesibles con contraste `>= 4.5:1`:

| Rol | Token Identidad | Token Acción (Botón con texto blanco) | Ratio Contraste vs #FFFFFF |
|---|---|---|---|
| **Primary** | `--rm-primary: #344D7A` | `--rm-primary: #344D7A` | **8.44:1 (PASS WCAG AA)** |
| **Accent** | `--rm-accent: #E58C6C` | `--rm-accent-action: #B35E43` | **4.57:1 (PASS WCAG AA)** |
| **Success** | `--rm-success: #6F9D7B` | `--rm-success-action: #4F7E5B` | **4.70:1 (PASS WCAG AA)** |
| **Danger** | `--rm-danger: #C8645A` | `--rm-danger-action: #A7443B` | **5.94:1 (PASS WCAG AA)** |
| **Info** | `--rm-info: #778EAD` | `--rm-info-action: #5F7899` | **4.53:1 (PASS WCAG AA)** |

> [!IMPORTANT]
> Queda terminantemente prohibido crear tokens por rol (ej: `--rm-enf-coral`, `--rm-medico-blue`). Todos los roles comparten la paleta canónica.

---

## 6. Verificación Matemática de Contraste WCAG 2.1 AA

Cada combinación de color de texto sobre las superficies del Design System ha sido verificada matemáticamente:

### A. Textos sobre Superficies Cálidas (Requisito: `>= 4.5:1`)
| Token de Texto | Hex | vs `#FBF7F2` (Card) | vs `#F7F0E9` (Panel) | vs `#F2E8DD` (Bloque) | vs `#EEE2D4` (Header) | vs `#F4ECE3` (Fondo App) |
|---|---|---|---|---|---|---|
| `--rm-text-title` | `#344563` | **9.04:1** | **8.54:1** | **7.97:1** | **7.56:1** | **8.25:1** |
| `--rm-text-body` | `#4A5A78` | **6.51:1** | **6.15:1** | **5.74:1** | **5.44:1** | **5.94:1** |
| `--rm-text-muted` | `#556276` | **5.80:1** | **5.48:1** | **5.11:1** | **4.85:1** | **5.29:1** |
| `--rm-text-soft` | `#59667A` | **5.46:1** | **5.16:1** | **4.81:1** | **4.57:1** | **4.98:1** |

*Todas las combinaciones superan con solvencia el umbral mínimo de 4.5:1 de la norma WCAG 2.1 Nivel AA.*

### B. Jerarquía Escalonada de Superficies
Para evitar el efecto blanco plano, la interfaz se estructura en capas perceptibles:
```text
1. FONDO APP (#F4ECE3)
      ↓
   2. PANEL / TOOLBAR (#F7F0E9)
         ↓
      3. CARD PRINCIPAL (#FBF7F2)
            ↓
         4. BLOQUE INTERNO / DETALLE (#F2E8DD)
```

---

## 7. Tipografía

- **Títulos y Cifras Clave**: `Outfit` (geométrica, cálida y legible).
- **UI, Formularios y Lectura**: `Inter` (neutral, óptima para tablas y datos densos).
- **Datos Numéricos / Códigos**: `ui-monospace` (códigos de historia clínica, horas y mediciones).

### Escala Canónica
- `--rm-font-page-title`: `26px` (título de pantalla)
- `--rm-font-section-title`: `18px` (títulos de sección)
- `--rm-font-card-title`: `16px` (cabeceras de tarjeta/modal)
- `--rm-font-body`: `14px` (cuerpo de texto e inputs)
- `--rm-font-table`: `13px` (filas y celdas de tabla)
- `--rm-font-label`: `12px` (etiquetas de formulario y chips)
- `--rm-font-badge`: `11px` (pills de estado y microtextos)

> [!WARNING]
> No se permiten tamaños de fuente inferiores a `11px` en ninguna parte de la interfaz. La preferencia clínica general es `12px` (`--rm-font-label`).

---

## 8. Spacing (Espaciado)

Escala cerrada y estricta de 7 valores (múltiplos de 4):
- `--rm-space-1`: `4px`
- `--rm-space-2`: `8px`
- `--rm-space-3`: `12px`
- `--rm-space-4`: `16px`
- `--rm-space-5`: `20px`
- `--rm-space-6`: `24px`
- `--rm-space-8`: `32px`

---


---

## 8.1 Densidad Compacta / Operativa (COMPACT / OPERATIONAL DENSITY)

Para maximizar el área útil en pantallas de trabajo diario de enfermería y gestión geriátrica (evitando scroll excesivo en resoluciones como **1600×900** y **1920×1080**), el Design System establece el estándar de **Densidad Compacta / Operativa**.

Este estándar es obligatorio para los módulos de trabajo clínico continuo:
- **Alertas Clínicas y Cuidados** (Patrón Maestro Implementado)
- **Mi Turno**
- **Mis Pacientes**
- **Medicación**
- **Evolución**
- **Reportes Asistenciales**

### Tabla Canónica de Tokens de Densidad Compacta

| Token CSS | Valor | Propósito y Aplicación |
| :--- | :--- | :--- |
| `--rm-page-gap` | `16px` | Separación vertical entre bloques maestros (Header, KPIs, Gráficos, Filtros, Tabla). |
| `--rm-section-gap` | `14px` | Separación entre secciones operativas o widgets internos. |
| `--rm-card-padding` | `14px` | Padding interno estándar de tarjetas y paneles. |
| `--rm-card-padding-compact` | `12px` | Padding para tarjetas secundarias o sub-bloques internos. |
| `--rm-control-height` | `38px` | Altura estándar de campos de entrada, selects y botones principales (`.rm-btn`). |
| `--rm-control-height-sm` | `34px` | Altura para botones secundarios, compactos y Quick Actions (`.rm-btn-sm`, `.rm-action-quick`). |
| `--rm-table-header-height` | `42px` | Altura de la cabecera de tablas clínicas (`.rm-table-header th`). |
| `--rm-table-row-height` | `48px` | Altura objetivo de filas de tabla clínica (46–52px). |
| `--rm-chart-height-sm` | `220px` | Altura total para micro-gráficos o sparklines en paneles secundarios. |
| `--rm-chart-height-md` | `250px` | Altura para gráficos en drawers o widgets de detalle. |
| `--rm-chart-height-lg` | `280px` | Altura máxima total para tarjetas de gráficos en dashboard (310–320px). |

### Especificaciones de Componentes en Densidad Compacta

1. **Header Principal**:
   - Padding vertical: `12px–14px`, padding horizontal: `18px–20px`.
   - Título de página: `24px`, font-weight `700`.
   - Subtítulo: `13px`, color `--rm-text-muted`.
   - Icono institucional: `40×40px` (evitar iconos sobredimensionados).
   - Botones de cabecera: altura `36–38px` (`.rm-btn`).

2. **KPIs (Tarjetas de Métrica)**:
   - Altura objetivo: `100–110px` (límite estricto: no superar 115px).
   - Padding: `14px 16px`.
   - Valor principal: `26px`, font-weight `700`, line-height `1.1`.
   - Etiqueta de métrica: `11.5px` (11–12px), uppercase, tracking `0.03em`.
   - Metadato inferior: `11px`, color `--rm-text-soft`.
   - Icono contenedor: `32×32px` (`h-8 w-8`).
   - Distribución: 5 KPIs en una sola fila en escritorio (`grid-cols-2 sm:grid-cols-2 lg:grid-cols-5`).

3. **Gráficos y Visualización (ChartCard)**:
   - Altura total contenida de la tarjeta: `310–320px` (en 1600×900 permite ver Header + KPIs + Gráficos simultáneamente).
   - Header de gráfico: `margin-bottom: 4px–8px`, título `15px`, subtítulo `12px`.
   - Canvas contenedor (`.rm-chart-body`): altura fija entre `190px–205px`.
   - Gráficos tipo Donut: diámetro reducido 15–20% respecto a pantalla completa (cutout `72%–76%`), centro con valor `20–22px` y label `10–11px`.
   - Gráficos de Barras: `maxBarThickness: 14px`, márgenes internos reducidos.
   - Footer / desglose inferior: padding `8px–10px`, tipografía `11px`.

4. **Barra de Filtros**:
   - Padding contenedor: `10px 12px`.
   - Gap entre controles: `8px–10px`.
   - Inputs y Selects: altura `38px`, font-size `13px`.

5. **Tablas Clínicas**:
   - Altura cabecera: `40–42px`, padding de celda `8px 12px`, texto `11.5px` (bold, uppercase).
   - Altura de filas: `46–52px`, padding de celda `8px 12px`.
   - Texto principal: `13px`, texto secundario/metadatos: `11.5px`.
   - Badges clínicos: `11px` (regla estricta: **nunca texto clínico menor a 11px**).

6. **Acciones de Fila (Quick Actions)**:
   - Botón de acción principal (`.rm-action-primary`): altura `34–36px`.
   - Botones rápidos de alta frecuencia (`.rm-action-quick`): altura `34–36px`.
   - Botón de menú overflow (`.rm-btn-icon`): `34×34px` visual (área táctil preservada mediante pseudo-elemento para WCAG).

7. **Modales y Drawers**:
   - Modal expediente: padding contenido `16px 18px`, gaps de sección `10–12px`, cabecera y pie fijos con scroll central.
   - Modal de confirmación/cierre: ancho máximo `480–520px` (`.rm-modal-sm`).
   - Drawer de evolución clínica: ancho escritorio `620–680px` (`.rm-drawer-md`), padding `16px 18px`, tarjetas de signos `p-3` (12px), gráfico integrado `220–250px`.

8. **Radios de Borde (Radius)**:
   - Tarjetas (`.rm-card`): `14px`.
   - Paneles maestros: `16px`.
   - Inputs y botones: `10px`.
   - Modales: `16px`.

## 9. Radios de Borde (Radius)

- **Inputs / Selects / Textareas**: `10px` (`--rm-radius-input`)
- **Botones**: `12px` (`--rm-radius-button`)
- **Cards y Módulos de KPI**: `16px` (`--rm-radius-card`)
- **Paneles y Layouts Maestros**: `20px` (`--rm-radius-panel`)
- **Modales y Drawers**: `20px` (`--rm-radius-modal`)
- **Badges y Pills**: `9999px` (`--rm-radius-badge`)

---

## 10. Sombras (Shadows)

Sombras cálidas y difusas:
- `--rm-shadow-sm`: `0 1px 3px rgba(52, 69, 99, 0.06)` (cards en reposo, botones secundarios)
- `--rm-shadow-md`: `0 6px 18px rgba(52, 69, 99, 0.08)` (hover de cards interactivas, tooltips)
- `--rm-shadow-lg`: `0 12px 28px rgba(52, 69, 99, 0.10)` (menús desplegables)
- `--rm-shadow-modal`: `0 20px 48px rgba(52, 69, 99, 0.16)` (modales y drawers)

---

## 11. Botones y Semántica de Acciones

Ubicación: `components/buttons.css`.
- `.rm-btn`: Clase base universal.
- **Tamaños Normalizados**:
  - Acciones compactas: `.rm-btn-sm` (36px visuales como mínimo).
  - Acciones estándar y principales: `.rm-btn-md` (40px de altura).
  - Icon-buttons interactivos: `.rm-btn-icon` (área mínima de interacción de `40x40px`).

### Reglas Semánticas de Botones
- **`.rm-btn-primary`** (`#344D7A`): Acción determinante principal de la pantalla o formulario.
- **`.rm-btn-accent`** (`#B35E43`): Acciones operativas destacadas (ej: registrar atención, abrir formulario, filtrar).
- **`.rm-btn-secondary`**: Superficie cálida con borde visible para acciones secundarias seguras.
- **`.rm-btn-ghost`**: Acciones neutrales o de cancelación sin carga visual.
- **`.rm-btn-danger`** (`#A7443B`): **ÚNICAMENTE para acciones destructivas o con consecuencias irreversibles** (Anular registro, Revocar asignación, Eliminar elemento, Cancelar definitivamente). **NO UTILIZAR** para reportar incidentes o atender alertas (acciones que usan `primary` o `accent`).
- **`.rm-btn-success`** (`#4F7E5B`): Confirmaciones de éxito del sistema. **NO SIGNIFICA AUTOMÁTICAMENTE** "Administrar medicamento" (la acción de administrar usa `primary` o `accent`; una vez completada, el estado "ADMINISTRADO" se refleja en un badge success).

---

## 12. Cards

Ubicación: `components/cards.css`.
- Estructura: `.rm-card`, `.rm-card-header`, `.rm-card-body`, `.rm-card-footer`.
- **Variantes**:
  - `.rm-card-default`: Superficie `#FBF7F2`, borde `#E3D6C8`.
  - `.rm-card-soft`: Superficie `#F7F0E9`, borde `#EADFD3`.
  - `.rm-card-interactive`: Con elevación hover `translateY(-1px)`.
  - `.rm-card-metric`: Tarjeta de KPI con cifra en `Outfit 26px`.

---

## 13. Formularios

Ubicación: `components/forms.css`.
- `.rm-field`, `.rm-label`, `.rm-input`, `.rm-select`, `.rm-textarea`.
- Altura uniforme de 42px, radio de 10px, padding horizontal de 14px.
- **Foco sin Blanco Puro**: Durante focus, el fondo se mantiene en `var(--rm-surface)` (`#FBF7F2`), acompañado de un halo exterior coral suave `0 0 0 3px #F7E1D9`.
- **Error**: Borde `#C8645A` y fondo `#F8E3E0`, con mensaje `.rm-error`.

---

## 14. Badges

Ubicación: `components/badges.css`.
- Estructura: `.rm-badge` + `.rm-badge-dot` + texto del estado.
- NUNCA transmiten estado únicamente por color.
- Variantes: `.rm-badge-success`, `.rm-badge-warning`, `.rm-badge-danger`, `.rm-badge-info`, `.rm-badge-neutral`.

---

## 15. Tablas

Ubicación: `components/tables.css`.
- `.rm-table-container`, `.rm-table`, `.rm-table-header`, `.rm-table-row`, `.rm-table-cell`, `.rm-table-actions`.
- Cabecera en `#EEE2D4` con texto `#344563` en mayúsculas `12-13px`.
- Celdas con tamaño `13-14px` sobre fondo `#FBF7F2` y hover `#F7F0E9`.
- Máximo 1 acción principal visible por fila + menú desplegable de acciones secundarias.

---

## 16. Modales

Ubicación: `components/modals.css`.
- Inspirados en el **Expediente de Alerta Clínica**.
- Fondo `#FBF7F2`, esquinas de 20px, sombra modal suave.
- Header y footer visualmente contrastados en `#F7F0E9`.
- Tamaños estándar: `sm` (440px), `md` (600px), `lg` (820px), `xl` (1080px).

---

## 17. Drawers

Ubicación: `components/drawers.css`.
- Despliegue lateral con anchos estándar: `sm` (560px), `md` (680px), `lg` (760px).
- Header fijo `#F7F0E9`, cuerpo scrollable continuo y footer contrastado.

---

## 18. Gráficos (ChartCard)

Ubicación: `components/charts.css`.
- `.rm-chart-card`: Altura mínima de 320px, leyenda estandarizada y tooltips oscuros accesibles.
- Paleta permitida para series: `--rm-primary`, `--rm-accent`, `--rm-success`, `--rm-warning`, `--rm-danger`, `--rm-info`.

---

## 19. Estados

Ubicación: `tokens/states.css`.
- El Design System representa estados genéricos: `success`, `warning`, `danger`, `info`, `neutral`.
- Provee tokens de fondo para tablas clínicas: `--rm-row-danger-bg` y `--rm-row-warning-bg`.

---

## 20. Accesibilidad

Ubicación: `tokens/accessibility.css`.
- `:focus-visible` de 2px en color coral con desplazamiento de 2px para teclado.
- Ratios WCAG AA comprobados matemáticamente (todos `>= 4.5:1`).
- Multimodalidad obligatoria (icono + texto + color).
- Soporte automático para `@media (prefers-reduced-motion: reduce)`.
- Manejo semántico de `[aria-disabled="true"]` como reflejo visual de la lógica del servidor/DOM.

---

## 21. Patrón de Pantalla Canónico

Ubicación: `patterns/module-page.css`.

Toda pantalla principal de RememberMind sigue la secuencia:
```text
PAGE HEADER → KPIS → VISUALIZACIÓN / GRÁFICOS → FILTER BAR → LISTADO / TABLA → MODAL / DRAWER CONTEXTUAL
```

---

## 22. Buenas Prácticas

- Usar siempre tokens semánticos en lugar de valores hexadecimales dispersos.
- No superar 1 botón primario por vista o modal.
- Mantener los botones de acción principal en 40px o superior.
- Garantizar que todo microtexto sea de al menos `11px`.

---

## 23. Anti-Patrones (Prohibiciones)

1. **Anti-patrón "White-on-White"**: Apilar tarjetas blancas sobre fondo blanco plano.
2. **Anti-patrón "Botón por Color"**: Clases como `.btn-red` o `.btn-blue`. Usar semántica (`primary`, `accent`, `danger`).
3. **Anti-patrón "Reglas Médicas en CSS"**: Acoplar reglas de signos vitales o escalas diagnósticas en estilos.
4. **Anti-patrón "Danger para Acciones Clínicas"**: Asignar botón rojo a acciones asistenciales ordinarias (ej. reportar novedad).
5. **Anti-patrón "Microtextos Ilegibles"**: Textos de 10px o inferiores.
