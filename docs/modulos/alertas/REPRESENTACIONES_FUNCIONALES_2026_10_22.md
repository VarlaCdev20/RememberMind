# Representaciones funcionales distintas por rol

## UX FLOW previo a implementar

Solicitud: tipos visuales diferentes con propósito, campos y continuación propios. La red SVG anterior cambia su geometría, pero conserva el mismo lenguaje de nodos en todos los roles; eso no satisface por completo la petición.

Entrada: alerta dentro del ámbito y selección profesional autorizados. Objetivo: comprender el caso desde la tarea del rol, consultar el dato que lo respalda y continuar en el formulario existente de su competencia. Salida: detalle o registro profesional autorizado; la consulta nunca confirma atención, derivación ni cierre.

| Área | Representación principal | Pregunta y siguiente paso existente |
|---|---|---|
| Enfermería | Línea de tiempo | ¿Qué ocurrió y qué actuación quedó documentada? Consultar intervención y seguimiento. |
| Medicina | Contraste de evidencia y revisión | ¿Qué dice la fuente y qué consta en la nota clínica? Abrir la nota propia autorizada. |
| Psicología | Red relacional | ¿Qué observaciones de ánimo/conducta e interacción constan? Consultar o registrar valoración psicológica. |
| Nutrición | Panel de alimentación, apoyo hídrico y continuidad | ¿Qué dato alimentario e hídrico está guardado? Consultar o registrar valoración nutricional. |
| Fisioterapia | Matriz de capacidades y autonomía | ¿Qué está descrito para cada capacidad, asistencia y supervisión? Consultar o registrar valoración funcional. |
| Pedagogía | Recorrido de observación de actividad | ¿Qué participación, desempeño y cambio se registró? Consultar o registrar seguimiento pedagógico. |
| Administración | Tablero de situación | ¿En qué situación está el aviso y quién lo sigue? Abrir coordinación existente. |
| Gerencia | Árbol de responsabilidad | ¿Quién está a cargo y qué vínculos profesionales constan? Consultar responsable e historial. |
| Superadministración | Cadena de procedencia | ¿Qué fuente, autoría y eventos respaldan el caso? Consultar detalle completo autorizado. |

La red completa permanece como exploración secundaria ajustable para los roles cuya representación principal es otra. No se transforma texto libre en puntuaciones, porcentajes, diagnóstico, derivación o balance medido. El requerimiento hídrico en mL por día no se compara con consumo inexistente.

Cada elemento seleccionable abre los campos autorizados en el inspector compartido. Los grupos separan origen verificable de contexto del residente y explican qué falta. La cronología usa eventos guardados; el tablero sitúa un caso en su estado persistido sin marcar pasos como completados por posición visual.

Sin caso: mostrar el esquema de trabajo y sus campos con la indicación «Sin caso seleccionado», sin nombres, métricas ni avisos ficticios. La página de comparación por rol permite inspeccionar esas estructuras sin datos personales e iniciar la previsualización oficial. Familia mantiene su portal y no recibe un panel clínico.

Captura: conservar campos, validaciones, autoría, dirty state y permisos vigentes. Se explica el propósito del formulario de cada especialidad y su continuidad; no se añade un sistema paralelo de solicitudes.

Fuentes: contrato visual compartido, documentación de mapas/vínculos, `ExperienciaAlertas`, `CamposRegistroEspecialidad`, lector de traza, acciones de registro existentes y middleware de preview. No aplica rediseño de reglas clínicas ni esquema de BDD. La referencia previa se adapta a la nueva instrucción explícita sobre tipos visuales. Etapas: flujo, visualización, componentes, movimiento reducido, escritura y QA.

Acceso a navegador: intento actual de abrir `http://localhost:8896/admin/alertas-clinicas` rechazado por Work con «Local browser use is disabled in Work settings». No se acredita sesión ni inspección visual real.

## Implementación y contrato de datos

`ComponerVistaCasoAlerta`, en `app/Modulos/Alertas/Presentacion/Soporte`, compone exclusivamente el DTO ya autorizado de `ConsultarTrazaAlerta`. No hace consultas, crea registros ni cambia autorizaciones. `estadoCodigo` contiene el estado guardado para ubicar el caso en el tablero; los estados históricos no reconocidos, incluido `ACTIVO`, permanecen en «Registro anterior». No se convierten en avisos abiertos.

La representación se renderiza en `components/ui/alerta-vista-profesional.blade.php`; `alerta-dato-consultable` permite consultar la información completa del elemento en el inspector. `alerta-trazabilidad` abre la vista principal del área; Psicología abre directamente la red. Los demás roles disponen de «Explorar vínculos completos» como opción secundaria. Pantalla completa funciona sobre el componente completo, incluida la vista profesional; la altura, acercamiento y organización local pertenecen a la red.

