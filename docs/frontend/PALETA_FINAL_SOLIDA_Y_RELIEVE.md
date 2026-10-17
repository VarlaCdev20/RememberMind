---
title: "Paleta final: superficies sólidas y relieve"
status: CURRENT
last_reviewed: 2026-10-14
verification_scope: UI_PALETTE_AND_CHART_RENDERING
---

# Paleta final: superficies sólidas y relieve

Decisión vigente de la propietaria: paleta institucional completa y corrección posterior que elimina degradados de superficies/controles/gráficas, conserva los de las letras y aumenta el relieve. Sustituye las propuestas #8DA18A/#E7E0D9.

## Implementación

La fuente de HEX es tokens/colors.css. Los usos existentes se mantienen mediante aliases semánticos; palette-consistency.css propaga el contrato al shell, dashboards, ficha, formularios y componentes. No contiene otra paleta ni redefine su geometría.

- Fondo general #EAE2DB; navbar y sidebar #DDD2C8; cards, cabeceras, filtros y paneles #ECE4DF; campos #E6DDD5.
- Texto principal #3E3732, bordes de la escala aprobada y focos visibles.
- Acciones primarias salvia #7FA58D, información petróleo #3F6B7D y peligro coral #C86D67. La acción primaria comparte el mismo contrato entre roles; la identidad contextual de navegación permanece.
- Badges separados para success, warning, danger, critical y neutral. Se conservan sus estados y labels del dominio.
- Cognición usa acento lavanda; ingesta/hidratación oro y medicación petróleo en los iconos de contexto existentes.
- Series 1–6: salvia, petróleo, coral, oro, menta y lavanda; las categorías, datos, ausencia de datos y clasificación clínica permanecen intactos.
- Fondos uniformes, sin degradados. Relieve mediante rim interior, borde y dos capas de sombra compartidas. No se eliminan fotos ni flechas funcionales de selects.
- Barras y áreas de gráficas con relleno uniforme; las áreas mantienen su opacidad y los datos originales. Las firmas existentes de los helpers se conservan para sus consumidores.
- El degradado tipográfico de la cabecera se conserva y se comprueba en navegador.

## Contraste

Los HEX solicitados permanecen en la paleta, incluidos sus *-text. Algunos pares propuestos no alcanzan AA: muted/canvas 2.88:1, primary-text/primary 4.01:1, texto claro/danger 3.43:1 y secondary/canvas 4.44:1.

El primario y el peligro utilizan la tinta oscura existente #07141C; las tintas pequeñas de los componentes usan aliases legibles derivados de las tintas aprobadas. Info sólido con su texto claro sí alcanza AA. Esto conserva los fondos solicitados. El modo oscuro mantiene superficies petróleo y tintas contrastadas; se corrigió success-soft para impedir una superficie clara con tinta clara.

## Verificación

- Build Vite de rama y checkout principal: PASS.
- PHP: 27 tests, 495 assertions en arquitectura, ficha médica, dashboard de Enfermería y cabecera.
- Suite frontend de la rama y del checkout principal: PASS; incluye pruebas de sidebar, gráficos y todos los flujos previamente cubiertos.
- Regresión de rellenos: no llama createLinearGradient ni depende del tamaño del canvas; conserva las series y los null originales.
- Navegador: 100 casos de ficha (10 secciones × 5 anchos × 2 temas), sin errores JavaScript, literales prohibidos, desbordamiento de página o fallos de contraste en las muestras comprobadas.
- Componentes de paleta: 10 casos, cinco anchos y ambos temas; superficies sólidas y relieve comprobados.
- Prueba persistente de Chrome: superficies uniformes, sombra visible, foco y degradado solo tipográfico en los cinco anchos y ambos temas.

Anchos: 1440, 1280, 1024, 768 y 390 px. Fixtures sintéticas y CSS/bundle reales. Los snapshots Blade proceden del componente Livewire; la validación visual no certifica todas las mutaciones clínicas HTTP. No se exportaron datos reales ni se modificó la base de datos.

Evidencia local: C:/Users/CARLAENCINAS/.codex/paleta-final-20261014, con logs de build, PHP, frontend y resultados/capturas de evidence y ficha-evidence. El estado previo se conserva en before/rama y before/principal.

## Integración y límites

Trabajo en UNIFICACION; main no fue modificada. En el checkout principal se aplicaron únicamente los cambios de paleta, conservando trabajo previo y las ediciones del otro chat. Sin push automático.

