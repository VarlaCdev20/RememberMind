<?php

namespace App\Modulos\Cuidados\Aplicacion\Consultas;

use App\Models\RegistroConductual;
use App\Models\Residente;
use App\Models\User;

final class ConsultarConducta
{
    public function ejecutar(User $usuario, string $residente, int $dias = 7, string $filtro = '', int $pagina = 1): array
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('registros_conductuales.ver'), 403);
        app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($residente, $usuario);
        abort_unless(in_array($dias, [7, 14, 30], true), 422);
        abort_unless($filtro === '' || in_array($filtro, ['apatia', 'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion'], true), 422);
        $base = RegistroConductual::where('cod_residente', $residente)->where('estado', 'VIGENTE');
        $inicio = now()->startOfDay()->subDays($dias - 1);
        $periodo = (clone $base)->whereBetween('fecha_hora', [$inicio, now()]);
        $campos = ['apatia' => 'Apatía', 'agitacion' => 'Agitación', 'agresividad' => 'Agresividad', 'ansiedad' => 'Ansiedad', 'aislamiento' => 'Aislamiento', 'deambulacion' => 'Deambulación', 'cambio_conducta' => 'Cambio de conducta'];
        $consultaSeries = (clone $periodo)->selectRaw('DATE(fecha_hora) as dia, COUNT(*) as registros');
        foreach ($campos as $campo => $label) {
            $consultaSeries->selectRaw("SUM(CASE WHEN {$campo} = true THEN 1 ELSE 0 END) as {$campo}");
        }
        $series = $consultaSeries->groupByRaw('DATE(fecha_hora)')->orderBy('dia')->get();
        $resumen = (clone $periodo)->selectRaw('COUNT(*) as total, SUM(CASE WHEN agitacion = true THEN 1 ELSE 0 END) as agitacion, SUM(CASE WHEN ansiedad = true THEN 1 ELSE 0 END) as ansiedad, SUM(CASE WHEN cambio_conducta = true THEN 1 ELSE 0 END) as cambios')->first();
        $historicos = (clone $base)->when($filtro !== '', fn ($query) => $query->where($filtro, true))
            ->with('personal:cod_personal,nombres,apellido_paterno,apellido_materno')
            ->orderByDesc('fecha_hora')->orderByDesc('cod_registro_conductual')->paginate(12, ['*'], 'conductaHistory', max(1, $pagina));
        $items = $historicos->map(fn (RegistroConductual $item) => $this->fila($item))->all();

        return [
            'residente' => Residente::findOrFail($residente),
            'series' => $series->map(fn ($row) => ['fecha' => (string) $row->dia, 'registros' => (int) $row->registros,
                'observadas' => collect($campos)->filter(fn ($label, $campo) => (int) $row->{$campo} > 0)->values()->all(),
            ])->all(),
            'resumen' => ['total' => (int) $resumen->total, 'agitacion' => (int) $resumen->agitacion, 'ansiedad' => (int) $resumen->ansiedad, 'cambios' => (int) $resumen->cambios],
            'historial' => $items, 'pagina' => $historicos->currentPage(), 'paginas' => $historicos->lastPage(), 'total' => $historicos->total(),
        ];
    }

    public function fila(RegistroConductual $registro): array
    {
        return [
            'codigo' => $registro->getKey(), 'fecha' => $registro->fecha_hora->format('d/m/Y'), 'hora' => $registro->fecha_hora->format('H:i'),
            'profesional' => trim(implode(' ', array_filter([$registro->personal?->nombres, $registro->personal?->apellido_paterno, $registro->personal?->apellido_materno]))) ?: 'Autor no disponible',
            'estado_animo' => $registro->estado_animo, 'participacion' => $registro->participacion,
            'apatia' => $registro->apatia, 'agitacion' => $registro->agitacion, 'agresividad' => $registro->agresividad,
            'ansiedad' => $registro->ansiedad, 'aislamiento' => $registro->aislamiento, 'deambulacion' => $registro->deambulacion,
            'cambio_conducta' => $registro->cambio_conducta, 'descripcion' => $registro->descripcion,
            'intervencion' => $registro->intervencion, 'respuesta' => $registro->respuesta,
        ];
    }
}
