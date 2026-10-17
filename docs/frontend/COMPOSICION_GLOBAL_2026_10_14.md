# Composición global — 14/10/2026

## Alcance implementado

Petición vigente: conservar el diseño aprobado y mejorar distribución, colores y tamaños de letra en todo el sistema.

La implementación se propaga por tokens y componentes existentes, sin crear un segundo Design System:

- Nunito Sans en títulos funcionales, Inter en cuerpo y Outfit en marca/saludos destacados.
- Página 28 px, sección 18 px, card 16 px, cuerpo 15 px, tabla 14 px, etiquetas 13 px y metadata 12 px. Se conservan variantes específicas de identidad clínica y valores.
- Separación de página/secciones 20 px, grids 16 px y padding base de card 16 px. Administración consume estos mismos tokens en lugar de sus valores locales.
- Indicadores con más espacio interior y mayor interlineado. Panel único ocupa el ancho disponible. Superadministración deja de reducir los títulos de panel a 15 px.
- Filtros de 44 px y texto de 14 px; selección sin franja interior, bisel ni cambio de grosor que desplace el contenido. Foco visible conservado.
- Tablas estándar y compartidas con encabezados legibles y espaciado canónico; variante compacta conserva su densidad con texto de 13 px.
- Opciones de ejemplos y escritura libre consumen la etiqueta de 13 px. Cabeceras de colecciones consumen la misma escala.
- Paleta aprobada conservada: fondo #D8D2CD, cards #ECE9E6 y campos #E7E2DE. Salvia en acciones, azul grisáceo en información y colores de estado existentes. Los paneles informativos usan el acento semántico de información.
- Botones sin bisel. Se conserva el modo oscuro y el significado clínico de los estados.

## Integración y seguridad

Los diez roles consumen la misma base: Superadministración, Gerencia, Administración, Médico, Enfermería, Psicología, Nutrición, Fisioterapia, Pedagogía y Familiar.

No se cambian rutas, datos, permisos, Policies, reglas clínicas ni BDD. No se añaden dependencias ni lógica V1. La exportación de vistas usa usuarios sintéticos y SQLite :memory:, sin crear residentes en la aplicación operativa.

## Verificación

| Comprobación | Resultado |
|---|---|
| PHP: AccesoDashboardPorRolTest, DashboardWelcomeHeaderTest, FiltrosDisenoUnificadoTest, OverlayComponentsTest, RoleDashboardDataTest | 30 PASS / 208 assertions |
| Exportación de los diez dashboards HTTP reales en testing | 1 PASS / 21 assertions |
| npm test final | 194 PASS, 0 omitidas |
| npm run build final | PASS; aviso existente de bundle mayor de 500 kB |
| Dashboards: diez roles × cinco anchos en claro + dos anchos en oscuro | 70 casos sin overflow ni errores JS |
| Contraste de texto en los dashboards exportados | 777 muestras; mínimo 4.69:1 |
| Formularios/consultas: doce vistas × cinco anchos | 60 casos sin overflow ni errores JS |
| Directorio: cards/lista × cinco anchos, menú y foco | 10 casos PASS |
| Shell/sidebar, tema claro/oscuro y comportamiento responsive | Cubiertos por npm test |
| Vista conectada de Mis residentes | 989 CSS px, título 28 px, sin overflow, paleta vigente |

Anchos exactos: 1440, 1280, 1024, 768 y 390 CSS px; oscuro 1440 y 390. Pruebas de navegador con prefers-reduced-motion activo. Evidencia local sintética: `storage/app/qa/composicion-global/`; JSON de resultados y capturas por rol/ancho/tema. El directorio conserva su evidencia en esa carpeta; las capturas de formularios se conservan en el directorio temporal de QA.

## Dictamen y límites

PASS para la propagación compartida y la matriz indicada. Se inspeccionaron composiciones representativas de escritorio, tablet y móvil, incluidos Enfermería, Administración, Superadministración, Médico, Nutrición, Familiar y Psicología.