| Tarea | Datos y organización | Continuación conectada |
|---|---|---|
| Enfermería | Fuente verificable, últimos eventos realmente registrados en secuencia y responsable del seguimiento. No marca actuaciones inexistentes. | Detalle con intervención, seguimiento y cierre sujetos a sus permisos. |
| Medicina | Fuente a un lado y nota clínica vigente en el otro: tipo y contenido de la evolución. El contexto del residente no se atribuye como causa del aviso. | Formulario de evolución médica existente. |
| Psicología | Red con ánimo, afecto, ansiedad, apatía; percepción, conducta, comunicación, interacción e impresión profesional; conclusión y recomendación. | Formulario de valoración psicológica existente. |
| Nutrición | Bloques de estado nutricional, apetito, deglución y riesgo valorado; asistencia, requerimiento hídrico y restricciones; conclusión y recomendación. | Formulario nutricional; requerimiento hídrico en **mL por día**, unidad confirmada por el propietario. |
| Fisioterapia | Tabla semántica aspecto/registro: marcha, equilibrio, traslado, fuerza y resistencia; autonomía diaria, supervisión y dependencia valorada; conclusión y recomendación. | Formulario de valoración funcional existente. |
| Pedagogía | Recorrido de observación: atención, comprensión, ejecución, reconocimiento, orientación, participación e interacción; cambio de desempeño y continuidad. | Formulario de seguimiento pedagógico existente. |
| Administración | Un caso situado en una única columna según su estado persistido; responsable consultable. El tablero representa situación, no un nuevo ciclo obligatorio. | Detalle y coordinación operativa según permiso. No concede escritura clínica. |
| Gerencia | Responsable del seguimiento, caso y ramas de vínculos profesionales efectivamente disponibles. | Consulta del detalle y responsabilidad según permiso. |
| Superadministración | Lectura ordenada de caso, fuente y responsabilidad/eventos. La autoría se consulta en el registro que la acredita; responsable no es sinónimo de autor. | Inspección del detalle completo autorizado. |

Cuando hay una valoración de contexto consultable, su fecha, autor y atención/área se muestran junto al contenido. Los bloques conservan el índice de su sección aunque falte otra sección: un dato no cambia de significado por desplazarse visualmente. «No» se conserva como dato y no se confunde con falta de registro. Los campos permanecen definidos por `CamposRegistroEspecialidad`; no se añaden catálogos ni campos a la BDD.

## Interacción, seguridad e integración

Seleccionar un dato enfoca el inspector con sus campos completos. Si una capa lo ocultaba, se restaura la vista completa; no se aceptan identificadores ajenos al DTO. Cambiar entre área, vínculos y fichas, mover elementos o ajustar la vista no persiste datos. Las relaciones completas continúan disponibles y conservan el sentido y procedencia previamente autorizados.

La acción de continuación utiliza `abrirRegistroEspecialidad` para las cinco especialidades y `verDetalle` para los demás roles. Cierra pantalla completa antes del modal, evita doble apertura y comunica el fallo sin simular éxito. Los formularios conservan validación, autoría del usuario, relación con la atención propia y bloqueo de guardado en previsualización. Se añade una explicación del propósito del rol. Abrir un formulario no crea una solicitud, derivación ni atención.

La comparación en `/admin/alertas-clinicas/por-rol` muestra las nueve estructuras sin datos personales y conserva el acceso a la previsualización oficial. Familia muestra su contrato de portal sin recibir una representación clínica. Se reutilizan los iconos Phosphor, tokens, superficies y movimiento reducido del Design System. Los textos largos envuelven; tablas y columnas se adaptan al espacio disponible. Esto describe implementación, no aceptación responsive verificada visualmente.

No hay migraciones, seeders, permisos nuevos, dependencias nuevas ni escrituras sobre la base institucional. Tampoco se incorporan nombres ficticios a las pantallas para completar áreas sin registros. No se modifica la inferencia del sistema experto ni se introducen umbrales clínicos. No se añade dependencia ejecutable V1 en los archivos cambiados.

## Verificación y pendientes

Se ejecutan pruebas acotadas del compositor, integración Livewire de las cinco especialidades, estados históricos, comparación de los diez roles, transporte de consulta y recuperación de interacción. La primera ejecución detectó un error de compilación Blade en el contenedor de la representación; se corrigió usando el bloque `@php` completo y se volvió a verificar. Los resultados definitivos se registran a continuación.

QA visual: **FAIL por evidencia incompleta**, no por un defecto visual observado. Pendientes: sesión real de Superadministración, previsualizaciones de cada rol, temas claro/oscuro, teclado/foco, formularios bloqueados y reflow en 1440, 1280, 1024, 768 y 390 px. Work impide el control del navegador; no se intenta eludir esa configuración ni se acredita un ingreso que no pudo realizarse.

Este seguimiento está en la rama oficial `feature/apoyo-decision-informe-neurocognitivo`, después de la integración ya publicada `ec302e33`. Los cambios de esta representación son posteriores a aquel push; no se declaran publicados por pertenecer a la misma rama.

