# Alertas: corrección y experiencia por responsabilidades

## Alcance y fuentes

Solicitud del propietario del 20/10/2026: corregir e implementar el núcleo y las experiencias de los diez roles, sin modificar estructura, matriz de permisos ni reglas clínicas. La instrucción más reciente autoriza commit y push en una rama del módulo Alertas y sustituye la restricción anterior de no publicar.

HEAD inicial: `2374b5571391cfa18468e47d9503ef4a4bdf0a09`, posterior al auditado `0f0675493cba0a86de8090526f1bec021ab0fc66`. Rama `feature/alertas-operacion-roles`, creada desde `origin/develop` y adelantada sin conflictos al HEAD funcional existente. No se incorpora la rama UI ajena ni se aplica la consolidación experta pendiente en la BD local.

SOURCE OF TRUTH: AGENTS aplicables; baseline y diccionario físico actuales con extensiones aprobadas; matriz de roles congelada; contrato del módulo; contrato visual y tokens existentes. Las descripciones históricas de Backend/Frontend quedan subordinadas a la arquitectura modular aprobada. La imagen aportada es un diagnóstico y propuestas, no autorización de nuevas competencias.

## Auditoría inicial

| Defecto confirmado por lectura | Evidencia | Causa e impacto | Prioridad |
|---|---|---|---|
| Fecha de cuidado inexistente | DeteccionAlertasService | Lee fecha_programada/hora_programada en vez de fecha_hora_programada; interrumpe detección o compara mal el vencimiento | Alta |
| Scheduler exige sesión web | routes/console.php y detector | No existe actor en CLI; falla antes de detectar. La identidad de autoría automática requiere decisión explícita | Alta |
| Estados distintos entre superficies | Alerta, panel, campana, dashboards, ficha | Reconocidas/asignadas desaparecen o se duplican; cierre rechazado aunque HTTP lo permite | Alta |
| Coordinación etiquetada como intervención clínica | AlertasService y modales | Un permiso operativo puede producir un evento clínico sin competencia | Alta |
| Consulta de métricas sin permiso | RoleDashboardDataService | Nutrición/Fisioterapia ven contador de alertas sin alertas.ver | Alta |
| Tipo supera el límite físico | AlertasPanel y AlertasService | Validador admite 80 caracteres para columna de 60 | Media |
| Origen y evento perdidos | GestionarIncidentesEnfermeria, RegistrarCuidadosDeTurno | Envía origen inexistente, sin referencia canónica y/o evento inicial | Alta |
| Detalle usa relaciones inexistentes | Modal de detalle y cierre | turno no pertenece a alerta, apellido heredado deja nombre incompleto | Media |
| Presentación inventa urgencia o estabilidad | DashboardService y ficha | Fallback a URGENTE y afirmación clínica por ausencia de alertas | Alta |
| Parámetro rápido sin lista permitida | AlertasPanel::setFiltroRapido | Permite cambiar cualquier propiedad pública en vez de filtros autorizados | Media |

Las hipótesis de concurrencia y comportamiento visual se comprobaron mediante las ejecuciones separadas descritas al final; la lectura estática no se usa como sustituto de esa evidencia.

## DOMAIN DESIGN

Núcleo compartido en Alertas: consulta autorizada y definición común de estados en el modelo; AlertasService conserva operaciones/transacciones/eventos; Livewire conserva filtros, detalle y captura. Las mutaciones revalidan cuenta, permiso, contexto y estado dentro del backend. El actor de evento siempre es la cuenta real; no se crea una cuenta de sistema ni se atribuye la ejecución a un profesional arbitrario.

Se conservan los estados ya existentes: ABIERTA, RECONOCIDA, ASIGNADA y EN_ATENCION son activos; ATENDIDA se consulta separadamente y admite cierre según la transición HTTP existente; CERRADA/ANULADA son terminales. Los estados heredados no se reescriben ni se generan nuevos catálogos. Apertura de campana no cambia estado.

## UX FLOW

