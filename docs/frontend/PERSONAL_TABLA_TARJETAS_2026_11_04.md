---
title: "Personal de Gerencia: tabla y tarjetas compartidas con Enfermería"
status: VERIFIED_SCOPED_UI
last_reviewed: 2026-11-04
runtime_verified: true
---

# Alcance

Petición de la propietaria: ordenar Personal institucional, unificar tamaños y ofrecer tabla/tarjetas usando como base Enfermería. Ruta: `/admin/personal-institucional`, variante de Gerencia.

## Antes y flujo aplicado

Cada fila mostraba hasta cinco acciones con igual peso; las filas crecían según las acciones disponibles. Filtros, recuento y selector de vista estaban separados. El recorrido ahora es buscar/filtrar → comparar personas en tabla o tarjetas → abrir ficha → consultar acciones secundarias cuando se necesitan. El alta sigue siendo la acción principal de la cabecera.

## Implementación

- Una superficie de trabajo con título, total, selector Tabla/Tarjetas, filtros y paginación compartidos.
- Reutilización real de `ui.resident-card` y `ui.resident-card-identity`, ya empleados por Enfermería. Las props opcionales `secondary` y `entity` permiten mostrar correo e identidad laboral sin edad o alojamiento. Slot opcional de acciones; los consumidores anteriores conservan su comportamiento por defecto, detalles y notas.
- Estado laboral y acceso separados mediante `ui.status-badge`; ningún cambio de estado se deduce de otro.
- Ficha visible y acciones secundarias en `dropdown`: horarios, situación laboral, suspensión/activación y enlace de acceso, según las mismas autorizaciones/confirmaciones anteriores.
- Tabla de cinco columnas en escritorio; reflow etiquetado en espacios estrechos, sin eliminar datos. Tarjetas en tres/dos/una columnas, con la geometría e identidad del componente de Enfermería.
- Tokens y escalas existentes; fondo/superficies cálidas y acento salvia. No se crea una paleta de rol.
- La regla global de densidad deja de reducir este directorio a 40 px; controles de al menos 44 px. Menús con Escape/retorno de foco y acciones accesibles sin hover.

## Límites

Se conserva todo el trabajo previo. No cambia BDD, consultas, permisos, reglas laborales, archivos o correo. No se envían enlaces ni se suspenden cuentas reales durante QA. No se crean usuarios en la base operativa. La revisión visual utiliza el router sintético existente con SQLite desechable.

## Evidencia ejecutada

- GerenciaPersonalRenovacionTest + PersonalInstitucionalResponsiveTest: 13 pruebas /132 aserciones correctas antes de aplicar la reutilización final.
- GerenciaPersonalRenovacionTest + PersonalInstitucionalResponsiveTest + MisResidentesNavegacionTest: 25 pruebas /187 aserciones correctas.
- PersonEntityPresentationTest: 3 pruebas /15 aserciones correctas: tarjeta de Enfermería sin regresión, tarjeta laboral sin edad/alojamiento y preservación de slots de detalle/nota.
- Build Vite correcto; advertencia de tamaño de bundle existente.
- El recorrido E2E amplio de Gerencia se interrumpió por su límite de300 segundos y no se declara aprobado. Se añadió una ejecución focal opcional `RM_GERENCIA_QA_PERSONAL=1` al test existente para verificar esta pantalla en1440/1280/1024/768/390 px, ambas presentaciones, menú/Escape/foco, filtros y medidas.
- La primera QA focal detectó controles reducidos por la regla global; se corrigió la causa antes de repetir. El resultado final se registra al cerrar la revisión.

Sin cambios nuevos de dominio/legacy. Esta entrega comprueba la presentación de Personal y la compatibilidad de sus componentes; no es aceptación global de todos los flujos de Gerencia.

## Cierre verificado

QA visual-funcional focal: PASS. Diez combinaciones (tabla/tarjetas en1440,1280,1024,768 y390 px), sin desbordamiento horizontal, celdas contenidas en sus filas y controles de al menos44 px; menú dentro de pantalla, Escape y retorno de foco, filtrado/vacío/limpieza. Modo oscuro de tarjetas comprobado en390 px. Cero errores JS o respuestas HTTP fallidas. Capturas actuales revisadas en escritorio y móvil.

