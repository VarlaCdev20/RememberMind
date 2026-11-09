---
title: "Gerencia: preparación y exportación PDF/CSV"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SPECIFIED_AGGREGATE_EXPORT_AND_SECURITY_CONTRACT
runtime_verified: false
---

# Exportación gerencial

## Acceso desde cada vista

Gerencia abre «Exportar reporte» en la cabecera de la consulta. Inicio prepara el resumen institucional. Personal, Documentación, Solicitudes y entregas, Áreas, Planificación, Cobertura, Residentes, Familiares y contactos, Ocupación, Alertas y Galería ofrecen indicadores de su propio ámbito. Los avisos de planificación ofrecen su reporte cuando la bandeja está habilitada.

El botón abre una ventana emergente en la consulta de origen. Permite elegir PDF o CSV y seleccionar todo el resumen o determinados indicadores. La descarga mantiene la consulta abierta y los errores se muestran dentro de la ventana. Cobertura conserva los filtros aplicados. Planificación conserva el período, área y turno, con una nota sobre la búsqueda por trabajador. Los demás reportes son resúmenes generales de su vista. Cada archivo conserva el alcance agregado aprobado en 26B/C y revalida el permiso de su fuente al descargar.

Inicio, Reportes, la ventana de exportación y el PDF comparten una proyección de gráficas por unidad. El selector ofrece diseño recomendado, dona, torta, radar (estrella) y barras. Donas y tortas se habilitan para categorías excluyentes; el total no se suma como otra porción. Cuando existe un total y categorías parciales, «Resto de registros» es su diferencia explícita. El radar compara al menos tres conteos de la misma unidad desde cero; no expresa una puntuación ni una meta institucional. Si el tipo elegido no es aplicable, se conserva la comparación en barras con una explicación. Los valores cero y las fuentes no disponibles siguen diferenciados. El PDF conserva el tipo elegido y el CSV los valores originales.

Por corrección expresa de la propietaria del 08/11/2026, los paneles nuevos de gráficas se limitan a Inicio y Reportes, que reutilizan su proyección ya cargada. Personal y las demás vistas operativas conservan sus tablas, tarjetas, filtros y paginación anteriores, con su botón de exportación en la cabecera. La ventana y los archivos exportados mantienen las gráficas y los filtros de su reporte. Se conserva el gráfico físico de Ocupación que existía antes de esta ampliación.

## 1. Recorrido y contenido

Desde la consulta de origen, «Exportar reporte» abre el modal compartido. Muestra el contexto, el formato y una lista de los indicadores disponibles con sus valores. Ambos formatos contienen la selección autorizada y conservan sus fórmulas y filtros.

Datos visibles: nombre del reporte, sección, referencia temporal, filtros efectivos, hora de consulta, hora de generación, ámbito autorizado, definiciones, unidades y limitaciones. Las fechas de consulta y generación pueden diferir: deben identificarse por separado. El autor generador procede de la sesión y auditoría, nunca de un campo editable para firmar por otro. La identidad técnica no se imprime al usuario como explicación.

PDF incluye tabla y definiciones; gráficas solo si sus valores son iguales a la tabla y el render local es legible. CSV conserva filas agregadas con indicador, valor, unidad, referencia, alcance y calidad/límite pertinente; no incrusta un gráfico ni omite ausencias para aparentar integridad. No hay archivos laborales, diagnósticos, nombres de residentes o listados masivos en anexos ocultos.

## 2. Igualdad entre consulta, revisión y descarga

Reutilizar una única consulta/proyección autorizada del módulo Reportes. No construir SQL distinto en Blade, hoja CSV y PDF. Conjunto plano: sección, filtros normalizados, referencia temporal, filas de indicadores, unidad, calidad y origen de definición. No pasar Models con relaciones completas al renderizador.

Cada preparación corresponde al conjunto efectivamente consultado. Si los datos actuales cambian antes de descargar, el sistema no debe recalcular silenciosamente y entregar otro contenido con la hora antigua: debe conservar legítimamente el conjunto en el alcance técnico disponible o invalidar la preparación y solicitar actualización/revisión con la nueva hora. No afirmar que existe una instantánea SQL o archivo histórico persistido por usar un token de sesión. La igualdad y los cambios concurrentes requieren pruebas al implementar.

Se revalida cuenta, permiso y alcance al preparar y descargar. El navegador no envía valores de indicadores confiables, rutas de plantilla, destinatarios o nombres de archivos arbitrarios; el servidor deriva todo desde el contexto válido. Una URL de descarga manipulada no permite elegir otro rol, población o reporte nominal.

## 3. Permisos y privacidad

26B/C autoriza funcionalmente PDF/CSV agregados con auditoría; no adopta una sustitución entre `reportes.institucional`, `reportes.exportar_pdf`, `areas.reportes` o permisos de otros reportes. [Rutas actuales](../../../../app/Modulos/Reportes/Presentacion/Rutas/web.php) utilizan condiciones distintas según familia. Antes de implementar hay que fijar el mapeo técnico mínimo de la decisión ya aprobada, aplicar backend/contexto y probar consulta/descarga por separado; no conceder `reportes.*` para que aparezca el botón.

