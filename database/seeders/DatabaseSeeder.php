<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Infraestructura base
            EstadoAdultoSeeder::class,
            EstadosAdultoFlujoClinicoSeeder::class,
            RolesAndPermissionsSeeder::class,
            SaludSeguimientoPermissionsSeeder::class,
            FlujoClinicoPermissionsSeeder::class,

            // Datos institucionales
            TurnoInstitucionalSeeder::class,
            TurnosEnfermeriaSeeder::class,
            TipoDocumentoUsuarioSeeder::class,
            TipoAtencionAdultoSeeder::class,
            TipoActividadAdultoSeeder::class,
            GeriatricSuiteSeeder::class,

            // Datos de ejemplo
            AdminSeeder::class,
            AdultoMayorSeeder::class,
            EnfermeriaPacienteSeeder::class,
        ]);
    }
}
