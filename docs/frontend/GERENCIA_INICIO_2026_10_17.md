# Gerencia — primer corte del inicio institucional

Fecha: 2026-10-17. Alcance: dashboard `/dashboard` del rol GERENTE y sus accesos existentes. No acredita cierre completo de personal, contratos, áreas, planificación, cobertura ni reportes.

## Fuentes y flujo

Contrato de roles congelado, ADR de arquitectura híbrida, contrato visual compartido y componentes del Design System. Gerencia organiza personal, áreas y planificación; Administración conserva las asignaciones operativas y los profesionales la autoría clínica.

UX FLOW: entrar → consultar indicadores del día → personal/documentación laboral → áreas → planificación → consulta de cobertura → reportes. Los destinos se filtran por permisos y middleware existentes. El dashboard no concede permisos ni modifica asignaciones.

## Correcciones

- Cabecera GERENCIA compacta, descripción operativa y cinco accesos explicados, sin repetirlos en cabecera y pie. Cards y encabezados compartidos; composición en `patterns/management-workspace.css`, sin paleta ni librería nueva.
- Cobertura diaria acepta los estados activos existentes de las asignaciones laborales, limita a jornadas abiertas del día y comprueba personal, área y turno activos. Cuenta personas distintas, aunque tengan varias asignaciones. El número de áreas y la distribución profesional ya no se recortan a ocho resultados.
- El dato de cobertura representa asignación laboral, no asistencia física ni comprobación de cobertura suficiente.
- La campana no programa peticiones Livewire periódicas durante «Ver como rol». El middleware de solo lectura sigue bloqueando POST; la sesión conserva identidad y roles reales.

## Verificación

- SQLite en memoria: `GerenciaDashboardTest|RoleDashboardDataTest|RolePreviewTest|AccesoDashboardPorRolTest`: **30 PASS / 229 aserciones**. Incluye nueve áreas, dos variantes de estado activo, deduplicación de personal, exclusión de área/personal/turno inactivos y fecha futura, apertura de cinco destinos con rol Gerente real y bloqueo de escrituras en preview.
- Build Vite: PASS; advertencia existente de bundle superior a 500 kB. Sin dependencias nuevas.
- Navegador con la cuenta administrativa en previsualización: cobertura real visible, cinco accesos y ausencia del iframe 403 tras retirar polling. Tab entre accesos y foco visible comprobados.
- Reflow medido a **1441, 1281, 1024, 768 y 391 CSS px**, sin desbordamiento horizontal en el documento. El zoom interno impidió acreditar exactamente 1440/1280/390. Captura de 1024 repetida tras carga estable para excluir el estado transitorio del menú al cambiar de breakpoint.
- El navegador indicó movimiento reducido desactivado. La variante CSS está implementada; no se afirma ejecución de ese modo ni una medición integral de contraste.

Dictamen visual: **verificación parcial; FAIL de aceptación estricta por evidencia incompleta** de anchos exactos y movimiento reducido. Esto no atribuye defectos visuales no observados. No se certifican todos los formularios enlazados ni producción.

## Acceso y pendientes

En la primera revisión todavía no existía una cuenta GERENTE. En la continuación, la propietaria confirmó el alta de una cuenta activa con personal vinculado y entregó la sesión iniciada. Se verificaron los cinco accesos desde esa sesión, sin previsualización de rol. No se guardan datos personales de la cuenta en este informe ni en Git.

Continuar con la revisión funcional de personal/documentación laboral y luego áreas/horarios. La revisión anterior de asignaciones y pase identificó diferencias pendientes; este corte no las declara resueltas. El KPI de personal del dashboard de Superadministración conserva una consulta propia y queda fuera de este cambio localizado.

## Continuación: sesión Gerente y revisión visual

Revisión del 17/10/2026 con el navegador integrado, sobre el árbol de trabajo existente de `feature/institucion-asignaciones-planificadas`. Se conservaron los cambios previos, la composición de Gerencia y sus consultas. No se modificaron estructura de BDD, permisos, roles, cuentas ni datos operativos. Alcance: inicio y apertura de destinos; no acredita los formularios, exportaciones ni el cierre de esos módulos.

### Diagnóstico y correcciones de presentación

