<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\User;
use App\Models\ValoracionFuncional;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Valoraciones\Dominio\Reglas\IndiceBarthel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RegistrarValoracionBarthel
{
    public function ejecutar(User $usuario, array $datos): void
    {
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($datos['cod_residente'], 'valoracion_enfermeria.registrar', $usuario);
        $datos['totalBarthel'] = IndiceBarthel::total($datos);
        $datos['clasificacionBarthel'] = IndiceBarthel::clasificar($datos['totalBarthel']);
        $datos['nivel_dependencia'] = $datos['clasificacionBarthel'];
        DB::transaction(function () use ($usuario, $datos) {
            // Marcar anterior como histórica
            ValoracionFuncional::where('cod_residente', $datos['cod_residente'])
                ->vigente()
                ->update(['estado' => 'HISTORICA']);

            $personal = $usuario?->personal;
            $codArea = $personal?->asignaciones()
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->latest('fecha_asignacion')
                ->value('cod_area');
            abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

            $atencion = Atencion::query()->create([
                'cod_atencion' => 'ATN_'.Str::upper(Str::random(10)),
                'cod_residente' => $datos['cod_residente'],
                'cod_area' => $codArea,
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => 'VALORACION_BARTHEL',
                'motivo' => "Índice de Barthel {$datos['totalBarthel']}/100",
                'fecha_hora' => $datos['fecha_valoracion'].' '.now()->format('H:i:s'),
                'estado' => 'FINALIZADA',
                'observacion' => $datos['observacion'] ?: null,
            ]);

            ValoracionFuncional::create([
                'cod_valoracion_funcional' => 'VAF_'.Str::upper(Str::random(10)),
                'cod_residente' => $datos['cod_residente'],
                'cod_personal' => $personal->cod_personal,
                'cod_atencion' => $atencion->cod_atencion,
                'fecha_hora' => $datos['fecha_valoracion'].' '.now()->format('H:i:s'),
                'marcha' => $datos['deambulacion'] >= 15 ? 'INDEPENDIENTE' : 'ASISTIDA',
                'equilibrio' => ($datos['usa_baston'] || $datos['usa_andador']) ? 'CON_APOYO' : 'SIN_APOYO',
                'traslado' => $datos['usa_silla_ruedas'] ? 'SILLA_RUEDAS' : ($datos['traslados'] >= 15 ? 'INDEPENDIENTE' : 'ASISTIDO'),
                'alimentacion_autonoma' => $datos['alimentacion'] >= 10 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                'bano_autonomo' => $datos['bano'] >= 5 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                'vestido_autonomo' => $datos['vestido'] >= 10 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                'higiene_autonoma' => $datos['aseo_personal'] >= 5 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                'continencia' => ($datos['control_intestinal'] >= 10 && $datos['control_vesical'] >= 10) ? 'CONTINENTE' : 'REQUIERE_APOYO',
                'movilidad_autonoma' => $datos['deambulacion'] >= 15 ? 'INDEPENDIENTE' : 'REQUIERE_APOYO',
                'necesita_supervision' => $datos['necesita_supervision'],
                'nivel_dependencia' => $datos['nivel_dependencia'],
                'conclusion' => "Barthel {$datos['totalBarthel']}/100 ({$datos['clasificacionBarthel']}). Riesgo de caída: {$datos['riesgo_caida']}."
                    .($datos['observacion'] ? " {$datos['observacion']}" : ''),
                'estado' => 'ACTIVA',
            ]);
        });
    }
}
