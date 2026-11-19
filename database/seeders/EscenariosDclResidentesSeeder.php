<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\ControlFuncionalIadl;
use App\Models\EvaluacionExperta;
use App\Models\Instrumento;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\ValoracionPsicologica;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GuardarResultadoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarResultadoInstitucional;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\instrumentos\RegistroMetodosInstrumentales;
use Ramsey\Uuid\Uuid;
use RuntimeException;

/** Diez casos ficticios optativos: no añade residentes, diagnósticos, permisos ni reglas. */
final class EscenariosDclResidentesSeeder extends Seeder
{
    public const LOTE = 'DCL10-20261119-V1';

    public const FECHA = '2026-11-19 12:00:00';

    public const CASOS = [
        '01' => ['documento' => '99120002', 'alterados' => ['COG-MEM'], 'patron' => 'PET-01'],
        '02' => ['documento' => '99120003', 'alterados' => ['COG-MEM', 'COG-LEN'], 'patron' => 'PET-02'],
        '03' => ['documento' => '99120001', 'alterados' => ['COG-ATE'], 'patron' => 'PET-03'],
        '04' => ['documento' => '99120004', 'alterados' => ['COG-EJE', 'COG-VIS'], 'patron' => 'PET-04'],
        '05' => ['documento' => '99120005', 'alterados' => ['COG-MEM'], 'patron' => 'PET-01'],
        '06' => ['documento' => '99120006', 'alterados' => ['COG-MEM', 'COG-ATE'], 'patron' => 'PET-02'],
        '07' => ['documento' => '99120007', 'alterados' => ['COG-LEN'], 'patron' => 'PET-03'],
        '08' => ['documento' => '99120008', 'alterados' => ['COG-ATE', 'COG-EJE'], 'patron' => 'PET-04'],
        '09' => ['documento' => '99120009', 'alterados' => ['COG-MEM'], 'patron' => 'PET-01'],
        '10' => ['documento' => '99120010', 'alterados' => ['COG-MEM', 'COG-EJE'], 'patron' => 'PET-02'],
    ];

    private const METODOS = [
        'COG-MEM' => 'HVLT-R-BO-2015-F5', 'COG-ATE' => 'BTA-BO-2015-NL',
        'COG-EJE' => 'TMT-B-BO-2015', 'COG-LEN' => 'BNT-BO-2015-60', 'COG-VIS' => 'ROCF-BO-2015-COPIA-A',
    ];

    public function run(): void
    {
        throw new RuntimeException('Carga optativa: utiliza scripts/cargar-escenarios-dcl.php con destino, profesional y área explícitos.');
    }

