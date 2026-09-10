# Reorganización del código

Fecha: 2026-09-10. Rama: MEJORA-SYS.

## Alcance y resultado

Reorganización física y actualización de referencias, sin cambios intencionales de funcionalidad, reglas de negocio, permisos ni base de datos. Laravel MVC, Blade y Livewire permanecen. No se instalaron paquetes ni se hicieron commits o push.

Se movieron 280 archivos: 117 de backend, 149 vistas y 14 fuentes frontend. Archivos eliminados sin reemplazo: 0.

## Estructura final

```text
app/
  Models/                         # Identidades Eloquent existentes conservadas
  Http/Controllers/<funcionalidad>/
  Http/Requests/<funcionalidad>/
  Livewire/<funcionalidad>/
  Actions/Identidad/{Fortify,Jetstream}/
  Services/{Identidad,Residentes,Documentos,Reportes}/
resources/
  views/
    layouts/
    components/                   # Componentes comunes existentes
    livewire/<funcionalidad>/
    pages/                        # Páginas previamente bajo admin y raíz
    auth/, profile/, errors/, vendor/, api/
    emails/, pdf/, reportes/       # Renderizadores y contratos existentes
    admin/                        # Actividades/Voluntariado conservados
  frontend/
    styles/
      app.css
      tokens/
      base/
      components/
      layouts/
      modules/
    scripts/
      app.js
      components/
      modules/
      utilities/
    assets/fuentes.css
```

Las funcionalidades agrupan código existente: Identidad, Residentes, Admisiones, Clinica, Medicacion, Valoraciones, Cuidados, Alertas, Documentos y Reportes. No todas tienen código en todas las capas. No se crean Queries o Policies vacías ni servicios base, repositorios genéricos, interfaces o proveedores por módulo.

Los modelos conservan sus namespaces App\\Models: cambiar identidades Eloquent podría afectar referencias polimórficas persistidas (incluidos permisos y auditoría). Mantenerlas evita requerir cambios en BD o adaptadores de compatibilidad. Las relaciones, claves, casts y reglas de los modelos permanecen idénticos. Se mantienen también directorios convencionales del framework y vistas de PDF/correo. Algunos directorios anteriores quedan vacíos tras los movimientos: la revisión automática bloqueó su retirada y se conservaron.

## Cambios realizados

- Namespaces PSR-4 y referencias a clases actualizados en controladores, requests, Livewire, servicios, acciones, proveedores, rutas, vistas y tests.
- Imports del controlador base y dependencias antes resueltas por el mismo namespace incorporados donde corresponde.
- Alias de componentes Livewire y nombres de vistas actualizados; incluidas referencias dinámicas de documentos.
- Nombres, URI, métodos y middleware de las 253 rutas conservados frente al estado inicial; solo cambian las referencias a clases movidas.
- Imports sin uso retirados con Pint, preset vacío y únicamente la regla no_unused_imports, en 17 archivos. Sin formateo general ni cambios de lógica.
- Vite, Tailwind y directivas de entrada actualizados a resources/frontend.
- Tokens existentes de colores, tipografía, dimensiones, espaciados, bordes y sombras centralizados físicamente bajo styles/tokens. Se conserva la estrategia class de modo oscuro y los valores actuales.
- La hoja compartida de componentes se divide por responsabilidad en tarjetas, botones, estados, campos, tablas, modales/vacíos, navegación, métricas, efectos, alertas y estructura institucional, conservando orden y declaraciones.
- Estilos base separados y efectos ambientales extraídos de la entrada JavaScript. Fuentes reunidas en assets/fuentes.css; assets públicos mantienen sus URL.
- Se reutilizan componentes Blade existentes para botones, inputs, modales, tarjetas, estados, alertas y tablas de dashboard. No se añade una segunda biblioteca de componentes.
- 26 bloques CSS/JS estáticos o con datos simples extraídos a 25 archivos: dos scripts de preadmisión eran idénticos y ahora comparten el mismo archivo. Se conservaron orden de ejecución, globals y ubicación de las etiquetas.
- Seis bloques JavaScript que requieren directivas o variables Blade se separan como adaptadores .js.blade.php. No se convierten artificialmente en módulos puros.

## Contrato de frontend y preparación futura

styles/app.css y scripts/app.js son entradas Vite. Los tokens CSS, fuentes y módulos JavaScript independientes pueden reutilizarse en una futura pantalla Vue sin duplicar el diseño visual. No se introduce Vue, Inertia ni TypeScript.

Los estilos y scripts de pantalla extraídos se incluyen desde rutas locales fijas con resource_path, en el mismo lugar del documento original. Esto mantiene la cascada y el ciclo de vida actual; no implica que todos sean bundles Vite ni que todo JavaScript sea independiente de Blade. Los seis adaptadores .js.blade.php conservan el contexto de renderizado. Su contenido se incluye en el escaneo Tailwind.

Al migrar una pantalla en el futuro habrá que reemplazar sus adaptadores Blade por props/datos explícitos y gestionar montaje/desmontaje de listeners. Esta tarea no modifica ese comportamiento ni migra pantallas. Los estilos de PDF/correo y de Actividades/Voluntariado se conservan para no alterar esos renderizadores o retirar módulos parcialmente.

## Limpieza conservadora y pendientes

No se encontraron llamadas dd(), dump() o console.log() de depuración en app, resources y routes. No se borraron utilidades de raíz, componentes aparentemente huérfanos o archivos dudosos. Tres parciales vacíos de adultos mayores (actions, filters y form) se conservan: la falta de referencias literales no prueba que no exista selección dinámica.

Actividades y Voluntariado siguen conectados y quedan pendientes de retiro posterior integral. No se desarrollaron ni ampliaron. Persisten algunos parciales Livewire bajo directorios anteriores y convenciones de paquetes que no se fuerzan a otra estructura.

La inspección estática encontró seis referencias a vistas ya inexistentes en el respaldo inicial: admin.adultos-mayores.atenciones.index, admin.adultos-mayores.evaluaciones.index, admin.adultos-mayores.familiares.index, admin.adultos-mayores.observaciones.index, admin.usuarios.documentos.preview y livewire.admin.enfermeria.asignacion-turno-panel. No se fabrican pantallas para resolverlas, porque eso ampliaría funcionalidad. Los paneles extensos con lógica de negocio mantienen sus métodos; dividirlos requiere pruebas de caracterización específicas para no alterar flujos.

## Verificación

