<?php

namespace Database\Seeders;

use App\Models\AdultoMayor;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Database\Seeder;

class PacienteMendozaSeeder extends Seeder
{
    // CI único para no colisionar con otros seeders
    private const CI_PACIENTE = '9988776-MND';

    public function run(): void
    {
        // 1. Buscar a la enfermera
        $enfermera = User::where('correo', 'enfermera.mendoza@casaamandita.com')->first();

        if (! $enfermera) {
            $this->command->error('[PacienteMendozaSeeder] No se encontró enfermera.mendoza@casaamandita.com.');
            $this->command->warn('  Ejecuta primero: php artisan db:seed --class=PersonalSeeder');
            return;
        }

        // 2. Estado clínico del adulto mayor
        $estadoCod = EstadoAdulto::whereIn('estado', ['EN_SEGUIMIENTO_ACTIVO', 'ACTIVO'])
            ->value('cod_est_adul')
            ?? EstadoAdulto::value('cod_est_adul');

        if (! $estadoCod) {
            $this->command->error('[PacienteMendozaSeeder] No existe ningún estado en la tabla estado_adulto.');
            return;
        }

        // 3. Crear o recuperar el paciente
        $paciente = AdultoMayor::firstWhere('ci', self::CI_PACIENTE);

        if (! $paciente) {
            $paciente = AdultoMayor::create([
                'nombres'            => 'FLORENCIA BEATRIZ',
                'ap_paterno'         => 'QUISPE',
                'ap_materno'         => 'GUTIERREZ',
                'ci'                 => self::CI_PACIENTE,
                'expedicion_ci'      => 'CB',
                'fecha_nac'          => '1940-03-15',
                'genero'             => 'FEMENINO',
                'estado_civil'       => 'VIUDA/O',
                'fecha_ing'          => now()->toDateString(),
                'hora_ing'           => '08:00',
                'tipo_ing'           => 'REGULAR',
                'permanencia'        => 'PERMANENTE',
                'cod_est_adul'       => $estadoCod,
                'grupo_sanguineo'    => 'A+',
                'factor_rh'          => '+',
                'alergias'           => 'NINGUNA CONOCIDA',
                'seguro_salud'       => 'SUS',
                'nivel_educat'       => 'PRIMARIA',
                'departamento_residencia' => 'COCHABAMBA',
                'ciudad_municipio'   => 'COCHABAMBA',
                'zona'               => 'Queru Queru',
                'calle'              => 'Av. Heroínas Nro. 123',
                'contacto_emergencia_nombre'      => 'LUIS QUISPE GUTIERREZ',
                'contacto_emergencia_parentesco'  => 'HIJO/A',
                'contacto_emergencia_celular'     => '70123456',
                'contacto_emergencia_direccion'   => 'Av. Heroínas Nro. 123, Cbba',
                'consentimiento_datos' => true,
                'motivo_ingreso'     => 'Requiere cuidados de enfermería. Movilidad reducida y control médico continuo.',
            ]);
            $this->command->line("  Paciente creado: {$paciente->nombres} {$paciente->ap_paterno} (CI: " . self::CI_PACIENTE . ')');
        } else {
            $this->command->line("  Paciente ya existe: {$paciente->nombres} {$paciente->ap_paterno}");
        }

        // 4. Habitación y cama dedicadas al demo
        $habitacion = Habitacion::firstWhere('codigo', 'HAB-D01');
        if (! $habitacion) {
            $habitacion = Habitacion::create([
                'codigo'           => 'HAB-D01',
                'nombre'           => 'Habitación D01',
                'tipo_habitacion'  => 'INDIVIDUAL',
                'capacidad'        => 1,
                'piso'             => 1,
                'descripcion'      => 'Habitación individual para paciente de enfermería',
                'estado'           => 'DISPONIBLE',
            ]);
        }

        $cama = Cama::firstWhere('codigo', 'CAM-D01-01');
        if (! $cama) {
            $cama = Cama::create([
                'codigo'          => 'CAM-D01-01',
                'cod_habitacion'  => $habitacion->cod_habitacion,
                'numero'          => 1,
                'estado'          => 'DISPONIBLE',
            ]);
        }

        // 5. Asignación de cama (el hook 'saved' actualiza adulto_mayor.cod_cama automáticamente)
        $asignacionCama = AsignacionAdultoMayor::where('cod_am', $paciente->cod_am)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $asignacionCama) {
            AsignacionAdultoMayor::create([
                'cod_am'           => $paciente->cod_am,
                'cod_habitacion'   => $habitacion->cod_habitacion,
                'cod_cama'         => $cama->cod_cama,
                'fecha_asignacion' => now()->toDateString(),
                'hora_asignacion'  => now()->toTimeString(),
                'estado'           => 'ACTIVO',
                'observaciones'    => 'Asignación automática — seeder demo',
                'registrado_por'   => $enfermera->cod_usu,
            ]);
            $this->command->line("  Cama asignada: {$cama->codigo} en {$habitacion->nombre}");
        }

        // 6. Asignación de turno para TODOS los turnos activos
        // (así la enfermera ve al paciente sin importar a qué hora entre al sistema)
        $turnos = TurnoEnfermeria::where('estado', 'ACTIVO')->get();

        if ($turnos->isEmpty()) {
            $this->command->warn('  No hay turnos activos. Ejecuta TurnosEnfermeriaSeeder primero.');
            return;
        }

        foreach ($turnos as $turno) {
            $asignacionExistente = AsignacionTurnoAdulto::where([
                'cod_am'             => $paciente->cod_am,
                'cod_turno'          => $turno->cod_turno,
                'cod_usu_enfermero'  => $enfermera->cod_usu,
            ])->first();

            if ($asignacionExistente) {
                $asignacionExistente->estado    = 'ACTIVA';
                $asignacionExistente->fecha_fin = null;
                $asignacionExistente->save();
            } else {
                AsignacionTurnoAdulto::create([
                    'cod_am'             => $paciente->cod_am,
                    'cod_turno'          => $turno->cod_turno,
                    'cod_usu_enfermero'  => $enfermera->cod_usu,
                    'cod_habitacion'     => $habitacion->cod_habitacion,
                    'cod_cama'           => $cama->cod_cama,
                    'fecha_inicio'       => now()->toDateString(),
                    'fecha_fin'          => null,
                    'nivel_supervision'  => 'ESTANDAR',
                    'estado'             => 'ACTIVA',
                    'motivo_asignacion'  => 'Asignación de demo — seeder',
                    'asignado_por'       => $enfermera->cod_usu,
                ]);
            }
        }

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║         PACIENTE ASIGNADO A ENFERMERA MENDOZA               ║');
        $this->command->info('╠══════════════════════════════════════════════════════════════╣');
        $this->command->info("║  Paciente  : FLORENCIA BEATRIZ QUISPE GUTIERREZ             ║");
        $this->command->info("║  CI        : " . self::CI_PACIENTE . '                                ║');
        $this->command->info("║  cod_am    : {$paciente->cod_am}");
        $this->command->info("║  Habitación: {$habitacion->nombre} ({$habitacion->codigo})");
        $this->command->info("║  Cama      : {$cama->codigo}");
        $this->command->info("║  Enfermera : enfermera.mendoza@casaamandita.com             ║");
        $this->command->info("║  cod_usu   : {$enfermera->cod_usu}");
        $this->command->info("║  Turnos    : " . $turnos->pluck('nombre')->join(', '));
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('  El paciente aparecerá en "Mis Pacientes" al iniciar sesión.');
    }
}
