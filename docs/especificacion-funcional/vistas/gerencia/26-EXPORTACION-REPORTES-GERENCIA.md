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

## 1. Recorrido y contenido

Desde [Reportes](25-REPORTES-GERENCIA.md), Preparar exportación abre una vista de revisión. Izquierda: contexto leído y selección PDF/CSV; derecha: vista previa del contenido agregado. En móvil se apilan, con tabla alternativa legible. El formato no cambia la población, fórmulas o filtros; solo la representación.

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

Solo el formato es selección en preparación; los datos del reporte son de lectura. PDF/CSV funcionan como radios con label/ayuda; Descargar PDF cambia a Descargar CSV al seleccionar. Cancelar y Volver recuperan consulta/filtros. No hay Guardar como cierre institucional ni formulario de firma médica.

Carga y generación muestran estado anunciado, prevención de doble envío y botón deshabilitado temporalmente; un fallo permite reintentar. Si faltan permisos, la denegación no simula un reporte con menos filas como si fuera completo. Fuentes insuficientes se indican; no son éxito total ni cero automático. Un reporte con cero comprobado puede exportarse con esa explicación, a diferencia de una consulta fallida.

## 6. Alcance de esta entrega

Preparación y contrato de descarga especificados, con [imagen](28-DISENO-IMAGENES-REPORTES.md) y [casos](27-ACEPTACION-REPORTES-GERENCIA.md). No se generó un reporte institucional real, no se instaló un permiso, no se cambió el motor de exportación ni se modificó la BDD. La representación técnica de un conjunto preparado y su caducidad deben implementarse y probarse sin crear persistencia histórica ficticia o campos ajenos.