### Resultados definitivos del 22 de octubre

| Verificación | Resultado y alcance |
|---|---|
| PHPUnit: `ComponerVistaCasoAlertaTest`, `AlertasFlujosPorRolTest` y `test_comparacion_muestra_diez_roles_sin_datos_personales_y_no_concede_escritura_clinica` | **PASS: 8 pruebas, 421 aserciones**, 63,451 s. SQLite `:memory:` protegido por `Tests\TestCase`; fixtures aislados. No es una certificación clínica ni PostgreSQL. |
| `node --test tests/Frontend/alertas-trazabilidad.test.js` | **PASS: 13 pruebas**. Incluye selección autorizada, modo principal, consulta del inspector, organización local, zoom/capas, teclado, resize, pantalla completa, recuperación y apertura única del formulario existente. |
| Compilación Blade y `php -l` de los tres componentes | **PASS** después de corregir la directiva inicial del contenedor. La integración posterior renderiza también formularios y la comparación por roles. |
| Pint `--test` sobre compositor, lector y tres archivos PHP de pruebas tocados | **PASS**. |
| `npm run build` | **PASS**, 12,46 s en la última ejecución. |
| `git diff --check` y búsqueda acotada de V1/debug/TODO en componentes nuevos | Sin hallazgos en ese alcance. |
| Navegador, responsive y accesibilidad reales | **Pendiente**. Apertura del panel de comparación solicitada mediante `open_in_codex`: respuesta `queued`; eso no confirma navegación, autenticación ni renderizado. |

Evidencia local de ejecución, excluida de Git: `output/alertas-20261021/representaciones-roles.xml`, `representaciones-roles.log`, `representaciones-interaccion.log` y `representaciones-build.log`. No se volvió a ejecutar la suite global ni se oculta la incidencia preexistente de color de foco documentada en `PUBLICACION_E_INTEGRACION_2026_10_22.md`.

Commit/push de este seguimiento: **no realizados**. La implementación y documentación quedan locales y revisables; la aceptación visual esencial sigue pendiente. Los artefactos temporales de `output/` no forman parte del cambio entregable.

## Guía de consulta y revisión adicional

1. En `/admin/alertas-clinicas/por-rol`, elegir una especialidad permite comparar su estructura, propósito y campos. Esta página no carga residentes: los apartados vacíos explican qué datos utilizará cada área.
2. Con una sesión de Superadministración, pulsar «Ver bandeja de este rol» inicia la previsualización oficial de solo lectura. La identidad original se conserva; el retorno se realiza mediante «Volver a Superadministrador».
3. En la bandeja, seleccionar una alerta con la acción propia del rol: por ejemplo, «Continuidad del cuidado», «Abrir revisión médica», «Revisar contexto nutricional» o «Consultar autonomía y movilidad». La representación corresponde al caso seleccionado y sus datos autorizados.
4. La vista principal es específica del área. «Explorar vínculos completos» abre la herramienta común de relaciones; Psicología abre directamente «Red de bienestar». Por ello, comparar únicamente la exploración secundaria no permite evaluar las diferencias entre las representaciones principales.
5. Seleccionar un elemento muestra su información en el inspector. Desde la red se puede enfocar la selección, cambiar capas, ajustar altura/acercamiento o consultar el historial. El botón de continuación abre el formulario correspondiente, con campos de consulta y guardado bloqueado durante la previsualización.

Criterios para revisar un caso: residente correcto; fuente realmente referenciada; contexto del mismo residente claramente separado de la causa; autor y fecha cuando constan; estado guardado sin reclasificar estados históricos; campos de la especialidad; acciones ajustadas a permisos. Sin registros o sin vínculo autorizado, se debe explicar la ausencia, no rellenarla con datos ficticios.

La revisión adicional del código confirma el cableado comparación → previsualización → selección de caso → representación → inspector/formulario. Se releen los resultados previos de 8 pruebas PHP/421 aserciones y 13 de interacción; no se presenta esa lectura como una ejecución nueva. Las pruebas no sustituyen la observación de los datos de la sesión abierta.

Nuevo intento de observar la pestaña: CUA rechazó adquirir acceso por la política de URL del navegador («The browser URL policy blocks this action»). Este bloqueo es distinto del aviso de configuración anterior. No se intentó usar otro navegador, HTTP directo, capturas mediante terminal ni un canal alternativo para eludirlo. La revisión visual actual y la comprobación de la sesión continúan pendientes.


## Consolidación solicitada posteriormente

El propietario solicitó reunir estos cambios en `feature/apoyo-decision-informe-neurocognitivo`
y publicarlos para el equipo. El registro conjunto y las instrucciones de actualización
están en `docs/sistema/PUBLICACION_CONJUNTA_2026_10_22.md`. Esta solicitud posterior
no modifica los límites de QA visual ni supone validación clínica.
