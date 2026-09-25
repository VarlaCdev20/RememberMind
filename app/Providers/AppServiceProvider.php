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

        \Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
            // SUPERADMINISTRADOR tiene lectura transversal, pero las acciones
            // clínicas de escritura siguen exigiendo el rol profesional competente.
            $esLectura = in_array($ability, ['view', 'viewAny'], true)
                || str_ends_with($ability, '.ver');

            return $user->hasRole('SUPERADMINISTRADOR') && $esLectura ? true : null;
        });

        \Illuminate\Support\Facades\Route::bind('adulto_mayor', function ($value) {
            return \App\Models\AdultoMayor::where('cod_residente', $value)->firstOrFail();
        });
        \Illuminate\Support\Facades\Route::bind('adulto', function ($value) {
            return \App\Models\AdultoMayor::where('cod_residente', $value)->firstOrFail();
        });
        \Illuminate\Support\Facades\Route::bind('residente', function ($value) {
            return \App\Models\Residente::where('cod_residente', $value)->firstOrFail();
        });

        \Livewire\Livewire::component('identidad.areas-institucionales-panel', \App\Frontend\Livewire\Superadministrador\Identidad\AreasInstitucionalesPanel::class);
        \Livewire\Livewire::component('identidad.roles-permisos-panel', \App\Frontend\Livewire\Superadministrador\Identidad\RolesPermisosPanel::class);
        \Livewire\Livewire::component('superadministrador.identidad.areas-institucionales-panel', \App\Frontend\Livewire\Superadministrador\Identidad\AreasInstitucionalesPanel::class);
        \Livewire\Livewire::component('superadministrador.identidad.roles-permisos-panel', \App\Frontend\Livewire\Superadministrador\Identidad\RolesPermisosPanel::class);
        \Livewire\Livewire::component('identidad.personal-institucional-panel', \App\Frontend\Livewire\Administracion\Identidad\PersonalInstitucionalPanel::class);
        \Livewire\Livewire::component('identidad.personal-institucional-form', \App\Frontend\Livewire\Administracion\Identidad\PersonalInstitucionalForm::class);
        \Livewire\Livewire::component('identidad.personal-institucional-horarios', \App\Frontend\Livewire\Administracion\Identidad\PersonalInstitucionalHorarios::class);
        \Livewire\Livewire::component('identidad.turnos-asignaciones-panel', \App\Frontend\Livewire\Administracion\Identidad\TurnosAsignacionesPanel::class);
        \Livewire\Livewire::component('identidad.usuario-ficha-panel', \App\Frontend\Livewire\Administracion\Identidad\UsuarioFichaPanel::class);
        \Livewire\Livewire::component('identidad.usuarios-panel', \App\Frontend\Livewire\Administracion\Identidad\UsuariosPanel::class);
        \Livewire\Livewire::component('administracion.identidad.personal-institucional-panel', \App\Frontend\Livewire\Administracion\Identidad\PersonalInstitucionalPanel::class);
        \Livewire\Livewire::component('administracion.identidad.personal-institucional-form', \App\Frontend\Livewire\Administracion\Identidad\PersonalInstitucionalForm::class);
        \Livewire\Livewire::component('administracion.identidad.personal-institucional-horarios', \App\Frontend\Livewire\Administracion\Identidad\PersonalInstitucionalHorarios::class);
        \Livewire\Livewire::component('administracion.identidad.turnos-asignaciones-panel', \App\Frontend\Livewire\Administracion\Identidad\TurnosAsignacionesPanel::class);
        \Livewire\Livewire::component('administracion.identidad.usuario-ficha-panel', \App\Frontend\Livewire\Administracion\Identidad\UsuarioFichaPanel::class);
        \Livewire\Livewire::component('administracion.identidad.usuarios-panel', \App\Frontend\Livewire\Administracion\Identidad\UsuariosPanel::class);
        \Livewire\Livewire::component('admisiones.decision-admision-modal', \App\Frontend\Livewire\Admisiones\DecisionAdmisionModal::class);
        \Livewire\Livewire::component('admisiones.preadmisiones-panel', \App\Frontend\Livewire\Admisiones\PreadmisionesPanel::class);
        \Livewire\Livewire::component('admisiones.preadmision-wizard', \App\Frontend\Livewire\Admisiones\PreadmisionWizard::class);
        \Livewire\Livewire::component('reportes.salud-reportes-panel', \App\Frontend\Livewire\Compartido\Reportes\SaludReportesPanel::class);
        \Livewire\Livewire::component('residentes.adulto-mayor-form-modal', \App\Frontend\Livewire\Compartido\Residentes\AdultoMayorFormModal::class);
        \Livewire\Livewire::component('residentes.adultos-mayores-panel', \App\Frontend\Livewire\Compartido\Residentes\AdultosMayoresPanel::class);
        \Livewire\Livewire::component('residentes.expediente-panel', \App\Frontend\Livewire\Compartido\Residentes\ExpedientePanel::class);
        \Livewire\Livewire::component('valoraciones.valoracion-barthel-modal', \App\Frontend\Livewire\Compartido\Valoraciones\ValoracionBarthelModal::class);
        \Livewire\Livewire::component('valoraciones.evaluacion-geriatrica-area-modal', \App\Frontend\Livewire\Compartido\Valoraciones\EvaluacionGeriatricaAreaModal::class);
        \Livewire\Livewire::component('valoraciones.evaluacion-geriatrica-modal', \App\Frontend\Livewire\Compartido\Valoraciones\EvaluacionGeriatricaModal::class);
        \Livewire\Livewire::component('valoraciones.valoracion-medica-modal', \App\Frontend\Livewire\Medico\Valoraciones\ValoracionMedicaModal::class);
        \Livewire\Livewire::component('valoraciones.salud-evaluaciones-geriatricas-panel', \App\Frontend\Livewire\Compartido\Valoraciones\SaludEvaluacionesGeriatricasPanel::class);
        \Livewire\Livewire::component('valoraciones.salud-valoracion-panel', \App\Frontend\Livewire\Compartido\Valoraciones\SaludValoracionPanel::class);
        \Livewire\Livewire::component('clinica.nota-evolucion-medica-modal', \App\Frontend\Livewire\Medico\Clinica\NotaEvolucionMedicaModal::class);
        \Livewire\Livewire::component('clinica.registro-signos-vitales-modal', \App\Frontend\Livewire\Compartido\Clinica\RegistroSignosVitalesModal::class);
        \Livewire\Livewire::component('clinica.salud-ficha-panel', \App\Frontend\Livewire\Compartido\Clinica\SaludFichaPanel::class);
        \Livewire\Livewire::component('clinica.salud-signos-panel', \App\Frontend\Livewire\Compartido\Clinica\SaludSignosPanel::class);
        \Livewire\Livewire::component('alertas.campana-notificaciones', \App\Frontend\Livewire\Compartido\Alertas\CampanaNotificaciones::class);
        \Livewire\Livewire::component('alertas.salud-alertas-panel', \App\Frontend\Livewire\Compartido\Alertas\SaludAlertasPanel::class);
        \Livewire\Livewire::component('alertas.alertas-panel', \App\Frontend\Livewire\Compartido\Alertas\AlertasPanel::class);
        \Livewire\Livewire::component('medicacion.medicacion-adulto-modal', \App\Frontend\Livewire\Medico\Medicacion\MedicacionAdultoModal::class);
        \Livewire\Livewire::component('medicacion.salud-medicacion-panel', \App\Frontend\Livewire\Medico\Medicacion\SaludMedicacionPanel::class);
        \Livewire\Livewire::component('medicacion.administracion-medicacion-modal', \App\Frontend\Livewire\Enfermeria\Medicacion\AdministracionMedicacionModal::class);
        \Livewire\Livewire::component('medicacion.salud-administracion-medicacion-panel', \App\Frontend\Livewire\Enfermeria\Medicacion\SaludAdministracionMedicacionPanel::class);
    }
}
