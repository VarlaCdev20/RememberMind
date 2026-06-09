<?php

namespace Database\Seeders;

use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\FichaMedicaAdulto;
use App\Models\MedicacionAdulto;
use App\Models\SignosVitalesAdulto;
use App\Models\User;
use App\Models\ValoracionFuncionalAdulto;
use Illuminate\Database\Seeder;

class ClinicaSeeder extends Seeder
{
    public function run(): void
    {
        $enfermero = User::whereHas('roles', fn($q) => $q->where('name', 'ENFERMEROS'))->first()
                  ?? User::first();
        $medico    = User::whereHas('roles', fn($q) => $q->whereIn('name', ['MEDICO GENERAL/GERIATRA']))->first()
                  ?? $enfermero;

        $adultos = AdultoMayor::where('ci', 'like', '%-DEMO')->get();
        if ($adultos->isEmpty()) {
            $this->command->warn('[ClinicaSeeder] No se encontraron residentes DEMO. Ejecute ResidentesSeeder primero.');
            return;
        }

        // Datos clínicos específicos por residente (índice = posición en colección adultos)
        $fichasData = [
            // Carmen Mamani — dependencia parcial, alergia penicilina
            [
                'hipertension'          => true,
                'diabetes'              => false,
                'problemas_cardiacos'   => false,
                'acv'                   => false,
                'parkinson'             => false,
                'epilepsia'             => false,
                'alzheimer_diagnosticado' => false,
                'depresion'             => true,
                'ansiedad'              => false,
                'problemas_sueno'       => true,
                'problemas_visuales'    => true,
                'problemas_auditivos'   => false,
                'dolor_cronico'         => false,
                'alergias'              => 'Penicilina',
                'restricciones_alimentarias' => 'Bajo contenido en sodio',
                'hospitalizaciones'     => 'Hospitalización por HTA en 2021 - Hospital de Clínicas La Paz',
                'observacion_medica'    => 'Control de presión arterial diario. Medicación antihipertensiva activa.',
            ],
            // Pedro Quispe — deterioro cognitivo leve
            [
                'hipertension'          => false,
                'diabetes'              => false,
                'problemas_cardiacos'   => true,
                'acv'                   => false,
                'parkinson'             => false,
                'epilepsia'             => false,
                'alzheimer_diagnosticado' => true,
                'depresion'             => false,
                'ansiedad'              => true,
                'problemas_sueno'       => true,
                'problemas_visuales'    => false,
                'problemas_auditivos'   => true,
                'dolor_cronico'         => false,
                'alergias'              => 'Ninguna conocida',
                'restricciones_alimentarias' => 'Sin restricciones especiales',
                'hospitalizaciones'     => 'Sin hospitalizaciones recientes',
                'observacion_medica'    => 'Seguimiento neurológico semestral. Control de evolución cognitiva.',
            ],
            // Elsa Choque — post-operatorio de cadera
            [
                'hipertension'          => false,
                'diabetes'              => false,
                'problemas_cardiacos'   => false,
                'acv'                   => false,
                'parkinson'             => false,
                'epilepsia'             => false,
                'alzheimer_diagnosticado' => false,
                'depresion'             => false,
                'ansiedad'              => true,
                'problemas_sueno'       => false,
                'problemas_visuales'    => true,
                'problemas_auditivos'   => false,
                'dolor_cronico'         => true,
                'alergias'              => 'Aspirina - AAS',
                'restricciones_alimentarias' => 'Alta proteína para recuperación ósea',
                'hospitalizaciones'     => 'Artroplastia total de cadera derecha 11/2025 - Hospital Viedma Cbba',
                'cirugias'              => 'Reemplazo total de cadera derecha',
                'observacion_medica'    => 'En proceso de rehabilitación post-operatoria. Fisioterapia diaria obligatoria.',
            ],
            // Rafael Torrico — diabetes
            [
                'hipertension'          => true,
                'diabetes'              => true,
                'problemas_cardiacos'   => false,
                'acv'                   => false,
                'parkinson'             => false,
                'epilepsia'             => false,
                'alzheimer_diagnosticado' => false,
                'depresion'             => false,
                'ansiedad'              => false,
                'problemas_sueno'       => false,
                'problemas_visuales'    => true,
                'problemas_auditivos'   => false,
                'dolor_cronico'         => true,
                'alergias'              => 'Sulfonamidas',
                'restricciones_alimentarias' => 'Dieta diabética hipocalórica. Sin azúcares simples.',
                'hospitalizaciones'     => 'Descompensación glucémica en 2023 - Hospital Santa Cruz',
                'observacion_medica'    => 'Control glucémico diario. Insulina programada. Revisión oftalmológica pendiente.',
            ],
            // Josefina Condori — vulnerable, sin antecedentes documentados
            [
                'hipertension'          => false,
                'diabetes'              => false,
                'problemas_cardiacos'   => false,
                'acv'                   => false,
                'parkinson'             => false,
                'epilepsia'             => false,
                'alzheimer_diagnosticado' => false,
                'depresion'             => true,
                'ansiedad'              => true,
                'problemas_sueno'       => true,
                'problemas_visuales'    => false,
                'problemas_auditivos'   => false,
                'dolor_cronico'         => false,
                'alergias'              => 'Ninguna conocida',
                'restricciones_alimentarias' => 'Sin restricciones conocidas',
                'hospitalizaciones'     => 'Sin historial documentado disponible',
                'observacion_medica'    => 'Evaluación psicológica inicial pendiente. Integración social prioritaria.',
            ],
        ];

        $medicamentosData = [
            [
                ['nombre' => 'Enalapril 10mg', 'dosis' => '10mg', 'frecuencia' => 'CADA 12 HORAS', 'via' => 'ORAL', 'motivo' => 'Control hipertensión arterial'],
                ['nombre' => 'Atorvastatina 20mg', 'dosis' => '20mg', 'frecuencia' => 'UNA VEZ AL DÍA (NOCHE)', 'via' => 'ORAL', 'motivo' => 'Control dislipidemia'],
            ],
            [
                ['nombre' => 'Donepezilo 5mg', 'dosis' => '5mg', 'frecuencia' => 'UNA VEZ AL DÍA (NOCHE)', 'via' => 'ORAL', 'motivo' => 'Manejo deterioro cognitivo leve'],
                ['nombre' => 'Memantina 10mg', 'dosis' => '10mg', 'frecuencia' => 'CADA 12 HORAS', 'via' => 'ORAL', 'motivo' => 'Tratamiento de Alzheimer leve'],
            ],
            [
                ['nombre' => 'Tramadol 50mg', 'dosis' => '50mg', 'frecuencia' => 'CADA 8 HORAS (por 30 días)', 'via' => 'ORAL', 'motivo' => 'Control dolor post-operatorio cadera'],
                ['nombre' => 'Calcio + Vitamina D3', 'dosis' => '600mg/400UI', 'frecuencia' => 'UNA VEZ AL DÍA', 'via' => 'ORAL', 'motivo' => 'Suplementación ósea post-quirúrgica'],
            ],
            [
                ['nombre' => 'Insulina Glargina 10UI', 'dosis' => '10 Unidades Internacionales', 'frecuencia' => 'UNA VEZ AL DÍA (NOCHE)', 'via' => 'SUBCUTÁNEA', 'motivo' => 'Control glucemia basal'],
                ['nombre' => 'Metformina 850mg', 'dosis' => '850mg', 'frecuencia' => 'CADA 8 HORAS', 'via' => 'ORAL', 'motivo' => 'Antidiabético oral complementario'],
            ],
            [
                ['nombre' => 'Sertralina 50mg', 'dosis' => '50mg', 'frecuencia' => 'UNA VEZ AL DÍA (MAÑANA)', 'via' => 'ORAL', 'motivo' => 'Tratamiento depresión y ansiedad'],
                ['nombre' => 'Polivitamínico + Hierro', 'dosis' => '1 tableta', 'frecuencia' => 'UNA VEZ AL DÍA', 'via' => 'ORAL', 'motivo' => 'Suplemento nutricional básico'],
            ],
        ];

        foreach ($adultos as $idx => $adulto) {
            // --- FichaMedicaAdulto ---
            if (FichaMedicaAdulto::where('cod_am', $adulto->cod_am)->doesntExist()) {
                FichaMedicaAdulto::create(array_merge(
                    $fichasData[$idx % count($fichasData)],
                    [
                        'cod_am'         => $adulto->cod_am,
                        'estado'         => 'ACTIVO',
                        'registrado_por' => $medico?->cod_usu,
                    ]
                ));
            }

            // --- SignosVitalesAdulto (3 registros por adulto: hace 10 días, hace 5 días, hoy) ---
            $registrosSignos = [
                ['dias' => 10, 'presion_s' => 130, 'presion_d' => 85, 'fc' => 72, 'fr' => 16, 'temp' => 36.5, 'sat' => 97, 'peso' => null],
                ['dias' => 5,  'presion_s' => 128, 'presion_d' => 82, 'fc' => 74, 'fr' => 17, 'temp' => 36.7, 'sat' => 96, 'peso' => null],
                ['dias' => 0,  'presion_s' => 125, 'presion_d' => 80, 'fc' => 70, 'fr' => 15, 'temp' => 36.4, 'sat' => 98, 'peso' => null],
            ];

            foreach ($registrosSignos as $signos) {
                $fecha = now()->subDays($signos['dias'])->toDateString();
                SignosVitalesAdulto::firstOrCreate(
                    ['cod_am' => $adulto->cod_am, 'fecha' => $fecha],
                    [
                        'hora'                 => '08:00:00',
                        'presion_sistolica'    => $signos['presion_s'] + ($idx * 2),
                        'presion_diastolica'   => $signos['presion_d'] + $idx,
                        'presion_arterial'     => ($signos['presion_s'] + $idx * 2) . '/' . ($signos['presion_d'] + $idx),
                        'frecuencia_cardiaca'  => $signos['fc'] + $idx,
                        'frecuencia_respiratoria' => $signos['fr'],
                        'temperatura'          => $signos['temp'],
                        'saturacion'           => $signos['sat'] - $idx,
                        'glucosa'              => ($idx === 3) ? (120 + $idx * 5) : null,
                        'estado'               => 'VIGENTE',
                        'registrado_por'       => $enfermero?->cod_usu,
                        'observacion'          => 'Registro de rutina — turno mañana.',
                    ]
                );
            }

            // --- MedicacionAdulto y AdministracionMedicacion ---
            $meds = $medicamentosData[$idx % count($medicamentosData)];
            foreach ($meds as $med) {
                $medicacion = MedicacionAdulto::firstOrCreate(
                    ['cod_am' => $adulto->cod_am, 'nombre_medicamento' => $med['nombre']],
                    [
                        'dosis'              => $med['dosis'],
                        'frecuencia'         => $med['frecuencia'],
                        'via_administracion' => $med['via'],
                        'fecha_inicio'       => now()->subDays(20)->toDateString(),
                        'estado'             => 'ACTIVA',
                        'observacion'        => $med['motivo'],
                        'registrado_por'     => $medico?->cod_usu,
                    ]
                );

                // 3 registros de administración
                foreach ([2, 1, 0] as $dAtras) {
                    AdministracionMedicacion::firstOrCreate(
                        ['cod_med_adulto' => $medicacion->cod_med_adulto, 'fecha' => now()->subDays($dAtras)->toDateString()],
                        [
                            'cod_am'         => $adulto->cod_am,
                            'hora_programada' => '08:00',
                            'hora_real'       => '08:05',
                            'administrado'    => true,
                            'efecto_observado' => 'Sin efectos adversos observados.',
                            'registrado_por'  => $enfermero?->cod_usu,
                        ]
                    );
                }
            }

            // --- ValoracionFuncionalAdulto ---
            if (ValoracionFuncionalAdulto::where('cod_am', $adulto->cod_am)->doesntExist()) {
                ValoracionFuncionalAdulto::create([
                    'cod_am'               => $adulto->cod_am,
                    'fecha_valoracion'     => now()->subDays(25)->toDateString(),
                    'come_solo'            => $idx < 3,
                    'se_bana_solo'         => $idx < 2,
                    'se_viste_solo'        => $idx < 3,
                    'va_bano_solo'         => $idx < 2,
                    'camina_solo'          => $idx !== 2,
                    'usa_baston'           => $idx === 0,
                    'usa_andador'          => $idx === 2,
                    'usa_silla_ruedas'     => false,
                    'baja_vision'          => in_array($idx, [0, 3]),
                    'baja_audicion'        => $idx === 1,
                    'dificultad_hablar'    => false,
                    'molestia_luz'         => false,
                    'molestia_ruido'       => $idx === 4,
                    'se_asusta_facil'      => $idx === 4,
                    'necesita_supervision' => $idx >= 2,
                    'nivel_dependencia'    => match ($idx) {
                        0, 4    => 'LEVE',
                        1       => 'MODERADA',
                        2       => 'SEVERA',
                        default => 'TOTAL',
                    },
                    'riesgo_caida'         => match ($idx) {
                        0, 1    => 'BAJO',
                        2, 3    => 'MEDIO',
                        default => 'ALTO',
                    },
                    'indice_barthel'       => match ($idx) {
                        0 => 80,
                        1 => 60,
                        2 => 40,
                        3 => 55,
                        4 => 70,
                    },
                    'estado'               => 'VIGENTE',
                    'registrado_por'       => $medico?->cod_usu,
                    'observacion'          => 'Valoración funcional inicial. Revisión programada en 3 meses.',
                ]);
            }
        }

        $this->command->info('[ClinicaSeeder] Fichas médicas, signos vitales, medicaciones, administraciones y valoraciones funcionales creadas para 5 residentes.');
    }
}
