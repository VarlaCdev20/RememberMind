<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DemoIntegralSeeder — Llenado completo del sistema con datos de demostración.
 *
 * Ejecuta todos los seeders de datos demo en el orden correcto
 * respetando las dependencias de claves foráneas.
 *
 * Uso: php artisan db:seed --class=DemoIntegralSeeder
 *
 * NOTA: Requiere que DatabaseSeeder ya haya sido ejecutado (estados, roles,
 * turnos, tipos de atención/actividad, áreas geriatricas, admin).
 */
class DemoIntegralSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║        DEMO INTEGRAL SEEDER — CASA AMANDITA         ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->info('');

        $this->call([
            // 1. Personal institucional (usuarios, roles, horarios, documentos)
            PersonalSeeder::class,

            // 2. Infraestructura física (habitaciones y camas)
            HabitacionesCamasSeeder::class,

            // 3. Residentes (adultos mayores, familiares, asignaciones, historial)
            ResidentesSeeder::class,

            // 4. Voluntarios (voluntarios, disponibilidades, asignaciones)
            VoluntariosSeeder::class,

            // 5. Historia clínica (fichas, signos vitales, medicación, valoraciones)
            ClinicaSeeder::class,

            // 6. Enfermería operativa (planes, tareas, seguimientos, alertas, pases)
            EnfermeriaOperativaSeeder::class,

            // 7. Preadmisiones en distintos estados
            PreadmisionesSeeder::class,

            // 8. Actividades, atenciones y observaciones
            ActividadesSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════════╗');
        $this->command->info('║  Demo integral completado. Resumen de datos creados:            ║');
        $this->command->info('╠══════════════════════════════════════════════════════════════════╣');
        $this->command->info('║  • 7  usuarios de personal con roles y horarios                 ║');
        $this->command->info('║  • 5  habitaciones y 10 camas                                   ║');
        $this->command->info('║  • 5  adultos mayores residentes con:                           ║');
        $this->command->info('║       - 5 familiares responsables                               ║');
        $this->command->info('║       - 5 asignaciones de habitación/cama                       ║');
        $this->command->info('║       - 10 entradas en historial de estado                      ║');
        $this->command->info('║       - 10 documentos institucionales                           ║');
        $this->command->info('║  • 5  voluntarios con disponibilidades y asignaciones           ║');
        $this->command->info('║  • 5  fichas médicas con antecedentes                           ║');
        $this->command->info('║  • 15 registros de signos vitales (3 por residente)             ║');
        $this->command->info('║  • 10 prescripciones de medicación activa                       ║');
        $this->command->info('║  • 30 registros de administración de medicación                 ║');
        $this->command->info('║  • 5  valoraciones funcionales (Barthel)                       ║');
        $this->command->info('║  • 5  planes de cuidado activos                                 ║');
        $this->command->info('║  • 25 tareas en planes de cuidado                               ║');
        $this->command->info('║  • 15 seguimientos diarios                                      ║');
        $this->command->info('║  • 5  alertas clínicas con acciones                             ║');
        $this->command->info('║  • 5  asignaciones de turno de enfermería                       ║');
        $this->command->info('║  • 5  pases de turno                                            ║');
        $this->command->info('║  • 5  preadmisiones (distintos estados)                         ║');
        $this->command->info('║  • 15 actividades registradas                                   ║');
        $this->command->info('║  • 10 atenciones registradas                                    ║');
        $this->command->info('║  • 10 observaciones clínicas                                    ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->info('  CREDENCIALES DEL PERSONAL (contraseñas individuales):');
        $this->command->info('  ┌─────────────────────────────────────────────────────────────────────────┐');
        $this->command->info('  │ enfermera.mendoza@casaamandita.com    Enfermera#2025  [ENFERMEROS]       │');
        $this->command->info('  │ enfermera.choque@casaamandita.com     Enfermera#2025  [ENFERMEROS]       │');
        $this->command->info('  │ medico.salinas@casaamandita.com       Medico#2025     [MEDICO]           │');
        $this->command->info('  │ psicologa.villca@casaamandita.com     Psicologa#2025  [PSICOLOGO/A]      │');
        $this->command->info('  │ nutricionista.copa@casaamandita.com   Nutricion#2025  [NUTRICIONISTA]    │');
        $this->command->info('  │ fisio.torrez@casaamandita.com         Fisio#2025      [FISIOTERAPEUTA]   │');
        $this->command->info('  │ admin.baldelomar@casaamandita.com     Admin#2025      [ADMINISTRADOR]    │');
        $this->command->info('  └─────────────────────────────────────────────────────────────────────────┘');
        $this->command->info('');
    }
}