    public function ejecutar(string $basePermitida, string $usuarioId, string $areaId): array
    {
        $this->destino($basePermitida);
        if (DB::transactionLevel() !== 0) {
            throw new RuntimeException('La inferencia necesita transacciones propias; no envolver este seeder en otra transacción.');
        }
        if (CarbonImmutable::parse(self::FECHA)->addHour()->isFuture()) {
            throw new RuntimeException('El corte del escenario todavía está en el futuro.');
        }
        $actor = User::with('personal')->findOrFail($usuarioId);
        abort_unless($actor->estado === 'ACTIVO' && $actor->hasRole('PSICOLOGO/A')
            && $actor->personal?->estado === 'ACTIVO' && $actor->personal?->profesion === 'PSICOLOGIA', 403);
        $area = Area::whereKey($areaId)->whereIn('estado', ['ACTIVO', 'ACTIVA'])->sole();
        if (! str_contains(mb_strtoupper($area->nombre), 'PSICOLOG')) {
            throw new RuntimeException('Se necesita el área explícita de Psicología.');
        }
        $residentes = [];
        foreach (self::CASOS as $numero => $caso) {
            $numero = (string) $numero;
            $r = Residente::where('numero_documento', $caso['documento'])->where('estado', 'ADMITIDO')->sole();
            if (! str_contains(mb_strtoupper((string) $r->observacion), 'SINTÉTICO')
                || ! $r->fecha_nacimiento || $r->fecha_nacimiento->age < 60) {
                throw new RuntimeException('Solo se admiten los residentes ficticios identificados y mayores de 60 años.');
            }
            Gate::forUser($actor)->authorize('ejecutarInstitucional', [EvaluacionExperta::class, $r]);
            $residentes[$numero] = $r;
        }
        // Usar únicamente el conocimiento adoptado; no reactivar ni revisar reglas desde un fixture.
        foreach (['ORION-COGNITIVO-1', 'ORION-PETERSEN-1'] as $codigo) {
            VersionModeloExperto::where('codigo_version', $codigo)->where('estado', 'ACTIVO')->sole();
        }
        $lector = app(ConsultarResultadoInstitucional::class);
        if (! $lector->instalado()) {
            throw new RuntimeException('Falta instalar la conservación institucional autorizada.');
        }
        $perfilesAnteriores = config('sistema_experto.perfiles_instrumentales', []);
        $catalogo = $this->catalogo();
        $resultados = [];
        try {
            config(['sistema_experto.perfiles_instrumentales' => [...$perfilesAnteriores, ...array_column($catalogo, 'perfil')]]);
            foreach (self::CASOS as $numero => $caso) {
                $numero = (string) $numero;
                $r = $residentes[$numero];
                $solicitud = $this->uuid($numero, 'resultado');
                $existente = EvaluacionExperta::where('id_evaluacion_dominio', 'INST:'.$solicitud)->first();
                if (! $existente) {
                    $this->fuentes($actor, $area, $r, $numero, $caso, $catalogo);
                }
                // El Action vuelve a adquirir SQL, ejecuta los motores y verifica el resultado conservado.
                $guardado = app(GuardarResultadoInstitucional::class)->ejecutar($actor, $r, $solicitud);
                $contenido = $guardado['contenido'];
                if (! $guardado['persistido'] || $contenido['patron'] !== $caso['patron']
                    || $contenido['resultado'] !== 'PATRON_ORIENTATIVO_CON_SOPORTE') {
                    throw new RuntimeException('La inferencia del caso '.$numero.' no coincide con el contrato: '.json_encode([
                        'resultado' => $contenido['resultado'], 'patron' => $contenido['patron'], 'requisitos' => $contenido['requisitos'],
                    ], JSON_UNESCAPED_UNICODE));
                }
                $resultados[] = ['caso' => $numero, 'residente' => $r->getKey(), 'nombre' => $r->nombre_completo,
                    'evaluacion' => $contenido['id'], 'patron' => $contenido['patron'], 'resultado' => $contenido['resultado'],
                    'hechos' => count($contenido['hechos']), 'criterios' => count($contenido['criterios']), 'nuevo' => $existente === null];
            }
        } finally {
            config(['sistema_experto.perfiles_instrumentales' => $perfilesAnteriores]);
        }

        return ['lote' => self::LOTE, 'casos' => $resultados, 'diagnosticos_creados' => 0,
            'residentes_creados' => 0, 'reglas_modificadas' => false, 'confirmacion_clinica' => false];
    }

    private function destino(string $basePermitida): void
    {
        $c = DB::connection();
        $local = app()->environment('local') && $c->getDriverName() === 'pgsql'
            && $c->getDatabaseName() === 'remembermind_dev' && $basePermitida === 'remembermind_dev'
            && in_array($c->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true);
        $pruebas = app()->environment('testing') && $basePermitida === $c->getDatabaseName()
            && (($c->getDriverName() === 'sqlite' && $basePermitida === ':memory:')
                || ($c->getDriverName() === 'pgsql' && str_ends_with($basePermitida, '_testing')
                    && str_starts_with($basePermitida, 'remembermind_test_')
                    && in_array($c->getConfig('host'), ['127.0.0.1', 'localhost', '::1'], true)));
        if (! $local && ! $pruebas) {
            throw new RuntimeException('Destino no autorizado para los escenarios ficticios DCL10.');
        }
    }

