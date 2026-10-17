# Administración de medicación — entrega del 14/10/2026

Actualización solicitada después de esta entrega: [imagen del medicamento, distribución y acciones](MEDICACION_IMAGEN_Y_ACCIONES.md). Incluye ampliación de foto, revisión de botones, contexto móvil y verificación posterior con Livewire HTTP real. Los resultados siguientes corresponden a la primera iteración.

## Alcance y fuentes

Implementación del formulario existente de Enfermería, desde **Mis residentes → Registrar → Administración de medicación**. El texto adjunto del propietario define esta entrega. El adjunto disponible no contiene la imagen mencionada: se aplica la distribución descrita y el contrato visual institucional; la fidelidad a esa imagen queda pendiente de recibirla.

Fuentes consultadas: AGENTS y sus instrucciones de app/resources/tests/database, baseline BDD congelado y diccionario, contrato del módulo de Medicación, auditoría del formulario programado, arquitectura híbrida, contrato visual y stacks de sistema/UX. Las reglas de prescripción y las competencias profesionales conservan su fuente institucional.

## Archivos creados

- `app/Modulos/Medicacion/Aplicacion/Consultas/ConsultarAdministracionMedicacion.php`: lectura autorizada de orden, historial, detalle y agregados.
- `app/Modulos/Medicacion/Presentacion/Vistas/livewire/medicacion/partials/administracion-consultas.blade.php`: ventanas independientes de historial y evolución y prescripción de solo lectura.
- `resources/frontend/scripts/modules/medicacion-registro.js`: envío, conservación de captura ante fallo de transporte, protección de salida y geometría de ventanas.
- `resources/frontend/styles/design-system/patterns/medication-administration.css`: composición 60/40, variantes responsive y superficies mediante tokens existentes.
- `tests/Feature/AdministracionMedicacionFormularioTest.php`: interacción Livewire, persistencia, contexto, negativos y exportación de estados sintéticos.
- `tests/Feature/AdministracionMedicacionConcurrenciaPostgresTest.php` y `tests/Support/medicacion_concurrencia_worker.php`: dos procesos reales contra PostgreSQL desechable.
- `tests/Frontend/medicacion-registro.test.js`: transporte, bloqueo de doble envío y contrato de series.
- `tests/Support/medicacion-visual-qa.mjs`: navegador con vistas renderizadas por Livewire y datos sintéticos.

## Archivos modificados

- Medicación: `AgendaMedicacionService`, `RegistrarAdministracionMedicacionService`, `RegistrarAdministracionFichaHttp`, `StoreAdministracionMedicacionRequest`, `AdministracionMedicacionModal`, `SaludAdministracionMedicacionPanel` y la vista del modal.
- Cuidados: `MisPacientes`, directorio de residentes y selector de registro.
- Registro de vistas modulares, entradas de JavaScript/CSS.
- Pruebas `MisPacientesRedisenadaTest`, `TabMedicacionEnfermeriaTest` y `FrontendArchitectureTest`.
- Contrato documental del módulo y este informe.

## Componentes y servicios reutilizados

`ui.modal-livewire`, `ui.form-section`, `ui.action-button`, `ui.clinical-trend-window`, controles y tokens del Design System, geometría de Signos vitales y `RMCharts`/Chart.js. No se incorpora una biblioteca nueva ni una segunda implementación de movimiento/redimensionamiento.

Backend: `TurnoEnfermeriaService`, `MiTurnoService`, `AgendaMedicacionService`, escritor central `RegistrarAdministracionMedicacionService`, asignaciones/jornadas existentes, códigos correlativos centrales y Activitylog.

## Navegación e integración

El selector abre el componente canónico en la misma página. El acceso sigue disponible sin dosis pendientes, para consultar resultados. Volver regresa al selector del mismo residente; cerrar conserva el contexto del directorio. La agenda se actualiza mediante los eventos existentes después del guardado confirmado. La entrada HTTP también utiliza el escritor central y ya no duplica su auditoría.

## Secciones del formulario

