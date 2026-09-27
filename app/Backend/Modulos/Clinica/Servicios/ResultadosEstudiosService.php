<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\DocumentoClinico;
use App\Models\EstudioClinico;
use App\Models\Residente;
use App\Models\ResultadoEstudio;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class ResultadosEstudiosService
{
    public function obtenerEstudios(Residente $residente): Collection
    {
        $items = collect();
        $estudios = EstudioClinico::query()->where('cod_residente', $residente->cod_residente)
            ->with(['tipo', 'personal', 'resultados.componente', 'informes.personal'])
            ->orderByDesc('fecha_realizacion')->orderByDesc('fecha_solicitud')->get();

        foreach ($estudios as $estudio) {
            $fecha = $estudio->fecha_realizacion ?? $estudio->fecha_solicitud;
            $informe = $estudio->informes->sortByDesc('fecha_hora')->first();
            if ($estudio->resultados->isEmpty()) {
                $items->push($this->mapearEstudio($estudio, null, $informe, $fecha));
            } else {
                foreach ($estudio->resultados as $resultado) {
                    $items->push($this->mapearEstudio($estudio, $resultado, $informe, $fecha));
                }
            }
        }

        $documentos = DocumentoClinico::query()->where('cod_residente', $residente->cod_residente)
            ->whereNull('cod_estudio')
            ->where(function ($query): void {
                $query->whereIn('tipo_documento', ['MEDICO', 'ESTUDIO', 'LABORATORIO', 'EXAMEN', 'IMAGEN', 'CARDIOLOGICO'])
                    ->orWhere('titulo', 'like', '%laboratorio%')->orWhere('titulo', 'like', '%estudio%')
                    ->orWhere('titulo', 'like', '%informe%');
            })->with('personal')->get();

        foreach ($documentos as $documento) {
            $fecha = Carbon::parse($documento->fecha_hora);
            $items->push([
                'id' => 'DOC_'.$documento->cod_documento_clinico,
                'cod_doc' => $documento->cod_documento_clinico,
                'fecha' => $fecha->format('d/m/Y'), 'fecha_raw' => $fecha->toIso8601String(), 'hora' => $fecha->format('H:i'),
                'titulo' => $documento->titulo, 'tipo_categoria' => $this->categoria($documento->tipo_documento),
                'tipo_texto' => 'Documento clínico', 'parametro_clave' => null,
                'resultado_valor' => 'Documento adjunto', 'resultado_unidad' => '', 'rango_referencia' => null,
                'estado' => strtoupper((string) $documento->estado), 'estado_badge' => $documento->estado ?: 'Registrado',
                'estado_color' => 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-700',
                'estado_dot' => 'bg-slate-500', 'estado_icono' => 'ph-bold ph-file-text',
                'solicitado_por' => 'No registrado', 'profesional_nombre' => $this->nombrePersonal($documento->personal),
                'profesional_rol' => $documento->personal?->profesion ?: 'No registrado', 'validado_por' => 'No registrado',
                'laboratorio_centro' => null, 'fecha_validacion' => null,
                'observaciones' => $documento->observacion ?: $documento->descripcion,
                'hallazgos' => null, 'conclusion' => null, 'tiene_documento' => filled($documento->ruta_archivo),
                'nombre_documento' => $documento->titulo.'.'.strtolower((string) $documento->formato),
                'ruta_documento' => $documento->ruta_archivo, 'es_real_db' => true, 'historico_valores' => [],
            ]);
        }

        return $items->sortByDesc('fecha_raw')->values();
    }

    public function obtenerMetricas(Collection $estudios): array
    {
        $total = $estudios->count();
        $normales = $estudios->whereIn('estado', ['NORMAL', 'DENTRO_RANGO'])->count();
        $fueraRango = $estudios->whereIn('estado', ['ALTO', 'BAJO', 'CRITICO', 'FUERA_RANGO'])->count();
        $seguimiento = $estudios->whereIn('estado', ['VIGILANCIA', 'PENDIENTE', 'ALTO', 'BAJO', 'CRITICO', 'FUERA_RANGO'])->count();
        $ultimo = $estudios->first();

        return [
            'total' => $total, 'normales' => $normales,
            'normales_pct' => $total > 0 ? round(($normales / $total) * 100) : 0,
            'fuera_rango' => $fueraRango, 'seguimiento' => $seguimiento,
            'ultimo_estudio' => $ultimo ? [
                'fecha' => $ultimo['fecha'], 'titulo' => $ultimo['titulo'], 'tipo' => $ultimo['tipo_texto'],
                'estado' => $ultimo['estado_badge'], 'estado_color' => $ultimo['estado_color'],
            ] : null,
        ];
    }

    public function obtenerDatosGrafico(Residente $residente, string $parametro, string $periodo = '6m'): array
    {
        $desde = match ($periodo) {
            '30d' => now()->subDays(30), '3m' => now()->subMonths(3), default => now()->subMonths(6)
        };
        $resultados = ResultadoEstudio::query()->whereNotNull('valor_numerico')
            ->whereHas('estudio', fn ($query) => $query->where('cod_residente', $residente->cod_residente)
                ->whereRaw('COALESCE(fecha_realizacion, fecha_solicitud) >= ?', [$desde]))
            ->whereHas('componente', fn ($query) => $query->whereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower($parametro).'%']))
            ->with(['estudio', 'componente'])->get()
            ->sortBy(fn ($resultado) => $resultado->estudio->fecha_realizacion ?? $resultado->estudio->fecha_solicitud)->values();
        $ultimo = $resultados->last();
        [$minimo, $maximo] = $this->extraerRango($ultimo?->rango_referencia);

        return [
            'parametro' => mb_strtolower($parametro),
            'nombre' => $ultimo?->componente?->nombre ?? Str::ucfirst($parametro),
            'unidad' => $ultimo?->unidad ?? $ultimo?->componente?->unidad_referencia ?? '',
            'rango_min' => $minimo, 'rango_max' => $maximo,
            'texto_rango' => $ultimo?->rango_referencia ?? 'No registrado',
            'labels' => $resultados->map(fn ($r) => Carbon::parse($r->estudio->fecha_realizacion ?? $r->estudio->fecha_solicitud)->format('d/m/y'))->all(),
            'data' => $resultados->map(fn ($r) => (float) $r->valor_numerico)->all(),
            'fechas' => $resultados->map(fn ($r) => Carbon::parse($r->estudio->fecha_realizacion ?? $r->estudio->fecha_solicitud)->format('d/m/Y'))->all(),
            'color' => '#1E3A8A', 'ultimo_valor' => $ultimo?->valor_numerico,
            'ultimo_periodo' => $ultimo ? Carbon::parse($ultimo->estudio->fecha_realizacion ?? $ultimo->estudio->fecha_solicitud)->format('d/m/Y') : null,
        ];
    }

    public function obtenerRangosReferencia(Residente $residente, string $parametro): array
    {
        $ultimo = ResultadoEstudio::query()
            ->whereHas('estudio', fn ($query) => $query->where('cod_residente', $residente->cod_residente))
            ->whereHas('componente', fn ($query) => $query->whereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower($parametro).'%']))
            ->with('componente')->whereNotNull('rango_referencia')->latest('cod_resultado_estudio')->first();

        return [
            'nombre' => $ultimo?->componente?->nombre ?? Str::ucfirst($parametro),
            'unidad' => $ultimo?->unidad ?? $ultimo?->componente?->unidad_referencia ?? '',
            'normal_min' => null, 'normal_max' => null, 'texto_rango' => $ultimo?->rango_referencia ?? 'No registrado',
            'clasificaciones' => $ultimo ? [[
                'etiqueta' => 'Rango informado', 'rango' => $ultimo->rango_referencia,
                'color' => 'text-slate-700 dark:text-slate-300', 'bg' => 'bg-slate-50 dark:bg-slate-900/40',
            ]] : [],
            'guia_clinica' => 'Los rangos se muestran únicamente cuando fueron registrados con el resultado del estudio.',
        ];
    }

    private function mapearEstudio($estudio, $resultado, $informe, $fecha): array
    {
        $fecha = Carbon::parse($fecha);
        $clasificacion = strtoupper((string) ($resultado?->clasificacion ?: $estudio->estado));
        $documento = DocumentoClinico::query()->where('cod_estudio', $estudio->cod_estudio)->first();
        $validador = $informe?->personal;

        return [
            'id' => $resultado ? 'RES_'.$resultado->cod_resultado_estudio : 'EST_'.$estudio->cod_estudio,
            'cod_doc' => $documento?->cod_documento_clinico, 'fecha' => $fecha->format('d/m/Y'),
            'fecha_raw' => $fecha->toIso8601String(), 'hora' => $fecha->format('H:i'),
            'titulo' => $resultado?->componente?->nombre ?? $estudio->tipo?->nombre ?? 'Estudio clínico',
            'tipo_categoria' => $this->categoria($estudio->tipo?->categoria), 'tipo_texto' => $estudio->tipo?->nombre ?? 'Estudio clínico',
            'parametro_clave' => $resultado?->componente?->nombre,
            'resultado_valor' => $resultado?->valor_numerico ?? $resultado?->valor_texto ?? 'Pendiente',
            'resultado_unidad' => $resultado?->unidad ?? '', 'rango_referencia' => $resultado?->rango_referencia,
            'estado' => $clasificacion, 'estado_badge' => $resultado?->clasificacion ?: $estudio->estado,
            'estado_color' => $this->colorEstado($clasificacion),
            'estado_dot' => in_array($clasificacion, ['NORMAL', 'DENTRO_RANGO'], true) ? 'bg-emerald-500' : 'bg-amber-500',
            'estado_icono' => 'ph-bold ph-flask', 'solicitado_por' => $this->nombrePersonal($estudio->personal),
            'profesional_nombre' => $this->nombrePersonal($validador ?: $estudio->personal),
            'profesional_rol' => ($validador ?: $estudio->personal)?->profesion ?: 'No registrado',
            'validado_por' => $validador ? $this->nombrePersonal($validador) : 'No registrado',
            'laboratorio_centro' => $estudio->centro_medico, 'fecha_validacion' => $informe?->fecha_hora?->format('d/m/Y H:i'),
            'observaciones' => $resultado?->observacion ?: $estudio->observacion,
            'hallazgos' => $informe?->hallazgos, 'conclusion' => $informe?->conclusion,
            'tiene_documento' => (bool) $documento,
            'nombre_documento' => $documento ? $documento->titulo.'.'.strtolower((string) $documento->formato) : null,
            'ruta_documento' => $documento?->ruta_archivo, 'es_real_db' => true, 'historico_valores' => [],
        ];
    }

    private function categoria(?string $categoria): string
    {
        $categoria = strtoupper(trim((string) $categoria));

        return match (true) {
            str_contains($categoria, 'LAB'), str_contains($categoria, 'EXAMEN') => 'LABORATORIO',
            str_contains($categoria, 'IMAGEN'), str_contains($categoria, 'RADIO') => 'IMAGEN',
            str_contains($categoria, 'CARDIO'), str_contains($categoria, 'ECG') => 'CARDIOLOGICO', default => 'OTROS',
        };
    }

    private function nombrePersonal($personal): string
    {
        if (! $personal) {
            return 'No registrado';
        }
        $nombre = trim(implode(' ', array_filter([$personal->nombres, $personal->apellido_paterno, $personal->apellido_materno])));

        return $nombre !== '' ? $nombre : 'No registrado';
    }

    private function colorEstado(string $estado): string
    {
        return match ($estado) {
            'NORMAL', 'DENTRO_RANGO', 'FINALIZADO' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'ALTO', 'BAJO', 'CRITICO', 'FUERA_RANGO' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
            default => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
        };
    }

    private function extraerRango(?string $texto): array
    {
        if (! $texto || preg_match_all('/-?\d+(?:[.,]\d+)?/', $texto, $coincidencias) < 2) {
            return [null, null];
        }

        return [(float) str_replace(',', '.', $coincidencias[0][0]), (float) str_replace(',', '.', $coincidencias[0][1])];
    }
}
