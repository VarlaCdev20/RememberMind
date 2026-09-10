# Inventario funcional AS-IS

Inspección estática del código en MEJORA-SYS el 10/09/2026: migraciones y restricciones físicas, modelos y relaciones, rutas/handlers, métodos de Livewire, servicios, FormRequests, enlaces y acciones Blade, seeders y tests. Una ruta o un método llamado render no acredita por sí solo funcionamiento extremo a extremo. No se ejecutaron escrituras de UI ni pruebas contra BD real en esta tarea.

Cobertura: 60 archivos de migraciones, 29 seeders, 46 modelos, 71 archivos Livewire, 28 controladores (incluye base), 17 FormRequests, 14 servicios, 312 archivos en resources/views, 19 archivos de tests (incluye TestCase). Los anexos permiten localizar operaciones y evidencias; los totales de código cuentan archivos, no capacidades independientes.

## Funciones reales y límites

| Área actual | Operaciones comprobadas en código | Estado / límite | Destino |
|---|---|---|---|
| Acceso | Login correo/password con estado ACTIVO; recuperación, contraseña, perfil, 2FA, sesiones | Registro y verificación de email deshabilitados; acceso_sistema no se comprueba en authenticateUsing | Identidad |
| Usuarios | Listar/buscar/filtrar, wizard alta/edición, activar/desactivar, bloquear acceso, reset, fichas y exportación/correo | Tres vías de escritura duplicadas; siete rutas usuarios ahora separan ver/crear/editar, grant de rol sigue sin lista delegable | Identidad |
| Roles | Selección de rol, matriz de permisos, guardar/restaurar cambios y usuarios del rol | Gate::before universal SUPERADMINISTRADOR; rol también usado como profesión | Identidad |
| Personal | Alta/edición, clasificación, área, documentos requeridos, horarios e informes | personalSalud/Admin son objetos virtuales del User; formación/cargo sin fuente normalizada | Institución |
| Áreas | Crear/editar/activar, ficha, responsable, métricas, PDF/Excel/CSV | UI mezcla catálogo y reportes; metadata de rol no debe ser autorización | Institución/Reportes |
| Horarios y planilla | Versionar horario por día, detectar solapes, día/semana/mes/año; rotaciones, descanso, apoyo, cobertura, cargas, reemplazo de plazas y exportación | Dos tablas por clase de personal; calendario generado no acredita trabajo realizado | Institución |
| Residentes | Alta wizard, edición, expediente, archivo/restauración, cambio estado con motivo, bitácora y reportes | Identidad+ingreso+cama+salud/contactos juntos; resource adultos usa adultos.ver para todas acciones | Residentes/Admisiones |
| Preadmisión | Wizard por pasos, documentación, selección enfermero disponible, PDF/correo; panel aprobación/rechazo | Aprobación crea adulto/familiar/documentos; pruebas actuales no recorren todo el flujo | Admisiones/Documentos |
| Valoración inicial | Dolor, orientación, movilidad, piel, continencia, alimentación, signos y recomendaciones; paso hacia valoración médica | Guarda JSON dentro de texto; defaults no equivalen observación clínica | VGI/Clínica |
| Decisión médica | Valoración inicial, decisión de admisión/derivación y transición del adulto | Guardar debe reautorizar y comprobar estado concurrente, no solo open | Admisiones/Clínica |
| Habitaciones/camas | Crear/editar habitación, crear cama, estado/capacidad, indicadores | No exclusión concurrente de ocupación; mostrar cama no prueba asignación segura | Admisiones |
| Pacientes de turno | Dashboard, calendario, asignados, filtros, ficha y PDF | Scope por asignación no es uniforme entre entradas al expediente | Cuidados |
| Clínica | Ficha de antecedentes/enfermedades; SOAP e interconsulta; ficha integrada de seis pestañas | Ficha actual se edita en sitio; etiquetas de tipo de nota no son módulo separado | Clínica |
| Signos | Registro, IMC, tendencias, filtros, corrección/anulación/restauración y alertas de umbral | Tres familias de panel/modal y controller; string PK tratado como int en algunas acciones/bindings | Clínica |
| Medicación | Registrar/editar, suspender/finalizar/archivar/restaurar, receta; registrar dosis u omisión | Pauta y prescriptor libres; cod_med_adulto validado integer pese PK string; sin coherencia compuesta de paciente | Medicación |
| VGI | Dominios cognitivo, afectivo, funcional, nutricional y social; instrumentos y puntajes; formularios JSON, riesgo, anulación, PDF | 26 instrumentos en seeder no significan 26 escalas validadas; score y estado duplicados | VGI |
| Funcional/Barthel | Autonomía, ayudas, visión/audición, supervisión, dependencia, Barthel y caída | Total no garantiza respuestas completas; formulaciones duplicadas | VGI |
| Planes/tareas | Crear/cerrar plan, asignar tarea, realizar/omitir/transferir, resultado | Definición y ejecución en misma fila; relaciones cod_tarea apuntan columnas ausentes | Cuidados |
| Seguimiento/pase | Alimentación, hidratación, movilidad, higiene, sueño, conducta; generar resumen, pendientes/alertas y recibir pase | Historial temporal sin corrección uniforme; JSON de pase es snapshot | Cuidados |
| Alertas | Crear, atender, acción, cierre y pendientes por residente | Heurísticas dispersas; incidente no entidad propia | Seguridad Asistencial |
| Familia/red | Vincular/existing/nuevo contacto, editar parentesco, responsable y emergencia, activar/desactivar, resumen PDF | Crea cuenta/contacto desde vías múltiples; familiar no debe ver todo residente por permiso global | Social |
| Visitas/ficha social | Navegación y vista base explícita de próxima fase; resumen con búsqueda de tablas alternativas | Pendiente real, sin tablas actuales; no registrar como CRUD completado | Social, completar |
| Actividades | Catálogo, programación, participación, realización/cancelación, resultado, reprogramación, reportes/gráficos | Cinco pantallas sobre misma fila por adulto, no evento colectivo normalizado | Actividades |
| Voluntariado administrativo | Perfil, estado/archivo, disponibilidad semanal, asignación/reprogramación/cumplimiento/cancelación, excepción por disponibilidad, asistencia/ausencia/justificación, horas y métricas | SQL directo, detección dinámica de columnas, asistencia por coincidencia sin FK de asignación | Actividades |
| Documentos | Subir/validar/observar/anular/reemplazar, vencimiento/checklist, generación institucional, paquete PDF, impresión y envío | Tres modelos/tablas, algunos archivos públicos; generado no es firmado ni recibido | Documentos |
| Reportes | Institucional, adultos, salud, familia, equipo, actividad, bitácora, áreas, ficha; PDF/Excel/CSV según endpoint, filtros/gráficos | ReportChartDataService referencia EvaluacionCognitiva inexistente y compara estado con1; Excel usa permiso PDF en rutas | Reportes |
| Sistema experto | Clasificaciones de signos y generarAlertasInteligentes del dashboard | No motor persistido/versionado ni revisión reproducible. Es desarrollo funcional pendiente, no migración de motor existente | Experto |
| Fisioterapia/nutrición | Valoración funcional/nutricional y signos existen en módulos compartidos | Rutas especializadas remiten a DashboardController; plan/evolución se completarán sobre atención y cuidado comunes | Clínica/VGI/Cuidados |
| Portal familiar/voluntario | Rutas de dashboard para resumen/actividades/visitas/docs y tareas propias | Sidebar marca placeholders y los oculta; falta experiencia contextual real | Social/Actividades |
| Estado de cuenta | Ruta admin.familiar.pagos al dashboard | No modelo, tabla ni operación financiera; no prometer facturación sin D12 | Fuera del alcance operativo actual |
| API/sistema | Sanctum/Jetstream, cola de correo, auditoría Spatie, caché; routes/api.php existe | bootstrap/app.php no registra api.php; no asumir API clínica existente | Infraestructura compartida |

## Evidencias transversales y regresiones a proteger

- resources/views/admin/familia-social/base.blade.php anuncia “Vista base preparada”. SidebarService::PLACEHOLDER_ROUTES enumera portales y rutas profesionales incompletas. Algunos alias médico sí comparten tabs funcionales (interconsultas/historial): no clasificarlos automáticamente como vacíos.
- resources/views/livewire/admin/actividades y enfermeria incluyen bindings con códigos string sin comillas; personal-institucional-documentos usa id_doc_usu aunque PK es cod_doc_usu. Son riesgos estáticos concretos que requieren pruebas de interacción; no se afirma que todos los caminos fallen.
- resources/views contiene show.blade.php.bak y nombre literal enfermeria\${view}.blade.php; revisar referencias antes de retirar. Archivos con prefijo especial de Livewire no son basura por su nombre.
- DocumentacionUsuarioService::subirDocumento usa disco public; un middleware de descarga no protege una URL pública directa. Mover a privado requiere migración de binarios y cierre de acceso público tras conciliación.
- Las pruebas nuevas UsuariosRoutesPermissionsTest protegen seis casos de rutas: invitados, lector/listado, lector sin create/edit y create/edit autorizados. Mantener este trabajo previo; no anunciarlo como cambio de esta tarea.
- CasosPreadmisionTest comprueba acceso y creación por modelo, no aprobación concurrente, documentos, transición clínica o rollback. PersonalInstitucionalHorariosTest sí comprueba dos casos de versión de horario. El resto es principalmente Jetstream y ejemplos.

