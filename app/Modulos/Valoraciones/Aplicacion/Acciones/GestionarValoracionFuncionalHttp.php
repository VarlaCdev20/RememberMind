<?php

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionFuncional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GestionarValoracionFuncionalHttp
{
    public function store(User $usuario, array $entrada, Residente $adulto_mayor): mixed
    {
        $data = $entrada;
        [$personal, $codArea] = $this->contextoPersonal($usuario);
        $valoracion = DB::transaction(function () use ($data, $adulto_mayor, $personal, $codArea): ValoracionFuncional {
            $atencion = Atencion::query()->create(['cod_atencion' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('atenciones'), 'cod_residente' => $adulto_mayor->cod_residente, 'cod_area' => $codArea, 'cod_personal' => $personal->cod_personal, 'tipo_atencion' => 'VALORACION_FUNCIONAL', 'motivo' => 'Valoración funcional integral', 'fecha_hora' => $data['fecha_valoracion'].' '.now()->format('H:i:s'), 'estado' => 'FINALIZADA', 'observacion' => $data['observacion'] ?? null]);

            return ValoracionFuncional::query()->create($this->payload($data, $adulto_mayor->cod_residente, $personal->cod_personal, $atencion->cod_atencion));
        });

        return $valoracion;
    }

    public function update(User $usuario, array $entrada, Residente $adulto_mayor, ValoracionFuncional $valoracion): mixed
    {
        abort_unless($valoracion->cod_residente === $adulto_mayor->cod_residente, 404);
        $valoracion->update($this->payload($entrada, $adulto_mayor->cod_residente, $valoracion->cod_personal, $valoracion->cod_atencion, $valoracion->cod_valoracion_funcional));

        return null;
    }

    private function payload(array $data, string $codResidente, string $codPersonal, string $codAtencion, ?string $id = null): array
    {
        $autonomia = fn (string $campo): string => ! empty($data[$campo]) ? 'INDEPENDIENTE' : 'REQUIERE_APOYO';

        return ['cod_valoracion_funcional' => app(\App\Compartido\Aplicacion\Contratos\CodigosEntidad::class)->siguiente('valoraciones_funcionales'), 'cod_residente' => $codResidente, 'cod_personal' => $codPersonal, 'cod_atencion' => $codAtencion, 'fecha_hora' => $data['fecha_valoracion'].' '.now()->format('H:i:s'), 'marcha' => ! empty($data['camina_solo']) ? 'INDEPENDIENTE' : 'ASISTIDA', 'equilibrio' => ! empty($data['usa_baston']) || ! empty($data['usa_andador']) ? 'CON_APOYO' : 'SIN_APOYO', 'traslado' => ! empty($data['usa_silla_ruedas']) ? 'SILLA_RUEDAS' : $autonomia('camina_solo'), 'alimentacion_autonoma' => $autonomia('come_solo'), 'bano_autonomo' => $autonomia('se_bana_solo'), 'vestido_autonomo' => $autonomia('se_viste_solo'), 'higiene_autonoma' => $autonomia('se_bana_solo'), 'continencia' => $autonomia('va_bano_solo'), 'movilidad_autonoma' => $autonomia('camina_solo'), 'necesita_supervision' => (bool) $data['necesita_supervision'], 'nivel_dependencia' => $data['nivel_dependencia'], 'conclusion' => $data['observacion'] ?? null, 'estado' => 'ACTIVA'];
    }

    private function contextoPersonal(User $usuario): array
    {
        $personal = $usuario?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional activa.');

        return [$personal, $codArea];
    }
}
