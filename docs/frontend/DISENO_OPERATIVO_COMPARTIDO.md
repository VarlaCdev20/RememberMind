# Diseño operativo compartido

Decisión de la propietaria del 06/11/2026: extender el diseño aprobado del directorio de personal mediante componentes reutilizables.

La implementación común está en `resources/frontend/styles/design-system/patterns/operational-design.css`, importada desde el índice del Design System. Consume tokens existentes; no incorpora otra paleta ni dependencias.

## Componentes

| Componente | Contrato visual |
|---|---|
| `ui.collection-header` / `ui.page-header` | Superficie clara diferenciada, título en mayúsculas, tipografía Inter existente, centrado del texto y acciones adaptables. |
| `ui.filter-bar` | Tinte del rol, campos claros, labels en mayúsculas, foco visible y controles táctiles. Conserva la distribución requerida por cada vista. |
| `.rm-table` / `.rm-data-table` | Cabecera compacta, centrada y en mayúsculas; datos sin transformación; sin divisores horizontales. Se conservan los estados semánticos y la adaptación de cada tabla. |
| `ui.table-surface` | Una superficie con relieve para tabla y pie. El slot `footer` permite integrar la paginación; sin recuadros anidados. No oculta los menús desplegables. |
| `ui.paginacion` | Controles redondeados, página activa con acento del rol, teclado y tamaño táctil. Conserva nombres de paginadores, filtros y acciones Livewire/URL. |

Uso de superficie con pie:

```blade
<x-ui.table-surface>
    <table class="rm-table">...</table>
    <x-slot:footer>
        <x-ui.paginacion :paginator="$resultados" mode="livewire" />
    </x-slot:footer>
</x-ui.table-surface>
```

No anidar `section-card` o `table-wrapper` para reproducir la misma superficie. Las variantes locales deben resolver distribución o densidad, sin copiar tipografía y paleta compartidas.

Las cabeceras con exportación agrupan sus acciones en iconos. El nombre de cada acción aparece al pasar el puntero o enfocarla con el teclado. Cada icono conserva un área de pulsación de 44 × 44 px y las acciones principales mantienen su acento.

## Alcance comprobado en código

La búsqueda de componentes y clases comunes localizó 109 archivos Blade consumidores en los doce módulos: Admisiones 2, Alertas 6, ApoyoDecision 3, Clinica 21, Cuidados 8, Identidad 11, Institucion 17, Medicacion 7, Reportes 15, Residentes 8, Social 5 y Valoraciones 6. Es un inventario de archivos consumidores, no un número de rutas ni una certificación visual individual.

Se conservan los acentos de cada rol: Gerencia morado, Enfermería verde y las restantes variantes institucionales. Los colores de éxito, advertencia y alerta mantienen su significado. No se cambian campos, autorizaciones, datos, reglas clínicas ni navegación.

Los gráficos, formularios de captura y cabeceras de bienvenida conservan sus componentes específicos. La capa común se aplica a sus elementos operativos equivalentes; no convierte una gráfica o un formulario en una tabla.

## Verificación

- `DisenoOperativoCompartidoTest`: renderizado Blade, slots, identificadores, labels y paginación nombrada.
- `operational-design.test.js`: diez roles, dos temas y anchos 1440/1280/1024/768/390; estados semánticos, teclado y desbordamiento.
- Pruebas de integración del inicio y directorio de Gerencia; regresión de dashboards, paginación, filtros, navegación y lectura cognitiva.

Las matrices de componentes no sustituyen el recorrido de todas las rutas con todos sus estados y permisos. La evidencia de esta entrega corresponde a los comandos ejecutados y sus artefactos locales.