Los dashboards exportados cubren los estados reales sin datos o sin vínculo profesional; el directorio y los formularios usan fixtures con datos sintéticos. La interacción de presentación usa transporte Livewire simulado; las pruebas PHP verifican acceso y datos por separado. No se ha inspeccionado manualmente cada pantalla interna ni cada combinación de datos de los diez roles. Este resultado no es certificación WCAG, clínica ni de producción.

## Git

Sin commit: los archivos compartidos ya contienen trabajo previo mezclado y el árbol mantiene numerosos cambios ajenos a este lote. No se hizo stage ni push. La documentación registra el alcance de esta mejora sin atribuir como propias las demás modificaciones.

## Ajuste posterior: indicadores de Enfermería

La propietaria solicitó mejorar exclusivamente la distribución y los números de las cuatro cards superiores. Se mantiene `ui.metric-card` y se aplica una variante contextual en `dashboard-workspace.css`, sin copiar markup ni cambiar métricas.

- Números tabulares de 32–40 px en función del ancho real de la card.
- Icono de 32 px alineado con la cifra, con espacio reservado para evitar superposición.
- Títulos de 16 px con espacio para dos líneas; descripciones de 13 px alineadas en cada fila.
- Padding 16 px en escritorio / 12 px móvil y separación compartida. Se mantienen los colores y las acciones existentes.
- Responsive conserva cuatro columnas en escritorio y dos en móvil/tablet según los breakpoints actuales.

Verificación: build PASS; DashboardEnfermeriaIntegracionVisualTest 4 PASS / 54 assertions; shell-visual-consistency 4 PASS. Siete casos de navegador (cinco tamaños claros y dos oscuros), sin overflow ni errores JS; números de al menos 32 px y offsets de títulos/descripciones alineados. Evidencia sintética con cifras de la referencia: `storage/app/qa/indicadores-enfermeria/`.

Comprobación adicional de la pestaña operativa a 1031 CSS px: cifras 1/1/1/5, 33.22 px, cuatro cards de 177.47 px, títulos a 73.07 px y descripciones a 122.66 px, sin overflow. Inspección de lectura únicamente; no se persisten registros. Sin cambio de BDD, lógica ni permisos. Sin nuevo commit por el trabajo previo mezclado en el archivo compartido.

### Corrección solicitada: cifra al costado

Sustituye la distribución vertical anterior: número a la izquierda, título a su lado y descripción inmediatamente debajo del título. El icono queda debajo de la cifra. Peso numérico 900; separación horizontal 12 px, vertical 4 px; sin alturas reservadas entre título y descripción. Se conserva el mismo componente y el scope exclusivo del resumen del turno.

Build PASS; cuatro pruebas de shell PASS; siete casos de navegador claros/oscuros PASS, con verificación de proximidad horizontal, alineación de centros y ausencia de overflow en 1440/1280/1024/768/390. Vista operativa: cuatro cards de 117.5 px de alto (antes 177.47), cifra de peso 900 y separación de 12 px, sin overflow. Evidencia `storage/app/qa/indicadores-enfermeria/kpi-horizontal-*`. No cambia backend, datos ni permisos. Sin commit por los cambios previos mezclados.

Última preferencia aplicada: peso de cifra 800 (antes 900), icono en esquina superior derecha con separación de 8 px más el borde. Se reserva su alto para no superponer el título. Se mantiene la cifra a la izquierda y el texto contiguo. Build PASS y siete casos responsive/tema PASS, verificando peso 800, ubicación del icono y ausencia de superposición/overflow. La evidencia horizontal se actualiza al estado vigente.

## Iconos compartidos: tamaño y visibilidad

Petición posterior: mejorar los iconos de todos los componentes compartidos conservando la composición aprobada y los iconos superiores derechos de los indicadores.

### Implementación

