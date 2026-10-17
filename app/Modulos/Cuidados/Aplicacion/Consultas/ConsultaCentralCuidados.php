<?php

namespace App\Modulos\Cuidados\Aplicacion\Consultas;

use App\Models\ControlCognitivo;
use App\Models\CuracionHerida;
use App\Models\Herida;
use App\Models\RegistroConductual;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\SignoVital;
use App\Models\User;
use App\Models\ValoracionDolor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Consulta de historia existente. Nunca interpreta clínica ni escribe registros. */
final class ConsultaCentralCuidados
{
    private const FUENTES = [
        'signos' => [SignoVital::class, 'signos', 'signos_vitales.ver', 'fecha_hora', 'Signos vitales'],
        'dolor' => [ValoracionDolor::class, 'dolor', 'valoraciones_dolor.ver', 'fecha_hora', 'Dolor'],
        'cognicion' => [ControlCognitivo::class, 'cognicion', 'controles_cognitivos.ver', 'fecha_hora', 'Cognición'],
        'conducta' => [RegistroConductual::class, 'conducta', 'registros_conductuales.ver', 'fecha_hora', 'Conducta'],
        'sueno' => [RegistroSueno::class, 'sueno', 'registros_sueno.ver', 'fecha', 'Sueño'],
        'ingesta' => [RegistroIngesta::class, 'ingesta', 'registros_ingesta.ver', 'fecha_hora', 'Ingesta'],
        'hidratacion' => [RegistroHidratacion::class, 'hidratacion', 'registros_hidratacion.ver', 'fecha_hora', 'Hidratación'],
        'eliminacion' => [RegistroEliminacion::class, 'eliminacion', 'registros_eliminacion.ver', 'fecha_hora', 'Eliminación'],
        'movilidad' => [RegistroMovilidad::class, 'movilidad', 'registros_movilidad.ver', 'fecha_hora', 'Movilidad'],
        'heridas' => [Herida::class, 'heridas', 'heridas.ver', 'fecha_hora_identificacion', 'Heridas identificadas'],
        'curaciones' => [CuracionHerida::class, 'heridas', 'curaciones_herida.ver', 'fecha_hora', 'Curaciones'],
    ];

    private const CAMPOS = [
        'signos' => ['presion_sistolica' => 'Presión sistólica (mmHg)', 'presion_diastolica' => 'Presión diastólica (mmHg)', 'frecuencia_cardiaca' => 'Frecuencia cardíaca (lpm)', 'frecuencia_respiratoria' => 'Frecuencia respiratoria (rpm)', 'temperatura' => 'Temperatura (°C)', 'saturacion_oxigeno' => 'SpO₂ (%)', 'glucemia' => 'Glucemia (mg/dL)', 'observacion' => 'Observación'],
        'dolor' => ['tipo_dolor' => 'Tipo de dolor', 'intensidad' => 'Intensidad registrada', 'ubicacion' => 'Ubicación', 'duracion' => 'Duración', 'frecuencia' => 'Frecuencia', 'desencadenante' => 'Desencadenante', 'factores_alivio' => 'Factores de alivio', 'intervencion' => 'Intervención', 'respuesta' => 'Respuesta', 'cod_valoracion_origen' => 'Valoración de origen'],
        'cognicion' => ['orientacion_persona' => 'Orientación en persona', 'orientacion_lugar' => 'Orientación en lugar', 'orientacion_tiempo' => 'Orientación en tiempo', 'memoria_reciente' => 'Memoria reciente', 'memoria_remota' => 'Memoria remota', 'atencion' => 'Atención', 'comprension' => 'Comprensión', 'lenguaje' => 'Lenguaje', 'sigue_instrucciones' => 'Sigue instrucciones', 'repite_preguntas' => 'Repite preguntas', 'olvida_indicaciones' => 'Olvida indicaciones', 'reconoce_personas' => 'Reconoce personas', 'reconoce_entorno' => 'Reconoce entorno', 'confusion' => 'Confusión registrada', 'cambio_cognitivo' => 'Cambio cognitivo registrado', 'observacion' => 'Observación'],
        'conducta' => ['estado_animo' => 'Estado de ánimo', 'participacion' => 'Participación', 'apatia' => 'Apatía', 'agitacion' => 'Agitación', 'agresividad' => 'Agresividad', 'ansiedad' => 'Ansiedad', 'aislamiento' => 'Aislamiento', 'deambulacion' => 'Deambulación', 'cambio_conducta' => 'Cambio de conducta', 'descripcion' => 'Descripción', 'intervencion' => 'Intervención', 'respuesta' => 'Respuesta'],
        'sueno' => ['horas_sueno' => 'Horas de sueño', 'despertares' => 'Despertares', 'insomnio' => 'Insomnio', 'somnolencia_diurna' => 'Somnolencia diurna', 'agitacion_nocturna' => 'Agitación nocturna', 'calidad' => 'Calidad registrada', 'observacion' => 'Observación'],
        'ingesta' => ['tipo_comida' => 'Comida', 'porcentaje_consumido' => 'Porcentaje consumido (%)', 'apetito' => 'Apetito', 'tolerancia' => 'Tolerancia', 'dificultad_deglucion' => 'Dificultad de deglución', 'observacion' => 'Observación'],
        'hidratacion' => ['cantidad_ml' => 'Cantidad (ml)', 'tipo_liquido' => 'Líquido', 'tolerancia' => 'Tolerancia', 'observacion' => 'Observación'],
        'heridas' => ['tipo_herida' => 'Tipo de herida', 'ubicacion' => 'Ubicación', 'causa' => 'Causa', 'clasificacion' => 'Clasificación registrada', 'observacion' => 'Observación', 'fecha_hora_cierre' => 'Fecha de cierre'],
        'curaciones' => ['longitud' => 'Longitud (cm)', 'ancho' => 'Ancho (cm)', 'profundidad' => 'Profundidad (cm)', 'tejido' => 'Tejido', 'exudado' => 'Exudado', 'olor' => 'Olor', 'dolor' => 'Dolor registrado', 'procedimiento' => 'Procedimiento', 'materiales' => 'Materiales', 'respuesta' => 'Respuesta', 'observacion' => 'Observación'],
    ];

    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function tiposDisponibles(User $usuario): array
    {
        $this->autorizar($usuario);
        $tipos = [];
        foreach (self::FUENTES as $origen => [, $tipo, $permiso, , $label]) {
            if ($origen !== 'curaciones' && $usuario->can($permiso)) {
                $tipos[$tipo] = $tipo === 'heridas' ? 'Heridas y curaciones' : $label;
            }
        }

        return $tipos;
    }

