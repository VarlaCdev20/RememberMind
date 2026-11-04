# Mis residentes — composición y tipografía, 14/10/2026

## Implementado

Se conserva la paleta aprobada: fondo café grisáceo, cards greige más claras, sin dominante amarilla ni bisel. El cambio afecta la presentación del directorio y los componentes actuales de tarjetas/filtros.

- Cuadrícula adaptada al espacio real: columnas de al menos 280 px cuando hay espacio y una columna en móvil; separación de 20 px, 16 px en móvil.
- Identidad centrada conservada: avatar de 80 px, nombre de 20 px / 800 en la familia compartida Nunito, edad y datos de 14 px. Nombre con target mínimo de 44 px.
- Nombres de listado de 16 px. Acciones, buscador y filtros de 14 px, etiquetas de 13 px.
- Etiquetas sobre cada desplegable, agrupadas semánticamente con su select, conservando los mismos IDs, labels, modelos Livewire y opciones.
- Filtros con reflow por ancho; contexto de cabecera agrupado para reducir su altura.
- Próximo cuidado en un bloque azul grisáceo discreto; ubicación con acento informativo; salvia en identidad/acción. Los estados clínicos conservan sus textos y tonos del backend.
- El selector tarjetas/listado usa una selección salvia suave sin sombra. Footer de acciones alineado y separado de la información.

## Integración y límites

Se reutilizan `resident-card`, `resident-card-identity`, `resident-filter-toolbar`, `collection-header` y los patrones compartidos. No se introduce una variante paralela por rol. La tipografía de identidad se propaga a los consumidores de la card; el layout de filtros se limita al directorio.

No cambia BDD, permisos, consultas, validación ni escritura clínica. No se guardan registros operativos para verificar este diseño.

## Verificado

- Seis pruebas PHP de `MisPacientesRedisenadaTest`: **PASS / 49 assertions**. Cabecera, búsqueda, chips, selección de vista, fotos/iniciales, habitación y ordenamiento.
- Cuatro pruebas JS de consistencia visual de shell/dashboards: **PASS**.
- Build final: **PASS**, con el aviso general de bundle mayor de 500 kB.
- QA sintético: **10 casos PASS**, tarjetas y listado en 1440/1280/1024/768/390 px. Sin overflow horizontal ni errores JS; contraste muestreado mínimo de 4.5:1. Menú de tres puntos con apertura, Escape y retorno de foco; buscador con foco visible.

Las capturas usan Blade y bundle reales con datos sintéticos y transporte Livewire simulado. Los filtros se verifican adicionalmente en PHP, no se atribuyen llamadas backend al harness visual. La revisión no es una certificación global de accesibilidad ni una ejecución de la suite PHP completa.

Evidencia local ignorada por Git: `storage/app/qa/directorio-composicion/`. Sin commit/push: los archivos compartidos contienen trabajo previo mezclado que se conserva.

### Cards de residentes ligeramente más compactas — 15/10/2026

Avatar 80→72 px, padding 18→14 px, menor separación entre datos y footer, y ancho mínimo de grid 280→260 px. Se conservan las letras, información, colores y botones de 44 px. Afecta únicamente las tarjetas del directorio de residentes. Build PASS, diez casos responsive de cards/lista PASS y cuatro pruebas visuales compartidas PASS; sin overflow y con menú/foco correctos. Sin cambios backend/BDD ni nuevo commit por trabajo previo mezclado.

### Barra de filtros compacta — 15/10/2026

La captura del propietario muestra dos filas sobredimensionadas, tres etiquetas visibles y selectores que crecen innecesariamente. Se reduce el padding a 10×12 px y la separación a 8 px. Se conservan los labels asociados para lectores de pantalla; la búsqueda y las opciones explican visualmente el propósito. Tarjetas/listado utiliza iconos con nombre accesible, tooltip y estado seleccionado. Los chips inactivos son neutros y el activo conserva salvia.

La composición depende del ancho real de la barra mediante un contenedor limitado al toolbar: una fila desde 1000 px disponibles, dos filas intermedias y tres grupos en móvil; el modo estrecho apila los selectores. Inputs y botones mantienen 44 px. No cambia modelos Livewire, IDs, opciones, conteos ni lógica de filtros.

Pruebas PHP de filtros y directorio: **8 PASS / 55 assertions**. Build: **PASS**. QA con Blade/bundle reales y datos sintéticos: **10 casos PASS** en tarjetas/listado a 1440, 1280, 1024, 768 y 390 px, sin overflow, errores JS ni pérdida de foco; capturas revisadas de escritorio y móvil. Evidencia: `storage/app/qa/directorio-filtros-compactos-2026-10-15/`. Sin nuevo commit: los archivos afectados contienen trabajo previo mezclado.

Regresión adicional del tema oscuro: **4 casos PASS**, tarjetas/listado en escritorio y móvil. Los controles mantienen los tokens del tema.

### Corrección del ancho de activación en la ventana real

El propietario seguía viendo dos filas. La inspección del navegador confirmó que el CSS nuevo estaba cargado, pero el toolbar disponía de 913 px y el modo de una fila exigía 1000 px. Se corrige el umbral a 900 px y se ajustan las columnas de habitación/orden a 190/138 px, manteniendo controles de 44 px. La búsqueda pasa a «Buscar residente...» para evitar texto cortado. Se actualiza únicamente la expectativa de ese copy en la prueba de cabecera.

Verificación en la pestaña conectada: altura **124→72 px**, una fila, sin overflow y placeholder actualizado. Build PASS; prueba de cabecera PASS / 15 assertions. QA sintético ampliado: **14 casos PASS**, añadiendo 989 y 1033 px a los cinco anchos anteriores y verificando explícitamente altura ≤80 px cuando hay ≥900 px disponibles. Captura aislada sin datos personales: `toolbar-compact-989.png` en el directorio de evidencia anterior. No fue un fallo de caché ni del backend.

### Cabecera con contexto separado — 15/10/2026

La captura del propietario mostraba identidad a la izquierda y una etiqueta extensa de jornada arriba de fecha/acción a la derecha. Se conserva el contenido y la superficie cálida; se separan estado, nombre del turno y fecha, con acción independiente alineada. El icono se reduce a 48 px y utiliza el radio compartido; «Cuidados programados» incorpora calendario y salvia. Se reutiliza el slot `context` de `collection-header` desde `residents-page-header`; los cambios de composición se limitan al directorio. En móvil el contexto pasa debajo de la identidad con un separador suave. Se mantiene literalmente el aviso de solo lectura y la misma autorización del enlace.

Build PASS. Pruebas PHP de cabecera y consulta fuera de turno: **2 PASS / 28 assertions**. Consistencia visual JS: **4 PASS**. QA sintético de cabecera activa/consulta: **14 casos claros + 4 oscuros PASS**, incluidos 1440/1280/1024/768/390 px y los anchos intermedios; contraste muestreado ≥4.5:1 y sin overflow. Inspección de la pestaña conectada con nombre de turno largo: **110 px de altura**, contenido completo y sin overflow. Evidencia: `storage/app/qa/directorio-cabecera-2026-10-15/`. Sin cambios backend, BDD ni nuevo commit por archivos compartidos con trabajo previo mezclado.
