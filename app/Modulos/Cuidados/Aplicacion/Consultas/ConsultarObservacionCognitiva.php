<?php

namespace App\Modulos\Cuidados\Aplicacion\Consultas;

use App\Models\ControlCognitivo;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Dominio\ObservacionCognitiva as Contrato;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ConsultarObservacionCognitiva
{
    public function ejecutar(User $usuario, string $residente, array $filtros = []): array
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('controles_cognitivos.ver') && ! $usuario->hasRole('FAMILIAR'), 403);
        app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($residente, $usuario);
        $filtros = [...['dias' => 30, 'desde' => null, 'hasta' => null, 'dominio' => '', 'orden' => 'desc', 'pagina' => 1], ...$filtros];
        $filtros = Validator::make($filtros, [
            'dias' => ['required', Rule::in([7, 30, 90, 0])], 'desde' => 'nullable|required_if:dias,0|date_format:Y-m-d|before_or_equal:today',
            'hasta' => 'nullable|required_if:dias,0|date_format:Y-m-d|after_or_equal:desde|before_or_equal:today',
            'dominio' => ['nullable', Rule::in(array_keys(Contrato::DOMINIOS))], 'orden' => ['required', Rule::in(['asc', 'desc'])], 'pagina' => 'required|integer|min:1|max:1000000',
        ])->validate();
        $inicio = (int) $filtros['dias'] === 0 ? Carbon::parse($filtros['desde'])->startOfDay() : now()->startOfDay()->subDays((int) $filtros['dias'] - 1);
        $fin = (int) $filtros['dias'] === 0 ? Carbon::parse($filtros['hasta'])->endOfDay()->min(now()) : now();
        $base = ControlCognitivo::where('cod_residente', $residente)->with('personal:cod_personal,nombres,apellido_paterno,apellido_materno');
        $periodo = (clone $base)->whereBetween('fecha_hora', [$inicio, $fin]);
        $historial = (clone $periodo)->when(filled($filtros['dominio'] ?? null), function ($q) use ($filtros) {
            $campos = array_keys(array_filter(Contrato::campos(), fn ($campo) => $campo[3] === $filtros['dominio']));
            $q->where(fn ($query) => collect($campos)->each(fn ($campo) => $query->orWhereNotNull($campo)));
        })->orderBy('fecha_hora', $filtros['orden'])->orderBy('cod_control_cognitivo', $filtros['orden'])->paginate(12, ['*'], 'cognicionHistory', $filtros['pagina']);
        $grafica = (clone $periodo)->where('estado', 'VIGENTE')->orderByDesc('fecha_hora')->orderByDesc('cod_control_cognitivo')->limit(200)->get()->reverse()->values();
        $ultimo = (clone $base)->where('estado', 'VIGENTE')->orderByDesc('fecha_hora')->orderByDesc('cod_control_cognitivo')->first();
        $resumenDominios = [];
        foreach (array_keys(Contrato::DOMINIOS) as $dominio) {
            $campos = array_keys(array_filter(Contrato::campos(), fn ($campo) => $campo[3] === $dominio));
            $ultimoDominio = $grafica->reverse()->first(fn ($registro) => collect($campos)->contains(fn ($campo) => $registro->{$campo} !== null));
            $resumenDominios[$dominio] = $ultimoDominio ? [
                'fecha' => $ultimoDominio->fecha_hora->format('d/m/Y H:i'),
                'resumen' => collect($campos)->filter(fn ($campo) => $ultimoDominio->{$campo} !== null)
                    ->map(fn ($campo) => Contrato::campos()[$campo][0].': '.Contrato::etiqueta($campo, $ultimoDominio->{$campo}))->implode(' · '),
            ] : null;
        }

        return ['residente' => Residente::with('ocupacionActiva.cama.habitacion')->findOrFail($residente),
            'historial' => $historial->map(fn ($r) => $this->fila($r))->all(), 'series' => $grafica->map(fn ($r) => $this->fila($r))->all(),
            'ultima' => $ultimo ? $this->fila($ultimo) : null, 'pagina' => $historial->currentPage(), 'paginas' => $historial->lastPage(),
            'total' => $historial->total(), 'hayHistoria' => (clone $base)->exists(), 'graficaLimitada' => (clone $periodo)->where('estado', 'VIGENTE')->count() > 200,
            'resumenDominios' => $resumenDominios];
    }

    public function fila(ControlCognitivo $registro): array
    {
        return ['codigo' => $registro->getKey(), 'fecha' => $registro->fecha_hora->format('d/m/Y'), 'hora' => $registro->fecha_hora->format('H:i'),
            'instante' => $registro->fecha_hora->toIso8601String(), 'estado' => $registro->estado,
            'profesional' => trim(implode(' ', array_filter([$registro->personal?->nombres, $registro->personal?->apellido_paterno, $registro->personal?->apellido_materno]))) ?: 'Autor no disponible',
            'respuestas' => collect(Contrato::campos())->mapWithKeys(fn ($def, $campo) => [$campo => $registro->{$campo}])->all(),
            'observacion' => $registro->observacion];
    }
}
