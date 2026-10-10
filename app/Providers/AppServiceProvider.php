<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Carbon\Carbon::setLocale('es');
        setlocale(LC_TIME, 'es_ES.utf8', 'es_ES', 'es');

        \Spatie\Permission\Models\Permission::created(function (\Spatie\Permission\Models\Permission $permission): void {
            \Spatie\Permission\Models\Role::query()
                ->where('name', 'SUPERADMINISTRADOR')
                ->where('guard_name', $permission->guard_name)
                ->first()
                ?->givePermissionTo($permission);
        });

        \Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
            // La lectura es global. La escritura clínica temporal se valida
            // en AccesoClinicoTemporalService y en los flujos de cada módulo.
            $esLectura = in_array($ability, ['view', 'viewAny'], true)
                || str_ends_with($ability, '.ver');

            return $user->hasRole('SUPERADMINISTRADOR') && $esLectura ? true : null;
        });

        \Illuminate\Support\Facades\Route::bind('adulto_mayor', function ($value) {
            return \App\Models\Residente::where('cod_residente', $value)->firstOrFail();
        });
        \Illuminate\Support\Facades\Route::bind('adulto', function ($value) {
            return \App\Models\Residente::where('cod_residente', $value)->firstOrFail();
        });
        \Illuminate\Support\Facades\Route::bind('residente', function ($value) {
            return \App\Models\Residente::where('cod_residente', $value)->firstOrFail();
        });

        \Livewire\Livewire::component('identidad.areas-institucionales-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\AreasInstitucionalesPanel::class);
        \Livewire\Livewire::component('identidad.roles-permisos-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\RolesPermisosPanel::class);
        \Livewire\Livewire::component('superadministrador.identidad.areas-institucionales-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\AreasInstitucionalesPanel::class);
        \Livewire\Livewire::component('superadministrador.identidad.roles-permisos-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\RolesPermisosPanel::class);
        \Livewire\Livewire::component('identidad.personal-institucional-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel::class);
        \Livewire\Livewire::component('identidad.personal-institucional-form', \App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm::class);
        \Livewire\Livewire::component('identidad.personal-institucional-horarios', \App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios::class);
        \Livewire\Livewire::component('identidad.turnos-asignaciones-panel', \App\Modulos\Institucion\Presentacion\Livewire\Paginas\TurnosAsignacionesPanel::class);
        \Livewire\Livewire::component('identidad.usuario-ficha-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuarioFichaPanel::class);
        \Livewire\Livewire::component('identidad.usuarios-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel::class);
        \Livewire\Livewire::component('administracion.identidad.personal-institucional-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\PersonalInstitucionalPanel::class);
        \Livewire\Livewire::component('administracion.identidad.personal-institucional-form', \App\Modulos\Identidad\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalForm::class);
        \Livewire\Livewire::component('administracion.identidad.personal-institucional-horarios', \App\Modulos\Institucion\Presentacion\Livewire\Funcionalidades\PersonalInstitucionalHorarios::class);
        \Livewire\Livewire::component('administracion.identidad.turnos-asignaciones-panel', \App\Modulos\Institucion\Presentacion\Livewire\Paginas\TurnosAsignacionesPanel::class);
        \Livewire\Livewire::component('administracion.identidad.usuario-ficha-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuarioFichaPanel::class);
        \Livewire\Livewire::component('administracion.identidad.usuarios-panel', \App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel::class);
        \Livewire\Livewire::component('admisiones.decision-admision-modal', \App\Modulos\Admisiones\Presentacion\Livewire\Funcionalidades\DecisionAdmisionModal::class);
        \Livewire\Livewire::component('admisiones.preadmisiones-panel', \App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionesPanel::class);
        \Livewire\Livewire::component('admisiones.preadmision-wizard', \App\Modulos\Admisiones\Presentacion\Livewire\Paginas\PreadmisionWizard::class);
        \Livewire\Livewire::component('reportes.salud-reportes-panel', \App\Modulos\Reportes\Presentacion\Livewire\Paginas\SaludReportesPanel::class);
        \Livewire\Livewire::component('residentes.adulto-mayor-form-modal', \App\Modulos\Residentes\Presentacion\Livewire\Funcionalidades\AdultoMayorFormModal::class);
        \Livewire\Livewire::component('residentes.adultos-mayores-panel', \App\Modulos\Residentes\Presentacion\Livewire\Paginas\AdultosMayoresPanel::class);
        \Livewire\Livewire::component('residentes.expediente-panel', \App\Modulos\Residentes\Presentacion\Livewire\Paginas\ExpedientePanel::class);
        \Livewire\Livewire::component('valoraciones.valoracion-barthel-modal', \App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionBarthelModal::class);
        \Livewire\Livewire::component('valoraciones.evaluacion-geriatrica-area-modal', \App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaAreaModal::class);
        \Livewire\Livewire::component('valoraciones.evaluacion-geriatrica-modal', \App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\EvaluacionGeriatricaModal::class);
        \Livewire\Livewire::component('valoraciones.valoracion-medica-modal', \App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionMedicaModal::class);
        \Livewire\Livewire::component('valoraciones.salud-evaluaciones-geriatricas-panel', \App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludEvaluacionesGeriatricasPanel::class);
        \Livewire\Livewire::component('valoraciones.salud-valoracion-panel', \App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\SaludValoracionPanel::class);
        \Livewire\Livewire::component('clinica.nota-evolucion-medica-modal', \App\Modulos\Clinica\Presentacion\Livewire\Funcionalidades\NotaEvolucionMedicaModal::class);
        \Livewire\Livewire::component('clinica.registro-signos-vitales-modal', \App\Modulos\Clinica\Presentacion\Livewire\Funcionalidades\RegistroSignosVitalesModal::class);
        \Livewire\Livewire::component('clinica.salud-ficha-panel', \App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludFichaPanel::class);
        \Livewire\Livewire::component('clinica.salud-signos-panel', \App\Modulos\Clinica\Presentacion\Livewire\Paginas\SaludSignosPanel::class);
        \Livewire\Livewire::component('alertas.campana-notificaciones', \App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones::class);
        \Livewire\Livewire::component('alertas.salud-alertas-panel', \App\Modulos\Alertas\Presentacion\Livewire\Paginas\SaludAlertasPanel::class);
        \Livewire\Livewire::component('alertas.alertas-panel', \App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel::class);
        \Livewire\Livewire::component('features.alertas.historial-alerta', \App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\HistorialAlerta::class);
        \Livewire\Livewire::component('medicacion.medicacion-adulto-modal', \App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\MedicacionAdultoModal::class);
        \Livewire\Livewire::component('medicacion.salud-medicacion-panel', \App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludMedicacionPanel::class);
        \Livewire\Livewire::component('medicacion.administracion-medicacion-modal', \App\Modulos\Medicacion\Presentacion\Livewire\Funcionalidades\AdministracionMedicacionModal::class);
        \Livewire\Livewire::component('medicacion.salud-administracion-medicacion-panel', \App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludAdministracionMedicacionPanel::class);
    }
}
