<?php

namespace App\Services\Identidad;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GeneradorPlanillaEnfermeriaService
{
    private const ROL_REGULAR = 'REGULAR';
    private const ROL_APOYO = 'APOYO';
    private const ROL_DESCANSO = 'DESCANSO';

    private const TURNO_MANANA = 'MANANA';
    private const TURNO_TARDE = 'TARDE';
    private const TURNO_NOCHE = 'NOCHE';
    private const TURNO_APOYO = 'APOYO';
    private const TURNO_DESCANSO = 'DESCANSO';

    protected array $temporales = [];

    /**
     * Turnos clínicos usados para la planilla de enfermería.
     * No se usan turnos institucionales administrativos aquí.
     */
    protected array $turnos = [
        self::TURNO_MANANA => [
            'codigo' => self::TURNO_MANANA,
            'nombre' => 'Mañana',
            'hora_inicio' => '06:00',
            'hora_fin' => '14:00',
            'horas' => 8,
            'rol' => self::ROL_REGULAR,
            'clase_visual' => 'bg-estado-exitoBg text-estado-exito border border-estado-exito/20',
        ],
        self::TURNO_TARDE => [
            'codigo' => self::TURNO_TARDE,
            'nombre' => 'Tarde',
            'hora_inicio' => '14:00',
            'hora_fin' => '22:00',
            'horas' => 8,
            'rol' => self::ROL_REGULAR,
            'clase_visual' => 'bg-boton-acento/10 text-boton-acento border border-boton-acento/20',
        ],
        self::TURNO_NOCHE => [
            'codigo' => self::TURNO_NOCHE,
            'nombre' => 'Noche',
            'hora_inicio' => '22:00',
            'hora_fin' => '06:00',
            'horas' => 8,
            'rol' => self::ROL_REGULAR,
            'clase_visual' => 'bg-boton-principal/10 text-boton-principal border border-boton-principal/20',
        ],
        self::TURNO_APOYO => [
            'codigo' => self::TURNO_APOYO,
            'nombre' => 'Apoyo / Volante',
            'hora_inicio' => '08:00',
            'hora_fin' => '16:00',
            'horas' => 8,
            'rol' => self::ROL_APOYO,
            'clase_visual' => 'bg-boton-acento/10 text-boton-acento border border-boton-acento/20',
        ],
        self::TURNO_DESCANSO => [
            'codigo' => self::TURNO_DESCANSO,
            'nombre' => 'Descanso',
            'hora_inicio' => null,
            'hora_fin' => null,
            'horas' => 0,
            'rol' => self::ROL_DESCANSO,
            'clase_visual' => 'bg-fondo-hover text-apoyo border border-borde-suave',
        ],
    ];