La inspección visual detectó además la altura fija de fila heredada, que causaba superposición vertical al reflow; se corrigió y se incorporó una aserción geométrica a la regresión. Se revisó el resultado corregido antes de aceptar.

Ejecución focal final: 1 test E2E correcto (85 s), diez combinaciones más modo oscuro y estados. Evidencia local sintética: output/auditoria-integral-20261025/gerencia-renovada/personal-unificado/resultado.json y capturas del mismo directorio. Build final correcto. PHP:28 pruebas únicas /202 aserciones (25 de Personal/navegación y3 de compatibilidad del componente); no sumar el filtro inicial solapado.

Verificación limitada a la presentación y regresiones indicadas. No se reejecutó la CI completa ni se modificó la base operativa. La rama conserva trabajo previo de otras tareas; el commit incluye únicamente esta entrega.

## Ajuste posterior de densidad y botones

A petición de la propietaria se reducen padding y separaciones de cabecera, filtros, tarjetas y filas; avatar de56 px en tarjetas y títulos con la escala de sección. Las acciones se agrupan al final de la fila; en tarjetas Ver ficha ocupa el espacio junto a Acciones, eliminando la separación amplia. Se conservan controles de44 px, estados y permisos. Verificación posterior: build correcto y E2E focal PASS (10 combinaciones en cinco anchos, filtros, menús/Escape/foco y oscuro390), sin superposiciones ni desbordamientos; cero errores JS/HTTP. Capturas sintéticas actualizadas en el mismo directorio de evidencia.
## Ajuste de columnas y paginación solicitado mediante anotaciones

Se reduce el relleno horizontal de celdas y se redistribuyen los anchos de las cinco columnas. En escritorio, las acciones comienzan junto a su encabezado; en móvil conservan su alineación para mantener los menús dentro de pantalla. La paginación usa una franja integrada, sin recuadro ni relleno exterior y con controles de44 px.

Se retira exclusivamente de esta vista el selector de tamaño sin conexión funcional: el backend pagina en12 registros y no expone la propiedad porPagina. Se conservan el rango, el total y los botones del paginador compartido. No cambia el tamaño de página, los permisos ni la persistencia.

Verificación final del ajuste: build correcto y E2E focal PASS (35 s), tabla/tarjetas en1440/1280/1024/768/390 px, filtros, menús, Escape/foco y oscuro390. Sin desbordamientos, superposición de filas, controles pequeños ni errores JS/HTTP. Capturas sintéticas actualizadas; no se reejecutó la CI completa.

## Refinamiento de paginación y filtros

Se añade la variante reutilizable rm-pagination--compact al componente canónico: grupo continuo de navegación, botones planos, página activa salvia y distribución centrada en móvil; controles de44 px y semántica existente. Se aplica solo a Personal y se eliminan sus overrides locales anteriores. Los demás consumidores conservan su presentación.

El filtro conserva búsqueda, estado y limpieza existentes, con mayor separación entre controles, opción Todos los accesos y región con nombre accesible. Se elimina la superficie/sombra heredada para integrarlo con el listado; escritorio en una fila y móvil con búsqueda completa y estado junto a Limpiar. Sin cambios de consultas, permisos, reglas ni BDD.

Verificación final: build correcto (advertencia existente de bundle); E2E focal PASS en26 s, diez combinaciones de tabla/tarjetas en cinco anchos, búsqueda/vacío/limpieza, menú/Escape/foco y oscuro390. Sin desbordamientos, superposición ni errores JS/HTTP. Inspección visual de capturas sintéticas; no se ejecutó la CI completa.

## Distribución de la cabecera de Personal

Se ajusta el componente compartido collection-header mediante su clase de composición existente: identidad y acciones en dos columnas en escritorio, separación uniforme y controles planos de44 px con tipografía del sistema. En espacios menores de850 px las acciones ocupan una fila de dos columnas iguales; por debajo de420 px se apilan a ancho completo. Icono de44 px y padding de16 px sin altura fija. Se conservan etiquetas, enlaces, permisos, colores y orden de teclado.