    private function catalogo(): array
    {
        return DB::transaction(function (): array {
            $catalogo = [];
            $metodos = (new RegistroMetodosInstrumentales)->metodos();
            foreach (self::METODOS as $dominio => $metodo) {
                $i = Instrumento::firstOrCreate(['codigo' => 'DCL10-'.$dominio], [
                    'nombre' => 'Captura de puntuaciones '.$dominio.' · escenario docente',
                    'tipo' => 'COGNITIVO', 'version' => '1.0-DOCENTE', 'estado' => 'ACTIVO',
                    'descripcion' => self::LOTE.' · Instrumento ficticio sin reactivos; no es una administración real de una prueba publicada.',
                ]);
                if ($i->version !== '1.0-DOCENTE' || $i->tipo !== 'COGNITIVO' || $i->estado !== 'ACTIVO'
                    || ! str_starts_with((string) $i->descripcion, self::LOTE)) {
                    throw new RuntimeException('El código del catálogo docente ya identifica otro contenido.');
                }
                $selectores = [];
                foreach ($metodos[$metodo]['entradas'] as $campo => $tipo) {
                    $q = PreguntaInstrumento::firstOrCreate(['cod_instrumento' => $i->getKey(), 'codigo' => 'ENTRADA-'.count($selectores)], [
                        'enunciado' => 'Dato ficticio: '.$campo, 'tipo_respuesta' => $tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO',
                        'orden' => count($selectores), 'estado' => 'ACTIVA',
                    ]);
                    if ($q->enunciado !== 'Dato ficticio: '.$campo || $q->estado !== 'ACTIVA'
                        || $q->tipo_respuesta !== ($tipo === 'LOGICO' ? 'LOGICO' : 'NUMERICO')) {
                        throw new RuntimeException('El catálogo docente fue modificado; no se sobrescribe.');
                    }
                    $selectores[$campo] = ['cod_pregunta' => $q->getKey(), 'campo' => $tipo === 'LOGICO' ? 'valor_logico' : 'valor_numero'];
                }
                if ($i->preguntas()->count() !== count($selectores)) {
                    throw new RuntimeException('El instrumento docente tiene campos adicionales.');
                }
                $catalogo[$dominio] = ['instrumento' => $i, 'perfil' => ['id' => 'DCL10-'.$dominio.'-1',
                    'cod_instrumento' => $i->getKey(), 'codigo_instrumento' => $i->codigo, 'version_instrumento' => $i->version,
                    'metodo' => $metodo, 'identidad_instrumento' => $metodos[$metodo]['identidad'], 'selectores' => $selectores]];
            }

            return $catalogo;
        });
    }

