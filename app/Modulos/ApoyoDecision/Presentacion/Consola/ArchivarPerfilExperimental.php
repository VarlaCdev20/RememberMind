<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Consola;

use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarEjecucionesExperimentales;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\Command;

/** Entrada autorizada a evidencia de ingeniería; no ofrece rutas HTTP ni escribe evaluaciones institucionales. */
final class ArchivarPerfilExperimental extends Command
{
    protected $signature = 'experto:archivo-perfil {operacion : ejecutar, consultar, historial o reproducir} {--autor= : Cuenta técnica existente} {--entrada= : Archivo local JSON con entrada integral} {--revision-g09= : PK de revisión técnica guardada y elegible} {--caso= : Identidad SINTETICO-} {--evaluacion= : Identidad SIM-}';

    protected $description = 'Conserva y consulta evidencia cifrada exclusivamente en testing/base desechable';

    public function handle(): int
    {
        if (! app()->environment('testing')) {
            $this->error('El archivo de ingeniería está cerrado fuera de testing.');

            return self::FAILURE;
        }
        $operacion = $this->argument('operacion');
        $autor = $this->option('autor');
        if (! in_array($operacion, ['ejecutar', 'consultar', 'historial', 'reproducir'], true)
            || ! is_string($autor) || ! preg_match('/^COD_USU_[0-9]{6,12}$/D', $autor)) {
            $this->error('Indique una operación disponible y la cuenta técnica mediante --autor.');

            return self::INVALID;
        }
        try {
            $usuario = User::query()->findOrFail($autor);
            $gestor = app(GestionarEjecucionesExperimentales::class);
            if ($operacion === 'ejecutar') {
                $entrada = LectorDeclaracionExperimental::leer($this->option('entrada'));
                $version = $this->option('revision-g09');
                $r = is_string($version) ? $gestor->ejecutarConRevision($usuario, $entrada, $version) : $gestor->ejecutar($usuario, $entrada);
            } else {
                $caso = $this->option('caso');
                $evaluacion = $this->option('evaluacion');
                if (! is_string($caso) || ($operacion !== 'historial' && ! is_string($evaluacion))) {
                    throw new DomainException('Indique --caso y, para una ejecución, --evaluacion.');
                }
                $r = match ($operacion) {
                    'consultar' => $gestor->consultar($usuario, $caso, $evaluacion),
                    'historial' => $gestor->historial($usuario, $caso),
                    'reproducir' => $gestor->reproducir($usuario, $caso, $evaluacion),
                };
            }
            $this->line(json_encode($r, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION));

            return self::SUCCESS;
        } catch (DomainException|\InvalidArgumentException|AuthorizationException|\JsonException $e) {
            $this->error($e instanceof \JsonException ? 'El archivo declarativo no contiene JSON válido.' : $e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            report($e);
            $this->error('No se completó la operación de archivo. Consulte el registro técnico.');

            return self::FAILURE;
        }
    }
}
