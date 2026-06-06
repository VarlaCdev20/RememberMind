<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Infraestructura base
            EstadoAdultoSeeder::class,
            RolesAndPermissionsSeeder::class,
            SaludSeguimientoPermissionsSeeder::class,
            FlujoClinicoPermissionsSeeder::class,

            // Datos institucionales
            AreasInstitucionalesSeeder::class,
            CargoAdministrativoSeeder::class,
            EspecialidadSeeder::class,
            TurnoInstitucionalSeeder::class,
            TipoDocumentoUsuarioSeeder::class,
            TipoAtencionAdultoSeeder::class,
            TipoActividadAdultoSeeder::class,
            TipoEvaluacionCognitivaSeeder::class,
            GeriatricSuiteSeeder::class,

            // Datos de ejemplo
            AdminSeeder::class,
            AdultoMayorSeeder::class,
            EnfermeriaPacienteSeeder::class,
        ]);
    }

}
