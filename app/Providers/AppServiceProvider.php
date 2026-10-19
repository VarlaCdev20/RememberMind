<?php

namespace App\Providers;

use App\Models\Residente;
use App\Modulos\Admisiones\Presentacion\Livewire\Funcionalidades\DecisionAdmisionModal;
use App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionesPanel;
use App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionWizard;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\HistorialAlerta;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\SaludAlertasPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Funcionalidades\NotaEvolucionMedicaModal;
use App\Modulos\Clinica\Presentacion\Livewire\Funcionalidades\RegistroSignosVitalesModal;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludFichaPanel;
use App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludSignosPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\RolesPermisosPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuarioFichaPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\AreasInstitucionalesPanel;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\TurnosAsignacionesPanel;
use App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\AdministracionMedicacionModal;
use App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\MedicacionAdultoModal;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludAdministracionMedicacionPanel;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludMedicacionPanel;
use App\Modulos\Reportes\Presentacion\Livewire\Paginas\SaludReportesPanel;
use App\Modulos\Residentes\Presentacion\Livewire\Funcionalidades\AdultoMayorFormModal;
use App\Modulos\Residentes\Presentacion\Livewire\Paginas\AdultosMayoresPanel;
use App\Modulos\Residentes\Presentacion\Livewire\Paginas\ExpedientePanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaAreaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionBarthelModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionMedicaModal;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludEvaluacionesGeriatricasPanel;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludValoracionPanel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_ES.utf8', 'es_ES', 'es');

        Permission::created(function (Permission $permission): void {
            // La propietaria reservó esta ejecución a Medicina y Psicología, también al crear el permiso.
            if (in_array($permission->name, ['evaluaciones_expertas.ejecutar_observaciones', 'reglas_expertas.gestionar', 'reglas_expertas.revisar',
                'valoraciones_petersen.crear', 'valoraciones_petersen.rectificar'], true)) {
                return;
            }
            Role::query()
                ->where('name', 'SUPERADMINISTRADOR')
                ->where('guard_name', $permission->guard_name)
                ->first()
                ?->givePermissionTo($permission);
        });

        Gate::before(function ($user, string $ability) {
            // La lectura es global. La escritura clínica temporal se valida
            // en AccesoClinicoTemporalService y en los flujos de cada módulo.
            $esLectura = in_array($ability, ['view', 'viewAny'], true)
                || str_ends_with($ability, '.ver');

            return $user->hasRole('SUPERADMINISTRADOR') && $esLectura ? true : null;
        });

        Route::bind('adulto_mayor', function ($value) {
            return Residente::where('cod_residente', $value)->firstOrFail();
        });
        Route::bind('adulto', function ($value) {
            return Residente::where('cod_residente', $value)->firstOrFail();
        });
        Route::bind('residente', function ($value) {
            return Residente::where('cod_residente', $value)->firstOrFail();
        });

        Livewire::component('identidad.areas-institucionales-panel', AreasInstitucionalesPanel::class);
        Livewire::component('identidad.roles-permisos-panel', RolesPermisosPanel::class);
        Livewire::component('superadministrador.identidad.areas-institucionales-panel', AreasInstitucionalesPanel::class);
        Livewire::component('superadministrador.identidad.roles-permisos-panel', RolesPermisosPanel::class);
        Livewire::component('identidad.personal-institucional-panel', PersonalInstitucionalPanel::class);
        Livewire::component('identidad.personal-institucional-form', PersonalInstitucionalForm::class);
        Livewire::component('identidad.personal-institucional-horarios', PersonalInstitucionalHorarios::class);
        Livewire::component('identidad.turnos-asignaciones-panel', TurnosAsignacionesPanel::class);
        Livewire::component('identidad.usuario-ficha-panel', UsuarioFichaPanel::class);
        Livewire::component('identidad.usuarios-panel', UsuariosPanel::class);
        Livewire::component('administracion.identidad.personal-institucional-panel', PersonalInstitucionalPanel::class);
        Livewire::component('administracion.identidad.personal-institucional-form', PersonalInstitucionalForm::class);
        Livewire::component('administracion.identidad.personal-institucional-horarios', PersonalInstitucionalHorarios::class);
        Livewire::component('administracion.identidad.turnos-asignaciones-panel', TurnosAsignacionesPanel::class);
        Livewire::component('administracion.identidad.usuario-ficha-panel', UsuarioFichaPanel::class);
        Livewire::component('administracion.identidad.usuarios-panel', UsuariosPanel::class);
        Livewire::component('admisiones.decision-admision-modal', DecisionAdmisionModal::class);
        Livewire::component('admisiones.preadmisiones-panel', PreadmisionesPanel::class);
        Livewire::component('admisiones.preadmision-wizard', PreadmisionWizard::class);
        Livewire::component('reportes.salud-reportes-panel', SaludReportesPanel::class);
        Livewire::component('residentes.adulto-mayor-form-modal', AdultoMayorFormModal::class);
        Livewire::component('residentes.adultos-mayores-panel', AdultosMayoresPanel::class);
        Livewire::component('residentes.expediente-panel', ExpedientePanel::class);
        Livewire::component('valoraciones.valoracion-barthel-modal', ValoracionBarthelModal::class);
        Livewire::component('valoraciones.evaluacion-geriatrica-area-modal', EvaluacionGeriatricaAreaModal::class);
        Livewire::component('valoraciones.evaluacion-geriatrica-modal', EvaluacionGeriatricaModal::class);
        Livewire::component('valoraciones.valoracion-medica-modal', ValoracionMedicaModal::class);
        Livewire::component('valoraciones.salud-evaluaciones-geriatricas-panel', SaludEvaluacionesGeriatricasPanel::class);
        Livewire::component('valoraciones.salud-valoracion-panel', SaludValoracionPanel::class);
        Livewire::component('clinica.nota-evolucion-medica-modal', NotaEvolucionMedicaModal::class);
        Livewire::component('clinica.registro-signos-vitales-modal', RegistroSignosVitalesModal::class);
        Livewire::component('clinica.salud-ficha-panel', SaludFichaPanel::class);
        Livewire::component('clinica.salud-signos-panel', SaludSignosPanel::class);
        Livewire::component('alertas.campana-notificaciones', CampanaNotificaciones::class);
        Livewire::component('alertas.salud-alertas-panel', SaludAlertasPanel::class);
        Livewire::component('alertas.alertas-panel', AlertasPanel::class);
        Livewire::component('features.alertas.historial-alerta', HistorialAlerta::class);
        Livewire::component('medicacion.medicacion-adulto-modal', MedicacionAdultoModal::class);
        Livewire::component('medicacion.salud-medicacion-panel', SaludMedicacionPanel::class);
        Livewire::component('medicacion.administracion-medicacion-modal', AdministracionMedicacionModal::class);
        Livewire::component('medicacion.salud-administracion-medicacion-panel', SaludAdministracionMedicacionPanel::class);
    }
}