No cambian permisos, autoría, reglas clínicas, medicación ni persistencia. El reparto 60/20/10/10 orienta la jerarquía; no se fabrican alertas ni se fuerza una cuota de rojo cuando no existen estados de peligro. El problema anterior de documentos/cifras de ejemplo continúa registrado en FICHA_CLINICA_COLORES_UNIFICADOS.md y está fuera de esta tarea.

## Corrección anterior: fondo y navbar (trama sustituida por la aclaración siguiente)

La propietaria pidió el fondo y navbar del mismo color que el sidebar, y trama en el fondo. El alias de página reutiliza capuchino. La trama reutiliza los tokens de puntos de 22 px, adaptados al tema, y se limita al lienzo. Se eliminan los halos ambientales; cards, controles y gráficas continúan sólidos y con relieve. La navbar conserva su separación mediante borde y sombra.

Verificación de esta corrección: 14 pruebas de arquitectura (405 assertions), 5 pruebas de navegador de shell/sidebar y 20 combinaciones visuales (dos builds, dos temas y cinco anchos). Sin diferencias de color entre las tres áreas ni desbordamiento horizontal. Build de rama y checkout principal aprobados. Evidencia sintética en C:/Users/CARLAENCINAS/.codex/fondo-trama-20261014/evidence.


## Aclaración histórica: trama de profundidad

La propietaria descartó la trama de puntos. El lienzo usa líneas diagonales cruzadas con un borde de sombra y otro de luz para sugerir bajorrelieve, y una sombra interior en su encuentro con la navbar. El fondo base continúa siendo el mismo del sidebar y navbar. Los trazos son bandas de color entero (stops coincidentes, sin transición de color). Las tarjetas, botones y gráficas conservan su superficie sólida. Los tokens de relieve están centralizados en tokens/shadows.css y se adaptan a ambos temas; el efecto es estático y no interfiere con controles ni contenido.

Verificación de la trama de profundidad: 5 pruebas de shell/sidebar, 14 pruebas de arquitectura (405 assertions), 20 vistas sintéticas a 1440, 1280, 1024, 768 y 390 px en claro/oscuro con los dos builds. Fondo, navbar y sidebar con el mismo color, sin puntos ni desbordamientos; relieve del lienzo y superficies sólidas comprobados. Ambos builds aprobados. Evidencia en C:/Users/CARLAENCINAS/.codex/trama-relieve-20261014/evidence.

## Ajuste histórico: trama corrugada

La propietaria pidió sustituir la trama diagonal por una corrugada. El lienzo presenta nervaduras verticales paralelas cada 32 px con un borde iluminado y otro sombreado, reutilizando los tokens y temas existentes. Se conserva la base capuchino compartida con sidebar/navbar, el relieve interior y las superficies sólidas de tarjetas y controles. No hay puntos ni líneas cruzadas.

Verificación de corrugado: 4 pruebas de navegador de shell, 14 pruebas de arquitectura (405 assertions), 20 vistas sintéticas entre 390 y 1440 px en ambos temas y ambos builds. Se comprobó orientación vertical única, color base compartido, superficies sólidas, relieve y ausencia de desbordamiento. Builds aprobados. Evidencia en C:/Users/CARLAENCINAS/.codex/trama-corrugada-20261014/evidence.

## Decisión vigente: puntos menudos

La propietaria pidió puntos muy pequeños en el fondo. La trama tiene radio de 0.6 px y separación de 16 px, con tintas discretas adaptadas a claro/oscuro. Sustituye las franjas corrugadas y la propuesta orgánica sin publicar. Fondo, navbar y sidebar conservan el color base compartido, y tarjetas/controles sus superficies sólidas y relieve. No hay imágenes externas ni SVG de la propuesta anterior en el producto.

Verificación de puntos menudos: 4 pruebas de navegador, 14 pruebas de arquitectura (405 assertions), 20 vistas sintéticas en cinco tamaños y ambos temas/builds. La alineación a medio píxel asegura puntos visibles incluso a densidad 1: una muestra de 64 × 64 px contiene 16 píxeles de puntos y 4080 del fondo. Build aprobado en ambos checkouts. Evidencia en C:/Users/CARLAENCINAS/.codex/puntos-menudos-20261014/evidence.

## Ajuste vigente: tarjetas sin panel exterior y fondo más claro

La captura identifica el panel envolvente del listado de residentes. Se retiran su fondo, borde, sombra y padding exterior; se conserva la sección accesible, contador, filtros, estados y las tarjetas individuales con su relieve. El fondo general se aclara a #EAE2DB, manteniendo los puntos menudos; navbar y sidebar conservan #DDD2C8. La adaptación se implementa en tokens y CSS compartidos, sin cambios de datos, permisos ni acciones.