| Comprobación | Antes | Después |
|---|---|---|
| php artisan test | 37 passed, 8 skipped, 0 failed; 86 assertions | 37 passed, 8 skipped, 0 failed; 86 assertions |
| php artisan route:list | 253 rutas | 253 rutas; mismos contratos |
| npm run build | Correcto | Correcto |
| composer dump-autoload | No solicitado inicialmente | Correcto; también validación optimizada --strict-psr |
| Sintaxis PHP y JS separado | — | Sin errores |
| php artisan view:cache | — | Correcto |

Los tests se ejecutaron con SQLite :memory: y variables de entorno limitadas al proceso de prueba; no contra la BD de trabajo. Los ocho omitidos corresponden a funcionalidades Jetstream deshabilitadas. Compilar Blade no prueba cada interacción de usuario: no se afirma cobertura E2E de todos los módulos ni equivalencia visual comprobada pantalla por pantalla.

Se verificaron por SHA-256 los modelos, database/ y documentos anteriores: sin cambios. composer/package y sus lockfiles no se modifican. Las vistas movidas existen y las clases/trait movidos cargan con el autoloader.

El estado inicial ya contenía cambios en routes/web.php, tests/Feature/UsuariosRoutesPermissionsTest.php y docs/refactorizacion-total/. Se conservaron; el test de usuarios solo actualiza sus expectativas de nombres de vistas. Git presenta eliminaciones de rutas antiguas y archivos nuevos en rutas destino mientras no se agregan al índice: son movimientos, no pérdida de archivos. No se ejecutó git add.

## Inventario completo de movimientos

