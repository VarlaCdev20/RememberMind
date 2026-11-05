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

## Centrado del conjunto y tipografía del paginador

Se agrupan rango, números y salto directo en el centro mediante flex, sin repartirlos por los extremos. Tipografía friendly compartida (Nunito Sans),12 px, cifras tabulares y menor peso; se conservan formas circulares, cápsula y objetivos táctiles. En móvil el rango queda debajo del grupo centrado. Comprobación en la pestaña operativa recargada: texto y números de12 px, sin desbordamiento y diferencia de centrado inferior a0,01 px.

Build correcto; E2E focal PASS (104 s): tabla/tarjetas y formulario en cinco anchos, filtros, menús/foco, salto y oscuro390; capturas sintéticas actualizadas e inspección visual del conjunto en1440 px. Ajuste exclusivamente CSS de la variante compacta; sin cambios funcionales, BDD, permisos o legacy. CI completa no ejecutada.

## Corrección de interpretación del centrado

La propietaria aclara que el centrado corresponde al contenido de las burbujas. Se restituyen las posiciones anteriores: rango a la izquierda, navegación en el centro y salto a la derecha; adaptación móvil anterior conservada. Números y botón Ir usan centrado horizontal/vertical explícito, sin padding desplazado; valor del selector centrado con padding simétrico. Se mantienen12 px y fuente friendly. Comprobación en navegador operativo recargado: layout grid, rango alineado al inicio y todos los controles con justify-content/align-items center y padding0; selector text-align center.

Build y E2E focal PASS (45 s), tabla/tarjetas y formulario en cinco anchos, filtros, menús/foco y oscuro390. Captura de escritorio revisada con las posiciones restauradas. Ajuste CSS sin cambios funcionales o de permisos; CI completa no ejecutada.

## Formulario combinado de las tres referencias históricas

A petición de la propietaria, Registrar trabajador adopta fotografía lateral y datos en tres columnas de la página de adulto mayor, progreso con porcentaje de la página de Personal y organización compacta del modal de siete pasos. Se conservan cuatro pasos y los contratos actuales: identidad/contacto, laboral, acceso y documentación/revisión; no se restauran campos o reglas históricas. Modal canónico rectangular de1024 px, radio moderado y altura limitada a90dvh/760 px; scroll interior y pie de acciones visible. En móvil fotografía horizontal, campos a una columna y acciones accesibles.

Se conecta el campo foto_perfil ya admitido por GuardarPersonalInstitucional, con vista previa y validación inmediata usando las reglas existentes (JPG/JPEG/PNG/WebP,2 MB). Un archivo inválido se rechaza y no avanza. El alta confirmada se probó con fotografía persistida y archivo existente en el disco público simulado; no se eligieron datos de la BDD operativa. El botón final también se deshabilita durante la subida de foto.

El alta de trabajador reutiliza el mismo modal para las cuentas ya autorizadas, incluida Superadmin al entrar a Personal; se elimina el contenedor antiguo duplicado de alta. Su formulario separado Nuevo usuario no se modifica. Horarios conserva su tamaño original y la ficha de edición de Superadmin conserva su contenedor anterior. Sin nuevos permisos, cambios de BDD o reglas institucionales. Confirmación de descarte y autorización backend conservadas.

Pruebas ejecutadas: suite GerenciaPersonalRenovacionTest,11 pruebas/83 aserciones; verificación posterior del PDF rechazado y alta con foto,2 pruebas/13 aserciones; prueba adicional de apertura para GERENTE y SUPERADMINISTRADOR,1 prueba/6 aserciones. Son12 pruebas únicas, con el filtro de2 pruebas solapado. E2E focal final PASS (36 s), tabla/tarjetas y formulario en1440/1280/1024/768/390 px, filtros, menús/Escape/foco y oscuro390. Build correcto. Capturas de formulario escritorio/móvil revisadas. Cambios finales de indicadores obligatorios y target de subida no alteran geometría o backend. Comprobado en sesión operativa de Sara: modal1024 px y tres columnas, abierto sin introducir/guardar datos. CI completa y PostgreSQL no ejecutados para esta presentación/validación de archivo.

