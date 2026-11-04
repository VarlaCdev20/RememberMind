# Cards de emergencias y alertas — 16/10/2026

## Cambio implementado

Se conserva el recorrido autorizado: revisar prioridades, identificar al residente, consultar el motivo y registrar atención o abrir el contexto. El alcance es la composición visual de las cards y del resumen; no cambia consultas, permisos, prioridad, estados ni persistencia.

- Lista con columnas según el ancho real disponible. Cuando hay una sola card ancha, motivo y seguimiento se distribuyen en dos columnas interiores. En móvil las cards se apilan.
- Cabecera con prioridad, título y estado. Crítica activa mantiene texto explícito, icono y superficie de alerta con tinta contrastada, sin cabecera oscura; alta conserva su acento de advertencia.
- Residente y ubicación agrupados. Motivo separado de los datos de seguimiento: fecha, origen, responsable y límite cuando existe. Fecha resumida a minutos en la card; el atributo `datetime` conserva el instante completo.
- Atención y detalle son acciones principales; evolución, ubicación y cierre forman el grupo de apoyo. Se reutiliza `action-button`, conservando los mismos métodos y condiciones de permiso. Se agrega feedback de carga al abrir atención y detalle.
- Resumen con número a la izquierda, etiqueta próxima e icono en la esquina. Se eliminan franjas laterales gruesas. En móvil, dos columnas y resumen final a lo ancho.

El patrón `emergency-workspace.css` compone superficies, tipografía, espaciado, radios, sombras y estados desde los tokens vigentes. Está limitado a `.rm-emergency-workspace`; no cambia las cards de otros módulos. No se crean componentes, catálogos ni dependencias adicionales.

## Verificación y límites

- `php artisan test --filter=AlertasFlujoTest`: 17 PASS, 87 aserciones. Incluye atención, cierre, historial, permisos, prioridad y acceso a contexto.
- `npm run build`: PASS; permanece la advertencia existente de chunk JS superior a 500 kB.
- Exportación Blade real con datos sintéticos en SQLite `:memory:`: PASS.
- DOM renderizado en 1440, 1280, 1024, 768 y 390 CSS px: sin overflow horizontal, sin botones recortados y sin controles de las cards menores de 44 × 44 px. No aparecen literales de datos inválidos. La cabecera crítica usa fondo RGB 242/217/215 y tinta 122/61/57 (contraste 6.14:1).
- La vista conectada carga las nuevas clases y distribución. No se guardaron datos desde el navegador.

**Dictamen visual: FAIL por evidencia incompleta.** Tanto la pestaña conectada como el fixture aislado devuelven «Unable to capture screenshot». Se comprobaron estructura y geometría con DOM, pero queda pendiente la inspección visual de capturas. No se afirma QA visual completa, revisión del tema oscuro o simulación de movimiento reducido.

Fixture aislado: `storage/app/qa/emergencias/`. Hay cambios previos mezclados en la vista y en el Design System; no se crea un commit que los incluya ni se hace push.

## Ajuste de densidad solicitado sobre la captura

Se compacta la card: encabezado de una línea cuando hay ancho suficiente, avatar de 32 px, menos padding vertical y footer de acciones más delgado. En cards anchas con tres datos de seguimiento, estos ocupan una fila; con límite adicional conservan dos columnas. Se mantienen los textos completos y las acciones de al menos 44 px.

Build PASS. En la ventana conectada, encabezado de unos 40 CSS px y card de unos 244 CSS px. Reflow comprobado por DOM en 1441, 1281, 1024, 768 y 391 CSS px, sin overflow horizontal ni botones recortados. La escala interna del navegador impide obtener algunos anchos exactos mediante los tamaños enteros del viewport. No se afirma inspección visual nueva por captura; continúa la limitación documentada arriba. No cambia backend.