1. Enfermería: pendientes autorizados del turno → prioridad/ubicación/origen → detalle con trayectoria → intervención o seguimiento real → cierre justificado → continuidad en pase de turno. Recordatorios de medicación separados; no administran dosis.
2. Médico: consulta clínica → prioridad/origen → expediente y procesos propios. No obtiene gestión/cierre de alertas por lectura.
3. Psicología: seguimiento documentado → residente/origen → instrumentos y valoraciones propios. No se convierte una variación cognitiva en alerta automática.
4. Gerencia: agregados reales de pendientes/responsabilidad/origen → consulta; sin actuaciones clínicas.
5. Administración: reconocimiento/asignación/seguimiento de coordinación; sin registrar intervención clínica. El cierre con permiso conserva su significado operativo, se identifica como coordinación y no acredita resolución clínica.
6. Superadministración: integridad, eventos y supervisión; ninguna escritura clínica por su rol técnico.
7. Nutrición: continuidad en valoraciones, ingesta y registros propios; sin habilitar centro clínico ausente en permisos.
8. Fisioterapia: registros funcionales, dolor y seguimiento propios; sin nueva facultad de alertas.
9. Pedagogía: actividades/seguimientos y próximos pendientes de su responsabilidad.
10. Familiar: visitas/actividades/documentación expresamente compartida del residente vinculado; sin campana ni centro clínico.

Una sola biblioteca de componentes y tokens. Filtros con labels, errores conservados, indicadores basados en el mismo ámbito, listas móviles y detalles accesibles. Las propuestas de reglas/competencias de la imagen permanecen fuera de la implementación.

## Decisiones pendientes

- Autoría institucional del scheduler: eventos_alerta exige cod_usuario. La persistencia automática sin sesión necesita una identidad de ejecución expresamente autorizada; no se elige el primer usuario ni se desactiva autorización. Continuar con detección autorizada y diagnóstico explícito del scheduler mientras esa decisión está pendiente.
- Nuevos permisos de gestión/cierre para Medicina, Psicología u otras disciplinas; nuevas reglas cognitivas/nutricionales/funcionales; indicadores de cobertura sin criterio aprobado.
- Clasificación aprobada que distinga cierre clínico de cierre operativo: modulo identifica origen, no competencia. No se asume que MANUAL equivale a administrativo ni se retiran permisos congelados basándose en esa inferencia.
- Catálogo cerrado de estados y cualquier modificación física: fuera de alcance.

## Verificación

Evidencia de esta tarea, independiente del informe previo de instalación. Directorio privado fuera del repositorio:
`C:/Users/LOQ/RememberMind-respaldos/20261020-075544-actualizada/`.

### Resultado por rol

| Rol | Resultado implementado y comprobado |
|---|---|
| Enfermería | Bandeja del ámbito del turno; reconocidas/asignadas permanecen pendientes; intervención real y seguimiento; cierre justificado; trayectoria, origen y nueva medición; campana distingue recordatorios; agenda/ficha/pase/PDF usan estados comunes. |
| Medicina/Geriatría | Consulta priorizada y fuente clínica solo con permiso específico; evolución con fecha/unidad y continuidad al expediente; sin habilitar gestión clínica de alertas ausente en su contrato. |
| Psicología | Consulta de alertas registradas, historial y continuación en procesos/instrumentos autorizados; no se afirma generación de reglas cognitivas nuevas. |
| Gerencia | Distribución real por origen y responsabilidad, consulta y estados; sin acceso indirecto a signos, diagnósticos o alergias por tener permiso de alertas. |
| Administración | Reconocimiento, asignación y seguimiento operativo con eventos; cierre explícito de coordinación; no produce INTERVENCION ni estado de atención clínica por su rol. |
| Superadministración | Supervisión y trazabilidad; advertencia verificable sobre vigilancia desatendida pendiente; sin escritura clínica automática por excepción técnica. |
| Nutrición | Panel y procesos propios; eliminado contador de alertas no autorizado; centro clínico rechaza acceso directo. |
| Fisioterapia | Panel y seguimiento funcional propios; eliminado contador de alertas no autorizado; centro clínico rechaza acceso directo. |
| Pedagogía | Panel de actividades y procesos autorizados; sin nueva bandeja clínica ni permisos inventados. |
| Familiar | Panel vinculado/autorizado; sin centro, métricas ni contenido clínico por inferencia del vínculo. |