Verificación del ajuste: 4 pruebas de navegador del shell/listado, 14 pruebas de arquitectura (405 assertions) y 20 vistas sintéticas (cinco anchos, ambos temas, ambos builds). Panel exterior transparente, sin borde/sombra; tarjetas individuales con superficie y relieve; sin desbordamientos. Ambos builds aprobados. Evidencia en C:/Users/CARLAENCINAS/.codex/residentes-sin-panel-20261014/evidence.

## Ajuste vigente: superficies sin blancos

La propietaria pide unificar las áreas claras. Las superficies soft/elevated/header son aliases del beige #ECE4DF; los campos usan #E6DDD5. Se propaga a cabeceras de listados, filtros, paneles, modales y menús. Las utilidades blancas heredadas del shell consumen la superficie semántica existente. Se preservan acentos/estados, foco, relieve y tema oscuro. La sección de residentes continúa sin panel envolvente.

Verificación de superficies unificadas: 4 pruebas de navegador del shell/componentes y 14 pruebas de arquitectura (405 assertions). 20 vistas sintéticas de cabecera/filtros/tarjetas en cinco tamaños, dos temas y dos builds; 10 vistas adicionales de paleta/contraste con botones y estados. Sin blancos en las superficies comprobadas, sin desbordamientos y contraste de los pares verificados mayor o igual a 4.5:1. Builds aprobados. Evidencia en C:/Users/CARLAENCINAS/.codex/superficies-sin-blanco-20261014/evidence y palette-evidence.

## Dirección vigente: más color y relieve

La propietaria pide reducir la apariencia monocromática y demasiado clara de todo el sistema. El fondo pasa a arena #DCCEBF y el chrome a café grisáceo #C8BFB7; tarjetas #E6DACE, apoyo #D6C4B3 y paneles elevados #EBE0D5. Campos #DCCCBD con borde más definido y tinta secundaria contrastada. Salvia #689D7C, petróleo #356F85, coral #BD765D, oro #CCA34A, menta #6FB89A y lavanda #9B83BF refuerzan las funciones existentes. Se aumenta el relieve de tarjetas/paneles/botones y el acento de cabeceras/acciones/selección. No se añaden blancos, degradados de superficie, panel envolvente ni estados clínicos nuevos.

La propietaria aprobó explícitamente la combinación y distinción de colores de Inicio (captura aportada) como referencia compartida. Después pidió sidebar y navbar iguales al fondo o más café grisáceo: ambos usan #C8BFB7. Los iconos reales de cognición/conducta, ingesta, hidratación y medicación reciben respectivamente lavanda, oro, menta y petróleo.

Verificación final de color/relieve y chrome grisáceo: 155 pruebas frontend y 14 de arquitectura (405 assertions), ambos builds aprobados. 20 vistas de listado, 10 de paleta/contraste y 10 de ficha/contextos, cinco anchos y dos temas; QA independiente PASS acotado a consumidores sintéticos con CSS final. Iconos reales de cognición/ingesta/medicación con acento propagado; primario 5.95:1, info 5.32:1 y hover primario 4.5027:1. Sin desbordamientos ni datos inválidos visibles. No certifica todos los flujos clínicos autenticados; no hubo cambios de backend/persistencia. Evidencia y QA_INDEPENDIENTE.md en C:/Users/CARLAENCINAS/.codex/color-relieve-20261014.

## Corrección vigente: chrome claro igual al fondo

La propietaria pide aclarar sidebar/navbar y usar el mismo color del fondo. Capuchino pasa a ser alias de página: #DCCEBF en las tres áreas. No cambia la combinación aprobada de Inicio, los acentos, puntos menudos, tarjetas ni relieve.

Verificación incremental: 5 pruebas de shell/sidebar y 14 de arquitectura (405 assertions), ambos builds aprobados. En 20 vistas sintéticas (cinco anchos, dos temas y ambos builds), sidebar/navbar/fondo tienen el mismo color; se mantienen relieve, puntos y superficies existentes. Evidencia en C:/Users/CARLAENCINAS/.codex/chrome-claro-unificado-20261014/evidence.

## Tono vigente: café grisáceo claro

La propietaria pide un tono más café grisáceo. La base compartida cambia de arena #DCCEBF a #D7CEC7, con menos amarillo y luminosidad similar. Fondo/sidebar/navbar siguen usando el mismo token. Acentos, tarjetas, puntos, relieve y tema oscuro se conservan.

Verificación incremental del café grisáceo: 5 pruebas de shell/sidebar, 14 de arquitectura (405 assertions), ambos builds y 20 vistas sintéticas aprobadas. Las tres áreas comparten #D7CEC7; relieve, puntos, acentos y distribución se conservan. Evidencia en C:/Users/CARLAENCINAS/.codex/cafe-grisaceo-20261014/evidence.