Cierre posterior a los últimos ajustes de labels/subida:3 pruebas focales correctas /19 aserciones (apertura, archivo inválido y alta con foto persistida). Se actualizó la pestaña solo tras comprobar que no había datos introducidos y se dejó el modal abierto. La galería histórica queda conservada en un commit separado.

## Validación por campo del registro de trabajador

Se reutiliza ui.field con estados inválido/válido, mensajes e iconos junto al recuadro y atributos aria-invalid/aria-describedby. El servidor valida al perder el foco mediante wire:model.live.blur (Livewire4 distingue sincronización local de envío); la función institucional se valida al cambiar y los archivos tras la subida. El verde solo aparece para un valor no vacío comprobado; los opcionales vacíos permanecen neutrales. Al continuar se señalan todos los obligatorios faltantes y se enfoca el primer error. El guardado mantiene validación completa, autorización, transacción y persistencia actuales.

Por indicación expresa de la propietaria se elimina el resumen amarillo superior y la alerta genérica duplicada de validación. Se añade el token semántico compartido de error de captura rojo vivo (#CF2434, variante oscura #FF818B): borde de2 px y texto rojo con icono; verde con texto Dato válido. No se altera la paleta cálida de superficies. Errores técnicos de guardado conservan su mensaje recuperable en el formulario.

Se alinea el paso de nacimiento con los límites ya exigidos al guardar (18–100 años), sin crear reglas nuevas. La validación de unicidad del CI ya no excluye por accidente un personal sin cuenta durante un alta. El backend devuelve juntos los documentos obligatorios ausentes según el catálogo del rol; el formulario vuelve al paso afectado si el guardado detecta errores. No se incorporan campos históricos, permisos, tablas o columnas.

Verificación final: php artisan test --compact --filter=GerenciaPersonalRenovacionTest:15 pruebas,141 aserciones correctas. Incluye corrección inválido/válido, opcional vacío, edad fuera de rango, correo duplicado normalizado, contraseña corta, documentos obligatorios ausentes sin persistencia y alta completa con foto. npm run build correcto. E2E RM_GERENCIA_QA_PERSONAL=1: PASS (46 s), campos rojos/verde y mensajes asociados en1440/1280/1024/768/390 px, primer foco, ausencia de resumen amarillo y sin desbordamientos; tabla/tarjetas/filtros/menús previos conservados. Capturas validacion-{ancho}.png en output/auditoria-integral-20261025/gerencia-renovada/personal-unificado, revisadas escritorio y móvil. En la pestaña operativa se comprobó rojo rgb(207,36,52), ocho campos vacíos señalados, primer foco y ausencia del resumen; no se guardó personal. CI completa y PostgreSQL no ejecutados para este cambio; pruebas de persistencia usan datos sintéticos SQLite/Storage simulado.

## Modal más amplio y Nunito

Ajuste visual solicitado: ancho máximo1180 px (antes1024), altura máxima94dvh/860 px, radio20 px del modal, campos y botones18 px; campos de48 px y tamaño15 px (16 px móvil para legibilidad). Padding interior20 px escritorio/16 móvil. Conserva distribución de tres/dos/una columnas, scroll interior, pie visible, paleta cálida y validaciones rojo/verde.

Se incorpora @fontsource/nunito5.3.0, licencia OFL-1.1, archivos latinos locales400/600/700/800; sin CDN o envío externo. Se aplica exclusivamente al modal de registro, incluidos controles y encabezados, mediante las variables tipográficas existentes. No se cambia backend, datos o permisos. Build correcto; E2E focal en1440/1280/1024/768/390 comprueba geometría, carga de Nunito en controles/etiquetas/botones y regresión de validación. Capturas escritorio/móvil revisadas. Navegador integrado deshabilitado en Work: no se pudo actualizar la pestaña operativa; comprobación visual realizada con servidor sintético aislado. Auditoría npm: Nunito sin hallazgos; seis dependencias previas con severidad alta (chromium-bidi, follow-redirects, puppeteer, puppeteer-core, vite, zod), fuera del ajuste visual; no se ejecutó npm audit fix.

## Un solo indicador de avance

A petición de la propietaria se conserva la navegación de cuatro pasos conectados y se elimina la segunda barra con porcentaje, su separador inferior y sus estilos sin consumidores. Se reúnen las ayudas en una línea breve de paso actual, campos obligatorios y guardado final. Nunito, geometría suave, campos, validación y navegación conservados. Build PASS; GerenciaPersonalRenovacionTest15 pruebas/141 aserciones PASS; E2E focal cinco anchos PASS (49 s), incluidas correcciones rojo/verde. Captura escritorio revisada. Navegador integrado continúa deshabilitado en Work: actualización de pestaña operativa pendiente de recarga del usuario.

## Captura más viva y cabecera delgada

La propietaria pide acentos más vivos, captura más grande, zona superior más pequeña y ejemplos centrados/translúcidos. Campos de54 px y Nunito17 px, etiquetas15 px; superficies de captura más claras y contraste salvia en fotografía, sección, paso actual y acción principal. Se usan tokens compartidos de superficie/salvia/primary-ink, sin alterar el significado de rojo/verde. El encabezado reduce padding vertical a4 px y los iconos de pasos a32 px con control de al menos48 px; línea de pasos alineada a los nuevos centros. Se mantiene una sola navegación.

Se añaden ejemplos sintéticos como placeholders, centrados y con tinta secundaria al72% de opacidad; los valores introducidos conservan opacidad completa y alineación inicial. No se cambia opacidad de errores ni etiquetas. Revisión de prioridad CSS para que estilos generales no oculten el tamaño de fuente o el acento principal; fondos claros excluyen campos inválidos para conservar rojo. Sin modificaciones backend, BDD o permisos. Build correcto; E2E focal verifica cinco anchos, carga de Nunito y regresión de validación. Capturas escritorio y móvil se revisan. La pestaña operativa requiere recarga manual mientras el navegador integrado siga deshabilitado.

## Retirada del matiz amarillo del formulario

La propietaria rechaza expresamente el crema/amarillo de las superficies. Se definen tokens semánticos compartidos para panel gris cálido neutro (#E8E7E5), sección clara gris verdosa (#F5F7F5) y campo blanco; variantes oscuras remiten a los tokens petróleo existentes. Se consumen solo en el modal de alta/edición de personal, sin recolorear el sistema entero. Encabezado salvia claro, fotografía/carga salvia y acción principal menta existente con tinta petróleo oscura. Se conserva Nunito, tamaños, pasos compactos y ejemplos centrados; rojo/verde de validación mantienen prioridad. Sin cambios de backend, permisos o BDD. Build y E2E focal cinco anchos; capturas sintéticas se revisan antes de cierre. Pestaña operativa requiere recarga manual por navegador integrado deshabilitado.

## Fondo café grisáceo solicitado

La propietaria concreta el fondo del formulario en café grisáceo: se ajusta el token compartido rm-form-panel de#E8E7E5 a#D8D2CF. Solo los consumidores del formulario cambian; campos blancos, secciones claras, acentos menta/salvia y variante oscura se conservan. Cambio exclusivo de color, sin backend o datos. Build y revisión E2E focal de cinco tamaños para cierre.

Ajuste posterior solicitado: café grisáceo más claro, rm-form-panel#E9E5E2 (antes#D8D2CF). Solo cambia el valor del token diurno; build correcto. No se repite E2E para este aclarado de color sin cambios de estructura o interacción; revisión visual del nuevo tono en pestaña operativa pendiente de recarga.

## Superficie unificada y captura más amplia

A petición de la propietaria se retira el blanco: en tema claro sección y campos consumen el mismo café grisáceo claro rm-form-panel#E9E5E2. Encabezado, cuerpo y pie comparten fondo; fotografía/carga y acción principal mantienen acentos salvia/menta. Se elimina el recuadro interior y su padding lateral para liberar ancho, con labels y bordes de campos conservados. Modal máximo1240 px, campos58 px, Nunito18 px en captura y16 px en etiquetas/acciones; título de sección20 px. Se preservan distribución responsive y estados de validación con prioridad de color. Sin cambios de dominio, permisos o BDD. Build correcto y E2E focal de cinco tamaños para verificación final; no se modifica la pestaña operativa mientras el navegador integrado está deshabilitado.

## Verde de Continuar en la parte superior, aclarado

La propietaria aprueba como referencia el verde menta de Continuar y pide aplicarlo arriba y a algunos controles. Se conserva la cabecera delgada, cuatro pasos y línea fina: cabecera, paso activo/completados, Elegir foto y Continuar comparten el acento; pendientes más legibles (opacidad80%, labels13 px y peso700). Tinta oscura para contraste. Sin doble barra ni cambios funcionales.

Aclaración inmediata de la propietaria: usar ese verde más clarito. Se deriva un único rm-form-accent en el contenedor (75% menta existente +25% superficie café grisáceo clara), sin recolorear otras pantallas. La estructura y versión menta inicial pasaron E2E de cinco anchos (42 s), incluida carga de Nunito y validaciones; el aclarado posterior cambia solo el token local y tiene build correcto. La captura del tono aclarado no se volvió a generar y la pestaña operativa requiere recarga; no se presenta la captura anterior como evidencia del aclarado. No hay cambios de backend, BDD o permisos.

## Cabecera más clara, título y pasos legibles

La propietaria precisa aclarar el verde solo arriba, poner Registrar trabajador en mayúsculas y mejorar el indicador señalado. Cabecera mezcla45% del acento menta claro con55% del panel café grisáceo; título visual en mayúsculas, peso800 y espaciado moderado. Pasos con iconos36 px, línea centrada22 px, labels14 px en escritorio y pendientes sin opacidad global, para conservar legibilidad. Separación breve entre pasos y estado del registro. Se mantiene el encabezado delgado y una única navegación; no se modifica interacción, permisos o datos. Build correcto; revisión E2E focal y capturas de cinco tamaños antes de cierre.

## Apellidos alternativos, mayúsculas y tipo de captura

Decisión explícita de la propietaria: cualquiera de los dos apellidos puede faltar, pero debe existir al menos uno. Se centralizan las reglas y normalización en DatosPersonalesTrabajador y se reutilizan en Livewire y GuardarPersonalInstitucional. Se aceptan letras Unicode/tildes/ñ, espacios, guiones y apóstrofos en nombres/apellidos; se rechazan números y signos ajenos. Documento y celular conservan sus reglas numéricas existentes. Expedido admite letras. Dirección, matrícula y observaciones admiten letras/números según su contenido y se normalizan a mayúsculas; correo conserva minúsculas y contraseña no se transforma.

Captura inmediata mediante evento input, también para pegado, sin alterar el cursor: elimina caracteres incompatibles en campos de letras/números y muestra mayúsculas; backend vuelve a validar entradas manipuladas. La validación de los dos apellidos conserva los errores de otros recuadros. Se elimina la frase Paso1de4/obligatorios/guardado final; queda ayuda específica de al menos un apellido, navegación con iconos40 px y labels15 px y textarea sin redimensionado (sin rayas). No se cambia BDD ni permisos: apellido_paterno sigue siendo string NOT NULL del baseline; una ausencia válida con materno presente se guarda como cadena vacía, sin inventar un apellido ni copiar el materno.

Verificación: GerenciaPersonalRenovacionTest18 pruebas/173 aserciones PASS, incluye backend directo rechazando entradas manipuladas sin crear cuenta, alta real en SQLite sintético solo con materno, mayúsculas y contraseña intacta. Build PASS. E2E cinco anchos verifica filtro numérico/letras, mayúsculas, apellido materno único, persistencia visual de otros errores, ausencia del texto y resize none; capturas revisadas. No se escribe BDD operativa. PostgreSQL/CI completa no ejecutados en este ajuste. Navegador integrado sigue deshabilitado; recarga manual de pestaña operativa pendiente.