| Origen | Destino |
|---|---|
| `app/Http/Controllers/Admin/AdultoMayorController.php` | `app/Http/Controllers/Residentes/AdultoMayorController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorAtencionController.php` | `app/Http/Controllers/Residentes/AdultoMayorAtencionController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorDocumentoController.php` | `app/Http/Controllers/Documentos/AdultoMayorDocumentoController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorEvaluacionController.php` | `app/Http/Controllers/Valoraciones/AdultoMayorEvaluacionController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorFamiliarController.php` | `app/Http/Controllers/Residentes/AdultoMayorFamiliarController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/AdultoMayorObservacionController.php` | `app/Http/Controllers/Residentes/AdultoMayorObservacionController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorAdministracionMedicacionController.php` | `app/Http/Controllers/Medicacion/AdultoMayorAdministracionMedicacionController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorFichaMedicaController.php` | `app/Http/Controllers/Clinica/AdultoMayorFichaMedicaController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorMedicacionController.php` | `app/Http/Controllers/Medicacion/AdultoMayorMedicacionController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorSignosVitalesController.php` | `app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php` |
| `app/Http/Controllers/Admin/AdultosMayores/Salud/AdultoMayorValoracionFuncionalController.php` | `app/Http/Controllers/Valoraciones/AdultoMayorValoracionFuncionalController.php` |
| `app/Http/Controllers/Admin/AreasInstitucionales/AreaReporteController.php` | `app/Http/Controllers/Reportes/AreaReporteController.php` |
| `app/Http/Controllers/Admin/BitacoraController.php` | `app/Http/Controllers/Reportes/BitacoraController.php` |
| `app/Http/Controllers/Admin/Enfermeria/FichaPacienteReporteController.php` | `app/Http/Controllers/Reportes/FichaPacienteReporteController.php` |
| `app/Http/Controllers/Admin/FamiliaSocial/ResumenFamiliaSocialController.php` | `app/Http/Controllers/Residentes/ResumenFamiliaSocialController.php` |
| `app/Http/Controllers/Admin/PersonalDocumentosController.php` | `app/Http/Controllers/Documentos/PersonalDocumentosController.php` |
| `app/Http/Controllers/Admin/Reportes/ReporteAdultosController.php` | `app/Http/Controllers/Reportes/ReporteAdultosController.php` |
| `app/Http/Controllers/Admin/Reportes/ReporteBitacoraController.php` | `app/Http/Controllers/Reportes/ReporteBitacoraController.php` |
| `app/Http/Controllers/Admin/Reportes/ReporteEquipoController.php` | `app/Http/Controllers/Reportes/ReporteEquipoController.php` |
| `app/Http/Controllers/Admin/Reportes/ReporteFamiliaresController.php` | `app/Http/Controllers/Reportes/ReporteFamiliaresController.php` |
| `app/Http/Controllers/Admin/Reportes/ReporteInstitucionalController.php` | `app/Http/Controllers/Reportes/ReporteInstitucionalController.php` |
| `app/Http/Controllers/Admin/Reportes/ReporteSaludController.php` | `app/Http/Controllers/Reportes/ReporteSaludController.php` |
| `app/Http/Controllers/Admin/UsuarioController.php` | `app/Http/Controllers/Identidad/UsuarioController.php` |
| `app/Http/Controllers/Admin/Usuarios/DocumentosUsuarioController.php` | `app/Http/Controllers/Documentos/DocumentosUsuarioController.php` |
| `app/Http/Controllers/DashboardController.php` | `app/Http/Controllers/Reportes/DashboardController.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/StoreAdministracionMedicacionRequest.php` | `app/Http/Requests/Medicacion/StoreAdministracionMedicacionRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/StoreCambioEstadoRequest.php` | `app/Http/Requests/Clinica/StoreCambioEstadoRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/StoreFichaMedicaRequest.php` | `app/Http/Requests/Clinica/StoreFichaMedicaRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/StoreMedicacionRequest.php` | `app/Http/Requests/Medicacion/StoreMedicacionRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/StoreSignosVitalesRequest.php` | `app/Http/Requests/Clinica/StoreSignosVitalesRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/StoreValoracionFuncionalRequest.php` | `app/Http/Requests/Valoraciones/StoreValoracionFuncionalRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/UpdateFichaMedicaRequest.php` | `app/Http/Requests/Clinica/UpdateFichaMedicaRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/Salud/UpdateMedicacionRequest.php` | `app/Http/Requests/Medicacion/UpdateMedicacionRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/StoreActividadAdultoRequest.php` | `app/Http/Requests/Residentes/StoreActividadAdultoRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/StoreAtencionAdultoRequest.php` | `app/Http/Requests/Residentes/StoreAtencionAdultoRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/StoreDocumentoAdultoRequest.php` | `app/Http/Requests/Documentos/StoreDocumentoAdultoRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/StoreFamiliarAdultoRequest.php` | `app/Http/Requests/Residentes/StoreFamiliarAdultoRequest.php` |
| `app/Http/Requests/Admin/AdultosMayores/StoreObservacionAdultoRequest.php` | `app/Http/Requests/Residentes/StoreObservacionAdultoRequest.php` |
| `app/Http/Requests/Admin/StoreAdultoMayorRequest.php` | `app/Http/Requests/Residentes/StoreAdultoMayorRequest.php` |
| `app/Http/Requests/Admin/StoreUsuarioRequest.php` | `app/Http/Requests/Identidad/StoreUsuarioRequest.php` |
| `app/Http/Requests/Admin/UpdateAdultoMayorRequest.php` | `app/Http/Requests/Residentes/UpdateAdultoMayorRequest.php` |
| `app/Http/Requests/Admin/UpdateUsuarioRequest.php` | `app/Http/Requests/Identidad/UpdateUsuarioRequest.php` |
| `app/Livewire/Admin/Admisiones/PreadmisionesPanel.php` | `app/Livewire/Admisiones/PreadmisionesPanel.php` |
| `app/Livewire/Admin/Admisiones/PreadmisionWizard.php` | `app/Livewire/Admisiones/PreadmisionWizard.php` |
| `app/Livewire/Admin/AdultosMayores/AdultoMayorFormModal.php` | `app/Livewire/Residentes/AdultoMayorFormModal.php` |
| `app/Livewire/Admin/AdultosMayores/AdultosMayoresPanel.php` | `app/Livewire/Residentes/AdultosMayoresPanel.php` |
| `app/Livewire/Admin/AdultosMayores/AlertasPendientesPanel.php` | `app/Livewire/Alertas/AlertasPendientesPanel.php` |
| `app/Livewire/Admin/AdultosMayores/Evaluaciones/EvaluacionGeriatricaModal.php` | `app/Livewire/Valoraciones/EvaluacionGeriatricaModal.php` |
| `app/Livewire/Admin/AdultosMayores/Reportes/ReportesAdultoPanel.php` | `app/Livewire/Reportes/ReportesAdultoPanel.php` |
| `app/Livewire/Admin/AdultosMayores/ReportesInstitucionalesPanel.php` | `app/Livewire/Reportes/ReportesInstitucionalesPanel.php` |
| `app/Livewire/Admin/AdultosMayores/Salud/AdministracionMedicacionModal.php` | `app/Livewire/Medicacion/AdministracionMedicacionModal.php` |
| `app/Livewire/Admin/AdultosMayores/Salud/FichaMedicaAdultoModal.php` | `app/Livewire/Clinica/FichaMedicaAdultoModal.php` |
| `app/Livewire/Admin/AdultosMayores/Salud/HistorialEstadoAdultoPanel.php` | `app/Livewire/Clinica/HistorialEstadoAdultoPanel.php` |
| `app/Livewire/Admin/AdultosMayores/Salud/MedicacionAdultoModal.php` | `app/Livewire/Medicacion/MedicacionAdultoModal.php` |
| `app/Livewire/Admin/AdultosMayores/Salud/SignosVitalesAdultoModal.php` | `app/Livewire/Clinica/SignosVitalesAdultoModal.php` |
| `app/Livewire/Admin/AdultosMayores/Salud/ValoracionFuncionalAdultoModal.php` | `app/Livewire/Valoraciones/ValoracionFuncionalAdultoModal.php` |
| `app/Livewire/Admin/AreasInstitucionales/AreasInstitucionalesPanel.php` | `app/Livewire/Identidad/AreasInstitucionalesPanel.php` |
| `app/Livewire/Admin/Enfermeria/AlertasPanel.php` | `app/Livewire/Alertas/AlertasPanel.php` |
| `app/Livewire/Admin/Enfermeria/AsignacionTurnoPanel.php` | `app/Livewire/Cuidados/AsignacionTurnoPanel.php` |
| `app/Livewire/Admin/Enfermeria/DashboardTurno.php` | `app/Livewire/Cuidados/DashboardTurno.php` |
| `app/Livewire/Admin/Enfermeria/FichaPaciente.php` | `app/Livewire/Cuidados/FichaPaciente.php` |
| `app/Livewire/Admin/Enfermeria/HabitacionesPanel.php` | `app/Livewire/Admisiones/HabitacionesPanel.php` |
| `app/Livewire/Admin/Enfermeria/MisPacientes.php` | `app/Livewire/Cuidados/MisPacientes.php` |
| `app/Livewire/Admin/Enfermeria/PaseTurnoPanel.php` | `app/Livewire/Cuidados/PaseTurnoPanel.php` |
| `app/Livewire/Admin/Enfermeria/PlanCuidadoPanel.php` | `app/Livewire/Cuidados/PlanCuidadoPanel.php` |
| `app/Livewire/Admin/Enfermeria/SeguimientoDiarioPanel.php` | `app/Livewire/Cuidados/SeguimientoDiarioPanel.php` |
| `app/Livewire/Admin/Enfermeria/TareasPlanPanel.php` | `app/Livewire/Cuidados/TareasPlanPanel.php` |
| `app/Livewire/Admin/Enfermeria/TurnosEnfermeriaPanel.php` | `app/Livewire/Cuidados/TurnosEnfermeriaPanel.php` |
| `app/Livewire/Admin/Enfermeria/ValoracionEnfermeriaPanel.php` | `app/Livewire/Valoraciones/ValoracionEnfermeriaPanel.php` |
| `app/Livewire/Admin/Enfermeria/ValoracionInicialModal.php` | `app/Livewire/Valoraciones/ValoracionInicialModal.php` |
| `app/Livewire/Admin/Enfermeria/ValoracionMedicaPanel.php` | `app/Livewire/Valoraciones/ValoracionMedicaPanel.php` |
| `app/Livewire/Admin/FamiliaSocial/RedApoyoPanel.php` | `app/Livewire/Residentes/RedApoyoPanel.php` |
| `app/Livewire/Admin/Medico/DashboardMedico.php` | `app/Livewire/Clinica/DashboardMedico.php` |
| `app/Livewire/Admin/Medico/DecisionAdmisionModal.php` | `app/Livewire/Admisiones/DecisionAdmisionModal.php` |
| `app/Livewire/Admin/Medico/FichaClinicaIntegradaPanel.php` | `app/Livewire/Clinica/FichaClinicaIntegradaPanel.php` |
| `app/Livewire/Admin/Medico/NotaEvolucionMedicaModal.php` | `app/Livewire/Clinica/NotaEvolucionMedicaModal.php` |
| `app/Livewire/Admin/Medico/PacientesSeguimientoPanel.php` | `app/Livewire/Clinica/PacientesSeguimientoPanel.php` |
| `app/Livewire/Admin/Medico/RegistroSignosVitalesModal.php` | `app/Livewire/Clinica/RegistroSignosVitalesModal.php` |
| `app/Livewire/Admin/Medico/SignosVitalesPanel.php` | `app/Livewire/Clinica/SignosVitalesPanel.php` |
| `app/Livewire/Admin/Medico/ValoracionBarthelModal.php` | `app/Livewire/Valoraciones/ValoracionBarthelModal.php` |
| `app/Livewire/Admin/Medico/ValoracionMedicaModal.php` | `app/Livewire/Valoraciones/ValoracionMedicaModal.php` |
| `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalDocumentos.php` | `app/Livewire/Documentos/PersonalInstitucionalDocumentos.php` |
| `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalForm.php` | `app/Livewire/Identidad/PersonalInstitucionalForm.php` |
| `app/Livewire/Admin/PersonalInstitucional/Partials/PersonalInstitucionalHorarios.php` | `app/Livewire/Identidad/PersonalInstitucionalHorarios.php` |
| `app/Livewire/Admin/PersonalInstitucional/PersonalInstitucionalPanel.php` | `app/Livewire/Identidad/PersonalInstitucionalPanel.php` |
| `app/Livewire/Admin/PersonalInstitucional/TurnosAsignacionesPanel.php` | `app/Livewire/Identidad/TurnosAsignacionesPanel.php` |
| `app/Livewire/Admin/Psicologia/DashboardPsicologo.php` | `app/Livewire/Valoraciones/DashboardPsicologo.php` |
| `app/Livewire/Admin/Psicologia/EvaluacionesAreaPanel.php` | `app/Livewire/Valoraciones/EvaluacionesAreaPanel.php` |
| `app/Livewire/Admin/Psicologia/EvaluacionGeriatricaAreaModal.php` | `app/Livewire/Valoraciones/EvaluacionGeriatricaAreaModal.php` |
| `app/Livewire/Admin/RolesPermisos/RolesPermisosPanel.php` | `app/Livewire/Identidad/RolesPermisosPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludAdministracionMedicacionPanel.php` | `app/Livewire/Medicacion/SaludAdministracionMedicacionPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludAlertasPanel.php` | `app/Livewire/Alertas/SaludAlertasPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludEvaluacionesGeriatricasPanel.php` | `app/Livewire/Valoraciones/SaludEvaluacionesGeriatricasPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludFichaPanel.php` | `app/Livewire/Clinica/SaludFichaPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludMedicacionPanel.php` | `app/Livewire/Medicacion/SaludMedicacionPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludReportesPanel.php` | `app/Livewire/Reportes/SaludReportesPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludResumenPanel.php` | `app/Livewire/Clinica/SaludResumenPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludSeguimientoListPanel.php` | `app/Livewire/Clinica/SaludSeguimientoListPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludSignosPanel.php` | `app/Livewire/Clinica/SaludSignosPanel.php` |
| `app/Livewire/Admin/SaludSeguimiento/SaludValoracionPanel.php` | `app/Livewire/Valoraciones/SaludValoracionPanel.php` |
| `app/Livewire/Admin/Usuarios/UsuarioFichaPanel.php` | `app/Livewire/Identidad/UsuarioFichaPanel.php` |
| `app/Livewire/Admin/Usuarios/UsuarioFormModal.php` | `app/Livewire/Identidad/UsuarioFormModal.php` |
| `app/Livewire/Admin/Usuarios/UsuariosPanel.php` | `app/Livewire/Identidad/UsuariosPanel.php` |
| `app/Services/Admin/AdultoMayorBitacoraService.php` | `app/Services/Reportes/AdultoMayorBitacoraService.php` |
| `app/Services/Admin/AdultoMayorService.php` | `app/Services/Residentes/AdultoMayorService.php` |
| `app/Services/Dashboard/DashboardService.php` | `app/Services/Reportes/DashboardService.php` |
| `app/Services/Enfermeria/GeneradorPlanillaEnfermeriaService.php` | `app/Services/Identidad/GeneradorPlanillaEnfermeriaService.php` |
| `app/Services/SidebarService.php` | `app/Services/Identidad/SidebarService.php` |
| `app/Services/Usuarios/DocumentacionUsuarioService.php` | `app/Services/Documentos/DocumentacionUsuarioService.php` |
| `app/Services/Usuarios/DocumentosUsuarioService.php` | `app/Services/Documentos/DocumentosUsuarioService.php` |
| `app/Services/Usuarios/UsuarioFichaService.php` | `app/Services/Identidad/UsuarioFichaService.php` |
| `app/Actions/Fortify/CreateNewUser.php` | `app/Actions/Identidad/Fortify/CreateNewUser.php` |
| `app/Actions/Fortify/PasswordValidationRules.php` | `app/Actions/Identidad/Fortify/PasswordValidationRules.php` |
| `app/Actions/Fortify/ResetUserPassword.php` | `app/Actions/Identidad/Fortify/ResetUserPassword.php` |
| `app/Actions/Fortify/UpdateUserPassword.php` | `app/Actions/Identidad/Fortify/UpdateUserPassword.php` |
| `app/Actions/Fortify/UpdateUserProfileInformation.php` | `app/Actions/Identidad/Fortify/UpdateUserProfileInformation.php` |
| `app/Actions/Jetstream/DeleteUser.php` | `app/Actions/Identidad/Jetstream/DeleteUser.php` |
| `resources/views/livewire/admin/admisiones/preadmisiones-panel.blade.php` | `resources/views/livewire/admisiones/preadmisiones-panel.blade.php` |
| `resources/views/livewire/admin/admisiones/preadmision-wizard.blade.php` | `resources/views/livewire/admisiones/preadmision-wizard.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/adulto-mayor-form-modal.blade.php` | `resources/views/livewire/residentes/adulto-mayor-form-modal.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/adultos-mayores-panel.blade.php` | `resources/views/livewire/residentes/adultos-mayores-panel.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/alertas-pendientes-panel.blade.php` | `resources/views/livewire/alertas/alertas-pendientes-panel.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/evaluaciones/evaluacion-geriatrica-modal.blade.php` | `resources/views/livewire/valoraciones/evaluacion-geriatrica-modal.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/reportes/reportes-adulto-panel.blade.php` | `resources/views/livewire/reportes/reportes-adulto-panel.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/reportes-institucionales-panel.blade.php` | `resources/views/livewire/reportes/reportes-institucionales-panel.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/salud/administracion-medicacion-modal.blade.php` | `resources/views/livewire/medicacion/administracion-medicacion-modal.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/salud/ficha-medica-adulto-modal.blade.php` | `resources/views/livewire/clinica/ficha-medica-adulto-modal.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/salud/historial-estado-adulto-panel.blade.php` | `resources/views/livewire/clinica/historial-estado-adulto-panel.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/salud/medicacion-adulto-modal.blade.php` | `resources/views/livewire/medicacion/medicacion-adulto-modal.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/salud/signos-vitales-adulto-modal.blade.php` | `resources/views/livewire/clinica/signos-vitales-adulto-modal.blade.php` |
| `resources/views/livewire/admin/adultos-mayores/salud/valoracion-funcional-adulto-modal.blade.php` | `resources/views/livewire/valoraciones/valoracion-funcional-adulto-modal.blade.php` |
| `resources/views/livewire/admin/areas-institucionales/areas-institucionales-panel.blade.php` | `resources/views/livewire/identidad/areas-institucionales-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/alertas-panel.blade.php` | `resources/views/livewire/alertas/alertas-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/dashboard-turno.blade.php` | `resources/views/livewire/cuidados/dashboard-turno.blade.php` |
| `resources/views/livewire/admin/enfermeria/ficha-paciente.blade.php` | `resources/views/livewire/cuidados/ficha-paciente.blade.php` |
| `resources/views/livewire/admin/enfermeria/habitaciones-panel.blade.php` | `resources/views/livewire/admisiones/habitaciones-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/mis-pacientes.blade.php` | `resources/views/livewire/cuidados/mis-pacientes.blade.php` |
| `resources/views/livewire/admin/enfermeria/pase-turno-panel.blade.php` | `resources/views/livewire/cuidados/pase-turno-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/plan-cuidado-panel.blade.php` | `resources/views/livewire/cuidados/plan-cuidado-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/seguimiento-diario-panel.blade.php` | `resources/views/livewire/cuidados/seguimiento-diario-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/tareas-plan-panel.blade.php` | `resources/views/livewire/cuidados/tareas-plan-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/turnos-enfermeria-panel.blade.php` | `resources/views/livewire/cuidados/turnos-enfermeria-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/valoracion-enfermeria-panel.blade.php` | `resources/views/livewire/valoraciones/valoracion-enfermeria-panel.blade.php` |
| `resources/views/livewire/admin/enfermeria/valoracion-inicial-modal.blade.php` | `resources/views/livewire/valoraciones/valoracion-inicial-modal.blade.php` |
| `resources/views/livewire/admin/enfermeria/valoracion-medica-panel.blade.php` | `resources/views/livewire/valoraciones/valoracion-medica-panel.blade.php` |
| `resources/views/livewire/admin/familia-social/red-apoyo-panel.blade.php` | `resources/views/livewire/residentes/red-apoyo-panel.blade.php` |
| `resources/views/livewire/admin/medico/dashboard-medico.blade.php` | `resources/views/livewire/clinica/dashboard-medico.blade.php` |
| `resources/views/livewire/admin/medico/decision-admision-modal.blade.php` | `resources/views/livewire/admisiones/decision-admision-modal.blade.php` |
| `resources/views/livewire/admin/medico/ficha-clinica-integrada-panel.blade.php` | `resources/views/livewire/clinica/ficha-clinica-integrada-panel.blade.php` |
| `resources/views/livewire/admin/medico/nota-evolucion-medica-modal.blade.php` | `resources/views/livewire/clinica/nota-evolucion-medica-modal.blade.php` |
| `resources/views/livewire/admin/medico/pacientes-seguimiento-panel.blade.php` | `resources/views/livewire/clinica/pacientes-seguimiento-panel.blade.php` |
| `resources/views/livewire/admin/medico/registro-signos-vitales-modal.blade.php` | `resources/views/livewire/clinica/registro-signos-vitales-modal.blade.php` |
| `resources/views/livewire/admin/medico/signos-vitales-panel.blade.php` | `resources/views/livewire/clinica/signos-vitales-panel.blade.php` |
| `resources/views/livewire/admin/medico/valoracion-barthel-modal.blade.php` | `resources/views/livewire/valoraciones/valoracion-barthel-modal.blade.php` |
| `resources/views/livewire/admin/medico/valoracion-medica-modal.blade.php` | `resources/views/livewire/valoraciones/valoracion-medica-modal.blade.php` |
| `resources/views/livewire/admin/personal-institucional/partials/personal-institucional-documentos.blade.php` | `resources/views/livewire/documentos/personal-institucional-documentos.blade.php` |
| `resources/views/livewire/admin/personal-institucional/partials/personal-institucional-form.blade.php` | `resources/views/livewire/identidad/personal-institucional-form.blade.php` |
| `resources/views/livewire/admin/personal-institucional/partials/personal-institucional-horarios.blade.php` | `resources/views/livewire/identidad/personal-institucional-horarios.blade.php` |
| `resources/views/livewire/admin/personal-institucional/personal-institucional-panel.blade.php` | `resources/views/livewire/identidad/personal-institucional-panel.blade.php` |
| `resources/views/livewire/admin/personal-institucional/turnos-asignaciones-panel.blade.php` | `resources/views/livewire/identidad/turnos-asignaciones-panel.blade.php` |
| `resources/views/livewire/admin/psicologia/dashboard-psicologo.blade.php` | `resources/views/livewire/valoraciones/dashboard-psicologo.blade.php` |
| `resources/views/livewire/admin/psicologia/evaluaciones-area-panel.blade.php` | `resources/views/livewire/valoraciones/evaluaciones-area-panel.blade.php` |
| `resources/views/livewire/admin/psicologia/evaluacion-geriatrica-area-modal.blade.php` | `resources/views/livewire/valoraciones/evaluacion-geriatrica-area-modal.blade.php` |
| `resources/views/livewire/admin/roles-permisos/roles-permisos-panel.blade.php` | `resources/views/livewire/identidad/roles-permisos-panel.blade.php` |
| `resources/views/livewire/admin/salud-seguimiento/salud-alertas-panel.blade.php` | `resources/views/livewire/alertas/salud-alertas-panel.blade.php` |
| `resources/views/livewire/admin/salud-seguimiento/salud-evaluaciones-geriatricas-panel.blade.php` | `resources/views/livewire/valoraciones/salud-evaluaciones-geriatricas-panel.blade.php` |
| `resources/views/livewire/admin/salud-seguimiento/salud-ficha-panel.blade.php` | `resources/views/livewire/clinica/salud-ficha-panel.blade.php` |
| `resources/views/livewire/admin/salud-seguimiento/salud-reportes-panel.blade.php` | `resources/views/livewire/reportes/salud-reportes-panel.blade.php` |
| `resources/views/livewire/admin/salud-seguimiento/salud-resumen-panel.blade.php` | `resources/views/livewire/clinica/salud-resumen-panel.blade.php` |
| `resources/views/livewire/admin/salud-seguimiento/salud-seguimiento-list-panel.blade.php` | `resources/views/livewire/clinica/salud-seguimiento-list-panel.blade.php` |
| `resources/views/livewire/admin/salud-seguimiento/salud-signos-panel.blade.php` | `resources/views/livewire/clinica/salud-signos-panel.blade.php` |
| `resources/views/livewire/admin/usuarios/usuario-ficha-panel.blade.php` | `resources/views/livewire/identidad/usuario-ficha-panel.blade.php` |
| `resources/views/livewire/admin/usuarios/usuario-form-modal.blade.php` | `resources/views/livewire/identidad/usuario-form-modal.blade.php` |
| `resources/views/livewire/admin/usuarios/usuarios-panel.blade.php` | `resources/views/livewire/identidad/usuarios-panel.blade.php` |
| `resources/views/admin/admisiones/index.blade.php` | `resources/views/pages/admisiones/index.blade.php` |
| `resources/views/admin/adultos-mayores/alertas-pendientes-placeholder.blade.php` | `resources/views/pages/adultos-mayores/alertas-pendientes-placeholder.blade.php` |
| `resources/views/admin/adultos-mayores/create.blade.php` | `resources/views/pages/adultos-mayores/create.blade.php` |
| `resources/views/admin/adultos-mayores/documentos/index.blade.php` | `resources/views/pages/adultos-mayores/documentos/index.blade.php` |
| `resources/views/admin/adultos-mayores/edit.blade.php` | `resources/views/pages/adultos-mayores/edit.blade.php` |
| `resources/views/admin/adultos-mayores/index.blade.php` | `resources/views/pages/adultos-mayores/index.blade.php` |
| `resources/views/admin/adultos-mayores/partials/actions.blade.php` | `resources/views/pages/adultos-mayores/partials/actions.blade.php` |
| `resources/views/admin/adultos-mayores/partials/filters.blade.php` | `resources/views/pages/adultos-mayores/partials/filters.blade.php` |
| `resources/views/admin/adultos-mayores/partials/form.blade.php` | `resources/views/pages/adultos-mayores/partials/form.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/bienestar.blade.php` | `resources/views/pages/adultos-mayores/reportes/bienestar.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/general.blade.php` | `resources/views/pages/adultos-mayores/reportes/general.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/individual.blade.php` | `resources/views/pages/adultos-mayores/reportes/individual.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/institucional.blade.php` | `resources/views/pages/adultos-mayores/reportes/institucional.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/pdf_especifico.blade.php` | `resources/views/pages/adultos-mayores/reportes/pdf_especifico.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/pdf_evaluacion_individual.blade.php` | `resources/views/pages/adultos-mayores/reportes/pdf_evaluacion_individual.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/pdf_individual.blade.php` | `resources/views/pages/adultos-mayores/reportes/pdf_individual.blade.php` |
| `resources/views/admin/adultos-mayores/reportes/word_individual.blade.php` | `resources/views/pages/adultos-mayores/reportes/word_individual.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_cognitivo.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_cognitivo.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_documentos.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_documentos.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_historial.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_historial.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_identificacion.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_identificacion.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_participacion.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_participacion.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_red-apoyo.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_red-apoyo.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_reportes.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_reportes.blade.php` |
| `resources/views/admin/adultos-mayores/show/carpetas/_salud.blade.php` | `resources/views/pages/adultos-mayores/show/carpetas/_salud.blade.php` |
| `resources/views/admin/adultos-mayores/show/_actividades.blade.php` | `resources/views/pages/adultos-mayores/show/_actividades.blade.php` |
| `resources/views/admin/adultos-mayores/show/_atenciones.blade.php` | `resources/views/pages/adultos-mayores/show/_atenciones.blade.php` |
| `resources/views/admin/adultos-mayores/show/_cabecera-expediente.blade.php` | `resources/views/pages/adultos-mayores/show/_cabecera-expediente.blade.php` |
| `resources/views/admin/adultos-mayores/show/_documentos.blade.php` | `resources/views/pages/adultos-mayores/show/_documentos.blade.php` |
| `resources/views/admin/adultos-mayores/show/_evaluaciones-cognitivas.blade.php` | `resources/views/pages/adultos-mayores/show/_evaluaciones-cognitivas.blade.php` |
| `resources/views/admin/adultos-mayores/show/_familiares.blade.php` | `resources/views/pages/adultos-mayores/show/_familiares.blade.php` |
| `resources/views/admin/adultos-mayores/show/_historial-estados.blade.php` | `resources/views/pages/adultos-mayores/show/_historial-estados.blade.php` |
| `resources/views/admin/adultos-mayores/show/_modales-existentes.blade.php` | `resources/views/pages/adultos-mayores/show/_modales-existentes.blade.php` |
| `resources/views/admin/adultos-mayores/show/_reportes.blade.php` | `resources/views/pages/adultos-mayores/show/_reportes.blade.php` |
| `resources/views/admin/adultos-mayores/show/_resumen.blade.php` | `resources/views/pages/adultos-mayores/show/_resumen.blade.php` |
| `resources/views/admin/adultos-mayores/show/_salud-medica.blade.php` | `resources/views/pages/adultos-mayores/show/_salud-medica.blade.php` |
| `resources/views/admin/adultos-mayores/show/_seguimiento-observaciones.blade.php` | `resources/views/pages/adultos-mayores/show/_seguimiento-observaciones.blade.php` |
| `resources/views/admin/adultos-mayores/show.blade.php` | `resources/views/pages/adultos-mayores/show.blade.php` |
| `resources/views/admin/adultos-mayores/show.blade.php.bak` | `resources/views/pages/adultos-mayores/show.blade.php.bak` |
| `resources/views/admin/areas-institucionales/index.blade.php` | `resources/views/pages/areas-institucionales/index.blade.php` |
| `resources/views/admin/areas-institucionales/reportes/area-pdf.blade.php` | `resources/views/pages/areas-institucionales/reportes/area-pdf.blade.php` |
| `resources/views/admin/areas-institucionales/reportes/general-pdf.blade.php` | `resources/views/pages/areas-institucionales/reportes/general-pdf.blade.php` |
| `resources/views/admin/bitacora/index.blade.php` | `resources/views/pages/bitacora/index.blade.php` |
| `resources/views/admin/enfermeria/reportes/ficha-paciente-pdf.blade.php` | `resources/views/pages/enfermeria/reportes/ficha-paciente-pdf.blade.php` |
| `resources/views/admin/familia-social/base.blade.php` | `resources/views/pages/familia-social/base.blade.php` |
| `resources/views/admin/familia-social/resumen.blade.php` | `resources/views/pages/familia-social/resumen.blade.php` |
| `resources/views/admin/personal-institucional/pdfs/confidencialidad.blade.php` | `resources/views/pages/personal-institucional/pdfs/confidencialidad.blade.php` |
| `resources/views/admin/personal-institucional/pdfs/contrato.blade.php` | `resources/views/pages/personal-institucional/pdfs/contrato.blade.php` |
| `resources/views/admin/personal-institucional/pdfs/funciones.blade.php` | `resources/views/pages/personal-institucional/pdfs/funciones.blade.php` |
| `resources/views/admin/personal-institucional/pdfs/reglamento.blade.php` | `resources/views/pages/personal-institucional/pdfs/reglamento.blade.php` |
| `resources/views/admin/roles-permisos/index.blade.php` | `resources/views/pages/roles-permisos/index.blade.php` |
| `resources/views/admin/turnos-asignaciones/index.blade.php` | `resources/views/pages/turnos-asignaciones/index.blade.php` |
| `resources/views/admin/usuarios/create.blade.php` | `resources/views/pages/usuarios/create.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/autorizacion-administrativa.blade.php` | `resources/views/pages/usuarios/documentos/individuales/autorizacion-administrativa.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/autorizacion-comunicacion.blade.php` | `resources/views/pages/usuarios/documentos/individuales/autorizacion-comunicacion.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/carta-bienvenida.blade.php` | `resources/views/pages/usuarios/documentos/individuales/carta-bienvenida.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/checklist-institucional.blade.php` | `resources/views/pages/usuarios/documentos/individuales/checklist-institucional.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/checklist-requeridos.blade.php` | `resources/views/pages/usuarios/documentos/individuales/checklist-requeridos.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/compromiso-voluntario.blade.php` | `resources/views/pages/usuarios/documentos/individuales/compromiso-voluntario.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/confidencialidad-clinica.blade.php` | `resources/views/pages/usuarios/documentos/individuales/confidencialidad-clinica.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/confidencialidad.blade.php` | `resources/views/pages/usuarios/documentos/individuales/confidencialidad.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/constancia-familiar.blade.php` | `resources/views/pages/usuarios/documentos/individuales/constancia-familiar.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/constancia-registro.blade.php` | `resources/views/pages/usuarios/documentos/individuales/constancia-registro.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/protocolo-documental.blade.php` | `resources/views/pages/usuarios/documentos/individuales/protocolo-documental.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/protocolo-emergencias.blade.php` | `resources/views/pages/usuarios/documentos/individuales/protocolo-emergencias.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/protocolo-registro-clinico.blade.php` | `resources/views/pages/usuarios/documentos/individuales/protocolo-registro-clinico.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/reglamento-visitas.blade.php` | `resources/views/pages/usuarios/documentos/individuales/reglamento-visitas.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/reglamento-voluntariado.blade.php` | `resources/views/pages/usuarios/documentos/individuales/reglamento-voluntariado.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/responsabilidad-administrativa.blade.php` | `resources/views/pages/usuarios/documentos/individuales/responsabilidad-administrativa.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/responsabilidad-clinica.blade.php` | `resources/views/pages/usuarios/documentos/individuales/responsabilidad-clinica.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/responsabilidad-documental.blade.php` | `resources/views/pages/usuarios/documentos/individuales/responsabilidad-documental.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/trato-digno.blade.php` | `resources/views/pages/usuarios/documentos/individuales/trato-digno.blade.php` |
| `resources/views/admin/usuarios/documentos/individuales/uso-responsable.blade.php` | `resources/views/pages/usuarios/documentos/individuales/uso-responsable.blade.php` |
| `resources/views/admin/usuarios/documentos/layout.blade.php` | `resources/views/pages/usuarios/documentos/layout.blade.php` |
| `resources/views/admin/usuarios/documentos/paquete-documental.blade.php` | `resources/views/pages/usuarios/documentos/paquete-documental.blade.php` |
| `resources/views/admin/usuarios/documentos/paquete_documental.blade.php` | `resources/views/pages/usuarios/documentos/paquete_documental.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/acuerdo_corresponsabilidad.blade.php` | `resources/views/pages/usuarios/documentos/partials/acuerdo_corresponsabilidad.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/acuerdo_voluntariado.blade.php` | `resources/views/pages/usuarios/documentos/partials/acuerdo_voluntariado.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/caratula.blade.php` | `resources/views/pages/usuarios/documentos/partials/caratula.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/checklist_recepcion.blade.php` | `resources/views/pages/usuarios/documentos/partials/checklist_recepcion.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/compromiso_confidencialidad.blade.php` | `resources/views/pages/usuarios/documentos/partials/compromiso_confidencialidad.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/datos-usuario.blade.php` | `resources/views/pages/usuarios/documentos/partials/datos-usuario.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/datos_personales.blade.php` | `resources/views/pages/usuarios/documentos/partials/datos_personales.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/encabezado-documento.blade.php` | `resources/views/pages/usuarios/documentos/partials/encabezado-documento.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/firmas.blade.php` | `resources/views/pages/usuarios/documentos/partials/firmas.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/pie-documento.blade.php` | `resources/views/pages/usuarios/documentos/partials/pie-documento.blade.php` |
| `resources/views/admin/usuarios/documentos/partials/reglamento_interno.blade.php` | `resources/views/pages/usuarios/documentos/partials/reglamento_interno.blade.php` |
| `resources/views/admin/usuarios/edit.blade.php` | `resources/views/pages/usuarios/edit.blade.php` |
| `resources/views/admin/usuarios/index.blade.php` | `resources/views/pages/usuarios/index.blade.php` |
| `resources/views/admin/usuarios/show.blade.php` | `resources/views/pages/usuarios/show.blade.php` |
| `resources/views/welcome.blade.php` | `resources/views/pages/welcome.blade.php` |
| `resources/views/dashboard.blade.php` | `resources/views/pages/dashboard.blade.php` |
| `resources/css/app.css` | `resources/frontend/styles/app.css` |
| `resources/css/design-of-system/fuentes.css` | `resources/frontend/styles/tokens/tipografia.css` |
| `resources/css/design-of-system/paleta-colores.css` | `resources/frontend/styles/tokens/paleta-colores.css` |
| `resources/css/design-of-system/variables-semanticas.css` | `resources/frontend/styles/tokens/variables-semanticas.css` |
| `resources/css/design-of-system/escala-visual.css` | `resources/frontend/styles/tokens/escala-visual.css` |
| `resources/css/design-of-system/modo-oscuro.css` | `resources/frontend/styles/tokens/modo-oscuro.css` |
| `resources/css/design-of-system/texturas-tramas.css` | `resources/frontend/styles/base/texturas-tramas.css` |
| `resources/css/design-of-system/animaciones-transiciones.css` | `resources/frontend/styles/components/animaciones-transiciones.css` |
| `resources/css/design-of-system/componentes-ui.css` | `resources/frontend/styles/components/componentes-ui.css` |
| `resources/js/app.js` | `resources/frontend/scripts/app.js` |
| `resources/js/bootstrap.js` | `resources/frontend/scripts/utilities/bootstrap.js` |
| `resources/js/gama-de-colores/modo-oscuro.js` | `resources/frontend/scripts/utilities/modo-oscuro.js` |
| `resources/js/modules/red-apoyo-svg.js` | `resources/frontend/scripts/modules/red-apoyo-svg.js` |
| `resources/js/modules/red-apoyo-three.js` | `resources/frontend/scripts/modules/red-apoyo-three.js` |