## Muestra vigente: recuadros beige grisáceo

La propietaria aportó una muestra plana #E0D7CE. Ese color exacto se aplica a las superficies principales; apoyo #D2C5BB, elevadas #E6DDD5, campos #D6CCC3 y bordes neutralizados acompañan el fondo compartido #D7CEC7. Se conservan acentos aprobados de Inicio, relieve, puntos, estructura y estados.

Verificación de muestra: 4 pruebas de navegador y 14 de arquitectura (405 assertions), ambos builds y 20 vistas sintéticas aprobadas. Cabeceras/tarjetas/filtros usan RGB224215206, igual a la muestra; fondo/sidebar/navbar permanecen #D7CEC7. Sin pérdida de relieve, puntos ni reflow. Evidencia en C:/Users/CARLAENCINAS/.codex/recuadros-grisaceos-20261014/evidence.

## Ajuste vigente: recuadros más claros

La propietaria pide aclarar los recuadros. Superficie principal #E6DED6 y elevada #EBE3DC, conservando el matiz grisáceo, fondo/sidebar/navbar #D7CEC7, campos, acentos, puntos y relieve.

Verificación del aclarado: 4 pruebas de navegador, 14 de arquitectura (405 assertions), ambos builds y 20 vistas sintéticas aprobadas. Recuadros principales #E6DED6 y elevados #EBE3DC; fondo/chrome #D7CEC7 sin cambios. Evidencia en C:/Users/CARLAENCINAS/.codex/recuadros-claros-20261014/evidence.

## Tarjetas compactas con tipografía proporcional

La propietaria pide cards más pequeñas y letras en proporción. El componente compartido de indicadores reduce padding a 8 px y mínimo de altura a 98 px (antes 116 px; móvil 110 px). Cifras 36–38 px, títulos 19–20 px y descripciones 15–16 px, calculados según el ancho de cada tarjeta mediante clamp y unidades de contenedor. Se reserva espacio para el icono y la altura crece con textos largos.

Las tarjetas de residentes usan el mismo criterio: nombres 18–22 px, datos 14–15 px y metadatos de la escala compartida. Avatar de 88 px (80 px en móvil), padding de 14 px y menos espacio vertical vacío. Los nombres, ubicaciones y cuidados largos se muestran completos; los botones conservan un mínimo de 44 px. No se cambian datos, estados, enlaces, colores ni reglas clínicas.

Verificación: ambos builds aprobados; 5 pruebas de navegador contra el build aislado propio y 14 pruebas de arquitectura (405 assertions) aprobadas. Comparación sintética antes/rama/principal: 30 casos de indicadores y 30 de residentes, sin desbordamiento ni recorte. QA independiente PASS en 1440, 1280, 1024, 768 y 390 px, temas claro/oscuro y fuentes compiladas reales. Evidencia privada en C:/Users/CARLAENCINAS/.codex/cards-compactas-20261014.

Límite de integración: al ejecutar las pruebas de shell sobre el build del checkout principal, tres verificaciones de superficies/colores fallan por cambios simultáneos ajenos a esta tarea (transparencias y diferencias entre roles). Las mismas assertions pasan con el CSS aislado de UNIFICACION. Se preserva ese trabajo concurrente; no se declara aprobada toda la apariencia del checkout compartido.


## Inicio de Enfermería basado en la ficha clínica (15/10/2026)

La propietaria pide mejorar Inicio tomando como referencia la ficha clínica actual. Se conservan orden, métricas, enlaces, permisos y estados. Los módulos reutilizan el relieve compartido de la ficha y cabeceras redondeadas con tintes de contexto: salvia para cuidados/residentes, azul para agenda/ubicación/actividad, lavanda para conducta, coral para incidentes y ámbar para la sección de alertas. Esos tintes identifican la función; la prioridad de cada alerta sigue procediendo del backend.

Los títulos usan la escala compartida de 16 px y los subtítulos 12 px. Las acciones de cabecera tienen borde, superficie y foco visibles, con mínimo de 44 px. Controles y filas de residentes ganan separación visual. No se añaden degradados ni superficies blancas ni nuevos datos. En móvil las acciones pasan debajo del título; en oscuro lavanda/coral se mezclan al 16% con la superficie temática para conservar contraste.