### Diseño y recuperación

- Sistema visual compartido existente: jerarquía, indicadores consultables, prioridad registrada, tarjetas con ubicación/responsable/origen/fecha y acción principal al detalle.
- Accesos rápidos Pendientes/A mi cargo/Sin responsable; búsqueda/estado/prioridad visibles; contexto y fechas en Más filtros, con apertura accesible por teclado.
- Formularios con labels, errores junto al campo y feedback; resumen de errores dentro del formulario activo. Confirmación de descarte conserva la captura cuando el usuario decide continuar.
- Detalle con foco contenido, Escape y retorno al botón de origen; fuente y evolución con mmHg/lpm/rpm/°C/%/mg/dL según el dato.
- Ubicación limitada a habitación, cama, piso y tipo realmente registrados; eliminados riesgos, movilidad y niveles ficticios.
- Históricos ACTIVO de la BD local se conservan y se pueden consultar expresamente. No se reclasifican ni se presentan como pendientes canónicos por suposición.

### Corrección de campos y controles por rol

Trabajo realizado directamente en el checkout y los módulos existentes; sin copias del producto, ramas de implementación paralelas ni carpetas pendientes de integrar.

| Actor/contexto | Campos y controles ajustados |
|---|---|
| Enfermería de turno, residente asignado | Formulario de intervención exclusivo del rol real; autorización también al abrir. Nota de 5 a 10.000 caracteres, origen/evolución con unidades y prioridad histórica representada correctamente. |
| Enfermería fuera de turno | Conserva lectura de las asignaciones permitidas del día. El detalle explica el requisito de turno/asignación y no ofrece reconocer, asignar ni intervenir cuando el escritor lo rechazaría. |
| Administración/Superadministración | Seguimiento y cierre de coordinación; errores y confirmación hablan del historial de la alerta. El resultado aclara que el cierre no acredita resolución clínica. La apertura directa de intervención se rechaza sin eventos. |
| Roles con permiso vigente de creación | Residente disponible en su ámbito, tipo de 3 a 60 caracteres y descripción de 10 a 10.000. Ejemplo neutral y orígenes sin afirmar un diagnóstico o una administración de fármaco. Grupo de prioridad con fieldset/legend. Se aclara que un aviso manual no acredita un registro clínico vinculado. |
| Medicina/Psicología/Gerencia | Consulta y continuidad según permisos actuales. No se añaden formularios clínicos de Alertas para aparentar facultades no aprobadas. |
| Nutrición/Fisioterapia/Pedagogía/Familiar | Entrada clínica denegada según matriz ordinaria; procesos propios conservados. No se amplían campos, métricas ni acceso clínico por el rediseño. |

Controles compartidos: el responsable debe ser personal activo, profesional, con permiso de Alertas y acceso al residente; las opciones se acotan en bloque usando la misma ventana de lectura del servicio de turnos, sin consulta individual por cada candidato. Una selección cambiada pendiente pide confirmación de descarte. Los orígenes del filtro incluyen los de alertas cerradas/anuladas, independientemente del gráfico de pendientes. «A mi cargo» solo aparece con personal vinculado. Las fechas invertidas o inválidas muestran error y las inválidas no se pasan a SQL.

### Controles institucionales y uniformidad

En la bandeja de Alertas, creación, responsable del detalle y avisos de seguimiento se reutiliza `x-ui.selector`; las fechas de filtros usan `x-ui.calendario`. No se implementa otro menú ni otro calendario. Los elementos nativos ocultos solo sincronizan Livewire; la interacción visible utiliza los componentes propios. Los desplegables de modal/drawer permanecen dentro de su contexto de foco y Escape cierra primero el desplegable.