## Seeders: contenido y uso

DatabaseSeeder ordena infraestructura/estados/permisos → turnos/tipos/VGI → cuentas/personal → habitaciones/residentes → preadmisiones → clínica/enfermería → actividades/voluntariado/demo. Hay seeders con personas nominales y escenarios demostrativos: no ejecutarlos en producción ni usar sus datos para completar acreditaciones reales. Durante refactor separar catálogos mínimos idempotentes de fixtures de prueba/demo. Los seeders RolesAndPermissions, SaludSeguimientoPermissions y FlujoClinicoPermissions requieren un catálogo canónico único. Las áreas se insertan también en una migración, aspecto a retirar de la futura evolución estructural.

GeriatricSuiteSeeder contiene FVS, Mini-Cog, MMSE, MoCA, GDS-15, CESD-7, Katz, Lawton, SPPB, FRAIL, TUG, Susurro, revisión medicación, velocidad marcha, Peek, Snellen, cartilla bolsillo, Braden, Norton, MNA-SF, MUST, SARC-F, barreras, maltrato, OARS y Díaz-Veiga. Cada versión prospectiva exige formulario, reglas, prueba de puntuación y aprobación clínica; no inventar licencias ni equivalencia clínica.

## Índice de métodos inspeccionados

Este anexo cubre Controllers, Requests, Livewire, Services y tests. Métodos de render/filtros se incluyen para trazabilidad técnica, sin contarlos como CRUD independientes. SQL directo de Voluntariado no aparece como import de modelo, pero sí en las operaciones funcionales anteriores.

