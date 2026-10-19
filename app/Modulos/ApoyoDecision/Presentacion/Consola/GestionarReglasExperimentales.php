<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Consola;

use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarConocimientoExperimental;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\EditorPaqueteExperimental;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;

/** Administración declarativa en pruebas; el archivo contiene datos, nunca PHP, SQL o expresiones ejecutables. */
final class GestionarReglasExperimentales extends Command
{
    protected $signature = 'experto:gestionar-conocimiento {operacion : crear, sucesora, comparar o retirar} {--autor= : Cuenta técnica existente} {--revision= : Identidad física conservada} {--codigo= : Código TEST- de revisión nueva} {--motivo= : Justificación técnica} {--paquete= : Archivo local JSON de conocimiento declarativo}';

    protected $description = 'Administra revisiones de conocimiento únicamente en consola testing y base desechable';

    public function handle(): int
    {
        if (! app()->environment('testing')) {
            $this->error('La gestión está cerrada fuera de testing; no habilita administración institucional.');

            return self::FAILURE;
        }
        $operacion = $this->argument('operacion');
        if (! in_array($operacion, ['crear', 'sucesora', 'comparar', 'retirar'], true)) {
            $this->error('Operación no disponible.');

            return self::INVALID;
        }
        $autor = $this->option('autor');
        if (! is_string($autor) || ! preg_match('/^COD_USU_[0-9]{6,12}$/D', $autor)) {
            $this->error('Indique la cuenta técnica responsable mediante --autor.');

            return self::INVALID;
        }
        try {
            $usuario = User::query()->findOrFail($autor);
            $gestor = app(GestionarConocimientoExperimental::class);
            $version = $this->option('revision');
            $motivo = $this->option('motivo');
            if ($operacion === 'retirar') {
                if (! is_string($version) || ! is_string($motivo)) {
                    throw new DomainException('Indique --revision y --motivo.');
                }
                $gestor->retirar($usuario, $version, $motivo);
                $this->info('Revisión retirada de nuevas pruebas; conocimiento e historia conservados.');

                return self::SUCCESS;
            }
            $archivo = $this->option('paquete');
            $ruta = is_string($archivo) ? realpath($archivo) : false;
            if (! $ruta || ! is_file($ruta) || filesize($ruta) > 1048576) {
                throw new DomainException('Indique un archivo local de conocimiento de hasta 1 MiB.');
            }
            $contenido = file_get_contents($ruta);
            if ($contenido === false) {
                throw new DomainException('No se pudo leer el conocimiento.');
            }
            $datos = json_decode($contenido, true, 64, JSON_THROW_ON_ERROR);
            if (! is_array($datos) || ! is_string($datos['version'] ?? null) || ! is_array($datos['tablas'] ?? null)
                || ! is_array($datos['contratos_relaciones'] ?? null) || ! is_array($datos['contratos_extraccion'] ?? null) || ! is_string($datos['tipo_regla_resultado'] ?? null)) {
                throw new DomainException('Contrato declarativo incompleto.');
            }
            $p = new PaqueteConocimiento($datos['version'], $datos['tablas'], $datos['contratos_relaciones'], true, $datos['tipo_regla_resultado'], $datos['contratos_extraccion']);
            if ($operacion === 'comparar') {
                if (! is_string($version)) {
                    throw new DomainException('Indique --revision.');
                }
                // La lectura también exige la Policy técnica, sin reutilizarla como permiso de escritura institucional.
                Gate::forUser($usuario)->authorize('consultar', VersionModeloExperto::class);
                $cambios = (new EditorPaqueteExperimental)->comparar($gestor->leer($usuario, $version, $p), $p);
                $this->line(json_encode($cambios, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                return self::SUCCESS;
            }
            $codigo = $this->option('codigo');
            if (! is_string($codigo) || ! is_string($motivo) || ($operacion === 'sucesora' && ! is_string($version))) {
                throw new DomainException('Indique --codigo, --motivo y, para sucesora, --revision.');
            }
            $id = $gestor->crear($usuario, $p, $codigo, $motivo, $operacion === 'sucesora' ? $version : null);
            $this->info('Revisión técnica conservada: '.$id.'. Sin activación asistencial.');

            return self::SUCCESS;
        } catch (DomainException|\JsonException|AuthorizationException $e) {
            $this->error($e instanceof \JsonException ? 'El archivo no contiene JSON declarativo válido.' : $e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            report($e);
            $this->error('No se completó la operación. Revise el registro técnico; no se confirma un guardado parcial.');

            return self::FAILURE;
        }
    }
}