1. **Contexto:** residente, edad, cama/habitación si están documentadas, profesional autenticado, jornada y momento del servidor. No permite atribuir el registro a otro profesional.
2. **Dosis programada:** identidad exacta de prescripción + horario + fecha + hora; filtros y conteos de pendientes/administradas/omitidas/todas. No selecciona automáticamente la primera dosis. Las fechas anteriores consultan resultados guardados, sin generar programación histórica a partir de una orden actual.
3. **Orden médica:** medicamento, presentación, dosis, vía, frecuencia, indicación y prescriptor; prescripción y horarios de solo lectura.
4. **Registro:** ninguna opción de resultado al abrir. Administración exige hora y dosis reales; efecto, reacción y observaciones se registran como texto factual. Omisión exige motivo y elimina los campos incompatibles.
5. **Seguridad:** alergias documentadas con reacción/gravedad/estado, únicamente con permiso; ausencia de documentación y ausencia de permiso se distinguen. No se simula un motor de interacciones ni se declara una dosis “segura”. La diferencia horaria se describe sin asignar severidad clínica.
6. **Resumen y acciones:** vista previa claramente sin guardar, confirmación de descarte para limpiar/cambiar dosis/salir y confirmación proveniente del servidor. Un fallo de transporte conserva la captura y recomienda consultar el historial antes de reintentar.

## Consultas independientes

**Historial:** 7/30/90 días o intervalo personalizado, resultado, resumen/detalle, agrupación visual por fecha y páginas de 15 registros. El detalle conserva autor, estado y valores persistidos, incluidos eventos anulados. La vía/unidad/nombre proceden de la orden relacionada y se identifican como tales; no se presentan como una versión histórica inexistente.

**Evolución:** barras de administradas/omitidas por día/semana/mes, tabla accesible y totales reales. Solo cuenta combinaciones de estado/resultado vigentes según la lectura de continuidad existente. Seleccionar un periodo permite consultar sus registros, respetando el intervalo y agrupación de la gráfica cargada. No calcula porcentajes de cumplimiento, pendientes históricos ni denominadores que la BDD no conserva.

Ambas ventanas pueden permanecer abiertas en escritorio; se mueven y redimensionan con ratón o teclado. Cerrar una conserva la captura y la otra ventana. En móvil, el foco permanece en la ventana activa, incluidos sus botones de cierre. Al seleccionar otra prescripción, las consultas abiertas se actualizan para evitar mezclar un título nuevo con datos anteriores.

## Validación, autorización y persistencia

- Cuenta ACTIVO, permiso de creación, competencia de Enfermería, residente asignado, personal propio y una jornada vigente inequívoca. La ventana nocturna puede comenzar el día anterior.
- Permisos de lectura separados para prescripción, horarios, administraciones y alergias; comprobados en servidor, también al volver a renderizar contexto sensible.
- Fecha de captura hoy; hora real de hoy no futura; dosis numérica positiva con hasta tres decimales dentro del tipo existente. El formulario exige captura explícita; el escritor conserva la compatibilidad de las entradas existentes que utilizan la dosis programada.
- Motivo de omisión con 5–500 caracteres después de recortar espacios; textos complementarios hasta 2.000 caracteres. Omisión guarda dosis/hora real/efecto/reacción como NULL.
- Relación residente–prescripción–horario comprobada de nuevo bajo bloqueo, con pauta semanal y hora actuales. Horarios distintos a la misma hora mantienen identidad propia.
- Transacción con bloqueos de cuenta/personal/asignación/jornada/orden/horario y protección de duplicado por ocurrencia. Una auditoría dentro de la transacción; su fallo revierte registro y reserva correlativa.
- No se modifica la orden médica, no se borra historia y no se cambia el esquema congelado. Las pruebas de persistencia utilizan datos sintéticos y la prueba PostgreSQL su propio cluster desechable.

## Verificación y evidencia

Los logs y capturas se conservan fuera de Git en `C:/Users/CARLAENCINAS/.codex/medicacion-20261014/`.