    private function fuentes(User $actor, Area $area, Residente $r, string $numero, array $caso, array $catalogo): void
    {
        DB::transaction(function () use ($actor, $area, $r, $numero, $caso, $catalogo): void {
            $r = Residente::whereKey($r->getKey())->lockForUpdate()->sole();
            $marca = '['.self::LOTE.':'.$numero.']';
            // Fuentes completas y sin resultado: reintentar la inferencia, nunca duplicar la captura.
            $ya = ValoracionPetersenOriginal::where('solicitud_uuid', $this->uuid($numero, 'C05'))->exists();
            if ($ya) {
                if (AplicacionInstrumento::where('cod_residente', $r->getKey())->where('observacion', $marca)->count() !== 5
                    || ValoracionPetersenOriginal::where('cod_residente', $r->getKey())->where('justificacion', 'like', $marca.'%')->count() !== 9) {
                    throw new RuntimeException('El lote de fuentes está incompleto o fue alterado.');
                }

                return;
            }
            if (AplicacionInstrumento::where('cod_residente', $r->getKey())->where('observacion', $marca)->exists()) {
                throw new RuntimeException('No se completa automáticamente una captura parcial ajena a la transacción del lote.');
            }
            $fecha = CarbonImmutable::parse(self::FECHA);
            $registrar = app(RegistrarValoracionPetersenOriginal::class);
            $revisados = [];
            foreach ($catalogo as $dominio => $c) {
                $respuestas = [];
                $alterado = in_array($dominio, $caso['alterados'], true);
                foreach ($c['perfil']['selectores'] as $campo => $s) {
                    $respuestas[] = ['cod_pregunta' => $s['cod_pregunta'], $s['campo'] => $s['campo'] === 'valor_logico' ? true : $this->valor($campo, $alterado, (int) $numero)];
                }
                $atencion = $this->atencion($actor, $area, $r, $fecha, $marca.' '.$dominio);
                // Fuente de fixture: no simula una sesión asistencial ni crea turnos para el autor.
                // Los juicios y la ejecución posteriores conservan sus Actions/Policies reales.
                $app = AplicacionInstrumento::create(['fecha_hora' => $fecha, 'cod_residente' => $r->getKey(),
                    'cod_personal' => $actor->personal->getKey(), 'cod_usuario_registro' => $actor->getKey(),
                    'registrado_en' => now(), 'cod_atencion' => $atencion->getKey(), 'observacion' => $marca,
                    'cod_instrumento' => $c['instrumento']->getKey(), 'estado' => 'COMPLETA']);
                foreach ($respuestas as $respuesta) {
                    RespuestaInstrumento::create(['cod_aplicacion' => $app->getKey(), ...$respuesta]);
                }
                $v = $registrar->ejecutar($actor, $r, $this->entrada($numero, $dominio, $marca, [
                    'estado_original' => $alterado ? 'A' : 'P', 'alcance_dominio' => true, 'contexto_interpretado' => true,
                    'soportes' => [['tipo_soporte' => 'MEDICION', 'cod_aplicacion' => $app->getKey()]],
                ]));
                $revisados[] = ['tipo_soporte' => 'DOMINIO_REVISADO', 'cod_valoracion_revisada' => $v->getKey()];
            }
            $cambios = [];
            foreach ([$fecha->subDays(7), $fecha] as $i => $f) {
                $atencion = $this->atencion($actor, $area, $r, $f, $marca.' C01-'.$i);
                $cambios[] = ControlCognitivo::create(['cod_residente' => $r->getKey(), 'cod_personal' => $actor->personal->getKey(),
                    'cod_atencion' => $atencion->getKey(), 'fecha_hora' => $f, 'estado' => 'VIGENTE',
                    'observacion' => $marca.' Escenario ficticio de funcionamiento previo y cambio corroborado; no observación de una persona real.']);
            }
            $registrar->ejecutar($actor, $r, $this->entrada($numero, 'C01', $marca, [
                'cambio_respecto_previo' => true, 'corroboracion_independiente' => true,
                'soportes' => [['tipo_soporte' => 'HISTORIA_PREVIA', 'cod_control_cognitivo' => $cambios[0]->getKey()],
                    ['tipo_soporte' => 'CORROBORACION', 'cod_control_cognitivo' => $cambios[1]->getKey()]],
            ]));
            $atencion = $this->atencion($actor, $area, $r, $fecha, $marca.' IADL');
            $iadl = ControlFuncionalIadl::create(['cod_residente' => $r->getKey(), 'cod_personal' => $actor->personal->getKey(),
                'cod_usuario_registro' => $actor->getKey(), 'cod_atencion' => $atencion->getKey(), 'fecha_hora' => $fecha,
                'registrado_en' => now(), 'actividad' => 'MEDICACION', 'oportunidad' => 'OBSERVADA',
                'desempeno' => 'INDEPENDIENTE', 'tipo_ayuda' => 'NINGUNA', 'causa_ayuda' => 'NINGUNA',
                'nivel_previo' => 'INDEPENDIENTE', 'fuente' => 'OBSERVACION_DIRECTA', 'estado' => 'REGISTRADO',
                'observacion' => $marca.' Independencia relativa de un caso ficticio; no acredita administración de medicación.',
            ]);
            $registrar->ejecutar($actor, $r, $this->entrada($numero, 'C03', $marca, [
                'revision_integrada' => true, 'oportunidad_adecuada' => true, 'atribucion_revisada' => true,
                'soportes' => [['tipo_soporte' => 'IADL', 'cod_control_iadl' => $iadl->getKey()]],
            ]));
            foreach (['C04', 'C05'] as $elemento) {
                $atencion = $this->atencion($actor, $area, $r, $fecha, $marca.' '.$elemento);
                $psico = ValoracionPsicologica::create(['cod_residente' => $r->getKey(), 'cod_personal' => $actor->personal->getKey(),
                    'cod_atencion' => $atencion->getKey(), 'fecha_hora' => $fecha, 'estado' => 'VIGENTE',
                    'conclusion' => $marca.' Soporte ficticio de revisión explícita '.$elemento.'. Sin firma ni atención real.',
                ]);
                $condiciones = $elemento === 'C04'
                    ? ['revision_explicita' => true, 'factores_pertinentes_revisados' => true, 'impedimento_no_resuelto' => false]
                    : ['suficiencia_conjunto' => true, 'condiciones_revisadas' => true, 'exclusiones_revisadas' => true];
                $registrar->ejecutar($actor, $r, $this->entrada($numero, $elemento, $marca, $condiciones + [
                    'soportes' => [['tipo_soporte' => $elemento === 'C04' ? 'DIFERENCIAL' : 'CALIDAD',
                        'cod_valoracion_psicologica' => $psico->getKey()], ...($elemento === 'C05' ? $revisados : [])],
                ]));
            }
            activity('Escenarios cognitivos')->causedBy($actor)->performedOn($r)->event('dcl10_fuentes_ficticias_creadas')
                ->withProperties(['lote' => self::LOTE, 'caso' => $numero, 'origen' => 'seeder_autorizado', 'atencion_real' => false])
                ->log('Fuentes de escenario ficticio guardadas; resultado todavía pendiente de ejecutar.');
        });
    }

