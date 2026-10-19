<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Adaptadores;

use App\Models\AntecedenteClinico;
use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\Personal;
use App\Models\RegistroSueno;
use App\Models\User;
use App\Models\ValoracionPsicologica;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorControlesCognitivos as Cognitivos;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorIadlExperimental as Iadl;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorSeguimientosPedagogicos as Pedagogia;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\FuentesOperativas\AdaptadorValoracionesFuncionales as Funcionalidad;
use App\Modulos\ApoyoDecision\Infraestructura\Adaptadores\MapeoFuentes\ExtractorVariableMapeada as Tipos;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

/**
 * Adquisición por lotes desde Models existentes en una base de pruebas desechable.
 * Reutiliza listas/tipos de adaptadores; psicología y contexto médico conservan texto
 * sin NLP, interpretación ni conversión a variables cognitivas o estados Petersen.
 */
final class RecolectorContextoPerfilExperimental
{
    public const VERSION = 'CTX-FUENTES-TEST-1';

    public function recoger(User $autor, string $caso, string $evaluacion, DateTimeImmutable $corte, array $selecciones): array
    {
        $db = DB::connection();
        Tipos::conexionSegura($db->getName());
        if (! app()->runningInConsole() || ! str_starts_with($caso, 'SINTETICO-') || ! str_starts_with($evaluacion, 'SIM-')) {
            throw new DomainException('La adquisición exige consola de pruebas y caso/evaluación sintéticos.');
        }
        $contratos = $this->contratos();
        if (array_diff(array_keys($selecciones), array_keys($contratos))) {
            throw new DomainException('Fuente ajena al contrato de adquisición experimental.');
        }

        return $db->transaction(function () use ($autor, $caso, $evaluacion, $corte, $selecciones, $contratos, $db): array {
            $actor = User::query()->lockForUpdate()->findOrFail($autor->getKey());
            Gate::forUser($actor)->authorize('consultar', VersionModeloExperto::class);
            $salida = [];
            foreach ($selecciones as $tabla => $ids) {
                if (! is_array($ids) || ! array_is_list($ids) || count(array_unique($ids, SORT_REGULAR)) !== count($ids)) {
                    throw new DomainException('La selección requiere PK únicas y explícitas.');
                }
                foreach ($ids as $id) {
                    if (! is_string($id) || ! str_starts_with($id, 'SIM-') || strlen($id) > 20) {
                        throw new DomainException('Identificador fuente ajeno al conjunto sintético.');
                    }
                }
                if ($ids === []) {
                    continue;
                }
                $c = $contratos[$tabla];
                $metadatos = array_values(array_filter([$c['pk'], 'cod_residente', 'cod_personal', 'estado', $c['fecha'], $c['episodio']]));
                $filas = $c['modelo']::on($db->getName())->select($metadatos)->where('cod_residente', $caso)
                    ->whereIn($c['pk'], $ids)->orderBy($c['pk'])->get();
                if ($filas->count() !== count($ids)) {
                    throw new RuntimeException('Una fuente no existe o pertenece a otro caso.');
                }
                $autores = Personal::on($db->getName())->whereIn('cod_personal', $filas->pluck('cod_personal')->unique())
                    ->pluck('cod_personal')->all();
                $atenciones = $c['episodio'] === 'cod_atencion'
                    ? Atencion::on($db->getName())->where('cod_residente', $caso)->whereIn('cod_atencion', $filas->pluck('cod_atencion')->filter()->unique())
                        ->pluck('cod_atencion')->all() : [];
                $enCorte = [];
                foreach ($filas as $fila) {
                    $fecha = $fila->getRawOriginal($c['fecha']);
                    $instante = $this->fecha($fecha, $c['precision']);
                    if ($instante !== null && $instante <= $corte) {
                        $enCorte[] = $fila->getRawOriginal($c['pk']);
                    }
                }
                $completas = $c['modelo']::on($db->getName())->select(array_values(array_unique([...$metadatos, ...array_keys($c['campos'])])))
                    ->where('cod_residente', $caso)->whereIn($c['pk'], $enCorte)->get()->keyBy($c['pk']);
                foreach ($filas as $fila) {
                    $id = $fila->getRawOriginal($c['pk']);
                    $completa = $completas[$id] ?? null;
                    $motivos = [];
                    if ($completa !== null) {
                        foreach ($metadatos as $campo) {
                            if ($completa->getRawOriginal($campo) !== $fila->getRawOriginal($campo)) {
                                throw new RuntimeException('La fuente cambió durante su captura; se cancela el lote.');
                            }
                        }
                    } else {
                        $motivos[] = $fila->getRawOriginal($c['fecha']) === null ? 'FECHA_ORIGINAL_NO_DISPONIBLE' : 'FUENTE_POSTERIOR_AL_CORTE';
                    }
                    $personal = $fila->getRawOriginal('cod_personal');
                    if (! in_array($personal, $autores, true)) {
                        throw new DomainException('Autor original no identificable; no se inventa un profesional.');
                    }
                    $episodio = $c['episodio'] === null ? null : $fila->getRawOriginal($c['episodio']);
                    if ($c['episodio'] === 'cod_atencion' && $episodio !== null && ! in_array($episodio, $atenciones, true)) {
                        throw new DomainException('Atención original ajena al caso.');
                    }
                    $estado = $fila->getRawOriginal('estado');
                    $utilizable = $c['estados'] === null ? null : in_array($estado, $c['estados'], true);
                    if ($utilizable === false) {
                        $motivos[] = 'ESTADO_OPERACIONAL_NO_UTILIZABLE';
                    }
                    $valores = [];
                    if ($completa !== null) {
                        foreach ($c['campos'] as $campo => $tipo) {
                            $raw = $completa->getRawOriginal($campo);
                            $tipado = $tipo === 'entero' ? $this->entero($raw) : Tipos::valorFisico($completa, $campo, $tipo);
                            $valores[$campo] = ['original' => $raw, 'tipo_original' => get_debug_type($raw), 'tipado' => $tipado];
                        }
                    }
                    $salida[] = ['contrato' => self::VERSION, 'uso' => 'CAPTURA_CONTEXTO_TEST_ONLY',
                        'caso' => $caso, 'evaluacion' => $evaluacion, 'fecha_corte' => $corte->format('Y-m-d\TH:i:s.uP'),
                        'tabla' => $tabla, 'registro_original' => $id, 'fecha_original' => $fila->getRawOriginal($c['fecha']),
                        'precision_fecha' => $c['precision'], 'zona_fuente' => 'America/La_Paz',
                        'autor_original' => $personal, 'procedencia_verificada' => true, 'estado_original' => $estado,
                        'estado_utilizable' => $utilizable, 'episodio_original' => $episodio,
                        'tipo_episodio_original' => $c['episodio'], 'valores' => $valores,
                        'rol' => 'CONTEXTO_SIN_INFERENCIA_AUTOMATICA', 'motivos' => $motivos];
                }
            }

            return $salida;
        });
    }