No publicar archivos en una carpeta web pública ni enviarlos a un servicio externo de renderizado. Reutilizar infraestructura local existente: [ReportExportService](../../../../app/Modulos/Reportes/Infraestructura/Exportaciones/ReportExportService.php), [nombres de archivo](../../../../app/Modulos/Reportes/Infraestructura/Exportaciones/ReportFileNameService.php) y las plantillas permitidas. Verificar cada motor de PDF/fallback, fuentes locales y tratamiento de recursos remotos, sin tomar configuración de un controlador como garantía de todos los recorridos.

Las salidas siguen siendo información institucional: agregado no significa anonimización garantizada, especialmente en grupos pequeños. No se inventa un umbral de supresión de celdas; si se define una política de divulgación externa, necesita su propia decisión. Esta entrega no habilita publicación externa ni correo automático.

## 4. Validación de archivos y auditoría

- PDF: título/período o referencia actual, consulta/generación, ámbito, unidades, definiciones, límites y paginación. No cortar columnas o imprimir botones. Exportar solo contenido autorizado, sin HTML de formularios o atributos ocultos.
- CSV: UTF-8, acentos y separador/escape consistentes, números documentados y ausencias distinguibles de cero. Neutralizar fórmulas de hoja de cálculo en campos textuales sin alterar valores numéricos válidos; probar comillas, saltos de línea y etiquetas con prefijos peligrosos.
- Nombre seguro sin datos nominales, por ejemplo `reporte-gerencial-resumen-2026-10-26.pdf`; Content-Type y disposición de descarga apropiados. No exponer rutas internas.
- Registrar auditoría mediante Activitylog existente: actor, acción, formato, reporte, filtros mínimos no sensibles, hora, resultado y alcance pertinente. No crear otra tabla corporativa. Generación/respuesta emitida no demuestra que el usuario guardó el archivo en su dispositivo.
- Ante fallo no devolver un archivo vacío con éxito ni marcar «Descargado». Informar error seguro y permitir reintento. Si se distingue intento/fallo/resultado, los eventos deben decir exactamente qué ocurrió, sin datos clínicos o SQL en logs.

## 5. Formularios y estados

El formato y los indicadores son las selecciones de la ventana. Los valores consultados son de lectura. PDF y CSV usan radios con etiqueta y ayuda. «Descargar PDF» cambia a «Descargar CSV» al elegir el formato. Los indicadores se marcan mediante casillas y se requiere al menos uno. Cerrar devuelve el foco al botón de origen y conserva la consulta y sus filtros.

Carga y generación muestran estado anunciado, prevención de doble envío y botón deshabilitado temporalmente; un fallo permite reintentar. Si faltan permisos, la denegación no simula un reporte con menos filas como si fuera completo. Fuentes insuficientes se indican; no son éxito total ni cero automático. Un reporte con cero comprobado puede exportarse con esa explicación, a diferencia de una consulta fallida.

## 6. Alcance de esta entrega

Preparación y contrato de descarga especificados, con [imagen](28-DISENO-IMAGENES-REPORTES.md) y [casos](27-ACEPTACION-REPORTES-GERENCIA.md). No se generó un reporte institucional real, no se instaló un permiso, no se cambió el motor de exportación ni se modificó la BDD. La representación técnica de un conjunto preparado y su caducidad deben implementarse y probarse sin crear persistencia histórica ficticia o campos ajenos.

### Verificación de gráficas del 08/11/2026

Sobre el árbol local, se verificaron las barras del modal y del PDF con datos sintéticos: selección parcial, agrupación por unidad, conteos cero y fuentes no disponibles. El modal se comprobó en 1440, 1280, 1024, 768 y 390 px; se revisaron las descargas PDF/CSV, el retorno del foco y el modo oscuro móvil. Se inspeccionaron los PDF renderizados, incluido el resumen de seis indicadores en una página. Pasaron 11 pruebas de exportación gerencial con 156 aserciones, dos pruebas JavaScript y la compilación de producción. Esta evidencia corresponde a presentación y exportación local; no es una nueva aceptación integral de Gerencia ni una prueba nueva de PostgreSQL.

Ampliación posterior del mismo día: cinco pruebas de presentación (23 aserciones), cinco pruebas de integración del reporte (60 aserciones) y tres pruebas JavaScript verifican tipos de gráfica, denominadores, ausencia de datos, selección preparada y permisos. Se recorrieron 13 vistas en escritorio y móvil, se ejercitaron dona/torta/radar y se inspeccionaron sus PDF renderizados. La revisión final comprobó leyendas y notas completas en los cinco anchos, temas claro/oscuro y actualización Livewire sin duplicar instancias. Se conserva el gráfico físico existente de Ocupación. Compilación de producción correcta; sin cambios de BDD ni permisos nuevos. Evidencia de navegador generada exclusivamente con datos sintéticos y base temporal aislada.

Corrección posterior solicitada por la propietaria: retirados los paneles nuevos de las diez vistas operativas y la exportación repetida dentro de las tarjetas de Personal. El contenido de la vista de Personal coincide con la versión anterior, salvo el botón de reporte de cabecera ya solicitado. Verificación sintética en navegador: tabla en cinco anchos, cambio a tarjetas sin botones de exportación duplicados e Inicio/Reportes conservando las gráficas. Compilación correcta. Esta corrección sustituye la ubicación en vistas operativas descrita en la ampliación anterior.
