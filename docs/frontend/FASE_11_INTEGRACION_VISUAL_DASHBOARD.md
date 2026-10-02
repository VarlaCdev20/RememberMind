# Fase 11 — Integración visual del dashboard de Enfermería

## Alcance

Se integró únicamente la vista de inicio de Enfermería con los componentes y tokens ya presentes. No se añadieron métricas, rutas, tablas ni cambios clínicos. La lectura sigue el orden: contexto del turno, KPI, alertas/pacientes/incidentes y, por último, distribución/agenda/tareas/ubicación/notas. El orden del DOM coincide con el visual.

## Decisiones de composición

- La topbar conserva una sola acción de alertas y un solo perfil. El buscador anterior no efectuaba búsquedas, así que se retiró en vez de simular una función. El welcome muestra un único título principal, rol, contexto, fecha/hora y una foto institucional; no duplica controles.
- Se retiraron los cinco indicadores antiguos y las visualizaciones de progreso repetidas. Las KPI sin dato verificable se presentan como no disponibles, sin color de alarma; las alertas reales mantienen su prioridad.
- Los paneles inferiores usan tarjetas compactas y el mismo componente de estado vacío. No se muestran clasificaciones, enlaces, fechas ni datos clínicos inferidos.
- La gráfica de incidentes existente conserva sus datos y leyenda, con animación breve y colores derivados de tokens. Se respeta `prefers-reduced-motion`.
- El acceso «Medicación» del sidebar de Enfermería apunta ahora a su ruta propia; así coincide la navegación con el elemento activo.

## Archivos de esta fase

Creados: `docs/frontend/FASE_11_INTEGRACION_VISUAL_DASHBOARD.md` y `tests/Feature/DashboardEnfermeriaIntegracionVisualTest.php`.

Intervenidos (algunos ya tenían cambios de fases anteriores, que se conservaron):

- `resources/views/livewire/cuidados/dashboard-turno.blade.php`
- `resources/views/components/ui/dashboard-welcome-header.blade.php`
- `resources/views/components/ui/empty-state.blade.php`
- `resources/views/components/ui/patient-turn-row.blade.php`
- `resources/views/components/layout/topbar-enfermeria.blade.php`
- `resources/views/components/layout/barra-lateral-sistema.blade.php`
- `resources/frontend/styles/design-system/components/empty-states.css`
- `resources/frontend/styles/design-system/patterns/nursing-dashboard-layout.css`
- `app/Backend/Modulos/Identidad/Servicios/SidebarService.php`
- Pruebas: `DashboardWelcomeHeaderTest`, `NursingDashboardAgendaTest`, `SidebarServiceTest`, `UnificacionShellEnfermeriaTest`, `IncidentesEnfermeriaTest`, `FrontendArchitectureTest`, `MiTurnoServiceTest` y `MiTurnoFueraDeJornadaTest`.

## Tamaños y temas

El grid de 12 columnas mantiene una fila principal de alertas/pacientes/incidentes. Entre 1200 y 1439 px, los paneles complementarios se reparten en filas de tres y dos para evitar columnas demasiado angostas. Por debajo de 1200 px se simplifica progresivamente a paneles completos; en móvil los KPI ocupan dos columnas y el resto una sola. Los fondos, textos, bordes y foco usan tokens existentes. En oscuro, el gráfico y los iconos KPI reducen el brillo de la paleta global sin crear una segunda identidad; el sidebar utiliza tokens café existentes. No se añadieron blancos puros ni amarillos en el dashboard; el rojo queda reservado a estados críticos reales.

## Pruebas y límites

Se añadió una prueba de integración del render que comprueba un solo H1, una foto institucional, una campana, ausencia de búsqueda ficticia e indicadores antiguos, tarjetas esperadas y ausencia de enlaces `#`. Se actualizaron aserciones obsoletas de componentes, shell, arquitectura y turno, conservando el contenedor canónico de gráficas; también se corrigió la ruta activa real de Medicación. El build y la compilación Blade son parte de la verificación de esta fase.

Verificación automatizada: última suite completa con 442 pruebas aprobadas y 13 omitidas (9667 aserciones); batería focalizada final con 95 pruebas aprobadas (759 aserciones); `npm run build` y `php artisan view:cache` completados. El `git diff --check` acotado a los archivos intervenidos no presenta errores; el global aún señala espacios finales anteriores en `resources/views/livewire/cuidados/ficha/tab-medicacion.blade.php`, fuera de esta fase.

## Validación en navegador

Se inspeccionó una sesión real de Enfermería sin jornada activa. Se midieron 1440, 1280, aproximadamente 1024 (1025 px efectivos por el zoom del navegador), 768, 390 y 360 px. Tras estabilizar las transiciones, todos tuvieron diferencia cero entre ancho de documento y área visible. Se revisaron capturas de escritorio y móvil, temas claro y oscuro, un solo H1, carga de imágenes, estados vacíos y foco visible. No se capturaron errores ni advertencias de consola en la pestaña de prueba. El menú de perfil abre con teclado, cierra con Escape y devuelve el foco al disparador.

La inspección reveló y permitió corregir: tokens tipográficos heredados indefinidos que reducían el H1, overflow de la topbar cerca de 768 px, estilos antiguos que restituían padding/sombra del estado vacío, logo faltante del sidebar y subtítulos contradictorios cuando no hay jornada. Sin jornada, la ausencia de consulta de alertas ya no se presenta como ausencia confirmada de alertas. Todos los recursos de imagen observados cargan después del ajuste.

Pendiente: inspección visual de filas y gráficas con datos de un turno activo, estados críticos reales y carga/errores de red. No se creó una jornada ni se alteraron registros clínicos para fabricar ese escenario; la cobertura automática valida datos, aislamiento y estados de turno.