El selector compartido admite `:search-threshold="20"` para ajustar el umbral. El valor predeterminado es **15**: con 14 o menos opciones no aparece búsqueda; con 15 o más sí. El umbral cuenta opciones habilitadas con valor, excluyendo el placeholder, no el total de filas de la base. Las pruebas de frontend cubren 2/10/14/15/16 opciones, el placeholder y una parametrización a 20. Se conservan selección, teclado, scroll y estados de error del Design System.

### Datos existentes y resumen de consulta

Consulta PostgreSQL institucional en transacción **READ ONLY**: 25 alertas, todas con estado `ACTIVO`; 25 sin `modulo` y 25 sin responsable. Tipo, título, descripción, prioridad, generación, fecha y residente tienen valor. No se imprime ni exporta información personal en esta comprobación.

El resumen anterior agregaba solo pendientes canónicos y podía mostrar «Sin datos por mostrar» junto a una lista de registros anteriores. Ahora origen/estado se agregan desde la misma consulta filtrada antes de paginar; con `ACTIVO`, el gráfico representa sus registros y muestra «Sin origen registrado» cuando corresponde. El total registrado permanece visible, separado de pendientes actuales. Si no hay pendientes actuales pero sí registros anteriores, la entrada sin filtro explícito muestra todos. Si una consulta no coincide, informa el total existente y ofrece «Ver todas las alertas»; no afirma ausencia de datos global.

Se conservan los valores originales de las 25 alertas. No se inventa origen/responsable ni se convierte `ACTIVO` en `ABIERTA`. Se corrigió el span de cuatro columnas heredado por los paneles dentro de la cuadrícula de dos columnas: comparten fila en escritorio y se apilan al reducir el ancho. Las tarjetas usan una acción principal «Ver alerta»; los controles de consulta, la información de estado anterior y el estado técnico de vigilancia tienen jerarquías separadas. El detalle explica por qué un estado anterior no habilita actuaciones del ciclo vigente. No hay simulaciones ni registros de demostración en la instalación institucional; las fixtures se limitan a las pruebas aisladas.

### Ejecuciones y límites

| Evidencia | Resultado |
|---|---|
| `alertas-postgres-validacion.xml` | 53 pruebas / 275 aserciones: 52 aprobadas y un positivo de gráfico obsoleto con Administración. Se corrigió el actor del fixture; no se debilitó la frontera. |
| `alertas-gate.xml` | 20 pruebas / 142 aserciones PASS en cluster PostgreSQL nuevo y desechable. Incluye positivo corregido, gráficos denegados, ubicación sin SQL clínico, cuenta revocada, actor distinto, estados y coordinación. |
| `alertas-carrera.xml` | 1 prueba / 7 aserciones PASS: dos procesos independientes intentan cerrar simultáneamente; un cierre y un conflicto, sin segundo evento. Cluster separado. |
| `alertas-integraciones.xml` | 308 pruebas / 2595 aserciones; 303 aprobadas, 3 fallos y 2 errores identificados. Se corrigieron las expectativas obsoletas de polling/métricas y se restauró evolución de signos; los dos escenarios de cuidado ahora documentan la intervención de la alerta crítica antes de continuar. Repeticiones específicas abajo. |
| `alertas-correcciones-final.xml` | 42 pruebas / 248 aserciones; 41 aprobadas y un fallo de registro del nuevo parcial de descarte. Se registró en `config/vistas_modulares.php`. |
| `alertas-evolucion.xml` | Repetición del único caso fallido anterior: PASS, 1 prueba / 39 aserciones. |
| `alertas-vistas.xml` | PASS, 1 prueba / 34 aserciones: entradas HTTP y render de los diez roles con fixtures sintéticos. |
| `qa-alertas/resultado-responsive.json` | 80 capturas, cinco anchos 1440/1280/1024/768/390: sin overflow horizontal del documento, literales inválidos ni inputs visibles sin etiqueta en el ámbito medido. Complemento: móviles desplazadas y diez capturas oscuras. |
| `qa-alertas/resultado-flujo.json` | Login real de diez cuentas sintéticas en servidor aislado; seis centros permitidos y cuatro denegados; cero errores JavaScript observados. Reconocimiento, asignación, validación, intervención y cierre ejecutados en Enfermería. |
| `qa-alertas/persistencia-flujo.json` | Tras el recorrido: CERRADA, seis eventos conservados, un mismo autor. La prueba no opera sobre la BD institucional. |
| `qa-alertas/resultado-estados.json` | Campana móvil, filtros abiertos, desconexión visible, Tab contenido en diálogo, Escape, retorno al botón y tema oscuro comprobados contra el servidor aislado. |
| `qa-alertas/resultado-captura.json` | Escape pide confirmación, continuar conserva el texto y descarte explícito cierra. Sin escritura clínica al descartar. |
| `qa-alertas/resultado-origen.json` | Origen/evolución y unidades visibles para Enfermería/Medicina/Superadmin; ausentes para Gerencia/Administración según permiso. |
| `alertas-build-final.log` | `npm run build` PASS. Sin dependencias nuevas. |

