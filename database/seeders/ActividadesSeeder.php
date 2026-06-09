<?php

namespace Database\Seeders;

use App\Models\ActividadAdulto;
use App\Models\AdultoMayor;
use App\Models\AtencionAdulto;
use App\Models\EstadoAdulto;
use App\Models\ObsAdulto;
use App\Models\TipoActividadAdulto;
use App\Models\TipoAtencionAdulto;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActividadesSeeder extends Seeder
{
    public function run(): void
    {
        $adultos = AdultoMayor::where('ci', 'like', '%-DEMO')->get();
        if ($adultos->isEmpty()) {
            $this->command->warn('[ActividadesSeeder] No se encontraron residentes DEMO. Ejecute ResidentesSeeder primero.');
            return;
        }

        $enfermero = User::whereHas('roles', fn($q) => $q->where('name', 'ENFERMEROS'))->first()
                  ?? User::first();
        $medico    = User::whereHas('roles', fn($q) => $q->whereIn('name', ['MEDICO GENERAL/GERIATRA']))->first()
                  ?? $enfermero;

        $tipoActOcupacional  = TipoActividadAdulto::where('nombre', 'Terapia Ocupacional')->first()
                            ?? TipoActividadAdulto::first();
        $tipoActCognitiva    = TipoActividadAdulto::where('nombre', 'Estimulación Cognitiva')->first()
                            ?? TipoActividadAdulto::first();
        $tipoActGimnasia     = TipoActividadAdulto::where('nombre', 'Gimnasia Adaptada')->first()
                            ?? TipoActividadAdulto::first();
        $tipoActMusicoterapia = TipoActividadAdulto::where('nombre', 'Musicoterapia')->first()
                            ?? TipoActividadAdulto::first();
        $tipoActIntegracion  = TipoActividadAdulto::where('nombre', 'Integración Social')->first()
                            ?? TipoActividadAdulto::first();
        $tipoActLudo         = TipoActividadAdulto::where('nombre', 'Ludoterapia')->first()
                            ?? TipoActividadAdulto::first();

        $tipoAtenMedica      = TipoAtencionAdulto::where('nombre', 'Médica General')->first()
                            ?? TipoAtencionAdulto::first();
        $tipoAtenEnf         = TipoAtencionAdulto::where('nombre', 'Enfermería')->first()
                            ?? TipoAtencionAdulto::first();
        $tipoAtenFisio       = TipoAtencionAdulto::where('nombre', 'Fisioterapia')->first()
                            ?? TipoAtencionAdulto::first();
        $tipoAtenPsico       = TipoAtencionAdulto::where('nombre', 'Psicología')->first()
                            ?? TipoAtencionAdulto::first();
        $tipoAtenNutr        = TipoAtencionAdulto::where('nombre', 'Nutrición')->first()
                            ?? TipoAtencionAdulto::first();

        $estadoActivo = EstadoAdulto::where('estado', 'ACTIVO')->first();

        // Actividades programadas (2 por adulto, diferentes días)
        $actividadesPorAdulto = [
            // Carmen — HTA, movilidad asistida
            [$tipoActOcupacional, $tipoActGimnasia],
            // Pedro — cognitivo
            [$tipoActCognitiva, $tipoActMusicoterapia],
            // Elsa — post-op
            [$tipoActGimnasia, $tipoActOcupacional],
            // Rafael — diabetes
            [$tipoActGimnasia, $tipoActIntegracion],
            // Josefina — vulnerable
            [$tipoActIntegracion, $tipoActLudo],
        ];

        $atencionesPrograma = [
            [$tipoAtenMedica, $tipoAtenEnf],
            [$tipoAtenMedica, $tipoAtenPsico],
            [$tipoAtenFisio, $tipoAtenEnf],
            [$tipoAtenMedica, $tipoAtenNutr],
            [$tipoAtenPsico, $tipoAtenEnf],
        ];

        foreach ($adultos as $idx => $adulto) {
            // --- ActividadAdulto ---
            $tiposAct = $actividadesPorAdulto[$idx % count($actividadesPorAdulto)];
            foreach ($tiposAct as $i => $tipoAct) {
                if (! $tipoAct) continue;
                $fecha = now()->subDays($i + 1)->toDateString();
                ActividadAdulto::firstOrCreate(
                    ['cod_am' => $adulto->cod_am, 'cod_tipo_act' => $tipoAct->cod_tipo_act, 'fecha' => $fecha],
                    [
                        'hora_inicio'  => '10:00:00',
                        'hora_fin'     => '11:00:00',
                        'estado'       => 'COMPLETADA',
                        'observacion'  => "Actividad realizada. Residente participó activamente.",
                        'registrado_por' => $enfermero->cod_usu,
                    ]
                );
            }

            // Actividad programada para mañana (PENDIENTE)
            $primerTipo = $tiposAct[0] ?? null;
            if ($primerTipo) {
                ActividadAdulto::firstOrCreate(
                    ['cod_am' => $adulto->cod_am, 'cod_tipo_act' => $primerTipo->cod_tipo_act, 'fecha' => now()->addDay()->toDateString()],
                    [
                        'hora_inicio'  => '10:00:00',
                        'hora_fin'     => '11:00:00',
                        'estado'       => 'PROGRAMADA',
                        'observacion'  => null,
                        'registrado_por' => $enfermero->cod_usu,
                    ]
                );
            }

            // --- AtencionAdulto ---
            $tiposAten = $atencionesPrograma[$idx % count($atencionesPrograma)];
            foreach ($tiposAten as $j => $tipoAten) {
                if (! $tipoAten) continue;
                $fecha = now()->subDays($j + 1)->toDateString();
                AtencionAdulto::firstOrCreate(
                    ['cod_am' => $adulto->cod_am, 'cod_tipo_aten' => $tipoAten->cod_tipo_aten, 'fecha' => $fecha],
                    [
                        'hora'         => '09:00:00',
                        'estado'       => 'REALIZADA',
                        'observacion'  => "Atención de {$tipoAten->nombre}. Residente estable. Sin novedades significativas.",
                        'registrado_por' => $j === 0 ? $medico?->cod_usu : $enfermero->cod_usu,
                    ]
                );
            }

            // --- ObsAdulto (2 observaciones por adulto) ---
            $obsData = [
                [
                    'tipo_obs'        => 'COMPORTAMIENTO',
                    'descripcion'     => "Residente {$adulto->nombres} muestra buen humor y colaboración durante las actividades diarias.",
                    'categoria'       => 'CONDUCTUAL',
                    'nivel_riesgo'    => 'BAJO',
                    'dias'            => 3,
                ],
                [
                    'tipo_obs'        => 'SALUD',
                    'descripcion'     => "Se observan signos vitales dentro de parámetros normales. Sin quejas de malestar.",
                    'categoria'       => 'CLINICA',
                    'nivel_riesgo'    => 'BAJO',
                    'dias'            => 1,
                ],
            ];

            foreach ($obsData as $obs) {
                ObsAdulto::firstOrCreate(
                    [
                        'cod_am'   => $adulto->cod_am,
                        'tipo_obs' => $obs['tipo_obs'],
                        'fecha'    => now()->subDays($obs['dias'])->toDateString(),
                    ],
                    [
                        'descripcion'     => $obs['descripcion'],
                        'observacion'     => $obs['descripcion'],
                        'categoria'       => $obs['categoria'],
                        'nivel_riesgo'    => $obs['nivel_riesgo'],
                        'nivel_importancia' => $obs['nivel_riesgo'],
                        'cod_est_adul'    => $estadoActivo?->cod_est_adul,
                        'registrado_por'  => $enfermero->cod_usu,
                        'creado_por'      => $enfermero->cod_usu,
                    ]
                );
            }
        }

        $this->command->info('[ActividadesSeeder] Actividades, atenciones y observaciones creadas para 5 residentes.');
    }
}
