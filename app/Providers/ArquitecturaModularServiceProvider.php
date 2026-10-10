<?php

namespace App\Providers;

use App\Compartido\Infraestructura\Vistas\VistaModularFinder;
use App\Models\AdministracionMedicacion;
use App\Models\Cama;
use App\Models\EvaluacionExperta;
use App\Models\Habitacion;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\ValoracionEnfermeriaPreadmision;
use App\Models\VersionModeloExperto;
use App\Modulos\Admisiones\Presentacion\Livewire\Funcionalidades\DecisionAdmisionModal;
use App\Modulos\Admisiones\Presentacion\Livewire\Paginas\AlojamientoResidente;
use App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionesPanel;
use App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionWizard;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\HistorialAlerta;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPendientesPanel;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\IncidentesPanel;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\PanelAlertas;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\SaludAlertasPanel;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ConocimientoPanel;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\ApoyoDecision\Presentacion\Policies\EvaluacionExpertaPolicy;
use App\Modulos\ApoyoDecision\Presentacion\Policies\VersionModeloExpertoPolicy;
use App\Modulos\Clinica\Presentacion\Livewire\Funcionalidades\NotaEvolucionMedicaModal;
use App\Modulos\Clinica\Presentacion\Livewire\Funcionalidades\RegistroSignosVitalesModal;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\DashboardMedico;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\FichaPaciente;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\ObjetivosSignosVitalesPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\PacientesSeguimientoPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludFichaPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludResumenPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludSeguimientoListPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludSignosPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SignosVitalesPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\AgendaEnfermeria;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\AsignacionTurnoPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\DashboardTurno;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\MisPacientes;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\PaseTurnoPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\PlanCuidadoPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\RegistrosEnfermeria;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\SeguimientoDiarioPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\TareasPlanPanel;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\TurnosEnfermeriaPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\AreasInstitucionalesPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\RolesPermisosPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuarioFichaPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\HabitacionesPanel;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\TurnosAsignacionesPanel;
use App\Modulos\Institucion\Presentacion\Policies\CamaPolicy;
use App\Modulos\Institucion\Presentacion\Policies\HabitacionPolicy;
use App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\AdministracionMedicacionModal;
use App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\MedicacionAdultoModal;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludAdministracionMedicacionPanel;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludMedicacionPanel;
use App\Modulos\Medicacion\Presentacion\Policies\AdministracionMedicacionPolicy;
use App\Modulos\Medicacion\Presentacion\Policies\PrescripcionPolicy;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReporteEnfermeria;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReportesActividadesPanel;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReportesAdultoPanel;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\ReportesInstitucionalesPanel;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\SaludReportesPanel;
use App\Modulos\Residentes\Presentacion\Livewire\Funcionalidades\AdultoMayorFormModal;
use App\Modulos\Residentes\Presentacion\Livewire\Paginas\AdultosMayoresPanel;
use App\Modulos\Residentes\Presentacion\Livewire\Paginas\ExpedientePanel;
use App\Modulos\Residentes\Presentacion\Policies\ResidentePolicy;
use App\Modulos\Social\Presentacion\Livewire\Paginas\ActividadesPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\AsistenciaPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\ParticipacionPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\RedApoyoPanel;
use App\Modulos\Social\Presentacion\Livewire\Paginas\TiposActividadPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaAreaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionBarthelModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionInicialModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionMedicaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\DashboardPsicologo;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\EvaluacionesAreaPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludEvaluacionesGeriatricasPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludValoracionPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\ValoracionEnfermeriaPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\ValoracionMedicaPanel;
use App\Modulos\Valoraciones\Presentacion\Policies\ValoracionEnfermeriaPreadmisionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