    public function consultar(User $usuario, array $filtros, int $pagina = 1): array
    {
        $tipos = $this->tiposDisponibles($usuario);
        abort_if($tipos === [], 403);
        abort_if($filtros['tipo'] !== '' && ! isset($tipos[$filtros['tipo']]), 403);
        Validator::make($filtros, [
            'tipo' => ['nullable', Rule::in(array_keys($tipos))],
            'residente' => 'nullable|string|max:20',
            'desde' => 'required|date_format:Y-m-d',
            'hasta' => 'required|date_format:Y-m-d|after_or_equal:desde|before_or_equal:today',
        ], ['hasta.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
            'hasta.before_or_equal' => 'Selecciona un período hasta hoy.'])->validate();
        if ($filtros['residente'] !== '') {
            $this->turnos->autorizarConsultaResidente($filtros['residente'], $usuario);
        }
        $alcance = $this->turnos->obtenerPacientesAsignadosQuery($usuario)
            ->when($filtros['residente'] !== '', fn ($q) => $q->whereKey($filtros['residente']));
        $union = null;
        foreach (self::FUENTES as $origen => [$clase, $tipo, $permiso, $fecha]) {
            if (! isset($tipos[$tipo]) || ! $usuario->can($permiso) || ($filtros['tipo'] !== '' && $tipo !== $filtros['tipo'])) {
                continue;
            }
            $modelo = new $clase;
            $tabla = $modelo->getTable();
            $consulta = $modelo->newQuery()->toBase();
            $columnaResidente = $tabla.'.cod_residente';
            if ($origen === 'curaciones') {
                $consulta->join('heridas', 'heridas.cod_herida', '=', $tabla.'.cod_herida');
                $columnaResidente = 'heridas.cod_residente';
            }
            $consulta->selectRaw('? as origen, ? as tipo', [$origen, $tipo])
                ->addSelect($tabla.'.'.$modelo->getKeyName().' as codigo', $columnaResidente.' as cod_residente', $tabla.'.'.$fecha.' as fecha')
                ->whereIn($columnaResidente, (clone $alcance)->select('cod_residente')->toBase());
            if ($origen === 'sueno') {
                $consulta->whereDate($tabla.'.'.$fecha, '>=', $filtros['desde'])
                    ->whereDate($tabla.'.'.$fecha, '<=', $filtros['hasta'])
                    ->whereDate($tabla.'.'.$fecha, '<=', today()->toDateString());
            } else {
                $consulta->whereBetween($tabla.'.'.$fecha, [$filtros['desde'], $filtros['hasta'].' 23:59:59'])
                    ->where($tabla.'.'.$fecha, '<=', now()->toDateTimeString());
            }
            if ($origen === 'curaciones') {
                $consulta->selectRaw('? as estado', ['GUARDADO']);
            } else {
                $consulta->addSelect($tabla.'.estado as estado');
            }
            $union = $union ? $union->unionAll($consulta) : $consulta;
        }
        $base = $union ? DB::query()->fromSub($union, 'cuidados_guardados') : null;
        $resumen = $base ? (clone $base)->select('tipo')->selectRaw("count(*) as total, max(fecha) as ultima_fecha, sum(case when estado = 'ANULADO' then 1 else 0 end) as anulados")->groupBy('tipo')->get()->keyBy('tipo') : collect();
        $total = $base ? (clone $base)->count() : 0;
        $pagina = min(max(1, $pagina), max(1, (int) ceil($total / 20)));
        $filas = $base ? (clone $base)->orderByDesc('fecha')->orderBy('origen')->orderByDesc('codigo')->forPage($pagina, 20)->get() : collect();
        $modelos = [];
        foreach ($filas->groupBy('origen') as $origen => $grupo) {
            $clase = self::FUENTES[$origen][0];
            $relaciones = $origen === 'curaciones' ? ['herida.residente', 'personal'] : ['residente', 'personal'];
            $modelos[$origen] = $clase::with($relaciones)->whereKey($grupo->pluck('codigo')->all())->get()->keyBy(fn ($m) => (string) $m->getKey());
        }
        $datos = $filas->map(fn ($fila) => $this->presentar($fila->origen, $modelos[$fila->origen][$fila->codigo]));

        return [
            'tipos' => $tipos,
            'resumen' => $resumen->map(fn ($r) => ['total' => (int) $r->total, 'anulados' => (int) $r->anulados, 'ultima_fecha' => Carbon::parse($r->ultima_fecha)->format('d/m/Y')])->all(),
            'total' => $total,
            'residentes_con_registros' => $base ? (clone $base)->distinct()->count('cod_residente') : 0,
            'registros' => new LengthAwarePaginator($datos, $total, 20, $pagina),
        ];
    }