    protected array $dias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo',
    ];

    /**
     * Patrón base de la semana 1.
     * Las siguientes semanas se generan con rotación modular.
     */
    protected array $patronBase = [
        'Lunes' => [
            self::TURNO_MANANA => ['E01', 'E02', 'E03'],
            self::TURNO_TARDE => ['E04', 'E05', 'E06'],
            self::TURNO_NOCHE => ['E07', 'E08', 'E09'],
            self::TURNO_APOYO => ['E10'],
            self::TURNO_DESCANSO => ['E11', 'E12'],
        ],
        'Martes' => [
            self::TURNO_MANANA => ['E04', 'E05', 'E06'],
            self::TURNO_TARDE => ['E07', 'E08', 'E09'],
            self::TURNO_NOCHE => ['E10', 'E11', 'E12'],
            self::TURNO_APOYO => ['E01'],
            self::TURNO_DESCANSO => ['E02', 'E03'],
        ],
        'Miércoles' => [
            self::TURNO_MANANA => ['E07', 'E08', 'E09'],
            self::TURNO_TARDE => ['E10', 'E11', 'E12'],
            self::TURNO_NOCHE => ['E01', 'E02', 'E03'],
            self::TURNO_APOYO => ['E04'],
            self::TURNO_DESCANSO => ['E05', 'E06'],
        ],
        'Jueves' => [
            self::TURNO_MANANA => ['E10', 'E11', 'E12'],
            self::TURNO_TARDE => ['E01', 'E02', 'E03'],
            self::TURNO_NOCHE => ['E04', 'E05', 'E06'],
            self::TURNO_APOYO => ['E07'],
            self::TURNO_DESCANSO => ['E08', 'E09'],
        ],
        'Viernes' => [
            self::TURNO_MANANA => ['E01', 'E05', 'E09'],
            self::TURNO_TARDE => ['E02', 'E06', 'E10'],
            self::TURNO_NOCHE => ['E03', 'E07', 'E11'],
            self::TURNO_APOYO => ['E12'],
            self::TURNO_DESCANSO => ['E04', 'E08'],
        ],
        'Sábado' => [
            self::TURNO_MANANA => ['E02', 'E06', 'E10'],
            self::TURNO_TARDE => ['E03', 'E07', 'E11'],
            self::TURNO_NOCHE => ['E04', 'E08', 'E12'],
            self::TURNO_APOYO => ['E05'],
            self::TURNO_DESCANSO => ['E01', 'E09'],
        ],
        'Domingo' => [
            self::TURNO_MANANA => ['E03', 'E07', 'E11'],
            self::TURNO_TARDE => ['E04', 'E08', 'E12'],
            self::TURNO_NOCHE => ['E01', 'E05', 'E09'],
            self::TURNO_APOYO => ['E06'],
            self::TURNO_DESCANSO => ['E02', 'E10'],
        ],
    ];

    /**
     * Punto principal de entrada.
     */
    public function generar(array $config = []): array
    {
        $config = $this->normalizarConfig($config);

        $fechaInicioStr = $config['fecha_inicio']->toDateString();
        $fechaFinStr = $config['fecha_inicio']->copy()->addWeeks($config['cantidad_semanas'])->subDay()->toDateString();

        try {
            if (Schema::hasTable('asignaciones_plazas_enfermeria')) {
                $this->temporales = \App\Models\AsignacionPlazaEnfermeria::with('user')
                    ->whereBetween('fecha', [$fechaInicioStr, $fechaFinStr])
                    ->get()
                    ->groupBy(fn($item) => $item->plaza . '_' . $item->fecha->toDateString())
                    ->toArray();
            } else {
                $this->temporales = [];
            }
        } catch (\Throwable $e) {
            report($e);
            $this->temporales = [];
        }

        $enfermeros = $this->resolverEnfermeros($config);

        if (count($enfermeros) < 12) {
            $enfermeros = $this->enfermerosVirtuales();
        }

        $planilla = [];

        for ($semana = 1; $semana <= $config['cantidad_semanas']; $semana++) {
            $planilla[] = $this->generarSemana(
                numeroSemana: $semana,
                fechaInicioSemana: $config['fecha_inicio']->copy()->addWeeks($semana - 1),
                enfermeros: $enfermeros,
                saltoSemanal: $config['salto_semanal']
            );
        }

        $cargaLaboral = $this->calcularCargaLaboral($planilla);
        $alertas = $this->validarAlertas($planilla, $cargaLaboral, $config);
        $resumen = $this->generarResumen($planilla, $cargaLaboral, $alertas, $config);
        $equilibrio = $this->calcularEquilibrio(count($enfermeros), $config['salto_semanal']);

        $resultado = [
            'config' => $this->serializarConfig($config),
            'turnos' => $this->turnos,
            'enfermeros' => $enfermeros,
            'planilla' => $planilla,
            'vista_semanal' => $this->generarVistaSemanal($planilla, $config),
            'vista_hoy' => $this->generarVistaHoy($planilla),
            'vista_por_enfermero' => $this->generarVistaPorEnfermero($planilla),
            'carga_laboral' => $cargaLaboral,
            'alertas' => $alertas,
            'resumen' => $resumen,
            'equilibrio' => $equilibrio,
            'filtros_disponibles' => $this->generarFiltrosDisponibles($enfermeros),
        ];

        return $this->aplicarFiltros($resultado, $config);
    }

    protected function normalizarConfig(array $config): array
    {
        $fechaInicio = $config['fecha_inicio'] ?? now()->startOfWeek(Carbon::MONDAY);

        try {
            $fechaInicio = Carbon::parse($fechaInicio)->startOfDay();
        } catch (Throwable) {
            $fechaInicio = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        }

        return [
            'fecha_inicio' => $fechaInicio,
            'cantidad_semanas' => max(1, min((int) ($config['cantidad_semanas'] ?? 1), 52)),
            'salto_semanal' => max(1, (int) ($config['salto_semanal'] ?? 1)),
            'total_enfermeros' => max(12, (int) ($config['total_enfermeros'] ?? 12)),
            'trabajador_filtro' => $config['trabajador_filtro'] ?? null,
            'turno_filtro' => $config['turno_filtro'] ?? null,
            'grupo_filtro' => $config['grupo_filtro'] ?? null,
            'mostrar_apoyo' => filter_var($config['mostrar_apoyo'] ?? true, FILTER_VALIDATE_BOOL),
            'mostrar_descanso' => filter_var($config['mostrar_descanso'] ?? true, FILTER_VALIDATE_BOOL),
            'mostrar_grupos' => filter_var($config['mostrar_grupos'] ?? false, FILTER_VALIDATE_BOOL),
            'solo_conflictos' => filter_var($config['solo_conflictos'] ?? false, FILTER_VALIDATE_BOOL),
            'usar_usuarios_reales' => filter_var($config['usar_usuarios_reales'] ?? false, FILTER_VALIDATE_BOOL),
            'horas_maximas_semana' => (int) ($config['horas_maximas_semana'] ?? 56),
            'noches_maximas_periodo' => (int) ($config['noches_maximas_periodo'] ?? 8),
        ];
    }

    protected function serializarConfig(array $config): array
    {
        return [
            ...$config,
            'fecha_inicio' => $config['fecha_inicio']->format('Y-m-d'),
        ];
    }

    /**
     * En esta fase puede trabajar con E01-E12.
     * Si después se desea usar users reales, se activa usar_usuarios_reales.
     */
    protected function resolverEnfermeros(array $config): array
    {
        $enfermeros = [];

        try {
            if (!class_exists(\App\Models\AsignacionPlazaEnfermeria::class) || !Schema::hasTable('asignaciones_plazas_enfermeria')) {
                return $this->enfermerosVirtuales($config['total_enfermeros'] ?? 12);
            }

            $titulares = \App\Models\AsignacionPlazaEnfermeria::with('user')
                ->whereNull('fecha')
                ->where('tipo', 'TITULAR')
                ->get()
                ->keyBy('plaza');

            $total = max(12, (int) ($config['total_enfermeros'] ?? 12));

            for ($i = 1; $i <= $total; $i++) {
                $codigo = 'E' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $titular = $titulares->get($codigo);
                $user = $titular ? $titular->user : null;

                if ($user) {
                    $enfermeros[] = [
                        'codigo' => $codigo,
                        'cod_usu' => $user->cod_usu,
                        'nombre' => trim(($user->nombres ?? '') . ' ' . ($user->ap_paterno ?? '')) ?: $codigo,
                        'correo' => $user->correo ?? null,
                        'rol' => 'ENFERMEROS',
                        'estado' => $user->estado ?? 'ACTIVO',
                        'tipo' => 'TITULAR',
                        'familia_visual' => $this->familiaVisual($codigo),
                        'clase_familia' => $this->claseFamilia($codigo),
                    ];
                } else {
                    $enfermeros[] = [
                        'codigo' => $codigo,
                        'cod_usu' => null,
                        'nombre' => 'Sin enfermero asignado',
                        'correo' => null,
                        'rol' => 'ENFERMEROS',
                        'estado' => 'ACTIVO',
                        'tipo' => 'DISPONIBLE',
                        'familia_visual' => $this->familiaVisual($codigo),
                        'clase_familia' => $this->claseFamilia($codigo),
                    ];
                }
            }
        } catch (\Throwable $e) {
            report($e);
            return $this->enfermerosVirtuales($config['total_enfermeros'] ?? 12);
        }

        return $enfermeros;
    }

    protected function enfermerosVirtuales(int $total = 12): array
    {
        return collect(range(1, $total))
            ->map(function (int $numero) {
                $codigo = 'E' . str_pad((string) $numero, 2, '0', STR_PAD_LEFT);

                return [
                    'codigo' => $codigo,
                    'cod_usu' => null,
                    'nombre' => 'Enfermero ' . $codigo,
                    'correo' => null,
                    'rol' => 'ENFERMEROS',
                    'estado' => 'ACTIVO',
                    'familia_visual' => $this->familiaVisual($codigo),
                    'clase_familia' => $this->claseFamilia($codigo),
                ];
            })
            ->values()
            ->all();
    }

    protected function generarSemana(int $numeroSemana, Carbon $fechaInicioSemana, array $enfermeros, int $saltoSemanal): array
    {
        $total = count($enfermeros);
        $dias = [];

        foreach ($this->dias as $indiceDia => $nombreDia) {
            $fecha = $fechaInicioSemana->copy()->addDays($indiceDia - 1);

            $dias[] = $this->generarDia(
                numeroSemana: $numeroSemana,
                nombreDia: $nombreDia,
                fecha: $fecha,
                enfermeros: $enfermeros,
                saltoSemanal: $saltoSemanal,
                total: $total
            );
        }

        return [
            'semana' => $numeroSemana,
            'fecha_inicio' => $fechaInicioSemana->format('Y-m-d'),
            'fecha_fin' => $fechaInicioSemana->copy()->addDays(6)->format('Y-m-d'),
            'dias' => $dias,
        ];
    }

    protected function generarDia(
        int $numeroSemana,
        string $nombreDia,
        Carbon $fecha,
        array $enfermeros,
        int $saltoSemanal,
        int $total
    ): array {
        $turnosDia = [];
        $patronDia = $this->patronBase[$nombreDia] ?? [];

        foreach ($patronDia as $codigoTurno => $codigosBase) {
            $asignaciones = [];

            foreach ($codigosBase as $posicionEnTurno => $codigoBase) {
                $codigoRotado = $this->rotarCodigo($codigoBase, $numeroSemana, $saltoSemanal, $total);
                
                // Get titular first
                $titularBase = $this->buscarEnfermeroPorCodigo($enfermeros, $codigoRotado);
                $titular_cod = ($titularBase && $titularBase['cod_usu']) ? $titularBase['cod_usu'] : null;
                $titular_nom = ($titularBase && $titularBase['cod_usu']) ? $titularBase['nombre'] : 'Sin enfermero asignado';

                $fechaStr = $fecha->toDateString();
                $tempKey = $codigoRotado . '_' . $fechaStr;

                $reemplazo_cod = null;
                $reemplazo_nom = null;
                $es_reemplazo = false;
                $motivo = null;
                $tipo_asignacion = ($titular_cod) ? 'TITULAR' : 'DISPONIBLE';

                $active_enfermero = $titularBase;

                // Check if date-specific assignment exists
                if (isset($this->temporales[$tempKey]) && !empty($this->temporales[$tempKey])) {
                    $temporal = $this->temporales[$tempKey][0];
                    $user = $temporal['user'] ?? null;

                    $reemplazo_cod = $user ? $user['cod_usu'] : null;
                    $reemplazo_nom = $user ? trim(($user['nombres'] ?? '') . ' ' . ($user['ap_paterno'] ?? '')) : 'Sin enfermero asignado';
                    $es_reemplazo = true;
                    $motivo = $temporal['motivo'] ?? null;
                    $tipo_asignacion = $temporal['tipo'] ?? 'REEMPLAZO';

                    $active_enfermero = [
                        'codigo' => $codigoRotado,
                        'cod_usu' => $reemplazo_cod,
                        'nombre' => $reemplazo_nom ?: 'Sin enfermero asignado',
                        'correo' => $user ? ($user['correo'] ?? null) : null,
                        'rol' => 'ENFERMEROS',
                        'estado' => $user ? ($user['estado'] ?? 'ACTIVO') : 'ACTIVO',
                        'tipo' => $tipo_asignacion,
                        'familia_visual' => $this->familiaVisual($codigoRotado),
                        'clase_familia' => $this->claseFamilia($codigoRotado),
                    ];
                }

                $grupo = $this->resolverGrupoPorTurno($codigoTurno, $posicionEnTurno);
                $turno = $this->turnos[$codigoTurno];

                $asignaciones[] = [
                    'codigo' => $codigoRotado,
                    'cod_usu' => $active_enfermero['cod_usu'] ?? null,
                    'nombre' => $active_enfermero['nombre'] ?? 'Sin enfermero asignado',
                    'correo' => $active_enfermero['correo'] ?? null,
                    'rol_en_turno' => $turno['rol'],
                    'turno_codigo' => $codigoTurno,
                    'turno_nombre' => $turno['nombre'],
                    'grupo' => $grupo,
                    'grupo_nombre' => $grupo ? 'Grupo ' . $grupo : null,
                    'hora_inicio' => $turno['hora_inicio'],
                    'hora_fin' => $turno['hora_fin'],
                    'horas' => $turno['horas'],
                    'familia_visual' => $this->familiaVisual($codigoRotado),
                    'clase_familia' => $this->claseFamilia($codigoRotado),
                    'clase_turno' => $turno['clase_visual'],
                    
                    // Added metadata
                    'titular_cod' => $titular_cod,
                    'titular_nom' => $titular_nom,
                    'reemplazo_cod' => $reemplazo_cod,
                    'reemplazo_nom' => $reemplazo_nom,
                    'es_reemplazo' => $es_reemplazo,
                    'motivo' => $motivo,
                    'tipo_asignacion' => $tipo_asignacion,
                    'fecha' => $fechaStr,
                ];
            }

            $turnosDia[$codigoTurno] = [
                'codigo' => $codigoTurno,
                'nombre' => $this->turnos[$codigoTurno]['nombre'],
                'hora_inicio' => $this->turnos[$codigoTurno]['hora_inicio'],
                'hora_fin' => $this->turnos[$codigoTurno]['hora_fin'],
                'horas' => $this->turnos[$codigoTurno]['horas'],
                'rol' => $this->turnos[$codigoTurno]['rol'],
                'asignaciones' => $asignaciones,
            ];
        }

        return [
            'fecha' => $fecha->format('Y-m-d'),
            'dia' => $nombreDia,
            'numero_dia_semana' => $fecha->dayOfWeekIso,
            'turnos' => $turnosDia,
            'cobertura' => $this->validarCoberturaDia($turnosDia),
        ];
    }

    protected function rotarCodigo(string $codigo, int $semana, int $saltoSemanal, int $total): string
    {
        $numeroBase = (int) preg_replace('/[^0-9]/', '', $codigo);

        if ($numeroBase <= 0) {
            return $codigo;
        }

        $posicionBase = $numeroBase - 1;

        $posicionFinal = ($posicionBase + (($semana - 1) * $saltoSemanal)) % $total;

        return 'E' . str_pad((string) ($posicionFinal + 1), 2, '0', STR_PAD_LEFT);
    }

    protected function buscarEnfermeroPorCodigo(array $enfermeros, string $codigo): ?array
    {
        foreach ($enfermeros as $enfermero) {
            if (($enfermero['codigo'] ?? null) === $codigo) {
                return $enfermero;
            }
        }

        return null;
    }

    protected function resolverGrupoPorTurno(string $codigoTurno, int $posicionEnTurno): ?string
    {
        if (! in_array($codigoTurno, [self::TURNO_MANANA, self::TURNO_TARDE, self::TURNO_NOCHE], true)) {
            return null;
        }

        return match ($posicionEnTurno) {
            0 => 'A',
            1 => 'B',
            2 => 'C',
            default => null,
        };
    }

    protected function calcularCargaLaboral(array $planilla): array
    {
        $carga = [];

        foreach ($this->recorrerAsignaciones($planilla) as $item) {
            $codigo = $item['asignacion']['codigo'];

            if (! isset($carga[$codigo])) {
                $carga[$codigo] = [
                    'codigo' => $codigo,
                    'nombre' => $item['asignacion']['nombre'] ?? $codigo,
                    'mananas' => 0,
                    'tardes' => 0,
                    'noches' => 0,
                    'apoyos' => 0,
                    'descansos' => 0,
                    'jornadas_trabajadas' => 0,
                    'horas_totales' => 0,
                    'grupos_a' => 0,
                    'grupos_b' => 0,
                    'grupos_c' => 0,
                    'noches_consecutivas_max' => 0,
                    'estado' => 'Equilibrado',
                    'clase_estado' => 'bg-estado-exitoBg text-estado-exito',
                    'detalle' => [],
                ];
            }

            $turno = $item['turno_codigo'];
            $horas = (int) ($item['asignacion']['horas'] ?? 0);

            match ($turno) {
                self::TURNO_MANANA => $carga[$codigo]['mananas']++,
                self::TURNO_TARDE => $carga[$codigo]['tardes']++,
                self::TURNO_NOCHE => $carga[$codigo]['noches']++,
                self::TURNO_APOYO => $carga[$codigo]['apoyos']++,
                self::TURNO_DESCANSO => $carga[$codigo]['descansos']++,
                default => null,
            };

            if ($turno !== self::TURNO_DESCANSO) {
                $carga[$codigo]['jornadas_trabajadas']++;
                $carga[$codigo]['horas_totales'] += $horas;
            }

            $grupo = $item['asignacion']['grupo'] ?? null;

            if ($grupo === 'A') {
                $carga[$codigo]['grupos_a']++;
            }

            if ($grupo === 'B') {
                $carga[$codigo]['grupos_b']++;
            }

            if ($grupo === 'C') {
                $carga[$codigo]['grupos_c']++;
            }

            $carga[$codigo]['detalle'][] = [
                'semana' => $item['semana'],
                'fecha' => $item['fecha'],
                'dia' => $item['dia'],
                'turno' => $turno,
                'turno_nombre' => $item['turno_nombre'],
                'hora_inicio' => $item['asignacion']['hora_inicio'],
                'hora_fin' => $item['asignacion']['hora_fin'],
                'grupo' => $grupo,
                'rol_en_turno' => $item['asignacion']['rol_en_turno'],
                'horas' => $horas,
            ];
        }

        foreach ($carga as $codigo => $registro) {
            $carga[$codigo]['noches_consecutivas_max'] = $this->calcularNochesConsecutivasMax($registro['detalle']);
        }

        return collect($carga)
            ->sortKeys()
            ->map(fn (array $registro) => $this->clasificarCarga($registro, $carga))
            ->values()
            ->all();
    }

    protected function clasificarCarga(array $registro, array $cargaCompleta): array
    {
        $horas = $registro['horas_totales'];
        $promedio = collect($cargaCompleta)->avg('horas_totales') ?: 0;
        $diferencia = $horas - $promedio;

        $registro['diferencia_promedio'] = round($diferencia, 2);

        if ($registro['noches_consecutivas_max'] >= 3) {
            $registro['estado'] = 'Revisar noches';
            $registro['clase_estado'] = 'bg-boton-acento/10 text-boton-acento';
            return $registro;
        }

        if ($diferencia >= 16) {
            $registro['estado'] = 'Carga alta';
            $registro['clase_estado'] = 'bg-estado-peligroBg text-estado-peligro';
            return $registro;
        }

        if ($diferencia <= -16) {
            $registro['estado'] = 'Carga baja';
            $registro['clase_estado'] = 'bg-fondo-hover text-apoyo';
            return $registro;
        }

        $registro['estado'] = 'Equilibrado';
        $registro['clase_estado'] = 'bg-estado-exitoBg text-estado-exito';

        return $registro;
    }

    protected function calcularNochesConsecutivasMax(array $detalle): int
    {
        $ordenado = collect($detalle)->sortBy('fecha')->values();

        $max = 0;
        $actual = 0;

        foreach ($ordenado as $item) {
            if (($item['turno'] ?? null) === self::TURNO_NOCHE) {
                $actual++;
                $max = max($max, $actual);
            } else {
                $actual = 0;
            }
        }

        return $max;
    }

    protected function validarAlertas(array $planilla, array $cargaLaboral, array $config): array
    {
        $alertas = [];

        foreach ($planilla as $semana) {
            foreach ($semana['dias'] as $dia) {
                $codigosDia = [];

                foreach ($dia['turnos'] as $codigoTurno => $turno) {
                    $cantidad = count($turno['asignaciones'] ?? []);

                    $esperado = match ($codigoTurno) {
                        self::TURNO_MANANA,
                        self::TURNO_TARDE,
                        self::TURNO_NOCHE => 3,
                        self::TURNO_APOYO => 1,
                        self::TURNO_DESCANSO => 2,
                        default => 0,
                    };

                    if ($cantidad !== $esperado) {
                        $alertas[] = $this->alerta(
                            nivel: 'CRITICO',
                            tipo: 'COBERTURA_INCORRECTA',
                            mensaje: "{$turno['nombre']} requiere {$esperado} enfermero(s), pero tiene {$cantidad}.",
                            fecha: $dia['fecha'],
                            dia: $dia['dia'],
                            turno: $codigoTurno
                        );
                    }

                    foreach ($turno['asignaciones'] ?? [] as $asignacion) {
                        $codigo = $asignacion['codigo'];

                        if (in_array($codigo, $codigosDia, true)) {
                            $alertas[] = $this->alerta(
                                nivel: 'CRITICO',
                                tipo: 'DUPLICIDAD_DIARIA',
                                mensaje: "El enfermero {$codigo} aparece más de una vez el mismo día.",
                                fecha: $dia['fecha'],
                                dia: $dia['dia'],
                                turno: $codigoTurno,
                                enfermero: $codigo
                            );
                        }

                        $codigosDia[] = $codigo;
                    }
                }

                if (count(array_unique($codigosDia)) !== count($codigosDia)) {
                    $alertas[] = $this->alerta(
                        nivel: 'CRITICO',
                        tipo: 'DIA_CON_DUPLICIDAD',
                        mensaje: "Existe duplicidad de enfermeros en {$dia['dia']}.",
                        fecha: $dia['fecha'],
                        dia: $dia['dia']
                    );
                }

                if (count(array_unique($codigosDia)) !== 12) {
                    $alertas[] = $this->alerta(
                        nivel: 'MEDIO',
                        tipo: 'DIA_INCOMPLETO',
                        mensaje: "El día {$dia['dia']} no utiliza exactamente 12 enfermeros únicos.",
                        fecha: $dia['fecha'],
                        dia: $dia['dia']
                    );
                }
            }
        }

        foreach ($cargaLaboral as $carga) {
            if (($carga['horas_totales'] ?? 0) > ($config['horas_maximas_semana'] * $config['cantidad_semanas'])) {
                $alertas[] = $this->alerta(
                    nivel: 'ALTO',
                    tipo: 'SOBRECARGA_HORARIA',
                    mensaje: "{$carga['codigo']} supera el máximo de horas configurado.",
                    enfermero: $carga['codigo']
                );
            }

            if (($carga['noches_consecutivas_max'] ?? 0) >= 3) {
                $alertas[] = $this->alerta(
                    nivel: 'MEDIO',
                    tipo: 'NOCHES_CONSECUTIVAS',
                    mensaje: "{$carga['codigo']} acumula {$carga['noches_consecutivas_max']} noches consecutivas.",
                    enfermero: $carga['codigo']
                );
            }

            if (($carga['descansos'] ?? 0) <= 0) {
                $alertas[] = $this->alerta(
                    nivel: 'ALTO',
                    tipo: 'SIN_DESCANSO',
                    mensaje: "{$carga['codigo']} no tiene descanso registrado en el periodo generado.",
                    enfermero: $carga['codigo']
                );
            }
        }

        return $alertas;
    }

    protected function alerta(
        string $nivel,
        string $tipo,
        string $mensaje,
        ?string $fecha = null,
        ?string $dia = null,
        ?string $turno = null,
        ?string $enfermero = null
    ): array {
        return [
            'nivel' => $nivel,
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'fecha' => $fecha,
            'dia' => $dia,
            'turno' => $turno,
            'enfermero' => $enfermero,
            'clase_visual' => match ($nivel) {
                'CRITICO' => 'bg-estado-peligroBg text-estado-peligro border border-estado-peligro/20',
                'ALTO' => 'bg-boton-acento/10 text-boton-acento border border-boton-acento/20',
                'MEDIO' => 'bg-fondo-hover text-apoyo border border-borde-suave',
                default => 'bg-estado-exitoBg text-estado-exito border border-estado-exito/20',
            },
        ];
    }

    protected function validarCoberturaDia(array $turnosDia): array
    {
        $resultado = [
            'completa' => true,
            'detalle' => [],
        ];

        foreach ($turnosDia as $codigoTurno => $turno) {
            $esperado = match ($codigoTurno) {
                self::TURNO_MANANA,
                self::TURNO_TARDE,
                self::TURNO_NOCHE => 3,
                self::TURNO_APOYO => 1,
                self::TURNO_DESCANSO => 2,
                default => 0,
            };

            $actual = count($turno['asignaciones'] ?? []);

            $resultado['detalle'][$codigoTurno] = [
                'esperado' => $esperado,
                'actual' => $actual,
                'ok' => $actual === $esperado,
                'texto' => "{$actual}/{$esperado}",
            ];

            if ($actual !== $esperado) {
                $resultado['completa'] = false;
            }
        }

        return $resultado;
    }

    protected function generarResumen(array $planilla, array $cargaLaboral, array $alertas, array $config): array
    {
        $totalDias = $config['cantidad_semanas'] * 7;
        $turnosRegularesEsperados = $totalDias * 3;
        $turnosRegularesCubiertos = 0;
        $apoyos = 0;
        $descansos = 0;

        foreach ($this->recorrerAsignaciones($planilla) as $item) {
            match ($item['turno_codigo']) {
                self::TURNO_MANANA,
                self::TURNO_TARDE,
                self::TURNO_NOCHE => $turnosRegularesCubiertos += 1,
                self::TURNO_APOYO => $apoyos += 1,
                self::TURNO_DESCANSO => $descansos += 1,
                default => null,
            };
        }

        $turnosCubiertosTexto = ((int) ($turnosRegularesCubiertos / 3)) . '/' . $turnosRegularesEsperados;

        $alertasCriticas = collect($alertas)->where('nivel', 'CRITICO')->count();

        return [
            'total_enfermeros' => count($cargaLaboral),
            'cobertura_porcentaje' => $alertasCriticas === 0 ? 100 : max(0, 100 - ($alertasCriticas * 5)),
            'cobertura_texto' => $alertasCriticas === 0 ? '100%' : 'Revisar',
            'turnos_cubiertos' => $turnosCubiertosTexto,
            'apoyos' => $apoyos,
            'descansos' => $descansos,
            'alertas_total' => count($alertas),
            'alertas_criticas' => $alertasCriticas,
            'cantidad_semanas' => $config['cantidad_semanas'],
            'salto_semanal' => $config['salto_semanal'],
            'estado_planilla' => $alertasCriticas === 0 ? 'Vista previa correcta' : 'Vista previa con observaciones',
            'clase_estado' => $alertasCriticas === 0
                ? 'bg-estado-exitoBg text-estado-exito'
                : 'bg-boton-acento/10 text-boton-acento',
        ];
    }

    protected function calcularEquilibrio(int $totalEnfermeros, int $saltoSemanal): array
    {
        $mcd = $this->mcd($totalEnfermeros, $saltoSemanal);
        $periodo = (int) ($totalEnfermeros / max(1, $mcd));

        return [
            'total_enfermeros' => $totalEnfermeros,
            'salto_semanal' => $saltoSemanal,
            'periodo_equilibrio_semanas' => $periodo,
            'semanas_equilibrio' => [
                $periodo,
                $periodo * 2,
                $periodo * 3,
                $periodo * 4,
            ],
            'descripcion' => "Con {$totalEnfermeros} enfermeros y salto semanal {$saltoSemanal}, el equilibrio completo se alcanza cada {$periodo} semana(s).",
        ];
    }

    protected function mcd(int $a, int $b): int
    {
        while ($b !== 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }

        return abs($a);
    }

    protected function generarVistaSemanal(array $planilla, array $config): array
    {
        return $planilla;
    }

    protected function generarVistaHoy(array $planilla): ?array
    {
        $hoy = now()->format('Y-m-d');

        foreach ($planilla as $semana) {
            foreach ($semana['dias'] as $dia) {
                if (($dia['fecha'] ?? null) === $hoy) {
                    return [
                        'semana' => $semana['semana'],
                        ...$dia,
                    ];
                }
            }
        }

        return $planilla[0]['dias'][0] ?? null;
    }

    protected function generarVistaPorEnfermero(array $planilla): array
    {
        $vista = [];

        foreach ($this->recorrerAsignaciones($planilla) as $item) {
            $codigo = $item['asignacion']['codigo'];

            if (! isset($vista[$codigo])) {
                $vista[$codigo] = [
                    'codigo' => $codigo,
                    'nombre' => $item['asignacion']['nombre'] ?? $codigo,
                    'familia_visual' => $item['asignacion']['familia_visual'] ?? null,
                    'clase_familia' => $item['asignacion']['clase_familia'] ?? null,
                    'programacion' => [],
                ];
            }

            $vista[$codigo]['programacion'][] = [
                'semana' => $item['semana'],
                'fecha' => $item['fecha'],
                'dia' => $item['dia'],
                'turno_codigo' => $item['turno_codigo'],
                'turno_nombre' => $item['turno_nombre'],
                'hora_inicio' => $item['asignacion']['hora_inicio'],
                'hora_fin' => $item['asignacion']['hora_fin'],
                'rol_en_turno' => $item['asignacion']['rol_en_turno'],
                'grupo' => $item['asignacion']['grupo'],
                'grupo_nombre' => $item['asignacion']['grupo_nombre'],
                'horas' => $item['asignacion']['horas'],
                'clase_turno' => $item['asignacion']['clase_turno'],
            ];
        }

        return collect($vista)->sortKeys()->values()->all();
    }

    protected function aplicarFiltros(array $resultado, array $config): array
    {
        $trabajador = $config['trabajador_filtro'];
        $turnoFiltro = $config['turno_filtro'];
        $grupoFiltro = $config['grupo_filtro'];

        if (! $trabajador && ! $turnoFiltro && ! $grupoFiltro && $config['mostrar_apoyo'] && $config['mostrar_descanso']) {
            return $resultado;
        }

        $resultado['vista_semanal'] = collect($resultado['vista_semanal'])
            ->map(function (array $semana) use ($trabajador, $turnoFiltro, $grupoFiltro, $config) {
                $semana['dias'] = collect($semana['dias'])->map(function (array $dia) use ($trabajador, $turnoFiltro, $grupoFiltro, $config) {
                    $dia['turnos'] = collect($dia['turnos'])
                        ->filter(function (array $turno, string $codigoTurno) use ($turnoFiltro, $config) {
                            if (! $config['mostrar_apoyo'] && $codigoTurno === self::TURNO_APOYO) {
                                return false;
                            }

                            if (! $config['mostrar_descanso'] && $codigoTurno === self::TURNO_DESCANSO) {
                                return false;
                            }

                            if ($turnoFiltro && $codigoTurno !== $turnoFiltro) {
                                return false;
                            }

                            return true;
                        })
                        ->map(function (array $turno) use ($trabajador, $grupoFiltro) {
                            $turno['asignaciones'] = collect($turno['asignaciones'])
                                ->filter(function (array $asignacion) use ($trabajador, $grupoFiltro) {
                                    if ($trabajador && ($asignacion['codigo'] ?? null) !== $trabajador) {
                                        return false;
                                    }

                                    if ($grupoFiltro && ($asignacion['grupo'] ?? null) !== $grupoFiltro) {
                                        return false;
                                    }

                                    return true;
                                })
                                ->values()
                                ->all();

                            return $turno;
                        })
                        ->all();

                    return $dia;
                })->values()->all();

                return $semana;
            })
            ->values()
            ->all();

        if ($trabajador) {
            $resultado['vista_por_enfermero'] = collect($resultado['vista_por_enfermero'])
                ->where('codigo', $trabajador)
                ->values()
                ->all();

            $resultado['carga_laboral'] = collect($resultado['carga_laboral'])
                ->where('codigo', $trabajador)
                ->values()
                ->all();
        }

        if ($config['solo_conflictos']) {
            $resultado['alertas'] = collect($resultado['alertas'])
                ->whereIn('nivel', ['CRITICO', 'ALTO'])
                ->values()
                ->all();
        }

        return $resultado;
    }

    protected function generarFiltrosDisponibles(array $enfermeros): array
    {
        return [
            'trabajadores' => collect($enfermeros)
                ->map(fn (array $e) => [
                    'codigo' => $e['codigo'],
                    'nombre' => $e['nombre'],
                ])
                ->values()
                ->all(),
            'turnos' => collect($this->turnos)
                ->map(fn (array $turno) => [
                    'codigo' => $turno['codigo'],
                    'nombre' => $turno['nombre'],
                ])
                ->values()
                ->all(),
            'grupos' => [
                ['codigo' => 'A', 'nombre' => 'Grupo A'],
                ['codigo' => 'B', 'nombre' => 'Grupo B'],
                ['codigo' => 'C', 'nombre' => 'Grupo C'],
            ],
        ];
    }

    protected function recorrerAsignaciones(array $planilla): array
    {
        $items = [];

        foreach ($planilla as $semana) {
            foreach ($semana['dias'] as $dia) {
                foreach ($dia['turnos'] as $codigoTurno => $turno) {
                    foreach ($turno['asignaciones'] ?? [] as $asignacion) {
                        $items[] = [
                            'semana' => $semana['semana'],
                            'fecha' => $dia['fecha'],
                            'dia' => $dia['dia'],
                            'turno_codigo' => $codigoTurno,
                            'turno_nombre' => $turno['nombre'],
                            'asignacion' => $asignacion,
                        ];
                    }
                }
            }
        }

        return $items;
    }

    protected function familiaVisual(string $codigo): string
    {
        $numero = (int) preg_replace('/[^0-9]/', '', $codigo);

        return match ($numero % 3) {
            1 => 'Familia Verde',
            2 => 'Familia Azul',
            0 => 'Familia Rosa',
            default => 'Sin familia',
        };
    }

    protected function claseFamilia(string $codigo): string
    {
        $numero = (int) preg_replace('/[^0-9]/', '', $codigo);

        return match ($numero % 3) {
            1 => 'bg-estado-exitoBg text-estado-exito border border-estado-exito/20',
            2 => 'bg-boton-principal/10 text-boton-principal border border-boton-principal/20',
            0 => 'bg-boton-acento/10 text-boton-acento border border-boton-acento/20',
            default => 'bg-fondo-hover text-apoyo border border-borde-suave',
        };
    }

    public function obtenerTurnoEnFecha(string $codUsu, $fecha): ?array
    {
        $fechaCarbon = Carbon::parse($fecha);
        $fechaInicioSemana = $fechaCarbon->copy()->startOfWeek(Carbon::MONDAY);

        $resultado = $this->generar([
            'fecha_inicio' => $fechaInicioSemana,
            'cantidad_semanas' => 1,
            'usar_usuarios_reales' => true,
        ]);

        $fechaStr = $fechaCarbon->toDateString();
        $semana = $resultado['planilla'][0] ?? null;
        if (!$semana) {
            return null;
        }

        foreach ($semana['dias'] as $dia) {
            if (($dia['fecha'] ?? null) === $fechaStr) {
                foreach ($dia['turnos'] as $turno) {
                    foreach ($turno['asignaciones'] ?? [] as $asignacion) {
                        if (($asignacion['cod_usu'] ?? null) === $codUsu) {
                            return $asignacion;
                        }
                    }
                }
            }
        }

        return null;
    }
}