final class ArquitecturaModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->callAfterResolving('view', function ($view): void {
            $previous = $view->getFinder();
            $finder = new VistaModularFinder(
                $this->app['files'], $previous->getPaths(),
                $this->app['config']->get('vistas_modulares', []), $this->app->basePath(),
            );
            foreach ($previous->getHints() as $namespace => $paths) {
                $finder->addNamespace($namespace, $paths);
            }
            $view->setFinder($finder);
        });
    }

    public function boot(): void
    {
        foreach (glob($this->app->basePath('app/Modulos/*/Presentacion/Vistas'), GLOB_ONLYDIR) as $path) {
            $module = basename(dirname(dirname($path)));
            $this->loadViewsFrom($path, strtolower($module));
        }

        // Explicit mappings replace discovery from the former App\Policies directory.
        Gate::policy(AdministracionMedicacion::class, AdministracionMedicacionPolicy::class);
        Gate::policy(Cama::class, CamaPolicy::class);
        Gate::policy(EvaluacionExperta::class, EvaluacionExpertaPolicy::class);
        Gate::policy(Habitacion::class, HabitacionPolicy::class);
        Gate::policy(Prescripcion::class, PrescripcionPolicy::class);
        Gate::policy(Residente::class, ResidentePolicy::class);
        Gate::policy(ValoracionEnfermeriaPreadmision::class, ValoracionEnfermeriaPreadmisionPolicy::class);
        Gate::policy(VersionModeloExperto::class, VersionModeloExpertoPolicy::class);

        // Keep aliases used by existing snapshots and full-page components.
        Livewire::component('app.frontend.livewire.administracion.actividades.actividades-panel', ActividadesPanel::class);
        Livewire::component('app.frontend.livewire.administracion.actividades.asistencia-panel', AsistenciaPanel::class);
        Livewire::component('app.frontend.livewire.administracion.actividades.participacion-panel', ParticipacionPanel::class);
        Livewire::component('app.frontend.livewire.administracion.actividades.reportes-actividades-panel', ReportesActividadesPanel::class);
        Livewire::component('app.frontend.livewire.administracion.actividades.tipos-actividad-panel', TiposActividadPanel::class);
        Livewire::component('app.frontend.livewire.administracion.identidad.personal-institucional-form', PersonalInstitucionalForm::class);
        Livewire::component('app.frontend.livewire.administracion.identidad.personal-institucional-horarios', PersonalInstitucionalHorarios::class);
        Livewire::component('app.frontend.livewire.administracion.identidad.personal-institucional-panel', PersonalInstitucionalPanel::class);
        Livewire::component('app.frontend.livewire.administracion.identidad.turnos-asignaciones-panel', TurnosAsignacionesPanel::class);
        Livewire::component('app.frontend.livewire.administracion.identidad.usuario-ficha-panel', UsuarioFichaPanel::class);
        Livewire::component('app.frontend.livewire.administracion.identidad.usuarios-panel', UsuariosPanel::class);
        Livewire::component('app.frontend.livewire.admisiones.alojamiento-residente', AlojamientoResidente::class);
        Livewire::component('app.frontend.livewire.admisiones.decision-admision-modal', DecisionAdmisionModal::class);
        Livewire::component('app.frontend.livewire.admisiones.habitaciones-panel', HabitacionesPanel::class);
        Livewire::component('app.frontend.livewire.admisiones.preadmisiones-panel', PreadmisionesPanel::class);
        Livewire::component('app.frontend.livewire.admisiones.preadmision-wizard', PreadmisionWizard::class);
        Livewire::component('app.frontend.livewire.compartido.alertas.alertas-panel', AlertasPanel::class);
        Livewire::component('app.frontend.livewire.compartido.alertas.alertas-pendientes-panel', AlertasPendientesPanel::class);
        Livewire::component('app.frontend.livewire.compartido.alertas.campana-notificaciones', CampanaNotificaciones::class);
        Livewire::component('app.frontend.livewire.compartido.alertas.panel-alertas', PanelAlertas::class);
        Livewire::component('app.frontend.livewire.compartido.alertas.salud-alertas-panel', SaludAlertasPanel::class);
        Livewire::component('app.frontend.livewire.compartido.clinica.ficha-paciente', FichaPaciente::class);
        Livewire::component('app.frontend.livewire.compartido.clinica.registro-signos-vitales-modal', RegistroSignosVitalesModal::class);
        Livewire::component('app.frontend.livewire.compartido.clinica.salud-ficha-panel', SaludFichaPanel::class);
        Livewire::component('app.frontend.livewire.compartido.clinica.salud-resumen-panel', SaludResumenPanel::class);
        Livewire::component('app.frontend.livewire.compartido.clinica.salud-seguimiento-list-panel', SaludSeguimientoListPanel::class);
        Livewire::component('app.frontend.livewire.compartido.clinica.salud-signos-panel', SaludSignosPanel::class);
        Livewire::component('app.frontend.livewire.compartido.clinica.signos-vitales-panel', SignosVitalesPanel::class);
        Livewire::component('app.frontend.livewire.compartido.reportes.reportes-adulto-panel', ReportesAdultoPanel::class);
        Livewire::component('app.frontend.livewire.compartido.reportes.salud-reportes-panel', SaludReportesPanel::class);
        Livewire::component('app.frontend.livewire.compartido.residentes.adulto-mayor-form-modal', AdultoMayorFormModal::class);
        Livewire::component('app.frontend.livewire.compartido.residentes.adultos-mayores-panel', AdultosMayoresPanel::class);
        Livewire::component('app.frontend.livewire.compartido.residentes.expediente-panel', ExpedientePanel::class);
        Livewire::component('app.frontend.livewire.compartido.residentes.red-apoyo-panel', RedApoyoPanel::class);
        Livewire::component('app.frontend.livewire.compartido.valoraciones.evaluaciones-area-panel', EvaluacionesAreaPanel::class);
        Livewire::component('app.frontend.livewire.compartido.valoraciones.evaluacion-geriatrica-area-modal', EvaluacionGeriatricaAreaModal::class);
        Livewire::component('app.frontend.livewire.compartido.valoraciones.evaluacion-geriatrica-modal', EvaluacionGeriatricaModal::class);
        Livewire::component('app.frontend.livewire.compartido.valoraciones.salud-evaluaciones-geriatricas-panel', SaludEvaluacionesGeriatricasPanel::class);
        Livewire::component('app.frontend.livewire.compartido.valoraciones.salud-valoracion-panel', SaludValoracionPanel::class);
        Livewire::component('app.frontend.livewire.compartido.valoraciones.valoracion-barthel-modal', ValoracionBarthelModal::class);
        Livewire::component('app.frontend.livewire.compartido.valoraciones.valoracion-inicial-modal', ValoracionInicialModal::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.agenda-enfermeria', AgendaEnfermeria::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.asignacion-turno-panel', AsignacionTurnoPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.dashboard-turno', DashboardTurno::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.incidentes-panel', IncidentesPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.mis-pacientes', MisPacientes::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.pase-turno-panel', PaseTurnoPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.plan-cuidado-panel', PlanCuidadoPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.registros-enfermeria', RegistrosEnfermeria::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.reporte-enfermeria', ReporteEnfermeria::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.seguimiento-diario-panel', SeguimientoDiarioPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.tareas-plan-panel', TareasPlanPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.cuidados.turnos-enfermeria-panel', TurnosEnfermeriaPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.medicacion.administracion-medicacion-modal', AdministracionMedicacionModal::class);
        Livewire::component('app.frontend.livewire.enfermeria.medicacion.salud-administracion-medicacion-panel', SaludAdministracionMedicacionPanel::class);
        Livewire::component('app.frontend.livewire.enfermeria.valoraciones.valoracion-enfermeria-panel', ValoracionEnfermeriaPanel::class);
        Livewire::component('app.frontend.livewire.features.alertas.historial-alerta', HistorialAlerta::class);
        Livewire::component('app.frontend.livewire.medico.clinica.dashboard-medico', DashboardMedico::class);
        Livewire::component('app.frontend.livewire.medico.clinica.nota-evolucion-medica-modal', NotaEvolucionMedicaModal::class);
        Livewire::component('app.frontend.livewire.medico.clinica.objetivos-signos-vitales-panel', ObjetivosSignosVitalesPanel::class);
        Livewire::component('app.frontend.livewire.medico.clinica.pacientes-seguimiento-panel', PacientesSeguimientoPanel::class);
        Livewire::component('app.frontend.livewire.medico.clinica.resultados-experto-residente', ResultadosExpertoResidente::class);
        Livewire::component('app.frontend.livewire.medico.medicacion.medicacion-adulto-modal', MedicacionAdultoModal::class);
        Livewire::component('app.frontend.livewire.medico.medicacion.salud-medicacion-panel', SaludMedicacionPanel::class);
        Livewire::component('app.frontend.livewire.medico.valoraciones.valoracion-medica-modal', ValoracionMedicaModal::class);
        Livewire::component('app.frontend.livewire.medico.valoraciones.valoracion-medica-panel', ValoracionMedicaPanel::class);
        Livewire::component('app.frontend.livewire.psicologia.dashboard-psicologo', DashboardPsicologo::class);
        Livewire::component('app.frontend.livewire.superadministrador.identidad.areas-institucionales-panel', AreasInstitucionalesPanel::class);
        Livewire::component('app.frontend.livewire.superadministrador.identidad.roles-permisos-panel', RolesPermisosPanel::class);
        Livewire::component('app.frontend.livewire.superadministrador.reportes.reportes-institucionales-panel', ReportesInstitucionalesPanel::class);
        Livewire::component('app.frontend.livewire.superadministrador.sistema-experto.conocimiento-panel', ConocimientoPanel::class);
    }
}
