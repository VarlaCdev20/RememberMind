<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionFuncional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GestionarValoracionFuncional
{
    public function guardar(User $usuario, array $datos): void
    {
        abort_unless($usuario->can($datos['editandoId'] ? 'valoraciones_funcionales.editar' : 'valoraciones_funcionales.crear'), 403);
        DB::transaction(function () use ($datos, $usuario): void {
            if ($datos['editandoId']) {
                $valoracion = $this->valoracion($datos['editandoId'], $datos['adulto']);
                $valoracion->update($this->payload($datos, $valoracion->cod_personal, $valoracion->cod_atencion, $valoracion->cod_valoracion_funcional));

                return;
            }

            [$personal, $codArea] = $this->contextoPersonal($usuario);
            ValoracionFuncional::query()
                ->where('cod_residente', $datos['adulto']->cod_residente)
                ->whereIn('estado', ['ACTIVA', 'VIGENTE'])
                ->update(['estado' => 'HISTORICA']);

            $atencion = Atencion::query()->create([
                'cod_atencion' => 'ATN_'.Str::upper(Str::random(10)),
                'cod_residente' => $datos['adulto']->cod_residente,
                'cod_area' => $codArea,
                'cod_personal' => $personal->cod_personal,
                'tipo_atencion' => 'VALORACION_FUNCIONAL',
                'motivo' => $datos['nivel_dependencia'],
                'fecha_hora' => $datos['fecha_valoracion'].' '.now()->format('H:i:s'),
                'estado' => 'FINALIZADA',
                'observacion' => $datos['observacion'] ?: null,
            ]);

            ValoracionFuncional::query()->create($this->payload($datos, $personal->cod_personal, $atencion->cod_atencion));
        });

    }

    public function anular(User $usuario, array $datos): void
    {
        abort_unless($usuario->can('valoraciones_funcionales.editar'), 403);
        $valoracion = $this->valoracion((string) $datos['anulandoId'], $datos['adulto']);
        $valoracion->update([
            'estado' => 'ANULADA',
            'conclusion' => trim((string) $valoracion->conclusion."\nAnulada: {$datos['motivo_anulacion']}"),
        ]);
    }

    public function marcarVigente(User $usuario, Residente $adulto, string $id): void
    {
        abort_unless($usuario->can('valoraciones_funcionales.editar'), 403);
        $datos = ['adulto' => $adulto];
        DB::transaction(function () use ($id, $datos): void {
            ValoracionFuncional::query()
                ->where('cod_residente', $datos['adulto']->cod_residente)
                ->whereIn('estado', ['ACTIVA', 'VIGENTE'])
                ->update(['estado' => 'HISTORICA']);
            $this->valoracion($id, $datos['adulto'])->update(['estado' => 'ACTIVA']);
        });
    }

    private function payload(array $datos, string $codPersonal, string $codAtencion, ?string $id = null): array
    {
        $autonomia = fn (bool $valor): string => $valor ? 'INDEPENDIENTE' : 'REQUIERE_APOYO';
        $apoyo = $datos['usa_silla_ruedas'] ? 'SILLA_RUEDAS' : ($datos['usa_andador'] ? 'ANDADOR' : ($datos['usa_baston'] ? 'BASTON' : 'SIN_APOYO'));

        return [
            'cod_valoracion_funcional' => $id ?? 'VAF_'.Str::upper(Str::random(10)),
            'cod_residente' => $datos['adulto']->cod_residente,
            'cod_personal' => $codPersonal,
            'cod_atencion' => $codAtencion,
            'fecha_hora' => $datos['fecha_valoracion'].' '.now()->format('H:i:s'),
            'marcha' => $datos['camina_solo'] ? 'INDEPENDIENTE' : 'ASISTIDA',
            'equilibrio' => $apoyo,
            'traslado' => $datos['usa_silla_ruedas'] ? 'SILLA_RUEDAS' : $autonomia($datos['camina_solo']),
            'alimentacion_autonoma' => $autonomia($datos['come_solo']),
            'bano_autonomo' => $autonomia($datos['se_bana_solo']),
            'vestido_autonomo' => $autonomia($datos['se_viste_solo']),
            'higiene_autonoma' => $autonomia($datos['se_bana_solo']),
            'continencia' => $autonomia($datos['va_bano_solo']),
            'movilidad_autonoma' => $autonomia($datos['camina_solo']),
            'necesita_supervision' => $datos['necesita_supervision'],
            'nivel_dependencia' => $datos['nivel_dependencia'],
            'conclusion' => trim(($datos['indice_barthel'] !== '' ? "Barthel {$datos['indice_barthel']}/100. " : '')
                ."Riesgo de caída: {$datos['riesgo_caida']}. ".$datos['observacion']),
            'estado' => 'ACTIVA',
        ];
    }

    private function contextoPersonal(User $usuario): array
    {
        $personal = $usuario?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

        return [$personal, $codArea];
    }

    private function valoracion(string $id, Residente $adulto): ValoracionFuncional
    {
        return ValoracionFuncional::query()->where('cod_residente', $adulto->cod_residente)->findOrFail($id);
    }
}