- Distribución conservada: cabecera compacta, cuatro indicadores, cinco accesos explicados y paneles de cobertura/profesión/actividades. A 1440/1280/1024 hay cuatro indicadores por fila; a 768/390, dos. Los accesos pasan de cinco columnas a distribución 3+2 y finalmente una columna. Los paneles pasan a una columna móvil, sin recortar profesiones ni el estado vacío.
- Se confirmó el fondo `#D8D2CD`, las tarjetas claras, el salvia y la transparencia sutil. Título y tarjetas usan Nunito Sans con la escala compartida. No se creó otra paleta, componente o tipografía por rol.
- Los botones de la barra superior aún tenían un degradado que producía aspecto biselado. `components/topbar.css` reutiliza ahora el fondo translúcido y la sombra suave existentes, sin degradado.
- La barra móvil sin búsqueda reservaba una fila vacía. El mismo componente compartido ocupa ahora 68 px en esos casos, frente a 108 px antes, con seis controles de aproximadamente 44 × 44 px. Las barras con búsqueda conservan su disposición y se verificaron mediante fixtures en claro y oscuro.
- Los subtítulos y periodos de `dashboard-data.css` tenían contraste de 3,77–3,88:1. Ahora reutilizan `--rm-text-secondary-readable`: medición sobre fondos compuestos de 5,46–5,62:1 en claro y 7,99–8,98:1 en oscuro. Este cambio beneficia a los consumidores del panel compartido y no modifica las series ni sus significados. No constituye certificación global de accesibilidad.

### Evidencia visual y funcional

| Criterio | Ancho/tema | Evidencia | Resultado |
|---|---|---|---|
| Reflow del inicio | 1440, 1280, 1024, 768 y 390 CSS px, claro | `innerWidth` comprobado; ancho del documento y `scrollWidth` del main sin exceder su ancho; captura en cada tamaño | PASS |
| Navegación y controles móviles | 390, claro | Menú abre; grupos Personal, Organización y Consulta institucional despliegan destinos; Escape cierra y devuelve foco al disparador | PASS |
| Accesos por teclado | 390, claro | Tab entre los cinco accesos; contorno de 2 px y foco sin quedar bajo la barra fija | PASS |
| Paneles y vacío real | Móvil, claro | Distribución completa de profesiones y «No hay actividades programadas» visibles al desplazar el main | PASS |
| Tintas corregidas | Móvil, claro/oscuro | Contraste compuesto de subtítulos y periodos; tema claro restaurado | PASS acotado |
| Literales prohibidos | Cinco anchos, claro | Sin `null`, `NaN`, `undefined`, `Infinity` ni `[object Object]` en el texto renderizado del main | PASS |
| Movimiento reducido | Fixture del componente compartido | Media emulada en Puppeteer; transiciones inmediatas verificadas | PASS automatizado; pendiente en la sesión real |
| Escritura, validación, éxito y dirty state | Inicio de solo consulta | No hay formulario ni mutación de dominio en esta superficie | N/A; formularios de destinos excluidos |

El zoom interno del navegador se compensó con el control de viewport y se midió el ancho CSS real; esta continuación sí obtuvo los cinco anchos exactos. Los overrides temporales se retiran al terminar. La evidencia local del acceso móvil se conserva fuera de Git, sin fichas ni datos personales visibles.

Los cinco enlaces del inicio abrieron desde la sesión GERENTE: Personal institucional, Áreas institucionales, Horarios y asignaciones, Turnos de Enfermería (Cobertura) y Reporte General Institucional. No se guardaron asignaciones, se generaron exportaciones ni se usaron acciones clínicas. La consola capturada no mostró errores durante la revisión final del inicio.

### Pruebas de esta continuación

- `php artisan test --filter='GerenciaDashboardTest|RoleDashboardDataTest|RolePreviewTest|AccesoDashboardPorRolTest'`: **30 PASS / 229 aserciones**, SQLite en memoria. Conserva las comprobaciones de acceso real, cuenta inactiva, límites de rol y solo lectura del preview. No se afirma validación PostgreSQL.
- `node --test tests/Frontend/management-topbar.test.js tests/Frontend/shell-visual-consistency.test.js tests/Frontend/sidebar-responsive.test.js tests/Frontend/chart-design-system.test.js`: **13 PASS / 0 omitidas**. Incluye regresión de geometría a 320/390/768/1024/1280/1440, búsqueda presente/ausente, ambos temas, ausencia de biseles, contraste y movimiento reducido.
- `npm run build`: **PASS** con Vite 8.3.1. No se añadieron dependencias. En esta ejecución no apareció la advertencia histórica de bundle mayor de 500 kB; sí el aviso de tiempos del plugin CSS.

### Pendientes delimitados

- Dictamen estricto de `remembermind-visual-functional-qa`: **FAIL por evidencia incompleta**, limitado a movimiento reducido en la sesión real (el navegador declara `false` y no ofrece emulación de medios). Los cinco anchos, teclado y correcciones observadas sí se verificaron. No se atribuye un defecto de movimiento no observado.
- Reportes abre, pero conserva discrepancias previas que requieren revisión propia: nombre institucional diferente del inicio; resumen de personal de salud/administrativo 3/26 frente a detalle 25/3; terminología «Adultos mayores» aplicada a residentes. No se corrigió su agregación ni interpretación clínica desde una tarea de presentación del inicio.
- Las vistas Personal, Planificación y Cobertura usan indicadores con contratos diferentes (horario, asignación y jornada). Su conciliación y la prueba de formularios/documentación laboral siguen pendientes; la apertura del enlace no los certifica.
- Sin nueva dependencia V1 en el cambio. Se conserva la deuda previa de rutas históricas, incluidas las enlazadas por el menú; no se renombraron rutas como parte de este ajuste.
- Sin commit en esta continuación: se preserva el árbol de trabajo amplio preexistente y el cierre visual estricto sigue pendiente. No se hizo push.

