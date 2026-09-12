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
            EnfermeriaSecurityPermissionsSeeder::class,
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

            // Cuenta inicial necesaria para acceder a una instalación vacía.
            // El personal, residentes y expedientes se registran desde el sistema.
            AdminSeeder::class,
        ]);
    }
}
