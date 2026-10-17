# Gráficas de Mi turno — 15/10/2026

## Alcance

Mejorar lectura y composición de las tres visualizaciones existentes del dashboard de Enfermería. Se mantienen consultas, permisos, asignaciones, estados y fuentes de datos.

- Seguimiento: distribución de residentes del contexto autorizado por alertas activas, con total, cantidad y porcentaje por categoría. No representa una valoración clínica.
- Actividad: cantidades de acciones realizadas, pendientes y retrasadas de `dashboard.estado_tareas`. Las retrasadas se muestran separadas de las pendientes.
- Incidentes: cantidades por día y categoría de `incidentesTendencia`, período de siete días y comparación semanal ya calculada por el backend.

## Implementación

Se reutilizan RMCharts, Chart.js y tokens compartidos. La dona usa segmentos sólidos y leyenda alineada con cantidades y porcentajes. Actividad distingue realizadas en salvia, pendientes en ámbar y retrasadas en coral, con valores también en HTML. Incidentes usa líneas rectas y marcadores diferenciados, eje de cantidades enteras y detalle desplegable por fecha accesible con teclado. No se agregan métricas ni umbrales.

Los paneles conservan las superficies institucionales. El layout interno se adapta al ancho disponible con container queries; las etiquetas no se ocultan en móvil. Las animaciones locales reutilizan duración compartida y respetan la rama existente de movimiento reducido.

## Ajuste posterior de composición y tamaño

Las tres gráficas se reúnen en «Resumen visual del turno». El ancho real del dashboard determina tres columnas, dos con incidentes a lo ancho, o una columna móvil. Se eliminan las alturas mínimas que estiraban los paneles vacíos; cada card ajusta su altura al contenido. La tendencia tiene 184 px de dibujo (180 px en móvil), la actividad 144 px y la dona 128–144 px. Cantidades y porcentajes se alinean en una misma línea. Los ejes muestran menos marcas, conservando acceso a todas las fechas mediante el detalle HTML; los puntos tienen mayor área de interacción sin ampliar el marcador visible.

Verificación del ajuste: build y cinco pruebas frontend PASS; exportación Blade real normal/vacío/una acción PASS. Revisados los cinco anchos con datos sintéticos, sin overflow ni recortes de texto. Evidencias `storage/app/qa/graficas-turno/sizing-*.jpg`.

La prueba de jerarquía del dashboard pasa con 46 aserciones. Se corrigió su comprobación obsoleta de la clase `ph-bell text-lg`: ahora exige un único botón con nombre accesible «Notificaciones y Alertas» y un único bloque de resumen visual. La pantalla conectada confirma las tres gráficas agrupadas sin overflow.

## Verificación

Ajuste solicitado posteriormente: seguimiento ahora se muestra como torta completa (`pie`, sin hueco central). El total de residentes aparece junto a la leyenda; se conservan cantidades, porcentajes y fuentes de datos. Build y exportación Blade PASS; comprobación visual en escritorio y 390 CSS px sin overflow. Evidencias `pie-desktop.jpg` y `pie-390.jpg` en el fixture aislado.

- `npm run build`: PASS; advertencia existente de bundle JS superior a 500 kB.
- `node --test tests/Frontend/shell-visual-consistency.test.js`: 4 PASS.
- Exportación Blade de secciones reales con datos sintéticos: 1 PASS, 3 aserciones; escenarios normal, vacío y una acción.
- Navegador conectado: vista real actualizada y componentes QA con CSS/JS compilados. Revisados 1440, 1280, 1024, 768 y 390 CSS px; sin desplazamiento horizontal ni recortes de texto visibles. La escala interna se compensó en el viewport y se verificó con `innerWidth`.
- Detalle por fecha: abre con Enter, foco visible y cantidades accesibles en HTML.
- Contraste de textos muestreados sobre fondos compuestos en claro: mínimo 4.85:1. Tema oscuro revisado en escritorio.
- El navegador tiene movimiento reducido desactivado y no expone simulación; la rama de movimiento reducido se conserva en código, sin afirmar comprobación de ese modo en ejecución.

Evidencias y fixture aislado: `storage/app/qa/graficas-turno/`. No se escribieron registros clínicos ni se modificó la base de datos. El fixture no sustituye una prueba integral de Livewire ni una validación clínica.

## Integración y Git

Cambios en la vista existente de `dashboard-turno` y el patrón compartido `nursing-dashboard-layout.css`. No se introducen dependencias ni legacy. Los archivos contienen cambios anteriores de otras tareas; no se genera un commit que mezcle ese trabajo ni se realiza push.
