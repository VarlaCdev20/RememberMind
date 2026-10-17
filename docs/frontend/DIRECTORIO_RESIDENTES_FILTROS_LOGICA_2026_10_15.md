# Mis residentes — lógica de filtros, 15/10/2026

## Alcance y contrato

Solicitud del propietario: mejorar la lógica de filtros del directorio de Enfermería conservando la barra compacta. Búsqueda, habitación y alertas se combinan por intersección dentro del alcance autorizado del enfermero. Los contadores de los botones representan el total asignado; «Mostrando N de M» expresa el resultado combinado. Las alertas se consultan con sus estados/prioridades existentes, sin nuevas clasificaciones clínicas.

## Defectos reproducidos y correcciones

- `limpiarFiltros` dejaba activos alertas, medicación y cuidados. Ahora reutiliza la limpieza completa de criterios, reinicia la página y conserva tarjetas/listado. La acción de limpiar criterios conserva el orden; la limpieza del estado vacío también restablece Nombre A–Z, como antes.
- «Todos» dejaba activo `filtroAlertas=CON_ALERTAS`. Los dos estados existentes de alertas se sincronizan tanto al actualizar propiedades como al aplicar/remover filtros mediante acciones. Habitación y búsqueda se conservan al cambiar solo alertas.
- El filtro rápido podía prevalecer sobre «Sin alertas» o alertas prioritarias; se elimina esa contradicción y la condición SQL duplicada.
- Los filtros activos no estaban visibles. Se reutiliza `active-filters-row` para mostrar búsqueda normalizada, habitación y criterios existentes, con retirada individual y limpieza conjunta. Los botones de retirada tienen 44×44 px; textos largos pueden envolver sin overflow.
- El resultado no indicaba restricciones internas de medicación/cuidados. El conteo ahora depende de los criterios activos completos; una búsqueda formada solo por espacios no cuenta como filtro.
- El orden por nombre no desempataba residentes homónimos. Se añaden apellidos y código como desempate estable para la paginación.

## Evidencia

- Antes de corregir: **5 regresiones fallidas / 1 caso correcto**.
- Después: **15 pruebas PHP PASS / 88 assertions**, incluyendo combinaciones, limpieza, alertas detalladas, paginación, orden, filtros canónicos y exclusión de residentes de otro enfermero. SQLite en memoria y datos sintéticos.
- Build: **PASS**, con el aviso existente de tamaño de bundle.
- QA con Blade y bundle reales, transporte Livewire simulado: **21 casos claros + 6 oscuros PASS**, tarjetas/listado/criterios activos. Anchos claros: 1440, 1280, 1033, 1024, 989, 768 y 390 px. Sin overflow ni errores JS; botones de retirada ≥44 px y foco visible.
- Navegador conectado con backend real: «Con alertas» devolvió **1 de 5**; retirar el filtro devolvió **5**, seleccionó «Todos» y quitó el conteo filtrado. No se escribió información clínica; se restauró la selección inicial.
- Evidencia local ignorada: `storage/app/qa/directorio-filtros-logica-2026-10-15/`.

## Límites

Sin cambios de BDD, reglas clínicas, permisos, servicio de alcance, formularios ni historia. No se ejecutó la suite PHP completa ni integración PostgreSQL para este ajuste de lectura. Persisten los criterios internos preexistentes; no se añaden opciones clínicas al toolbar. Los archivos afectados contienen trabajo previo mezclado: no se crea commit ni se hace push.