    public function detalle(User $usuario, string $origen, string $codigo): array
    {
        $tipos = $this->tiposDisponibles($usuario);
        abort_unless(isset(self::FUENTES[$origen]), 404);
        [$clase, $tipo, $permiso] = self::FUENTES[$origen];
        abort_unless(isset($tipos[$tipo]) && $usuario->can($permiso), 403);
        $modelo = $clase::with($origen === 'curaciones' ? ['herida.residente', 'personal'] : ['residente', 'personal'])->findOrFail($codigo);
        $residente = $origen === 'curaciones' ? $modelo->herida->cod_residente : $modelo->cod_residente;
        $this->turnos->autorizarConsultaResidente($residente, $usuario);

        return $this->presentar($origen, $modelo);
    }

    private function autorizar(User $usuario): void
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('enfermeria.ver_dashboard')
            && ($usuario->hasRole('ENFERMEROS') || $this->turnos->tieneLecturaClinicaGlobal($usuario)), 403);
    }

    private function presentar(string $origen, Model $modelo): array
    {
        [, $tipo, , $fecha, $label] = self::FUENTES[$origen];
        $residente = $origen === 'curaciones' ? $modelo->herida->residente : $modelo->residente;
        $campos = [];
        if (in_array($origen, ['eliminacion', 'movilidad'], true)) {
            $resumen = $modelo->resumenOperacional();
            $campos = $resumen['campos'];
            if ($origen === 'eliminacion') {
                array_unshift($campos, ['nombre' => 'Tipo', 'valor' => $resumen['tipo_label']]);
            }
            if (filled($modelo->observacion)) {
                $campos[] = ['nombre' => 'Observación', 'valor' => $modelo->observacion];
            }
        } else {
            foreach (self::CAMPOS[$origen] as $campo => $nombre) {
                if (str_starts_with($campo, 'cod_')) {
                    continue;
                }
                $valor = $modelo->getAttribute($campo);
                if ($valor === null || $valor === '') {
                    continue;
                }
                $texto = is_bool($valor) ? ($valor ? 'Sí' : 'No') : ($valor instanceof \DateTimeInterface ? $valor->format('d/m/Y H:i') : (string) $valor);
                $campos[] = ['nombre' => $nombre, 'valor' => $texto];
            }
        }
        if ($origen === 'curaciones') {
            array_unshift($campos,
                ['nombre' => 'Herida', 'valor' => $modelo->herida->tipo_herida.' · '.$modelo->herida->ubicacion]);
        }

        return [
            'origen' => $origen, 'tipo' => $tipo, 'codigo' => (string) $modelo->getKey(), 'label' => $label,
            'fecha' => $modelo->{$fecha}->format($origen === 'sueno' ? 'd/m/Y' : 'd/m/Y H:i'),
            'residente_codigo' => $residente->getKey(), 'residente' => trim($residente->nombres.' '.$residente->apellido_paterno.' '.$residente->apellido_materno),
            'profesional' => $modelo->personal ? trim($modelo->personal->nombres.' '.$modelo->personal->apellido_paterno.' '.$modelo->personal->apellido_materno) : 'Autor no informado en el registro',
            'estado' => $modelo->getAttribute('estado'), 'campos' => $campos,
        ];
    }
}