- Se reutilizan `--rm-icon-md` (20 px) y `--rm-icon-lg` (24 px). No se incorpora librería ni otra escala.
- Tarjetas y encabezados de sección: glifo de 20 px; soporte de 36 px. Indicadores compactos de Enfermería: soporte de 28 px, con altura reservada para evitar superposición con el título.
- Sidebar abierto/compacto y barra superior: glifo de 24 px. La columna del sidebar crece con el icono y mantiene sus etiquetas, grupos y badges.
- Botones compartidos: 20 px junto al texto; 24 px en botones de solo icono. SVG usa las mismas dimensiones. Los controles mantienen su área interactiva y nombres accesibles.
- Encabezado de drawer: clase compartida de icono a 24 px dentro de soporte de 36 px. Resumen del residente: 20 px con tinta más contrastada.
- Encabezados clínicos móviles: 24 px dentro del soporte existente de 36 px; se conservan los encabezados de escritorio de 28 px.
- Gráficas y paneles informativos: se corrige la tinta y, en modo oscuro, el fondo del icono. Se conserva el acento coral de las alertas de Administración y se usa tinta de peligro legible. No cambian severidad ni datos.
- No se agrandan indiscriminadamente los indicadores de despliegue, puntos de estado, checks o ilustraciones. El cambio sigue la función de cada componente.

### Verificación

- Build final: PASS, con el aviso de bundle mayor de 500 kB existente.
- PHP: OverlayComponentsTest + DashboardEnfermeriaIntegracionVisualTest, 7 PASS / 72 assertions.
- npm test durante la integración: 194 PASS. Tras las correcciones finales de contraste: shell/sidebar, 5 PASS.
- Diez dashboards en cinco anchos claros y dos oscuros: 70 casos PASS; 371 muestras de iconos de tarjetas, secciones y gráficas, mínimo 20 px y contraste mínimo 3.19:1. Sin recortes ni overflow.
- Indicadores de Enfermería: 7 casos PASS; se conserva peso numérico 800, cifra contigua al título e icono en esquina superior derecha sin superposición.
- Formularios/consultas: 60 casos PASS; Medicación: 50 casos PASS; directorio cards/lista: 10 casos PASS. Se verifican cinco anchos y ausencia de errores JS/desbordamiento.
- Pantalla operativa abierta: sidebar y topbar de 24 px, encabezado del drawer de 24 px e iconos del resumen de 20 px; sin overflow. Lectura de UI únicamente.

Evidencia sintética: `storage/app/qa/iconos-compartidos/`, incluidas capturas por rol/tema/ancho y resultados JSON. Los fixtures renderizan los componentes y assets del producto; el transporte de interacción se simula en QA. Este alcance no equivale a inspección manual de cada pantalla interna ni a certificación WCAG.

No se modifican BDD, permisos, datos, reglas clínicas ni lógica de persistencia. No se introduce legado ni nuevas dependencias. Sin commit/push: se mantienen los cambios previos mezclados de los componentes compartidos.

## Escala ligeramente compacta — 15/10/2026

Última petición: reducir un poco el tamaño general. Sustituye los tamaños superiores descritos en este informe: página 26 px, dashboard 28 px, sección 17 px, card 15 px, cuerpo 14 px y base numérica 25 px. Se conservan tablas/botones a 14 px, etiquetas 13 px y metadata 12 px. Las cifras destacadas siguen usando sus variantes contextuales.

Espacios compartidos: 16→14 px, 20→18 px, 24→22 px, 28→26 px y 32→30 px. Padding base de card 14 px; cabecera de dashboard de 190→180 px donde esa altura gobierna. La bienvenida conserva altura automática cuando requiere más espacio por el contenido. Los iconos conservan 20/24 px y las áreas de acción de 44 px; la compactación no usa zoom ni transforma toda la página.

Verificado: build PASS (aviso de bundle existente), npm test 194 PASS, diez roles en 70 combinaciones de tema/ancho sin overflow ni errores JS, 60 casos de formularios/consultas, diez casos de directorio y Cognición en cinco tamaños. Capturas revisadas de dashboard, Signos y Cognición móvil. Evidencia sintética en `storage/app/qa/escala-compacta-2026-10-15/`. No se ha repetido PHP porque este lote modifica únicamente tokens CSS y altura visual; no cambia datos, lógica, permisos ni BDD. Sin commit/push por los cambios previos mezclados.