### Consolidación de las correcciones por rol

| Ejecución | Resultado |
|---|---|
| `alertas-roles-final.xml` | PASS: 59 pruebas / 360 aserciones del núcleo de Alertas. |
| `alertas-roles-pg.xml` | PASS: 26 pruebas / 206 aserciones en PostgreSQL privado. |
| `alertas-fechas-pg.xml` | PASS: 26 pruebas / 210 aserciones; incluye fecha inválida sin error SQL. |
| `alertas-integracion-roles.xml` | PASS: 205 pruebas / 1.954 aserciones, Gerencia, métricas por rol, agenda de medicación, auditoría de turno y Mis residentes. |
| `alertas-vistas-final.xml` | PASS: 1 prueba / 34 aserciones, diez entradas por rol después de la compilación final, con gráficas y controles actuales. |
| `alertas-consulta-final-pg.xml` | PASS: 27 pruebas / 223 aserciones; resumen filtrado, entrada con estados anteriores, permisos y campos. |
| `alertas-controles-final.log` | PASS: 9 pruebas de frontend del selector, calendario y tema; incluye el umbral parametrizable de búsqueda. |
| `qa-alertas/resultado-campos.json` | 10 logins y 80 capturas de bandeja/detalle/validación/creación en cinco anchos, sin desbordamiento, campos visibles sin etiqueta ni literales inválidos. |
| `qa-alertas/resultado-campos-en-turno.json` | 25 capturas adicionales de Enfermería con jornada de la copia de QA; intervención, creación y validación accesibles. La modificación horaria solo se realizó en fixtures privadas, nunca en la jornada institucional. |
| `qa-alertas/resultado-controles-propios.json` | 16 capturas y recorrido de selectores/calendarios; opciones pequeñas sin búsqueda, límites de fechas sincronizados con backend, elección por teclado, Escape conserva diálogo y portales dentro del contexto de foco; cero errores JS. |
| `alertas-build-final-real.log` | Build PASS con el umbral, componentes institucionales y cuadrícula de resumen corregida. |
| `qa-alertas/resultado-responsive.json` (corte final) | PASS: 80 capturas de los diez roles, cinco anchos, recursos de la compilación final, selectores inicializados; sin overflow, literales inválidos, campos visibles sin etiqueta ni errores JS. |
| `qa-alertas/resultado-responsive-final.json` | PASS: diez capturas de Gerencia/Superadministración en cinco anchos; texto de selección inicializado, recursos JS/CSS disponibles, cero errores JS, paneles alineados en escritorio y sin desbordamiento. |

La tanda PostgreSQL `alertas-consulta-real-pg.xml` encontró siete errores de variable de vista porque el proceso ya había cargado la clase anterior cuando se añadió el dato del nuevo resumen. No se presenta esa tanda como aprobada: se repitió con implementación y vistas congeladas en `alertas-consulta-final-pg.xml`. La repetición final aprobó: **27 pruebas / 223 aserciones** en PostgreSQL privado; el render fresco de los diez roles aprobó además con esa variable presente. El cluster de pruebas fue detenido al finalizar.

