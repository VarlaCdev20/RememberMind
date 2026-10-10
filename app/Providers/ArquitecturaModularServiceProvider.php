<?php

namespace App\Providers;

use App\Models\AdministracionMedicacion;
use App\Models\Cama;
use App\Models\EvaluacionExperta;
use App\Models\Habitacion;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\ValoracionEnfermeriaPreadmision;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Presentacion\Policies\EvaluacionExpertaPolicy;
use App\Modulos\ApoyoDecision\Presentacion\Policies\VersionModeloExpertoPolicy;
use App\Modulos\Institucion\Presentacion\Policies\CamaPolicy;
use App\Modulos\Institucion\Presentacion\Policies\HabitacionPolicy;
use App\Modulos\Medicacion\Presentacion\Policies\AdministracionMedicacionPolicy;
use App\Modulos\Medicacion\Presentacion\Policies\PrescripcionPolicy;
use App\Modulos\Residentes\Presentacion\Policies\ResidentePolicy;
use App\Modulos\Valoraciones\Presentacion\Policies\ValoracionEnfermeriaPreadmisionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ArquitecturaModularServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
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

    }
}