Verificación: ambos builds aprobados; 5 pruebas de navegador contra el build aislado de UNIFICACION y 23 pruebas PHP de arquitectura/integración/header (481 assertions) aprobadas. QA independiente PASS en 20 casos sintéticos: ambos builds, cinco anchos y ambos temas, fuentes compiladas reales, sin desbordamiento ni errores JS. Contraste mínimo del texto muestreado 4.828:1 claro y 4.798:1 oscuro; controles de al menos 44 px y foco visible. También se verificó la integración en el Inicio abierto. Evidencia privada: C:/Users/CARLAENCINAS/.codex/inicio-ficha-20261015/qa-evidence. El resumen de alertas conserva su truncado previo y el enlace a detalle; no se certifica la apariencia de otras pantallas ni se atribuye validación clínica a estas comprobaciones.


## Recuperación estructural de DETALLES_ENF (15/10/2026)

La propietaria solicita recuperar únicamente el diseño estructural de la ficha. Referencia localizada: archivo-local/2026-10-11/DETALLES_ENF, commit 69d4c570, vistas históricas cabecera y tab-resumen. Se recupera la geometría común en el patrón clinical-detail.css: tarjetas/contexto con padding de 16 px en móvil y 20 px desde 640 px, separación de columnas de 20 px, cabeceras con separador inferior y 10 px hasta el contenido, pestañas como franja con borde inferior y esquinas superiores de 12 px. El resumen ya conserva tres columnas desde 1024 px y una debajo de ese ancho. Las pestañas pueden envolver en móvil para evitar scroll horizontal.

Se preservan los colores, fondos, sombras, datos, contenido disponible, acciones, permisos y contratos actuales. No se copian lógica, catálogos, cifras de ejemplo, formularios ni backend del archivo histórico. Esta recuperación corresponde a la estructura común; no es una restauración completa del código o contenido de las diez pestañas antiguas.

Verificación: ambos builds aprobados, 18 pruebas PHP (419 assertions) y 5 de navegador contra el build aislado de UNIFICACION aprobadas. QA independiente PASS en 30 casos sintéticos antes/rama/principal, cinco anchos y dos temas: geometría conforme, colores del principal iguales a su estado previo, sin desbordamiento ni errores JS, foco y controles de al menos 44 px. Integración revisada en la ficha abierta. Evidencia privada en C:/Users/CARLAENCINAS/.codex/ficha-estructura-20261015/qa-evidence; alcance y límites en QA_INDEPENDIENTE.md.


## Continuación estructural: Cuidados y comprobación de pestañas (15/10/2026)

La continuación completa la composición interna de Cuidados con bloques identificables de cabecera, indicadores, intervenciones e historial, manteniendo las cuatro columnas que tienen datos reales. Se añaden clases semánticas al Blade actual y geometría en clinical-detail.css. Desde 768 px las intervenciones se comparan en tabla; debajo se apilan cuidado, descripción, último registro y acción, con etiquetas y cabeceras accesibles. El botón Registrar conserva un mínimo de 44 px y la columna deja de heredar el estrechamiento/sticky de tablas generales. Los estados vacíos y de solo consulta mantienen su información y permisos.

No se restauran valores simulados, campos sin contrato, formularios ni lógica del archivo histórico. Backend, validaciones, métricas, autoría y permisos permanecen intactos. Se conservan los colores actuales, con una adaptación mínima de accesibilidad: la tinta de Registrar en oscuro consume el texto del tema, porque la combinación anterior tenía contraste 1.12:1. Contraste comprobado tras corregir: 13.08:1 normal y 11.27:1 hover.

Verificación: ambos builds aprobados; 21 pruebas PHP de Cuidados/ficha/arquitectura (439 assertions), exportación sintética de diez pestañas (11 assertions) y 5 pruebas de navegador del build aislado aprobadas. QA independiente de Cuidados: 20 casos, cinco anchos y dos temas en ambos builds; filas extensas, estado vacío y solo consulta, foco/Tab, controles de 44 px, sin recortes ni desbordamientos. Montaje del HTML real exportado de diez pestañas: 100 combinaciones de ancho/tema, sin errores JS, desbordamiento de página, pérdida de pestañas o literales inválidos; esta matriz precede únicamente al ajuste de tinta oscuro, revalidado aparte por QA. Integración observada en la ficha abierta, pestaña Cuidados.

Límites: algunos listados de Signos, Medicación, Eventos, Alertas, Estudios y Documentos conservan desplazamiento interno previo. No se declara recuperada toda función antigua ni se confunde esta comprobación estructural con validación clínica. Evidencia privada: C:/Users/CARLAENCINAS/.codex/ficha-cuidados-estructura-20261015/{qa-evidence,all-tabs-final}; dictamen en QA_INDEPENDIENTE.md. Ningún dato clínico real se guardó en fixtures o capturas de prueba.