## Extracciones de estilos y scripts de pantalla

| Vista de origen | Fuente extraída |
|---|---|
| `resources/views/auth/login.blade.php` | `resources/frontend/styles/modules/auth-login.css` |
| `resources/views/errors/403.blade.php` | `resources/frontend/scripts/modules/errors-403.js` |
| `resources/views/livewire/admin/salud-seguimiento/salud-valoracion-funcional.blade.php` | `resources/frontend/styles/modules/livewire-admin-salud-seguimiento-salud-valoracion-funcional.css` |
| `resources/views/livewire/admisiones/preadmision-wizard.blade.php` | `resources/frontend/scripts/modules/livewire-admisiones-preadmision-wizard.js` |
| `resources/views/livewire/admisiones/preadmisiones-panel.blade.php` | `resources/frontend/scripts/modules/livewire-admisiones-preadmision-wizard.js` |
| `resources/views/livewire/clinica/dashboard-medico.blade.php` | `resources/frontend/scripts/modules/livewire-clinica-dashboard-medico.js` |
| `resources/views/livewire/clinica/salud-resumen-panel.blade.php` | `resources/frontend/styles/modules/livewire-clinica-salud-resumen-panel.css` |
| `resources/views/livewire/clinica/salud-signos-panel.blade.php` | `resources/frontend/scripts/modules/livewire-clinica-salud-signos-panel.js` |
| `resources/views/livewire/clinica/signos-vitales-panel.blade.php` | `resources/frontend/scripts/modules/livewire-clinica-signos-vitales-panel.js` |
| `resources/views/livewire/cuidados/dashboard-turno.blade.php` | `resources/frontend/scripts/modules/livewire-cuidados-dashboard-turno.js` |
| `resources/views/livewire/identidad/areas-institucionales-panel.blade.php` | `resources/frontend/styles/modules/livewire-identidad-areas-institucionales-panel.css` |
| `resources/views/livewire/identidad/personal-institucional-form.blade.php` | `resources/frontend/scripts/modules/livewire-identidad-personal-institucional-form.js` |
| `resources/views/livewire/identidad/personal-institucional-horarios.blade.php` | `resources/frontend/scripts/modules/livewire-identidad-personal-institucional-horarios.js` |
| `resources/views/livewire/identidad/personal-institucional-panel.blade.php` | `resources/frontend/scripts/modules/livewire-identidad-personal-institucional-panel.js` |
| `resources/views/livewire/identidad/usuarios-panel.blade.php` | `resources/frontend/styles/modules/livewire-identidad-usuarios-panel.css` |
| `resources/views/livewire/identidad/usuarios-panel.blade.php` | `resources/frontend/scripts/modules/livewire-identidad-usuarios-panel-2.js` |
| `resources/views/livewire/residentes/adulto-mayor-form-modal.blade.php` | `resources/frontend/styles/modules/livewire-residentes-adulto-mayor-form-modal.css` |
| `resources/views/livewire/residentes/red-apoyo-panel.blade.php` | `resources/frontend/styles/modules/livewire-residentes-red-apoyo-panel.css` |
| `resources/views/livewire/valoraciones/valoracion-inicial-modal.blade.php` | `resources/frontend/styles/modules/livewire-valoraciones-valoracion-inicial-modal.css` |
| `resources/views/pages/dashboard.blade.php` | `resources/frontend/scripts/modules/pages-dashboard.js` |
| `resources/views/pages/familia-social/resumen.blade.php` | `resources/frontend/scripts/modules/pages-familia-social-resumen.js` |
| `resources/views/pages/usuarios/documentos/layout.blade.php` | `resources/frontend/styles/modules/pages-usuarios-documentos-layout.css` |
| `resources/views/pages/usuarios/documentos/paquete_documental.blade.php` | `resources/frontend/styles/modules/pages-usuarios-documentos-paquete_documental.css` |
| `resources/views/pages/welcome.blade.php` | `resources/frontend/styles/modules/pages-welcome.css` |
| `resources/views/pages/welcome.blade.php` | `resources/frontend/styles/modules/pages-welcome-2.css` |
| `resources/views/pages/welcome.blade.php` | `resources/frontend/scripts/modules/pages-welcome-3.js` |
| `resources/views/components/ui/sweetalert.blade.php` | `resources/frontend/scripts/modules/components-ui-sweetalert.js.blade.php` |
| `resources/views/livewire/cuidados/ficha-paciente.blade.php` | `resources/frontend/scripts/modules/livewire-cuidados-ficha-paciente.js.blade.php` |
| `resources/views/pages/adultos-mayores/create.blade.php` | `resources/frontend/scripts/modules/pages-adultos-mayores-create.js.blade.php` |
| `resources/views/pages/adultos-mayores/edit.blade.php` | `resources/frontend/scripts/modules/pages-adultos-mayores-edit.js.blade.php` |
| `resources/views/pages/adultos-mayores/show/carpetas/_reportes.blade.php` | `resources/frontend/scripts/modules/pages-adultos-mayores-show-carpetas-_reportes.js.blade.php` |
| `resources/views/pages/usuarios/create.blade.php` | `resources/frontend/scripts/modules/pages-usuarios-create.js.blade.php` |