    private function atencion(User $actor, Area $area, Residente $r, CarbonImmutable $fecha, string $marca): Atencion
    {
        return Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $actor->personal->getKey(),
            'cod_area' => $area->getKey(), 'tipo_atencion' => 'VALORACION', 'fecha_hora' => $fecha,
            'estado' => 'FINALIZADA', 'observacion' => $marca.' · CASO FICTICIO AUTORIZADO; no acredita atención real.']);
    }

    private function entrada(string $numero, string $elemento, string $marca, array $extra): array
    {
        return array_replace(['solicitud_uuid' => $this->uuid($numero, $elemento), 'elemento' => $elemento,
            'estado_original' => 'S', 'justificacion' => $marca.' Juicio original ficticio para verificar el contrato '.$elemento.'. No es un diagnóstico real.',
            'fecha_hora' => CarbonImmutable::now()->format('Y-m-d\TH:i:s.uP'), 'tipo_registro' => 'REGISTRO', 'soportes' => []], $extra);
    }

    private function uuid(string $numero, string $elemento): string
    {
        return Uuid::uuid5('0955b0ea-c006-444d-8e4a-8fd2c4fed4ce', self::LOTE.':'.$numero.':'.$elemento)->toString();
    }

    private function valor(string $campo, bool $alterado, int $n): int|float
    {
        $v = $n % 3;

        return match ($campo) {
            'ensayo_1' => $alterado ? 3 + $v : 8 + $v, 'ensayo_2' => $alterado ? 4 + $v : 9 + $v,
            'ensayo_3' => $alterado ? 5 + $v : 10 + $v, 'recuerdo_diferido' => $alterado ? 2 + $v : 8 + $v,
            'demora_minutos' => 22, 'escolaridad_anios' => 8,
            'forma_n_aciertos', 'forma_l_aciertos' => $alterado ? 3 + $v : 8 + ($n % 2),
            'segundos_parte_b' => $alterado ? 230 + $n : 90 + $n,
            'errores_observados' => 0, 'aciertos_espontaneos' => $alterado ? 28 + $n : 52 + $v,
            'aciertos_clave_semantica' => 2, 'puntaje_copia' => $alterado ? 16 + $v : 30 + $v,
            'elementos_revisados' => 18,
            default => throw new RuntimeException('Entrada numérica sin escenario explícito: '.$campo),
        };
    }
}
