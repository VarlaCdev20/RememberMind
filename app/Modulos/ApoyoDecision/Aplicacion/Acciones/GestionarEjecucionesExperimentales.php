<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\AlmacenEjecucionesExperimentales;
use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\aplicacion\casos_uso\EjecutarPerfilExperimental;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental;
use MotorExperto\investigacion\g09\PaqueteSemanticoG09Experimental;
use MotorExperto\soporte\conservacion\CodecInstantanea;

/** Ejecución y evidencia histórica exclusivamente de ingeniería, con cuenta técnica y destino desechable. */
final class GestionarEjecucionesExperimentales
{
    public function ejecutar(User $autor, array $entrada): array
    {
        $actor = $this->autor($autor);
        $this->validarEntrada($entrada);
        if (isset($entrada['conocimientoG09'])) {
            $version = $entrada['conocimientoG09']['version'] ?? null;
            if (! is_string($version)
                || (! str_starts_with($version, 'SIM-') && $version !== CatalogoReglasG09Experimental::VERSION)
                || DB::table('versiones_modelo_experto')->where('cod_version_modelo', $version)->orWhere('codigo_version', $version)->exists()) {
                throw new DomainException('Una revisión registrada o reservada exige selección autorizada mediante revision-g09.');
            }
        }
        $resultado = (new EjecutarPerfilExperimental)->ejecutar(...$entrada);

        return $this->conservar($actor, $resultado);
    }

    /** Usa la revisión realmente conservada, elegible y bloqueada durante todo el cálculo del perfil. */
    public function ejecutarConRevision(User $autor, array $entrada, string $versionG09): array
    {
        $actor = $this->autor($autor);
        $this->validarEntrada($entrada);
        if (array_key_exists('conocimientoG09', $entrada)) {
            throw new DomainException('Seleccione la revisión guardada sin aportar un paquete alternativo.');
        }
        $resultado = app(GestionarConocimientoExperimental::class)->conRevisionElegible($actor, $versionG09,
            PaqueteSemanticoG09Experimental::crear(), static function ($p) use ($entrada): array {
                $entrada['conocimientoG09'] = $p->declaracion();

                return (new EjecutarPerfilExperimental)->ejecutar(...$entrada);
            });

        return $this->conservar($actor, $resultado);
    }

    private function validarEntrada(array $entrada): void
    {
        $claves = ['contexto', 'capturas', 'tareas', 'memoria', 'referencias', 'comparaciones', 'fuentesComplementarias', 'conocimientoG09'];
        if (array_is_list($entrada) || array_diff(array_keys($entrada), $claves)
            || ! is_array($entrada['contexto'] ?? null) || ! is_array($entrada['capturas'] ?? null)
            || ! is_array($entrada['tareas'] ?? null) || ! is_string($entrada['memoria'] ?? null)) {
            throw new DomainException('La entrada integral requiere contexto, capturas, tareas y memoria declarativos.');
        }
        foreach (['referencias', 'comparaciones', 'fuentesComplementarias', 'conocimientoG09'] as $clave) {
            if (array_key_exists($clave, $entrada) && $entrada[$clave] !== null && ! is_array($entrada[$clave])) {
                throw new DomainException('Campo integral mal tipado: '.$clave);
            }
        }
        foreach (['comparaciones', 'fuentesComplementarias'] as $clave) {
            if (array_key_exists($clave, $entrada) && ! is_array($entrada[$clave])) {
                throw new DomainException('La colección integral no admite NULL: '.$clave);
            }
        }
    }

    private function conservar(User $actor, array $resultado): array
    {
        // Registra la intención antes de publicar. Si falla la auditoría no se crea el archivo;
        // si falla después, el intento y la identidad permiten recuperar el artefacto idempotente.
        $this->auditar($actor, 'ejecucion_laboratorio_solicitada', $resultado['caso'], $resultado['evaluacion']);
        $r = app(AlmacenEjecucionesExperimentales::class)->guardar($resultado['instantanea_entrada'], $resultado, $actor->getKey(), $this->huellaMotor());
        $this->auditar($actor, 'ejecucion_laboratorio_conservada', $r['caso'], $r['evaluacion']);

        return $r;
    }

    public function consultar(User $autor, string $caso, string $evaluacion): array
    {
        $actor = $this->autor($autor);
        $r = app(AlmacenEjecucionesExperimentales::class)->leer($caso, $evaluacion);
        $this->auditar($actor, 'ejecucion_laboratorio_consultada', $caso, $evaluacion);

        return $r;
    }

    public function historial(User $autor, string $caso): array
    {
        $actor = $this->autor($autor);
        $r = app(AlmacenEjecucionesExperimentales::class)->listar($caso);
        $this->auditar($actor, 'historial_laboratorio_consultado', $caso, null);

        return $r;
    }

    public function reproducir(User $autor, string $caso, string $evaluacion): array
    {
        $r = $this->consultar($autor, $caso, $evaluacion);
        if ($r['motor_sha256'] !== $this->huellaMotor()) {
            throw new DomainException('El motor actual difiere del conservado; la consulta histórica sigue disponible, pero no se reinterpreta.');
        }
        $calculado = (new EjecutarPerfilExperimental)->ejecutar(...$r['entrada']);
        if (CodecInstantanea::huella($calculado) !== $r['huella_resultado']) {
            throw new DomainException('La reproducción difiere del resultado histórico; se conserva el original.');
        }

        return $calculado;
    }

    private function autor(User $autor): User
    {
        $db = DB::connection();
        if (! app()->environment('testing') || ! app()->runningInConsole()
            || ! BaseTecnicaDesechable::permite($db->getDriverName(), $db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new DomainException('Archivo experimental disponible solo en consola/testing y base desechable.');
        }
        $actor = User::query()->findOrFail($autor->getKey());
        Gate::forUser($actor)->authorize('consultar', VersionModeloExperto::class);

        return $actor;
    }

    private function huellaMotor(): string
    {
        $raiz = base_path('motor-experto/src');
        $hashes = [];
        $iterador = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($raiz, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterador as $archivo) {
            if ($archivo->isFile() && $archivo->getExtension() === 'php') {
                $hash = hash_file('sha256', $archivo->getPathname());
                if ($hash === false) {
                    throw new \RuntimeException('No se pudo verificar una fuente del motor.');
                }
                $hashes[str_replace('\\', '/', substr($archivo->getPathname(), strlen($raiz)))] = $hash;
            }
        }
        ksort($hashes, SORT_STRING);

        return CodecInstantanea::huella($hashes);
    }

    private function auditar(User $actor, string $evento, string $caso, ?string $evaluacion): void
    {
        activity('sistema_experto')->causedBy($actor)->event($evento)
            ->withProperties(['caso' => $caso, 'evaluacion' => $evaluacion, 'uso' => 'TEST_ONLY', 'conservacion_bdd' => false])
            ->log('Operación de evidencia técnica de laboratorio.');
    }
}