La tanda amplia inicial se completó antes de las reparaciones. No se presenta como una nueva ejecución global totalmente verde: las correcciones se validan mediante repeticiones del caso afectado y su grupo. No se ejecutó toda la suite del repositorio ni se declara validación clínica.

### Seguridad, persistencia y legacy

Gate independiente `remembermind-security-review`: PASS acotado al módulo en el último corte, sin hallazgos bloqueantes pendientes. Revisión separada de la implementación; no confunde lectura de residente con acceso a toda la historia clínica.

Sin migraciones, seeders, cambios de permisos, estructura, cuentas reales o contraseñas. Pruebas con SQLite en memoria; integración y carrera con PostgreSQL privado desechable. El servidor de QA usa una copia SQLite exclusivamente de fixtures, otra cookie de sesión y otro puerto, sin conexión a la BD institucional.

Eliminados del modelo Alerta los aliases de escritura/relación heredados consumidos por este flujo; fixtures afectados migrados a atributos canónicos. No se realizó limpieza global ajena al módulo. La relación `Residente::alertasAbiertas` mantiene su API, usando los estados compartidos para sus consumidores PDF.

### Presentación local

- Sistema institucional: `http://127.0.0.1:8010/admin/alertas-clinicas`.
- Las capturas sintéticas son evidencia privada de pruebas. La presentación solicitada es la aplicación institucional, no la galería de QA.
- Se cerraron la galería 8012 y el servidor de pruebas 8013, preservando el servicio institucional 8010. Se solicitó la apertura de la consulta institucional con `filtroEstado=ACTIVO`. La herramienta respondió `queued`; el último intento de control del navegador informó que el uso local estaba deshabilitado. No se declara inspección visual final de la sesión institucional mientras esa herramienta no permita observarla; los controles y los cinco anchos sí se ejercitaron contra la aplicación aislada con el mismo código.

Publicación en `feature/alertas-operacion-roles`, conservando el checkout existente. La base funcional es `2374b5571391cfa18468e47d9503ef4a4bdf0a09`; incluye avances previos de ApoyoDecision ya publicados en su propia rama. El último `origin/develop` disponible es ancestro de esa base y queda 42 commits por detrás. Esa historia previa no debe atribuirse a esta tarea ni integrarse inadvertidamente en un PR exclusivo de Alertas. No se reescribe la historia ni se fusiona con develop/main durante esta publicación.

Separación de cambios para revisión y reversión:

1. `fix(alertas): unificar estados, permisos y operacion por rol`: correcciones del núcleo y consumidores, formularios y controles funcionales, protección de captura y pruebas. Incluye el marcado necesario para los campos, permisos, datos reales y feedback; no es un commit exclusivamente de backend.
2. `fix(alertas): ordenar estilos y distribucion responsive`: estilos de tarjetas, filtros, cabecera, resumen, foco y estados de carga. Se puede revertir este commit de estilos conservando lógica, autorizaciones, datos, controles y recuperación de errores. Revertir estilos no recupera el marcado anterior de la pantalla, que estaba mezclado con defectos funcionales.

Para retirar solamente los estilos, identificar el segundo commit con `git log --oneline --grep='ordenar estilos y distribucion responsive'` y ejecutar `git revert <hash-del-commit-de-estilos>` en una rama de trabajo. Después ejecutar `npm run build` y verificar la pantalla. No usar reset ni force push. La reversión completa del flujo requiere revisar conjuntamente los commits funcional y visual y sus consumidores.

El push se comprueba por coincidencia del HEAD local con la referencia remota; un intento fallido de conexión no acredita publicación. La primera conexión a GitHub durante este cierre falló por conectividad HTTPS. La verificación final de publicación se comunica con los hashes efectivos, sin incluir respaldos, credenciales ni datos institucionales en Git.

### Pendiente institucional aislado