| Archivo | Métodos |
|---|---|
| app/Livewire/Admin/Actividades/ActividadesPanel.php | rules, messages, updatingSearch, updatingFiltroTipo, updatingFiltroEstado, updatingFiltroFechaDesde, updatingFiltroFechaHasta, abrirRegistrar, abrirEditar, abrirDetalle, cerrarModales, guardarActividad, actualizarActividad, cancelarActividad, limpiarFiltros, resetForm, tablaExiste, getStats, getActividades, getTipos, getAdultos, getDetalle, render |
| app/Livewire/Admin/Actividades/AsistenciaPanel.php | rules, messages, updatingSearch, updatingFiltroTipo, updatingFiltroEstado, updatingFiltroFechaDesde, updatingFiltroFechaHasta, abrirDetalle, abrirResultado, abrirReprogramar, cerrarModales, marcarRealizada, marcarCancelada, guardarResultado, limpiarFiltros, resetForm, tablaExiste, getStats, getRegistros, getTipos, getDetalle, render |
| app/Livewire/Admin/Actividades/ParticipacionPanel.php | rules, messages, updatingSearch, updatingFiltroTipo, updatingFiltroEstado, updatingFiltroFechaDesde, updatingFiltroFechaHasta, abrirRegistrar, abrirEditar, abrirDetalle, cerrarModales, horaFormateada, existeDuplicado, guardarParticipacion, actualizarParticipacion, cancelarParticipacion, limpiarFiltros, resetForm, tablaExiste, getStats, getParticipaciones, getTipos, getAdultos, getDetalle, getParticipacionPorTipo, render |
| app/Livewire/Admin/Actividades/ReportesActividadesPanel.php | limpiarFiltros, buildFiltros, queryBase, queryFiltrada, getStats, getPreview, getChartEstado, getChartMes, getChartTipo, describePeriodo, getTipos, render |
| app/Livewire/Admin/Actividades/TiposActividadPanel.php | rules, messages, updatingSearch, updatingFiltroUso, limpiarFiltros, abrirRegistrar, abrirEditar, abrirDetalle, cerrarModales, guardarTipo, actualizarTipo, eliminarTipo, resetForm, tablaExiste, getStats, getTiposFiltrados, getDetalle, render |
| app/Livewire/Admin/Admisiones/PreadmisionesPanel.php | mount, updatingSearch, updatingEstado, updatingPrioridad, limpiarFiltros, verDocumentos, cerrarModalDocumentos, abrirModalRechazo, cerrarModalRechazo, aprobar, rechazar, exportarReportePdf, limpiarTexto, limitarTexto, soloNumeros, textoContiene, clasificarMotivoIngreso, clasificarProcedenciaIngreso, render |
| app/Livewire/Admin/Admisiones/PreadmisionWizard.php | configuracionDocumentos, siguiente, anterior, updatedEnfermeroId, validarPaso5, confirmarPreadmision, nuevaPreadmision, render, obtenerEnfermerosDisponiblesParaValoracion, obtenerTurnosVigentesDesdePlanilla, obtenerAsignacionPlazaVigente, enfermeroTieneDescansoEnFecha, horaEstaDentroDelTurno, validarEnfermeroSeleccionadoParaPreadmision, primerErrorEnfermero, validarPasoActual, rules, getEnfermeroRules, mensajesValidacion, normalizar |
| app/Livewire/Admin/AdultosMayores/AdultoMayorFormModal.php | mount, abrir, updated, cerrar, resetForm, cargarAdulto, toUpperText, onlyDigits, normalizarDatosFormulario, getEdadProperty, siguiente, anterior, validarPaso, guardar, render |
| app/Livewire/Admin/AdultosMayores/AdultosMayoresPanel.php | updated, crearAdultoMayor, editarAdultoMayor, render |
| app/Livewire/Admin/AdultosMayores/AlertasPendientesPanel.php | render |
| app/Livewire/Admin/AdultosMayores/Evaluaciones/EvaluacionGeriatricaModal.php | mount, abrir, cerrar, updatedCodArea, updatedCodInstrumento, rules, guardar, resetForm, render |
| app/Livewire/Admin/AdultosMayores/Reportes/ReportesAdultoPanel.php | mount, render |
| app/Livewire/Admin/AdultosMayores/ReportesInstitucionalesPanel.php | mount, render |
| app/Livewire/Admin/AdultosMayores/Salud/AdministracionMedicacionModal.php | rules, messages, abrirModalAdministracion, cerrarModal, updatedAdministrado, guardar, render |
| app/Livewire/Admin/AdultosMayores/Salud/FichaMedicaAdultoModal.php | rules, messages, abrirModalFichaMedica, cerrarModal, cargarDatos, resetCampos, guardar, render |
| app/Livewire/Admin/AdultosMayores/Salud/HistorialEstadoAdultoPanel.php | abrirModalHistorialEstado, cerrarModal, render |
| app/Livewire/Admin/AdultosMayores/Salud/MedicacionAdultoModal.php | rules, messages, abrirModalMedicacion, cerrarModal, cargarDatos, resetCampos, guardar, cambiarEstado, render |
| app/Livewire/Admin/AdultosMayores/Salud/SignosVitalesAdultoModal.php | rules, messages, abrirModalSignos, cerrarModal, cargarDatos, resetCampos, updated, calcularIMC, guardar, render |
| app/Livewire/Admin/AdultosMayores/Salud/ValoracionFuncionalAdultoModal.php | rules, messages, abrirModalValoracion, cerrarModal, cargarDatos, resetCampos, updated, calcularDependencia, guardar, render |
| app/Livewire/Admin/AreasInstitucionales/AreasInstitucionalesPanel.php | mount, render, limpiarFiltros, crearArea, editarArea, guardarArea, toggleEstado, verArea, cerrarFicha, cerrarFormulario, abrirReportes, cerrarReportes, abrirReporteArea, cerrarReporteArea, generarReporteGeneral, generarReporteArea, imprimirReporteGeneral, exportarReporteGeneralPdf, imprimirReporteArea, exportarReporteAreaPdf, exportarReporteGeneralExcel, exportarAreasExcel, exportarUsuariosAreaExcel, exportarReporteGeneralCsv, obtenerDatosReporteGeneral, obtenerDatosReporteArea, obtenerDatosGraficoUsuariosPorArea, obtenerDatosGraficoAreasPorTipo, obtenerDatosGraficoActivosInactivosPorArea, obtenerDatosGraficoEvolucionMensual, obtenerDatosGraficoActivosInactivosArea, obtenerDatosGraficoUsuariosPorRolArea, obtenerDatosGraficoEvolucionArea, obtenerDatosGraficoRankingAreas, obtenerColorTipo, obtenerIconoTipo, resetForm |
| app/Livewire/Admin/Enfermeria/AlertasPanel.php | abrirCrear, guardarAlerta, atenderAlerta, guardarAtencion, cerrarAlerta, confirmarCierre, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php | abrirCrear, guardar, finalizarAsignacion, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/DashboardTurno.php | mount, loadTurnoActual, exportarReporte, render, iniciarValoracion, construirCalendarioHorarios, normalizarDiaSemana |
| app/Livewire/Admin/Enfermeria/FichaPaciente.php | mount, cambiarTab, render |
| app/Livewire/Admin/Enfermeria/HabitacionesPanel.php | updatingSearch, updatingFiltroTipo, updatingFiltroEstado, abrirCrearHabitacion, abrirEditarHabitacion, guardarHabitacion, eliminarHabitacion, abrirCrearCama, guardarCama, cerrarModales, resetHabitacion, resetCama, getStats, render |
| app/Livewire/Admin/Enfermeria/MisPacientes.php | mount, updatingSearch, updatingFiltroRapido, updatingFiltroTurno, updatingFiltroEnfermero, updatingFiltroEstado, render, obtenerTurnoActual, aplicarFiltroRapido |
| app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php | abrirGenerar, generarPase, recibirPase, abrirVer, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/PlanCuidadoPanel.php | abrirCrear, guardar, cerrarPlan, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/SeguimientoDiarioPanel.php | mount, abrirCrear, guardar, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/TareasPlanPanel.php | abrirCrear, guardarTarea, abrirResultado, guardarResultado, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/TurnosEnfermeriaPanel.php | abrirCrear, abrirEditar, guardar, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/ValoracionEnfermeriaPanel.php | updatingSearch, updatingFiltroEstado, abrirVer, cerrarModales, render |
| app/Livewire/Admin/Enfermeria/ValoracionInicialModal.php | open, close, resetForm, updatedEstadoGeneral, updatedRiesgoCaida, guardar, render |
| app/Livewire/Admin/Enfermeria/ValoracionMedicaPanel.php | abrirCrear, abrirVer, guardar, cerrarModales, render |
| app/Livewire/Admin/FamiliaSocial/RedApoyoPanel.php | mount, updatedAdultoSeleccionado, limpiarSeleccion, actualizarRed, abrirVincular, editarVinculo, updatedFormCodFam, cerrarFormulario, guardarVinculo, abrirDetalleVinculo, cerrarDetalleVinculo, marcarResponsable, marcarContactoEmergencia, desactivarVinculo, activarVinculo, render, rules, messages, adultoActual, adultosDisponibles, familiaresColeccion, voluntariosColeccion, personasListado, familiaresDisponibles, agruparFamiliares, metricas, resolverFamiliar, actualizarDatosFamiliar, actualizarContactoEmergencia, detalleAdulto, resetForm, requiereNuevoFamiliar, nombreAdulto, nombreUsuario, iniciales, edad, formatoFecha, boolValue, adultoTieneContactoEmergencia, esContactoEmergencia, generarPasswordTemporal, generarCorreoTemporal |
| app/Livewire/Admin/Medico/DashboardMedico.php | mount, render, computeEdad, computeDiagnosticos, computeDependencia, computeImc, computeTendencia, computeEstados, computeNotasTipo, iniciarValoracionMedica, abrirDecisionAdmision |
| app/Livewire/Admin/Medico/DecisionAdmisionModal.php | open, close, resetForm, updatedDecision, guardar, render |
| app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php | mount, refreshData, cargarDatos, setTab, nuevaNota, nuevosSignos, nuevaBarthel, nuevaEvaluacionGeriatrica, render |
| app/Livewire/Admin/Medico/NotaEvolucionMedicaModal.php | mount, abrir, cerrar, rules, guardar, resetForm, render |
| app/Livewire/Admin/Medico/PacientesSeguimientoPanel.php | mount, updatingBusqueda, updatingTab, setTab, abrirFicha, nuevaNota, nuevosSignos, render |
| app/Livewire/Admin/Medico/RegistroSignosVitalesModal.php | mount, abrir, cerrar, updatedPeso, updatedTalla, calcularImc, rules, guardar, resetForm, render |
| app/Livewire/Admin/Medico/SignosVitalesPanel.php | mount, updatingBusqueda, setFiltro, abrirRegistroSignos, abrirFicha, alertaPA, alertaFC, alertaFR, alertaTemp, alertaSat, alertaGluc, nivelGlobal, render |
| app/Livewire/Admin/Medico/ValoracionBarthelModal.php | mount, abrir, cerrar, getTotalBarthelProperty, getClasificacionBarthelProperty, updatedAlimentacion, updatedBano, updatedAseoPersonal, updatedVestido, updatedControlIntestinal, updatedControlVesical, updatedUsoRetrete, updatedTraslados, updatedDeambulacion, updatedEscaleras, recalcular, rules, guardar, resetForm, render |
| app/Livewire/Admin/Medico/ValoracionMedicaModal.php | open, close, resetForm, guardar, render |
| app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalDocumentos.php | mount, cargarDocumentos, subirDocumento, eliminarDocumento, validarDocumento, render |
| app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalForm.php | mount, cargarDatos, rules, messages, updatedCorreo, updatedFechaNacimiento, calcularEdad, updatedCiudadId, updatedMunicipioId, updatedZonaId, updatedCalleId, updatedRolSeleccionado, updatedRolesSeleccionados, getClasificacionDerivadaProperty, clasificacionDesdeRoles, sincronizarClasificacionDesdeRoles, mapearRolInstitucional, clasificacionSalud, clasificacionAdministrativa, clasificacionBase, nombreAreaPorDefecto, getDocumentosConfiguradosProperty, obtenerDocumentosInstitucionalesPorRol, descargarPdfInstitucional, prepararGeneracionPdfInstitucional, buildPdfData, updatedArchivosTemporales, observarDocumento, removerDocumento, validarPasoActual, sincronizarSessionTemporal, avanzarPaso, retrocederPaso, gotoStep, confirmarActualizacion, determinarEstadoDocumental, preGuardar, guardar, render, guardarDatosLaboralesEnUsuario, catalogoEspecialidadesPorRol, catalogoCargosAdministrativosPorRol, getDepartamentosCatalogo, getMunicipiosCatalogo, getZonasCatalogo, getCallesCatalogo |
| app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalHorarios.php | mount, cargarDatos, abrirFormNuevo, editarAsignacion, guardar, prepararFinalizar, confirmarFinalizar, cancelarFinalizar, verDetalle, cerrarDetalle, cancelarFormulario, solicitarGuardar, solicitarFinalizar, render, usuario, resolverTipoPersonal, cargarAreas, cargarTurnos, cargarAsignacionesAgrupadas, claveAgrupacion, buscarTurnoCompatible, parsearObservaciones, observacionesSerializadas, verificarSolapamiento, rangosHoraSeCruzan, rangoMinutos, actualizarEstadoAsignacionGrupo, sincronizarTimestampsRegistro, usuarioBloqueado, resetFormulario, normalizarDiaSemana, fechaParaInput, queryHorarios, nuevoModeloHorario, primaryKeyHorario, ordenDiasSql |
| app/Livewire/Admin/PersonalInstitucional/PersonalInstitucionalPanel.php | render, setTab, cambiarVistaListadoResumen, limpiarFiltrosResumen, limpiarFiltros, abrirModalNuevo, abrirModalEdicion, abrirHorariosPersonal, continuarHorariosNuevoPersonal, abrirModuloHorarios, cerrarModal, toggleEstado, obtenerUsuarios, obtenerPersonalResumenPayload, aplicarRubroResumen, aplicarBusquedaResumen, aplicarEstadoResumen, aplicarTurnoResumen, obtenerResumenInstitucional, obtenerEstadisticas, obtenerChartData, obtenerInterpretacionReportes, baseUsuariosInstitucionales, aplicarFiltroInstitucional, contarUsuariosPorCategoria, aplicarTabActiva, aplicarBusqueda, aplicarBusquedaFlexible, aplicarFiltros, aplicarEstado, contarEstado, contarPersonalEnTurno, usuariosConHorarioActivoIds, usuariosConTurnoIds, existeUsuarioInstitucional, contarPersonalSalud, tiposSaludPorFiltro, rolesInstitucionales, rolesSistema, rolesSalud, rolesAdministrativos, rolesExcluidos, esEstadoActivo |
| app/Livewire/Admin/PersonalInstitucional/TurnosAsignacionesPanel.php | mount, abrirNuevaAsignacion, seleccionarUsuario, cerrarModal, cambiarVista, seleccionarFecha, irHoy, moverPeriodo, limpiarFiltros, exportarCalendario, cambiarTipoPlanilla, cambiarVistaPlanilla, generarPlanillaEnfermeria, limpiarFiltrosPlanilla, updatedFechaInicioPlanilla, updatedCantidadSemanas, updatedSaltoSemanal, updatedTrabajadorFiltro, updatedTurnoFiltroPlanilla, updatedGrupoFiltroPlanilla, updatedMostrarApoyo, updatedMostrarDescanso, updatedMostrarGrupos, updatedSoloConflictos, resetPlanillaEnfermeria, render, areasInstitucionales, personalFiltrado, rolesPorArea, obtenerVirtualAsignaciones, asignacionesFiltradas, personalModal, metricas, datosDia, datosSemana, datosMes, datosAnio, eventosFecha, mapEvento, eventosSegunFiltro, estadoVisual, detectarConflictos, asignacionesSeCruzan, rangosHoraSeCruzan, rangoMinutos, estaEnTurnoAhora, bloqueTurno, usuarioActivo, usuarioTieneRol, rolVisual, codAreaUsuario, nombreAreaUsuario, normalizarDiaSemana, abrirAsignarPlaza, guardarAsignacionPlaza, desvincularPlaza |
| app/Livewire/Admin/Psicologia/DashboardPsicologo.php | mount, render |
| app/Livewire/Admin/Psicologia/EvaluacionesAreaPanel.php | mount, updatingBusqueda, nuevaEvaluacion, render |
| app/Livewire/Admin/Psicologia/EvaluacionGeriatricaAreaModal.php | mount, abrir, cerrar, updatedCodInstrumento, rules, guardar, resetForm, render |
| app/Livewire/Admin/RolesPermisos/RolesPermisosPanel.php | roles, rolSeleccionado, usuariosDelRol, mount, agruparPermisos, seleccionarRol, togglePermiso, guardarPermisos, restaurarCambios, abrirUsuariosRol, cerrarUsuariosRol, obtenerNombreVisualRol, obtenerDescripcionRol, obtenerColorRol, obtenerNombreVisualPermiso, render |
| app/Livewire/Admin/SaludSeguimiento/SaludAdministracionMedicacionPanel.php | mount, render |
| app/Livewire/Admin/SaludSeguimiento/SaludAlertasPanel.php | render |
| app/Livewire/Admin/SaludSeguimiento/SaludEvaluacionesGeriatricasPanel.php | mount, render |
| app/Livewire/Admin/SaludSeguimiento/SaludFichaPanel.php | mount, updatingSearchGeneral, updatingFiltroEstado, cargarAdulto, loadData, buscarPacienteAction, getPacientesSelectorProperty, openModalGeneral, openModalAlergias, openModalCondiciones, openModalAntecedentes, openModalObservaciones, closeModal, resetForm, fillForm, save, archivarFicha, render |
| app/Livewire/Admin/SaludSeguimiento/SaludMedicacionPanel.php | mount, updatedCodAm, updatingSearch, updatingFiltroEstado, updatingFiltroVia, limpiarFiltros, render |
| app/Livewire/Admin/SaludSeguimiento/SaludReportesPanel.php | render, generarVistaPrevia |
| app/Livewire/Admin/SaludSeguimiento/SaludResumenPanel.php | mount, render |
| app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php | updatingSearch, abrirExpediente, cerrarExpediente, mount, updatedSeccionActiva, validarPermisoSeccion, cambiarSeccion, getContext, getGlobalStats, getResumenDashboard, render |
| app/Livewire/Admin/SaludSeguimiento/SaludSignosPanel.php | mount, updated, render, buscarPacienteAction, actualizarPanel, limpiarFiltros, cargarAdulto, obtenerPacientesSelector, construirPacienteResumen, construirReporteClinico, abrirFormularioNuevo, abrirFormularioEditar, abrirDetalle, guardar, confirmarGuardarConAlertas, abrirAnular, anularConMotivo, confirmarAnular, restaurarRegistro, abrirFormularioPresion, abrirFormularioCardiaca, abrirFormularioTemperatura, abrirFormularioPeso, abrirFormularioObservacion, cerrarModal, cerrarFormulario, cerrarDetalle, cerrarAnular, limpiarFormulario, detectarAlertas, rules, messages, validarMomentoMedicion, validarCoherenciaClinica, calcularImcVisual, aplicarFiltros, cumpleFiltroParametro, paginarColeccion, obtenerResponsables, construirMetricas, metricaPresion, metricaNumerica, metricaSinDatos, construirResumenPeriodo, construirInterpretacion, construirChartData, construirGraficaPrincipal, construirResumenSemanal, analizarRegistro, clasificarPresion, clasificarNumero, agregarEvaluacion, agregarAlerta, variacionPresion, variacionNumerica, promedioPresion, minMaxPresion, formatearFechaHora |
| app/Livewire/Admin/SaludSeguimiento/SaludValoracionPanel.php | rules, mount, updatedFiltroEstado, updatedFiltroRiesgo, updatedFechaDesde, updatedFechaHasta, abrirFormNuevo, abrirFormEditar, abrirDetalle, abrirAnular, cerrarModales, guardar, confirmarAnular, restaurar, marcarVigente, fillFormDesde, resetForm, render |
| app/Livewire/Admin/Usuarios/UsuarioFichaPanel.php | mount, setTab, abrirSubida, subirArchivo, validarDoc, abrirObservarDoc, observarDoc, abrirAnularDoc, anularDoc, descargarDoc, restablecerPassword, toggleAcceso, generarExpedientePdf, generarExpedienteExcel, render |
| app/Livewire/Admin/Usuarios/UsuarioFormModal.php | rules, messages, abrir, cerrar, togglePasswordSection, generarPasswordTemporal, actualizarPassword, guardar, render |
| app/Livewire/Admin/Usuarios/UsuariosPanel.php | updatedPaisTelefono, updatedPaisDocumento, updatedFechaNacimiento, updatedRol, updatedDepartamentoDomicilio, updatedMunicipioDomicilio, resolverDepartamentoDomicilio, resolverMunicipioDomicilio, resolverZonaDomicilio, armarDireccionCompleta, reglasDireccion, mensajesDireccion, buscarFamiliarExistente, normalizarDatosFormulario, limpiarCamposDependientesDelRol, guardarVinculosFamiliar, adultoMayorLabel, obtenerEtiquetaAdultoMayor, filtrarAdultosMayoresActivos, updatedBusquedaAdultoMayor, seleccionarAdultoMayor, limpiarSeleccionAdultoMayor, obtenerAdultoMayorSeleccionado, obtenerDireccionResumen, updatedEspecialidadSalud, updatedCargoAdministrativo, updatedCorreo, rules, messages, validarUnicidadDocumento, validarTelefono, normalizarTexto, normalizarMayusculas, resetFormulario, crearUsuario, vincularAdultoMayor, desvincularAdultoMayor, registrarYVincularAdulto, editarUsuario, cerrarFormulario, siguientePaso, anteriorPaso, irPaso, generarPasswordTemporal, regenerarPasswordTemporal, guardarUsuario, guardarSubModelos, procesarPostGuardado, abrirVistaCompleta, cerrarVistaCompleta, abrirFichaRapida, cerrarFichaRapida, verUsuario, cambiarSeccionDetalle, volverAlListadoUsuarios, abrirEdicionDesdeDetalle, aplicarFiltros, limpiarFiltros, updatingSearch, updatingFiltroRol, updatingFiltroEstado, updatingFiltroArea, toggleEstado, restablecerPasswordUsuario, exportarUsuariosPdf, exportarUsuariosExcel, exportarUsuariosCsv, obtenerNombreRolLegible, enviarCorreoRequisitosAction, reenviarCorreoRequisitos, cerrarPostRegistro, registrarOtroUsuario, verFichaPostRegistro, abrirModalSubirDoc, cerrarModalSubirDoc, guardarDocumento, render |
| app/Livewire/Admin/Voluntariado/AsignacionesPanel.php | mount, updatingSearch, updatingFechaFiltro, updatingFechaDesde, updatingFechaHasta, updatingEstadoFiltro, updatingTurnoFiltro, updatingAsistenciaFiltro, updatedCodVol, updatedFechaAsig, abrirCrear, editar, reprogramar, cerrarFormulario, guardar, guardarBajoResponsabilidad, limpiarFiltros, verDetalle, cerrarDetalle, confirmarAsignacion, marcarCumplida, cancelarAsignacion, render, guardarAsignacion, rules, messages, asignacionesQuery, aplicarFiltroTurno, aplicarFiltroAsistencia, metricas, proximasAsignaciones, detalleAsignacion, decorarAsignacion, resumenDisponibilidadFormulario, resumenDisponibilidad, advertenciaDisponibilidad, actualizarAdvertenciaDisponibilidad, asignacionDuplicada, cambiarEstado, voluntarioEstaActivo, siguienteCodigoAsignacion, voluntariosActivos, adultosMayores, resetForm, estadoAsistenciaAsignacion, normalizarEstado, whereEstadoActivoAsignacion, whereEstadoNoCancelado, diaDesdeFecha, diaSqlDesdeFecha, variantesDia, turnoDesdeHora, diasSemana, estados, turnos, estadosAsistencia |
| app/Livewire/Admin/Voluntariado/AsistenciaPanel.php | mount, updatingSearch, updatingFechaFiltro, updatingFechaDesde, updatingFechaHasta, updatingEstadoFiltro, updatingTurnoFiltro, abrirCrear, abrirCrearDesdeAsignacion, editar, cerrarFormulario, updatedAsignacionContexto, updatedCodVol, updatedFecha, guardar, limpiarFiltros, verDetalle, cerrarDetalle, marcarAsistio, marcarNoAsistio, justificarAusencia, marcarReprogramado, render, rules, messages, asistenciasQuery, aplicarFiltroTurno, metricas, asignacionesPendientes, asignacionesPendientesQuery, asignacionesFormulario, detalleAsistencia, decorarAsistencia, decorarAsignacionPendiente, programacionFormulario, programacionPorVoluntarioFecha, buscarAsignacion, asignacionRelacionada, actualizarAdvertenciaAsignacion, asistenciaDuplicada, siguienteCodigoAsistencia, cambiarEstado, voluntariosActivos, resetForm, tiempoColaboradoTexto, horasColaboradasTexto, normalizarEstado, normalizarEstadoAsignacion, diaDesdeFecha, diaSqlDesdeFecha, variantesDia, turnoDesdeHora, puedeGestionarAsistencia, diasSemana, estados, turnos |
| app/Livewire/Admin/Voluntariado/DisponibilidadPanel.php | mount, updatingSearch, updatingDiaFiltro, updatingTurnoFiltro, updatingEstadoFiltro, updatedFechaFiltro, semanaAnterior, semanaSiguiente, irHoy, abrirCrear, editar, cerrarFormulario, guardar, marcarNoDisponible, reactivarDisponibilidad, limpiarFiltros, render, rules, messages, disponibilidadQuery, aplicarFiltroTurno, aplicarFiltroEstado, decorarDisponibilidad, calendario, metricas, voluntariosActivos, horarioDuplicado, voluntarioEstaActivo, siguienteCodigoDisponibilidad, resetForm, turnoDesdeHora, estadoOperativo, fechaParaDia, diaDesdeFecha, rangoSemanaTexto, ordenDiaSql, diasSemana, turnos, estados |
| app/Livewire/Admin/Voluntariado/VoluntariadoResumenPanel.php | mount, render, obtenerMetricas, metricasResumen, flujoOperativo, submodulos, proximasAsignaciones, alertasOperativas, asignacionesHoySinAsistencia, whereAsignacionVigenteEn, whereEstadoActivo, whereEstadoOperativo, whereEstadoAusenteOPendiente, nombrePersona, tablaExiste |
| app/Livewire/Admin/Voluntariado/VoluntariosPanel.php | mount, updatingSearch, updatingEstadoFiltro, updatingAreaFiltro, updatingTipoFiltro, updatingDisponibilidadFiltro, abrirCrear, editar, cerrarFormulario, guardar, cambiarEstado, archivar, verPerfil, cerrarPerfil, limpiarFiltros, render, rules, messages, crearVoluntario, actualizarVoluntario, voluntariosQuery, voluntarioBaseQuery, voluntarioDetalleQuery, metricas, disponibilidades, asignacionesActivas, asistenciasRecientes, conteo, conteoAsignacionesActivas, recargarPerfilSiCorresponde, resetForm, areasDisponibles, areasSugeridas, estados, linksSubmodulos, areaInstitucionalVoluntariado, existeColumnaVoluntario, columnaVoluntario, voluntariosNoArchivadosQuery, whereArchivado, insertarVoluntario, actualizarVoluntarioTabla, filtrarColumnasVoluntarios |
| app/Services/Admin/AdultoMayorBitacoraService.php | obtenerBitacora, obtenerTiposEventos, formatearRegistro, traducirLogName |
| app/Services/Admin/AdultoMayorService.php | obtenerListado, obtenerDetalle, obtenerEstados, obtenerTiposAtenciones, calcularEdad, guardarFoto, crearAdultoMayor, actualizarAdultoMayor, archivar, restaurar |
| app/Services/Dashboard/DashboardService.php | obtenerDatosDashboard, obtenerSaludoUsuario, obtenerKpisInstitucionales, obtenerResumenSalud, obtenerAlertasEstructuradas, obtenerEquipoInstitucional, obtenerAdultosPorEstado, obtenerDistribucionEquipoInstitucional, obtenerRedFamiliar, conteoAdultosConEstado, obtenerEstadisticas, obtenerDistribucionRoles, obtenerActividadMensual, obtenerListaUsuarios, obtenerUltimasActividades, generarAlertasInteligentes, obtenerModulosInstitucionales, obtenerBitacoraAuditoria, traducirEvento, traducirModulo, limpiarDescripcion, conteoSeguro, obtenerRolPrimario, limpiarCache |
| app/Services/Enfermeria/GeneradorPlanillaEnfermeriaService.php | generar, normalizarConfig, serializarConfig, resolverEnfermeros, enfermerosVirtuales, generarSemana, generarDia, rotarCodigo, buscarEnfermeroPorCodigo, resolverGrupoPorTurno, calcularCargaLaboral, clasificarCarga, calcularNochesConsecutivasMax, validarAlertas, alerta, validarCoberturaDia, generarResumen, calcularEquilibrio, mcd, generarVistaSemanal, generarVistaHoy, generarVistaPorEnfermero, aplicarFiltros, generarFiltrosDisponibles, recorrerAsignaciones, familiaVisual, claseFamilia, obtenerTurnoEnFecha |
| app/Services/Reportes/AreasReportDataService.php | getGeneralReportData, getAreaReportData, getUsuariosPorArea, getAreasPorTipo, getActivosInactivosPorArea, getEvolucionMensualGlobal, getRankingAreasUsuarios, getActivosInactivosArea, getUsuariosPorRolArea, getEvolucionArea |
| app/Services/Reportes/ReportChartDataService.php | usuariosPorArea, areasPorTipo, usuariosActivosInactivosPorArea, usuariosPorRol, evolucionUsuariosPorMes, evaluacionesPorMes, adultosPorEstado, movimientosBitacoraPorMes |
| app/Services/Reportes/ReporteDataService.php | adultosResumen, adultosListaCompleta, adultosLista, adultosEstado, adultosGenero, adultosEdad, saludResumen, fichasLista, medicacionLista, valoracionesLista, atencionesPorMes, dependenciaDistribucion, riesgosCaida, familiaresResumen, vinculosLista, adultosSinFamiliar, parentescosDistribucion, equipoResumen, personalSaludLista, personalAdminLista, voluntariosLista, especialidadesDistribucion, areasVoluntariosDistribucion, actividadesResumen, actividadesLista, actividadesPorMes, actividadesEstado, actividadesPorAdulto, bitacoraResumen, bitacoraLista, bitacoraModulos, bitacoraEventos, bitacoraTendencia, sinEliminados |
| app/Services/Reportes/ReportExportService.php | exportPdf, exportExcel, exportCsv |
| app/Services/Reportes/ReportFileNameService.php | generate |
| app/Services/Reportes/ReportWatermarkService.php | getText, getCssStyles |
| app/Services/SidebarService.php | getSidebar, getAdministrativeSidebar, buildSection, buildItem, isPlaceholderRoute, isSuperadmin |
| app/Services/Usuarios/DocumentacionUsuarioService.php | obtenerChecklistUsuario, calcularAvanceDocumental, subirDocumento, validarDocumento, observarDocumento, anularDocumento |
| app/Services/Usuarios/DocumentosUsuarioService.php | documentosRequeridosPorRol, documentosInstitucionalesPorRol, documentosGeneradosPorRol, documentoDisponibleParaRol, datosUsuario, datosDocumentoIndividual, vistaDocumentoIndividual, nombreArchivoDocumento, nombreArchivoPaquete, prepararPaquete, datosDocumentoUsuario, obtenerAreaCargo, obtenerVinculosFamiliares |
| app/Services/Usuarios/UsuarioFichaService.php | obtenerExpedienteCompleto, obtenerHorariosActivos, obtenerHistorialHorarios, obtenerHistorialActividad, formatearRol |
| app/Http/Controllers/Admin/AdultoMayorController.php | __construct, index, create, store, show, reporteIndividual, reporteEspecifico, reporteGeneral, reporteInstitucional, reporteBienestar, edit, update, archivar, restaurar, cambiarEstado, destroy, anularEvaluacionGeriatrica, pdfEvaluacionGeriatrica |
| app/Http/Controllers/Admin/AdultosMayores/AdultoMayorActividadController.php | store, update, destroy, restore |
| app/Http/Controllers/Admin/AdultosMayores/AdultoMayorAtencionController.php | index, store, update, destroy, restore |
| app/Http/Controllers/Admin/AdultosMayores/AdultoMayorDocumentoController.php | index, store, update, destroy, restore |
| app/Http/Controllers/Admin/AdultosMayores/AdultoMayorEvaluacionController.php | index, store, interpretarPuntaje, destroy, restore |
| app/Http/Controllers/Admin/AdultosMayores/AdultoMayorFamiliarController.php | index, store, generarPasswordTemporal, generarCorreoTemporal, update, destroy, restore |
| app/Http/Controllers/Admin/AdultosMayores/AdultoMayorObservacionController.php | index, store, update, destroy, restore |
| app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorAdministracionMedicacionController.php | store |
| app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorFichaMedicaController.php | store, update, archivar, restore |
| app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorMedicacionController.php | store, update, suspender, finalizar, archivar, restore |
| app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorSignosVitalesController.php | store, update |
| app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorValoracionFuncionalController.php | store, update |
| app/Http/Controllers/Admin/AreasInstitucionales/AreaReporteController.php | __construct, cleanUtf8, generalPdf, areaPdf, generalExcel, generalCsv, areaExcel |
| app/Http/Controllers/Admin/BitacoraController.php | index, obtenerInfoModulo, obtenerInfoEvento, identificarRegistroAfectado |
| app/Http/Controllers/Admin/Enfermeria/FichaPacienteReporteController.php | pdf |
| app/Http/Controllers/Admin/FamiliaSocial/ResumenFamiliaSocialController.php | __invoke, dashboardData, hasTable, hasColumn, adultosBaseQuery, vinculosActivosQuery, adultosConRedIds, adultosSinRedIds, adultosConResponsableIds, adultosSinResponsableIds, adultosSinContactoIds, adultosSinFichaIds, countAdultosByIds, countFamiliares, countVinculosActivos, visitasData, visitasMensuales, visitasRecientesList, fichaSocialData, ultimasFichasSociales, fichaSocialTable, redIncompletaList, alertasSociales, adultosByIds, adultoSelectColumns, reportesSociales, firstExistingTable, firstExistingColumn, firstFilled, nombreDesdeCampos, nombreAdulto, percent, nivelSocial, mesCorto |
| app/Http/Controllers/Admin/PersonalDocumentosController.php | generarPdf |
| app/Http/Controllers/Admin/Reportes/ReporteActividadesController.php | __construct, compilarDatos, preview, pdf, excel |
| app/Http/Controllers/Admin/Reportes/ReporteAdultosController.php | __construct, prepararPayload, preview, pdf, excel |
| app/Http/Controllers/Admin/Reportes/ReporteBitacoraController.php | __construct, compilarDatos, preview, pdf |
| app/Http/Controllers/Admin/Reportes/ReporteEquipoController.php | __construct, compilarDatos, preview, pdf, excel |
| app/Http/Controllers/Admin/Reportes/ReporteFamiliaresController.php | __construct, compilarDatos, preview, pdf, excel |
| app/Http/Controllers/Admin/Reportes/ReporteInstitucionalController.php | __construct, obtenerDatos, preview, pdf, excel |
| app/Http/Controllers/Admin/Reportes/ReporteSaludController.php | __construct, compilarDatos, preview, pdf, excel |
| app/Http/Controllers/Admin/UsuarioController.php | index, create, store, generarPasswordInicial, show, edit, update, desactivar, activar, destroy, fichaPdf, documentacionPdf, horariosPdf, enviarFichaCorreo, solicitudDocumentalPdf |
| app/Http/Controllers/Admin/Usuarios/DocumentosUsuarioController.php | __construct, preview, paquetePdf, verDocumento, pdfDocumento, imprimirDocumento, enviarPaqueteCorreo |
| app/Http/Controllers/Controller.php | Sin métodos propios |
| app/Http/Controllers/DashboardController.php | __construct, index, registrarAcceso |
| app/Http/Requests/Admin/AdultosMayores/Salud/StoreAdministracionMedicacionRequest.php | authorize, rules, messages, prepareForValidation |
| app/Http/Requests/Admin/AdultosMayores/Salud/StoreCambioEstadoRequest.php | authorize, rules, messages |
| app/Http/Requests/Admin/AdultosMayores/Salud/StoreFichaMedicaRequest.php | authorize, rules, messages, prepareForValidation |
| app/Http/Requests/Admin/AdultosMayores/Salud/StoreMedicacionRequest.php | authorize, rules, messages |
| app/Http/Requests/Admin/AdultosMayores/Salud/StoreSignosVitalesRequest.php | authorize, rules, messages, withValidator |
| app/Http/Requests/Admin/AdultosMayores/Salud/StoreValoracionFuncionalRequest.php | authorize, rules, messages, prepareForValidation |
| app/Http/Requests/Admin/AdultosMayores/Salud/UpdateFichaMedicaRequest.php | rules |
| app/Http/Requests/Admin/AdultosMayores/Salud/UpdateMedicacionRequest.php | rules |
| app/Http/Requests/Admin/AdultosMayores/StoreActividadAdultoRequest.php | authorize, rules, messages |
| app/Http/Requests/Admin/AdultosMayores/StoreAtencionAdultoRequest.php | authorize, prepareForValidation, rules, messages |
| app/Http/Requests/Admin/AdultosMayores/StoreDocumentoAdultoRequest.php | authorize, rules, messages |
| app/Http/Requests/Admin/AdultosMayores/StoreFamiliarAdultoRequest.php | authorize, rules, messages |
| app/Http/Requests/Admin/AdultosMayores/StoreObservacionAdultoRequest.php | authorize, rules, messages |
| app/Http/Requests/Admin/StoreAdultoMayorRequest.php | authorize, prepareForValidation, rules, messages |
| app/Http/Requests/Admin/StoreUsuarioRequest.php | authorize, prepareForValidation, rules, messages |
| app/Http/Requests/Admin/UpdateAdultoMayorRequest.php | authorize, prepareForValidation, rules, messages |
| app/Http/Requests/Admin/UpdateUsuarioRequest.php | authorize, prepareForValidation, rules, messages |
| app/Http/Responses/LoginResponse.php | toResponse |
| tests/Feature/ApiTokenPermissionsTest.php | test_api_token_permissions_can_be_updated |
| tests/Feature/AuthenticationTest.php | test_login_screen_can_be_rendered, test_users_can_authenticate_using_the_login_screen, test_users_can_not_authenticate_with_invalid_password |
| tests/Feature/BrowserSessionsTest.php | test_other_browser_sessions_can_be_logged_out |
| tests/Feature/CasosPreadmisionTest.php | setUp, test_invitados_son_redirigidos_al_login_al_intentar_ver_casos, test_usuario_con_permiso_puede_ver_panel_de_casos, test_usuario_sin_permiso_recibe_403_al_intentar_ver_casos, test_wizard_registro_de_caso_responde_200, test_creacion_y_persistencia_de_caso_en_base_de_datos |
| tests/Feature/CreateApiTokenTest.php | test_api_tokens_can_be_created |
| tests/Feature/DeleteAccountTest.php | test_user_accounts_can_be_deleted, test_correct_password_must_be_provided_before_account_can_be_deleted |
| tests/Feature/DeleteApiTokenTest.php | test_api_tokens_can_be_deleted |
| tests/Feature/EmailVerificationTest.php | test_email_verification_screen_can_be_rendered, test_email_can_be_verified, test_email_can_not_verified_with_invalid_hash |
| tests/Feature/ExampleTest.php | test_the_application_returns_a_successful_response |
| tests/Feature/PasswordConfirmationTest.php | test_confirm_password_screen_can_be_rendered, test_password_can_be_confirmed, test_password_is_not_confirmed_with_invalid_password |
| tests/Feature/PasswordResetTest.php | test_reset_password_link_screen_can_be_rendered, test_reset_password_link_can_be_requested, test_reset_password_screen_can_be_rendered, test_password_can_be_reset_with_valid_token |
| tests/Feature/PersonalInstitucionalHorariosTest.php | test_creates_schedule_rows_and_updates_user_area, test_editing_an_assignment_finalizes_previous_rows_and_creates_a_new_version, crearUsuarioConRol, crearArea, crearTurno |
| tests/Feature/ProfileInformationTest.php | test_current_profile_information_is_available, test_profile_information_can_be_updated |
| tests/Feature/RegistrationTest.php | test_registration_screen_can_be_rendered, test_registration_screen_cannot_be_rendered_if_support_is_disabled, test_new_users_can_register |
| tests/Feature/TwoFactorAuthenticationSettingsTest.php | test_two_factor_authentication_can_be_enabled, test_recovery_codes_can_be_regenerated, test_two_factor_authentication_can_be_disabled |
| tests/Feature/UpdatePasswordTest.php | test_password_can_be_updated, test_current_password_must_be_correct, test_new_passwords_must_match |
| tests/Feature/UsuariosRoutesPermissionsTest.php | setUp, test_invitado_no_puede_acceder_a_las_rutas_de_usuarios, test_usuario_con_solo_permiso_ver_puede_listar, test_usuario_con_solo_permiso_ver_no_puede_abrir_create, test_usuario_con_solo_permiso_ver_no_puede_abrir_edit, test_usuario_con_permiso_crear_puede_abrir_create, test_usuario_con_permiso_editar_puede_abrir_edit, userWithOnlyPermission |
| tests/TestCase.php | Sin métodos propios |
| tests/Unit/ExampleTest.php | test_that_true_is_true |