    private function contratos(): array
    {
        $contratos = [];
        foreach ([Cognitivos::class, Pedagogia::class, Funcionalidad::class, Iadl::class] as $adaptador) {
            $contratos[$adaptador::TABLA] = ['modelo' => $adaptador::MODELO, 'pk' => $adaptador::PK,
                'fecha' => 'fecha_hora', 'precision' => 'INSTANTE_SQL', 'episodio' => $adaptador::EPISODIO,
                'campos' => $adaptador::CAMPOS, 'estados' => $adaptador::ESTADOS];
        }
        $contratos['valoraciones_psicologicas'] = ['modelo' => ValoracionPsicologica::class, 'pk' => 'cod_valoracion_psicologica',
            'fecha' => 'fecha_hora', 'precision' => 'INSTANTE_SQL', 'episodio' => 'cod_atencion', 'estados' => null,
            'campos' => array_fill_keys(['estado_animo', 'afecto', 'ansiedad', 'apatia', 'percepcion', 'conducta',
                'comunicacion', 'interaccion_social', 'impresion_cognitiva', 'conclusion', 'recomendacion'], 'texto')];
        $contratos['diagnosticos'] = ['modelo' => Diagnostico::class, 'pk' => 'cod_diagnostico', 'fecha' => 'fecha_hora',
            'precision' => 'INSTANTE_SQL', 'episodio' => 'cod_atencion', 'estados' => null,
            'campos' => array_fill_keys(['codigo_clinico', 'nombre', 'tipo', 'certeza', 'observacion'], 'texto')];
        $contratos['antecedentes_clinicos'] = ['modelo' => AntecedenteClinico::class, 'pk' => 'cod_antecedente',
            'fecha' => 'fecha_referencia', 'precision' => 'DIA', 'episodio' => null, 'estados' => null,
            'campos' => array_fill_keys(['tipo_antecedente', 'descripcion', 'fuente_informacion', 'observacion'], 'texto')];
        $contratos['registros_sueno'] = ['modelo' => RegistroSueno::class, 'pk' => 'cod_registro_sueno', 'fecha' => 'fecha',
            'precision' => 'DIA', 'episodio' => 'cod_jornada', 'estados' => null,
            'campos' => ['horas_sueno' => 'decimal', 'despertares' => 'entero', 'insomnio' => 'booleano',
                'somnolencia_diurna' => 'booleano', 'agitacion_nocturna' => 'booleano', 'calidad' => 'texto', 'observacion' => 'texto']];

        return $contratos;
    }

    private function fecha(?string $valor, string $precision): ?DateTimeImmutable
    {
        if ($valor === null) {
            return null;
        }
        $formato = $precision === 'DIA' ? 'Y-m-d' : 'Y-m-d H:i:s';
        $fecha = DateTimeImmutable::createFromFormat('!'.$formato, $valor, new DateTimeZone('America/La_Paz'));
        if ($fecha === false || $fecha->format($formato) !== $valor) {
            throw new DomainException('Fecha original inválida; no se corrige con el reloj actual.');
        }

        return $precision === 'DIA' ? $fecha->setTime(23, 59, 59) : $fecha;
    }

    private function entero(mixed $valor): ?int
    {
        if ($valor === null || is_int($valor)) {
            return $valor;
        }
        if (is_string($valor) && filter_var($valor, FILTER_VALIDATE_INT) !== false) {
            return (int) $valor;
        }
        throw new DomainException('Tipo entero incompatible en el registro original.');
    }
}
