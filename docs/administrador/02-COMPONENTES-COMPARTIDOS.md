# Componentes compartidos: listado y navegación

## Paginación institucional

Componente [x-ui.paginacion](../../resources/views/components/ui/paginacion.blade.php), con [CSS canónico](../../resources/frontend/styles/design-system/components/paginacion.css). Altura 68 px mínima; botones de 44 px; páginas cercanas, extremos y puntos cuando corresponda. En móvil adapta filas. No requiere dependencia nueva.

Listado GET:

```blade
<x-ui.paginacion :paginator="$registros" mode="url" :per-page="$porPagina"
    per-page-name="por_pagina" label="admisiones" />
```

El controlador debe validar `por_pagina` en 10/20/50 y usar ese valor al paginar. El selector conserva parámetros, incluidos arrays y otros paginadores, y elimina la página correspondiente cuando cambia tamaño.

Listado Livewire:

```blade
<x-ui.paginacion :paginator="$registros" mode="livewire" :per-page="$porPagina"
    per-page-name="porPagina" label="solicitudes" />
```

El componente Livewire conserva `WithPagination`, valida tamaño y reinicia su paginador al cambiar filtros/tamaño. Los controles llaman `previousPage`, `nextPage`, `gotoPage` con el nombre del paginador.

Los overrides en `resources/views/vendor/pagination/` y `resources/views/vendor/livewire/` delegan al componente; no duplican estilos. En paginadores existentes sin configuración de tamaño se oculta ese selector para no presentar una función que su backend no implementa. Su apariencia queda unificada y su tamaño de consulta permanece vigente.

Los paginadores simples no inventan total; muestran rango y anterior/siguiente. Los de cursor muestran registros de la página y cursor. Los wrappers de cursor usan include para conservar el objeto del framework.

## Selector

[x-ui.selector](../../resources/views/components/ui/selector.blade.php) soporta `wire:model` o `name/value` en formularios GET. Las opciones nativas conservan el valor para el envío y Livewire.

```blade
<label for="orden-listado">Ordenar</label>
<x-ui.selector name="orden" id="orden-listado" label="Ordenar" :auto-submit="true">
    <option value="recientes" selected>Más recientes</option>
    <option value="antiguas">Más antiguas</option>
</x-ui.selector>
```

Buscador solo con más de 10 opciones y tres opciones visibles en listas largas. El resto se desplaza. Flechas/Home/End recorren opciones habilitadas; Enter elige; Escape cierra y devuelve foco; Tab permite continuar. `auto-submit` usa `requestSubmit`, por lo que respeta validación y los interceptores del formulario. Selección múltiple conserva un array.

En modal con foco restringido, `teleport` debe apuntar a una capa interior. Se mantienen tokens semánticos y una indicación de foco alrededor del buscador completo.

## Menú lateral

[Vista](../../resources/views/components/layout/barra-lateral-sistema.blade.php) y [CSS](../../resources/frontend/styles/design-system/components/sidebar.css). Icono, texto, contador y caret ocupan columnas independientes. Las etiquetas largas pueden envolver; el contador no se coloca encima del texto. En modo compacto queda bajo el icono, sin perder su nombre accesible.

No cambiar navegación/roles para resolver un problema de distribución. Mantener preferencias desktop, comportamiento laptop temporal y drawer móvil.

## Pruebas

- [Paginación](../../tests/Feature/PaginacionInstitucionalTest.php): GET/Livewire, filtros, arrays, paginador nombrado, simple y cursor.
- [Controles](../../tests/Frontend/controles-institucionales.test.js): GET/Livewire, selección, búsqueda y teclado.
- [Geometría](../../tests/Frontend/sidebar-layout.test.js) y [navegación](../../tests/Frontend/sidebar-responsive.test.js): ambos temas, móvil, compacto, persistencia y ausencia de colisiones.

Después de modificar estilos, generar build antes de correr pruebas que leen su manifest.