La ejecución programada desatendida sigue bloqueada por autoría. `alertas:verificar-vigilancia` devuelve fallo explícito `BLOCKED_AUTORIA`; no se certifica monitoreo automático activo. Detección manual autorizada y generación transaccional desde registros confirmados continúan disponibles. Nuevas facultades profesionales y reglas clínicas propuestas permanecen pendientes de decisión, tal como exige la solicitud.


## Ajustes de interacción según comentarios de navegador

Tres superficies marcadas por la propietaria, implementadas sobre la misma página y componentes actuales:

- Tarjetas: procedencia con icono y texto legible a la izquierda; «Ver alerta» como acción separada a la derecha, con estado de carga de esa alerta.
- «Más filtros»: control institucional con icono de ajustes, título/ayuda, caret, borde y foco compartidos. Apertura por Enter/Espacio, sin marcador nativo visible; se conservan selector/calendario propios y umbral de búsqueda 15.
- Cabecera: acciones con iconos, jerarquía primaria/secundaria/discreta, separación y controles de al menos 44 px; adaptación a una columna en móvil.

### Mapa de carga y transición

| Interacción | Comportamiento y recuperación |
|---|---|
| Abrir/actualizar consulta | Spinner del control accionado y mensaje de consulta sin overlay que oculte los datos. La transición no es condición para completar la lectura. |
| Guardar/atender/cerrar/asignar/reconocer/detectar | Estado de procesamiento real de Livewire; prevención de nueva acción durante escritura. Botones de cerrar/cancelar de los formularios quedan deshabilitados mientras se confirma el registro. |
| Escape durante guardado | Protección en captura a nivel ventana: cubre también el caso donde deshabilitar el botón desplaza el foco al body. No se envía una solicitud de cierre. |
| Error de red/servidor/conflicto/sesión | Mensaje institucional sin insertar HTML, SQL ni traza. No se informa éxito ni se reenvía una operación automáticamente. El texto de la captura permanece. 403 conserva el tratamiento institucional de acceso existente. |
| Cancelar después de un fallo | Tras finalizar la solicitud vuelven los controles; la confirmación de descarte permite continuar editando con el texto conservado. |
| Movimiento reducido | Se conservan estado, mensaje y foco; las transiciones usan los tokens y la reducción de movimiento del Design System. |

La revisión detectó y corrigió Escape desde body durante el guardado y un spinner duplicado dentro de los botones de envío. No se crea otra ventana global de carga ni se inventan porcentajes de progreso.

### Verificación de este ajuste

- `alertas-interaccion-frontend.log`: 11 pruebas PASS de interacción, calendario, selector y tema. Incluye no interceptar otro módulo, preservar 403 y no insertar HTML de error.
- `alertas-interaccion-vistas.xml`: PASS, 1 prueba / 34 aserciones, entradas de diez roles.
- `alertas-interaccion-modales.xml`: PASS, 2 pruebas / 38 aserciones; apertura y cierre de formularios, validación y recuperación de captura.
- `qa-alertas/resultado-interaccion.json`: recorrido real de la aplicación aislada en 1440/1280/1024/768/390; filtros por teclado y controles de 44 px. Prueba de respuesta demorada y fallo controlado de transporte: un solo `guardarAlerta`, ningún cierre por Escape durante guardado, texto conservado, ningún HTML interno visible, ningún reintento, cero errores JS. La respuesta de fallo se provoca exclusivamente en QA, no en la instalación institucional.
- `qa-alertas/inspeccion-interaccion.log`: 15 capturas de Enfermería/Administración/Superadministración en cinco anchos, sin overflow ni errores de recursos/JS o literales inválidos.
- `alertas-build-interaccion.log`: build PASS.

No hay cambios de permisos, reglas clínicas, esquema ni datos institucionales en este ajuste. Las pruebas usan la copia privada sintética; no se presentan como registros reales ni sustituyen la página institucional. La inspección directa de la pestaña institucional sigue limitada por la herramienta de navegador local deshabilitada. La nueva interacción requiere cargar la compilación actual mediante recarga completa de la página.