Verificación de cabecera: build correcto y E2E focal PASS (22 s), diez combinaciones en1440/1280/1024/768/390 px, sin overflow ni controles pequeños; filtros y menús/Escape/foco conservados. Capturas revisadas en1440,768 y390 px. Sin cambios de backend o permisos; CI completa fuera de esta verificación localizada.

## Buscador compacto y reutilización del filtro canónico

Se reemplaza el input local por ui.filter-search con lupa, conservando busquedaResumen, debounce e identificador. Props opcionales inputId y showClear mantienen los valores anteriores en otros consumidores; label vacío permite una única etiqueta visible proporcionada por ui.field. El buscador queda limitado a26 rem (416 px), estado a14 rem y Limpiar contiguo, sin estirar el grupo por todo el listado. Se conserva adaptación móvil y tokens existentes.

Build correcto y E2E focal PASS (25 s): tabla/tarjetas en cinco anchos, búsqueda/vacío/limpieza, menú/Escape/foco y oscuro390, sin errores JS/HTTP o desbordamientos. Ajuste final de etiqueta verificado en navegador operativo: una sola etiqueta asociada, lupa presente y buscador de416 px. Pestaña recargada para mostrar los cambios. Sin cambios de backend, permisos o persistencia; no se ejecutó la CI completa.

## Paginador más delgado

Se elimina el padding vertical, el recuadro/fondo del grupo de páginas y su separación interna; margen superior reducido a4 px. Se conserva el resaltado salvia y los controles de44 px. Medición en la pestaña operativa recargada: franja de45,6 px, padding0 y sin borde exterior de navegación. Build y E2E focal PASS (25 s), diez combinaciones en cinco anchos, filtros, menús/foco y oscuro390; capturas actualizadas y revisadas. Sin cambios funcionales o de permisos. CI completa no ejecutada para este ajuste CSS.

## Referencia del paginador, registro y ficha compacta

Referencia visual aportada por la propietaria: cápsula baja con números, página actual circular con borde, flechas circulares separadas y selector/botón de salto. Se conserva esa composición centrada; adaptación al salvia ya aprobado, textos en español y superficies de32 px dentro de objetivos táctiles de44 px. Rango con tipografía de etiqueta y menor peso. En móvil se reorganizan navegación, salto y rango en filas centradas.

ui.paginacion añade showJump opcional (desactivado por defecto), que usa gotoPage y el nombre real del paginador; Personal lo habilita. El grupo de números conserva display:contents en los consumidores anteriores. Salto real verificado en la pestaña operativa:1-12 ->13-24 ->1-12 sin mutaciones de datos.

Registrar trabajador reutiliza ui.form-section como Nuevo usuario de Superadmin: progreso con iconos y línea, secciones de campos y pie uniforme. Se conservan sus cuatro pasos, todos los campos, validaciones, confirmación de descarte y autorización; no se copian campos ni permisos exclusivos de Superadmin. Se retira la cabecera duplicada únicamente dentro del modal gerencial. La cabecera de otros consumidores se conserva. En móvil se abrevian las etiquetas visuales de pasos, manteniendo sus nombres accesibles completos.

Ver ficha conserva action-button compartido, ahora variante pequeña, sin expansión en tarjetas: menor tipografía y fondo de32 px con área de interacción de44 px. Sin cambios de acciones/permisos.

Verificación: build correcto;6 pruebas PHP /42 aserciones (paginador y wizard/confirmación integral). E2E focal final PASS (38 s): diez combinaciones tabla/tarjetas y formulario en cinco anchos, búsqueda/vacío/limpieza, menú/Escape/foco, salto sin cambio de página en fixture, cierre de formulario y oscuro390. Primera QA detectó overflow de los pasos en390 px; corregido antes del PASS. Cero errores JS/HTTP. Capturas sintéticas formulario-1440/1280/1024/768/390 y listado actualizadas y revisadas en escritorio/móvil. Formulario operativo abierto sin introducir ni guardar datos; cuatro pasos y sección compartida confirmados. Sin cambios de BDD, reglas, permisos o legacy; CI completa no ejecutada.
