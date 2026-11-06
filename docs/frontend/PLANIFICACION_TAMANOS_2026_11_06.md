# Planificación: proporciones y tamaños — 06/11/2026

Continuación del diseño aprobado: calendario más amplio (al menos 75% del espacio en escritorio), panel de detalle de 264px, cabecera y barra temporal más compactas, encabezados de días de 64px y celdas vacías con menos altura. Se mantiene la separación de tarjetas de 14px; nombres Nunito de 15px, función de 13px y datos del detalle de 15px. Botones conservan objetivo mínimo de 44px. La agenda móvil y el esquema de colores siguen vigentes.

Solo CSS de `.rm-planning` y dos aserciones del E2E cambian. Sin backend, estructura BDD, permisos ni cambios sobre datos. Se inspeccionaron capturas de escritorio y detalle móvil.

Verificación del corte nocturno 05/11: recorrido E2E aprobado en 1440,1280,1024,768 y390px, con selección y foco, filtros, mes, nueva asignación y horarios existentes; comprueba tamaño mínimo de nombres y proporción de calendario. Build Vite aprobado. Evidencias: `output/planificacion-tamanos-e2e-20261105.txt`, `output/planificacion-tamanos-build-20261105.txt` y capturas en `output/planificacion-diseno-20261105/`. No se repiten suites de dominio al no modificarse comportamiento de servidor. El pendiente ajeno de Alertas del informe anterior sigue sin resolverse en esta tarea.
