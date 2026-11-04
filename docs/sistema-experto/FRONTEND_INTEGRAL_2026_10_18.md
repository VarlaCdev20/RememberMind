# Sistema experto — implementación frontend y verificación

Fecha: 18/10/2026. Proyecto: RememberMind. Ruta: http://127.0.0.1:8000/admin/sistema-experto.

Se implementó la experiencia disponible dentro de los contratos y permisos actuales. **No se declara cumplido todo el PROMPT MAESTRO 02**: la escritura de conocimiento, conservación durable y publicación de informes técnicos carecen de habilitación web; la aceptación visual integral sigue pendiente.

## Antes y alcance

Existía ConocimientoPanel, una pantalla que mezclaba versiones, catálogo técnico, candidatos y demostración COG-MEM. La integración de laboratorio anterior referenciaba un parcial ausente. Existía también ResultadosExpertoResidente y sus parciales clínicos, con políticas independientes para Medicina, Psicología y Enfermería.

Se transformó únicamente la pantalla administrativa de Sistema experto, manteniendo su ruta, sidebar compartido, arquitectura modular, marca y sistema de diseño. Se conservaron los cambios previos y el trabajo simultáneo de la conversación SISTEMA EXPERTO. No se cambiaron tablas, columnas, permisos, roles ni reglas del motor. La separación asistencial permanece.

## Pantallas entregadas

| Superficie | Comportamiento real |
| --- | --- |
| Laboratorio | Aviso experimental, selector de escenario, ejecución explícita, cobertura obtenida de catálogos del motor y restricciones visibles. Entrar no ejecuta automáticamente. |
| Casos sintéticos | Once entradas del generador real, descripción, corte, versión, tipo, búsqueda, orden, filtros y paginación. Selección y ejecución reales. El estado y última ejecución corresponden a esta sesión. |
| Resultados | Identificación, fecha real de ejecución, corte original, manifiesto de versiones, resumen, cinco dominios, interpretación, evidencias, reglas, limitaciones, Petersen y trazas. |
| Fundamento de dominio | Detalle desplegable con subcomponentes, hallazgos múltiples, evaluabilidad, reglas, contexto, confusores y rutas de soporte. No afirma evaluadas las tareas pendientes de Memoria. |
| Evidencias | Fuente, identificador de registro, fecha, componente, rol, admisibilidad, mapeo, criterio y motivos. Detalle autorizado sin respuestas originales. |
| Petersen | Requisitos C01–C05, estados A/P/U/I/X con explicación, soportes, patrón candidato o abstención real, versión y motivos legibles con códigos conservados en detalle. |
| Conocimiento | Paquete experimental incorporado o versión almacenada. Catálogo de reglas con filtros de dominio, estado, subcomponente y texto; detalle de condiciones, consecuencias y fundamento. |
| Catálogo general | Conceptos, variables, dominios, valores, relaciones, fuentes y mapeos procedentes del paquete seleccionado. |
| Red semántica | Nodos y relaciones reales, filtro de dominio, selección, recorrido de relaciones inmediatas y ampliación/reducción. No contiene un grafo decorativo ni nodos inventados. |
| Versiones | Fechas, responsable, estado, retiro y antecesora cuando existen. Comparación de reglas por contenido semántico; distingue añadidas, retiradas, modificadas y sin cambios. |
| Verificación | Explica la ausencia del contrato de publicación de suites/commit/métricas; no presenta cifras de pruebas inventadas. |
| Historial temporal | Hasta seis instantáneas propias de sesión, consultables sin volver a inferir. Caducan con la sesión y no sustituyen un archivo institucional. |

Se agregaron dos escenarios de **entrada**, sin salidas precalculadas: tareas sin hallazgos sintéticos y evaluación no interpretable. Usan literales y compuertas ya admitidos por el motor. Los once escenarios incluyen positivos, negativos, insuficientes, contradictorios, no interpretables y los cuatro patrones candidatos Petersen.