## Índice de plantillas inspeccionadas

Las familias anteriores explican su funcionalidad. Este índice permite revisar cobertura de layouts, parciales, componentes, PDFs, correos y pantallas, sin equiparar archivo con módulo.

- `resources/views/admin/admisiones/index.blade.php`
- `resources/views/admin/adultos-mayores/alertas-pendientes-placeholder.blade.php`
- `resources/views/admin/adultos-mayores/create.blade.php`
- `resources/views/admin/adultos-mayores/documentos/index.blade.php`
- `resources/views/admin/adultos-mayores/edit.blade.php`
- `resources/views/admin/adultos-mayores/index.blade.php`
- `resources/views/admin/adultos-mayores/partials/actions.blade.php`
- `resources/views/admin/adultos-mayores/partials/filters.blade.php`
- `resources/views/admin/adultos-mayores/partials/form.blade.php`
- `resources/views/admin/adultos-mayores/reportes/bienestar.blade.php`
- `resources/views/admin/adultos-mayores/reportes/general.blade.php`
- `resources/views/admin/adultos-mayores/reportes/individual.blade.php`
- `resources/views/admin/adultos-mayores/reportes/institucional.blade.php`
- `resources/views/admin/adultos-mayores/reportes/pdf_especifico.blade.php`
- `resources/views/admin/adultos-mayores/reportes/pdf_evaluacion_individual.blade.php`
- `resources/views/admin/adultos-mayores/reportes/pdf_individual.blade.php`
- `resources/views/admin/adultos-mayores/reportes/word_individual.blade.php`
- `resources/views/admin/adultos-mayores/show.blade.php`
- `resources/views/admin/adultos-mayores/show.blade.php.bak`
- `resources/views/admin/adultos-mayores/show/_actividades.blade.php`
- `resources/views/admin/adultos-mayores/show/_atenciones.blade.php`
- `resources/views/admin/adultos-mayores/show/_cabecera-expediente.blade.php`
- `resources/views/admin/adultos-mayores/show/_documentos.blade.php`
- `resources/views/admin/adultos-mayores/show/_evaluaciones-cognitivas.blade.php`
- `resources/views/admin/adultos-mayores/show/_familiares.blade.php`
- `resources/views/admin/adultos-mayores/show/_historial-estados.blade.php`
- `resources/views/admin/adultos-mayores/show/_modales-existentes.blade.php`
- `resources/views/admin/adultos-mayores/show/_reportes.blade.php`
- `resources/views/admin/adultos-mayores/show/_resumen.blade.php`
- `resources/views/admin/adultos-mayores/show/_salud-medica.blade.php`
- `resources/views/admin/adultos-mayores/show/_seguimiento-observaciones.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_cognitivo.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_documentos.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_historial.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_identificacion.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_participacion.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_red-apoyo.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_reportes.blade.php`
- `resources/views/admin/adultos-mayores/show/carpetas/_salud.blade.php`
- `resources/views/admin/areas-institucionales/index.blade.php`
- `resources/views/admin/areas-institucionales/reportes/area-pdf.blade.php`
- `resources/views/admin/areas-institucionales/reportes/general-pdf.blade.php`
- `resources/views/admin/bitacora/index.blade.php`
- `resources/views/admin/enfermeria/reportes/ficha-paciente-pdf.blade.php`
- `resources/views/admin/familia-social/base.blade.php`
- `resources/views/admin/familia-social/resumen.blade.php`
- `resources/views/admin/personal-institucional/pdfs/confidencialidad.blade.php`
- `resources/views/admin/personal-institucional/pdfs/contrato.blade.php`
- `resources/views/admin/personal-institucional/pdfs/funciones.blade.php`
- `resources/views/admin/personal-institucional/pdfs/reglamento.blade.php`
- `resources/views/admin/roles-permisos/index.blade.php`
- `resources/views/admin/turnos-asignaciones/index.blade.php`
- `resources/views/admin/usuarios/create.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/autorizacion-administrativa.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/autorizacion-comunicacion.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/carta-bienvenida.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/checklist-institucional.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/checklist-requeridos.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/compromiso-voluntario.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/confidencialidad-clinica.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/confidencialidad.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/constancia-familiar.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/constancia-registro.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/protocolo-documental.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/protocolo-emergencias.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/protocolo-registro-clinico.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/reglamento-visitas.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/reglamento-voluntariado.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/responsabilidad-administrativa.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/responsabilidad-clinica.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/responsabilidad-documental.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/trato-digno.blade.php`
- `resources/views/admin/usuarios/documentos/individuales/uso-responsable.blade.php`
- `resources/views/admin/usuarios/documentos/layout.blade.php`
- `resources/views/admin/usuarios/documentos/paquete-documental.blade.php`
- `resources/views/admin/usuarios/documentos/paquete_documental.blade.php`
- `resources/views/admin/usuarios/documentos/partials/acuerdo_corresponsabilidad.blade.php`
- `resources/views/admin/usuarios/documentos/partials/acuerdo_voluntariado.blade.php`
- `resources/views/admin/usuarios/documentos/partials/caratula.blade.php`
- `resources/views/admin/usuarios/documentos/partials/checklist_recepcion.blade.php`
- `resources/views/admin/usuarios/documentos/partials/compromiso_confidencialidad.blade.php`
- `resources/views/admin/usuarios/documentos/partials/datos-usuario.blade.php`
- `resources/views/admin/usuarios/documentos/partials/datos_personales.blade.php`
- `resources/views/admin/usuarios/documentos/partials/encabezado-documento.blade.php`
- `resources/views/admin/usuarios/documentos/partials/firmas.blade.php`
- `resources/views/admin/usuarios/documentos/partials/pie-documento.blade.php`
- `resources/views/admin/usuarios/documentos/partials/reglamento_interno.blade.php`
- `resources/views/admin/usuarios/edit.blade.php`
- `resources/views/admin/usuarios/index.blade.php`
- `resources/views/admin/usuarios/show.blade.php`
- `resources/views/admin/voluntarios/asignaciones.blade.php`
- `resources/views/admin/voluntarios/asistencia.blade.php`
- `resources/views/admin/voluntarios/disponibilidad.blade.php`
- `resources/views/admin/voluntarios/index.blade.php`
- `resources/views/admin/voluntarios/voluntarios.blade.php`
- `resources/views/api/api-token-manager.blade.php`
- `resources/views/api/index.blade.php`
- `resources/views/auth/confirm-password.blade.php`
- `resources/views/auth/forgot-password.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/auth/reset-password.blade.php`
- `resources/views/auth/two-factor-challenge.blade.php`
- `resources/views/auth/verify-email.blade.php`
- `resources/views/components/action-message.blade.php`
- `resources/views/components/action-section.blade.php`
- `resources/views/components/admin/admisiones/⚡preadmision-wizard.blade.php`
- `resources/views/components/admin/adultos-mayores/reportes/⚡reportes-adulto-panel.blade.php`
- `resources/views/components/admin/enfermeria/⚡registro-signos-vitales.blade.php`
- `resources/views/components/admin/salud-seguimiento/⚡salud-seguimiento-master-panel.blade.php`
- `resources/views/components/application-logo.blade.php`
- `resources/views/components/application-mark.blade.php`
- `resources/views/components/authentication-card-logo.blade.php`
- `resources/views/components/authentication-card.blade.php`
- `resources/views/components/banner.blade.php`
- `resources/views/components/button.blade.php`
- `resources/views/components/checkbox.blade.php`
- `resources/views/components/confirmation-modal.blade.php`
- `resources/views/components/confirms-password.blade.php`
- `resources/views/components/danger-button.blade.php`
- `resources/views/components/dashboard-card.blade.php`
- `resources/views/components/dialog-modal.blade.php`
- `resources/views/components/dropdown-link.blade.php`
- `resources/views/components/dropdown.blade.php`
- `resources/views/components/enfermeria/⚡dashboard-turno.blade.php`
- `resources/views/components/enfermeria/⚡ficha-paciente.blade.php`
- `resources/views/components/enfermeria/⚡mis-pacientes.blade.php`
- `resources/views/components/form-section.blade.php`
- `resources/views/components/input-error.blade.php`
- `resources/views/components/input.blade.php`
- `resources/views/components/label.blade.php`
- `resources/views/components/layout/barra-lateral-sistema.blade.php`
- `resources/views/components/layout/navbar-sistema.blade.php`
- `resources/views/components/modal.blade.php`
- `resources/views/components/modules/adulto-mini-card.blade.php`
- `resources/views/components/nav-link.blade.php`
- `resources/views/components/responsive-nav-link.blade.php`
- `resources/views/components/secondary-button.blade.php`
- `resources/views/components/section-border.blade.php`
- `resources/views/components/section-title.blade.php`
- `resources/views/components/stat-card.blade.php`
- `resources/views/components/switchable-team.blade.php`
- `resources/views/components/ui/empty-state.blade.php`
- `resources/views/components/ui/encabezado-dashboard.blade.php`
- `resources/views/components/ui/grafico-dashboard.blade.php`
- `resources/views/components/ui/kpis-dashboard.blade.php`
- `resources/views/components/ui/metric-card.blade.php`
- `resources/views/components/ui/modal-livewire.blade.php`
- `resources/views/components/ui/page-header.blade.php`
- `resources/views/components/ui/panel-actividades-dashboard.blade.php`
- `resources/views/components/ui/panel-alertas-dashboard.blade.php`
- `resources/views/components/ui/panel-equipo-institucional.blade.php`
- `resources/views/components/ui/panel-modulos-dashboard.blade.php`
- `resources/views/components/ui/panel-salud-dashboard.blade.php`
- `resources/views/components/ui/seccion-graficos-dashboard.blade.php`
- `resources/views/components/ui/status-badge.blade.php`
- `resources/views/components/ui/sweetalert.blade.php`
- `resources/views/components/ui/tabla-bitacora-dashboard.blade.php`
- `resources/views/components/ui/tabla-usuarios-dashboard.blade.php`
- `resources/views/components/ui/toast.blade.php`
- `resources/views/components/validation-errors.blade.php`
- `resources/views/dashboard.blade.php`
- `resources/views/emails/personal-institucional/bienvenida.blade.php`
- `resources/views/emails/preadmision/documentos-pendientes.blade.php`
- `resources/views/emails/team-invitation.blade.php`
- `resources/views/emails/usuarios/bienvenida.blade.php`
- `resources/views/emails/usuarios/confirmacion-registro.blade.php`
- `resources/views/emails/usuarios/credenciales-iniciales.blade.php`
- `resources/views/emails/usuarios/ficha-adjunta.blade.php`
- `resources/views/emails/usuarios/paquete_documental.blade.php`
- `resources/views/emails/usuarios/password-actualizada.blade.php`
- `resources/views/emails/usuarios/solicitud-documental.blade.php`
- `resources/views/errors/403.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/sistema.blade.php`
- `resources/views/livewire/admin/actividades/actividades-panel.blade.php`
- `resources/views/livewire/admin/actividades/asistencia-panel.blade.php`
- `resources/views/livewire/admin/actividades/participacion-panel.blade.php`
- `resources/views/livewire/admin/actividades/reportes-actividades-panel.blade.php`
- `resources/views/livewire/admin/actividades/tipos-actividad-panel.blade.php`
- `resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php`
- `resources/views/livewire/admin/admisiones/preadmisiones-panel.blade.php`
- `resources/views/livewire/admin/adultos-mayores/adulto-mayor-form-modal.blade.php`
- `resources/views/livewire/admin/adultos-mayores/adultos-mayores-panel.blade.php`
- `resources/views/livewire/admin/adultos-mayores/alertas-pendientes-panel.blade.php`
- `resources/views/livewire/admin/adultos-mayores/evaluaciones/evaluacion-geriatrica-modal.blade.php`
- `resources/views/livewire/admin/adultos-mayores/reportes-institucionales-panel.blade.php`
- `resources/views/livewire/admin/adultos-mayores/reportes/reportes-adulto-panel.blade.php`
- `resources/views/livewire/admin/adultos-mayores/salud/administracion-medicacion-modal.blade.php`
- `resources/views/livewire/admin/adultos-mayores/salud/ficha-medica-adulto-modal.blade.php`
- `resources/views/livewire/admin/adultos-mayores/salud/historial-estado-adulto-panel.blade.php`
- `resources/views/livewire/admin/adultos-mayores/salud/medicacion-adulto-modal.blade.php`
- `resources/views/livewire/admin/adultos-mayores/salud/signos-vitales-adulto-modal.blade.php`
- `resources/views/livewire/admin/adultos-mayores/salud/valoracion-funcional-adulto-modal.blade.php`
- `resources/views/livewire/admin/areas-institucionales/areas-institucionales-panel.blade.php`
- `resources/views/livewire/admin/enfermeria${view}.blade.php`
- `resources/views/livewire/admin/enfermeria/alertas-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/dashboard-turno.blade.php`
- `resources/views/livewire/admin/enfermeria/ficha-paciente.blade.php`
- `resources/views/livewire/admin/enfermeria/habitaciones-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/mis-pacientes.blade.php`
- `resources/views/livewire/admin/enfermeria/pase-turno-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/plan-cuidado-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/seguimiento-diario-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/tareas-plan-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/turnos-enfermeria-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/valoracion-enfermeria-panel.blade.php`
- `resources/views/livewire/admin/enfermeria/valoracion-inicial-modal.blade.php`
- `resources/views/livewire/admin/enfermeria/valoracion-medica-panel.blade.php`
- `resources/views/livewire/admin/familia-social/_nodo-familiar.blade.php`
- `resources/views/livewire/admin/familia-social/red-apoyo-panel.blade.php`
- `resources/views/livewire/admin/medico/dashboard-medico.blade.php`
- `resources/views/livewire/admin/medico/decision-admision-modal.blade.php`
- `resources/views/livewire/admin/medico/ficha-clinica-integrada-panel.blade.php`
- `resources/views/livewire/admin/medico/nota-evolucion-medica-modal.blade.php`
- `resources/views/livewire/admin/medico/pacientes-seguimiento-panel.blade.php`
- `resources/views/livewire/admin/medico/registro-signos-vitales-modal.blade.php`
- `resources/views/livewire/admin/medico/signos-vitales-panel.blade.php`
- `resources/views/livewire/admin/medico/valoracion-barthel-modal.blade.php`
- `resources/views/livewire/admin/medico/valoracion-medica-modal.blade.php`
- `resources/views/livewire/admin/personal-institucional/partials/personal-institucional-documentos.blade.php`
- `resources/views/livewire/admin/personal-institucional/partials/personal-institucional-form.blade.php`
- `resources/views/livewire/admin/personal-institucional/partials/personal-institucional-horarios.blade.php`
- `resources/views/livewire/admin/personal-institucional/personal-institucional-panel.blade.php`
- `resources/views/livewire/admin/personal-institucional/turnos-asignaciones-panel.blade.php`
- `resources/views/livewire/admin/psicologia/dashboard-psicologo.blade.php`
- `resources/views/livewire/admin/psicologia/evaluacion-geriatrica-area-modal.blade.php`
- `resources/views/livewire/admin/psicologia/evaluaciones-area-panel.blade.php`
- `resources/views/livewire/admin/roles-permisos/roles-permisos-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/_parciales/cabecera-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-administracion-medicacion.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-alertas-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-evaluaciones-geriatricas-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-ficha-general.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-ficha-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-medicacion.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-panel-individual.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-reportes-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-resumen-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-seguimiento-list-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-signos-panel.blade.php`
- `resources/views/livewire/admin/salud-seguimiento/salud-valoracion-funcional.blade.php`
- `resources/views/livewire/admin/usuarios/usuario-ficha-panel.blade.php`
- `resources/views/livewire/admin/usuarios/usuario-form-modal.blade.php`
- `resources/views/livewire/admin/usuarios/usuarios-panel.blade.php`
- `resources/views/livewire/admin/voluntariado/asignaciones-panel.blade.php`
- `resources/views/livewire/admin/voluntariado/asistencia-panel.blade.php`
- `resources/views/livewire/admin/voluntariado/disponibilidad-panel.blade.php`
- `resources/views/livewire/admin/voluntariado/voluntariado-resumen-panel.blade.php`
- `resources/views/livewire/admin/voluntariado/voluntarios-panel.blade.php`
- `resources/views/navigation-menu.blade.php`
- `resources/views/pdf/exports/actividades/general.blade.php`
- `resources/views/pdf/exports/adultos-mayores/general.blade.php`
- `resources/views/pdf/exports/adultos-mayores/individual.blade.php`
- `resources/views/pdf/exports/areas/area.blade.php`
- `resources/views/pdf/exports/areas/general.blade.php`
- `resources/views/pdf/exports/bitacora/general.blade.php`
- `resources/views/pdf/exports/evaluaciones/evolucion-cognitiva.blade.php`
- `resources/views/pdf/exports/evaluaciones/general.blade.php`
- `resources/views/pdf/exports/layouts/report-footer.blade.php`
- `resources/views/pdf/exports/layouts/report-header.blade.php`
- `resources/views/pdf/exports/layouts/report-layout.blade.php`
- `resources/views/pdf/exports/layouts/watermark.blade.php`
- `resources/views/pdf/exports/salud/general.blade.php`
- `resources/views/pdf/exports/turnos/area.blade.php`
- `resources/views/pdf/exports/turnos/cobertura-semanal.blade.php`
- `resources/views/pdf/exports/turnos/general.blade.php`
- `resources/views/pdf/exports/usuarios/documentacion_pdf.blade.php`
- `resources/views/pdf/exports/usuarios/excel_ficha.blade.php`
- `resources/views/pdf/exports/usuarios/expediente_ficha.blade.php`
- `resources/views/pdf/exports/usuarios/general.blade.php`
- `resources/views/pdf/exports/usuarios/horarios_pdf.blade.php`
- `resources/views/pdf/exports/usuarios/solicitud_documental_pdf.blade.php`
- `resources/views/pdf/exports/usuarios/usuario.blade.php`
- `resources/views/pdf/exports/voluntariado/general.blade.php`
- `resources/views/pdf/personal-institucional/acta-funciones.blade.php`
- `resources/views/pdf/personal-institucional/bienvenida.blade.php`
- `resources/views/pdf/personal-institucional/confidencialidad.blade.php`
- `resources/views/pdf/personal-institucional/contrato.blade.php`
- `resources/views/pdf/personal-institucional/ficha.blade.php`
- `resources/views/pdf/preadmision/acta-recepcion.blade.php`
- `resources/views/pdf/preadmision/autorizacion-valoracion.blade.php`
- `resources/views/pdf/preadmision/consentimiento-datos.blade.php`
- `resources/views/pdf/preadmision/ficha.blade.php`
- `resources/views/policy.blade.php`
- `resources/views/profile/delete-user-form.blade.php`
- `resources/views/profile/logout-other-browser-sessions-form.blade.php`
- `resources/views/profile/show.blade.php`
- `resources/views/profile/two-factor-authentication-form.blade.php`
- `resources/views/profile/update-password-form.blade.php`
- `resources/views/profile/update-profile-information-form.blade.php`
- `resources/views/reportes/actividades/index.blade.php`
- `resources/views/reportes/adultos/index.blade.php`
- `resources/views/reportes/bitacora/index.blade.php`
- `resources/views/reportes/equipo/index.blade.php`
- `resources/views/reportes/familiares/index.blade.php`
- `resources/views/reportes/institucional/general.blade.php`
- `resources/views/reportes/layouts/reporte-base.blade.php`
- `resources/views/reportes/partials/grafica-barras-pdf.blade.php`
- `resources/views/reportes/salud/index.blade.php`
- `resources/views/terms.blade.php`
- `resources/views/vendor/pagination/bootstrap-3.blade.php`
- `resources/views/vendor/pagination/bootstrap-4.blade.php`
- `resources/views/vendor/pagination/bootstrap-5.blade.php`
- `resources/views/vendor/pagination/semantic-ui.blade.php`
- `resources/views/vendor/pagination/simple-bootstrap-3.blade.php`
- `resources/views/vendor/pagination/simple-bootstrap-4.blade.php`
- `resources/views/vendor/pagination/simple-bootstrap-5.blade.php`
- `resources/views/vendor/pagination/simple-tailwind.blade.php`
- `resources/views/vendor/pagination/tailwind.blade.php`
- `resources/views/welcome.blade.php`

## Hallazgo adicional: datos clínicos almacenados solo en bitácora

App/Livewire/Admin/Medico/ValoracionMedicaModal::guardar actualiza estado y registra parte de la valoración en activity_log.properties (condición, cognición, dependencia, signos y observación), sin insertar ficha/nota clínica. Otros campos capturados por ese modal no figuran en la escritura: no pueden recuperarse del formulario después del hecho. DecisionAdmisionModal::guardar intenta usar estado_anterior='DECISION_ADMISION' donde el esquema espera cod_est_adul y envía usuario_id en lugar de cambiado_por; requiere regresión transaccional. No considerar estas vías como valoración/decisión fiable y completa solo porque exista pantalla.

