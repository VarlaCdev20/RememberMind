<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // ─────────────────────────────────────────────
            // 1. Infraestructura base
            // ─────────────────────────────────────────────
            EstadoAdultoSeeder::class,
            EstadosAdultoFlujoClinicoSeeder::class,
            RolesAndPermissionsSeeder::class,
            SaludSeguimientoPermissionsSeeder::class,
            FlujoClinicoPermissionsSeeder::class,

            // ─────────────────────────────────────────────
            // 2. Catálogos y datos institucionales base
            // ─────────────────────────────────────────────
            TurnoInstitucionalSeeder::class,
            TurnosEnfermeriaSeeder::class,
            TipoDocumentoUsuarioSeeder::class,
            TipoAtencionAdultoSeeder::class,
            TipoActividadAdultoSeeder::class,
            GeriatricSuiteSeeder::class,

            // ─────────────────────────────────────────────
            // 3. Usuarios, roles operativos y personal
            // ─────────────────────────────────────────────
            AdminSeeder::class,
            PersonalSeeder::class,
            EnfermeroConHorarioSeeder::class,
            CarlaPatriciaEnfermeriaSeeder::class,

            // ─────────────────────────────────────────────
            // 4. Habitaciones, camas y residentes
            // ─────────────────────────────────────────────
            HabitacionesCamasSeeder::class,
            AdultoMayorSeeder::class,
            ResidentesSeeder::class,
            PacienteMendozaSeeder::class,

            // ─────────────────────────────────────────────
            // 5. Preadmisiones y flujo inicial
            // ─────────────────────────────────────────────
            PreadmisionesSeeder::class,
            AdultoConPreadmisionAprobadaSeeder::class,

            // ─────────────────────────────────────────────
            // 6. Datos clínicos, enfermería y seguimiento
            // ─────────────────────────────────────────────
            ClinicaSeeder::class,
            EnfermeriaPacienteSeeder::class,
            DatosEnfermeriaDemoSeeder::class,
            EnfermeriaOperativaSeeder::class,
            AlertasRealesSeeder::class,

            // ─────────────────────────────────────────────
            // 7. Actividades, voluntariado y demo integral
            // ─────────────────────────────────────────────
            ActividadesSeeder::class,
            VoluntariosSeeder::class,
            DemoIntegralSeeder::class,
        ]);
    }
}