## Componentes e integración

Archivos propios principales:

- app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ConocimientoPanel.php: navegación, filtros, selección, paginación e interacciones; autorización revalidada con el usuario actual.
- app/Modulos/ApoyoDecision/Aplicacion/DTOs/PresentarResultadoLaboratorio.php: proyección determinista, cantidades auténticas, traducción de estados/motivos y eliminación de respuestas originales.
- app/Modulos/ApoyoDecision/Aplicacion/Consultas/ExplorarConocimientoExperto.php: consulta y comparación autorizadas; normaliza variables mediante nodos semánticos y fundamentos mediante observacion.
- app/Modulos/ApoyoDecision/Infraestructura/Simulacion/SesionLaboratorioExperimental.php: instantáneas temporales separadas por actor y limitadas a seis.
- Siete nuevos parciales Blade en Presentacion/Vistas/livewire/superadministrador/sistema-experto: casos, datos, ejecutar, explorador, historial-sesion, red-semantica y resultado-integral.
- config/vistas_modulares.php: registro de esos parciales.
- resources/frontend/styles/design-system/patterns/expert-laboratory.css: adaptación visual usando tokens y componentes institucionales existentes.
- tests/Feature/LaboratorioExpertoFrontendTest.php: 23 pruebas propias.

La ejecución utiliza ConsultarLaboratorioExperimental → EscenariosPerfilExperimental → EjecutarPerfilExperimental y el constructor de resultados existente. La consulta almacenada utiliza ConsultarConocimientoAdministrativo y CargadorConocimiento. La capa de presentación no interpreta instrumentos, no resuelve contradicciones y no convierte hallazgos descriptivos en estados Petersen.

## Permisos y acciones restringidas

El acceso exige cuenta ACTIVA, rol SUPERADMINISTRADOR, permiso auditoria.ver y ausencia de previsualización de otro rol, conforme a VersionModeloExpertoPolicy. Se protege la ruta, cada acción Livewire y las consultas. Las instantáneas no pueden consultarse desde otra cuenta. La ejecución solo funciona en local/testing.

Medicina y Enfermería conservan sus alcances clínicos existentes. No se les agrega navegación ni permiso de investigación.

Crear, versionar, habilitar y archivar siguen deshabilitados en web: GestionarConocimientoExperimental exige consola/testing y base desechable. Por ello no se creó un asistente de guardado que aparente una operación autorizada. No se borran reglas ni se modifica conocimiento histórico.

La selección de una revisión almacenada para ejecutar, su comparación de impacto sobre casos, la reproducción histórica y el archivo durable permanecen en sus contratos de consola. La comparación web muestra contenido, no impacto clínico.

La exportación web queda pendiente: no se habilitó un botón PDF/Excel que aparente un resultado durable autorizado. La infraestructura PDF del proyecto, por sí sola, no habilita el archivo experimental desde web.

## Pruebas ejecutadas

- Suite final de interfaz y consulta: **29 pruebas, 300 aserciones, todas correctas**.
- Grupo propio previo con arquitectura: **51 pruebas, 5747 aserciones, todas correctas**, antes de la última regresión de comparación.
- Verificación independiente coordinada: grupo SQLite de interfaz, consulta, consola y arquitectura **55 correctas/5787 aserciones**, más regresión final corregida de comparación **1/6**.
- PostgreSQL desechable independiente: grupo con **55 pruebas correctas**, un fallo inicial del fixture de comparación; la repetición corregida de esa prueba pasó **1/6**. No se presenta ese primer lote como íntegramente verde.
- npm test: **218 correctas, 0 fallidas y 2 omitidas** de 220 existentes.
- npm run build: **PASS**, ejecutado inicialmente y repetido por la tarea coordinada.
- Pint de los cinco archivos PHP propios y git diff --check: **PASS**.
- No se ejecutó la suite PHP global completa como parte de este frontend.