| Verificación | Evidencia |
|---|---|
| Captura, agenda, modales seguros y arquitectura | `php-final.log`: 52 pruebas, 657 aserciones, PASS |
| Rama de entrega: regresión e integración del selector | `worktree-final.log`: 220 pruebas, 5.599 aserciones, PASS |
| Últimos ajustes de contexto y enlace gráfica–historial | `form-scope-final.log` y `worktree-form-final.log`: 17 pruebas, 117 aserciones en cada ejecución, PASS |
| PostgreSQL programada, negativos y concurrencia real | `postgres-final.log`: 39 pruebas, 254 aserciones, PASS |
| Últimos ajustes de consulta en PostgreSQL | `postgres-consultas.log`: 17 pruebas, 117 aserciones, PASS |
| Regresión ampliada de Enfermería | `regression-final.log`: 261/262 inicialmente; el único fallo era un contrato estático de componentes, corregido y comprobado después en `php-final.log` |
| JavaScript de medicación | 3 pruebas: contrato de series, fallo de red y doble envío, PASS |
| Build | `build-final.log` y `worktree-build.log`: PASS; en el directorio principal permanece el aviso general de bundle mayor de 500 kB |
| Suite general de frontend | `frontend-final.log`: 186/187 PASS; falla la consistencia previa de superficies del dashboard de Enfermería en `shell-visual-consistency.test.js:131`, reproducida también en `frontend-tests.log` anterior |

QA de navegador en **1440, 1280, 1024, 768 y 390 px**, con movimiento reducido: estados inicial, dosis seleccionada, prescripción, administración, omisión, validación incorrecta, descarte, guardado y consultas. Se comprueban viewport, ausencia de scroll horizontal, espacio utilizable del cuerpo, dos ventanas, cierre independiente, movimiento/tamaño con teclado, tabla/gráfica real y foco móvil. `qa/evidence/resultados.json` registra los resultados; las capturas adicionales muestran historial, evolución y ambas ventanas.

Resultado final: 45 combinaciones de estado/viewport y 15 capturas adicionales de consultas, sin errores JavaScript. Se inspeccionaron visualmente escritorio, móvil y ambas ventanas.

El navegador consume HTML/estado exportado de pruebas Livewire con una conexión de UI sintética; **no acredita persistencia desde una sesión real de navegador**. La persistencia y la autorización sí se ejecutan en PHPUnit contra SQLite y PostgreSQL. No se han utilizado residentes reales para las capturas.

## Límites, deuda y decisiones

- Pendiente recibir la imagen de referencia para acreditar fidelidad exacta.
- El esquema no versiona todas las ocurrencias antiguas ni los atributos de la orden; se muestran eventos persistidos y se explica la procedencia de los datos relacionados.
- PRN conserva su flujo existente y la deuda institucional de idempotencia; esta entrega cubre administración programada.
- La variante anterior del panel general mantiene su propia validación de diferencias de dosis. No se convierte esa variante en un protocolo transversal sin decisión institucional.
- No se incorporan umbrales clínicos, nuevos permisos, alertas automáticas ni nuevas reglas de prescripción. No se requiere aprobación de una decisión clínica nueva para los cambios técnicos descritos.
- La discrepancia visual previa del dashboard general queda fuera del alcance de este formulario.
- Commit local en rama de entrega; sin push ni despliegue. Los cambios previos del propietario en el directorio principal se conservan.

## Ajuste de composición — 14/10/2026

Al abrir Evolución en escritorio (desde 1280 px), la captura reserva espacio a la derecha y se alinea a la izquierda. La evolución inicia en 440 px; al ampliarla, el formulario conserva al menos 720 px. Las secciones de captura se reorganizan en una columna. Cerrar la evolución restaura el ancho y el centrado originales, conservando la dosis seleccionada. Se reutilizan la geometría de Signos vitales y `clinical-trend-window`; pantallas menores conservan la ventana superpuesta. No se modifica backend, permisos ni BDD.

El contenido de la consulta adapta filtros, canvas y tabla al ancho disponible. Se comprueban separación entre ventanas, ampliación/restablecimiento, cierre, ausencia de overflow interno y conservación del contexto. La posición inicial se acomoda automáticamente; el arrastre manual existente sigue disponible.

Verificación del ajuste: cinco pruebas JS de medicación PASS; build PASS (aviso general de bundle >500 kB); harness sintético Blade/Alpine de 50 estados/ancho en claro (1440/1280/1024/768/390) y 10 en oscuro (1440/390), cero errores. Capturas de 1440 y 1280 revisadas. Evidencia local: `%TEMP%/remembermind-medication-side-by-side/`. Estas comprobaciones de UI no acreditan persistencia en una sesión operativa. Sin commit adicional por cambios previos mezclados en el checkout.