### Ajuste posterior del saludo

Por indicación de la propietaria, el título fijo «GERENCIA» se sustituyó por el saludo horario y el nombre de la cuenta que ya entrega `DashboardService`; el rol permanece en su badge. Se retiró «Acceso según permisos» de la cabecera de Gerencia por ser una etiqueta genérica sin utilidad operativa. Los permisos backend no cambian. Se reutilizan los tramos existentes: antes de las 12:00, buenos días; desde las 12:00, buenas tardes; desde las 19:00, buenas noches, en el horario configurado de la aplicación (`America/La_Paz` en la sesión comprobada).

Verificado en navegador a 390 y 1440 CSS px, con nombre completo legible y sin desbordamiento. `GerenciaDashboardTest|RoleDashboardDataTest`: **14 PASS / 84 aserciones**, incluidos los tres saludos con una cuenta sintética y la ausencia de la etiqueta genérica. Build: **PASS**. Esta comprobación de copy no cambia los pendientes del dictamen estricto anterior.

### Adaptación posterior a la composición de Enfermería

La propietaria solicitó corregir la responsividad y conservar los colores/composición de Enfermería. Se retiraron los overrides de Gerencia que forzaban cabecera alineada a la izquierda, otra proporción de imagen y saludo de tinta plana. La cabecera vuelve a la composición canónica compartida: saludo centrado, tratamiento tipográfico café/salvia, jerarquía, imagen y superficies comunes. El badge y el encabezado institucional de Gerencia reutilizan los tokens compartidos de cuidado, sin modificar los acentos de navegación ni definir colores nuevos.

El resumen de indicadores reutiliza ahora las reglas existentes de Enfermería mediante `rm-dashboard-overview`; no se copió otra geometría. Cifra y etiqueta aparecen contiguas, con descripción debajo e icono reservado arriba. `management-workspace.css` adapta columnas al ancho real del contenedor: cuatro indicadores desde 960 px disponibles, dos debajo; los paneles se apilan debajo de 640 px disponibles. Esto evita forzar cuatro tarjetas estrechas cuando el menú lateral ocupa parte del portátil. Se conservan los cinco accesos y los datos previos.

Comprobación visual real: **1440, 1280, 1024, 768 y 390 CSS px** sin desbordamiento del main. En 1024 se ven dos indicadores por fila; en 1280/1440, cuatro. A 768 se repitió la captura una vez estabilizada la transición del menú: drawer cerrado, borde derecho del sidebar en 0, contenido completo. Móvil también revisado en oscuro; tema claro restaurado. Las fixtures sintéticas añaden 320 px, nombres largos, ambos temas, separación entre cifra/etiqueta, accesos de al menos 180 px y equivalencia de cabecera con Enfermería. No se accedió a una cuenta clínica ni se alteraron roles para obtener la referencia.

Verificación de este ajuste:

- `php artisan test --filter='GerenciaDashboardTest|DashboardEnfermeriaIntegracionVisualTest'`: **10 PASS / 97 aserciones**.
- `node --test tests/Frontend/management-workspace.test.js tests/Frontend/shell-visual-consistency.test.js tests/Frontend/management-topbar.test.js`: **7 PASS / 0 omitidas**.
- `npm run build`: **PASS**; sin nuevas dependencias.
- La ampliación a `tests/Frontend/nursing-dashboard.test.js` detectó **cuatro fallos en la fixture JavaScript existente**, fuera de los archivos de producto modificados: su simulador `config()` devuelve solo `options`, mientras `renderOne()` requiere `config.data.datasets[0]`. Esta prueba no carga el CSS cambiado. No se modificó el módulo de gráficas ni se debilitó la prueba para ocultar el fallo; queda pendiente corregir su simulador conforme al contrato actual.

Se mantienen los pendientes anteriores de Reportes y movimiento reducido en la sesión real. Sin cambios de backend, BDD, datos, permisos o rutas; sin nuevo legacy ejecutable. No se creó commit ni se hizo push sobre el árbol amplio preexistente.

## Cierre Git posterior

La propietaria solicitó commit y push y confirmó incluir todos los cambios pendientes del proyecto. Este corte se publica como avance técnico, conservando los pendientes de aceptación visual real. Resultados del conjunto y correcciones de cierre: [informe de publicación](../sistema/PUBLICACION_AVANCES_2026_10_17.md).