Cobertura: once ejecuciones equivalentes al motor real; cinco dominios; resultado y traza; ausencia de respuestas originales; instantáneas inmutables al navegar; historial propio; búsqueda y paginación independientes; reglas y relaciones reales; comparación con PK regeneradas y fundamento cambiado; error de integridad sin exposición interna; fallo de ejecución que conserva el resultado anterior; ID ajeno; entrada arbitraria; cuenta inactiva; permiso retirado; roles clínicos; previsualización y producción.

## Verificación visual y funcional

Se usó CUA sobre un harness HTTP aislado en :8879: Laravel/Livewire y motor auténticos, SQLite :memory: reconstruida por solicitud y cuenta sintética de pruebas. No se sustituyó la ejecución por respuestas simuladas del frontend. El harness es temporal, externo al producto y no cambia .env ni datos operativos.

Se ejercitaron ejecución positiva y contradictoria, abstención real, navegación, filtro móvil sin coincidencias, fundamento desplegable, red, selección con Enter y ampliación al 110%. Se observó carga y se comprobaron labels y controles de al menos 44 px en la muestra revisada. No hubo errores/warnings de consola en la consulta muestreada.

| Ancho objetivo | CSS efectivo medido | Desbordamiento de documento/laboratorio |
| --- | --- | --- |
| 1440 | 1441 | No / No |
| 1280 | 1281 | No / No |
| 1024 | 1024 | No / No |
| 768 | 768 | No / No |
| 390 | 391 | No / No |

El zoom del IAB era 72%; se compensaron dimensiones y se registró el tamaño efectivo. Los nombres de capturas indican el objetivo; qa-responsive.json conserva las medidas. Se inspeccionaron las cinco capturas. La de 768 conserva el drawer compartido abierto durante el cambio desde escritorio; no acredita revisión de esa pantalla con drawer cerrado. Algunas capturas del IAB incluyen margen y suavizado de imagen. Las capturas oscuras posteriores usan el recorte nativo de CUA de la superficie visible, sin edición de imagen; su ancho efectivo era aproximadamente 352 CSS px.

Tema claro y oscuro observados. Contraste muestreado de texto principal sobre superficie oscura: aproximadamente **14,91:1**; no certifica todos los textos, estados o nivel WCAG. La navegación con lector de pantalla y la matriz completa de pantallas/estados/temas/reduced-motion no se verificaron.

En :8000 se comprobó la sesión habitual de Enfermería: acceso directo a /admin/sistema-experto rechazado con **403**. Se restauró su dashboard sin cambiar sesión, cuenta ni permisos. Para ver el laboratorio habitual debe acceder una cuenta Superadministrador autorizada.

**Dictamen de aceptación visual integral: FAIL por evidencia incompleta**, no por un desbordamiento observado. La verificación parcial y las limitaciones anteriores se conservan explícitamente.

## Pendientes y límites

1. Contrato y autorización web de creación/versionado/archivo de reglas; formularios de escritura solo cuando existan esas condiciones.
2. Conservación durable y consulta histórica experimental autorizadas para HTTP; selección de revisiones para ejecutar y comparación de impacto.
3. Exportación auténtica sobre ese resultado durable y autorizado.
4. Consulta de auditoría de modificaciones y publicación de resultados de pruebas/commit desde un contrato backend.
5. Definiciones, fuentes o metadatos que el backend no entrega; se muestran ausencias en lugar de inventar valores.
6. Completar la aceptación visual de todas las pantallas y estados, drawer cerrado en tablet, teclado exhaustivo, lectores de pantalla, contraste general y movimiento reducido.
7. Revisión clínica/instrumental de cualquier futuro uso asistencial, fuera del alcance del laboratorio.

No se hizo commit ni push: el checkout contiene trabajo previo y simultáneo del motor, la persistencia y este frontend en la misma rama. No se cambió esa rama ni se mezcló ese trabajo en un commit automático.
